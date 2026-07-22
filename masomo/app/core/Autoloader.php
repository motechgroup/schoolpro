<?php
/**
 * Autoloader Class
 * Handles automatic class loading
 */

class Autoloader {
    
    /**
     * Register autoloader
     */
    public static function register() {
        spl_autoload_register([__CLASS__, 'load']);
    }
    
    /**
     * Load class file
     */
    public static function load($className) {
        $basePath = APP_PATH;
        
        // Map namespace prefixes to directories
        $namespaceMap = [
            'App\\Core\\' => $basePath . '/core/',
            'App\\Models\\' => $basePath . '/models/',
            'App\\Controllers\\' => $basePath . '/controllers/',
            'App\\Middleware\\' => $basePath . '/middleware/',
        ];
        
        // Try namespace mapping first
        foreach ($namespaceMap as $prefix => $directory) {
            $prefixLength = strlen($prefix);
            if (strncmp($prefix, $className, $prefixLength) === 0) {
                $relativeClass = substr($className, $prefixLength);
                $file = $directory . str_replace('\\', '/', $relativeClass) . '.php';
                
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }
        
        // Fallback: try standard locations
        $directories = [
            $basePath . '/core/',
            $basePath . '/models/',
            $basePath . '/controllers/',
            $basePath . '/middleware/',
            $basePath . '/helpers/',
        ];
        
        foreach ($directories as $directory) {
            $file = $directory . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
}

