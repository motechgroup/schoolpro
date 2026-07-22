<?php
/**
 * User Model
 */

class User extends Model {
    protected $table = 'users';
    
    /**
     * Get user with role details
     */
    public function getUserWithRole($id) {
        $sql = "SELECT u.*, r.name as role_name, r.description as role_description
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all users with role details
     */
    public function getAllWithRoles($filters = []) {
        $sql = "SELECT u.*, r.name as role_name, r.description as role_description
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['role'])) {
            $sql .= " AND r.name = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Get users by role
     */
    public function getByRole($roleName) {
        $sql = "SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE r.name = ? AND u.status = 'active'
                ORDER BY u.first_name, u.last_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleName]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get user count by role
     */
    public function getCountByRole() {
        $sql = "SELECT r.name as role_name, COUNT(u.id) as count
                FROM roles r
                LEFT JOIN users u ON r.id = u.role_id AND u.status = 'active'
                GROUP BY r.id, r.name
                ORDER BY r.name";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}

