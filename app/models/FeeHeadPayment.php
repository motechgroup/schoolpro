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
        $params = [];
        $whereClauses = ["sfh.status = 'active'"];
        
        if (!empty($academicYear)) {
            $whereClauses[] = "sfh.academic_year = ?";
            $params[] = $academicYear;
        }
        
        $whereSql = implode(" AND ", $whereClauses);
        
        // Fee head breakdown query
        $sql = "SELECT 
                    fh.id as fee_head_id,
                    fh.name as fee_head_name,
                    fh.code as fee_head_code,
                    COALESCE(SUM(sfh.amount), 0) as total_billed,
                    COALESCE(SUM(fhp.amount), 0) as total_collected
                FROM fee_heads fh
                LEFT JOIN student_fee_heads sfh ON fh.id = sfh.fee_head_id AND {$whereSql}
                LEFT JOIN fee_head_payments fhp ON sfh.id = fhp.student_fee_head_id
                GROUP BY fh.id, fh.name, fh.code
                ORDER BY (CASE WHEN fh.code = 'TUITION' OR LOWER(fh.name) LIKE '%tuition%' THEN 0 ELSE 1 END), fh.name";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $headBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $tuitionBilled = 0;
        $tuitionCollected = 0;
        $otherBilled = 0;
        $otherCollected = 0;
        
        foreach ($headBreakdown as &$item) {
            $item['total_billed'] = floatval($item['total_billed'] ?? 0);
            $item['total_collected'] = floatval($item['total_collected'] ?? 0);
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
        
        // Check direct payments to handle payments before fee head breakdown was assigned
        $dateWhere = "";
        $dateParams = [];
        if (!empty($startDate) && !empty($endDate)) {
            $dateWhere = " WHERE p.payment_date BETWEEN ? AND ?";
            $dateParams = [$startDate, $endDate];
        }
        
        $totalDirectPaidStmt = $this->db->prepare("SELECT COALESCE(SUM(p.amount), 0) as total_direct FROM payments p {$dateWhere}");
        $totalDirectPaidStmt->execute($dateParams);
        $totalDirectPaid = floatval($totalDirectPaidStmt->fetch()['total_direct'] ?? 0);
        
        $allocatedPaidStmt = $this->db->prepare("SELECT COALESCE(SUM(fhp.amount), 0) as total_allocated 
                                           FROM fee_head_payments fhp 
                                           JOIN payments p ON fhp.payment_id = p.id {$dateWhere}");
        $allocatedPaidStmt->execute($dateParams);
        $totalAllocatedPaid = floatval($allocatedPaidStmt->fetch()['total_allocated'] ?? 0);
        
        $unallocatedPaid = max(0, $totalDirectPaid - $totalAllocatedPaid);
        if ($unallocatedPaid > 0) {
            $tuitionCollected += $unallocatedPaid;
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
            'headBreakdown' => $headBreakdown
        ];
    }
}

