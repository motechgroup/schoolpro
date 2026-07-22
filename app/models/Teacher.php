<?php
/**
 * Teacher Model
 */

class Teacher extends Model {
    protected $table = 'teachers';
    
    /**
     * Get teacher with user details
     */
    public function getTeacherWithDetails($id) {
        $sql = "SELECT t.*, 
                       u.email,
                       u.status as user_status,
                       r.name as role_name
                FROM teachers t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE t.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all active teachers
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY first_name, last_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all teachers with details
     */
    public function getAllWithDetails() {
        $sql = "SELECT t.*, 
                       u.email,
                       r.name as role_name
                FROM teachers t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE t.status = 'active'
                ORDER BY t.first_name, t.last_name";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get classes assigned to teacher
     */
    public function getAssignedClasses($teacherId) {
        $stmt = $this->db->prepare("SELECT c.*, g.display_name as grade_display_name 
                                    FROM classes c
                                    LEFT JOIN grades g ON c.grade_id = g.id
                                    WHERE c.class_teacher_id = ? AND c.status = 'active'");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Find teacher by user_id
     */
    public function findByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM teachers WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}

