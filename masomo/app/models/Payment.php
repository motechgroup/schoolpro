<?php
/**
 * Payment Model
 */

class Payment extends Model {
    protected $table = 'payments';
    
    /**
     * Get payment with details
     */
    public function getPaymentWithDetails($id) {
        $sql = "SELECT p.*, 
                       s.first_name as student_first_name,
                       s.last_name as student_last_name,
                       s.admission_number,
                       u.first_name as received_by_first_name,
                       u.last_name as received_by_last_name
                FROM payments p
                LEFT JOIN students s ON p.student_id = s.id
                LEFT JOIN users u ON p.received_by = u.id
                WHERE p.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get payments by student
     */
    public function getByStudent($studentId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE student_id = ? ORDER BY payment_date DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Generate receipt number
     */
    public function generateReceiptNumber() {
        $year = date('Y');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return 'RCP-' . $year . '-' . $random;
    }
}

