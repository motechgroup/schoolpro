<?php
/**
 * M-Pesa Helper Class
 * Handles M-Pesa STK Push integration using Daraja API
 */

class MpesaHelper {
    
    /**
     * Get M-Pesa settings from database
     */
    private static function getSettings() {
        try {
            $db = Database::getInstance()->getConnection();
            $keys = [
                'mpesa_api_consumer_key',
                'mpesa_api_consumer_secret',
                'mpesa_api_shortcode',
                'mpesa_api_passkey',
                'mpesa_environment',
                'mpesa_callback_url'
            ];
            
            $placeholders = implode(',', array_fill(0, count($keys), '?'));
            $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($placeholders)");
            $stmt->execute($keys);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $settings = [];
            foreach ($results as $result) {
                $settings[$result['setting_key']] = $result['setting_value'];
            }
            
            return $settings;
        } catch (Exception $e) {
            error_log("Error loading M-Pesa settings: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get access token from M-Pesa API
     */
    public static function getAccessToken() {
        // Try to get credentials from database first, fallback to config
        $settings = self::getSettings();
        
        // Debug: Log what settings were found
        error_log("M-Pesa getAccessToken - Settings found: " . json_encode(array_keys($settings)));
        
        $consumerKey = trim($settings['mpesa_api_consumer_key'] ?? MPESA_CONSUMER_KEY ?? '');
        $consumerSecret = trim($settings['mpesa_api_consumer_secret'] ?? MPESA_CONSUMER_SECRET ?? '');
        
        // Check if credentials are empty (after trimming)
        if (empty($consumerKey) || empty($consumerSecret)) {
            error_log("M-Pesa credentials are missing. Consumer Key: " . (empty($consumerKey) ? 'EMPTY' : 'SET') . ", Consumer Secret: " . (empty($consumerSecret) ? 'EMPTY' : 'SET'));
            error_log("M-Pesa Settings from DB: " . json_encode($settings));
            return null;
        }
        
        // Log credential status (first few chars only for security)
        error_log("M-Pesa API: Attempting to get access token. Consumer Key: " . substr($consumerKey, 0, 10) . "... (length: " . strlen($consumerKey) . ")");
        
        // Get environment from settings
        $environment = trim($settings['mpesa_environment'] ?? MPESA_ENVIRONMENT ?? 'sandbox');
        
        $url = ($environment === 'production') 
            ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
            : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        
        $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log("M-Pesa API cURL Error: " . $curlError);
            error_log("M-Pesa API URL: " . $url);
            error_log("M-Pesa Consumer Key: " . (empty($consumerKey) ? 'EMPTY' : substr($consumerKey, 0, 10) . '...'));
            return null;
        }
        
        if ($httpCode !== 200) {
            error_log("M-Pesa API Error: HTTP $httpCode - Response: " . $response);
            error_log("M-Pesa API URL: " . $url);
            error_log("M-Pesa Environment: " . $environment);
            error_log("M-Pesa Consumer Key (first 15 chars): " . substr($consumerKey, 0, 15));
            
            // Try to parse error response
            $errorData = json_decode($response, true);
            if (isset($errorData['error'])) {
                error_log("M-Pesa API Error Details: " . json_encode($errorData));
                $errorMessage = $errorData['error_description'] ?? $errorData['error'] ?? 'Unknown error';
                
                // Provide more specific error messages
                if (strpos(strtolower($errorMessage), 'invalid') !== false || strpos(strtolower($errorMessage), 'unauthorized') !== false) {
                    error_log("M-Pesa API: Credentials appear to be invalid or unauthorized");
                }
            }
            
            return null;
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            $errorMsg = $result['error_description'] ?? $result['error'] ?? 'Unknown error';
            error_log("M-Pesa API Error: " . $errorMsg);
            error_log("M-Pesa API Full Response: " . json_encode($result));
            return null;
        }
        
        if (empty($result['access_token'])) {
            error_log("M-Pesa API: Access token not found in response");
            error_log("M-Pesa API Response: " . json_encode($result));
            return null;
        }
        
        return $result['access_token'];
    }
    
    /**
     * Initiate STK Push
     */
    public static function initiateSTKPush($phoneNumber, $amount, $accountReference, $transactionDesc) {
        // Get settings first to verify credentials
        $settings = self::getSettings();
        $consumerKey = trim($settings['mpesa_api_consumer_key'] ?? MPESA_CONSUMER_KEY ?? '');
        $consumerSecret = trim($settings['mpesa_api_consumer_secret'] ?? MPESA_CONSUMER_SECRET ?? '');
        
        // Debug: Log settings retrieval
        error_log("M-Pesa STK Push - Settings keys found: " . json_encode(array_keys($settings)));
        error_log("M-Pesa STK Push - Consumer Key: " . (empty($consumerKey) ? 'EMPTY' : 'SET (' . strlen($consumerKey) . ' chars)'));
        error_log("M-Pesa STK Push - Consumer Secret: " . (empty($consumerSecret) ? 'EMPTY' : 'SET (' . strlen($consumerSecret) . ' chars)'));
        
        // Verify credentials are set before attempting to get token
        if (empty($consumerKey) || empty($consumerSecret)) {
            error_log("M-Pesa STK Push: Missing credentials - Consumer Key: " . (empty($consumerKey) ? 'EMPTY' : 'SET') . ", Consumer Secret: " . (empty($consumerSecret) ? 'EMPTY' : 'SET'));
            error_log("M-Pesa STK Push - Full settings: " . json_encode($settings));
            return ['success' => false, 'message' => 'M-Pesa API credentials are not configured. Please go to Settings > Payment Settings and enter your Consumer Key and Consumer Secret.'];
        }
        
        $accessToken = self::getAccessToken();
        
        if (!$accessToken) {
            // Credentials are set but token request failed - check PHP error log for details
            error_log("M-Pesa STK Push: Failed to get access token despite credentials being set");
            error_log("M-Pesa Consumer Key (first 15 chars): " . substr($consumerKey, 0, 15));
            error_log("M-Pesa Environment: " . ($settings['mpesa_environment'] ?? MPESA_ENVIRONMENT ?? 'not set'));
            
            // Check PHP error log for the actual error from getAccessToken
            return ['success' => false, 'message' => 'Failed to get access token from M-Pesa API. Credentials appear to be set, but the API request failed. Please check: 1) Your Consumer Key and Consumer Secret are correct, 2) Your internet connection, 3) The environment setting (Sandbox/Production) matches your credentials, 4) Check PHP error log for detailed error message.'];
        }
        
        // Get other settings from database
        $shortcode = $settings['mpesa_api_shortcode'] ?? MPESA_SHORTCODE;
        $passkey = $settings['mpesa_api_passkey'] ?? MPESA_PASSKEY;
        // Use callback URL from settings, or fallback to current BASE_URL (which auto-detects ngrok)
        $callbackUrl = !empty($settings['mpesa_callback_url']) 
            ? $settings['mpesa_callback_url'] 
            : (defined('BASE_URL') ? BASE_URL . '/mpesa/callback' : MPESA_CALLBACK_URL);
        $environment = $settings['mpesa_environment'] ?? MPESA_ENVIRONMENT;
        
        // Log callback URL for debugging
        error_log("M-Pesa STK Push - Using Callback URL: " . $callbackUrl);
        
        if (empty($shortcode) || empty($passkey)) {
            return ['success' => false, 'message' => 'M-Pesa Shortcode or Passkey is missing. Please configure them in Settings.'];
        }
        
        if (empty($callbackUrl)) {
            return ['success' => false, 'message' => 'M-Pesa Callback URL is missing. Please configure it in Settings > Payment Settings.'];
        }
        
        // Validate callback URL format
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'Invalid Callback URL format. Please enter a valid URL (e.g., https://yourdomain.com/masomo/mpesa/callback)'];
        }
        
        // Ensure callback URL uses HTTPS (required by M-Pesa, except for localhost)
        if (strpos($callbackUrl, 'http://') === 0 && strpos($callbackUrl, 'localhost') === false && strpos($callbackUrl, '127.0.0.1') === false) {
            return ['success' => false, 'message' => 'Callback URL must use HTTPS (except for localhost). Please use https:// in your callback URL.'];
        }
        
        $url = ($environment === 'production')
            ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        
        $timestamp = date('YmdHis');
        $password = base64_encode($shortcode . $passkey . $timestamp);
        
        // Format phone number (remove + and ensure it starts with 254)
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        }
        if (substr($phoneNumber, 0, 3) !== '254') {
            $phoneNumber = '254' . $phoneNumber;
        }
        
        $data = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => $shortcode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
            return [
                'success' => true,
                'merchant_request_id' => $result['MerchantRequestID'],
                'checkout_request_id' => $result['CheckoutRequestID'],
                'response_code' => $result['ResponseCode'],
                'response_description' => $result['ResponseDescription'],
                'customer_message' => $result['CustomerMessage']
            ];
        } else {
            return [
                'success' => false,
                'message' => $result['errorMessage'] ?? 'STK Push failed',
                'response' => $result
            ];
        }
    }
    
    /**
     * Query transaction status
     * Useful for checking transaction status if callback is delayed
     */
    public static function queryTransactionStatus($checkoutRequestId) {
        $accessToken = self::getAccessToken();
        
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to get access token'];
        }
        
        // Get settings from database
        $settings = self::getSettings();
        $shortcode = $settings['mpesa_api_shortcode'] ?? MPESA_SHORTCODE;
        $passkey = $settings['mpesa_api_passkey'] ?? MPESA_PASSKEY;
        $environment = $settings['mpesa_environment'] ?? MPESA_ENVIRONMENT;
        
        $url = ($environment === 'production')
            ? 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';
        
        $timestamp = date('YmdHis');
        $password = base64_encode($shortcode . $passkey . $timestamp);
        
        $data = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 second connection timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log("M-Pesa API Query Error: " . $curlError);
            return [
                'success' => false,
                'message' => 'Connection error: ' . $curlError,
                'data' => null
            ];
        }
        
        $result = json_decode($response, true);
        
        return [
            'success' => $httpCode === 200,
            'data' => $result
        ];
    }
    
    /**
     * Process M-Pesa callback
     */
    public static function processCallback($callbackData) {
        $db = Database::getInstance()->getConnection();
        
        $body = json_decode($callbackData, true);
        
        if (isset($body['Body']['stkCallback'])) {
            $stkCallback = $body['Body']['stkCallback'];
            $merchantRequestId = $stkCallback['MerchantRequestID'];
            $checkoutRequestId = $stkCallback['CheckoutRequestID'];
            $resultCode = $stkCallback['ResultCode'];
            $resultDesc = $stkCallback['ResultDesc'];
            
            // Find transaction
            $stmt = $db->prepare("SELECT * FROM mpesa_transactions WHERE merchant_request_id = ? AND checkout_request_id = ?");
            $stmt->execute([$merchantRequestId, $checkoutRequestId]);
            $transaction = $stmt->fetch();
            
            if ($transaction) {
                $status = ($resultCode == 0) ? 'completed' : 'failed';
                
                if ($resultCode == 0 && isset($stkCallback['CallbackMetadata']['Item'])) {
                    $items = $stkCallback['CallbackMetadata']['Item'];
                    $mpesaReceiptNumber = '';
                    $amount = 0;
                    $phoneNumber = '';
                    $transactionDate = '';
                    
                    foreach ($items as $item) {
                        switch ($item['Name']) {
                            case 'MpesaReceiptNumber':
                                $mpesaReceiptNumber = $item['Value'];
                                break;
                            case 'Amount':
                                $amount = $item['Value'];
                                break;
                            case 'PhoneNumber':
                                $phoneNumber = $item['Value'];
                                break;
                            case 'TransactionDate':
                                $transactionDate = $item['Value'];
                                break;
                        }
                    }
                    
                    // Update transaction
                    $updateStmt = $db->prepare("UPDATE mpesa_transactions 
                                               SET result_code = ?, 
                                                   result_desc = ?,
                                                   mpesa_receipt_number = ?,
                                                   amount = ?,
                                                   phone_number = ?,
                                                   transaction_date = ?,
                                                   status = ?,
                                                   callback_data = ?
                                               WHERE id = ?");
                    $updateStmt->execute([
                        $resultCode,
                        $resultDesc,
                        $mpesaReceiptNumber,
                        $amount,
                        $phoneNumber,
                        $transactionDate,
                        $status,
                        $callbackData,
                        $transaction['id']
                    ]);
                    
                    // If payment was successful, update payment record
                    if ($status === 'completed' && $transaction['payment_id']) {
                        $paymentStmt = $db->prepare("UPDATE payments 
                                                     SET mpesa_receipt = ?, 
                                                         mpesa_transaction_id = ?
                                                     WHERE id = ?");
                        $paymentStmt->execute([
                            $mpesaReceiptNumber,
                            $checkoutRequestId,
                            $transaction['payment_id']
                        ]);
                        
                        // Update invoice balance
                        $invoiceModel = new Invoice();
                        $invoiceModel->updateBalance($transaction['payment_id']);
                    }
                } else {
                    // Update transaction status
                    $updateStmt = $db->prepare("UPDATE mpesa_transactions 
                                               SET result_code = ?, 
                                                   result_desc = ?,
                                                   status = ?,
                                                   callback_data = ?
                                               WHERE id = ?");
                    $updateStmt->execute([
                        $resultCode,
                        $resultDesc,
                        $status,
                        $callbackData,
                        $transaction['id']
                    ]);
                }
            }
        }
    }
}

