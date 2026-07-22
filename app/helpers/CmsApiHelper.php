<?php
/**
 * CMS API Helper
 * Helps school systems communicate with the CMS
 */

class CmsApiHelper {
    
    /**
     * Send heartbeat to CMS
     * This should be called periodically (e.g., every 5 minutes) from the school system
     */
    public static function sendHeartbeat($apiKey, $apiSecret, $data = []) {
        // Get CMS URL from settings or use default
        $cmsUrl = self::getCmsUrl();
        
        if (empty($cmsUrl) || empty($apiKey)) {
            error_log("CMS API: Missing CMS URL or API key");
            return false;
        }
        
        // Prepare monitoring data
        $monitoringData = array_merge([
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'status' => 'online',
            'system_version' => APP_VERSION ?? '1.0.0',
            'php_version' => PHP_VERSION,
            'database_status' => self::checkDatabaseStatus(),
            'storage_used_mb' => self::calculateStorageUsed(),
            'total_students' => self::getTotalStudents(),
            'total_teachers' => self::getTotalTeachers(),
            'total_users' => self::getTotalUsers(),
            'active_users_24h' => self::getActiveUsers24h(),
            'total_payments_today' => self::getTotalPaymentsToday(),
            'error_count_24h' => self::getErrorCount24h(),
            'response_time_ms' => 0, // Can be calculated if needed
            'uptime_percentage' => 100, // Can be calculated if needed
            'health_score' => self::calculateHealthScore()
        ], $data);
        
        // Send to CMS
        $ch = curl_init($cmsUrl . '/monitoring/heartbeat');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($monitoringData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if ($result && isset($result['success']) && $result['success']) {
                error_log("CMS API: Heartbeat sent successfully");
                return true;
            }
        }
        
        error_log("CMS API: Failed to send heartbeat. HTTP Code: $httpCode, Response: $response");
        return false;
    }
    
    /**
     * Get CMS URL from settings
     */
    private static function getCmsUrl() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT value FROM settings WHERE key = 'cms_url' LIMIT 1");
        $stmt->execute();
        $setting = $stmt->fetch();
        
        return $setting ? $setting['value'] : null;
    }
    
    /**
     * Check database connection status
     */
    private static function checkDatabaseStatus() {
        try {
            $db = Database::getInstance()->getConnection();
            $db->query("SELECT 1");
            return 'connected';
        } catch (Exception $e) {
            return 'error';
        }
    }
    
    /**
     * Calculate storage used in MB
     */
    private static function calculateStorageUsed() {
        $uploadDir = UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            return 0;
        }
        
        $size = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadDir)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return round($size / 1024 / 1024, 2); // Convert to MB
    }
    
    /**
     * Get total students count
     */
    private static function getTotalStudents() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT COUNT(*) as count FROM students");
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get total teachers count
     */
    private static function getTotalTeachers() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT COUNT(*) as count FROM teachers");
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get total users count
     */
    private static function getTotalUsers() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get active users in last 24 hours
     */
    private static function getActiveUsers24h() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT COUNT(DISTINCT user_id) as count FROM activity_logs 
                               WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get total payments today
     */
    private static function getTotalPaymentsToday() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments 
                               WHERE DATE(created_at) = CURDATE()");
            $result = $stmt->fetch();
            return (float)($result['total'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get error count in last 24 hours (from error logs if available)
     */
    private static function getErrorCount24h() {
        // This would need to be implemented based on your error logging system
        return 0;
    }
    
    /**
     * Calculate health score (0-100)
     */
    private static function calculateHealthScore() {
        $score = 100;
        
        // Check database
        if (self::checkDatabaseStatus() !== 'connected') {
            $score -= 30;
        }
        
        // Check storage (if over 90% of limit, reduce score)
        // This would need the storage limit from CMS
        
        return max(0, $score);
    }
}

