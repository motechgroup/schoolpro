<?php
/**
 * Subject Controller (Learning Areas)
 * Handles subject/learning area management operations
 */

class SubjectController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * List all subjects
     */
    public function index() {
        $subjectModel = $this->model('LearningArea');
        $gradeModel = $this->model('Grade');
        
        $filters = [
            'grade_id' => $_GET['grade_id'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $subjects = $subjectModel->getAllWithDetails($filters);
        $grades = $gradeModel->getAllOrdered();
        
        $data = [
            'title' => 'Subject Management - ' . APP_NAME,
            'subjects' => $subjects,
            'grades' => $grades,
            'filters' => $filters
        ];
        
        $this->view('subjects/index', $data);
    }
    
    /**
     * Show create form
     */
    public function create() {
        $gradeModel = $this->model('Grade');
        $grades = $gradeModel->getAllOrdered();
        $selectedGradeId = $_GET['grade_id'] ?? null;
        
        $data = [
            'title' => 'Create Subject - ' . APP_NAME,
            'grades' => $grades,
            'selectedGradeId' => $selectedGradeId,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('subjects/create', $data);
    }
    
    /**
     * Store new subject
     */
    public function store() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $subjectModel = $this->model('LearningArea');
        
        $data = [
            'code' => sanitize($_POST['code'] ?? ''),
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'grade_id' => intval($_POST['grade_id'] ?? 0)
        ];
        
        // Validation
        if (empty($data['code']) || empty($data['name']) || empty($data['grade_id'])) {
            $this->json(['success' => false, 'message' => 'Code, name, and grade are required']);
            return;
        }
        
        // Check if code already exists
        if ($subjectModel->codeExists($data['code'])) {
            $this->json(['success' => false, 'message' => 'Subject code already exists']);
            return;
        }
        
        $id = $subjectModel->create($data);
        
        if ($id) {
            $this->json([
                'success' => true,
                'message' => 'Subject created successfully',
                'redirect' => BASE_URL . '/subjects'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create subject']);
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $subjectModel = $this->model('LearningArea');
        $gradeModel = $this->model('Grade');
        
        $subject = $subjectModel->getLearningAreaWithDetails($id);
        
        if (!$subject) {
            $this->setFlash('error', 'Subject not found');
            $this->redirect('/subjects');
            return;
        }
        
        $grades = $gradeModel->getAllOrdered();
        
        $data = [
            'title' => 'Edit Subject - ' . APP_NAME,
            'subject' => $subject,
            'grades' => $grades,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('subjects/edit', $data);
    }
    
    /**
     * Update subject
     */
    public function update($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $subjectModel = $this->model('LearningArea');
        
        // Check if subject exists
        $subject = $subjectModel->findById($id);
        if (!$subject) {
            $this->json(['success' => false, 'message' => 'Subject not found']);
            return;
        }
        
        $data = [
            'code' => sanitize($_POST['code'] ?? ''),
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'grade_id' => intval($_POST['grade_id'] ?? 0)
        ];
        
        // Validation
        if (empty($data['code']) || empty($data['name']) || empty($data['grade_id'])) {
            $this->json(['success' => false, 'message' => 'Code, name, and grade are required']);
            return;
        }
        
        // Check if code already exists (excluding current subject)
        if ($subjectModel->codeExists($data['code'], $id)) {
            $this->json(['success' => false, 'message' => 'Subject code already exists']);
            return;
        }
        
        $result = $subjectModel->update($id, $data);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Subject updated successfully',
                'redirect' => BASE_URL . '/subjects'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update subject']);
        }
    }
    
    /**
     * Delete subject
     */
    public function delete($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $subjectModel = $this->model('LearningArea');
        
        // Check if subject exists
        $subject = $subjectModel->findById($id);
        if (!$subject) {
            $this->json(['success' => false, 'message' => 'Subject not found']);
            return;
        }
        
        // Check if subject is used in examinations
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM examination_subjects WHERE learning_area_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $this->json(['success' => false, 'message' => 'Cannot delete subject. It is being used in examinations.']);
            return;
        }
        
        // Check if subject is used in assessments
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM assessments WHERE learning_area_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $this->json(['success' => false, 'message' => 'Cannot delete subject. It is being used in assessments.']);
            return;
        }
        
        $result = $subjectModel->delete($id);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Subject deleted successfully'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete subject']);
        }
    }
}

