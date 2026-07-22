<?php
/**
 * Jenga API Helper
 * Handles Equity Bank Jenga API integration for transaction fetching
 */

class JengaHelper {
    
    private $apiKey;
    private $apiSecret;
    private $merchantCode;
    private $baseUrl;
    private $environment; // 'sandbox' or 'production'
    
    public function __construct() {
        $db = Database::getInstance()->getConnection();
        
        // Get Jenga API settings
        $settings = $this->getSettings($db);
        
        $this->apiKey = $settings['jenga_api_key'] ?? '';
        $this->apiSecret = $settings['jenga_api_secret'] ?? '';
        $this->merchantCode = $settings['jenga_merchant_code'] ?? '';
        $this->environment = $settings['jenga_environment'] ?? 'sandbox';
        
        // Set base URL based on environment
        if ($this->environment === 'production') {
            $this->baseUrl = 'https://api.finserve.africa';
        } else {
            $this->baseUrl = 'https://uat.finserve.africa';
        }
    }
    
    /**
     * Get Jenga API settings from database
     */
    private function getSettings($db) {
        $keys = [
            'jenga_api_key',
            'jenga_api_secret',
            'jenga_merchant_code',
            'jenga_environment',
            'equity_bank_account'
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
    }
    
    /**
     * Generate signature for Jenga API request
     * Signature is HMAC-SHA256 of the request body using the API secret
     * 
     * @param string $requestBody JSON string of the request body
     * @return string Base64 encoded signature
     */
    private function generateSignature($requestBody) {
        if (empty($this->apiSecret)) {
            throw new Exception('API Secret is required for signature generation');
        }
        
        // Generate HMAC-SHA256 signature
        $signature = hash_hmac('sha256', $requestBody, $this->apiSecret, true);
        
        // Return base64 encoded signature
        return base64_encode($signature);
    }
    
    /**
     * Generate Jenga API authentication token
     * Based on Jenga API v3 authentication
     * Correct endpoint: /authentication/api/v3/authenticate/merchant
     */
    public function getAccessToken() {
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new Exception('Jenga API credentials not configured');
        }
        
        if (empty($this->merchantCode)) {
            throw new Exception('Jenga API merchant code not configured');
        }
        
        // Jenga API v3 uses merchant authentication endpoint
        // Test: https://uat.finserve.africa/authentication/api/v3/authenticate/merchant
        // Prod: https://api.finserve.africa/authentication/api/v3/authenticate/merchant
        $url = $this->baseUrl . '/authentication/api/v3/authenticate/merchant';
        
        // Jenga API requires Api-Key header and merchantCode + consumerSecret in body
        $postData = [
            'merchantCode' => $this->merchantCode,
            'consumerSecret' => $this->apiSecret
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Api-Key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if (!empty($error)) {
            error_log("Jenga API Token cURL Error: $error");
            throw new Exception('Failed to connect to Jenga API: ' . $error);
        }
        
        if ($httpCode !== 200) {
            error_log("Jenga API Token Error: HTTP $httpCode - $response");
            error_log("Jenga API Token Request URL: $url");
            error_log("Jenga API Token Request Data: " . json_encode($postData));
            throw new Exception('Failed to get Jenga API access token. HTTP Code: ' . $httpCode . '. Response: ' . substr($response, 0, 200));
        }
        
        $data = json_decode($response, true);
        
        // Jenga API returns accessToken in response
        if (isset($data['accessToken'])) {
            return $data['accessToken'];
        } elseif (isset($data['token'])) {
            return $data['token'];
        } elseif (isset($data['data']['accessToken'])) {
            return $data['data']['accessToken'];
        } elseif (isset($data['data']['token'])) {
            return $data['data']['token'];
        }
        
        error_log("Jenga API Token Response: " . $response);
        throw new Exception('Invalid response from Jenga API - access token not found. Response: ' . substr($response, 0, 200));
    }
    
    /**
     * Fetch account transactions
     * Uses Jenga API v3 account mini-statement endpoint
     */
    public function getAccountTransactions($accountNumber, $startDate = null, $endDate = null) {
        if (empty($accountNumber)) {
            throw new Exception('Account number is required');
        }
        
        $token = $this->getAccessToken();
        
        // Default to last 30 days if dates not provided
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }
        
        // Jenga API v3 account full statement endpoint
        // Correct endpoint: /v3-apis/account-api/v3.0/accounts/fullStatement
        // Test: https://uat.finserve.africa/v3-apis/account-api/v3.0/accounts/fullStatement
        // Prod: https://api.finserve.africa/v3-apis/account-api/v3.0/accounts/fullStatement
        $url = $this->baseUrl . '/v3-apis/account-api/v3.0/accounts/fullStatement';
        
        // Request body parameters as per Jenga API documentation
        $postData = [
            'countryCode' => 'KE', // Kenya - ISO Alpha-2 country code
            'accountNumber' => $accountNumber,
            'fromDate' => $startDate, // YYYY-MM-DD format
            'toDate' => $endDate,     // YYYY-MM-DD format
            'limit' => 100,           // Optional: Maximum number of transactions
            'reference' => ''          // Optional: Reference for tracking
        ];
        
        // Convert to JSON string for signature generation
        $requestBody = json_encode($postData);
        
        // Generate signature for the request
        $signature = $this->generateSignature($requestBody);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
            'signature: ' . $signature
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if (!empty($error)) {
            error_log("Jenga API Transactions cURL Error: $error");
            throw new Exception('Failed to connect to Jenga API: ' . $error);
        }
        
        if ($httpCode !== 200) {
            error_log("Jenga API Transactions Error: HTTP $httpCode - $response");
            error_log("Jenga API Transactions Request URL: $url");
            error_log("Jenga API Transactions Request Data: " . json_encode($postData));
            throw new Exception('Failed to fetch transactions from Jenga API. HTTP Code: ' . $httpCode . '. Response: ' . substr($response, 0, 200));
        }
        
        $data = json_decode($response, true);
        
        // Ensure we have valid data
        if (!is_array($data)) {
            error_log("Jenga API Transactions: Invalid response format - not an array. Response: " . substr($response, 0, 200));
            return [];
        }
        
        // Handle different response formats
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        } elseif (isset($data['transactions']) && is_array($data['transactions'])) {
            return $data['transactions'];
        } elseif (isset($data['results']) && is_array($data['results'])) {
            return $data['results'];
        } elseif (is_array($data) && !empty($data)) {
            // Check if data is a list of transactions (numeric array)
            if (isset($data[0]) && is_array($data[0])) {
                return $data;
            }
        }
        
        // Empty response is still success (no transactions)
        return [];
    }
    
    /**
     * Get account balance
     * Uses Jenga API v3 account balance endpoint
     */
    public function getAccountBalance($accountNumber) {
        if (empty($accountNumber)) {
            throw new Exception('Account number is required');
        }
        
        $token = $this->getAccessToken();
        
        // Jenga API v3 balance endpoint
        // Try the v3-apis pattern first, then fallback to other patterns
        $balanceEndpoints = [
            $this->baseUrl . '/v3-apis/account-api/v3.0/accounts/balances/KE/' . $accountNumber,
            $this->baseUrl . '/account/api/v3/accounts/balances/KE/' . $accountNumber,
            $this->baseUrl . '/account/v3/accounts/balances/KE/' . $accountNumber
        ];
        
        $lastBalanceError = null;
        $lastBalanceResponse = null;
        
        foreach ($balanceEndpoints as $balanceUrl) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $balanceUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if (!empty($error)) {
                $lastBalanceError = 'cURL Error: ' . $error;
                continue;
            }
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                if (isset($data['data'])) {
                    return $data['data'];
                }
                return $data;
            }
            
            $lastBalanceError = "HTTP $httpCode";
            $lastBalanceResponse = $response;
        }
        
        // If all balance endpoints failed, log but don't throw (balance is optional)
        error_log("Jenga API Balance: All endpoints failed. Last error: $lastBalanceError");
        error_log("Jenga API Balance Last Response: " . substr($lastBalanceResponse, 0, 200));
        
        // Return null instead of throwing (balance fetch is optional)
        return null;
    }
    
    /**
     * Parse transaction reference to extract student admission number
     */
    public function parseStudentReference($reference) {
        if (empty($reference)) {
            return null;
        }
        
        // Try to extract admission number from reference
        // Common formats: "ADM100", "SCH100", "100", etc.
        $reference = trim($reference);
        
        // Remove common prefixes (case insensitive)
        $prefixes = ['ADM', 'SCH', 'STU', 'STUDENT'];
        foreach ($prefixes as $prefix) {
            if (stripos($reference, $prefix) === 0) {
                $reference = substr($reference, strlen($prefix));
                break;
            }
        }
        
        // Extract numeric part
        preg_match('/\d+/', $reference, $matches);
        if (!empty($matches)) {
            return $matches[0];
        }
        
        return null;
    }
    
    /**
     * Match transaction to student
     */
    public function matchTransactionToStudent($transaction) {
        $db = Database::getInstance()->getConnection();
        
        // Try to extract student admission number from transaction reference
        $reference = $transaction['reference'] ?? $transaction['narration'] ?? $transaction['description'] ?? $transaction['transactionReference'] ?? '';
        $admissionNumber = $this->parseStudentReference($reference);
        
        if ($admissionNumber) {
            $stmt = $db->prepare("SELECT id, admission_number, first_name, last_name FROM students WHERE admission_number = ? AND status = 'active'");
            $stmt->execute([$admissionNumber]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($student) {
                return $student;
            }
        }
        
        // Try matching by amount and date (if transaction amount matches outstanding balance)
        // This is a fallback method
        $amount = abs(floatval($transaction['amount'] ?? $transaction['transactionAmount'] ?? 0));
        $transactionDate = $transaction['transactionDate'] ?? $transaction['date'] ?? date('Y-m-d');
        
        if ($amount > 0) {
            // Find students with outstanding balances matching this amount
            $stmt = $db->prepare("
                SELECT DISTINCT s.id, s.admission_number, s.first_name, s.last_name
                FROM students s
                INNER JOIN invoices i ON s.id = i.student_id
                WHERE s.status = 'active' 
                AND i.balance >= ? 
                AND i.balance <= ?
                AND i.status IN ('pending', 'partial')
                LIMIT 10
            ");
            $minAmount = $amount * 0.95; // Allow 5% variance
            $maxAmount = $amount * 1.05;
            $stmt->execute([$minAmount, $maxAmount]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($students) === 1) {
                return $students[0];
            }
        }
        
        return null;
    }
}

