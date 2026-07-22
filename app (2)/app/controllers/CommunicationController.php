<?php
/**
 * Communication Controller
 * Handles SMS and communication features
 */

class CommunicationController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * Communication index - SMS and Email sending interface
     */
    public function index() {
        $parentModel = $this->model('ParentModel');
        $classModel = $this->model('ClassModel');
        $teacherModel = $this->model('Teacher');
        $emailTemplateModel = $this->model('EmailTemplate');
        
        $filters = [
            'status' => $_GET['status'] ?? 'active',
            'has_balance' => $_GET['has_balance'] ?? null
        ];
        
        $parents = $parentModel->getAllWithDetails($filters);
        $classes = $classModel->getAllWithDetails();
        $teachers = $teacherModel->getAllWithDetails();
        
        // Get SMS balance
        require_once APP_PATH . '/helpers/SmsHelper.php';
        $smsHelper = new SmsHelper();
        $balanceInfo = $smsHelper->getBalance();
        
        // Get email templates
        $emailTemplates = $emailTemplateModel->getActiveTemplates();
        
        $data = [
            'title' => 'Communication - ' . APP_NAME,
            'parents' => $parents,
            'teachers' => $teachers,
            'classes' => $classes,
            'filters' => $filters,
            'sms_balance' => $balanceInfo,
            'email_templates' => $emailTemplates,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('communication/index', $data);
    }
    
    /**
     * Send SMS to single parent
     */
    public function sendSms() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $phone = sanitize($_POST['phone'] ?? '');
        $message = $_POST['message'] ?? ''; // Don't sanitize yet
        $parentId = $_POST['parent_id'] ?? null;
        
        if (empty($phone) || empty($message)) {
            $this->json(['success' => false, 'message' => 'Phone number and message are required']);
            return;
        }
        
        require_once APP_PATH . '/helpers/SmsHelper.php';
        $smsHelper = new SmsHelper();
        
        // If parent_id is provided, personalize the message
        if ($parentId) {
            $parentModel = $this->model('ParentModel');
            $studentModel = $this->model('Student');
            $invoiceModel = $this->model('Invoice');
            
            $parent = $parentModel->findById($parentId);
            if ($parent) {
                $children = $studentModel->getByParent($parentId);
                $message = $this->personalizeMessage($message, $parent, $children, $invoiceModel);
            }
        } else {
            $message = sanitize($message);
        }
        
        $result = $smsHelper->sendSms($phone, $message);
        
        $this->json($result);
    }
    
    /**
     * Send bulk SMS with personalized messages
     */
    public function sendBulkSms() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $message = $_POST['message'] ?? ''; // Don't sanitize yet, we'll do it after placeholder replacement
        $recipientType = $_POST['recipient_type'] ?? 'selected';
        $parentIds = $_POST['parent_ids'] ?? [];
        $classId = $_POST['class_id'] ?? null;
        $hasBalance = $_POST['has_balance'] ?? null;
        
        if (empty($message)) {
            $this->json(['success' => false, 'message' => 'Message is required']);
            return;
        }
        
        require_once APP_PATH . '/helpers/SmsHelper.php';
        $smsHelper = new SmsHelper();
        $parentModel = $this->model('ParentModel');
        $studentModel = $this->model('Student');
        $invoiceModel = $this->model('Invoice');
        
        $recipients = []; // Array of [phone, personalized_message, parent_id]
        
        if ($recipientType == 'selected' && !empty($parentIds)) {
            // Get phones and student data for selected parents
            foreach ($parentIds as $parentId) {
                $parent = $parentModel->findById($parentId);
                if ($parent && !empty($parent['phone'])) {
                    $children = $studentModel->getByParent($parentId);
                    $personalizedMessage = $this->personalizeMessage($message, $parent, $children, $invoiceModel);
                    $recipients[] = [
                        'phone' => $parent['phone'],
                        'message' => $personalizedMessage,
                        'parent_id' => $parentId
                    ];
                }
            }
        } elseif ($recipientType == 'class' && $classId) {
            // Get phones for parents of students in a class
            $students = $studentModel->getByClass($classId);
            $parentStudentsMap = []; // Group students by parent
            
            foreach ($students as $student) {
                $parentId = $student['parent_id'];
                if (!isset($parentStudentsMap[$parentId])) {
                    $parentStudentsMap[$parentId] = [];
                }
                $parentStudentsMap[$parentId][] = $student;
            }
            
            foreach ($parentStudentsMap as $parentId => $children) {
                $parent = $parentModel->findById($parentId);
                if ($parent && !empty($parent['phone'])) {
                    $personalizedMessage = $this->personalizeMessage($message, $parent, $children, $invoiceModel);
                    $recipients[] = [
                        'phone' => $parent['phone'],
                        'message' => $personalizedMessage,
                        'parent_id' => $parentId
                    ];
                }
            }
        } elseif ($recipientType == 'all') {
            // Get all active parents
            $filters = ['status' => 'active'];
            if ($hasBalance == '1') {
                $filters['has_balance'] = '1';
            }
            $parents = $parentModel->getAllWithDetails($filters);
            
            foreach ($parents as $parent) {
                if (!empty($parent['phone'])) {
                    $children = $studentModel->getByParent($parent['id']);
                    $personalizedMessage = $this->personalizeMessage($message, $parent, $children, $invoiceModel);
                    $recipients[] = [
                        'phone' => $parent['phone'],
                        'message' => $personalizedMessage,
                        'parent_id' => $parent['id']
                    ];
                }
            }
        }
        
        if (empty($recipients)) {
            $this->json(['success' => false, 'message' => 'No recipients found']);
            return;
        }
        
        // Send personalized SMS to each recipient
        $results = [];
        $successCount = 0;
        $failureCount = 0;
        
        foreach ($recipients as $recipient) {
            $result = $smsHelper->sendSms($recipient['phone'], $recipient['message']);
            $results[] = [
                'phone' => $recipient['phone'],
                'parent_id' => $recipient['parent_id'],
                'result' => $result
            ];
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failureCount++;
            }
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }
        
        $this->json([
            'success' => $failureCount == 0,
            'total' => count($recipients),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'results' => $results
        ]);
    }
    
    /**
     * Personalize message with student and fee data
     */
    private function personalizeMessage($template, $parent, $children, $invoiceModel) {
        $message = $template;
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        
        // Replace parent placeholders
        $message = str_replace('{parent_name}', htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']), $message);
        
        // If message contains student-related placeholders, use first child or aggregate
        if (!empty($children)) {
            $firstChild = $children[0];
            $totalBalance = 0;
            $studentNames = [];
            $admissionNumbers = [];
            $classNames = [];
            
            foreach ($children as $child) {
                $studentNames[] = $child['first_name'] . ' ' . $child['last_name'];
                $admissionNumbers[] = $child['admission_number'];
                $classNames[] = $child['class_name'] ?? 'N/A';
                
                // Calculate total balance for this child
                $invoices = $invoiceModel->getByStudent($child['id'], $currentYear);
                foreach ($invoices as $inv) {
                    $invoiceModel->updateBalance($inv['id']);
                }
                $invoices = $invoiceModel->getByStudent($child['id'], $currentYear);
                foreach ($invoices as $inv) {
                    $totalBalance += $inv['balance'] ?? 0;
                }
            }
            
            // Replace student placeholders
            $message = str_replace('{student_name}', htmlspecialchars($firstChild['first_name'] . ' ' . $firstChild['last_name']), $message);
            $message = str_replace('{students_names}', htmlspecialchars(implode(', ', $studentNames)), $message);
            $message = str_replace('{admission_number}', htmlspecialchars($firstChild['admission_number']), $message);
            $message = str_replace('{admission_numbers}', htmlspecialchars(implode(', ', $admissionNumbers)), $message);
            $message = str_replace('{class_name}', htmlspecialchars($firstChild['class_name'] ?? 'N/A'), $message);
            $message = str_replace('{class_names}', htmlspecialchars(implode(', ', array_unique($classNames))), $message);
            $message = str_replace('{grade}', htmlspecialchars($firstChild['grade_display_name'] ?? 'N/A'), $message);
            $message = str_replace('{fee_balance}', formatCurrency($totalBalance), $message);
            $message = str_replace('{children_count}', count($children), $message);
        } else {
            // No children, replace with N/A or empty
            $message = str_replace('{student_name}', 'N/A', $message);
            $message = str_replace('{students_names}', 'N/A', $message);
            $message = str_replace('{admission_number}', 'N/A', $message);
            $message = str_replace('{admission_numbers}', 'N/A', $message);
            $message = str_replace('{class_name}', 'N/A', $message);
            $message = str_replace('{class_names}', 'N/A', $message);
            $message = str_replace('{grade}', 'N/A', $message);
            $message = str_replace('{fee_balance}', formatCurrency(0), $message);
            $message = str_replace('{children_count}', '0', $message);
        }
        
        // Replace school placeholders
        $message = str_replace('{school_name}', APP_NAME, $message);
        $message = str_replace('{current_date}', date('d/m/Y'), $message);
        $message = str_replace('{current_year}', date('Y'), $message);
        
        // Sanitize the final message
        return sanitize($message);
    }
    
    /**
     * Send WhatsApp message to single parent
     */
    public function sendWhatsApp() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $phone = sanitize($_POST['phone'] ?? '');
        $message = $_POST['message'] ?? '';
        $parentId = $_POST['parent_id'] ?? null;
        $mediaUrl = sanitize($_POST['media_url'] ?? null);
        
        if (empty($phone) || empty($message)) {
            $this->json(['success' => false, 'message' => 'Phone number and message are required']);
            return;
        }
        
        require_once APP_PATH . '/helpers/WhatsAppHelper.php';
        $whatsappHelper = new WhatsAppHelper();
        
        if (!$whatsappHelper->isConfigured()) {
            $this->json(['success' => false, 'message' => 'WhatsApp is not configured. Please configure WhatsApp settings first.']);
            return;
        }
        
        // If parent_id is provided, personalize the message
        if ($parentId) {
            $parentModel = $this->model('ParentModel');
            $studentModel = $this->model('Student');
            $invoiceModel = $this->model('Invoice');
            
            $parent = $parentModel->findById($parentId);
            if ($parent) {
                $children = $studentModel->getByParent($parentId);
                $message = $this->personalizeMessage($message, $parent, $children, $invoiceModel);
            }
        } else {
            $message = sanitize($message);
        }
        
        $result = $whatsappHelper->sendMessage($phone, $message, $mediaUrl);
        
        $this->json($result);
    }
    
    /**
     * Send bulk WhatsApp messages
     */
    public function sendBulkWhatsApp() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $message = $_POST['message'] ?? '';
        $recipientType = $_POST['recipient_type'] ?? 'selected';
        $parentIds = $_POST['parent_ids'] ?? [];
        $classId = $_POST['class_id'] ?? null;
        $hasBalance = $_POST['has_balance'] ?? null;
        $mediaUrl = sanitize($_POST['media_url'] ?? null);
        
        if (empty($message)) {
            $this->json(['success' => false, 'message' => 'Message is required']);
            return;
        }
        
        require_once APP_PATH . '/helpers/WhatsAppHelper.php';
        $whatsappHelper = new WhatsAppHelper();
        
        if (!$whatsappHelper->isConfigured()) {
            $this->json(['success' => false, 'message' => 'WhatsApp is not configured. Please configure WhatsApp settings first.']);
            return;
        }
        
        $parentModel = $this->model('ParentModel');
        $studentModel = $this->model('Student');
        $invoiceModel = $this->model('Invoice');
        
        $recipients = [];
        
        if ($recipientType == 'selected' && !empty($parentIds)) {
            foreach ($parentIds as $parentId) {
                $parent = $parentModel->findById($parentId);
                if ($parent && !empty($parent['phone'])) {
                    $children = $studentModel->getByParent($parentId);
                    $personalizedMessage = $this->personalizeMessage($message, $parent, $children, $invoiceModel);
                    $recipients[] = [
                        'phone' => $parent['phone'],
                        'message' => $personalizedMessage,
                        'parent_id' => $parentId
                    ];
                }
            }
        } elseif ($recipientType == 'class' && $classId) {
            $students = $studentModel->getByClass($classId);
            $parentPhones = [];
            foreach ($students as $student) {
                if (!empty($student['parent_id'])) {
                    $parent = $parentModel->findById($student['parent_id']);
                    if ($parent && !empty($parent['phone']) && !in_array($parent['phone'], $parentPhones)) {
                        $parentPhones[] = $parent['phone'];
                        $children = $studentModel->getByParent($parent['id']);
                        $personalizedMessage = $this->personalizeMessage($message, $parent, $children, $invoiceModel);
                        $recipients[] = [
                            'phone' => $parent['phone'],
                            'message' => $personalizedMessage,
                            'parent_id' => $parent['id']
                        ];
                    }
                }
            }
        } elseif ($recipientType == 'all') {
            $allParents = $parentModel->getAllWithDetails();
            foreach ($allParents as $parent) {
                if (!empty($parent['phone'])) {
                    // Filter by balance if requested
                    if ($hasBalance && empty($parent['total_balance']) || $parent['total_balance'] <= 0) {
                        continue;
                    }
                    
                    $children = $studentModel->getByParent($parent['id']);
                    $personalizedMessage = $this->personalizeMessage($message, $parent, $children, $invoiceModel);
                    $recipients[] = [
                        'phone' => $parent['phone'],
                        'message' => $personalizedMessage,
                        'parent_id' => $parent['id']
                    ];
                }
            }
        }
        
        if (empty($recipients)) {
            $this->json(['success' => false, 'message' => 'No recipients found']);
            return;
        }
        
        $successCount = 0;
        $failureCount = 0;
        $results = [];
        
        foreach ($recipients as $recipient) {
            $result = $whatsappHelper->sendMessage($recipient['phone'], $recipient['message'], $mediaUrl);
            $results[] = [
                'phone' => $recipient['phone'],
                'result' => $result
            ];
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failureCount++;
            }
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }
        
        $this->json([
            'success' => $failureCount == 0,
            'message' => "Sent to {$successCount} recipients" . ($failureCount > 0 ? ", {$failureCount} failed" : ""),
            'total' => count($recipients),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'results' => $results
        ]);
    }
    
    /**
     * Send report card via WhatsApp
     */
    public function sendReportCardWhatsApp() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $studentId = intval($_POST['student_id'] ?? 0);
        $examinationId = intval($_POST['examination_id'] ?? 0);
        
        if (!$studentId || !$examinationId) {
            $this->json(['success' => false, 'message' => 'Student ID and Examination ID are required']);
            return;
        }
        
        require_once APP_PATH . '/helpers/WhatsAppHelper.php';
        require_once APP_PATH . '/helpers/ReportCardHelper.php';
        
        $whatsappHelper = new WhatsAppHelper();
        $reportCardHelper = new ReportCardHelper();
        
        if (!$whatsappHelper->isConfigured()) {
            $this->json(['success' => false, 'message' => 'WhatsApp is not configured']);
            return;
        }
        
        // Generate report card PDF
        $pdfPath = $reportCardHelper->generatePDF($studentId, $examinationId);
        
        if (!$pdfPath) {
            $this->json(['success' => false, 'message' => 'Failed to generate report card']);
            return;
        }
        
        // Get student and parent info
        $studentModel = $this->model('Student');
        $parentModel = $this->model('ParentModel');
        
        $student = $studentModel->findById($studentId);
        if (!$student || empty($student['parent_id'])) {
            $this->json(['success' => false, 'message' => 'Student or parent not found']);
            return;
        }
        
        $parent = $parentModel->findById($student['parent_id']);
        if (!$parent || empty($parent['phone'])) {
            $this->json(['success' => false, 'message' => 'Parent phone number not found']);
            return;
        }
        
        // Upload PDF to a publicly accessible location (or use existing URL)
        // For now, assume PDF is accessible via URL
        $pdfUrl = BASE_URL . '/' . $pdfPath;
        
        $message = "Dear {$parent['first_name']},\n\n";
        $message .= "Please find attached the report card for {$student['first_name']} {$student['last_name']}.\n\n";
        $message .= "Thank you,\n" . getSchoolName();
        
        // Send as document
        $result = $whatsappHelper->sendDocument($parent['phone'], $pdfUrl, $message, "Report_Card_{$student['admission_number']}.pdf");
        
        $this->json($result);
    }
    
    /**
     * SMS settings page
     */
    public function settings() {
        $db = Database::getInstance()->getConnection();
        
        // Get current SMS settings
        $stmt = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'sms_%' ORDER BY setting_key");
        $smsSettings = $stmt->fetchAll();
        
        // Get current WhatsApp settings
        $stmt = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'whatsapp_%' ORDER BY setting_key");
        $whatsappSettings = $stmt->fetchAll();
        
        $smsSettingsMap = [];
        foreach ($smsSettings as $setting) {
            $smsSettingsMap[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $whatsappSettingsMap = [];
        foreach ($whatsappSettings as $setting) {
            $whatsappSettingsMap[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $data = [
            'title' => 'Communication Settings - ' . APP_NAME,
            'sms_settings' => $smsSettingsMap,
            'whatsapp_settings' => $whatsappSettingsMap,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('communication/settings', $data);
    }
    
    /**
     * Save SMS and WhatsApp settings
     */
    public function saveSettings() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        // SMS settings
        $smsSettings = [
            'sms_api_key' => sanitize($_POST['sms_api_key'] ?? ''),
            'sms_sender_id' => sanitize($_POST['sms_sender_id'] ?? 'MASOMO'),
            'sms_partner_id' => sanitize($_POST['sms_partner_id'] ?? ''),
            'sms_api_url' => sanitize($_POST['sms_api_url'] ?? 'https://sms.textsms.co.ke')
        ];
        
        // WhatsApp settings
        $whatsappSettings = [
            'whatsapp_provider' => sanitize($_POST['whatsapp_provider'] ?? 'cloud_api'),
            'whatsapp_api_key' => sanitize($_POST['whatsapp_api_key'] ?? ''),
            'whatsapp_api_secret' => sanitize($_POST['whatsapp_api_secret'] ?? ''),
            'whatsapp_phone_number_id' => sanitize($_POST['whatsapp_phone_number_id'] ?? ''),
            'whatsapp_business_account_id' => sanitize($_POST['whatsapp_business_account_id'] ?? ''),
            'whatsapp_api_url' => sanitize($_POST['whatsapp_api_url'] ?? '')
        ];
        
        // Merge all settings
        $allSettings = array_merge($smsSettings, $whatsappSettings);
        
        $db->beginTransaction();
        
        try {
            foreach ($allSettings as $key => $value) {
                $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, description, updated_at) 
                                     VALUES (?, ?, ?, NOW()) 
                                     ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
                // Use empty description for existing settings, or set appropriate description
                $description = '';
                if (strpos($key, 'whatsapp_') === 0) {
                    $description = 'WhatsApp ' . ucfirst(str_replace(['whatsapp_', '_'], ['', ' '], $key));
                } elseif (strpos($key, 'sms_') === 0) {
                    $description = 'SMS ' . ucfirst(str_replace(['sms_', '_'], ['', ' '], $key));
                }
                $stmt->execute([$key, $value, $description, $value]);
            }
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Settings saved successfully']);
        } catch (Exception $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Failed to save settings: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Test SMS sending
     */
    public function testSms() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $phone = sanitize($_POST['test_phone'] ?? '');
        $message = sanitize($_POST['test_message'] ?? 'This is a test SMS from ' . APP_NAME);
        
        if (empty($phone)) {
            $this->json(['success' => false, 'message' => 'Phone number is required']);
            return;
        }
        
        if (empty($message)) {
            $this->json(['success' => false, 'message' => 'Message is required']);
            return;
        }
        
        require_once APP_PATH . '/helpers/SmsHelper.php';
        $smsHelper = new SmsHelper();
        
        $result = $smsHelper->sendSms($phone, $message);
        
        // Add test flag and more debugging info for test SMS
        $result['test'] = true;
        
        // For test SMS, ALWAYS include detailed API response information
        $debugInfo = [];
        if (isset($result['http_code'])) {
            $debugInfo[] = 'HTTP Code: ' . $result['http_code'];
        }
        if (isset($result['curl_error'])) {
            $debugInfo[] = 'Connection Error: ' . $result['curl_error'];
        }
        if (isset($result['curl_errno']) && $result['curl_errno'] != 0) {
            $debugInfo[] = 'cURL Error Code: ' . $result['curl_errno'];
        }
        if (isset($result['response'])) {
            $responsePreview = is_string($result['response']) ? $result['response'] : json_encode($result['response']);
            $debugInfo[] = 'API Response: ' . $responsePreview;
        }
        if (isset($result['data']) && is_array($result['data'])) {
            $debugInfo[] = 'Parsed Data: ' . json_encode($result['data'], JSON_PRETTY_PRINT);
        }
        if (!empty($debugInfo)) {
            $result['debug'] = implode(' | ', $debugInfo);
        }
        
        // Always include full response data for test SMS
        $result['full_response'] = $result['response'] ?? '';
        $result['full_data'] = $result['data'] ?? null;
        
        $this->json($result);
    }
    
    /**
     * Test WhatsApp sending
     */
    public function testWhatsApp() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $phone = sanitize($_POST['test_phone'] ?? '');
        
        if (empty($phone)) {
            $this->json(['success' => false, 'message' => 'Phone number is required']);
            return;
        }
        
        $message = sanitize($_POST['test_message'] ?? 'This is a test WhatsApp message from ' . APP_NAME);
        
        require_once APP_PATH . '/helpers/WhatsAppHelper.php';
        $whatsappHelper = new WhatsAppHelper();
        
        if (!$whatsappHelper->isConfigured()) {
            $this->json(['success' => false, 'message' => 'WhatsApp is not configured. Please configure WhatsApp settings first.']);
            return;
        }
        
        $result = $whatsappHelper->sendMessage($phone, $message);
        $result['test'] = true;
        
        // Add detailed debugging info for test WhatsApp
        $debugInfo = [];
        if (isset($result['http_code'])) {
            $debugInfo[] = 'HTTP Code: ' . $result['http_code'];
        }
        if (isset($result['response'])) {
            $responsePreview = is_string($result['response']) ? $result['response'] : json_encode($result['response'], JSON_PRETTY_PRINT);
            $debugInfo[] = 'API Response: ' . substr($responsePreview, 0, 500);
        }
        if (isset($result['data']) && is_array($result['data'])) {
            $debugInfo[] = 'Parsed Data: ' . json_encode($result['data'], JSON_PRETTY_PRINT);
        }
        if (!empty($debugInfo)) {
            $result['debug'] = implode(' | ', $debugInfo);
        }
        
        // Always include full response data for test WhatsApp
        $result['full_response'] = $result['response'] ?? '';
        $result['full_data'] = $result['data'] ?? null;
        
        $this->json($result);
    }
    
    /**
     * Send email to parents or teachers
     */
    public function sendEmail() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        require_once APP_PATH . '/helpers/EmailHelper.php';
        require_once APP_PATH . '/helpers/EmailTemplateHelper.php';
        
        $recipientType = sanitize($_POST['recipient_type'] ?? 'parent');
        $recipientIds = $_POST['recipient_ids'] ?? [];
        $classId = $_POST['class_id'] ?? null;
        $templateId = intval($_POST['template_id'] ?? 0);
        $subject = sanitize($_POST['subject'] ?? '');
        $message = $_POST['message'] ?? '';
        $isHtml = isset($_POST['is_html']) ? (bool)$_POST['is_html'] : true;
        $hasBalance = isset($_POST['has_balance']) && $_POST['has_balance'] == '1';
        
        if (empty($subject) || empty($message)) {
            $this->json(['success' => false, 'message' => 'Subject and message are required']);
            return;
        }
        
        $emailHelper = new EmailHelper();
        
        if (!$emailHelper->isPHPMailerAvailable()) {
            $this->json(['success' => false, 'message' => 'PHPMailer is not installed. Please install it to send emails.']);
            return;
        }
        
        $emailTemplateModel = $this->model('EmailTemplate');
        $parentModel = $this->model('ParentModel');
        $teacherModel = $this->model('Teacher');
        $studentModel = $this->model('Student');
        
        $recipients = [];
        $results = [];
        $successCount = 0;
        $failureCount = 0;
        
        // Get recipients based on type
        if ($recipientType === 'parent') {
            $invoiceModel = $this->model('Invoice');
            
            if (!empty($recipientIds)) {
                // Selected parents
                foreach ($recipientIds as $parentId) {
                    $parent = $parentModel->findById($parentId);
                    if ($parent && !empty($parent['email'])) {
                        // Check if parent has balance if filter is enabled
                        if ($hasBalance) {
                            $parentWithDetails = $parentModel->getParentWithDetails($parentId);
                            $totalBalance = $parentWithDetails['total_balance'] ?? 0;
                            
                            // If balance filter is on and parent has no balance, skip
                            if ($totalBalance <= 0) {
                                continue;
                            }
                        }
                        
                        $recipients[] = [
                            'type' => 'parent',
                            'id' => $parentId,
                            'email' => $parent['email'],
                            'name' => trim($parent['first_name'] . ' ' . $parent['last_name']),
                            'parent' => $parent
                        ];
                    }
                }
            } elseif ($classId) {
                // All parents in a class
                $students = $studentModel->getByClass($classId);
                $parentIds = array_unique(array_column($students, 'parent_id'));
                
                foreach ($parentIds as $parentId) {
                    if ($parentId) {
                        $parent = $parentModel->findById($parentId);
                        if ($parent && !empty($parent['email'])) {
                            // Check if parent has balance if filter is enabled
                            if ($hasBalance) {
                                $parentWithDetails = $parentModel->getParentWithDetails($parentId);
                                $totalBalance = $parentWithDetails['total_balance'] ?? 0;
                                
                                // If balance filter is on and parent has no balance, skip
                                if ($totalBalance <= 0) {
                                    continue;
                                }
                            }
                            
                            $recipients[] = [
                                'type' => 'parent',
                                'id' => $parentId,
                                'email' => $parent['email'],
                                'name' => trim($parent['first_name'] . ' ' . $parent['last_name']),
                                'parent' => $parent
                            ];
                        }
                    }
                }
            } else {
                // All parents (when no specific selection)
                $filters = ['status' => 'active'];
                if ($hasBalance) {
                    $filters['has_balance'] = '1';
                }
                $allParents = $parentModel->getAllWithDetails($filters);
                
                foreach ($allParents as $parent) {
                    if (!empty($parent['email'])) {
                        $recipients[] = [
                            'type' => 'parent',
                            'id' => $parent['id'],
                            'email' => $parent['email'],
                            'name' => trim($parent['first_name'] . ' ' . $parent['last_name']),
                            'parent' => $parent
                        ];
                    }
                }
            }
        } elseif ($recipientType === 'teacher') {
            if (!empty($recipientIds)) {
                // Selected teachers
                foreach ($recipientIds as $teacherId) {
                    $teacher = $teacherModel->findById($teacherId);
                    if ($teacher && !empty($teacher['email'])) {
                        $recipients[] = [
                            'type' => 'teacher',
                            'id' => $teacherId,
                            'email' => $teacher['email'],
                            'name' => trim($teacher['first_name'] . ' ' . $teacher['last_name']),
                            'teacher' => $teacher
                        ];
                    }
                }
            } else {
                // All teachers
                $allTeachers = $teacherModel->getAllWithDetails();
                foreach ($allTeachers as $teacher) {
                    if (!empty($teacher['email'])) {
                        $recipients[] = [
                            'type' => 'teacher',
                            'id' => $teacher['id'],
                            'email' => $teacher['email'],
                            'name' => trim($teacher['first_name'] . ' ' . $teacher['last_name']),
                            'teacher' => $teacher
                        ];
                    }
                }
            }
        }
        
        if (empty($recipients)) {
            $this->json(['success' => false, 'message' => 'No recipients found with email addresses']);
            return;
        }
        
        // Process and send emails
        foreach ($recipients as $recipient) {
            $finalSubject = $subject;
            $finalMessage = $message;
            
            // If template is used, process variables
            if ($templateId > 0) {
                $template = $emailTemplateModel->getTemplate($templateId);
                if ($template) {
                    $variables = [];
                    
                    if ($recipient['type'] === 'parent') {
                        // Get first student for this parent
                        $students = $studentModel->getByParent($recipient['id']);
                        $studentId = !empty($students) ? $students[0]['id'] : null;
                        $variables = EmailTemplateHelper::getParentVariables($recipient['id'], $studentId);
                    } elseif ($recipient['type'] === 'teacher') {
                        $variables = EmailTemplateHelper::getTeacherVariables($recipient['id']);
                    }
                    
                    $processed = EmailTemplateHelper::processTemplate($template, $variables);
                    $finalSubject = $processed['subject'];
                    $finalMessage = $processed['body'];
                }
            } else {
                // Replace basic variables in custom message
                if ($recipient['type'] === 'parent') {
                    $students = $studentModel->getByParent($recipient['id']);
                    $studentId = !empty($students) ? $students[0]['id'] : null;
                    $variables = EmailTemplateHelper::getParentVariables($recipient['id'], $studentId);
                } else {
                    $variables = EmailTemplateHelper::getTeacherVariables($recipient['id']);
                }
                
                foreach ($variables as $key => $value) {
                    $finalSubject = str_replace('{' . $key . '}', $value ?? '', $finalSubject);
                    $finalMessage = str_replace('{' . $key . '}', $value ?? '', $finalMessage);
                }
            }
            
            // Send email
            try {
                $result = $emailHelper->sendEmailWithPHPMailer(
                    $recipient['email'],
                    $finalSubject,
                    $finalMessage,
                    $isHtml
                );
                
                // Log email
                $this->logEmail($recipient['email'], $finalSubject, $finalMessage, $templateId, $recipientType, $recipient['id'], $result);
                
                if ($result) {
                    $successCount++;
                    $results[] = [
                        'email' => $recipient['email'],
                        'name' => $recipient['name'],
                        'success' => true
                    ];
                } else {
                    $failureCount++;
                    $results[] = [
                        'email' => $recipient['email'],
                        'name' => $recipient['name'],
                        'success' => false,
                        'error' => 'Failed to send'
                    ];
                }
            } catch (Exception $e) {
                $failureCount++;
                $this->logEmail($recipient['email'], $finalSubject, $finalMessage, $templateId, $recipientType, $recipient['id'], false, $e->getMessage());
                $results[] = [
                    'email' => $recipient['email'],
                    'name' => $recipient['name'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
            
            // Small delay to avoid rate limiting
            usleep(200000); // 0.2 seconds
        }
        
        $this->json([
            'success' => $failureCount == 0,
            'total' => count($recipients),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'results' => $results
        ]);
    }
    
    /**
     * Get email template preview
     */
    public function getTemplatePreview() {
        $templateId = intval($_GET['template_id'] ?? 0);
        $recipientType = sanitize($_GET['recipient_type'] ?? 'parent');
        $recipientId = intval($_GET['recipient_id'] ?? 0);
        
        if (empty($templateId) || empty($recipientId)) {
            $this->json(['success' => false, 'message' => 'Template ID and Recipient ID are required']);
            return;
        }
        
        require_once APP_PATH . '/helpers/EmailTemplateHelper.php';
        
        $emailTemplateModel = $this->model('EmailTemplate');
        $template = $emailTemplateModel->getTemplate($templateId);
        
        if (!$template) {
            $this->json(['success' => false, 'message' => 'Template not found']);
            return;
        }
        
        $variables = [];
        if ($recipientType === 'parent') {
            $studentModel = $this->model('Student');
            $students = $studentModel->getByParent($recipientId);
            $studentId = !empty($students) ? $students[0]['id'] : null;
            $variables = EmailTemplateHelper::getParentVariables($recipientId, $studentId);
        } elseif ($recipientType === 'teacher') {
            $variables = EmailTemplateHelper::getTeacherVariables($recipientId);
        }
        
        $processed = EmailTemplateHelper::processTemplate($template, $variables);
        
        $this->json([
            'success' => true,
            'subject' => $processed['subject'],
            'body' => $processed['body']
        ]);
    }
    
    /**
     * Log email to database
     */
    private function logEmail($toEmail, $subject, $body, $templateId = null, $recipientType = 'parent', $recipientId = null, $success = false, $errorMessage = null) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO email_logs (to_email, subject, body, template_id, recipient_type, recipient_id, success, error_message, sent_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $toEmail,
                $subject,
                $body,
                $templateId,
                $recipientType,
                $recipientId,
                $success ? 1 : 0,
                $errorMessage
            ]);
        } catch (Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
        }
    }
    
    /**
     * Get email history/logs
     */
    public function emailHistory() {
        $db = Database::getInstance()->getConnection();
        
        $page = intval($_GET['page'] ?? 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        // Get email logs
        $stmt = $db->prepare("SELECT el.*, et.name as template_name 
                          FROM email_logs el 
                          LEFT JOIN email_templates et ON el.template_id = et.id 
                          ORDER BY el.sent_at DESC 
                          LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $stmt = $db->query("SELECT COUNT(*) as total FROM email_logs");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $this->json([
            'success' => true,
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }
}

