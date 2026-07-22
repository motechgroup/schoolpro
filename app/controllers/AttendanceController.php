<?php
/**
 * Attendance Controller
 */

class AttendanceController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher', 'teacher'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * Attendance index - class selection
     */
    public function index() {
        $classModel = $this->model('ClassModel');
        $academicYear = $_GET['academic_year'] ?? date('Y') . '/' . (date('Y') + 1);
        $classes = $classModel->getAllWithDetails($academicYear);
        
        $data = [
            'title' => 'Mark Attendance - ' . APP_NAME,
            'classes' => $classes,
            'academicYear' => $academicYear
        ];
        
        $this->view('attendance/index', $data);
    }
    
    /**
     * Mark attendance
     */
    public function mark() {
        $classModel = $this->model('ClassModel');
        $studentModel = $this->model('Student');
        
        $classId = $_GET['class_id'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');
        
        if (!$classId) {
            $this->setFlash('error', 'Class is required');
            $this->redirect('/dashboard');
            return;
        }
        
        $class = $classModel->getClassWithDetails($classId);
        $students = $studentModel->getByClass($classId);
        
        // Get existing attendance
        $attendanceModel = $this->model('Attendance');
        $existingAttendance = $attendanceModel->getClassAttendance($classId, $date);
        $attendanceMap = [];
        foreach ($existingAttendance as $att) {
            $attendanceMap[$att['student_id']] = $att['status'];
        }
        
        $data = [
            'title' => 'Mark Attendance - ' . APP_NAME,
            'class' => $class,
            'students' => $students,
            'date' => $date,
            'existingAttendance' => $attendanceMap,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('attendance/mark', $data);
    }
    
    /**
     * Save attendance
     */
    public function save() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $classId = intval($_POST['class_id'] ?? 0);
        $date = sanitize($_POST['date'] ?? '');
        $attendanceData = $_POST['attendance'] ?? [];
        
        if (empty($classId) || empty($date) || empty($attendanceData)) {
            $this->json(['success' => false, 'message' => 'Invalid attendance data']);
            return;
        }
        
        $attendanceModel = $this->model('Attendance');
        
        if ($attendanceModel->markBulkAttendance($classId, $date, $attendanceData, Auth::userId())) {
            $this->json(['success' => true, 'message' => 'Attendance marked successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to mark attendance']);
        }
    }
}

