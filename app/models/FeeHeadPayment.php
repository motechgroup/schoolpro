<?php
/**
 * Fee Head Payment Model
 * Tracks payments per fee head
 */

class FeeHeadPayment extends Model {
    protected $table = 'fee_head_payments';
    
    /**
     * Get payments for a student fee head
     */
    public function getByStudentFeeHead($studentFeeHeadId) {
        $sql = "SELECT fhp.*, p.payment_date, p.payment_method, p.receipt_number, 
                       p.reference_number, p.remarks, p.mpesa_receipt, p.mpesa_transaction_id,
                       p.created_at, p.amount as payment_amount
                FROM {$this->table} fhp
                LEFT JOIN payments p ON fhp.payment_id = p.id
                WHERE fhp.student_fee_head_id = ?
                ORDER BY p.payment_date DESC, p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentFeeHeadId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get total paid for a student fee head
     */
    public function getTotalPaid($studentFeeHeadId) {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM {$this->table} 
                WHERE student_fee_head_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentFeeHeadId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Get fee head payment breakdown for student
     */
    public function getStudentFeeHeadBreakdown($studentId, $term, $academicYear) {
        $sql = "SELECT sfh.*,
                       fh.code as fee_head_code,
                       fh.name as fee_head_name,
                       COALESCE(SUM(fhp.amount), 0) as paid_amount,
                       (sfh.amount - COALESCE(SUM(fhp.amount), 0)) as balance
                FROM student_fee_heads sfh
                LEFT JOIN fee_heads fh ON sfh.fee_head_id = fh.id
                LEFT JOIN fee_head_payments fhp ON sfh.id = fhp.student_fee_head_id
                WHERE sfh.student_id = ? AND sfh.term = ? AND sfh.academic_year = ? AND sfh.status = 'active'
                GROUP BY sfh.id
                ORDER BY fh.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $term, $academicYear]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create fee head payments
     */
    public function createPayments($paymentId, $feeHeadPayments) {
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (payment_id, student_fee_head_id, amount) VALUES (?, ?, ?)");
            
            foreach ($feeHeadPayments as $studentFeeHeadId => $amount) {
                if ($amount > 0) {
                    $stmt->execute([$paymentId, $studentFeeHeadId, $amount]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating fee head payments: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Tuition vs Other Fee Heads collection breakdown
     */
    public function getTuitionVsOtherBreakdown($startDate = null, $endDate = null, $academicYear = null) {
        $billedWhere = "status = 'active'";
        $billedParams = [];
        
        if (!empty($academicYear)) {
            $billedWhere .= " AND academic_year = ?";
            $billedParams[] = $academicYear;
        }
        
        // 1. Get total billed per fee head
        $billedSql = "SELECT fee_head_id, COALESCE(SUM(amount), 0) as total_billed 
                      FROM student_fee_heads 
                      WHERE {$billedWhere} 
                      GROUP BY fee_head_id";
        $stmt = $this->db->prepare($billedSql);
        $stmt->execute($billedParams);
        $billedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $billedMap = [];
        foreach ($billedRows as $b) {
            $billedMap[$b['fee_head_id']] = floatval($b['total_billed']);
        }
        
        // 2. Get total collected per fee head
        $collectedWhere = "1=1";
        $collectedParams = [];
        
        if (!empty($academicYear)) {
            $collectedWhere .= " AND sfh.academic_year = ?";
            $collectedParams[] = $academicYear;
        }
        
        if (!empty($startDate) && !empty($endDate)) {
            $collectedWhere .= " AND p.payment_date BETWEEN ? AND ?";
            $collectedParams[] = $startDate;
            $collectedParams[] = $endDate;
        }
        
        $collectedSql = "SELECT sfh.fee_head_id, COALESCE(SUM(fhp.amount), 0) as total_collected 
                         FROM fee_head_payments fhp 
                         JOIN student_fee_heads sfh ON fhp.student_fee_head_id = sfh.id 
                         JOIN payments p ON fhp.payment_id = p.id 
                         WHERE {$collectedWhere} 
                         GROUP BY sfh.fee_head_id";
        $stmt = $this->db->prepare($collectedSql);
        $stmt->execute($collectedParams);
        $collectedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $collectedMap = [];
        foreach ($collectedRows as $c) {
            $collectedMap[$c['fee_head_id']] = floatval($c['total_collected']);
        }
        
        // 3. Fetch all fee heads
        $feeHeads = $this->db->query("SELECT id as fee_head_id, name as fee_head_name, code as fee_head_code FROM fee_heads ORDER BY (CASE WHEN code = 'TUITION' OR LOWER(name) LIKE '%tuition%' THEN 0 ELSE 1 END), name")->fetchAll(PDO::FETCH_ASSOC);
        
        $tuitionBilled = 0;
        $tuitionCollected = 0;
        $otherBilled = 0;
        $otherCollected = 0;
        
        foreach ($feeHeads as &$item) {
            $fhId = $item['fee_head_id'];
            $item['total_billed'] = $billedMap[$fhId] ?? 0;
            $item['total_collected'] = $collectedMap[$fhId] ?? 0;
            $item['balance'] = max(0, $item['total_billed'] - $item['total_collected']);
            $isTuition = ($item['fee_head_code'] === 'TUITION' || stripos($item['fee_head_name'], 'tuition') !== false);
            $item['is_tuition'] = $isTuition;
            
            if ($isTuition) {
                $tuitionBilled += $item['total_billed'];
                $tuitionCollected += $item['total_collected'];
            } else {
                $otherBilled += $item['total_billed'];
                $otherCollected += $item['total_collected'];
            }
        }

        // Fallback: If student_fee_heads is empty or not in use, aggregate directly from invoices & payments tables
        if (($tuitionBilled + $otherBilled == 0) && ($tuitionCollected + $otherCollected == 0)) {
            $invWhere = "1=1";
            $invParams = [];
            if (!empty($academicYear)) {
                $invWhere .= " AND academic_year = ?";
                $invParams[] = $academicYear;
            }

            $invStmt = $this->db->prepare("SELECT COALESCE(SUM(total_amount), 0) as total_billed, 
                                                 COALESCE(SUM(paid_amount), 0) as total_paid, 
                                                 COALESCE(SUM(balance), 0) as total_balance 
                                          FROM invoices WHERE {$invWhere}");
            $invStmt->execute($invParams);
            $invRow = $invStmt->fetch(PDO::FETCH_ASSOC);

            $invBilled = floatval($invRow['total_billed'] ?? 0);
            $invPaid = floatval($invRow['total_paid'] ?? 0);

            // Also sum actual payments table
            $payWhere = "1=1";
            $payParams = [];
            if (!empty($startDate) && !empty($endDate)) {
                $payWhere .= " AND payment_date BETWEEN ? AND ?";
                $payParams[] = $startDate;
                $payParams[] = $endDate;
            }
            $payStmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total_collected FROM payments WHERE {$payWhere}");
            $payStmt->execute($payParams);
            $actualCollected = floatval($payStmt->fetchColumn());

            $effectiveCollected = max($invPaid, $actualCollected);

            if ($invBilled > 0 || $effectiveCollected > 0) {
                // Check if invoice_items table has data
                $tuitionItemBilled = 0;
                $otherItemBilled = 0;
                try {
                    $allItemRows = $this->db->query("SELECT description, SUM(amount) as sum_amt FROM invoice_items GROUP BY description")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($allItemRows as $ir) {
                        $desc = strtolower($ir['description'] ?? '');
                        $amt = floatval($ir['sum_amt'] ?? 0);
                        if (strpos($desc, 'tuition') !== false) {
                            $tuitionItemBilled += $amt;
                        } else {
                            $otherItemBilled += $amt;
                        }
                    }
                } catch (Exception $e) {
                    $tuitionItemBilled = 0;
                    $otherItemBilled = 0;
                }

                $totItem = $tuitionItemBilled + $otherItemBilled;
                if ($totItem > 0) {
                    $tRatio = $tuitionItemBilled / $totItem;
                } else {
                    $tRatio = 0.85; // 85% Tuition default split
                }

                $tuitionBilled = $invBilled * $tRatio;
                $otherBilled = $invBilled * (1 - $tRatio);
                $tuitionCollected = $effectiveCollected * $tRatio;
                $otherCollected = $effectiveCollected * (1 - $tRatio);
            }
        }
        
        return [
            'tuition' => [
                'billed' => $tuitionBilled,
                'collected' => $tuitionCollected,
                'balance' => max(0, $tuitionBilled - $tuitionCollected)
            ],
            'other' => [
                'billed' => $otherBilled,
                'collected' => $otherCollected,
                'balance' => max(0, $otherBilled - $otherCollected)
            ],
            'total' => [
                'billed' => $tuitionBilled + $otherBilled,
                'collected' => $tuitionCollected + $otherCollected,
                'balance' => max(0, ($tuitionBilled + $otherBilled) - ($tuitionCollected + $otherCollected))
            ],
            'headBreakdown' => $feeHeads
        ];
    }
}
