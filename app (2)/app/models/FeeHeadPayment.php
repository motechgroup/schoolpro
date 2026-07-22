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
}

