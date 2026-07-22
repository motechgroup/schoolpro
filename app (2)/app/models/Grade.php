<?php
/**
 * Grade Model
 * Handles grade-related database operations
 */

class Grade extends Model {
    protected $table = 'grades';
    
    /**
     * Get all grades ordered by level
     */
    public function getAllOrdered() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY level ASC");
        return $stmt->fetchAll();
    }
}

