<?php
/**
 * Notification Controller
 * Handles notification fetching and marking as read
 */

class NotificationController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
    }
    
    /**
     * Get notifications (AJAX)
     */
    public function getNotifications() {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) {
            $this->json(['notifications' => [], 'unread_count' => 0]);
            return;
        }
        
        $notificationModel = new SchoolNotification();
        $notifications = $notificationModel->getBySchoolId($schoolId, 20);
        $unreadCount = $notificationModel->getUnreadCount($schoolId);
        
        $this->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }
    
    /**
     * Mark notification as read (AJAX)
     */
    public function markAsRead($id) {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) {
            $this->json(['success' => false, 'message' => 'School not found'], 400);
            return;
        }
        
        $notificationModel = new SchoolNotification();
        if ($notificationModel->markAsRead($id, $schoolId)) {
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to mark as read'], 500);
        }
    }
    
    /**
     * Mark all notifications as read (AJAX)
     */
    public function markAllAsRead() {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) {
            $this->json(['success' => false, 'message' => 'School not found'], 400);
            return;
        }
        
        $notificationModel = new SchoolNotification();
        if ($notificationModel->markAllAsRead($schoolId)) {
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to mark all as read'], 500);
        }
    }
    
    /**
     * Get school ID from subdomain or config
     */
    private function getSchoolId() {
        if (defined('SCHOOL_ID')) {
            return SCHOOL_ID;
        }
        
        require_once APP_PATH . '/helpers/SchoolStatusHelper.php';
        $subdomain = SchoolStatusHelper::getSubdomain();
        
        if ($subdomain) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id FROM cms_schools WHERE subdomain = ? LIMIT 1");
            $stmt->execute([$subdomain]);
            $school = $stmt->fetch();
            if ($school) {
                return $school['id'];
            }
        }
        
        return null;
    }
}

