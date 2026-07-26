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
        if ($academicYear === 'all') {
            $academicYear = null;
        }
        $invoices = $this->getByStudent($studentId, $academicYear);
        
        // Fallback: If no invoices found for the specified academic year, check if student has invoices in any year
        if (empty($invoices) && $academicYear !== null) {
            $allInvoices = $this->getByStudent($studentId, null);
            if (!empty($allInvoices)) {
                $invoices = $allInvoices;
            }
        }
        
        // Ensure balances are updated for all invoices
        foreach ($invoices as &$inv) {
            $this->updateBalance($inv['id']);
        }
        
        // Re-fetch with fresh balances
        if (empty($invoices) || $academicYear === null) {
            $invoices = $this->getByStudent($studentId, null);
        } else {
            $invoices = $this->getByStudent($studentId, $academicYear);
            if (empty($invoices)) {
                $invoices = $this->getByStudent($studentId, null);
            }
        }
        
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

    /**
     * Build flexible SQL condition for academic year matching
     */
    private function buildAcademicYearWhereClause($columnName, $academicYear, &$params) {
        if (empty($academicYear) || $academicYear === 'all') {
            return "1=1";
        }

        $trimmed = trim($academicYear);
        preg_match_all('/\d{4}/', $trimmed, $matches);
        $years = array_unique($matches[0] ?? []);

        $conditions = [
            "{$columnName} = ?",
            "TRIM({$columnName}) = ?",
            "REPLACE({$columnName}, '-', '/') = ?",
            "REPLACE({$columnName}, '/', '-') = ?"
        ];

        $params[] = $trimmed;
        $params[] = $trimmed;
        $params[] = str_replace('-', '/', $trimmed);
        $params[] = str_replace('/', '-', $trimmed);

        foreach ($years as $y) {
            $conditions[] = "{$columnName} LIKE ?";
            $params[] = '%' . $y . '%';
        }

        $conditions[] = "({$columnName} IS NULL OR {$columnName} = '')";

        return "(" . implode(" OR ", $conditions) . ")";
    }

    /**
     * Get overall term summary metrics (Term 1, Term 2, Term 3, and Total)
     *
     * @param string|null $academicYear
     * @param int|null $classId
     * @return array
     */
    public function getTermSummaryMetrics($academicYear = null, $classId = null) {
        $params = [];
        $whereClause = "1=1";

        if (!empty($academicYear) && $academicYear !== 'all') {
            $whereClause .= " AND " . $this->buildAcademicYearWhereClause('i.academic_year', $academicYear, $params);
        }

        if (!empty($classId)) {
            $whereClause .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        $sql = "SELECT 
                    i.term,
                    COUNT(i.id) as invoice_count,
                    COALESCE(SUM(i.total_amount), 0) as total_billed,
                    COALESCE(SUM(i.paid_amount), 0) as total_paid,
                    COALESCE(SUM(i.balance), 0) as total_balance
                FROM invoices i
                LEFT JOIN students s ON i.student_id = s.id
                WHERE {$whereClause}
                GROUP BY i.term";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fallback: If 0 invoices were found for specific year filter, re-query without year restriction if invoices exist
        if (empty($rows) && !empty($academicYear)) {
            $fbParams = [];
            $fbWhere = "1=1";
            if (!empty($classId)) {
                $fbWhere .= " AND s.class_id = ?";
                $fbParams[] = $classId;
            }
            $stmt = $this->db->prepare("SELECT i.term, COUNT(i.id) as invoice_count, COALESCE(SUM(i.total_amount), 0) as total_billed, COALESCE(SUM(i.paid_amount), 0) as total_paid, COALESCE(SUM(i.balance), 0) as total_balance FROM invoices i LEFT JOIN students s ON i.student_id = s.id WHERE {$fbWhere} GROUP BY i.term");
            $stmt->execute($fbParams);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $termData = [
            1 => ['billed' => 0.00, 'paid' => 0.00, 'balance' => 0.00, 'invoices' => 0, 'rate' => 0.0],
            2 => ['billed' => 0.00, 'paid' => 0.00, 'balance' => 0.00, 'invoices' => 0, 'rate' => 0.0],
            3 => ['billed' => 0.00, 'paid' => 0.00, 'balance' => 0.00, 'invoices' => 0, 'rate' => 0.0],
        ];

        $overallBilled = 0.00;
        $overallPaid = 0.00;
        $overallBalance = 0.00;
        $overallInvoices = 0;

        foreach ($rows as $row) {
            $t = intval($row['term']);
            if (isset($termData[$t])) {
                $billed = floatval($row['total_billed']);
                $paid = floatval($row['total_paid']);
                $bal = floatval($row['total_balance']);
                $rate = ($billed > 0) ? round(($paid / $billed) * 100, 1) : 0.0;

                $termData[$t] = [
                    'billed' => $billed,
                    'paid' => $paid,
                    'balance' => $bal,
                    'invoices' => intval($row['invoice_count']),
                    'rate' => $rate
                ];

                $overallBilled += $billed;
                $overallPaid += $paid;
                $overallBalance += $bal;
                $overallInvoices += intval($row['invoice_count']);
            }
        }

        $overallRate = ($overallBilled > 0) ? round(($overallPaid / $overallBilled) * 100, 1) : 0.0;

        return [
            'terms' => $termData,
            'overall' => [
                'billed' => $overallBilled,
                'paid' => $overallPaid,
                'balance' => $overallBalance,
                'invoices' => $overallInvoices,
                'rate' => $overallRate
            ]
        ];
    }

    /**
     * Get class-wise term breakdown matrix
     *
     * @param string|null $academicYear
     * @return array
     */
    public function getClassTermBreakdown($academicYear = null) {
        $sqlClasses = "SELECT c.id as class_id, c.name as class_name, g.display_name as grade_name,
                             (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id AND s.status = 'active') as student_count
                      FROM classes c
                      LEFT JOIN grades g ON c.grade_id = g.id
                      WHERE c.status = 'active'
                      ORDER BY g.level ASC, c.name ASC";
        $classes = $this->db->query($sqlClasses)->fetchAll(PDO::FETCH_ASSOC);

        $params = [];
        $where = "1=1";
        if (!empty($academicYear) && $academicYear !== 'all') {
            $where .= " AND " . $this->buildAcademicYearWhereClause('i.academic_year', $academicYear, $params);
        }

        $sqlInvoices = "SELECT s.class_id, i.term,
                               COALESCE(SUM(i.total_amount), 0) as total_billed,
                               COALESCE(SUM(i.paid_amount), 0) as total_paid,
                               COALESCE(SUM(i.balance), 0) as total_balance
                        FROM invoices i
                        LEFT JOIN students s ON i.student_id = s.id
                        WHERE {$where} AND s.class_id IS NOT NULL
                        GROUP BY s.class_id, i.term";
        $stmt = $this->db->prepare($sqlInvoices);
        $stmt->execute($params);
        $invoiceRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fallback: If 0 invoice rows returned with academic year filter, fallback to all invoices
        if (empty($invoiceRows) && !empty($academicYear)) {
            $stmt = $this->db->query("SELECT s.class_id, i.term, COALESCE(SUM(i.total_amount), 0) as total_billed, COALESCE(SUM(i.paid_amount), 0) as total_paid, COALESCE(SUM(i.balance), 0) as total_balance FROM invoices i LEFT JOIN students s ON i.student_id = s.id WHERE s.class_id IS NOT NULL GROUP BY s.class_id, i.term");
            $invoiceRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $matrix = [];
        foreach ($invoiceRows as $inv) {
            $cId = $inv['class_id'];
            $t = intval($inv['term']);
            if (!isset($matrix[$cId])) {
                $matrix[$cId] = [];
            }
            $matrix[$cId][$t] = [
                'billed' => floatval($inv['total_billed']),
                'paid' => floatval($inv['total_paid']),
                'balance' => floatval($inv['total_balance'])
            ];
        }

        $result = [];
        foreach ($classes as $c) {
            $cId = $c['class_id'];
            $t1 = $matrix[$cId][1] ?? ['billed' => 0, 'paid' => 0, 'balance' => 0];
            $t2 = $matrix[$cId][2] ?? ['billed' => 0, 'paid' => 0, 'balance' => 0];
            $t3 = $matrix[$cId][3] ?? ['billed' => 0, 'paid' => 0, 'balance' => 0];

            $totBilled = $t1['billed'] + $t2['billed'] + $t3['billed'];
            $totPaid = $t1['paid'] + $t2['paid'] + $t3['paid'];
            $totBal = $t1['balance'] + $t2['balance'] + $t3['balance'];
            $rate = ($totBilled > 0) ? round(($totPaid / $totBilled) * 100, 1) : 0.0;

            $result[] = [
                'class_id' => $cId,
                'class_name' => $c['class_name'],
                'grade_name' => $c['grade_name'],
                'student_count' => intval($c['student_count']),
                'term_1' => $t1,
                'term_2' => $t2,
                'term_3' => $t3,
                'total_billed' => $totBilled,
                'total_paid' => $totPaid,
                'total_balance' => $totBal,
                'collection_rate' => $rate
            ];
        }

        return $result;
    }

    /**
     * Get student list with term-by-term fee summary and cumulative balance
     *
     * @param string|null $academicYear
     * @param int|null $classId
     * @param int|null $termFilter
     * @param string|null $statusFilter ('paid', 'partial', 'pending', 'overpaid')
     * @param string|null $searchQuery
     * @return array
     */
    public function getStudentTermBalanceList($academicYear = null, $classId = null, $termFilter = null, $statusFilter = null, $searchQuery = null) {
        $where = "s.status = 'active'";
        $params = [];

        if (!empty($classId)) {
            $where .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        if (!empty($searchQuery)) {
            $where .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.admission_number LIKE ?)";
            $searchTerm = '%' . $searchQuery . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sqlStudents = "SELECT s.id as student_id, s.admission_number, s.first_name, s.last_name, s.gender,
                               c.name as class_name, g.display_name as grade_name
                        FROM students s
                        LEFT JOIN classes c ON s.class_id = c.id
                        LEFT JOIN grades g ON c.grade_id = g.id
                        WHERE {$where}
                        ORDER BY c.name ASC, s.first_name ASC, s.last_name ASC";

        $stmt = $this->db->prepare($sqlStudents);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($students)) {
            return [];
        }

        $studentIds = array_column($students, 'student_id');
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

        $invParams = $studentIds;
        $invWhere = "student_id IN ({$placeholders})";
        if (!empty($academicYear) && $academicYear !== 'all') {
            $invWhere .= " AND " . $this->buildAcademicYearWhereClause('academic_year', $academicYear, $invParams);
        }

        $sqlInvoices = "SELECT student_id, term, total_amount, paid_amount, balance
                        FROM invoices
                        WHERE {$invWhere}
                        ORDER BY term ASC";
        $stmtInv = $this->db->prepare($sqlInvoices);
        $stmtInv->execute($invParams);
        $invoiceRows = $stmtInv->fetchAll(PDO::FETCH_ASSOC);

        // Fallback: If 0 invoice rows returned for student list with academic year filter, query without year filter
        if (empty($invoiceRows) && !empty($academicYear)) {
            $fbInvParams = $studentIds;
            $sqlInvoices = "SELECT student_id, term, total_amount, paid_amount, balance
                            FROM invoices
                            WHERE student_id IN ({$placeholders})
                            ORDER BY term ASC";
            $stmtInv = $this->db->prepare($sqlInvoices);
            $stmtInv->execute($fbInvParams);
            $invoiceRows = $stmtInv->fetchAll(PDO::FETCH_ASSOC);
        }

        $studentInvoiceMap = [];
        foreach ($invoiceRows as $inv) {
            $sId = $inv['student_id'];
            $t = intval($inv['term']);
            if (!isset($studentInvoiceMap[$sId])) {
                $studentInvoiceMap[$sId] = [];
            }
            $studentInvoiceMap[$sId][$t] = [
                'billed' => floatval($inv['total_amount']),
                'paid' => floatval($inv['paid_amount']),
                'balance' => floatval($inv['balance'])
            ];
        }

        $result = [];
        foreach ($students as $st) {
            $sId = $st['student_id'];
            $terms = $studentInvoiceMap[$sId] ?? [];

            $t1 = $terms[1] ?? ['billed' => 0.0, 'paid' => 0.0, 'balance' => 0.0];
            $t2 = $terms[2] ?? ['billed' => 0.0, 'paid' => 0.0, 'balance' => 0.0];
            $t3 = $terms[3] ?? ['billed' => 0.0, 'paid' => 0.0, 'balance' => 0.0];

            $totBilled = $t1['billed'] + $t2['billed'] + $t3['billed'];
            $totPaid = $t1['paid'] + $t2['paid'] + $t3['paid'];
            $netBalance = $totBilled - $totPaid;

            if ($netBalance < -0.01) {
                $feeStatus = 'overpaid';
            } elseif ($netBalance <= 0.01 && $totBilled > 0) {
                $feeStatus = 'paid';
            } elseif ($totPaid > 0) {
                $feeStatus = 'partial';
            } else {
                $feeStatus = 'pending';
            }

            // Filter by specific term if requested
            if (!empty($termFilter)) {
                $tf = intval($termFilter);
                $termBal = ($terms[$tf]['balance'] ?? 0.0);
                if ($statusFilter === 'paid' && $termBal > 0.01) continue;
                if ($statusFilter === 'pending' && $termBal <= 0.01) continue;
                if ($statusFilter === 'partial' && ($termBal <= 0.01 || ($terms[$tf]['paid'] ?? 0) == 0)) continue;
            } elseif (!empty($statusFilter)) {
                if ($statusFilter !== 'all' && $feeStatus !== $statusFilter) {
                    continue;
                }
            }

            $st['t1'] = $t1;
            $st['t2'] = $t2;
            $st['t3'] = $t3;
            $st['total_billed'] = $totBilled;
            $st['total_paid'] = $totPaid;
            $st['net_balance'] = $netBalance;
            $st['fee_status'] = $feeStatus;

            $result[] = $st;
        }

        return $result;
    }
}

