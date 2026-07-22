<?php
/**
 * Student Model
 * Handles student-related database operations
 */

class Student extends Model {
    protected $table = 'students';
    
    /**
     * Get student with parent and class information
     */
    public function getStudentWithDetails($id) {
        $sql = "SELECT s.*, 
                       p.first_name as parent_first_name, 
                       p.last_name as parent_last_name,
                       p.phone as parent_phone,
                       p.email as parent_email,
                       c.name as class_name,
                       g.name as grade_name,
                       g.display_name as grade_display_name
                FROM students s
                LEFT JOIN parents p ON s.parent_id = p.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                WHERE s.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all students with details
     */
    public function getAllWithDetails($filters = []) {
        // Get current academic year for fee calculations
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        
        // Calculate balance using subquery for better performance
        $sql = "SELECT s.*, 
                       p.first_name as parent_first_name, 
                       p.last_name as parent_last_name,
                       p.phone as parent_phone,
                       c.name as class_name,
                       g.display_name as grade_display_name,
                       COALESCE((
                           SELECT SUM(i.balance) 
                           FROM invoices i 
                           WHERE i.student_id = s.id 
                           AND i.academic_year = ? 
                           AND i.status IN ('pending', 'partial')
                           AND i.balance > 0
                       ), 0) as total_balance
                FROM students s
                LEFT JOIN parents p ON s.parent_id = p.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                WHERE 1=1";
        
        $params = [$currentYear];
        
        if (!empty($filters['status'])) {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['class_id'])) {
            $sql .= " AND s.class_id = ?";
            $params[] = $filters['class_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.admission_number LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Filter by fee balance - add to WHERE clause using subquery
        if (isset($filters['fee_status']) && !empty($filters['fee_status'])) {
            if ($filters['fee_status'] === 'with_balance') {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM invoices i 
                    WHERE i.student_id = s.id 
                    AND i.academic_year = ? 
                    AND i.status IN ('pending', 'partial')
                    AND i.balance > 0
                )";
                $params[] = $currentYear;
            } elseif ($filters['fee_status'] === 'no_balance') {
                $sql .= " AND NOT EXISTS (
                    SELECT 1 FROM invoices i 
                    WHERE i.student_id = s.id 
                    AND i.academic_year = ? 
                    AND i.status IN ('pending', 'partial')
                    AND i.balance > 0
                )";
                $params[] = $currentYear;
            } elseif ($filters['fee_status'] === 'fully_paid') {
                // Students with invoices that are all paid (have invoices but no balance)
                $sql .= " AND EXISTS (
                    SELECT 1 FROM invoices i2 
                    WHERE i2.student_id = s.id 
                    AND i2.academic_year = ? 
                    AND i2.status = 'paid'
                ) AND NOT EXISTS (
                    SELECT 1 FROM invoices i3 
                    WHERE i3.student_id = s.id 
                    AND i3.academic_year = ? 
                    AND i3.status IN ('pending', 'partial')
                    AND i3.balance > 0
                )";
                $params[] = $currentYear;
                $params[] = $currentYear;
            }
        }
        
        $sql .= " ORDER BY s.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if admission number exists
     */
    public function admissionNumberExists($admissionNumber, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE admission_number = ?";
        $params = [$admissionNumber];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Get students by class
     */
    public function getByClass($classId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE class_id = ? AND status = 'active' ORDER BY first_name, last_name");
        $stmt->execute([$classId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get students by parent
     */
    public function getByParent($parentId) {
        $stmt = $this->db->prepare("SELECT s.*, c.name as class_name, g.display_name as grade_display_name 
                                    FROM {$this->table} s
                                    LEFT JOIN classes c ON s.class_id = c.id
                                    LEFT JOIN grades g ON c.grade_id = g.id
                                    WHERE s.parent_id = ? AND s.status = 'active' 
                                    ORDER BY s.first_name, s.last_name");
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }
}

