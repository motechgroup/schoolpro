<?php
/**
 * Book Borrow Model
 * Handles book borrowing/assignment operations
 */

class BookBorrow extends Model {
    protected $table = 'book_borrows';
    
    /**
     * Get all borrows with book and student details
     */
    public function getAll($filters = []) {
        $sql = "SELECT bb.*,
                       b.title as book_title,
                       b.isbn as book_isbn,
                       b.author as book_author,
                       s.first_name as student_first_name,
                       s.last_name as student_last_name,
                       s.admission_number,
                       c.name as class_name,
                       g.display_name as grade_display_name,
                       u1.first_name as borrowed_by_first_name,
                       u1.last_name as borrowed_by_last_name,
                       u2.first_name as returned_to_first_name,
                       u2.last_name as returned_to_last_name,
                       COALESCE(bb.book_condition, 'good') as book_condition,
                       bb.condition_notes,
                       COALESCE(bb.points_awarded, 0) as points_awarded,
                       COALESCE(bb.points_deducted, 0) as points_deducted
                FROM {$this->table} bb
                LEFT JOIN books b ON bb.book_id = b.id
                LEFT JOIN students s ON bb.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                LEFT JOIN users u1 ON bb.borrowed_by = u1.id
                LEFT JOIN users u2 ON bb.returned_to = u2.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND bb.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['student_id'])) {
            $sql .= " AND bb.student_id = ?";
            $params[] = $filters['student_id'];
        }
        
        if (!empty($filters['book_id'])) {
            $sql .= " AND bb.book_id = ?";
            $params[] = $filters['book_id'];
        }
        
        if (isset($filters['overdue_only']) && $filters['overdue_only']) {
            $sql .= " AND bb.status = 'borrowed' AND bb.due_date < CURDATE()";
        }
        
        $sql .= " ORDER BY bb.borrow_date DESC, bb.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get borrow by ID
     */
    public function getById($id) {
        $sql = "SELECT bb.*,
                       b.title as book_title,
                       b.isbn as book_isbn,
                       b.author as book_author,
                       s.first_name as student_first_name,
                       s.last_name as student_last_name,
                       s.admission_number,
                       c.name as class_name,
                       g.display_name as grade_display_name,
                       u1.first_name as borrowed_by_first_name,
                       u1.last_name as borrowed_by_last_name,
                       u2.first_name as returned_to_first_name,
                       u2.last_name as returned_to_last_name,
                       COALESCE(bb.book_condition, 'good') as book_condition,
                       bb.condition_notes,
                       COALESCE(bb.points_awarded, 0) as points_awarded,
                       COALESCE(bb.points_deducted, 0) as points_deducted
                FROM {$this->table} bb
                LEFT JOIN books b ON bb.book_id = b.id
                LEFT JOIN students s ON bb.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                LEFT JOIN users u1 ON bb.borrowed_by = u1.id
                LEFT JOIN users u2 ON bb.returned_to = u2.id
                WHERE bb.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get active borrows for a student
     */
    public function getActiveBorrowsByStudent($studentId) {
        $sql = "SELECT bb.*,
                       b.title as book_title,
                       b.isbn as book_isbn,
                       b.author as book_author
                FROM {$this->table} bb
                LEFT JOIN books b ON bb.book_id = b.id
                WHERE bb.student_id = ? AND bb.status = 'borrowed'
                ORDER BY bb.due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get active borrows for a book
     */
    public function getActiveBorrowsByBook($bookId) {
        $sql = "SELECT bb.*,
                       s.first_name as student_first_name,
                       s.last_name as student_last_name,
                       s.admission_number,
                       c.name as class_name
                FROM {$this->table} bb
                LEFT JOIN students s ON bb.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE bb.book_id = ? AND bb.status = 'borrowed'
                ORDER BY bb.due_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bookId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if student has overdue books
     */
    public function hasOverdueBooks($studentId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE student_id = ? AND status = 'borrowed' AND due_date < CURDATE()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Update overdue status
     */
    public function updateOverdueStatus() {
        $sql = "UPDATE {$this->table} 
                SET status = 'overdue' 
                WHERE status = 'borrowed' AND due_date < CURDATE()";
        
        return $this->db->exec($sql);
    }
    
    /**
     * Calculate fine for overdue book
     */
    public function calculateFine($borrowId, $dailyFineRate = 10.00) {
        $borrow = $this->getById($borrowId);
        if (!$borrow || $borrow['status'] !== 'borrowed') {
            return 0;
        }
        
        $dueDate = new DateTime($borrow['due_date']);
        $today = new DateTime();
        
        if ($today <= $dueDate) {
            return 0;
        }
        
        $daysOverdue = $today->diff($dueDate)->days;
        return $daysOverdue * $dailyFineRate;
    }
}

