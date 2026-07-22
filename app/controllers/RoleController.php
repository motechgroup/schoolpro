<?php
/**
 * Role Management Controller
 * Handles role and permission management (Super Admin only)
 */

class RoleController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasRole('super_admin')) {
            http_response_code(403);
            die("Access denied. Super Admin only.");
        }
    }
    
    /**
     * List all roles
     */
    public function index() {
        $roleModel = $this->model('Role');
        $roles = $roleModel->getAllWithDetails();
        
        $data = [
            'title' => 'Role Management - ' . APP_NAME,
            'roles' => $roles
        ];
        
        $this->view('roles/index', $data);
    }
    
    /**
     * Show role details and permissions
     */
    public function show($id) {
        $roleModel = $this->model('Role');
        
        // Validate ID
        $id = intval($id);
        if ($id <= 0) {
            $this->setFlash('error', 'Invalid role ID');
            $this->redirect('/roles');
            return;
        }
        
        // Try to get role - use getAllWithDetails and filter, since that works
        $allRoles = $roleModel->getAllWithDetails();
        $role = null;
        
        // Search through all roles to find the one with matching ID
        foreach ($allRoles as $r) {
            // Check both string and int comparison
            $roleId = isset($r['id']) ? intval($r['id']) : 0;
            if ($roleId === $id) {
                $role = $r;
                break;
            }
        }
        
        // If still not found, try direct query
        if (!$role || !is_array($role)) {
            $role = $roleModel->getRoleWithPermissions($id);
        }
        
        // Final check - if still not found, show error
        if (!$role || !is_array($role) || !isset($role['id'])) {
            $availableIds = [];
            foreach ($allRoles as $r) {
                if (isset($r['id'])) {
                    $roleName = isset($r['name']) ? str_replace('_', ' ', $r['name']) : 'unknown';
                    $availableIds[] = 'ID ' . $r['id'] . ': ' . ucwords($roleName);
                }
            }
            
            $errorMsg = 'Role ID ' . $id . ' not found. ';
            if (!empty($availableIds)) {
                $errorMsg .= 'Available roles: ' . implode(', ', $availableIds);
            } else {
                $errorMsg .= 'No roles found in database. Please run database migrations.';
            }
            
            $this->setFlash('error', $errorMsg);
            $this->redirect('/roles');
            return;
        }
        
        // Ensure permissions is always an array
        if (!isset($role['permissions']) || !is_array($role['permissions'])) {
            if (is_string($role['permissions'])) {
                $decoded = json_decode($role['permissions'], true);
                $role['permissions'] = $decoded !== null ? $decoded : [];
            } else {
                $role['permissions'] = [];
            }
        }
        
        // Ensure role has all required fields
        if (!isset($role['name'])) {
            $role['name'] = '';
        }
        if (!isset($role['description'])) {
            $role['description'] = '';
        }
        if (!isset($role['id'])) {
            $this->setFlash('error', 'Invalid role data');
            $this->redirect('/roles');
            return;
        }
        
        // Get users with this role
        $userModel = $this->model('User');
        $users = $userModel->getByRole($role['name']);
        
        // Get available permissions
        $availablePermissions = Role::getAvailablePermissions();
        
        $data = [
            'title' => 'Role Permissions - ' . APP_NAME,
            'roleData' => $role,  // Changed from 'role' to 'roleData' to avoid conflicts
            'users' => $users,
            'availablePermissions' => $availablePermissions,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('roles/show', $data);
    }
    
    /**
     * Update role permissions
     */
    public function updatePermissions($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $roleModel = $this->model('Role');
        $role = $roleModel->findById($id);
        
        if (!$role) {
            $this->json(['success' => false, 'message' => 'Role not found']);
            return;
        }
        
        // Prevent modifying super_admin permissions
        if ($role['name'] === 'super_admin') {
            $this->json(['success' => false, 'message' => 'Cannot modify super admin permissions']);
            return;
        }
        
        // Get selected permissions
        $permissions = $_POST['permissions'] ?? [];
        
        // If "*" is selected, grant all permissions
        if (in_array('*', $permissions)) {
            $permissions = ['*'];
        } else {
            // Validate permissions
            $availablePermissions = array_keys(Role::getAvailablePermissions());
            $permissions = array_intersect($permissions, $availablePermissions);
        }
        
        if ($roleModel->updatePermissions($id, $permissions)) {
            $this->json([
                'success' => true,
                'message' => 'Permissions updated successfully',
                'redirect' => BASE_URL . '/roles/show/' . $id
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update permissions']);
        }
    }
    
    /**
     * Edit role details
     */
    public function edit($id) {
        $roleModel = $this->model('Role');
        $role = $roleModel->findById($id);
        
        if (!$role) {
            $this->setFlash('error', 'Role not found');
            $this->redirect('/roles');
            return;
        }
        
        // Prevent editing super_admin
        if ($role['name'] === 'super_admin') {
            $this->setFlash('error', 'Cannot edit super admin role');
            $this->redirect('/roles');
            return;
        }
        
        $data = [
            'title' => 'Edit Role - ' . APP_NAME,
            'role' => $role,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('roles/edit', $data);
    }
    
    /**
     * Update role details
     */
    public function update($id) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $roleModel = $this->model('Role');
        $role = $roleModel->findById($id);
        
        if (!$role) {
            $this->json(['success' => false, 'message' => 'Role not found']);
            return;
        }
        
        // Prevent editing super_admin
        if ($role['name'] === 'super_admin') {
            $this->json(['success' => false, 'message' => 'Cannot edit super admin role']);
            return;
        }
        
        $roleData = [
            'description' => sanitize($_POST['description'] ?? '')
        ];
        
        if ($roleModel->update($id, $roleData)) {
            $this->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'redirect' => BASE_URL . '/roles/show/' . $id
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update role']);
        }
    }
}

