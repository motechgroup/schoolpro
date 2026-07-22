<?php
/**
 * SMS Helper Class
 * Handles SMS sending via TextSMS.co.ke API
 * Reference: https://textsms.co.ke/
 */

class SmsHelper {
    private $apiKey;
    private $senderId;
    private $partnerId;
    private $apiUrl = 'https://textsms.co.ke/api/send';
    
    public function __construct() {
        // Get SMS settings from database or config
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM settings WHERE setting_key IN ('sms_api_key', 'sms_sender_id', 'sms_partner_id', 'sms_api_url')");
        $settings = $stmt->fetchAll();
        
        $settingsMap = [];
        foreach ($settings as $setting) {
            $settingsMap[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $this->apiKey = $settingsMap['sms_api_key'] ?? SMS_API_KEY ?? '';
        $this->senderId = $settingsMap['sms_sender_id'] ?? SMS_SENDER_ID ?? 'MASOMO';
        $this->partnerId = $settingsMap['sms_partner_id'] ?? '';
        $this->apiUrl = $settingsMap['sms_api_url'] ?? 'https://sms.textsms.co.ke';
    }
    
    /**
     * Send SMS to a single phone number
     */
    public function sendSms($phone, $message) {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'SMS API key not configured. Please configure SMS settings.'
            ];
        }
        
        // Format phone number (ensure it starts with country code)
        $phone = $this->formatPhoneNumber($phone);
        
        if (!$phone) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format'
            ];
        }
        
        // Prepare API request - TextSMS.co.ke format
        // Based on TextSMS API documentation: https://textsms.co.ke/bulk-sms-api/
        // Parameters: apikey, partnerID, message, shortcode, mobile
        $data = [
            'apikey' => $this->apiKey,
            'shortcode' => $this->senderId,
            'mobile' => $phone,
            'message' => $message
        ];
        
        // Add partnerID if configured (TextSMS uses partnerID parameter)
        if (!empty($this->partnerId)) {
            $data['partnerID'] = $this->partnerId;
        }
        
        // Ensure API URL has proper format
        $apiUrl = trim($this->apiUrl);
        if (empty($apiUrl)) {
            return [
                'success' => false,
                'message' => 'SMS API URL not configured'
            ];
        }
        
        // Add protocol if missing
        if (!preg_match('/^https?:\/\//', $apiUrl)) {
            $apiUrl = 'https://' . $apiUrl;
        }
        
        // Ensure endpoint is included
        // TextSMS.co.ke uses: https://sms.textsms.co.ke/api/services/sendsms/
        // Check if URL already ends with a complete endpoint pattern
        $hasEndpoint = (
            substr($apiUrl, -20) === '/api/services/sendsms' ||
            substr($apiUrl, -21) === '/api/services/sendsms/' ||
            substr($apiUrl, -13) === '/api/sms/send' ||
            substr($apiUrl, -9) === '/api/send' ||
            substr($apiUrl, -9) === '/sms/send' ||
            substr($apiUrl, -5) === '/send'
        );
        
        if (!$hasEndpoint) {
            // Default to the official TextSMS.co.ke endpoint
            $apiUrl = rtrim($apiUrl, '/') . '/api/services/sendsms/';
        }
        
        // Log request details for debugging (always log for SMS)
        error_log("SMS Request URL: " . $apiUrl);
        error_log("SMS Request Data: " . json_encode($data, JSON_PRETTY_PRINT));
        
        // Send via cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        
        // Log response for debugging
        error_log("SMS Response HTTP Code: " . $httpCode);
        error_log("SMS Response Body: " . substr($response, 0, 500));
        if ($curlError) {
            error_log("SMS cURL Error: " . $curlError . " (Code: " . $curlErrno . ")");
        }
        
        curl_close($ch);
        
        // Handle cURL errors
        if ($curlErrno !== 0 || !empty($curlError)) {
            $errorMsg = 'Connection error';
            if ($curlErrno == 6) {
                $errorMsg = 'Could not resolve host. Please check your API URL.';
            } elseif ($curlErrno == 7) {
                $errorMsg = 'Failed to connect to SMS server. Please check your internet connection and API URL.';
            } elseif ($curlErrno == 28) {
                $errorMsg = 'Request timeout. The SMS server took too long to respond.';
            } else {
                $errorMsg = 'cURL Error (' . $curlErrno . '): ' . $curlError;
            }
            
            return [
                'success' => false,
                'message' => $errorMsg,
                'curl_error' => $curlError,
                'curl_errno' => $curlErrno
            ];
        }
        
        // Log SMS attempt
        $this->logSms($phone, $message, $response, $httpCode);
        
        // Check if response is HTML (indicates wrong endpoint or API error)
        if (stripos($response, '<!doctype') !== false || 
            stripos($response, '<html') !== false || 
            stripos($response, 'Page Not Found') !== false ||
            stripos($response, '404') !== false) {
            
            // Extract base URL to suggest alternatives
            // Remove any existing endpoint to get the base URL
            $baseUrl = rtrim($this->apiUrl, '/');
            // Remove common endpoint patterns to get base URL
            $baseUrl = preg_replace('#(/api/sms/send|/api/send|/sms/send|/send)$#', '', $baseUrl);
            $baseUrl = rtrim($baseUrl, '/');
            
            // If base URL doesn't have protocol, add it
            if (!preg_match('/^https?:\/\//', $baseUrl)) {
                $baseUrl = 'https://' . $baseUrl;
            }
            
            $suggestions = [];
            // Official TextSMS.co.ke endpoint and common alternatives
            $commonEndpoints = ['/api/services/sendsms/', '/send', '/api/send', '/sms/send', '/api/sms/send'];
            foreach ($commonEndpoints as $endpoint) {
                $suggestedUrl = $baseUrl . $endpoint;
                // Don't suggest the same URL that was just tried
                if ($suggestedUrl !== $apiUrl) {
                    $suggestions[] = $suggestedUrl;
                }
            }
            
            $message = 'Invalid API endpoint. The SMS gateway returned an HTML 404 page instead of JSON. ';
            $message .= 'Current URL: ' . $apiUrl . '. ';
            $message .= 'Try these alternatives: ' . implode(', ', $suggestions) . '. ';
            $message .= 'Or check your TextSMS dashboard for the correct endpoint URL.';
            
            return [
                'success' => false,
                'message' => $message,
                'http_code' => $httpCode,
                'response' => substr($response, 0, 500),
                'error_type' => 'invalid_endpoint',
                'current_url' => $apiUrl,
                'suggested_urls' => $suggestions
            ];
        }
        
        // Parse response (adjust based on actual API response format)
        $responseData = json_decode($response, true);
        
        // Handle different response formats
        if ($httpCode == 200) {
            // Check for success indicators in various formats
            $isSuccess = false;
            $errorMessage = 'SMS sending failed';
            $hasError = false;
            
            // Common error indicators that mean SMS was NOT sent (even with HTTP 200)
            $errorKeywords = ['insufficient', 'balance', 'invalid', 'failed', 'error', 'denied', 'rejected', 'blocked', 'not registered', 'unauthorized', 'expired'];
            
            if (is_array($responseData)) {
                // TextSMS.co.ke specific format: {"responses": [{"response-code": 200, "response-description": "Success", ...}]}
                // Check this FIRST as it's the most specific format for TextSMS
                if (isset($responseData['responses']) && is_array($responseData['responses']) && !empty($responseData['responses'])) {
                    $firstResponse = $responseData['responses'][0];
                    if (isset($firstResponse['response-code'])) {
                        if ($firstResponse['response-code'] == 200) {
                            $description = strtolower($firstResponse['response-description'] ?? '');
                            if (stripos($description, 'success') !== false || stripos($description, 'sent') !== false || empty($description)) {
                                // TextSMS success format detected - return success immediately
                                return [
                                    'success' => true,
                                    'message' => 'SMS sent successfully',
                                    'data' => $responseData,
                                    'message_id' => $firstResponse['messageid'] ?? null,
                                    'mobile' => $firstResponse['mobile'] ?? null
                                ];
                            } else {
                                // Response code 200 but description indicates failure
                                $hasError = true;
                                $errorMessage = $firstResponse['response-description'] ?? 'SMS sending failed';
                            }
                        } else {
                            // Non-200 response code
                            $hasError = true;
                            $errorMessage = $firstResponse['response-description'] ?? 'SMS sending failed (Code: ' . $firstResponse['response-code'] . ')';
                        }
                    }
                }
                
                // Check for explicit error indicators (only if TextSMS format not detected)
                if (!isset($responseData['responses'])) {
                    $responseStr = json_encode($responseData);
                    foreach ($errorKeywords as $keyword) {
                        if (stripos($responseStr, $keyword) !== false) {
                            $hasError = true;
                            break;
                        }
                    }
                }
                
                // Check for error codes (non-zero error codes indicate failure)
                if (isset($responseData['error_code']) && $responseData['error_code'] != 0) {
                    $hasError = true;
                }
                if (isset($responseData['code']) && $responseData['code'] != 200 && $responseData['code'] < 200) {
                    $hasError = true;
                }
                
                // Format 1: {status: 'success'} or {status: 'sent'} (but not 'failed', 'error', etc.)
                if (isset($responseData['status'])) {
                    $status = strtolower($responseData['status']);
                    if (in_array($status, ['success', 'sent', 'ok', '200', 'queued', 'accepted']) && !$hasError) {
                        $isSuccess = true;
                    } elseif (in_array($status, ['failed', 'error', 'rejected', 'denied'])) {
                        $hasError = true;
                    }
                }
                // Format 2: {success: true}
                elseif (isset($responseData['success']) && ($responseData['success'] === true || $responseData['success'] === 'true' || $responseData['success'] == 1) && !$hasError) {
                    $isSuccess = true;
                }
                // Format 3: {code: 200} or {error_code: 0}
                elseif (isset($responseData['code']) && $responseData['code'] == 200 && !$hasError) {
                    $isSuccess = true;
                }
                elseif (isset($responseData['error_code']) && $responseData['error_code'] == 0 && !$hasError) {
                    $isSuccess = true;
                }
                // Format 4: Check for success keywords in response message
                elseif (isset($responseData['message']) && !$hasError) {
                    $msg = strtolower($responseData['message']);
                    if (stripos($msg, 'success') !== false || stripos($msg, 'sent') !== false || stripos($msg, 'queued') !== false) {
                        $isSuccess = true;
                    } elseif (stripos($msg, 'failed') !== false || stripos($msg, 'error') !== false) {
                        $hasError = true;
                    }
                }
                
                // Extract error message from various possible fields
                if (!$isSuccess || $hasError) {
                    $errorMessage = $responseData['message'] ?? 
                                   $responseData['error'] ?? 
                                   $responseData['msg'] ?? 
                                   $responseData['description'] ??
                                   $responseData['reason'] ??
                                   'SMS sending failed';
                    
                    // Include additional error details if available
                    if (isset($responseData['errors']) && is_array($responseData['errors'])) {
                        $errorMessage .= ': ' . implode(', ', $responseData['errors']);
                    }
                    
                    // Include error code if available
                    if (isset($responseData['error_code'])) {
                        $errorMessage .= ' (Error Code: ' . $responseData['error_code'] . ')';
                    }
                }
            } elseif (!empty($response)) {
                // Non-JSON response - check if it's a success message
                $responseLower = strtolower($response);
                $hasError = false;
                foreach ($errorKeywords as $keyword) {
                    if (stripos($responseLower, $keyword) !== false) {
                        $hasError = true;
                        break;
                    }
                }
                
                if (!$hasError && (stripos($response, 'success') !== false || 
                    stripos($response, 'sent') !== false || 
                    stripos($response, 'ok') !== false ||
                    preg_match('/\b(200|success|sent|queued)\b/i', $response))) {
                    $isSuccess = true;
                    $errorMessage = 'SMS sent successfully';
                } else {
                    // Plain text error
                    $errorMessage = trim($response);
                    if (empty($errorMessage)) {
                        $errorMessage = 'Unknown response from SMS gateway';
                    }
                }
            }
            
            if ($isSuccess) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'http_code' => $httpCode,
                    'response' => $response,
                    'data' => $responseData
                ];
            }
        } else {
            // HTTP error (not 200)
            $errorMessage = 'HTTP Error ' . $httpCode;
            if (is_array($responseData) && isset($responseData['message'])) {
                $errorMessage .= ': ' . $responseData['message'];
            } elseif (!empty($response)) {
                $errorMessage .= ': ' . (is_string($response) ? $response : json_encode($response));
            }
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'http_code' => $httpCode,
                'response' => $response,
                'data' => $responseData
            ];
        }
    }
    
    /**
     * Send bulk SMS to multiple phone numbers
     */
    public function sendBulkSms($phones, $message) {
        $results = [];
        $successCount = 0;
        $failureCount = 0;
        
        foreach ($phones as $phone) {
            $result = $this->sendSms($phone, $message);
            $results[] = [
                'phone' => $phone,
                'result' => $result
            ];
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failureCount++;
            }
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }
        
        return [
            'success' => $failureCount == 0,
            'total' => count($phones),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'results' => $results
        ];
    }
    
    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phone) {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 0, replace with 254
        if (substr($phone, 0, 1) == '0') {
            $phone = '254' . substr($phone, 1);
        }
        
        // If doesn't start with country code, add it
        if (strlen($phone) == 9) {
            $phone = '254' . $phone;
        }
        
        // Validate length (should be 12 digits: 254XXXXXXXXX)
        if (strlen($phone) != 12 || substr($phone, 0, 3) != '254') {
            return false;
        }
        
        return $phone;
    }
    
    /**
     * Log SMS attempt to database
     */
    private function logSms($phone, $message, $response, $httpCode) {
        $db = Database::getInstance()->getConnection();
        
        // Create sms_logs table if it doesn't exist
        try {
            $stmt = $db->prepare("INSERT INTO sms_logs (phone, message, response, http_code, sent_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$phone, $message, $response, $httpCode]);
        } catch (PDOException $e) {
            // Table might not exist, ignore for now
            error_log("SMS log error: " . $e->getMessage());
        }
    }
    
    /**
     * Get SMS balance (if API supports it)
     */
    public function getBalance() {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'SMS API key not configured'
            ];
        }
        
        $balanceUrl = str_replace('/send', '/balance', $this->apiUrl);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $balanceUrl . '?api_key=' . $this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            $data = json_decode($response, true);
            return [
                'success' => true,
                'balance' => $data['balance'] ?? 'Unknown',
                'data' => $data
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Unable to fetch balance'
        ];
    }
}

