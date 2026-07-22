<?php
/**
 * Grade Controller
 * Handles grade management operations
 */

class GradeController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * List all grades
     */
    public function index() {
        $gradeModel = $this->model('Grade');
        $grades = $gradeModel->getAllOrdered();
        
        $data = [
            'title' => 'Grade Management - ' . APP_NAME,
            'grades' => $grades
        ];
        
        $this->view('grades/index', $data);
    }
    
    /**
     * Show grade details
     */
    public function show($id) {
        $gradeModel = $this->model('Grade');
        $classModel = $this->model('ClassModel');
        
        $grade = $gradeModel->findById($id);
        
        if (!$grade) {
            $this->setFlash('error', 'Grade not found');
            $this->redirect('/grades');
            return;
        }
        
        // Get classes for this grade
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        $classes = $classModel->getByGrade($id, $currentYear);
        
        // Get student count
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM students s 
                              JOIN classes c ON s.class_id = c.id 
                              WHERE c.grade_id = ? AND s.status = 'active'");
        $stmt->execute([$id]);
        $studentCount = $stmt->fetch()['count'];
        
        $data = [
            'title' => 'Grade Details - ' . APP_NAME,
            'grade' => $grade,
            'classes' => $classes,
            'studentCount' => $studentCount
        ];
        
        $this->view('grades/show', $data);
    }
    
    /**
     * Show create form
     */
    public function create() {
        $data = [
            'title' => 'Create Grade - ' . APP_NAME,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('grades/create', $data);
    }
    
    /**
     * Store new grade
     */
    public function store() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $gradeModel = $this->model('Grade');
        
        // Validate input
        $errors = [];
        if (empty($_POST['name'])) {
            $errors['name'] = 'Grade name is required';
        }
        if (empty($_POST['display_name'])) {
            $errors['display_name'] = 'Display name is required';
        }
        if (empty($_POST['level']) || !is_numeric($_POST['level'])) {
            $errors['level'] = 'Level is required and must be a number';
        }
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        // Check if grade name already exists
        $existing = $gradeModel->query("SELECT * FROM grades WHERE name = ?", [$_POST['name']])->fetch();
        
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Grade name already exists']);
            return;
        }
        
        $data = [
            'name' => strtoupper(sanitize($_POST['name'])),
            'display_name' => sanitize($_POST['display_name']),
            'level' => intval($_POST['level']),
            'description' => sanitize($_POST['description'] ?? '')
        ];
        
        $id = $gradeModel->create($data);
        
        if ($id) {
            $this->json([
                'success' => true,
                'message' => 'Grade created successfully',
                'redirect' => BASE_URL . '/grades/show/' . $id
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create grade']);
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $gradeModel = $this->model('Grade');
        $grade = $gradeModel->findById($id);
        
        if (!$grade) {
            $this->setFlash('error', 'Grade not found');
            $this->redirect('/grades');
            return;
        }
        
        $data = [
            'title' => 'Edit Grade - ' . APP_NAME,
            'grade' => $grade,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('grades/edit', $data);
    }
    
    /**
     * Update grade
     */
    public function update($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $gradeModel = $this->model('Grade');
        
        // Validate input
        $errors = [];
        if (empty($_POST['name'])) {
            $errors['name'] = 'Grade name is required';
        }
        if (empty($_POST['display_name'])) {
            $errors['display_name'] = 'Display name is required';
        }
        if (empty($_POST['level']) || !is_numeric($_POST['level'])) {
            $errors['level'] = 'Level is required and must be a number';
        }
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        // Check if grade name already exists (excluding current grade)
        $existing = $gradeModel->query("SELECT * FROM grades WHERE name = ? AND id != ?", 
                                      [$_POST['name'], $id])->fetch();
        
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Grade name already exists']);
            return;
        }
        
        $data = [
            'name' => strtoupper(sanitize($_POST['name'])),
            'display_name' => sanitize($_POST['display_name']),
            'level' => intval($_POST['level']),
            'description' => sanitize($_POST['description'] ?? '')
        ];
        
        if ($gradeModel->update($id, $data)) {
            $this->json([
                'success' => true,
                'message' => 'Grade updated successfully',
                'redirect' => BASE_URL . '/grades/show/' . $id
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update grade']);
        }
    }
    
    /**
     * Delete grade
     */
    public function delete($id) {
        $gradeModel = $this->model('Grade');
        $classModel = $this->model('ClassModel');
        
        // Check if grade has classes
        $classes = $classModel->getByGrade($id);
        
        if (!empty($classes)) {
            $this->json(['success' => false, 'message' => 'Cannot delete grade with existing classes. Please delete or reassign classes first.']);
            return;
        }
        
        // Check if grade has learning areas
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM learning_areas WHERE grade_id = ?");
        $stmt->execute([$id]);
        $learningAreaCount = $stmt->fetch()['count'];
        
        if ($learningAreaCount > 0) {
            $this->json(['success' => false, 'message' => 'Cannot delete grade with learning areas assigned.']);
            return;
        }
        
        if ($gradeModel->delete($id)) {
            $this->json(['success' => true, 'message' => 'Grade deleted successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete grade']);
        }
    }
}

