<?php
/**
 * Main Entry Point - Router
 * Kenyan Primary School Management System
 */

// Start session
session_start();

// Define base path
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Load configuration
require_once APP_PATH . '/config/config.php';

// Load Composer autoloader (for PHPMailer and other vendor packages)
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Load helper functions
require_once APP_PATH . '/core/Helper.php';

// Load autoloader
require_once APP_PATH . '/core/Autoloader.php';

// Initialize autoloader
Autoloader::register();

// Check if school is suspended (before any routing)
require_once APP_PATH . '/helpers/SchoolStatusHelper.php';
if (SchoolStatusHelper::isSuspended()) {
    // Show suspended screen
    $schoolStatus = SchoolStatusHelper::getSchoolStatus();
    require_once APP_PATH . '/views/errors/suspended.php';
    exit;
}

// Get URL from query string
$url = $_GET['url'] ?? '';

// Remove trailing slash
$url = rtrim($url, '/');

// Redirect root to login if not already logged in
// Note: Auth class will be loaded by autoloader when needed
if (empty($url)) {
    // Check if user is logged in (session check)
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/auth/login');
        exit;
    } else {
        // If logged in, redirect to dashboard
        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }
}

// Split URL into parts
$urlParts = explode('/', $url);

// URL to controller mapping (handle plural/singular differences)
$urlMapping = [
    'students' => 'Student',
    'teachers' => 'Teacher',
    'classes' => 'Class',
    'grades' => 'Grade',
    'announcements' => 'Announcement',
    'reports' => 'Report',
    'attendance' => 'Attendance',
    'payments' => 'Payment',
    'fees' => 'Fee',
    'feeheads' => 'FeeHead',
    'studentfees' => 'StudentFee',
    'feereport' => 'FeeReport',
    'assessments' => 'Assessment',
    'examinations' => 'Examination',
    'subjects' => 'Subject',
    'parents' => 'Parent',
    'parent' => 'Parent',
    'communication' => 'Communication',
    'emailtemplates' => 'EmailTemplate',
    'settings' => 'Settings',
    'users' => 'User',
    'roles' => 'Role',
    'profile' => 'Profile',
    'dashboard' => 'Dashboard',
    'systemlogs' => 'SystemLog',
    'notification' => 'Notification',
    'auth' => 'Auth',
    'api' => 'Api',
    'mpesa' => 'Mpesa',
    'equitybank' => 'EquityBank',
    'academicyears' => 'AcademicYear',
    'library' => 'Library'
];

// Get controller name from mapping or use default
$urlSegment = !empty($urlParts[0]) ? strtolower($urlParts[0]) : '';
$controllerName = !empty($urlMapping[$urlSegment]) 
    ? $urlMapping[$urlSegment] . 'Controller' 
    : (!empty($urlParts[0]) ? ucfirst($urlParts[0]) . 'Controller' : 'HomeController');
$method = $urlParts[1] ?? 'index';

// Convert kebab-case to camelCase (e.g., forgot-password -> forgotPassword)
if (strpos($method, '-') !== false) {
    $methodParts = explode('-', $method);
    $method = $methodParts[0];
    for ($i = 1; $i < count($methodParts); $i++) {
        $method .= ucfirst($methodParts[$i]);
    }
}

$params = array_slice($urlParts, 2);

// Check if controller exists
$controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        
        // Check if method exists (try direct first, then case-insensitive)
        $actualMethod = null;
        if (method_exists($controller, $method)) {
            $actualMethod = $method;
        } else {
            // Try case-insensitive match using ReflectionClass
            try {
                $reflection = new ReflectionClass($controller);
                foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
                    if (strcasecmp($refMethod->getName(), $method) === 0) {
                        $actualMethod = $refMethod->getName();
                        break;
                    }
                }
            } catch (Exception $e) {
                // Reflection failed, fall back to method_exists
                error_log("Reflection error: " . $e->getMessage());
            }
        }
        
        if ($actualMethod) {
            // Check if user is authenticated (except for auth pages and API endpoints)
            $publicControllers = ['authcontroller', 'homecontroller', 'apicontroller', 'mpesacontroller'];
            $publicMethods = ['login', 'register', 'forgotPassword', 'callback'];
            
            if (!in_array(strtolower($controllerName), $publicControllers) && 
                !in_array($actualMethod, $publicMethods)) {
                if (!Auth::isLoggedIn()) {
                    header('Location: ' . BASE_URL . '/auth/login');
                    exit;
                }
            }
            
            // Call the method with parameters using the actual method name
            call_user_func_array([$controller, $actualMethod], $params);
        } else {
            // Method not found
            http_response_code(404);
            // Check if this is an API request (AJAX/JSON)
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ||
                !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false ||
                !empty($_POST) || !empty($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Method not found: ' . $method, 'error' => '404']);
                exit;
            }
            require_once APP_PATH . '/views/errors/404.php';
        }
    } else {
        // Controller class not found
        http_response_code(404);
        // Check if this is an API request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ||
            !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false ||
            !empty($_POST) || !empty($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Controller not found: ' . $controllerName, 'error' => '404']);
            exit;
        }
        require_once APP_PATH . '/views/errors/404.php';
    }
} else {
    // Controller file not found
    http_response_code(404);
    // Check if this is an API request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ||
        !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false ||
        !empty($_POST) || !empty($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Controller file not found: ' . $controllerName, 'error' => '404']);
        exit;
    }
    require_once APP_PATH . '/views/errors/404.php';
}

