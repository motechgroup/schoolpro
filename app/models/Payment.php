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
     * Get payment by receipt number
     */
    public function getByReceiptNumber($receiptNumber) {
        $sql = "SELECT p.*, 
                       s.first_name as student_first_name,
                       s.last_name as student_last_name,
                       s.middle_name as student_middle_name,
                       s.admission_number,
                       s.class_id,
                       c.name as class_name,
                       i.invoice_number,
                       i.total_amount as invoice_total,
                       i.balance as invoice_balance,
                       i.paid_amount as invoice_paid,
                       u.first_name as received_by_first_name,
                       u.last_name as received_by_last_name
                FROM payments p
                LEFT JOIN students s ON p.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN invoices i ON p.invoice_id = i.id
                LEFT JOIN users u ON p.received_by = u.id
                WHERE p.receipt_number = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$receiptNumber]);
        $payment = $stmt->fetch();
        
        // If invoice balance is not current, recalculate it
        if ($payment && !empty($payment['invoice_id'])) {
            require_once APP_PATH . '/models/Invoice.php';
            $invoiceModel = new Invoice();
            $invoiceModel->updateBalance($payment['invoice_id']);
            
            // Fetch updated invoice balance
            $invoiceStmt = $this->db->prepare("SELECT balance, total_amount, paid_amount FROM invoices WHERE id = ?");
            $invoiceStmt->execute([$payment['invoice_id']]);
            $invoice = $invoiceStmt->fetch();
            
            if ($invoice) {
                $payment['invoice_balance'] = $invoice['balance'];
                $payment['invoice_total'] = $invoice['total_amount'];
                $payment['invoice_paid'] = $invoice['paid_amount'];
            }
        }
        
        return $payment;
    }
    
    /**
     * Get all payments with details
     */
    public function getAllWithDetails($filters = []) {
        $sql = "SELECT p.*, 
                       s.first_name as student_first_name,
                       s.last_name as student_last_name,
                       s.admission_number,
                       u.first_name as received_by_first_name,
                       u.last_name as received_by_last_name
                FROM payments p
                LEFT JOIN students s ON p.student_id = s.id
                LEFT JOIN users u ON p.received_by = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['student_id'])) {
            $sql .= " AND p.student_id = ?";
            $params[] = $filters['student_id'];
        }
        
        if (!empty($filters['receipt_number'])) {
            $sql .= " AND p.receipt_number LIKE ?";
            $params[] = '%' . $filters['receipt_number'] . '%';
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND p.payment_date >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND p.payment_date <= ?";
            $params[] = $filters['end_date'];
        }
        
        $sql .= " ORDER BY p.payment_date DESC, p.id DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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

