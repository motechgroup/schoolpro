<?php
/**
 * Helper Functions
 * Utility functions used throughout the application
 */

/**
 * Sanitize input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Format date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Format currency (Kenyan Shillings)
 */
function formatCurrency($amount) {
    return 'KES ' . number_format($amount, 2);
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

/**
 * Generate admission number
 * Sequential numbers starting from 100
 * Returns the next available admission number
 */
function generateAdmissionNumber() {
    $db = Database::getInstance()->getConnection();
    
    // Get the highest numeric admission number
    $stmt = $db->query("SELECT admission_number FROM students 
                       WHERE admission_number REGEXP '^[0-9]+$' 
                       ORDER BY CAST(admission_number AS UNSIGNED) DESC 
                       LIMIT 1");
    $result = $stmt->fetch();
    
    if ($result && is_numeric($result['admission_number'])) {
        // Start from the highest number + 1, but ensure it's at least 100
        $nextNumber = max(100, intval($result['admission_number']) + 1);
    } else {
        // Start from 100 if no numeric admission numbers exist
        $nextNumber = 100;
    }
    
    // Check if this number already exists (in case of gaps), find next available
    $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM students WHERE admission_number = ?");
    while (true) {
        $checkStmt->execute([strval($nextNumber)]);
        $exists = $checkStmt->fetch()['count'] > 0;
        
        if (!$exists) {
            break; // Found available number
        }
        $nextNumber++; // Try next number
    }
    
    // Return as string (e.g., "100", "101", "102")
    return strval($nextNumber);
}

/**
 * Generate UPI (Unique Personal Identifier)
 */
function generateUPI() {
    return 'UPI' . date('Y') . strtoupper(generateRandomString(8));
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Kenyan format)
 */
function isValidPhone($phone) {
    // Remove spaces and dashes
    $phone = preg_replace('/[\s\-]/', '', $phone);
    // Check if it starts with +254 or 0 and has 9 more digits
    return preg_match('/^(\+254|0)[17]\d{8}$/', $phone);
}

/**
 * Format phone number
 */
function formatPhone($phone) {
    $phone = preg_replace('/[\s\-]/', '', $phone);
    if (preg_match('/^0/', $phone)) {
        return '+254' . substr($phone, 1);
    }
    return $phone;
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Get school name from settings or config
 */
/**
 * Get system logo URL (for login page and favicon)
 * Uses redcoinlogo.png as the default site-wide logo
 */
function getSystemLogo() {
    $logoFile = 'redcoinlogo.png';
    $logoPath = BASE_URL . '/public/uploads/' . $logoFile;
    $filePath = PUBLIC_PATH . '/uploads/' . $logoFile;
    
    // Check if logo exists, otherwise use a data URI placeholder
    if (file_exists($filePath)) {
        return $logoPath;
    }
    
    // Return a data URI placeholder if file doesn't exist
    return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="#1e40af"/><text x="50" y="50" font-family="Arial" font-size="20" fill="white" text-anchor="middle" dominant-baseline="middle">Logo</text></svg>');
}

/**
 * Get dashboard logo URL
 * Checks for custom dashboard logo setting, otherwise falls back to system logo
 */
function getDashboardLogo() {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'dashboard_logo'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['setting_value'])) {
            $logoFile = $result['setting_value'];
            $logoPath = BASE_URL . '/public/uploads/' . $logoFile;
            $filePath = PUBLIC_PATH . '/uploads/' . $logoFile;
            
            // Check if file exists
            if (file_exists($filePath)) {
                return $logoPath;
            }
        }
    } catch (Exception $e) {
        error_log("Error getting dashboard logo: " . $e->getMessage());
    }
    
    // Fallback to system logo
    return getSystemLogo();
}

/**
 * Get school logo URL (from settings) or fallback to system logo
 */
function getSchoolLogo() {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'school_logo'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['setting_value'])) {
            $logoFile = $result['setting_value'];
            $logoPath = BASE_URL . '/public/uploads/' . $logoFile;
            $filePath = PUBLIC_PATH . '/uploads/' . $logoFile;
            
            // Check if file exists
            if (file_exists($filePath)) {
                return $logoPath;
            }
        }
    } catch (Exception $e) {
        error_log("Error getting school logo: " . $e->getMessage());
    }
    
    // Fallback to system logo
    return getSystemLogo();
}

/**
 * Get uploaded image URL with fallback
 * Returns the image URL if file exists, or returns null for fallback handling
 */
function getImageUrl($filename, $defaultPath = null) {
    if (empty($filename)) {
        return null;
    }
    
    $filePath = PUBLIC_PATH . '/uploads/' . $filename;
    $imageUrl = BASE_URL . '/public/uploads/' . $filename;
    
    // Check if file exists
    if (file_exists($filePath)) {
        return $imageUrl;
    }
    
    // Return default or null if not found
    return $defaultPath;
}

/**
 * Get image URL or placeholder
 * Returns image URL if exists, or a data URI placeholder
 */
function getImageUrlOrPlaceholder($filename, $placeholderText = 'No Image') {
    $url = getImageUrl($filename);
    
    if ($url) {
        return $url;
    }
    
    // Return SVG placeholder
    $placeholder = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><rect width="200" height="200" fill="#e5e7eb"/><text x="100" y="100" font-family="Arial" font-size="14" fill="#6b7280" text-anchor="middle" dominant-baseline="middle">' . htmlspecialchars($placeholderText) . '</text></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($placeholder);
}

function getSchoolName() {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'school_name' LIMIT 1");
        $result = $stmt->fetch();
        if ($result && !empty($result['setting_value'])) {
            return $result['setting_value'];
        }
    } catch (Exception $e) {
        // Fallback to config if settings table doesn't exist or query fails
    }
    return defined('SCHOOL_NAME') ? SCHOOL_NAME : APP_NAME;
}

/**
 * Get payment method display name
 */
function getPaymentMethodName($method) {
    $methods = [
        'cash' => 'Cash',
        'mpesa' => 'M-Pesa',
        'equity' => 'Equity Bank',
        'coop' => 'Co-operative Bank',
        'kcb' => 'KCB Bank',
        'family_bank' => 'Family Bank',
        'bank' => 'Bank Transfer',
        'cheque' => 'Cheque',
        'other' => 'Other'
    ];
    
    return $methods[$method] ?? ucfirst(str_replace('_', ' ', $method));
}

/**
 * Get M-Pesa PayBill number from settings
 */
function getMpesaPaybillNumber() {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mpesa_paybill_number'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['setting_value'] ?? '';
    } catch (Exception $e) {
        return '';
    }
}

/**
 * Generate QR code URL for student
 */
function generateStudentQRCode($studentId, $admissionNumber, $size = 100) {
    // QR code data: Student ID and admission number for verification
    $qrData = json_encode([
        'student_id' => $studentId,
        'admission_number' => $admissionNumber,
        'system' => 'masomo',
        'url' => BASE_URL . '/students/show/' . $studentId
    ]);
    
    // Use QR code API service (free and reliable)
    // Alternative: Can use local QR code library if preferred
    $encodedData = urlencode($qrData);
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}&bgcolor=FFFFFF&color=000000";
}

/**
 * Get current academic year
 */
function getCurrentAcademicYear() {
    try {
        $academicYearModel = new AcademicYear();
        return $academicYearModel->getCurrent();
    } catch (Exception $e) {
        error_log("Error getting current academic year: " . $e->getMessage());
        return null;
    }
}

/**
 * Get current term
 */
function getCurrentTerm() {
    try {
        $academicYearModel = new AcademicYear();
        return $academicYearModel->getCurrentTerm();
    } catch (Exception $e) {
        error_log("Error getting current term: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if school is open (within any active term)
 */
function isSchoolOpen() {
    try {
        $academicYearModel = new AcademicYear();
        return $academicYearModel->isSchoolOpen();
    } catch (Exception $e) {
        error_log("Error checking if school is open: " . $e->getMessage());
        return true; // Default to open if error
    }
}

/**
 * Get academic year name (formatted)
 */
function getAcademicYearName($academicYear = null) {
    if ($academicYear === null) {
        $current = getCurrentAcademicYear();
        return $current ? $current['name'] : date('Y') . '/' . (date('Y') + 1);
    }
    return $academicYear;
}

