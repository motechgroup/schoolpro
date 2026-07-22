<?php
/**
 * Test M-Pesa Callback Endpoint
 * Use this to test if the callback URL is accessible via ngrok
 */

header('Content-Type: application/json');

// Log the request
error_log("Test Callback Accessed: " . date('Y-m-d H:i:s'));
error_log("Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
error_log("Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
error_log("HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'unknown'));
error_log("POST Data: " . file_get_contents('php://input'));

// Return success
echo json_encode([
    'status' => 'success',
    'message' => 'Callback endpoint is accessible',
    'timestamp' => date('Y-m-d H:i:s'),
    'url' => $_SERVER['REQUEST_URI'] ?? 'unknown'
]);

