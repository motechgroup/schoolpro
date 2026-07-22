<?php
/**
 * Class Controller
 * Handles class management operations
 */

class ClassController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'head_teacher'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * List all classes
     */
    public function index() {
        $classModel = $this->model('ClassModel');
        $gradeModel = $this->model('Grade');
        $teacherModel = $this->model('Teacher');
        
        $academicYear = $_GET['academic_year'] ?? date('Y') . '/' . (date('Y') + 1);
        $classes = $classModel->getAllWithDetails($academicYear);
        $grades = $gradeModel->getAllOrdered();
        $teachers = $teacherModel->getAllWithDetails();
        
        $data = [
            'title' => 'Class Management - ' . APP_NAME,
            'classes' => $classes,
            'grades' => $grades,
            'teachers' => $teachers,
            'academicYear' => $academicYear
        ];
        
        $this->view('classes/index', $data);
    }
    
    /**
     * Show class details
     */
    public function show($id) {
        $classModel = $this->model('ClassModel');
        $studentModel = $this->model('Student');
        
        $class = $classModel->getClassWithDetails($id);
        
        if (!$class) {
            $this->setFlash('error', 'Class not found');
            $this->redirect('/classes');
            return;
        }
        
        $students = $studentModel->getByClass($id);
        
        $data = [
            'title' => 'Class Details - ' . APP_NAME,
            'class' => $class,
            'students' => $students
        ];
        
        $this->view('classes/show', $data);
    }
    
    /**
     * Show create form
     */
    public function create() {
        $gradeModel = $this->model('Grade');
        $teacherModel = $this->model('Teacher');
        
        $grades = $gradeModel->getAllOrdered();
        $teachers = $teacherModel->getAllWithDetails();
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        $selectedGradeId = $_GET['grade_id'] ?? null;
        
        $data = [
            'title' => 'Create Class - ' . APP_NAME,
            'grades' => $grades,
            'teachers' => $teachers,
            'academicYear' => $currentYear,
            'selectedGradeId' => $selectedGradeId,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('classes/create', $data);
    }
    
    /**
     * Store new class
     */
    public function store() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $classModel = $this->model('ClassModel');
        
        // Validate input
        $errors = [];
        if (empty($_POST['name'])) {
            $errors['name'] = 'Class name is required';
        }
        if (empty($_POST['grade_id'])) {
            $errors['grade_id'] = 'Grade is required';
        }
        if (empty($_POST['academic_year'])) {
            $errors['academic_year'] = 'Academic year is required';
        }
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        // Check if class already exists
        $existing = $classModel->query("SELECT * FROM classes WHERE grade_id = ? AND name = ? AND academic_year = ?", 
                                      [$_POST['grade_id'], $_POST['name'], $_POST['academic_year']])->fetch();
        
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Class already exists for this grade and academic year']);
            return;
        }
        
        $data = [
            'grade_id' => intval($_POST['grade_id']),
            'name' => sanitize($_POST['name']),
            'class_teacher_id' => !empty($_POST['class_teacher_id']) ? intval($_POST['class_teacher_id']) : null,
            'capacity' => !empty($_POST['capacity']) ? intval($_POST['capacity']) : 40,
            'academic_year' => sanitize($_POST['academic_year']),
            'status' => 'active'
        ];
        
        $id = $classModel->create($data);
        
        if ($id) {
            $this->json([
                'success' => true,
                'message' => 'Class created successfully',
                'redirect' => BASE_URL . '/classes/show/' . $id
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create class']);
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $classModel = $this->model('ClassModel');
        $gradeModel = $this->model('Grade');
        $teacherModel = $this->model('Teacher');
        
        $class = $classModel->findById($id);
        
        if (!$class) {
            $this->setFlash('error', 'Class not found');
            $this->redirect('/classes');
            return;
        }
        
        $grades = $gradeModel->getAllOrdered();
        $teachers = $teacherModel->getAllWithDetails();
        
        $data = [
            'title' => 'Edit Class - ' . APP_NAME,
            'class' => $class,
            'grades' => $grades,
            'teachers' => $teachers,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('classes/edit', $data);
    }
    
    /**
     * Update class
     */
    public function update($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $classModel = $this->model('ClassModel');
        
        // Validate input
        $errors = [];
        if (empty($_POST['name'])) {
            $errors['name'] = 'Class name is required';
        }
        if (empty($_POST['grade_id'])) {
            $errors['grade_id'] = 'Grade is required';
        }
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        // Check if class already exists (excluding current class)
        $existing = $classModel->query("SELECT * FROM classes WHERE grade_id = ? AND name = ? AND academic_year = ? AND id != ?", 
                                      [$_POST['grade_id'], $_POST['name'], $_POST['academic_year'], $id])->fetch();
        
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Class already exists for this grade and academic year']);
            return;
        }
        
        $data = [
            'grade_id' => intval($_POST['grade_id']),
            'name' => sanitize($_POST['name']),
            'class_teacher_id' => !empty($_POST['class_teacher_id']) ? intval($_POST['class_teacher_id']) : null,
            'capacity' => !empty($_POST['capacity']) ? intval($_POST['capacity']) : 40,
            'academic_year' => sanitize($_POST['academic_year']),
            'status' => sanitize($_POST['status'] ?? 'active')
        ];
        
        if ($classModel->update($id, $data)) {
            $this->json([
                'success' => true,
                'message' => 'Class updated successfully',
                'redirect' => BASE_URL . '/classes/show/' . $id
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update class']);
        }
    }
    
    /**
     * Delete class (soft delete)
     */
    public function delete($id) {
        $classModel = $this->model('ClassModel');
        
        // Check if class has students
        $studentModel = $this->model('Student');
        $students = $studentModel->getByClass($id);
        
        if (!empty($students)) {
            $this->json(['success' => false, 'message' => 'Cannot delete class with assigned students. Please transfer students first.']);
            return;
        }
        
        // Soft delete - set status to archived
        if ($classModel->update($id, ['status' => 'archived'])) {
            $this->json(['success' => true, 'message' => 'Class deleted successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete class']);
        }
    }
}

