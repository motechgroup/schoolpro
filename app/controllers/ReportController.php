<?php
/**
 * Report Controller
 * Handles report generation and exports
 */

class ReportController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher', 'bursar', 'accountant'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * Reports dashboard
     */
    public function index() {
        $data = [
            'title' => 'Reports - ' . APP_NAME
        ];
        
        $this->view('reports/index', $data);
    }
    
    /**
     * Student report
     */
    public function students() {
        $studentModel = $this->model('Student');
        $classModel = $this->model('ClassModel');
        
        $filters = [
            'status' => $_GET['status'] ?? 'active',
            'class_id' => $_GET['class_id'] ?? null
        ];
        
        $students = $studentModel->getAllWithDetails($filters);
        $classes = $classModel->getAllWithDetails();
        
        $data = [
            'title' => 'Student Report - ' . APP_NAME,
            'students' => $students,
            'classes' => $classes,
            'filters' => $filters
        ];
        
        $this->view('reports/students', $data);
    }
    
    /**
     * Attendance report
     */
    public function attendance() {
        $classModel = $this->model('ClassModel');
        $attendanceModel = $this->model('Attendance');
        
        $classId = $_GET['class_id'] ?? null;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        $classes = $classModel->getAllWithDetails();
        $attendanceData = [];
        
        if ($classId) {
            $studentModel = $this->model('Student');
            $students = $studentModel->getByClass($classId);
            
            foreach ($students as $student) {
                $summary = $attendanceModel->getStudentAttendanceSummary($student['id'], $startDate, $endDate);
                if ($summary) {
                    $attendanceData[] = [
                        'student' => $student,
                        'summary' => $summary
                    ];
                }
            }
        }
        
        $data = [
            'title' => 'Attendance Report - ' . APP_NAME,
            'classes' => $classes,
            'attendanceData' => $attendanceData,
            'filters' => [
                'class_id' => $classId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ];
        
        $this->view('reports/attendance', $data);
    }
    
    /**
     * Financial report
     */
    public function financial() {
        $invoiceModel = $this->model('Invoice');
        $paymentModel = $this->model('Payment');
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        $db = Database::getInstance()->getConnection();
        
        // Get financial summary
        $stmt = $db->prepare("SELECT 
                                COUNT(*) as total_invoices,
                                SUM(total_amount) as total_billed,
                                SUM(paid_amount) as total_paid,
                                SUM(balance) as total_balance
                              FROM invoices 
                              WHERE created_at BETWEEN ? AND ?");
        $stmt->execute([$startDate, $endDate]);
        $summary = $stmt->fetch();
        
        // Get recent payments
        $stmt = $db->prepare("SELECT p.*, s.first_name as student_first_name, s.last_name as student_last_name
                              FROM payments p
                              LEFT JOIN students s ON p.student_id = s.id
                              WHERE p.payment_date BETWEEN ? AND ?
                              ORDER BY p.payment_date DESC
                              LIMIT 50");
        $stmt->execute([$startDate, $endDate]);
        $recentPayments = $stmt->fetchAll();
        
        $feeHeadPaymentModel = $this->model('FeeHeadPayment');
        $academicYear = getAcademicYearName($_GET['academic_year'] ?? null);
        $feeBreakdown = $feeHeadPaymentModel->getTuitionVsOtherBreakdown($startDate, $endDate, $academicYear);
        
        $data = [
            'title' => 'Financial Report - ' . APP_NAME,
            'summary' => $summary,
            'recentPayments' => $recentPayments,
            'feeBreakdown' => $feeBreakdown,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'academic_year' => $academicYear
            ]
        ];
        
        $this->view('reports/financial', $data);
    }
}

