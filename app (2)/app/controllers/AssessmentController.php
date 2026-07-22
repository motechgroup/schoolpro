<?php
/**
 * Assessment Controller
 * Handles CBC assessment operations
 */

class AssessmentController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher', 'teacher'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * Assessment dashboard
     */
    public function index() {
        $assessmentModel = $this->model('Assessment');
        $studentModel = $this->model('Student');
        
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        $currentTerm = $_GET['term'] ?? 1;
        
        // Get recent assessments
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT a.*, s.first_name as student_first_name, s.last_name as student_last_name,
                                     la.name as learning_area_name
                              FROM assessments a
                              LEFT JOIN students s ON a.student_id = s.id
                              LEFT JOIN learning_areas la ON a.learning_area_id = la.id
                              WHERE a.academic_year = ? AND a.term = ?
                              ORDER BY a.created_at DESC
                              LIMIT 20");
        $stmt->execute([$currentYear, $currentTerm]);
        $recentAssessments = $stmt->fetchAll();
        
        $data = [
            'title' => 'Assessments - ' . APP_NAME,
            'recentAssessments' => $recentAssessments,
            'currentTerm' => $currentTerm,
            'currentYear' => $currentYear
        ];
        
        $this->view('assessments/index', $data);
    }
}

