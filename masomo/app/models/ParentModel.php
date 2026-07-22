<?php
/**
 * Parent Model
 * Handles parent/guardian-related database operations
 */

class ParentModel extends Model {
    protected $table = 'parents';
    
    /**
     * Get parent with students and fee information
     */
    public function getParentWithDetails($id) {
        $sql = "SELECT p.*, 
                       COUNT(DISTINCT s.id) as children_count,
                       SUM(CASE WHEN i.balance > 0 THEN i.balance ELSE 0 END) as total_balance
                FROM parents p
                LEFT JOIN students s ON p.id = s.parent_id AND s.status = 'active'
                LEFT JOIN invoices i ON s.id = i.student_id AND i.status IN ('pending', 'partial')
                WHERE p.id = ?
                GROUP BY p.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get parent with students
     */
    public function getParentWithStudents($id) {
        $sql = "SELECT p.*, 
                       COUNT(s.id) as children_count
                FROM parents p
                LEFT JOIN students s ON p.id = s.parent_id AND s.status = 'active'
                WHERE p.id = ?
                GROUP BY p.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all parents with details and filters
     */
    public function getAllWithDetails($filters = []) {
        $sql = "SELECT p.*, 
                       COUNT(DISTINCT s.id) as children_count,
                       SUM(CASE WHEN i.balance > 0 THEN i.balance ELSE 0 END) as total_balance
                FROM parents p
                LEFT JOIN students s ON p.id = s.parent_id AND s.status = 'active'
                LEFT JOIN invoices i ON s.id = i.student_id AND i.status IN ('pending', 'partial')
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (p.first_name LIKE ? OR p.last_name LIKE ? OR p.phone LIKE ? OR p.email LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['has_balance']) && $filters['has_balance'] == '1') {
            $sql .= " HAVING total_balance > 0";
        }
        
        $sql .= " GROUP BY p.id ORDER BY p.first_name, p.last_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Search parents
     */
    public function search($term) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ?)
                AND status = 'active'
                ORDER BY first_name, last_name";
        
        $searchTerm = "%$term%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}

