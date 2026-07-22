<?php
/**
 * Fee Head Model
 */

class FeeHead extends Model {
    protected $table = 'fee_heads';
    
    /**
     * Get all active fee heads
     */
    public function getActive() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name");
        return $stmt->fetchAll();
    }
    
    /**
     * Get fee head with usage count
     */
    public function getWithUsageCount($id) {
        $sql = "SELECT f.*, 
                       COUNT(DISTINCT sfh.student_id) as student_count
                FROM fee_heads f
                LEFT JOIN student_fee_heads sfh ON f.id = sfh.fee_head_id AND sfh.status = 'active'
                WHERE f.id = ?
                GROUP BY f.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Query helper
     */
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

