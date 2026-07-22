<?php
/**
 * Authentication Class
 * Handles user authentication and authorization
 */

class Auth {
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user ID
     */
    public static function userId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user data
     */
    public static function user() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT u.*, r.name as role_name, r.permissions 
                              FROM users u 
                              JOIN roles r ON u.role_id = r.id 
                              WHERE u.id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user && $user['permissions']) {
            $user['permissions'] = json_decode($user['permissions'], true);
        }
        
        return $user;
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole($roleName) {
        $user = self::user();
        return $user && strtolower($user['role_name']) === strtolower($roleName);
    }
    
    /**
     * Check if user has any of the specified roles
     */
    public static function hasAnyRole($roles) {
        $user = self::user();
        if (!$user) return false;
        
        $userRole = strtolower($user['role_name']);
        foreach ($roles as $role) {
            if ($userRole === strtolower($role)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if user has permission
     */
    public static function hasPermission($permission) {
        $user = self::user();
        if (!$user) return false;
        
        // Super admin has all permissions
        if (self::hasRole('super_admin')) {
            return true;
        }
        
        $permissions = $user['permissions'] ?? [];
        
        // Check if user has "*" (all permissions)
        if (is_array($permissions) && in_array('*', $permissions)) {
            return true;
        }
        
        // Check specific permission
        return is_array($permissions) && in_array($permission, $permissions);
    }
    
    /**
     * Login user
     */
    public static function login($email, $password, $selectedRole = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT u.*, r.name as role_name 
                    FROM users u 
                    JOIN roles r ON u.role_id = r.id 
                    WHERE u.email = ? AND u.status = 'active'";
            
            $params = [$email];
            
            // If role is specified, verify it matches
            if ($selectedRole) {
                $sql .= " AND r.name = ?";
                $params[] = $selectedRole;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $user = $stmt->fetch();
            
            if (!$user) {
                return false;
            }
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Verify role matches if specified
                if ($selectedRole && strtolower($user['role_name']) !== strtolower($selectedRole)) {
                    return false;
                }
                
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role_name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Update last login
                try {
                    $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                } catch (Exception $e) {
                    // Log error but don't fail login
                    error_log("Failed to update last login: " . $e->getMessage());
                }
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        session_unset();
        session_destroy();
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth() {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }
    
    /**
     * Require specific role
     */
    public static function requireRole($roleName) {
        self::requireAuth();
        
        if (!self::hasRole($roleName)) {
            http_response_code(403);
            die("Access denied. Required role: $roleName");
        }
    }
    
    /**
     * Require permission
     */
    public static function requirePermission($permission) {
        self::requireAuth();
        
        if (!self::hasPermission($permission)) {
            http_response_code(403);
            die("Access denied. Required permission: $permission");
        }
    }
}

