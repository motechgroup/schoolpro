<?php
/**
 * Fee Head Controller
 * Manages fee heads (lunch, transport, tuition, etc.)
 */

class FeeHeadController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * List all fee heads
     */
    public function index() {
        $feeHeadModel = $this->model('FeeHead');
        $feeHeads = $feeHeadModel->findAll([], 'name ASC');
        
        $data = [
            'title' => 'Fee Heads Management - ' . APP_NAME,
            'feeHeads' => $feeHeads
        ];
        
        $this->view('feeheads/index', $data);
    }
    
    /**
     * Show create form
     */
    public function create() {
        $data = [
            'title' => 'Create Fee Head - ' . APP_NAME,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('feeheads/create', $data);
    }
    
    /**
     * Store new fee head
     */
    public function store() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $feeHeadModel = $this->model('FeeHead');
        
        // Validate input
        $errors = [];
        if (empty($_POST['code'])) {
            $errors['code'] = 'Fee head code is required';
        }
        if (empty($_POST['name'])) {
            $errors['name'] = 'Fee head name is required';
        }
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        // Check if code already exists
        $existing = $feeHeadModel->query("SELECT * FROM fee_heads WHERE code = ?", [strtoupper($_POST['code'])])->fetch();
        
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Fee head code already exists']);
            return;
        }
        
        $data = [
            'code' => strtoupper(sanitize($_POST['code'])),
            'name' => sanitize($_POST['name']),
            'description' => sanitize($_POST['description'] ?? ''),
            'default_amount' => !empty($_POST['default_amount']) ? floatval($_POST['default_amount']) : 0.00,
            'is_mandatory' => isset($_POST['is_mandatory']) ? 1 : 0,
            'status' => 'active'
        ];
        
        $id = $feeHeadModel->create($data);
        
        if ($id) {
            $this->json([
                'success' => true,
                'message' => 'Fee head created successfully',
                'redirect' => BASE_URL . '/feeheads'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create fee head']);
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $feeHeadModel = $this->model('FeeHead');
        $feeHead = $feeHeadModel->findById($id);
        
        if (!$feeHead) {
            $this->setFlash('error', 'Fee head not found');
            $this->redirect('/feeheads');
            return;
        }
        
        $data = [
            'title' => 'Edit Fee Head - ' . APP_NAME,
            'feeHead' => $feeHead,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('feeheads/edit', $data);
    }
    
    /**
     * Update fee head
     */
    public function update($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $feeHeadModel = $this->model('FeeHead');
        
        $data = [
            'code' => strtoupper(sanitize($_POST['code'])),
            'name' => sanitize($_POST['name']),
            'description' => sanitize($_POST['description'] ?? ''),
            'default_amount' => !empty($_POST['default_amount']) ? floatval($_POST['default_amount']) : 0.00,
            'is_mandatory' => isset($_POST['is_mandatory']) ? 1 : 0,
            'status' => sanitize($_POST['status'] ?? 'active')
        ];
        
        if ($feeHeadModel->update($id, $data)) {
            $this->json([
                'success' => true,
                'message' => 'Fee head updated successfully',
                'redirect' => BASE_URL . '/feeheads'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update fee head']);
        }
    }
    
    /**
     * Delete fee head
     */
    public function delete($id) {
        $feeHeadModel = $this->model('FeeHead');
        
        // Check if fee head is assigned to any students
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM student_fee_heads WHERE fee_head_id = ? AND status = 'active'");
        $stmt->execute([$id]);
        $inUse = $stmt->fetch()['count'] > 0;
        
        if ($inUse) {
            $this->json(['success' => false, 'message' => 'Cannot delete fee head that is assigned to students']);
            return;
        }
        
        // Soft delete
        if ($feeHeadModel->update($id, ['status' => 'inactive'])) {
            $this->json(['success' => true, 'message' => 'Fee head deleted successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete fee head']);
        }
    }
}

