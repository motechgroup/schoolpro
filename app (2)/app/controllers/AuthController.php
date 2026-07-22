<?php
/**
 * Authentication Controller
 * Handles login, logout, and authentication-related operations
 */

class AuthController extends Controller {
    
    /**
     * Show login page
     */
    public function login() {
        // Redirect if already logged in
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $data = [
            'title' => 'Login - ' . APP_NAME,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('auth/login', $data);
    }
    
    /**
     * Process login
     */
    public function processLogin() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $selectedRole = sanitize($_POST['role'] ?? '');
        
        // Validate input
        if (empty($email) || empty($password) || empty($selectedRole)) {
            $this->json(['success' => false, 'message' => 'Role, email and password are required']);
            return;
        }
        
        if (!isValidEmail($email)) {
            $this->json(['success' => false, 'message' => 'Invalid email format']);
            return;
        }
        
        // Attempt login with role verification
        if (Auth::login($email, $password, $selectedRole)) {
            $user = Auth::user();
            
            // Log activity
            $this->logActivity('login', 'auth', "User logged in: {$user['email']}");
            
            // Redirect based on role
            $redirectUrl = $this->getDashboardUrl($user['role_name']);
            
            $this->json([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => $redirectUrl
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Invalid email or password']);
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        if (Auth::isLoggedIn()) {
            $user = Auth::user();
            $this->logActivity('logout', 'auth', "User logged out: {$user['email']}");
        }
        
        Auth::logout();
        $this->redirect('/auth/login');
    }
    
    /**
     * Get dashboard URL based on role
     */
    private function getDashboardUrl($role) {
        $role = strtolower($role);
        
        $routes = [
            'super_admin' => BASE_URL . '/dashboard',
            'school_manager' => BASE_URL . '/dashboard',
            'school_admin' => BASE_URL . '/dashboard',
            'head_teacher' => BASE_URL . '/dashboard',
            'teacher' => BASE_URL . '/dashboard',
            'accountant' => BASE_URL . '/dashboard',
            'receptionist' => BASE_URL . '/dashboard',
            'bursar' => BASE_URL . '/dashboard',
            'librarian' => BASE_URL . '/dashboard',
            'parent' => BASE_URL . '/parent/dashboard',
            'student' => BASE_URL . '/student/dashboard'
        ];
        
        return $routes[$role] ?? BASE_URL . '/dashboard';
    }
    
    /**
     * Show forgot password page
     */
    public function forgotPassword() {
        // Redirect if already logged in
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $data = [
            'title' => 'Forgot Password - ' . APP_NAME,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('auth/forgot_password', $data);
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $method = sanitize($_POST['method'] ?? 'email');
        
        if (empty($email) && empty($phone)) {
            $this->json(['success' => false, 'message' => 'Email or phone number is required']);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Find user by email or phone
        $user = null;
        if (!empty($email)) {
            $stmt = $db->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ? AND u.status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (!empty($phone)) {
            // Try to find user by phone (check in parents, teachers, etc.)
            $stmt = $db->prepare("SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                LEFT JOIN parents p ON u.email = p.email
                LEFT JOIN teachers t ON u.email = t.email
                WHERE (p.phone = ? OR t.phone = ?) AND u.status = 'active'
                LIMIT 1");
            $stmt->execute([$phone, $phone]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if (!$user) {
            // Don't reveal if user exists for security
            $this->json(['success' => true, 'message' => 'If the email/phone exists, a password reset link/code has been sent.']);
            return;
        }
        
        // Generate reset token and code
        $resetToken = bin2hex(random_bytes(32));
        $resetCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Invalidate any existing reset tokens for this user
        $stmt = $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL");
        $stmt->execute([$user['id']]);
        
        // Create new reset record
        $stmt = $db->prepare("INSERT INTO password_resets (user_id, email, phone, reset_token, reset_code, reset_method, expires_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['id'],
            $user['email'],
            $phone,
            $resetToken,
            $resetCode,
            $method,
            $expiresAt
        ]);
        
        $success = false;
        $messages = [];
        
        // Send email if method includes email
        if ($method === 'email' || $method === 'both') {
            $emailSent = $this->sendPasswordResetEmail($user['email'], $resetToken, $user);
            if ($emailSent) {
                $success = true;
                $messages[] = 'Password reset link sent to your email.';
            }
        }
        
        // Send SMS if method includes SMS
        if ($method === 'sms' || $method === 'both') {
            $smsSent = $this->sendPasswordResetSms($phone ?: $this->getUserPhone($user['id']), $resetCode, $user);
            if ($smsSent) {
                $success = true;
                $messages[] = 'Password reset code sent to your phone.';
            }
        }
        
        if ($success) {
            $this->json(['success' => true, 'message' => implode(' ', $messages)]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to send password reset. Please try again.']);
        }
    }
    
    /**
     * Show reset password page
     */
    public function resetPassword() {
        $token = sanitize($_GET['token'] ?? '');
        $code = sanitize($_GET['code'] ?? '');
        
        if (empty($token) && empty($code)) {
            $this->setFlash('error', 'Invalid reset link or code.');
            $this->redirect('/auth/forgot-password');
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        $reset = null;
        
        if (!empty($token)) {
            $stmt = $db->prepare("SELECT pr.*, u.email, u.id as user_id 
                                  FROM password_resets pr 
                                  JOIN users u ON pr.user_id = u.id 
                                  WHERE pr.reset_token = ? AND pr.used_at IS NULL AND pr.expires_at > NOW()");
            $stmt->execute([$token]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (!empty($code)) {
            $stmt = $db->prepare("SELECT pr.*, u.email, u.id as user_id 
                                  FROM password_resets pr 
                                  JOIN users u ON pr.user_id = u.id 
                                  WHERE pr.reset_code = ? AND pr.used_at IS NULL AND pr.expires_at > NOW()");
            $stmt->execute([$code]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if (!$reset) {
            $this->setFlash('error', 'Invalid or expired reset link/code. Please request a new one.');
            $this->redirect('/auth/forgot-password');
            return;
        }
        
        $data = [
            'title' => 'Reset Password - ' . APP_NAME,
            'token' => $token,
            'code' => $code,
            'reset_id' => $reset['id'],
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('auth/reset_password', $data);
    }
    
    /**
     * Process password reset
     */
    public function processPasswordReset() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $resetId = intval($_POST['reset_id'] ?? 0);
        $token = sanitize($_POST['token'] ?? '');
        $code = sanitize($_POST['code'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($newPassword) || empty($confirmPassword)) {
            $this->json(['success' => false, 'message' => 'Password fields are required']);
            return;
        }
        
        if ($newPassword !== $confirmPassword) {
            $this->json(['success' => false, 'message' => 'Passwords do not match']);
            return;
        }
        
        if (strlen($newPassword) < 6) {
            $this->json(['success' => false, 'message' => 'Password must be at least 6 characters']);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Verify reset token/code
        $reset = null;
        if (!empty($token)) {
            $stmt = $db->prepare("SELECT * FROM password_resets WHERE id = ? AND reset_token = ? AND used_at IS NULL AND expires_at > NOW()");
            $stmt->execute([$resetId, $token]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (!empty($code)) {
            $stmt = $db->prepare("SELECT * FROM password_resets WHERE id = ? AND reset_code = ? AND used_at IS NULL AND expires_at > NOW()");
            $stmt->execute([$resetId, $code]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if (!$reset) {
            $this->json(['success' => false, 'message' => 'Invalid or expired reset link/code']);
            return;
        }
        
        // Update password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($updateStmt->execute([$newHash, $reset['user_id']])) {
            // Mark reset as used
            $stmt = $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?");
            $stmt->execute([$reset['id']]);
            
            $this->json([
                'success' => true,
                'message' => 'Password reset successfully. You can now login with your new password.',
                'redirect' => BASE_URL . '/auth/login'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to reset password. Please try again.']);
        }
    }
    
    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail($email, $token, $user) {
        try {
            require_once APP_PATH . '/helpers/EmailHelper.php';
            $emailHelper = new EmailHelper();
            
            if (!$emailHelper->isPHPMailerAvailable()) {
                return false;
            }
            
            $resetUrl = BASE_URL . '/auth/reset-password?token=' . $token;
            $schoolName = getSchoolName();
            
            $subject = 'Password Reset Request - ' . $schoolName;
            $message = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">Password Reset Request</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">Hello,</p>
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                We received a request to reset your password for your account at <strong>' . htmlspecialchars($schoolName) . '</strong>.
                            </p>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="' . htmlspecialchars($resetUrl) . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">Reset Password</a>
                            </div>
                            <p style="margin: 20px 0 0 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                Or copy and paste this link into your browser:<br>
                                <a href="' . htmlspecialchars($resetUrl) . '" style="color: #667eea; word-break: break-all;">' . htmlspecialchars($resetUrl) . '</a>
                            </p>
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;">
                                    <strong>⚠️ This link will expire in 1 hour.</strong> If you did not request a password reset, please ignore this email.
                                </p>
                            </div>
                            <p style="margin: 20px 0 0 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Best regards,<br>
                                <strong style="color: #667eea;">' . htmlspecialchars($schoolName) . '</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px; line-height: 1.5;">
                                This is an automated email. Please do not reply to this message.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
            
            return $emailHelper->sendEmailWithPHPMailer($email, $subject, $message, true);
        } catch (Exception $e) {
            error_log("Password reset email error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send password reset SMS
     */
    private function sendPasswordResetSms($phone, $code, $user) {
        try {
            require_once APP_PATH . '/helpers/SmsHelper.php';
            $smsHelper = new SmsHelper();
            
            $schoolName = getSchoolName();
            $message = "Your password reset code for {$schoolName} is: {$code}. This code expires in 1 hour. Do not share this code with anyone.";
            
            $result = $smsHelper->sendSms($phone, $message);
            return $result['success'] ?? false;
        } catch (Exception $e) {
            error_log("Password reset SMS error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user phone number
     */
    private function getUserPhone($userId) {
        $db = Database::getInstance()->getConnection();
        
        // Try to get phone from parents table
        $stmt = $db->prepare("SELECT p.phone FROM parents p JOIN users u ON p.email = u.email WHERE u.id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && !empty($result['phone'])) {
            return $result['phone'];
        }
        
        // Try to get phone from teachers table
        $stmt = $db->prepare("SELECT t.phone FROM teachers t JOIN users u ON t.email = u.email WHERE u.id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && !empty($result['phone'])) {
            return $result['phone'];
        }
        
        return null;
    }
    
    /**
     * Log activity
     */
    private function logActivity($action, $module, $description) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, module, description, ip_address, user_agent) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            Auth::userId(),
            $action,
            $module,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}

