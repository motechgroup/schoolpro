<?php
/**
 * Academic Year Controller
 * Handles academic year and term management
 */

class AcademicYearController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * List all academic years
     */
    public function index() {
        $academicYearModel = $this->model('AcademicYear');
        $academicYears = $academicYearModel->getAll();
        
        // Get terms for each academic year
        foreach ($academicYears as &$year) {
            $year['terms'] = $academicYearModel->getTerms($year['id']);
        }
        
        $data = [
            'title' => 'Academic Years & Terms - ' . APP_NAME,
            'academicYears' => $academicYears,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('academicyears/index', $data);
    }
    
    /**
     * Show create academic year form
     */
    public function create() {
        $data = [
            'title' => 'Create Academic Year - ' . APP_NAME,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('academicyears/create', $data);
    }
    
    /**
     * Store new academic year
     */
    public function store() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $academicYearModel = $this->model('AcademicYear');
        
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'start_date' => sanitize($_POST['start_date'] ?? ''),
            'end_date' => sanitize($_POST['end_date'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'upcoming'),
            'is_current' => !empty($_POST['is_current']) ? 1 : 0,
            'created_by' => Auth::userId()
        ];
        
        // Validate
        if (empty($data['name']) || empty($data['start_date']) || empty($data['end_date'])) {
            $this->json(['success' => false, 'message' => 'Name, start date, and end date are required']);
            return;
        }
        
        if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
            $this->json(['success' => false, 'message' => 'End date must be after start date']);
            return;
        }
        
        try {
            if ($academicYearModel->create($data)) {
                $this->json([
                    'success' => true,
                    'message' => 'Academic year created successfully',
                    'redirect' => BASE_URL . '/academicyears'
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to create academic year']);
            }
        } catch (Exception $e) {
            error_log("Academic year creation error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show edit academic year form
     */
    public function edit($id) {
        $academicYearModel = $this->model('AcademicYear');
        $academicYear = $academicYearModel->findById($id);
        
        if (!$academicYear) {
            $this->setFlash('error', 'Academic year not found');
            $this->redirect('/academicyears');
            return;
        }
        
        $terms = $academicYearModel->getTerms($id);
        
        $data = [
            'title' => 'Edit Academic Year - ' . APP_NAME,
            'academicYear' => $academicYear,
            'terms' => $terms,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('academicyears/edit', $data);
    }
    
    /**
     * Update academic year
     */
    public function update($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $academicYearModel = $this->model('AcademicYear');
        
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'start_date' => sanitize($_POST['start_date'] ?? ''),
            'end_date' => sanitize($_POST['end_date'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'upcoming'),
            'is_current' => !empty($_POST['is_current']) ? 1 : 0
        ];
        
        // Validate
        if (empty($data['name']) || empty($data['start_date']) || empty($data['end_date'])) {
            $this->json(['success' => false, 'message' => 'Name, start date, and end date are required']);
            return;
        }
        
        if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
            $this->json(['success' => false, 'message' => 'End date must be after start date']);
            return;
        }
        
        try {
            if ($academicYearModel->update($id, $data)) {
                $this->json([
                    'success' => true,
                    'message' => 'Academic year updated successfully',
                    'redirect' => BASE_URL . '/academicyears'
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update academic year']);
            }
        } catch (Exception $e) {
            error_log("Academic year update error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Create term
     */
    public function createTerm() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $academicYearModel = $this->model('AcademicYear');
        
        $data = [
            'academic_year_id' => intval($_POST['academic_year_id'] ?? 0),
            'term_number' => intval($_POST['term_number'] ?? 1),
            'name' => sanitize($_POST['name'] ?? ''),
            'start_date' => sanitize($_POST['start_date'] ?? ''),
            'end_date' => sanitize($_POST['end_date'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'upcoming'),
            'is_current' => !empty($_POST['is_current']) ? 1 : 0,
            'created_by' => Auth::userId()
        ];
        
        // Validate
        if (empty($data['academic_year_id']) || empty($data['name']) || empty($data['start_date']) || empty($data['end_date'])) {
            $this->json(['success' => false, 'message' => 'All fields are required']);
            return;
        }
        
        if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
            $this->json(['success' => false, 'message' => 'End date must be after start date']);
            return;
        }
        
        // Verify academic year exists & automatically expand its bounds if term dates extend outside
        $academicYear = $academicYearModel->findById($data['academic_year_id']);
        if (!$academicYear) {
            $this->json(['success' => false, 'message' => 'Academic year not found']);
            return;
        }
        
        $ayUpdate = [];
        if (strtotime($data['start_date']) < strtotime($academicYear['start_date'])) {
            $ayUpdate['start_date'] = $data['start_date'];
        }
        if (strtotime($data['end_date']) > strtotime($academicYear['end_date'])) {
            $ayUpdate['end_date'] = $data['end_date'];
        }
        if (!empty($ayUpdate)) {
            $academicYearModel->update($academicYear['id'], $ayUpdate);
        }
        
        try {
            if ($academicYearModel->createTerm($data)) {
                $this->json([
                    'success' => true,
                    'message' => 'Term created successfully',
                    'redirect' => BASE_URL . '/academicyears/edit/' . $data['academic_year_id']
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to create term']);
            }
        } catch (Exception $e) {
            error_log("Term creation error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Update term
     */
    public function updateTerm($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $academicYearModel = $this->model('AcademicYear');
        
        $term = $academicYearModel->getTermById($id);
        if (!$term) {
            $this->json(['success' => false, 'message' => 'Term not found']);
            return;
        }
        
        $data = [
            'term_number' => intval($_POST['term_number'] ?? $term['term_number']),
            'name' => sanitize($_POST['name'] ?? ''),
            'start_date' => sanitize($_POST['start_date'] ?? ''),
            'end_date' => sanitize($_POST['end_date'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'upcoming'),
            'is_current' => !empty($_POST['is_current']) ? 1 : 0
        ];
        
        // Validate
        if (empty($data['name']) || empty($data['start_date']) || empty($data['end_date'])) {
            $this->json(['success' => false, 'message' => 'All fields are required']);
            return;
        }
        
        if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
            $this->json(['success' => false, 'message' => 'End date must be after start date']);
            return;
        }
        
        // Get academic year & auto-expand bounds if term extends outside
        $academicYear = $academicYearModel->findById($term['academic_year_id']);
        if ($academicYear) {
            $ayUpdate = [];
            if (strtotime($data['start_date']) < strtotime($academicYear['start_date'])) {
                $ayUpdate['start_date'] = $data['start_date'];
            }
            if (strtotime($data['end_date']) > strtotime($academicYear['end_date'])) {
                $ayUpdate['end_date'] = $data['end_date'];
            }
            if (!empty($ayUpdate)) {
                $academicYearModel->update($academicYear['id'], $ayUpdate);
            }
        }
        
        try {
            if ($academicYearModel->updateTerm($id, $data)) {
                $this->json([
                    'success' => true,
                    'message' => 'Term updated successfully',
                    'redirect' => BASE_URL . '/academicyears/edit/' . $term['academic_year_id']
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update term']);
            }
        } catch (Exception $e) {
            error_log("Term update error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete academic year
     */
    public function delete($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $academicYearModel = $this->model('AcademicYear');
        
        try {
            if ($academicYearModel->delete($id)) {
                $this->json([
                    'success' => true,
                    'message' => 'Academic year deleted successfully',
                    'redirect' => BASE_URL . '/academicyears'
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete academic year']);
            }
        } catch (Exception $e) {
            error_log("Academic year deletion error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete term
     */
    public function deleteTerm($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $academicYearModel = $this->model('AcademicYear');
        
        $term = $academicYearModel->getTermById($id);
        if (!$term) {
            $this->json(['success' => false, 'message' => 'Term not found']);
            return;
        }
        
        try {
            if ($academicYearModel->deleteTerm($id)) {
                $this->json([
                    'success' => true,
                    'message' => 'Term deleted successfully',
                    'redirect' => BASE_URL . '/academicyears/edit/' . $term['academic_year_id']
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete term']);
            }
        } catch (Exception $e) {
            error_log("Term deletion error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}

