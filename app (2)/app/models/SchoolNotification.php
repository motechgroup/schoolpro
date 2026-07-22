<?php
/**
 * School Notification Model
 * For fetching CMS notifications for school systems
 */

class SchoolNotification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get notifications for a specific school
     */
    public function getBySchoolId($schoolId, $limit = 20, $unreadOnly = false) {
        if ($unreadOnly) {
            $sql = "SELECT * FROM cms_notifications 
                    WHERE ((recipient_type = 'school' AND recipient_id = ?) 
                       OR (recipient_type = 'all_schools'))
                    AND is_read = FALSE
                    ORDER BY created_at DESC LIMIT ?";
            $params = [$schoolId, $limit];
        } else {
            $sql = "SELECT * FROM cms_notifications 
                    WHERE ((recipient_type = 'school' AND recipient_id = ?) 
                       OR (recipient_type = 'all_schools'))
                    ORDER BY created_at DESC LIMIT ?";
            $params = [$schoolId, $limit];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get unread count for a school
     */
    public function getUnreadCount($schoolId) {
        $sql = "SELECT COUNT(*) as count FROM cms_notifications 
                WHERE ((recipient_type = 'school' AND recipient_id = ?) 
                   OR (recipient_type = 'all_schools'))
                AND is_read = FALSE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$schoolId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($id, $schoolId) {
        // Verify the notification belongs to this school
        $sql = "UPDATE cms_notifications 
                SET is_read = TRUE, read_at = CURRENT_TIMESTAMP 
                WHERE id = ? 
                AND ((recipient_type = 'school' AND recipient_id = ?) 
                     OR (recipient_type = 'all_schools'))";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id, $schoolId]);
    }
    
    /**
     * Mark all notifications as read for a school
     */
    public function markAllAsRead($schoolId) {
        $sql = "UPDATE cms_notifications 
                SET is_read = TRUE, read_at = CURRENT_TIMESTAMP 
                WHERE ((recipient_type = 'school' AND recipient_id = ?) 
                       OR (recipient_type = 'all_schools'))
                AND is_read = FALSE";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$schoolId]);
    }
}

