<?php
/**
 * System Log Helper
 * For logging in school systems
 */

class SystemLogHelper {
    
    public static function log($action, $description = null, $module = null, $status = 'success') {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Get school ID from subdomain or config
            $schoolId = self::getSchoolId();
            
            $stmt = $db->prepare("INSERT INTO school_system_logs (school_id, user_id, action, description, module, ip_address, user_agent, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $schoolId,
                $_SESSION['user_id'] ?? null,
                $action,
                $description,
                $module,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $status
            ]);
        } catch (Exception $e) {
            error_log("SystemLogHelper error: " . $e->getMessage());
            return false;
        }
    }
    
    private static function getSchoolId() {
        // Try to get from config first
        if (defined('SCHOOL_ID')) {
            return SCHOOL_ID;
        }
        
        // Try to get from subdomain
        $subdomain = self::getSubdomain();
        
        if ($subdomain) {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id FROM cms_schools WHERE subdomain = ? LIMIT 1");
                $stmt->execute([$subdomain]);
                $school = $stmt->fetch();
                if ($school) {
                    return $school['id'];
                }
            } catch (Exception $e) {
                error_log("SystemLogHelper getSchoolId error: " . $e->getMessage());
            }
        }
        
        return null;
    }
    
    private static function getSubdomain() {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Remove port if present
        $host = preg_replace('/:\d+$/', '', $host);
        
        // For localhost, try to get from config or return null
        if ($host === 'localhost' || $host === '127.0.0.1') {
            // Check if there's a subdomain in the path or config
            $subdomain = $_GET['subdomain'] ?? null;
            if ($subdomain) {
                return $subdomain;
            }
            
            // Check config for default subdomain
            if (defined('SCHOOL_SUBDOMAIN')) {
                return SCHOOL_SUBDOMAIN;
            }
            
            return null;
        }
        
        // Extract subdomain from host (e.g., school.example.com -> school)
        $parts = explode('.', $host);
        if (count($parts) >= 3) {
            return $parts[0];
        }
        
        return null;
    }
}

