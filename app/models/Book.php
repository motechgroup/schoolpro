<?php
/**
 * Book Model
 * Handles book-related database operations
 */

class Book extends Model {
    protected $table = 'books';
    
    /**
     * Get all books with availability status
     */
    public function getAll($filters = []) {
        $sql = "SELECT b.*,
                       la.name as subject_name,
                       la.code as subject_code,
                       c.name as class_name,
                       g.display_name as grade_display_name,
                       (SELECT COUNT(*) FROM book_borrows bb 
                        WHERE bb.book_id = b.id AND bb.status = 'borrowed') as borrowed_count
                FROM {$this->table} b
                LEFT JOIN learning_areas la ON b.subject_id = la.id
                LEFT JOIN classes c ON b.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['subject_id'])) {
            $sql .= " AND b.subject_id = ?";
            $params[] = $filters['subject_id'];
        }
        
        if (!empty($filters['class_id'])) {
            $sql .= " AND b.class_id = ?";
            $params[] = $filters['class_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['available_only']) && $filters['available_only']) {
            $sql .= " AND b.available_copies > 0 AND b.status = 'active'";
        }
        
        // Filter by student's class if provided
        if (!empty($filters['student_class_id'])) {
            $sql .= " AND (b.class_id = ? OR b.class_id IS NULL)";
            $params[] = $filters['student_class_id'];
        }
        
        $sql .= " ORDER BY b.title ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get book by ID with details
     */
    public function getById($id) {
        $sql = "SELECT b.*,
                       la.name as subject_name,
                       la.code as subject_code,
                       c.name as class_name,
                       g.display_name as grade_display_name,
                       (SELECT COUNT(*) FROM book_borrows bb 
                        WHERE bb.book_id = b.id AND bb.status = 'borrowed') as borrowed_count
                FROM {$this->table} b
                LEFT JOIN learning_areas la ON b.subject_id = la.id
                LEFT JOIN classes c ON b.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                WHERE b.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get available copies count
     */
    public function getAvailableCopies($bookId) {
        $book = $this->getById($bookId);
        if (!$book) {
            return 0;
        }
        
        $borrowed = intval($book['borrowed_count'] ?? 0);
        $total = intval($book['total_copies'] ?? 0);
        
        return max(0, $total - $borrowed);
    }
    
    /**
     * Update available copies
     */
    public function updateAvailableCopies($bookId) {
        $book = $this->getById($bookId);
        if (!$book) {
            return false;
        }
        
        $borrowed = intval($book['borrowed_count'] ?? 0);
        $total = intval($book['total_copies'] ?? 0);
        $available = max(0, $total - $borrowed);
        
        return $this->update($bookId, ['available_copies' => $available]);
    }
    
    /**
     * Get books by class
     */
    public function getByClass($classId) {
        return $this->getAll(['class_id' => $classId]);
    }
    
    /**
     * Get books by subject
     */
    public function getBySubject($subjectId) {
        return $this->getAll(['subject_id' => $subjectId]);
    }
    
    /**
     * Check if ISBN exists
     */
    public function isbnExists($isbn, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE isbn = ?";
        $params = [$isbn];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}

