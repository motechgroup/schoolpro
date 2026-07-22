<?php
/**
 * API Controller
 * Handles API endpoints including M-Pesa callbacks
 */

class ApiController extends Controller {
    
    /**
     * M-Pesa callback endpoint (public, no auth required)
     */
    public function mpesaCallback() {
        $callbackData = file_get_contents('php://input');
        
        require_once APP_PATH . '/helpers/MpesaHelper.php';
        MpesaHelper::processCallback($callbackData);
        
        // Return success response to M-Pesa
        header('Content-Type: application/json');
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        exit;
    }
}

