<?php
/**
 * Invoice Model
 */

class Invoice extends Model {
    protected $table = 'invoices';
    
    /**
     * Get invoice with student details
     */
    public function getInvoiceWithDetails($id) {
        $sql = "SELECT i.*, 
                       s.first_name as student_first_name,
                       s.last_name as student_last_name,
                       s.admission_number,
                       c.name as class_name,
                       g.display_name as grade_display_name
                FROM invoices i
                LEFT JOIN students s ON i.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                WHERE i.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get invoices by student
     */
    public function getByStudent($studentId, $academicYear = null) {
        $sql = "SELECT * FROM {$this->table} WHERE student_id = ?";
        $params = [$studentId];
        
        if ($academicYear) {
            $sql .= " AND academic_year = ?";
            $params[] = $academicYear;
        }
        
        $sql .= " ORDER BY term, created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Generate invoice number
     */
    public function generateInvoiceNumber() {
        $year = date('Y');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return 'INV-' . $year . '-' . $random;
    }
    
    /**
     * Update invoice balance after payment
     *
     * Primary source of truth is the payments table (what was actually paid).
     * Fee head payments are now considered legacy and only used if there are no payments.
     */
    public function updateBalance($invoiceId) {
        // Get invoice details
        $invoice = $this->findById($invoiceId);
        if (!$invoice) {
            return false;
        }

        // 1) Calculate paid amount from payments table for this invoice
        $paymentSql = "SELECT COALESCE(SUM(amount), 0) as total_paid FROM payments WHERE invoice_id = ?";
        $paymentStmt = $this->db->prepare($paymentSql);
        $paymentStmt->execute([$invoiceId]);
        $paymentResult = $paymentStmt->fetch();
        $paidAmount = $paymentResult['total_paid'] ?? 0;

        // 2) Legacy fallback: if no payments exist, use fee_head_payments aggregate
        if ($paidAmount == 0) {
            $sql = "SELECT COALESCE(SUM(fhp.amount), 0) as total_paid
                    FROM fee_head_payments fhp
                    INNER JOIN student_fee_heads sfh ON fhp.student_fee_head_id = sfh.id
                    WHERE sfh.student_id = ? AND sfh.term = ? AND sfh.academic_year = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$invoice['student_id'], $invoice['term'], $invoice['academic_year']]);
            $result = $stmt->fetch();
            $paidAmount = $result['total_paid'] ?? 0;
        }
        
        $balance = $invoice['total_amount'] - $paidAmount;
        $status = 'pending';
        if ($balance <= 0 && $paidAmount > 0) {
            $status = 'paid';
        } elseif ($paidAmount > 0 && $balance > 0) {
            $status = 'partial';
        }
        
        $updateSql = "UPDATE {$this->table} 
                     SET paid_amount = ?, 
                         balance = ?, 
                         status = ?
                     WHERE id = ?";
        
        $updateStmt = $this->db->prepare($updateSql);
        return $updateStmt->execute([$paidAmount, $balance, $status, $invoiceId]);
    }
    
    /**
     * Get multi-term fee carry-forward breakdown for a student
     *
     * Calculates term-by-term carry forward arrears or overpayment credit across terms.
     *
     * @param int $studentId
     * @param string|null $academicYear
     * @return array Contains 'invoices', 'total_billed', 'total_paid', 'net_balance'
     */
    public function getStudentTermBalances($studentId, $academicYear = null) {
        $invoices = $this->getByStudent($studentId, $academicYear);
        
        // Ensure balances are updated for all invoices
        foreach ($invoices as &$inv) {
            $this->updateBalance($inv['id']);
        }
        
        // Re-fetch with fresh balances
        $invoices = $this->getByStudent($studentId, $academicYear);
        
        $runningArrears = 0.00;
        $totalBilled = 0.00;
        $totalPaid = 0.00;
        
        $termBreakdown = [];
        foreach ($invoices as $inv) {
            $termFee = floatval($inv['total_amount'] ?? 0);
            $termPaid = floatval($inv['paid_amount'] ?? 0);
            
            $carriedIn = $runningArrears; // Arrears (+) or Credit (-) carried from prior terms
            $totalPayable = $carriedIn + $termFee;
            $netTermBalance = $totalPayable - $termPaid;
            
            $invData = $inv;
            $invData['term_fee'] = $termFee;
            $invData['term_paid'] = $termPaid;
            $invData['carried_in'] = $carriedIn;
            $invData['total_payable'] = $totalPayable;
            $invData['net_term_balance'] = $netTermBalance;
            
            $termBreakdown[] = $invData;
            
            // Update running arrears for next term
            $runningArrears = $netTermBalance;
            
            $totalBilled += $termFee;
            $totalPaid += $termPaid;
        }
        
        return [
            'invoices' => $termBreakdown,
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'net_balance' => $runningArrears
        ];
    }
    
    /**
     * Get total cumulative balance for a student up to a specific term/year
     *
     * @param int $studentId
     * @param int|null $upToTerm
     * @param string|null $academicYear
     * @return float Positive = Outstanding Arrears, Negative = Overpayment Credit
     */
    public function getCumulativeBalance($studentId, $upToTerm = null, $academicYear = null) {
        $summary = $this->getStudentTermBalances($studentId, $academicYear);
        
        if (empty($summary['invoices'])) {
            return 0.00;
        }
        
        if ($upToTerm === null) {
            return $summary['net_balance'];
        }
        
        $netBalance = 0.00;
        foreach ($summary['invoices'] as $inv) {
            if ($inv['term'] <= $upToTerm) {
                $netBalance = $inv['net_term_balance'];
            }
        }
        
        return $netBalance;
    }
    
    /**
     * Allocate a payment across student invoices starting from oldest term arrears
     *
     * @param int $studentId
     * @param float $totalAmount
     * @param array $paymentMeta (payment_method, reference_number, received_by, remarks, mpesa_receipt, mpesa_transaction_id)
     * @return array Details of created payments and allocated amounts
     */
    public function allocatePaymentAcrossInvoices($studentId, $totalAmount, $paymentMeta = []) {
        if ($totalAmount <= 0) {
            return ['success' => false, 'message' => 'Invalid payment amount'];
        }
        
        $academicYear = $paymentMeta['academic_year'] ?? (date('Y') . '/' . (date('Y') + 1));
        $invoices = $this->getByStudent($studentId, $academicYear);
        
        // Ensure balances are updated
        foreach ($invoices as $inv) {
            $this->updateBalance($inv['id']);
        }
        
        // Re-fetch updated invoices
        $invoices = $this->getByStudent($studentId, $academicYear);
        
        if (empty($invoices)) {
            return ['success' => false, 'message' => 'No invoices found for student'];
        }
        
        // Separate pending/partial invoices and sort by term ASC
        usort($invoices, function($a, $b) {
            return $a['term'] <=> $b['term'];
        });
        
        $remainingPayment = floatval($totalAmount);
        $allocatedPayments = [];
        
        $paymentModel = new Payment();
        
        // First, allocate to pending/partial invoices in chronological order
        foreach ($invoices as $inv) {
            if ($remainingPayment <= 0) {
                break;
            }
            
            $invBalance = floatval($inv['balance'] ?? 0);
            if ($invBalance <= 0) {
                continue; // Invoice fully paid
            }
            
            $payForThisInvoice = min($remainingPayment, $invBalance);
            
            $receiptNo = $paymentModel->generateReceiptNumber();
            $paymentData = [
                'invoice_id' => $inv['id'],
                'student_id' => $studentId,
                'payment_method' => $paymentMeta['payment_method'] ?? 'cash',
                'amount' => $payForThisInvoice,
                'payment_date' => $paymentMeta['payment_date'] ?? date('Y-m-d'),
                'receipt_number' => $receiptNo,
                'reference_number' => $paymentMeta['reference_number'] ?? '',
                'mpesa_receipt' => $paymentMeta['mpesa_receipt'] ?? '',
                'mpesa_transaction_id' => $paymentMeta['mpesa_transaction_id'] ?? '',
                'received_by' => $paymentMeta['received_by'] ?? Auth::userId(),
                'remarks' => ($paymentMeta['remarks'] ?? '') . " (Auto-allocated Term {$inv['term']})"
            ];
            
            $pId = $paymentModel->create($paymentData);
            if ($pId) {
                $this->updateBalance($inv['id']);
                $allocatedPayments[] = [
                    'payment_id' => $pId,
                    'invoice_id' => $inv['id'],
                    'term' => $inv['term'],
                    'amount' => $payForThisInvoice,
                    'receipt_number' => $receiptNo
                ];
                $remainingPayment -= $payForThisInvoice;
            }
        }
        
        // If there's still leftover payment (overpayment credit), apply to the latest invoice
        if ($remainingPayment > 0 && !empty($invoices)) {
            $latestInvoice = end($invoices);
            $receiptNo = $paymentModel->generateReceiptNumber();
            $paymentData = [
                'invoice_id' => $latestInvoice['id'],
                'student_id' => $studentId,
                'payment_method' => $paymentMeta['payment_method'] ?? 'cash',
                'amount' => $remainingPayment,
                'payment_date' => $paymentMeta['payment_date'] ?? date('Y-m-d'),
                'receipt_number' => $receiptNo,
                'reference_number' => $paymentMeta['reference_number'] ?? '',
                'mpesa_receipt' => $paymentMeta['mpesa_receipt'] ?? '',
                'mpesa_transaction_id' => $paymentMeta['mpesa_transaction_id'] ?? '',
                'received_by' => $paymentMeta['received_by'] ?? Auth::userId(),
                'remarks' => ($paymentMeta['remarks'] ?? '') . " (Excess credit carried forward)"
            ];
            
            $pId = $paymentModel->create($paymentData);
            if ($pId) {
                $this->updateBalance($latestInvoice['id']);
                $allocatedPayments[] = [
                    'payment_id' => $pId,
                    'invoice_id' => $latestInvoice['id'],
                    'term' => $latestInvoice['term'],
                    'amount' => $remainingPayment,
                    'receipt_number' => $receiptNo
                ];
            }
        }
        
        return [
            'success' => true,
            'allocated' => $allocatedPayments
        ];
    }
}

