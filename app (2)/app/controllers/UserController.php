<?php
/**
 * User Management Controller
 * Handles user CRUD operations (Super Admin only)
 */

class UserController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasRole('super_admin')) {
            http_response_code(403);
            die("Access denied. Super Admin only.");
        }
    }
    
    /**
     * List all users
     */
    public function index() {
        $userModel = $this->model('User');
        $db = Database::getInstance()->getConnection();
        
        // Get filters
        $filters = [
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        // Get all roles for filter dropdown
        $rolesStmt = $db->query("SELECT * FROM roles ORDER BY name");
        $roles = $rolesStmt->fetchAll();
        
        // Get users
        $users = $userModel->getAllWithRoles($filters);
        
        // Get user counts by role
        $userCounts = $userModel->getCountByRole();
        
        $data = [
            'title' => 'User Management - ' . APP_NAME,
            'users' => $users,
            'roles' => $roles,
            'filters' => $filters,
            'userCounts' => $userCounts
        ];
        
        $this->view('users/index', $data);
    }
    
    /**
     * Show create user form
     */
    public function create() {
        $db = Database::getInstance()->getConnection();
        
        // Get all roles
        $stmt = $db->query("SELECT * FROM roles ORDER BY name");
        $roles = $stmt->fetchAll();
        
        $data = [
            'title' => 'Create User - ' . APP_NAME,
            'roles' => $roles,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('users/create', $data);
    }
    
    /**
     * Store new user
     */
    public function store() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $userModel = $this->model('User');
        $db = Database::getInstance()->getConnection();
        
        // Validate input
        $errors = [];
        if (empty($_POST['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        if (empty($_POST['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        if (empty($_POST['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!isValidEmail($_POST['email'])) {
            $errors['email'] = 'Invalid email format';
        } elseif ($userModel->emailExists($_POST['email'])) {
            $errors['email'] = 'Email already exists';
        }
        if (empty($_POST['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($_POST['password']) < PASSWORD_MIN_LENGTH) {
            $errors['password'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        }
        if (empty($_POST['role_id'])) {
            $errors['role_id'] = 'Role is required';
        }
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        // Verify role exists
        $roleStmt = $db->prepare("SELECT id FROM roles WHERE id = ?");
        $roleStmt->execute([$_POST['role_id']]);
        if (!$roleStmt->fetch()) {
            $this->json(['success' => false, 'message' => 'Invalid role']);
            return;
        }
        
        try {
            // Create user
            $userData = [
                'role_id' => intval($_POST['role_id']),
                'email' => sanitize($_POST['email']),
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'first_name' => sanitize($_POST['first_name']),
                'last_name' => sanitize($_POST['last_name']),
                'phone' => !empty($_POST['phone']) ? formatPhone(sanitize($_POST['phone'])) : null,
                'status' => sanitize($_POST['status'] ?? 'active')
            ];
            
            $userId = $userModel->create($userData);
            
            $this->json([
                'success' => true,
                'message' => 'User created successfully',
                'redirect' => BASE_URL . '/users/show/' . $userId
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to create user: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show user details
     */
    public function show($id) {
        $userModel = $this->model('User');
        $user = $userModel->getUserWithRole($id);
        
        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/users');
            return;
        }
        
        // Get role ID for permission management link
        $db = Database::getInstance()->getConnection();
        $roleStmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
        $roleStmt->execute([$user['role_name']]);
        $roleData = $roleStmt->fetch();
        $user['role_id_for_permissions'] = $roleData['id'] ?? null;
        
        // Get related data based on role
        $relatedData = [];
        if ($user['role_name'] === 'teacher') {
            $teacherModel = $this->model('Teacher');
            $teacher = $teacherModel->findByUserId($user['id']);
            if ($teacher) {
                $relatedData['teacher'] = $teacher;
                $relatedData['assignedClasses'] = $teacherModel->getAssignedClasses($teacher['id']);
            }
        }
        
        $data = [
            'title' => 'User Details - ' . APP_NAME,
            'user' => $user,
            'relatedData' => $relatedData
        ];
        
        $this->view('users/show', $data);
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $userModel = $this->model('User');
        $db = Database::getInstance()->getConnection();
        
        $user = $userModel->getUserWithRole($id);
        
        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/users');
            return;
        }
        
        // Get all roles
        $stmt = $db->query("SELECT * FROM roles ORDER BY name");
        $roles = $stmt->fetchAll();
        
        $data = [
            'title' => 'Edit User - ' . APP_NAME,
            'user' => $user,
            'roles' => $roles,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('users/edit', $data);
    }
    
    /**
     * Update user
     */
    public function update($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $userModel = $this->model('User');
        $user = $userModel->findById($id);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found']);
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
        if (empty($_POST['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!isValidEmail($_POST['email'])) {
            $errors['email'] = 'Invalid email format';
        } elseif ($userModel->emailExists($_POST['email'], $id)) {
            $errors['email'] = 'Email already exists';
        }
        if (empty($_POST['role_id'])) {
            $errors['role_id'] = 'Role is required';
        }
        
        if (!empty($errors)) {
            $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            return;
        }
        
        // Prepare update data
        $userData = [
            'role_id' => intval($_POST['role_id']),
            'email' => sanitize($_POST['email']),
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'phone' => !empty($_POST['phone']) ? formatPhone(sanitize($_POST['phone'])) : null,
            'status' => sanitize($_POST['status'] ?? 'active')
        ];
        
        // Update password if provided
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < PASSWORD_MIN_LENGTH) {
                $this->json(['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters']);
                return;
            }
            $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        if ($userModel->update($id, $userData)) {
            $this->json([
                'success' => true,
                'message' => 'User updated successfully',
                'redirect' => BASE_URL . '/users/show/' . $id
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update user']);
        }
    }
    
    /**
     * Delete user (soft delete)
     */
    public function delete($id) {
        // Prevent deleting yourself
        if ($id == Auth::userId()) {
            $this->json(['success' => false, 'message' => 'You cannot delete your own account']);
            return;
        }
        
        $userModel = $this->model('User');
        $user = $userModel->findById($id);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        // Soft delete
        if ($userModel->update($id, ['status' => 'inactive'])) {
            $this->json(['success' => true, 'message' => 'User deactivated successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to deactivate user']);
        }
    }
}

