<?php
/**
 * Assessment Model
 * Handles CBC assessments
 */

class Assessment extends Model {
    protected $table = 'assessments';
    
    /**
     * Get assessments for student
     */
    public function getStudentAssessments($studentId, $term = null, $academicYear = null) {
        $sql = "SELECT a.*, 
                       la.name as learning_area_name,
                       la.code as learning_area_code,
                       s.name as strand_name,
                       ss.name as sub_strand_name
                FROM assessments a
                LEFT JOIN learning_areas la ON a.learning_area_id = la.id
                LEFT JOIN strands s ON a.strand_id = s.id
                LEFT JOIN sub_strands ss ON a.sub_strand_id = ss.id
                WHERE a.student_id = ?";
        
        $params = [$studentId];
        
        if ($term) {
            $sql .= " AND a.term = ?";
            $params[] = $term;
        }
        
        if ($academicYear) {
            $sql .= " AND a.academic_year = ?";
            $params[] = $academicYear;
        }
        
        $sql .= " ORDER BY a.assessed_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get assessment summary by learning area
     */
    public function getAssessmentSummary($studentId, $term, $academicYear) {
        $sql = "SELECT 
                    la.name as learning_area_name,
                    la.code as learning_area_code,
                    COUNT(*) as total_assessments,
                    AVG(a.score) as average_score,
                    MAX(a.level) as highest_level
                FROM assessments a
                LEFT JOIN learning_areas la ON a.learning_area_id = la.id
                WHERE a.student_id = ? AND a.term = ? AND a.academic_year = ?
                GROUP BY la.id, la.name, la.code
                ORDER BY la.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $term, $academicYear]);
        return $stmt->fetchAll();
    }
}

