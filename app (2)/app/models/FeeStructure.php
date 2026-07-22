<?php
/**
 * Fee Structure Model
 */

class FeeStructure extends Model {
    protected $table = 'fee_structure';
    
    /**
     * Get fee structure by grade and term
     */
    public function getByGradeAndTerm($gradeId, $term, $academicYear) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} 
                                    WHERE grade_id = ? AND term = ? AND academic_year = ? AND status = 'active'");
        $stmt->execute([$gradeId, $term, $academicYear]);
        return $stmt->fetchAll();
    }
    
    /**
     * Calculate total fees for grade and term
     */
    public function getTotalFees($gradeId, $term, $academicYear) {
        $stmt = $this->db->prepare("SELECT SUM(amount) as total FROM {$this->table} 
                                    WHERE grade_id = ? AND term = ? AND academic_year = ? AND status = 'active'");
        $stmt->execute([$gradeId, $term, $academicYear]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}

