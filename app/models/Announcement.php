<?php
/**
 * Announcement Model
 */

class Announcement extends Model {
    protected $table = 'announcements';
    
    /**
     * Get published announcements
     */
    public function getPublished($targetAudience = null, $limit = null) {
        $sql = "SELECT a.*, 
                       u.first_name as created_by_first_name,
                       u.last_name as created_by_last_name
                FROM announcements a
                LEFT JOIN users u ON a.created_by = u.id
                WHERE a.status = 'published'";
        
        $params = [];
        
        if ($targetAudience) {
            $sql .= " AND (a.target_audience = ? OR a.target_audience = 'all')";
            $params[] = $targetAudience;
        }
        
        $sql .= " ORDER BY a.published_at DESC, a.priority DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get announcements for user based on role
     */
    public function getForUser($userId, $userRole, $limit = 10) {
        // Map roles to target audience
        $audienceMap = [
            'super_admin' => 'all',
            'school_admin' => 'all',
            'head_teacher' => 'teachers',
            'teacher' => 'teachers',
            'bursar' => 'staff',
            'parent' => 'parents',
            'student' => 'students'
        ];
        
        $targetAudience = $audienceMap[strtolower($userRole)] ?? 'all';
        
        return $this->getPublished($targetAudience, $limit);
    }
}

