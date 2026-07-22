<?php
/**
 * Profile Controller
 * Handles user profile management
 */

class ProfileController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
    }
    
    /**
     * Show user profile
     */
    public function index() {
        $user = Auth::user();
        
        $data = [
            'title' => 'My Profile - ' . APP_NAME,
            'user' => $user,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('profile/index', $data);
    }
    
    /**
     * Update profile
     */
    public function update() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $user = Auth::user();
        $db = Database::getInstance()->getConnection();
        
        $data = [
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? '')
        ];
        
        // Validate
        if (empty($data['first_name']) || empty($data['last_name'])) {
            $this->json(['success' => false, 'message' => 'First name and last name are required']);
            return;
        }
        
        // Update user
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$data['first_name'], $data['last_name'], $data['phone'], $user['id']])) {
            $this->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'redirect' => BASE_URL . '/profile'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update profile']);
        }
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->json(['success' => false, 'message' => 'All password fields are required']);
            return;
        }
        
        if ($newPassword !== $confirmPassword) {
            $this->json(['success' => false, 'message' => 'New passwords do not match']);
            return;
        }
        
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            $this->json(['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters']);
            return;
        }
        
        $user = Auth::user();
        $db = Database::getInstance()->getConnection();
        
        // Verify current password
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch();
        
        if (!password_verify($currentPassword, $userData['password'])) {
            $this->json(['success' => false, 'message' => 'Current password is incorrect']);
            return;
        }
        
        // Update password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($updateStmt->execute([$newHash, $user['id']])) {
            $this->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to change password']);
        }
    }
}

