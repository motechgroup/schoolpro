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
     * Generate Jenga API authentication token
     * Based on Jenga API v3 authentication
     */
    public function getAccessToken() {
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new Exception('Jenga API credentials not configured');
        }
        
        // Jenga API v3 uses OAuth2 token endpoint
        $url = $this->baseUrl . '/identity/v2/token';
        
        $credentials = base64_encode($this->apiKey . ':' . $this->apiSecret);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        // Jenga API may require merchant code in body or as parameter
        $postData = [];
        if (!empty($this->merchantCode)) {
            $postData['merchantCode'] = $this->merchantCode;
        }
        
        if (!empty($postData)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        }
        
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
            throw new Exception('Failed to get Jenga API access token. HTTP Code: ' . $httpCode);
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['accessToken'])) {
            return $data['accessToken'];
        } elseif (isset($data['token'])) {
            return $data['token'];
        } elseif (isset($data['data']['accessToken'])) {
            return $data['data']['accessToken'];
        }
        
        error_log("Jenga API Token Response: " . $response);
        throw new Exception('Invalid response from Jenga API - access token not found');
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
        
        // Jenga API v3 uses POST for mini-statement
        $url = $this->baseUrl . '/account/v3/account/mini-statement';
        
        $postData = [
            'accountNumber' => $accountNumber,
            'countryCode' => 'KE', // Kenya
            'fromDate' => $startDate,
            'toDate' => $endDate
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        
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
            throw new Exception('Failed to fetch transactions from Jenga API. HTTP Code: ' . $httpCode);
        }
        
        $data = json_decode($response, true);
        
        // Handle different response formats
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        } elseif (isset($data['transactions']) && is_array($data['transactions'])) {
            return $data['transactions'];
        } elseif (isset($data['results']) && is_array($data['results'])) {
            return $data['results'];
        } elseif (is_array($data) && !empty($data)) {
            return $data;
        }
        
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
        $url = $this->baseUrl . '/account/v3/accounts/balances/KE/' . $accountNumber;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if (!empty($error)) {
            error_log("Jenga API Balance cURL Error: $error");
            throw new Exception('Failed to connect to Jenga API: ' . $error);
        }
        
        if ($httpCode !== 200) {
            error_log("Jenga API Balance Error: HTTP $httpCode - $response");
            throw new Exception('Failed to fetch account balance from Jenga API. HTTP Code: ' . $httpCode);
        }
        
        $data = json_decode($response, true);
        
        // Handle different response formats
        if (isset($data['data'])) {
            return $data['data'];
        }
        
        return $data;
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

