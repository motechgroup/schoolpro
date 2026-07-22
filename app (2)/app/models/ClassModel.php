<?php
/**
 * Class Model
 * Handles class-related database operations
 */

class ClassModel extends Model {
    protected $table = 'classes';
    
    /**
     * Get class with grade and teacher information
     */
    public function getClassWithDetails($id) {
        $sql = "SELECT c.*, 
                       g.name as grade_name,
                       g.display_name as grade_display_name,
                       t.first_name as teacher_first_name,
                       t.last_name as teacher_last_name
                FROM classes c
                LEFT JOIN grades g ON c.grade_id = g.id
                LEFT JOIN teachers t ON c.class_teacher_id = t.id
                WHERE c.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all classes with details
     */
    public function getAllWithDetails($academicYear = null) {
        $sql = "SELECT c.*, 
                       g.name as grade_name,
                       g.display_name as grade_display_name,
                       t.first_name as teacher_first_name,
                       t.last_name as teacher_last_name,
                       COUNT(s.id) as student_count
                FROM classes c
                LEFT JOIN grades g ON c.grade_id = g.id
                LEFT JOIN teachers t ON c.class_teacher_id = t.id
                LEFT JOIN students s ON c.id = s.class_id AND s.status = 'active'
                WHERE 1=1";
        
        $params = [];
        
        if ($academicYear) {
            $sql .= " AND c.academic_year = ?";
            $params[] = $academicYear;
        }
        
        $sql .= " GROUP BY c.id ORDER BY g.level, c.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get classes by grade
     */
    public function getByGrade($gradeId, $academicYear = null) {
        $sql = "SELECT * FROM {$this->table} WHERE grade_id = ?";
        $params = [$gradeId];
        
        if ($academicYear) {
            $sql .= " AND academic_year = ?";
            $params[] = $academicYear;
        }
        
        $sql .= " AND status = 'active' ORDER BY name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

