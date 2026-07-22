<?php
/**
 * Application Configuration
 * Kenyan Primary School Management System
 */

// Load environment variables from .env file if it exists
// BASE_PATH is defined in index.php before this config is loaded
$envFile = (defined('BASE_PATH') ? BASE_PATH : __DIR__ . '/../..') . '/.env';
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    foreach ($envVars as $key => $value) {
        if (!defined($key) && !empty($value)) {
            define($key, $value);
        }
    }
}

// Environment: 'development' or 'production'
// Auto-detect: if localhost or 127.0.0.1, use development, otherwise production
$detectedEnv = 'production';
if (isset($_SERVER['HTTP_HOST'])) {
    $host = strtolower($_SERVER['HTTP_HOST']);
    if (in_array($host, ['localhost', '127.0.0.1', '::1']) || strpos($host, 'localhost:') === 0 || strpos($host, '127.0.0.1:') === 0) {
        $detectedEnv = 'development';
    }
}

define('ENVIRONMENT', defined('ENVIRONMENT') ? ENVIRONMENT : $detectedEnv);

// Base URL - Auto-detect from request or use .env setting
// If BASE_URL is set in .env, use it, otherwise auto-detect
if (defined('BASE_URL') && !empty(BASE_URL)) {
    // Use from .env if set
    $baseUrl = BASE_URL;
} else {
    // Auto-detect protocol (ngrok always uses HTTPS)
    $protocol = 'http';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $protocol = 'https';
    } elseif (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false || strpos($_SERVER['HTTP_HOST'], 'ngrok-free.app') !== false)) {
        // ngrok always uses HTTPS
        $protocol = 'https';
    }
    
    // Get host from request
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Determine installation path from SCRIPT_NAME only (not REQUEST_URI which contains routes)
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $scriptDir = dirname($scriptName);
    $scriptDir = str_replace('\\', '/', $scriptDir);
    $scriptDir = rtrim($scriptDir, '/');
    
    // If script is directly in document root, use empty path
    // If script is in a subdirectory, use that subdirectory
    if ($scriptDir === '/' || $scriptDir === '.' || empty($scriptDir)) {
        // Root installation - use empty path
        $path = '';
    } else {
        // Subdirectory installation - use the subdirectory from script name
        $path = $scriptDir;
    }
    
    // Final cleanup
    $path = rtrim($path, '/');
    
    $baseUrl = $protocol . '://' . $host . $path;
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $baseUrl);
}

// Log BASE_URL for debugging (only in development)
if (ENVIRONMENT === 'development' && isset($host) && isset($path) && isset($protocol)) {
    error_log("BASE_URL detected: " . BASE_URL . " (Host: $host, Path: $path, Protocol: $protocol)");
}

// Application settings (from .env or defaults)
define('APP_NAME', defined('APP_NAME') ? APP_NAME : 'SchoolPro V2.0.0');
define('APP_VERSION', defined('APP_VERSION') ? APP_VERSION : '1.0.0');

// Database Configuration (from .env or defaults)
define('DB_HOST', defined('DB_HOST') ? DB_HOST : 'localhost');
define('DB_NAME', defined('DB_NAME') ? DB_NAME : 'xrsnxvnk_nesitarskyline');
define('DB_USER', defined('DB_USER') ? DB_USER : 'xrsnxvnk_nesitarskyadmin');
define('DB_PASS', defined('DB_PASS') ? DB_PASS : 'KDL$Cg{{vyFE]$QW');
define('DB_CHARSET', defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'MASOMO_SESSION');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// File Upload Settings
define('UPLOAD_DIR', BASE_PATH . '/public/uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// M-Pesa Configuration (Daraja API)
define('MPESA_ENVIRONMENT', 'sandbox'); // 'sandbox' or 'production'
define('MPESA_CONSUMER_KEY', '');
define('MPESA_CONSUMER_SECRET', '');
define('MPESA_SHORTCODE', '');
define('MPESA_PASSKEY', '');
// Callback URL used in STK Push requests. This must point to MpesaController::callback
define('MPESA_CALLBACK_URL', BASE_URL . '/mpesa/callback');

// SMS Gateway Configuration (Placeholder)
define('SMS_API_KEY', '');
define('SMS_SENDER_ID', 'MASOMO');
define('SMS_PARTNER_ID', ''); // Optional partner ID for TextSMS gateway

// Report Settings
define('SCHOOL_NAME', 'Sample Primary School');
define('SCHOOL_ADDRESS', 'Nairobi, Kenya');
define('SCHOOL_PHONE', '+254700000000');
define('SCHOOL_EMAIL', 'info@school.co.ke');

// Error Reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Africa/Nairobi');

