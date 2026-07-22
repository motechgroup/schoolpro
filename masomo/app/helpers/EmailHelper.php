<?php
/**
 * Email Helper
 * Handles email sending via SMTP
 */

class EmailHelper {
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpEncryption;
    private $fromEmail;
    private $fromName;
    
    public function __construct() {
        // Get SMTP settings from database
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'smtp_%'");
        $settings = $stmt->fetchAll();
        
        $settingsMap = [];
        foreach ($settings as $setting) {
            $settingsMap[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $this->smtpHost = $settingsMap['smtp_host'] ?? 'smtp.gmail.com';
        $this->smtpPort = $settingsMap['smtp_port'] ?? '587';
        $this->smtpUsername = $settingsMap['smtp_username'] ?? '';
        $this->smtpPassword = $settingsMap['smtp_password'] ?? '';
        $this->smtpEncryption = $settingsMap['smtp_encryption'] ?? 'tls';
        $this->fromEmail = $settingsMap['smtp_from_email'] ?? $this->smtpUsername;
        $this->fromName = $settingsMap['smtp_from_name'] ?? getSchoolName();
    }
    
    /**
     * Get from email (public accessor)
     */
    public function getFromEmail() {
        return $this->fromEmail;
    }
    
    /**
     * Send email
     */
    public function sendEmail($to, $subject, $message, $isHtml = true) {
        if (empty($this->smtpHost) || empty($this->smtpUsername) || empty($this->smtpPassword)) {
            error_log("SMTP not configured - Host: " . ($this->smtpHost ?: 'empty') . ", Username: " . ($this->smtpUsername ?: 'empty'));
            throw new Exception("SMTP not fully configured. Please check Host, Username, and Password settings.");
        }
        
        // Use PHP's mail() function with SMTP configuration
        // For production, consider using PHPMailer or SwiftMailer
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: " . ($isHtml ? "text/html" : "text/plain") . "; charset=UTF-8";
        $headers[] = "From: " . $this->fromName . " <" . $this->fromEmail . ">";
        $headers[] = "Reply-To: " . $this->fromEmail;
        $headers[] = "X-Mailer: PHP/" . phpversion();
        
        // For basic SMTP, we'll use mail() with headers
        // Note: For full SMTP support, install PHPMailer via Composer
        $result = @mail($to, $subject, $message, implode("\r\n", $headers));
        
        // Log email attempt
        $this->logEmail($to, $subject, $result);
        
        if (!$result) {
            $error = error_get_last();
            $errorMsg = $error ? $error['message'] : 'Unknown error';
            error_log("mail() function failed: " . $errorMsg);
            throw new Exception("Failed to send email via mail() function. Error: " . $errorMsg);
        }
        
        return $result;
    }
    
    /**
     * Check if PHPMailer is available
     */
    public function isPHPMailerAvailable() {
        return class_exists('PHPMailer\PHPMailer\PHPMailer');
    }
    
    /**
     * Send email with PHPMailer (if available)
     * Requires: composer require phpmailer/phpmailer
     */
    public function sendEmailWithPHPMailer($to, $subject, $message, $isHtml = true) {
        // Check if PHPMailer is available
        if (!$this->isPHPMailerAvailable()) {
            // Don't fallback to mail() - throw exception with helpful message
            throw new Exception("PHPMailer is not installed. Please install it using: composer require phpmailer/phpmailer. The basic mail() function cannot use SMTP settings.");
        }
        
        if (empty($this->smtpHost) || empty($this->smtpUsername) || empty($this->smtpPassword)) {
            $errorMsg = "SMTP not fully configured. Missing: " . 
                       (empty($this->smtpHost) ? "Host " : "") .
                       (empty($this->smtpUsername) ? "Username " : "") .
                       (empty($this->smtpPassword) ? "Password" : "");
            error_log("SMTP configuration error: " . $errorMsg);
            throw new Exception($errorMsg);
        }
        
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Enable verbose debug output (level 2 = client and server messages)
            // $mail->SMTPDebug = 2; // Uncomment for detailed debugging
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug: " . $str);
            };
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            
            // Set encryption
            if ($this->smtpEncryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($this->smtpEncryption === 'tls') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPAutoTLS = false;
            }
            
            $mail->Port = intval($this->smtpPort);
            $mail->Timeout = 30;
            
            // Recipients
            $mail->setFrom($this->fromEmail ?: $this->smtpUsername, $this->fromName);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->CharSet = 'UTF-8';
            
            $result = $mail->send();
            
            // Log email
            $this->logEmail($to, $subject, $result);
            
            return $result;
        } catch (Exception $e) {
            $errorMsg = isset($mail) && isset($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            error_log("PHPMailer error: " . $errorMsg);
            $this->logEmail($to, $subject, false, $errorMsg);
            throw new Exception("SMTP Error: " . $errorMsg);
        }
    }
    
    /**
     * Log email attempt
     */
    private function logEmail($to, $subject, $success, $error = null) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO email_logs (to_email, subject, success, error_message, sent_at) 
                                  VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$to, $subject, $success ? 1 : 0, $error]);
        } catch (Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
        }
    }
    
    /**
     * Send email with attachment using PHPMailer
     * Requires: composer require phpmailer/phpmailer
     */
    public function sendEmailWithAttachment($to, $subject, $message, $attachmentPath, $attachmentName = null) {
        // Check if PHPMailer is available
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            // Fallback to basic email without attachment
            error_log("PHPMailer not available. Sending email without attachment.");
            return $this->sendEmail($to, $subject, $message, true);
        }
        
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = $this->smtpEncryption === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtpPort;
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            
            // Attachments
            if (file_exists($attachmentPath)) {
                $mail->addAttachment($attachmentPath, $attachmentName ?? basename($attachmentPath));
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            $result = $mail->send();
            
            // Log email
            $this->logEmail($to, $subject, $result);
            
            return $result;
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            $this->logEmail($to, $subject, false, $e->getMessage());
            return false;
        }
    }
    
    /**
     * Test SMTP connection
     */
    public function testConnection() {
        if (empty($this->smtpHost) || empty($this->smtpUsername)) {
            return ['success' => false, 'message' => 'SMTP not configured'];
        }
        
        // Try sending a test email
        $testSubject = "SMTP Test - " . date('Y-m-d H:i:s');
        $testMessage = "This is a test email from " . APP_NAME . " to verify SMTP configuration.";
        
        $result = $this->sendEmail($this->smtpUsername, $testSubject, $testMessage, false);
        
        return [
            'success' => $result,
            'message' => $result ? 'Test email sent successfully' : 'Failed to send test email'
        ];
    }
}

