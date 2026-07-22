<?php
/**
 * KCB Buni API Helper
 * Handles KCB Bank Buni API integration for transaction fetching
 * Documentation: https://buni.kcbgroup.com/discover-apis
 */

class KcbBuniHelper {
    
    private $clientId;
    private $clientSecret;
    private $baseUrl;
    private $environment; // 'sandbox' or 'production'
    
    public function __construct() {
        $db = Database::getInstance()->getConnection();
        
        // Get KCB Buni API settings
        $settings = $this->getSettings($db);
        
        $this->clientId = $settings['kcb_buni_client_id'] ?? '';
        $this->clientSecret = $settings['kcb_buni_client_secret'] ?? '';
        $this->environment = $settings['kcb_buni_environment'] ?? 'sandbox';
        
        // Set base URL based on environment
        // KCB Buni API typically uses different endpoints for sandbox and production
        if ($this->environment === 'production') {
            $this->baseUrl = 'https://api.kcbgroup.com'; // Production URL (update based on actual API docs)
        } else {
            $this->baseUrl = 'https://sandbox.kcbgroup.com'; // Sandbox URL (update based on actual API docs)
        }
    }
    
    /**
     * Get KCB Buni API settings from database
     */
    private function getSettings($db) {
        $keys = [
            'kcb_buni_client_id',
            'kcb_buni_client_secret',
            'kcb_buni_environment',
            'kcb_bank_account'
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
     * Generate KCB Buni API authentication token
     * Uses OAuth2 client credentials flow
     */
    public function getAccessToken() {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception('KCB Buni API credentials not configured');
        }
        
        // KCB Buni API OAuth2 token endpoint
        // Update this URL based on actual KCB Buni API documentation
        $url = $this->baseUrl . '/oauth/token';
        
        // OAuth2 client credentials grant
        $postData = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if (!empty($error)) {
            error_log("KCB Buni API Token cURL Error: $error");
            throw new Exception('Failed to connect to KCB Buni API: ' . $error);
        }
        
        if ($httpCode !== 200) {
            error_log("KCB Buni API Token Error: HTTP $httpCode - $response");
            error_log("KCB Buni API Token Request URL: $url");
            throw new Exception('Failed to get KCB Buni API access token. HTTP Code: ' . $httpCode . '. Response: ' . substr($response, 0, 200));
        }
        
        $data = json_decode($response, true);
        
        // Handle OAuth2 token response
        if (isset($data['access_token'])) {
            return $data['access_token'];
        } elseif (isset($data['token'])) {
            return $data['token'];
        } elseif (isset($data['data']['access_token'])) {
            return $data['data']['access_token'];
        }
        
        error_log("KCB Buni API Token Response: " . $response);
        throw new Exception('Invalid response from KCB Buni API - access token not found. Response: ' . substr($response, 0, 200));
    }
    
    /**
     * Fetch account transactions
     * Uses KCB Buni Account Services API
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
        
        // KCB Buni Account Services - Transaction History endpoint
        // Update this endpoint based on actual KCB Buni API documentation
        $url = $this->baseUrl . '/api/v1/accounts/' . $accountNumber . '/transactions';
        
        // Add query parameters
        $url .= '?fromDate=' . urlencode($startDate) . '&toDate=' . urlencode($endDate);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
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
            error_log("KCB Buni API Transactions cURL Error: $error");
            throw new Exception('Failed to connect to KCB Buni API: ' . $error);
        }
        
        if ($httpCode !== 200) {
            error_log("KCB Buni API Transactions Error: HTTP $httpCode - $response");
            error_log("KCB Buni API Transactions Request URL: $url");
            throw new Exception('Failed to fetch transactions from KCB Buni API. HTTP Code: ' . $httpCode . '. Response: ' . substr($response, 0, 200));
        }
        
        $data = json_decode($response, true);
        
        // Ensure we have valid data
        if (!is_array($data)) {
            error_log("KCB Buni API Transactions: Invalid response format - not an array. Response: " . substr($response, 0, 200));
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
     * Uses KCB Buni Account Services API
     */
    public function getAccountBalance($accountNumber) {
        if (empty($accountNumber)) {
            throw new Exception('Account number is required');
        }
        
        $token = $this->getAccessToken();
        
        // KCB Buni Account Services - Balance endpoint
        // Update this endpoint based on actual KCB Buni API documentation
        $url = $this->baseUrl . '/api/v1/accounts/' . $accountNumber . '/balance';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
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
            error_log("KCB Buni API Balance cURL Error: $error");
            throw new Exception('Failed to connect to KCB Buni API: ' . $error);
        }
        
        if ($httpCode !== 200) {
            error_log("KCB Buni API Balance Error: HTTP $httpCode - $response");
            error_log("KCB Buni API Balance Request URL: $url");
            // Return null instead of throwing (balance fetch is optional)
            return null;
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

