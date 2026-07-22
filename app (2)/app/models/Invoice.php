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
}

