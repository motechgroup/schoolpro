<?php
/**
 * Parent Controller
 * Handles parent management (admin) and parent portal views
 */

class ParentController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
    }
    
    /**
     * List all parents (Admin view)
     */
    public function index() {
        // Check if user is admin
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            // If parent, redirect to their dashboard
            if (Auth::hasRole('parent')) {
                $this->redirect('/parent/dashboard');
                return;
            }
            http_response_code(403);
            die("Access denied");
        }
        
        $parentModel = $this->model('ParentModel');
        
        $filters = [
            'status' => $_GET['status'] ?? 'active',
            'search' => $_GET['search'] ?? null,
            'has_balance' => $_GET['has_balance'] ?? null
        ];
        
        $parents = $parentModel->getAllWithDetails($filters);
        
        $data = [
            'title' => 'Parents Management - ' . APP_NAME,
            'parents' => $parents,
            'filters' => $filters
        ];
        
        $this->view('parents/index', $data);
    }
    
    /**
     * Show parent details (Admin view)
     */
    public function show($id) {
        // Check if user is admin
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            http_response_code(403);
            die("Access denied");
        }
        
        $parentModel = $this->model('ParentModel');
        $studentModel = $this->model('Student');
        $invoiceModel = $this->model('Invoice');
        
        $parent = $parentModel->getParentWithDetails($id);
        
        if (!$parent) {
            $this->setFlash('error', 'Parent not found');
            $this->redirect('/parents');
            return;
        }
        
        // Get all children
        $children = $studentModel->getByParent($id);
        
        // Get fee information for all children
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        $totalBalance = 0;
        $invoices = [];
        
        foreach ($children as $child) {
            $childInvoices = $invoiceModel->getByStudent($child['id'], $currentYear);
            foreach ($childInvoices as $inv) {
                $invoiceModel->updateBalance($inv['id']);
            }
            $childInvoices = $invoiceModel->getByStudent($child['id'], $currentYear);
            $invoices = array_merge($invoices, $childInvoices);
            foreach ($childInvoices as $inv) {
                $totalBalance += $inv['balance'] ?? 0;
            }
        }
        
        $data = [
            'title' => 'Parent Details - ' . APP_NAME,
            'parent' => $parent,
            'children' => $children,
            'invoices' => $invoices,
            'total_balance' => $totalBalance,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('parents/show', $data);
    }
    
    /**
     * Parent dashboard
     */
    public function dashboard() {
        $user = Auth::user();
        $studentModel = $this->model('Student');
        $parentModel = $this->model('ParentModel');
        
        // Get parent ID from user (assuming parent user_id links to parents table)
        // For now, we'll search by email or phone
        $parent = $parentModel->query("SELECT * FROM parents WHERE email = ? OR phone = ? LIMIT 1", 
                                      [$user['email'], $user['email']])->fetch();
        
        if (!$parent) {
            $this->setFlash('error', 'Parent profile not found');
            $this->redirect('/dashboard');
            return;
        }
        
        $children = $studentModel->getByParent($parent['id']);
        
        $data = [
            'title' => 'Parent Portal - ' . APP_NAME,
            'parent' => $parent,
            'children' => $children
        ];
        
        $this->view('parent/dashboard', $data);
    }
    
    /**
     * View child details
     */
    public function child($studentId) {
        $studentModel = $this->model('Student');
        $attendanceModel = $this->model('Attendance');
        $assessmentModel = $this->model('Assessment');
        $invoiceModel = $this->model('Invoice');
        
        $student = $studentModel->getStudentWithDetails($studentId);
        
        // Verify parent owns this student
        $user = Auth::user();
        $parentModel = $this->model('ParentModel');
        $parent = $parentModel->query("SELECT * FROM parents WHERE email = ? LIMIT 1", [$user['email']])->fetch();
        
        if (!$parent || $student['parent_id'] != $parent['id']) {
            $this->setFlash('error', 'Access denied');
            $this->redirect('/parent/dashboard');
            return;
        }
        
        // Get attendance summary (current month)
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        $attendanceSummary = $attendanceModel->getStudentAttendanceSummary($studentId, $startDate, $endDate);
        
        // Get recent assessments
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        $assessments = $assessmentModel->getStudentAssessments($studentId, null, $currentYear);
        
        // Get invoices
        $invoices = $invoiceModel->getByStudent($studentId, $currentYear);
        
        $data = [
            'title' => 'Student Details - ' . APP_NAME,
            'student' => $student,
            'attendanceSummary' => $attendanceSummary,
            'assessments' => $assessments,
            'invoices' => $invoices
        ];
        
        $this->view('parent/child', $data);
    }
    
    /**
     * Show create form
     */
    public function create() {
        // Check if user is admin
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            http_response_code(403);
            die("Access denied");
        }
        
        $data = [
            'title' => 'Add New Parent - ' . APP_NAME,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('parents/create', $data);
    }
    
    /**
     * Store new parent
     */
    public function store() {
        // Check if user is admin
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $parentModel = $this->model('ParentModel');
        
        // Validate input
        $errors = [];
        
        if (empty($_POST['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($_POST['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($_POST['phone'])) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!isValidPhone($_POST['phone'])) {
            $errors['phone'] = 'Invalid phone number format';
        }
        
        if (!empty($_POST['email']) && !isValidEmail($_POST['email'])) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        // Check if phone already exists
        $phone = formatPhone(sanitize($_POST['phone']));
        $existing = $parentModel->query("SELECT id FROM parents WHERE phone = ?", [$phone])->fetch();
        if ($existing) {
            $this->json(['success' => false, 'message' => 'A parent with this phone number already exists']);
            return;
        }
        
        // Prepare data
        $parentData = [
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'phone' => $phone,
            'phone_alt' => !empty($_POST['phone_alt']) ? formatPhone(sanitize($_POST['phone_alt'])) : null,
            'email' => !empty($_POST['email']) ? sanitize($_POST['email']) : null,
            'id_number' => !empty($_POST['id_number']) ? sanitize($_POST['id_number']) : null,
            'occupation' => !empty($_POST['occupation']) ? sanitize($_POST['occupation']) : null,
            'address' => !empty($_POST['address']) ? sanitize($_POST['address']) : null,
            'relationship' => sanitize($_POST['relationship'] ?? 'guardian'),
            'status' => sanitize($_POST['status'] ?? 'active')
        ];
        
        $parentId = $parentModel->create($parentData);
        
        if ($parentId) {
            $this->json([
                'success' => true,
                'message' => 'Parent added successfully',
                'redirect' => BASE_URL . '/parents/show/' . $parentId
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to add parent']);
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        // Check if user is admin
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            http_response_code(403);
            die("Access denied");
        }
        
        $parentModel = $this->model('ParentModel');
        
        $parent = $parentModel->findById($id);
        
        if (!$parent) {
            $this->setFlash('error', 'Parent not found');
            $this->redirect('/parents');
            return;
        }
        
        $data = [
            'title' => 'Edit Parent - ' . APP_NAME,
            'parent' => $parent,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('parents/edit', $data);
    }
    
    /**
     * Update parent
     */
    public function update($id) {
        // Check if user is admin
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $parentModel = $this->model('ParentModel');
        
        // Check if parent exists
        $parent = $parentModel->findById($id);
        if (!$parent) {
            $this->json(['success' => false, 'message' => 'Parent not found']);
            return;
        }
        
        // Validate input
        $errors = [];
        
        if (empty($_POST['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($_POST['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($_POST['phone'])) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!isValidPhone($_POST['phone'])) {
            $errors['phone'] = 'Invalid phone number format';
        }
        
        if (!empty($_POST['email']) && !isValidEmail($_POST['email'])) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        // Check if phone already exists (excluding current parent)
        $phone = formatPhone(sanitize($_POST['phone']));
        $existing = $parentModel->query("SELECT id FROM parents WHERE phone = ? AND id != ?", [$phone, $id])->fetch();
        if ($existing) {
            $this->json(['success' => false, 'message' => 'A parent with this phone number already exists']);
            return;
        }
        
        // Prepare update data
        $parentData = [
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'phone' => $phone,
            'phone_alt' => !empty($_POST['phone_alt']) ? formatPhone(sanitize($_POST['phone_alt'])) : null,
            'email' => !empty($_POST['email']) ? sanitize($_POST['email']) : null,
            'id_number' => !empty($_POST['id_number']) ? sanitize($_POST['id_number']) : null,
            'occupation' => !empty($_POST['occupation']) ? sanitize($_POST['occupation']) : null,
            'address' => !empty($_POST['address']) ? sanitize($_POST['address']) : null,
            'relationship' => sanitize($_POST['relationship'] ?? 'guardian'),
            'status' => sanitize($_POST['status'] ?? 'active')
        ];
        
        $result = $parentModel->update($id, $parentData);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Parent updated successfully',
                'redirect' => BASE_URL . '/parents/show/' . $id
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update parent']);
        }
    }
    
    /**
     * Delete parent
     */
    public function delete($id) {
        // Check if user is admin
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $parentModel = $this->model('ParentModel');

        // Check if parent exists
        $parent = $parentModel->findById($id);
        if (!$parent) {
            $this->json(['success' => false, 'message' => 'Parent not found']);
            return;
        }
        
        // Delete parent
        $result = $parentModel->delete($id);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => 'Parent deleted successfully',
                'redirect' => BASE_URL . '/parents'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete parent']);
        }
    }
}

