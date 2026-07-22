<?php
/**
 * Learning Area Model (Subjects)
 * Handles learning area/subject-related database operations
 */

class LearningArea extends Model {
    protected $table = 'learning_areas';
    
    /**
     * Get learning area with grade details
     */
    public function getLearningAreaWithDetails($id) {
        $sql = "SELECT la.*, 
                       g.name as grade_name,
                       g.display_name as grade_display_name
                FROM learning_areas la
                LEFT JOIN grades g ON la.grade_id = g.id
                WHERE la.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all learning areas with grade details
     */
    public function getAllWithDetails($filters = []) {
        $sql = "SELECT la.*, 
                       g.name as grade_name,
                       g.display_name as grade_display_name
                FROM learning_areas la
                LEFT JOIN grades g ON la.grade_id = g.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['grade_id'])) {
            $sql .= " AND la.grade_id = ?";
            $params[] = $filters['grade_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (la.name LIKE ? OR la.code LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY g.level ASC, la.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get learning areas by grade
     */
    public function getByGrade($gradeId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE grade_id = ? ORDER BY name");
        $stmt->execute([$gradeId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if code exists
     */
    public function codeExists($code, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE code = ?";
        $params = [$code];
        
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

