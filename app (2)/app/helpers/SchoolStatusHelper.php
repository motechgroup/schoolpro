<?php
/**
 * School Status Helper
 * Checks if school is suspended from CMS
 */

class SchoolStatusHelper {
    
    /**
     * Get subdomain from current request
     */
    public static function getSubdomain() {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Remove port if present
        $host = preg_replace('/:\d+$/', '', $host);
        
        // For localhost, try to get from config or return default
        if ($host === 'localhost' || $host === '127.0.0.1') {
            // Check if there's a subdomain in the path or config
            // For development, we might use a query param or config
            $subdomain = $_GET['subdomain'] ?? null;
            if ($subdomain) {
                return $subdomain;
            }
            
            // Check config for default subdomain
            if (defined('SCHOOL_SUBDOMAIN')) {
                return SCHOOL_SUBDOMAIN;
            }
            
            // Default for localhost development
            return 'default';
        }
        
        // Extract subdomain from host (e.g., school.example.com -> school)
        $parts = explode('.', $host);
        if (count($parts) >= 3) {
            return $parts[0];
        }
        
        return null;
    }
    
    /**
     * Check if school is suspended
     */
    public static function isSuspended() {
        try {
            $db = Database::getInstance()->getConnection();
            $subdomain = self::getSubdomain();
            
            if (!$subdomain) {
                // If no subdomain detected, allow access (for main installation)
                return false;
            }
            
            // Check CMS schools table
            $stmt = $db->prepare("SELECT status FROM cms_schools WHERE subdomain = ? LIMIT 1");
            $stmt->execute([$subdomain]);
            $school = $stmt->fetch();
            
            if ($school && $school['status'] === 'suspended') {
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            // If CMS tables don't exist or error, allow access
            error_log("SchoolStatusHelper error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get school status information
     */
    public static function getSchoolStatus() {
        try {
            $db = Database::getInstance()->getConnection();
            $subdomain = self::getSubdomain();
            
            if (!$subdomain) {
                return null;
            }
            
            $stmt = $db->prepare("SELECT * FROM cms_schools WHERE subdomain = ? LIMIT 1");
            $stmt->execute([$subdomain]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("SchoolStatusHelper error: " . $e->getMessage());
            return null;
        }
    }
}

