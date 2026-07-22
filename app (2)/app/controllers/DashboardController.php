<?php
/**
 * Dashboard Controller
 * Main dashboard for authenticated users
 */

class DashboardController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
    }
    
    /**
     * Main dashboard
     */
    public function index() {
        $user = Auth::user();
        $role = strtolower($user['role_name']);
        
        // Initialize stats as empty array to prevent errors
        $stats = [];
        
        // Get recent announcements
        try {
            $announcementModel = $this->model('Announcement');
            $recentAnnouncements = $announcementModel->getForUser($user['id'], $user['role_name'], 5);
        } catch (Exception $e) {
            $recentAnnouncements = [];
            error_log("Announcements error: " . $e->getMessage());
        }
        
        // Get recent system logs for super_admin and school_manager
        $recentLogs = [];
        if (in_array($role, ['super_admin', 'school_manager'])) {
            try {
                $recentLogs = $this->getRecentSystemLogs();
            } catch (Exception $e) {
                error_log("Recent logs error: " . $e->getMessage());
            }
        }
        
        // Get dashboard data based on role
        try {
            $stats = $this->getDashboardStats($role);
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            $stats = [];
        }
        
        $data = [
            'title' => 'Dashboard - ' . APP_NAME,
            'user' => $user,
            'stats' => $stats,
            'announcements' => $recentAnnouncements ?? [],
            'recentLogs' => $recentLogs
        ];
        
        $this->view('dashboard/index', $data);
    }
    
    /**
     * Get dashboard statistics based on role
     */
    private function getDashboardStats($role) {
        $stats = [];
        
        try {
            $db = Database::getInstance()->getConnection();
            
            switch ($role) {
            case 'super_admin':
            case 'school_admin':
            case 'school_manager':
                // Basic counts
                $stats['total_students'] = $this->getCount('students', ['status' => 'active']);
                $stats['total_teachers'] = $this->getCount('teachers', ['status' => 'active']);
                $stats['total_classes'] = $this->getCount('classes', ['status' => 'active']);
                $stats['total_parents'] = $this->getCount('parents', ['status' => 'active']);
                
                // Additional detailed stats
                $stats['new_students_this_month'] = $this->getNewStudentsThisMonth();
                $stats['total_alumni'] = $this->getCount('students', ['status' => 'alumni']);
                $stats['pending_fees'] = $this->getPendingFeesCount();
                $stats['total_fees_collected'] = $this->getTotalFeesCollected();
                $stats['attendance_today'] = $this->getTodayAttendanceCount();
                $stats['recent_assessments'] = $this->getRecentAssessmentsCount();
                $stats['active_announcements'] = $this->getActiveAnnouncementsCount();
                $stats['students_by_gender'] = $this->getStudentsByGender();
                $stats['students_by_status'] = $this->getStudentsByStatus();
                $stats['revenue_this_month'] = $this->getRevenueThisMonth();
                $stats['revenue_this_year'] = $this->getRevenueThisYear();
                
                // Super admin only: User management stats
                if ($role === 'super_admin') {
                    $userModel = $this->model('User');
                    $stats['users_by_role'] = $userModel->getCountByRole();
                    $stats['total_users'] = $this->getCount('users', ['status' => 'active']);
                }
                break;
                
            case 'head_teacher':
            case 'teacher':
                $stats['total_students'] = $this->getCount('students', ['status' => 'active']);
                $stats['total_classes'] = $this->getCount('classes', ['status' => 'active']);
                $stats['attendance_today'] = $this->getTodayAttendanceCount();
                $stats['recent_assessments'] = $this->getRecentAssessmentsCount();
                break;
                
            case 'accountant':
            case 'bursar':
                $stats['pending_invoices'] = $this->getCount('invoices', ['status' => 'pending']);
                $stats['total_revenue'] = $this->getTotalRevenue();
                $stats['revenue_this_month'] = $this->getRevenueThisMonth();
                $stats['revenue_this_year'] = $this->getRevenueThisYear();
                $stats['partial_payments'] = $this->getCount('invoices', ['status' => 'partial']);
                $stats['pending_fees'] = $this->getPendingFeesCount();
                $stats['total_fees_collected'] = $this->getTotalFeesCollected();
                break;
                
            case 'receptionist':
                $stats['total_students'] = $this->getCount('students', ['status' => 'active']);
                $stats['total_parents'] = $this->getCount('parents', ['status' => 'active']);
                $stats['new_students_this_month'] = $this->getNewStudentsThisMonth();
                $stats['attendance_today'] = $this->getTodayAttendanceCount();
                break;
                
            case 'parent':
                $stats['children'] = $this->getParentChildrenCount();
                $stats['pending_invoices'] = $this->getParentPendingInvoices();
                break;
            }
        } catch (Exception $e) {
            // If database queries fail, return empty stats array
            // This ensures the dashboard still displays
            error_log("Dashboard stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get count from table
     */
    private function getCount($table, $conditions = []) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as count FROM $table";
        
        if (!empty($conditions)) {
            $where = [];
            $params = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params ?? []);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get total revenue
     */
    private function getTotalRevenue() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT SUM(paid_amount) as total FROM invoices WHERE status IN ('paid', 'partial')");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Get parent's children count
     */
    private function getParentChildrenCount() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM students WHERE parent_id = ? AND status = 'active'");
        $stmt->execute([Auth::userId()]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get new students added this month
     */
    private function getNewStudentsThisMonth() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) as count FROM students 
                           WHERE status = 'active' 
                           AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                           AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get pending fees count
     */
    private function getPendingFeesCount() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(DISTINCT student_id) as count FROM invoices 
                           WHERE status IN ('pending', 'partial') AND balance > 0");
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get total fees collected
     */
    private function getTotalFeesCollected() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT SUM(paid_amount) as total FROM invoices 
                           WHERE status IN ('paid', 'partial')");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Get today's attendance count
     */
    private function getTodayAttendanceCount() {
        $db = Database::getInstance()->getConnection();
        $today = date('Y-m-d');
        $stmt = $db->prepare("SELECT COUNT(DISTINCT student_id) as count FROM student_attendance 
                             WHERE attendance_date = ? AND status = 'present'");
        $stmt->execute([$today]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get recent assessments count (last 7 days)
     */
    private function getRecentAssessmentsCount() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) as count FROM assessments 
                           WHERE assessed_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)");
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get active announcements count
     */
    private function getActiveAnnouncementsCount() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) as count FROM announcements 
                           WHERE status = 'published'");
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get students by gender
     */
    private function getStudentsByGender() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT gender, COUNT(*) as count FROM students 
                           WHERE status = 'active' 
                           GROUP BY gender");
        $results = $stmt->fetchAll();
        $genderStats = ['male' => 0, 'female' => 0];
        foreach ($results as $row) {
            $genderStats[$row['gender']] = $row['count'];
        }
        return $genderStats;
    }
    
    /**
     * Get students by status
     */
    private function getStudentsByStatus() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT status, COUNT(*) as count FROM students 
                           GROUP BY status");
        $results = $stmt->fetchAll();
        $statusStats = [];
        foreach ($results as $row) {
            $statusStats[$row['status']] = $row['count'];
        }
        return $statusStats;
    }
    
    /**
     * Get revenue for this month
     */
    private function getRevenueThisMonth() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT SUM(paid_amount) as total FROM invoices 
                           WHERE status IN ('paid', 'partial') 
                           AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                           AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Get revenue for this year
     */
    private function getRevenueThisYear() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT SUM(paid_amount) as total FROM invoices 
                           WHERE status IN ('paid', 'partial') 
                           AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Get parent's pending invoices
     */
    private function getParentPendingInvoices() {
        $db = Database::getInstance()->getConnection();
        $userId = Auth::userId();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM invoices i
                             INNER JOIN students s ON i.student_id = s.id
                             WHERE s.parent_id = ? AND i.status IN ('pending', 'partial') AND i.balance > 0");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get recent system logs
     */
    private function getRecentSystemLogs() {
        $db = Database::getInstance()->getConnection();
        $schoolId = $this->getSchoolId();
        
        $sql = "SELECT l.*, u.first_name, u.last_name 
                FROM school_system_logs l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE 1=1";
        $params = [];
        
        if ($schoolId) {
            $sql .= " AND l.school_id = ?";
            $params[] = $schoolId;
        }
        
        $sql .= " ORDER BY l.created_at DESC LIMIT 5";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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

