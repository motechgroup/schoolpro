<?php
/**
 * WhatsApp Helper Class
 * Handles WhatsApp message sending via various APIs
 * Supports: WhatsApp Business API, WhatsApp Cloud API, Twilio, MessageBird
 */

class WhatsAppHelper {
    private $apiKey;
    private $apiSecret;
    private $phoneNumberId;
    private $businessAccountId;
    private $apiUrl;
    private $provider; // 'cloud_api', 'business_api', 'twilio', 'messagebird', 'green_api'
    
    public function __construct() {
        // Get WhatsApp settings from database
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'whatsapp_%'");
        $settings = $stmt->fetchAll();
        
        $settingsMap = [];
        foreach ($settings as $setting) {
            $settingsMap[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $this->apiKey = $settingsMap['whatsapp_api_key'] ?? '';
        $this->apiSecret = $settingsMap['whatsapp_api_secret'] ?? '';
        $this->phoneNumberId = $settingsMap['whatsapp_phone_number_id'] ?? '';
        $this->businessAccountId = $settingsMap['whatsapp_business_account_id'] ?? '';
        $this->apiUrl = $settingsMap['whatsapp_api_url'] ?? '';
        $this->provider = $settingsMap['whatsapp_provider'] ?? 'cloud_api';
    }
    
    /**
     * Send WhatsApp message to a single phone number
     */
    public function sendMessage($phone, $message, $mediaUrl = null, $mediaType = null) {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'WhatsApp API key not configured. Please configure WhatsApp settings.'
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
        
        // Route to appropriate provider
        switch ($this->provider) {
            case 'cloud_api':
            case 'business_api':
                return $this->sendViaCloudAPI($phone, $message, $mediaUrl, $mediaType);
            case 'twilio':
                return $this->sendViaTwilio($phone, $message, $mediaUrl, $mediaType);
            case 'messagebird':
                return $this->sendViaMessageBird($phone, $message, $mediaUrl, $mediaType);
            case 'green_api':
                return $this->sendViaGreenAPI($phone, $message, $mediaUrl, $mediaType);
            default:
                return $this->sendViaCloudAPI($phone, $message, $mediaUrl, $mediaType);
        }
    }
    
    /**
     * Send via WhatsApp Cloud API (Meta's official API)
     */
    private function sendViaCloudAPI($phone, $message, $mediaUrl = null, $mediaType = null) {
        if (empty($this->phoneNumberId)) {
            return [
                'success' => false,
                'message' => 'WhatsApp Phone Number ID not configured'
            ];
        }
        
        // Use provided API URL or default to Cloud API
        $apiUrl = $this->apiUrl ?: "https://graph.facebook.com/v18.0/{$this->phoneNumberId}/messages";
        
        // Prepare message payload
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
        ];
        
        // If media URL is provided, send as media message
        if ($mediaUrl && $mediaType) {
            $payload['type'] = $mediaType; // 'image', 'document', 'video', 'audio'
            $payload[$mediaType] = [
                'link' => $mediaUrl
            ];
            
            // Add caption if it's an image or video
            if (in_array($mediaType, ['image', 'video']) && !empty($message)) {
                $payload[$mediaType]['caption'] = $message;
            }
        } else {
            // Text message
            $payload['type'] = 'text';
            $payload['text'] = [
                'body' => $message
            ];
        }
        
        // Send request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Log attempt
        $this->logWhatsApp($phone, $message, $response, $httpCode);
        
        if ($curlError) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $curlError
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode == 200 && isset($responseData['messages'][0]['id'])) {
            return [
                'success' => true,
                'message' => 'WhatsApp message sent successfully',
                'message_id' => $responseData['messages'][0]['id'],
                'data' => $responseData
            ];
        } else {
            $errorMessage = $responseData['error']['message'] ?? 'Failed to send WhatsApp message';
            return [
                'success' => false,
                'message' => $errorMessage,
                'http_code' => $httpCode,
                'response' => $responseData
            ];
        }
    }
    
    /**
     * Send via Twilio WhatsApp API
     */
    private function sendViaTwilio($phone, $message, $mediaUrl = null, $mediaType = null) {
        $accountSid = $this->apiKey;
        $authToken = $this->apiSecret;
        $fromNumber = $this->phoneNumberId; // Twilio WhatsApp number (format: whatsapp:+14155238886)
        
        if (empty($accountSid) || empty($authToken) || empty($fromNumber)) {
            return [
                'success' => false,
                'message' => 'Twilio credentials not fully configured'
            ];
        }
        
        $apiUrl = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
        
        $data = [
            'From' => $fromNumber,
            'To' => 'whatsapp:' . $phone,
            'Body' => $message
        ];
        
        if ($mediaUrl) {
            $data['MediaUrl'] = $mediaUrl;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$accountSid}:{$authToken}");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->logWhatsApp($phone, $message, $response, $httpCode);
        
        $responseData = json_decode($response, true);
        
        if ($httpCode == 201 && isset($responseData['sid'])) {
            return [
                'success' => true,
                'message' => 'WhatsApp message sent successfully',
                'message_id' => $responseData['sid'],
                'data' => $responseData
            ];
        } else {
            $errorMessage = $responseData['message'] ?? 'Failed to send WhatsApp message';
            return [
                'success' => false,
                'message' => $errorMessage,
                'http_code' => $httpCode,
                'response' => $responseData
            ];
        }
    }
    
    /**
     * Send via MessageBird WhatsApp API
     */
    private function sendViaMessageBird($phone, $message, $mediaUrl = null, $mediaType = null) {
        $apiUrl = 'https://conversations.messagebird.com/v1/send';
        
        $payload = [
            'to' => $phone,
            'from' => $this->phoneNumberId,
            'type' => 'text',
            'content' => [
                'text' => $message
            ]
        ];
        
        if ($mediaUrl) {
            $payload['type'] = $mediaType ?: 'image';
            $payload['content'] = [
                'image' => ['url' => $mediaUrl]
            ];
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: AccessKey ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->logWhatsApp($phone, $message, $response, $httpCode);
        
        $responseData = json_decode($response, true);
        
        if ($httpCode == 201 || $httpCode == 200) {
            return [
                'success' => true,
                'message' => 'WhatsApp message sent successfully',
                'data' => $responseData
            ];
        } else {
            $errorMessage = $responseData['errors'][0]['description'] ?? 'Failed to send WhatsApp message';
            return [
                'success' => false,
                'message' => $errorMessage,
                'http_code' => $httpCode,
                'response' => $responseData
            ];
        }
    }
    
    /**
     * Send via Green API (popular WhatsApp Business API alternative)
     */
    private function sendViaGreenAPI($phone, $message, $mediaUrl = null, $mediaType = null) {
        $instanceId = $this->apiKey;
        $apiToken = $this->apiSecret;
        
        if (empty($instanceId)) {
            return [
                'success' => false,
                'message' => 'Green API Instance ID (API Key) is required. Please enter it in WhatsApp settings.'
            ];
        }
        
        if (empty($apiToken)) {
            return [
                'success' => false,
                'message' => 'Green API Token (API Secret) is required. Please enter it in WhatsApp settings.'
            ];
        }
        
        // Extract instance ID from API URL if provided (format: https://7105.api.greenapi.com)
        if (empty($instanceId) && !empty($this->apiUrl)) {
            if (preg_match('/https?:\/\/(\d+)\.api\.greenapi\.com/', $this->apiUrl, $matches)) {
                $instanceId = $matches[1];
            }
        }
        
        // Use custom API URL if provided, otherwise use default format
        if (!empty($this->apiUrl)) {
            $baseUrl = rtrim($this->apiUrl, '/');
            // If URL is in format https://7105.api.greenapi.com, use it directly
            if (preg_match('/https?:\/\/\d+\.api\.greenapi\.com/', $baseUrl)) {
                $apiUrl = "{$baseUrl}/waInstance{$instanceId}/sendMessage/{$apiToken}";
            } else {
                // Otherwise construct from base URL
                $apiUrl = "{$baseUrl}/waInstance{$instanceId}/sendMessage/{$apiToken}";
            }
        } else {
            $apiUrl = "https://api.green-api.com/waInstance{$instanceId}/sendMessage/{$apiToken}";
        }
        
        $payload = [
            'chatId' => $phone . '@c.us',
            'message' => $message
        ];
        
        if ($mediaUrl) {
            // For media, use sendFileByUrl endpoint
            if (!empty($this->apiUrl)) {
                $baseUrl = rtrim($this->apiUrl, '/');
                $fileUrl = "{$baseUrl}/waInstance{$instanceId}/sendFileByUrl/{$apiToken}";
            } else {
                $fileUrl = "https://api.green-api.com/waInstance{$instanceId}/sendFileByUrl/{$apiToken}";
            }
            $filePayload = [
                'chatId' => $phone . '@c.us',
                'urlFile' => $mediaUrl,
                'fileName' => basename($mediaUrl),
                'caption' => $message
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fileUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($filePayload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            $this->logWhatsApp($phone, $message, $response, $httpCode);
            
            $responseData = json_decode($response, true);
            
            // Check for cURL errors
            if ($curlError) {
                return [
                    'success' => false,
                    'message' => 'Connection error: ' . $curlError,
                    'http_code' => $httpCode,
                    'response' => $response
                ];
            }
            
            // Check for Green API errors (even with HTTP 200)
            if (isset($responseData['error'])) {
                $errorMsg = $responseData['error'] ?? 'Unknown error';
                if (isset($responseData['errorText'])) {
                    $errorMsg .= ': ' . $responseData['errorText'];
                }
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'http_code' => $httpCode,
                    'response' => $responseData
                ];
            }
            
            // Success: idMessage is present
            if (isset($responseData['idMessage'])) {
                return [
                    'success' => true,
                    'message' => 'WhatsApp message sent successfully',
                    'message_id' => $responseData['idMessage'],
                    'data' => $responseData
                ];
            }
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            $this->logWhatsApp($phone, $message, $response, $httpCode);
            
            $responseData = json_decode($response, true);
            
            // Check for cURL errors
            if ($curlError) {
                return [
                    'success' => false,
                    'message' => 'Connection error: ' . $curlError,
                    'http_code' => $httpCode,
                    'response' => $response
                ];
            }
            
            // Check for Green API errors (even with HTTP 200)
            if (isset($responseData['error'])) {
                $errorMsg = $responseData['error'] ?? 'Unknown error';
                if (isset($responseData['errorText'])) {
                    $errorMsg .= ': ' . $responseData['errorText'];
                }
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'http_code' => $httpCode,
                    'response' => $responseData
                ];
            }
            
            // Success: idMessage is present
            if (isset($responseData['idMessage'])) {
                return [
                    'success' => true,
                    'message' => 'WhatsApp message sent successfully',
                    'message_id' => $responseData['idMessage'],
                    'data' => $responseData
                ];
            }
        }
        
        // If we get here, something went wrong
        $errorMessage = 'Failed to send WhatsApp message';
        if (isset($responseData['errorText'])) {
            $errorMessage = $responseData['errorText'];
        } elseif (isset($responseData['error'])) {
            $errorMessage = $responseData['error'];
        } elseif (!empty($response)) {
            $errorMessage = 'Unexpected response: ' . substr($response, 0, 200);
        }
        
        return [
            'success' => false,
            'message' => $errorMessage,
            'http_code' => $httpCode,
            'response' => $responseData ?? $response
        ];
    }
    
    /**
     * Send document/file via WhatsApp
     */
    public function sendDocument($phone, $filePath, $caption = '', $fileName = null) {
        // Upload file to a publicly accessible URL first (or use existing URL)
        // For now, assume filePath is already a URL
        $fileUrl = $filePath;
        
        if (!filter_var($fileUrl, FILTER_VALIDATE_URL)) {
            // If it's a local file, you'd need to upload it first
            return [
                'success' => false,
                'message' => 'File URL is required for WhatsApp document sending'
            ];
        }
        
        return $this->sendMessage($phone, $caption, $fileUrl, 'document');
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
     * Log WhatsApp message attempt to database
     */
    private function logWhatsApp($phone, $message, $response, $httpCode) {
        $db = Database::getInstance()->getConnection();
        
        try {
            $stmt = $db->prepare("INSERT INTO whatsapp_logs (phone, message, response, http_code, sent_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$phone, $message, $response, $httpCode]);
        } catch (PDOException $e) {
            // Table might not exist, ignore for now
            error_log("WhatsApp log error: " . $e->getMessage());
        }
    }
    
    /**
     * Check if WhatsApp is configured
     */
    public function isConfigured() {
        if (empty($this->apiKey)) {
            return false;
        }
        
        // For Green API, both Instance ID and API Token are required
        if ($this->provider === 'green_api') {
            return !empty($this->apiKey) && !empty($this->apiSecret);
        }
        
        // For Twilio, both Account SID and Auth Token are required
        if ($this->provider === 'twilio') {
            return !empty($this->apiKey) && !empty($this->apiSecret);
        }
        
        // For Cloud API, Phone Number ID is also required
        if ($this->provider === 'cloud_api' || $this->provider === 'business_api') {
            return !empty($this->apiKey) && !empty($this->phoneNumberId);
        }
        
        return true;
    }
}

