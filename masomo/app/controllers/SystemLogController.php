<?php
/**
 * System Log Controller
 * For school super admin and managers to view system logs
 */

class SystemLogController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
    }
    
    public function index() {
        $user = Auth::user();
        $role = strtolower($user['role_name']);
        
        // Only super_admin and school_manager can access logs
        if (!in_array($role, ['super_admin', 'school_manager'])) {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get school ID from subdomain or config
        $schoolId = $this->getSchoolId();
        
        $filters = [
            'action' => $_GET['action'] ?? '',
            'module' => $_GET['module'] ?? '',
            'status' => $_GET['status'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $logs = $this->getLogs($schoolId, $limit, $offset, $filters);
        
        // Get unique modules and actions for filters
        $modules = $this->getUniqueModules($schoolId);
        $actions = $this->getUniqueActions($schoolId);
        
        $data = [
            'title' => 'System Logs - ' . APP_NAME,
            'user' => $user,
            'logs' => $logs,
            'filters' => $filters,
            'modules' => $modules,
            'actions' => $actions,
            'page' => $page,
            'schoolId' => $schoolId
        ];
        
        $this->view('systemlogs/index', $data);
    }
    
    private function getSchoolId() {
        // Try to get from config first
        if (defined('SCHOOL_ID')) {
            return SCHOOL_ID;
        }
        
        // Try to get from subdomain
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
    
    private function getLogs($schoolId, $limit, $offset, $filters) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT l.*, u.first_name, u.last_name, u.email 
                FROM school_system_logs l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE 1=1";
        $params = [];
        
        if ($schoolId) {
            $sql .= " AND l.school_id = ?";
            $params[] = $schoolId;
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND l.action LIKE ?";
            $params[] = '%' . $filters['action'] . '%';
        }
        
        if (!empty($filters['module'])) {
            $sql .= " AND l.module = ?";
            $params[] = $filters['module'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        
        $sql .= " ORDER BY l.created_at DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getUniqueModules($schoolId) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT DISTINCT module FROM school_system_logs WHERE module IS NOT NULL";
        $params = [];
        
        if ($schoolId) {
            $sql .= " AND school_id = ?";
            $params[] = $schoolId;
        }
        
        $sql .= " ORDER BY module";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function getUniqueActions($schoolId) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT DISTINCT action FROM school_system_logs WHERE 1=1";
        $params = [];
        
        if ($schoolId) {
            $sql .= " AND school_id = ?";
            $params[] = $schoolId;
        }
        
        $sql .= " ORDER BY action LIMIT 20";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

