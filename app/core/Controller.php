<?php
/**
 * Base Controller Class
 * Provides common controller functionality
 */

class Controller {
    
    /**
     * Load model
     */
    protected function model($modelName) {
        $modelFile = APP_PATH . '/models/' . $modelName . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $modelName();
        }
        throw new Exception("Model $modelName not found");
    }
    
    /**
     * Load view
     */
    protected function view($viewName, $data = []) {
        extract($data);
        $viewFile = APP_PATH . '/views/' . $viewName . '.php';
        
        if (file_exists($viewFile)) {
            require_once APP_PATH . '/views/layouts/header.php';
            require_once $viewFile;
            require_once APP_PATH . '/views/layouts/footer.php';
        } else {
            die("View $viewName not found");
        }
    }
    
    /**
     * Load view without layout
     */
    protected function viewPartial($viewName, $data = []) {
        extract($data);
        $viewFile = APP_PATH . '/views/' . $viewName . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View $viewName not found");
        }
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        // Clean any output buffer before sending JSON
        if (ob_get_level()) {
            ob_end_clean();
        }
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Redirect
     */
    protected function redirect($url) {
        // Ensure URL starts with /
        if (strpos($url, '/') !== 0) {
            $url = '/' . $url;
        }
        // Prevent double BASE_URL if URL already contains full URL
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            header("Location: " . $url);
        } else {
            header("Location: " . BASE_URL . $url);
        }
        exit;
    }
    
    /**
     * Set flash message
     */
    protected function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Get flash message
     */
    protected function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}

