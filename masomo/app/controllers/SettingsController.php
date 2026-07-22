<?php
/**
 * Settings Controller
 * Handles application settings management
 */

class SettingsController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * Settings index page
     */
    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // Get all settings
        $stmt = $db->query("SELECT * FROM settings ORDER BY setting_key");
        $settings = $stmt->fetchAll();
        
        $settingsMap = [];
        foreach ($settings as $setting) {
            $settingsMap[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $data = [
            'title' => 'System Settings - ' . APP_NAME,
            'settings' => $settingsMap,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('settings/index', $data);
    }
    
    /**
     * Save settings
     */
    public function save() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        $settings = [
            'school_name' => sanitize($_POST['school_name'] ?? ''),
            'school_address' => sanitize($_POST['school_address'] ?? ''),
            'school_phone' => sanitize($_POST['school_phone'] ?? ''),
            'school_email' => sanitize($_POST['school_email'] ?? '')
        ];
        
        $db->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) 
                                     VALUES (?, ?, NOW()) 
                                     ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
                $stmt->execute([$key, $value, $value]);
            }
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Settings saved successfully']);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Failed to save settings: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Upload school logo
     */
    public function uploadLogo() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'No file uploaded or upload error']);
            return;
        }
        
        $file = $_FILES['logo'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $this->json(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.']);
            return;
        }
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            $this->json(['success' => false, 'message' => 'File size exceeds 2MB limit.']);
            return;
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'school_logo_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = APP_PATH . '/../public/uploads/' . $filename;
        
        // Create uploads directory if it doesn't exist
        $uploadsDir = APP_PATH . '/../public/uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Delete old logo if exists
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'school_logo'");
            $stmt->execute();
            $oldLogo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($oldLogo && !empty($oldLogo['setting_value']) && $oldLogo['setting_value'] !== $filename) {
                $oldPath = $uploadsDir . $oldLogo['setting_value'];
                if (file_exists($oldPath) && strpos($oldLogo['setting_value'], 'school_logo_') === 0) {
                    @unlink($oldPath);
                }
            }
            
            // Save to database
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) 
                                 VALUES ('school_logo', ?, NOW()) 
                                 ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
            $stmt->execute([$filename, $filename]);
            
            $this->json([
                'success' => true, 
                'message' => 'Logo uploaded successfully',
                'logo_url' => BASE_URL . '/public/uploads/' . $filename
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to upload logo']);
        }
    }
    
    /**
     * Get payment settings
     */
    public function getPaymentSettings() {
        $db = Database::getInstance()->getConnection();
        
        $paymentKeys = [
            'mpesa_paybill_number',
            'mpesa_paybill_account_prefix',
            'mpesa_api_consumer_key',
            'mpesa_api_consumer_secret',
            'mpesa_api_passkey',
            'mpesa_api_shortcode',
            'mpesa_environment',
            'mpesa_callback_url',
            'equity_bank_account',
            'equity_bank_name',
            'jenga_api_key',
            'jenga_api_secret',
            'jenga_merchant_code',
            'jenga_environment',
            'jenga_auto_reconcile',
            'coop_bank_account',
            'coop_bank_name',
            'kcb_bank_account',
            'kcb_bank_name',
            'family_bank_account',
            'family_bank_name',
            'payment_auto_reconcile'
        ];
        
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('" . implode("','", $paymentKeys) . "')");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($results as $result) {
            $settings[$result['setting_key']] = $result['setting_value'];
        }
        
        $this->json(['success' => true, 'settings' => $settings]);
    }
    
    /**
     * Save payment settings
     */
    public function savePayment() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        $settings = [
            'mpesa_paybill_number' => sanitize($_POST['mpesa_paybill_number'] ?? ''),
            'mpesa_paybill_account_prefix' => sanitize($_POST['mpesa_paybill_account_prefix'] ?? ''),
            'mpesa_api_consumer_key' => sanitize($_POST['mpesa_api_consumer_key'] ?? ''),
            'mpesa_api_consumer_secret' => sanitize($_POST['mpesa_api_consumer_secret'] ?? ''),
            'mpesa_api_passkey' => sanitize($_POST['mpesa_api_passkey'] ?? ''),
            'mpesa_api_shortcode' => sanitize($_POST['mpesa_api_shortcode'] ?? ''),
            'mpesa_environment' => sanitize($_POST['mpesa_environment'] ?? 'sandbox'),
            'mpesa_callback_url' => sanitize($_POST['mpesa_callback_url'] ?? ''),
            'equity_bank_account' => sanitize($_POST['equity_bank_account'] ?? ''),
            'equity_bank_name' => sanitize($_POST['equity_bank_name'] ?? 'Equity Bank'),
            'jenga_api_key' => sanitize($_POST['jenga_api_key'] ?? ''),
            'jenga_api_secret' => sanitize($_POST['jenga_api_secret'] ?? ''),
            'jenga_merchant_code' => sanitize($_POST['jenga_merchant_code'] ?? ''),
            'jenga_environment' => sanitize($_POST['jenga_environment'] ?? 'sandbox'),
            'jenga_auto_reconcile' => isset($_POST['jenga_auto_reconcile']) ? '1' : '0',
            'coop_bank_account' => sanitize($_POST['coop_bank_account'] ?? ''),
            'coop_bank_name' => sanitize($_POST['coop_bank_name'] ?? 'Co-operative Bank'),
            'kcb_bank_account' => sanitize($_POST['kcb_bank_account'] ?? ''),
            'kcb_bank_name' => sanitize($_POST['kcb_bank_name'] ?? 'Kenya Commercial Bank'),
            'family_bank_account' => sanitize($_POST['family_bank_account'] ?? ''),
            'family_bank_name' => sanitize($_POST['family_bank_name'] ?? 'Family Bank'),
            'payment_auto_reconcile' => isset($_POST['payment_auto_reconcile']) ? '1' : '0'
        ];
        
        $db->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) 
                                     VALUES (?, ?, NOW()) 
                                     ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
                $stmt->execute([$key, $value, $value]);
            }
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Payment settings saved successfully']);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Failed to save payment settings: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Save SMTP settings
     */
    public function saveSmtp() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        $settings = [
            'smtp_host' => sanitize($_POST['smtp_host'] ?? ''),
            'smtp_port' => sanitize($_POST['smtp_port'] ?? '587'),
            'smtp_username' => sanitize($_POST['smtp_username'] ?? ''),
            'smtp_password' => sanitize($_POST['smtp_password'] ?? ''),
            'smtp_encryption' => sanitize($_POST['smtp_encryption'] ?? 'tls'),
            'smtp_from_email' => sanitize($_POST['smtp_from_email'] ?? ''),
            'smtp_from_name' => sanitize($_POST['smtp_from_name'] ?? '')
        ];
        
        $db->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) 
                                     VALUES (?, ?, NOW()) 
                                     ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
                $stmt->execute([$key, $value, $value]);
            }
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'SMTP settings saved successfully']);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Failed to save SMTP settings: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Remove school logo
     */
    public function removeLogo() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        try {
            // Get current logo
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'school_logo'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Delete logo file if exists
            if ($result && !empty($result['setting_value'])) {
                $logoPath = APP_PATH . '/../public/uploads/' . $result['setting_value'];
                if (file_exists($logoPath) && strpos($result['setting_value'], 'school_logo_') === 0) {
                    @unlink($logoPath);
                }
            }
            
            // Remove from database
            $stmt = $db->prepare("DELETE FROM settings WHERE setting_key = 'school_logo'");
            $stmt->execute();
            
            $this->json(['success' => true, 'message' => 'Logo removed successfully']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to remove logo: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Upload dashboard logo
     */
    public function uploadDashboardLogo() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        if (!isset($_FILES['dashboard_logo']) || $_FILES['dashboard_logo']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'No file uploaded or upload error']);
            return;
        }
        
        $file = $_FILES['dashboard_logo'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $this->json(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.']);
            return;
        }
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            $this->json(['success' => false, 'message' => 'File size exceeds 2MB limit.']);
            return;
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'dashboard_logo_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = APP_PATH . '/../public/uploads/' . $filename;
        
        // Create uploads directory if it doesn't exist
        $uploadsDir = APP_PATH . '/../public/uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Resize image to exact dimensions (367x76)
            if (!$this->resizeImageExact($uploadPath, 367, 76)) {
                @unlink($uploadPath);
                $this->json(['success' => false, 'message' => 'Failed to resize dashboard logo to required dimensions (367x76 pixels)']);
                return;
            }
            
            // Delete old dashboard logo if exists
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'dashboard_logo'");
            $stmt->execute();
            $oldLogo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($oldLogo && !empty($oldLogo['setting_value']) && $oldLogo['setting_value'] !== $filename) {
                $oldPath = $uploadsDir . $oldLogo['setting_value'];
                if (file_exists($oldPath) && strpos($oldLogo['setting_value'], 'dashboard_logo_') === 0) {
                    @unlink($oldPath);
                }
            }
            
            // Save to database
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) 
                                 VALUES ('dashboard_logo', ?, NOW()) 
                                 ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
            $stmt->execute([$filename, $filename]);
            
            $this->json([
                'success' => true, 
                'message' => 'Dashboard logo uploaded and resized successfully',
                'logo_url' => BASE_URL . '/public/uploads/' . $filename
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to upload dashboard logo']);
        }
    }
    
    /**
     * Remove dashboard logo
     */
    public function removeDashboardLogo() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        try {
            // Get current logo
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'dashboard_logo'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Delete logo file if exists
            if ($result && !empty($result['setting_value'])) {
                $logoPath = APP_PATH . '/../public/uploads/' . $result['setting_value'];
                if (file_exists($logoPath) && strpos($result['setting_value'], 'dashboard_logo_') === 0) {
                    @unlink($logoPath);
                }
            }
            
            // Remove from database
            $stmt = $db->prepare("DELETE FROM settings WHERE setting_key = 'dashboard_logo'");
            $stmt->execute();
            
            $this->json(['success' => true, 'message' => 'Dashboard logo removed successfully']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to remove dashboard logo: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Resize image to exact dimensions (crops or fits to exact size)
     */
    private function resizeImageExact($filePath, $targetWidth, $targetHeight) {
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }
        
        $mimeType = $imageInfo['mime'];
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Create image resource based on type
        switch ($mimeType) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($filePath);
                break;
            default:
                return false;
        }
        
        if (!$source) {
            return false;
        }
        
        // Calculate scaling to fill target dimensions (maintain aspect ratio, crop if needed)
        $ratio = max($targetWidth / $width, $targetHeight / $height);
        $srcWidth = (int)($targetWidth / $ratio);
        $srcHeight = (int)($targetHeight / $ratio);
        
        // Center the crop area
        $srcX = (int)(($width - $srcWidth) / 2);
        $srcY = (int)(($height - $srcHeight) / 2);
        
        // Create new image with exact dimensions
        $destination = imagecreatetruecolor($targetWidth, $targetHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $targetWidth, $targetHeight, $transparent);
        } else {
            // For JPEG, use white background
            $white = imagecolorallocate($destination, 255, 255, 255);
            imagefilledrectangle($destination, 0, 0, $targetWidth, $targetHeight, $white);
        }
        
        // Enable alpha blending for final render
        imagealphablending($destination, true);
        
        // Resize and crop image
        imagecopyresampled($destination, $source, 0, 0, $srcX, $srcY, $targetWidth, $targetHeight, $srcWidth, $srcHeight);
        
        // Save resized image
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($destination, $filePath, 90);
                break;
            case 'image/png':
                imagepng($destination, $filePath, 8);
                break;
            case 'image/gif':
                imagegif($destination, $filePath);
                break;
            default:
                imagedestroy($source);
                imagedestroy($destination);
                return false;
        }
        
        // Clean up
        imagedestroy($source);
        imagedestroy($destination);
        
        return true;
    }
    
    /**
     * Send test email to verify SMTP configuration
     */
    public function testEmail() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        require_once APP_PATH . '/helpers/EmailHelper.php';
        
        $testEmail = sanitize($_POST['test_email'] ?? '');
        
        // If no test email provided, use the SMTP username
        if (empty($testEmail)) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'smtp_username'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $testEmail = $result['setting_value'] ?? '';
        }
        
        if (empty($testEmail)) {
            $this->json(['success' => false, 'message' => 'Please provide a test email address or configure SMTP username']);
            return;
        }
        
        // Validate email format
        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Invalid email address format']);
            return;
        }
        
        try {
            $emailHelper = new EmailHelper();
            
            // Get school name
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'school_name'");
            $stmt->execute();
            $schoolResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $schoolName = $schoolResult['setting_value'] ?? APP_NAME;
            
            $subject = "Test Email - " . $schoolName . " - " . date('Y-m-d H:i:s');
            $message = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <h2 style='color: #2c3e50;'>SMTP Configuration Test</h2>
                        <p>This is a test email from <strong>{$schoolName}</strong> to verify that your SMTP settings are configured correctly.</p>
                        <p><strong>Test Details:</strong></p>
                        <ul>
                            <li>Sent at: " . date('Y-m-d H:i:s') . "</li>
                            <li>From: " . ($emailHelper->getFromEmail() ?? 'System') . "</li>
                            <li>To: {$testEmail}</li>
                        </ul>
                        <p>If you received this email, your SMTP configuration is working correctly!</p>
                        <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;'>
                            This is an automated test email from " . APP_NAME . ".
                        </p>
                    </div>
                </body>
                </html>
            ";
            
            // Check if PHPMailer is available
            if (!$emailHelper->isPHPMailerAvailable()) {
                $this->json([
                    'success' => false,
                    'message' => 'PHPMailer is not installed. To use SMTP email, please install PHPMailer by running: <code>composer require phpmailer/phpmailer</code> in your project directory. The basic PHP mail() function cannot use SMTP settings and requires a local mail server.',
                    'needs_phpmailer' => true
                ]);
                return;
            }
            
            // Try PHPMailer
            $result = false;
            $errorMessage = '';
            
            try {
                $result = $emailHelper->sendEmailWithPHPMailer($testEmail, $subject, $message, true);
            } catch (Exception $emailException) {
                $errorMessage = $emailException->getMessage();
                error_log("Email sending exception: " . $errorMessage);
            }
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => 'Test email sent successfully to ' . $testEmail . '. Please check your inbox (and spam folder).'
                ]);
            } else {
                // Provide more detailed error message
                $detailedMessage = 'Failed to send test email. ';
                
                if (!empty($errorMessage)) {
                    $detailedMessage .= $errorMessage;
                } else {
                    $detailedMessage .= 'Please check: ';
                    $detailedMessage .= '1) SMTP Host, Port, and Encryption settings are correct. ';
                    $detailedMessage .= '2) Username and Password are correct (use App Password for Gmail). ';
                    $detailedMessage .= '3) Your server allows outbound SMTP connections. ';
                    $detailedMessage .= '4) Check server error logs for more details.';
                }
                
                $this->json([
                    'success' => false,
                    'message' => $detailedMessage
                ]);
            }
        } catch (Exception $e) {
            error_log("Test email error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error sending test email: ' . $e->getMessage()
            ]);
        }
    }
}

