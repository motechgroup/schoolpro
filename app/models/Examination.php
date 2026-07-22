<?php
/**
 * Examination Model
 * Handles examination-related database operations
 */

class Examination extends Model {
    protected $table = 'examinations';
    
    /**
     * Get examination with class and creator details
     */
    public function getExaminationWithDetails($id) {
        $sql = "SELECT e.*, 
                       c.name as class_name,
                       g.display_name as grade_display_name,
                       u.first_name as creator_first_name,
                       u.last_name as creator_last_name
                FROM examinations e
                LEFT JOIN classes c ON e.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                LEFT JOIN users u ON e.created_by = u.id
                WHERE e.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all examinations with details
     */
    public function getAllWithDetails($filters = []) {
        $sql = "SELECT e.*, 
                       c.name as class_name,
                       g.display_name as grade_display_name,
                       u.first_name as creator_first_name,
                       u.last_name as creator_last_name,
                       COUNT(DISTINCT es.id) as subject_count,
                       COUNT(DISTINCT s.id) as student_count
                FROM examinations e
                LEFT JOIN classes c ON e.class_id = c.id
                LEFT JOIN grades g ON c.grade_id = g.id
                LEFT JOIN users u ON e.created_by = u.id
                LEFT JOIN examination_subjects es ON e.id = es.examination_id
                LEFT JOIN students s ON c.id = s.class_id AND s.status = 'active'
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['class_id'])) {
            $sql .= " AND e.class_id = ?";
            $params[] = $filters['class_id'];
        }
        
        if (!empty($filters['term'])) {
            $sql .= " AND e.term = ?";
            $params[] = $filters['term'];
        }
        
        if (!empty($filters['academic_year'])) {
            $sql .= " AND e.academic_year = ?";
            $params[] = $filters['academic_year'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " GROUP BY e.id ORDER BY e.academic_year DESC, e.term DESC, e.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get examination subjects
     */
    public function getExaminationSubjects($examinationId) {
        $sql = "SELECT es.*, 
                       la.name as learning_area_name,
                       la.code as learning_area_code,
                       t.first_name as teacher_first_name,
                       t.last_name as teacher_last_name
                FROM examination_subjects es
                LEFT JOIN learning_areas la ON es.learning_area_id = la.id
                LEFT JOIN teachers t ON es.teacher_id = t.id
                WHERE es.examination_id = ?
                ORDER BY la.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$examinationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get marks for a student in an examination
     */
    public function getStudentMarks($examinationId, $studentId) {
        $sql = "SELECT em.*, 
                       es.learning_area_id,
                       es.max_marks,
                       la.name as learning_area_name,
                       la.code as learning_area_code
                FROM examination_marks em
                LEFT JOIN examination_subjects es ON em.examination_subject_id = es.id
                LEFT JOIN learning_areas la ON es.learning_area_id = la.id
                WHERE em.examination_id = ? AND em.student_id = ?
                ORDER BY la.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$examinationId, $studentId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all marks for an examination
     */
    public function getAllMarks($examinationId) {
        $sql = "SELECT em.*, 
                       s.first_name as student_first_name,
                       s.last_name as student_last_name,
                       s.admission_number,
                       es.learning_area_id,
                       es.max_marks,
                       la.name as learning_area_name,
                       la.code as learning_area_code
                FROM examination_marks em
                LEFT JOIN students s ON em.student_id = s.id
                LEFT JOIN examination_subjects es ON em.examination_subject_id = es.id
                LEFT JOIN learning_areas la ON es.learning_area_id = la.id
                WHERE em.examination_id = ?
                ORDER BY s.admission_number, la.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$examinationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Calculate grade based on marks
     */
    public function calculateGrade($marks, $maxMarks = 100) {
        if ($maxMarks <= 0) return 'N/A';
        
        $percentage = ($marks / $maxMarks) * 100;
        
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 60) return 'C';
        if ($percentage >= 50) return 'D';
        if ($percentage >= 40) return 'E';
        return 'F';
    }
    
    /**
     * Get class students for an examination
     */
    public function getExaminationStudents($examinationId) {
        $sql = "SELECT s.*, c.name as class_name
                FROM students s
                LEFT JOIN examinations e ON s.class_id = e.class_id
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE e.id = ? AND s.status = 'active'
                ORDER BY s.admission_number";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$examinationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get marks entry progress for an examination
     */
    public function getMarksEntryProgress($examinationId) {
        $sql = "SELECT 
                    es.id as subject_id,
                    la.name as subject_name,
                    COUNT(DISTINCT s.id) as total_students,
                    COUNT(DISTINCT em.student_id) as marks_entered
                FROM examination_subjects es
                LEFT JOIN learning_areas la ON es.learning_area_id = la.id
                LEFT JOIN examinations e ON es.examination_id = e.id
                LEFT JOIN students s ON e.class_id = s.class_id AND s.status = 'active'
                LEFT JOIN examination_marks em ON es.id = em.examination_subject_id AND s.id = em.student_id
                WHERE es.examination_id = ?
                GROUP BY es.id, la.name
                ORDER BY la.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$examinationId]);
        return $stmt->fetchAll();
    }
}

