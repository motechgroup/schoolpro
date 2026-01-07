<?php
/**
 * M-Pesa Webhook Controller
 * Handles M-Pesa payment callbacks for automatic fee updates
 */

class MpesaController extends Controller {
    
    /**
     * Display M-Pesa transactions page
     */
    public function index() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])) {
            http_response_code(403);
            die("Access denied");
        }
        
        $db = Database::getInstance()->getConnection();
        
        // Get date range
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        // Convert dates to format used in mpesa_transactions table (YYYYMMDDHHmmss)
        $startDateFormatted = date('YmdHis', strtotime($startDate));
        $endDateFormatted = date('YmdHis', strtotime($endDate . ' 23:59:59'));
        
        // Fetch M-Pesa transactions
        $transactions = [];
        try {
            $stmt = $db->prepare("
                SELECT mt.*, 
                       s.id as student_id, 
                       s.admission_number, 
                       s.first_name, 
                       s.last_name,
                       p.receipt_number as payment_receipt,
                       p.payment_date,
                       CASE 
                           WHEN mt.reconciled = 1 OR mt.payment_id IS NOT NULL THEN 1 
                           ELSE 0 
                       END as is_reconciled
                FROM mpesa_transactions mt
                LEFT JOIN students s ON mt.student_id = s.id OR mt.account_number LIKE CONCAT('%', s.admission_number, '%')
                LEFT JOIN payments p ON mt.payment_id = p.id
                WHERE mt.transaction_date >= ? AND mt.transaction_date <= ?
                ORDER BY mt.created_at DESC
                LIMIT 500
            ");
            $stmt->execute([$startDateFormatted, $endDateFormatted]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process transactions to match students
            foreach ($transactions as &$transaction) {
                // If student not matched, try to extract from account_number
                if (empty($transaction['student_id']) && !empty($transaction['account_number'])) {
                    $admissionNumber = $this->extractAdmissionNumber($transaction['account_number']);
                    if ($admissionNumber) {
                        $stmt = $db->prepare("SELECT id, admission_number, first_name, last_name FROM students WHERE admission_number = ? AND status = 'active'");
                        $stmt->execute([$admissionNumber]);
                        $student = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($student) {
                            $transaction['student_id'] = $student['id'];
                            $transaction['admission_number'] = $student['admission_number'];
                            $transaction['first_name'] = $student['first_name'];
                            $transaction['last_name'] = $student['last_name'];
                        }
                    }
                }
            }
            unset($transaction);
            
        } catch (Exception $e) {
            error_log("M-Pesa Transactions Error: " . $e->getMessage());
        }
        
        $data = [
            'title' => 'M-Pesa Transactions - ' . APP_NAME,
            'transactions' => $transactions,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('mpesa/index', $data);
    }
    
    /**
     * M-Pesa callback/webhook handler
     * This endpoint receives payment notifications from M-Pesa
     */
    public function callback() {
        // Prevent any output before JSON
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set JSON header immediately
        header('Content-Type: application/json');
        
        // Allow CORS for M-Pesa callbacks (if needed)
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // Get raw POST data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // Log the callback for debugging
        error_log("M-Pesa Callback Received: " . $json);
        error_log("M-Pesa Callback URL: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
        error_log("M-Pesa Callback Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
        error_log("M-Pesa Callback Headers: " . json_encode(getallheaders()));
        
        // Handle OPTIONS preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'OK']);
            exit;
        }
        
        // Handle both STK Push callbacks and PayBill direct payment callbacks
        $callback = null;
        $isStkCallback = false;
        $isPayBillCallback = false;
        
        // Check for STK Push callback
        if (isset($data['Body']['stkCallback'])) {
            $callback = $data['Body']['stkCallback'];
            $isStkCallback = true;
            error_log("M-Pesa Callback: STK Push callback detected");
        }
        // Check for PayBill direct payment callback (TransactionCallback)
        elseif (isset($data['TransactionType']) || isset($data['Result'])) {
            // PayBill direct payment callback format
            $callback = $data;
            $isPayBillCallback = true;
            error_log("M-Pesa Callback: PayBill direct payment callback detected");
        }
        // Check for alternative PayBill callback format
        elseif (isset($data['Body']['TransactionCallback'])) {
            $callback = $data['Body']['TransactionCallback'];
            $isPayBillCallback = true;
            error_log("M-Pesa Callback: PayBill TransactionCallback format detected");
        }
        
        if (!$callback) {
            error_log("M-Pesa Callback: Invalid format - Data: " . print_r($data, true));
            echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback format']);
            exit;
        }
        
        // Extract result code and description based on callback type
        if ($isStkCallback) {
            $resultCode = $callback['ResultCode'] ?? 1;
            $resultDesc = $callback['ResultDesc'] ?? 'Unknown error';
            $metadata = $callback['CallbackMetadata']['Item'] ?? [];
        } else {
            // PayBill direct payment callback format
            $resultCode = isset($callback['ResultCode']) ? $callback['ResultCode'] : (isset($callback['Result']['ResultCode']) ? $callback['Result']['ResultCode'] : 0);
            $resultDesc = $callback['ResultDescription'] ?? $callback['Result']['ResultDesc'] ?? 'Success';
            $metadata = [];
            
            // Extract transaction details from PayBill callback
            if (isset($callback['TransactionAmount'])) {
                $metadata[] = ['Name' => 'Amount', 'Value' => $callback['TransactionAmount']];
            }
            if (isset($callback['MpesaReceiptNumber'])) {
                $metadata[] = ['Name' => 'MpesaReceiptNumber', 'Value' => $callback['MpesaReceiptNumber']];
            }
            if (isset($callback['PhoneNumber'])) {
                $metadata[] = ['Name' => 'PhoneNumber', 'Value' => $callback['PhoneNumber']];
            }
            if (isset($callback['TransactionDate'])) {
                $metadata[] = ['Name' => 'TransactionDate', 'Value' => $callback['TransactionDate']];
            }
            if (isset($callback['BillRefNumber'])) {
                $metadata[] = ['Name' => 'AccountReference', 'Value' => $callback['BillRefNumber']];
            }
        }
        
        error_log("M-Pesa Callback: ResultCode=$resultCode, ResultDesc=$resultDesc, Type=" . ($isStkCallback ? 'STK Push' : 'PayBill'));
        
        // If payment was successful
        if ($resultCode == 0 && !empty($metadata)) {
            
            // Extract payment details
            $amount = 0;
            $mpesaReceipt = '';
            $phoneNumber = '';
            $transactionDate = '';
            $accountNumber = '';
            
            foreach ($metadata as $item) {
                switch ($item['Name']) {
                    case 'Amount':
                        $amount = floatval($item['Value']);
                        break;
                    case 'MpesaReceiptNumber':
                        $mpesaReceipt = $item['Value'];
                        break;
                    case 'PhoneNumber':
                        $phoneNumber = $item['Value'];
                        break;
                    case 'TransactionDate':
                        $transactionDate = $item['Value'];
                        break;
                }
            }
            
            // Get account number from metadata if available
            if (isset($callback['CallbackMetadata']['Item'])) {
                foreach ($callback['CallbackMetadata']['Item'] as $item) {
                    if (isset($item['Name']) && $item['Name'] === 'AccountReference') {
                        $accountNumber = $item['Value'];
                        break;
                    }
                }
            }
            
            // Get merchant and checkout request IDs (only for STK Push)
            $merchantRequestId = $callback['MerchantRequestID'] ?? '';
            $checkoutRequestId = $callback['CheckoutRequestID'] ?? '';
            
            $db = Database::getInstance()->getConnection();
            
            // For PayBill direct payments, check if transaction already exists by receipt number
            if ($isPayBillCallback && !empty($mpesaReceipt)) {
                $stmt = $db->prepare("SELECT id, account_number, student_id FROM mpesa_transactions WHERE mpesa_receipt_number = ? LIMIT 1");
                $stmt->execute([$mpesaReceipt]);
                $existingTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingTransaction) {
                    // Update existing transaction
                    $updateStmt = $db->prepare("UPDATE mpesa_transactions 
                                               SET result_code = ?, 
                                                   result_desc = ?, 
                                                   status = 'completed',
                                                   amount = COALESCE(NULLIF(?, 0), amount),
                                                   phone_number = COALESCE(NULLIF(?, ''), phone_number),
                                                   transaction_date = COALESCE(NULLIF(?, ''), transaction_date, NOW()),
                                                   account_number = COALESCE(NULLIF(?, ''), account_number)
                                               WHERE id = ?");
                    $updateStmt->execute([$resultCode, $resultDesc, $amount, $phoneNumber, $transactionDate, $accountNumber, $existingTransaction['id']]);
                    error_log("M-Pesa Callback: Updated existing PayBill transaction - Receipt: $mpesaReceipt, Amount: $amount");
                    
                    if (empty($existingTransaction['account_number']) && !empty($accountNumber)) {
                        $accountNumber = $existingTransaction['account_number'];
                    }
                } else {
                    // Create new transaction record for PayBill direct payment
                    $insertStmt = $db->prepare("INSERT INTO mpesa_transactions 
                                               (mpesa_receipt_number, amount, phone_number, transaction_date, account_number, 
                                                status, result_code, result_desc, reconciled, created_at) 
                                               VALUES (?, ?, ?, ?, ?, 'completed', ?, ?, 0, NOW())");
                    $insertStmt->execute([$mpesaReceipt, $amount, $phoneNumber, $transactionDate, $accountNumber, $resultCode, $resultDesc]);
                    error_log("M-Pesa Callback: Created new PayBill transaction - Receipt: $mpesaReceipt, Amount: $amount, Account: $accountNumber");
                }
            }
            
            // For STK Push, update existing transaction
            if ($isStkCallback && !empty($checkoutRequestId)) {
                // If account number is not in metadata, get it from stored transaction
                if (empty($accountNumber)) {
                    $stmt = $db->prepare("SELECT account_number, student_id FROM mpesa_transactions WHERE checkout_request_id = ? LIMIT 1");
                    $stmt->execute([$checkoutRequestId]);
                    $storedTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($storedTransaction) {
                        $accountNumber = $storedTransaction['account_number'];
                    }
                }
                
                try {
                    $updateStmt = $db->prepare("UPDATE mpesa_transactions 
                                               SET result_code = ?, 
                                                   result_desc = ?, 
                                                   status = 'completed',
                                                   mpesa_receipt_number = COALESCE(NULLIF(?, ''), mpesa_receipt_number),
                                                   amount = COALESCE(NULLIF(?, 0), amount),
                                                   phone_number = COALESCE(NULLIF(?, ''), phone_number),
                                                   transaction_date = COALESCE(NULLIF(?, ''), transaction_date, NOW())
                                               WHERE checkout_request_id = ?");
                    $updateStmt->execute([$resultCode, $resultDesc, $mpesaReceipt, $amount, $phoneNumber, $transactionDate, $checkoutRequestId]);
                    error_log("M-Pesa Callback: Updated STK Push transaction - CheckoutRequestID: $checkoutRequestId, Receipt: $mpesaReceipt, Amount: $amount");
                } catch (Exception $e) {
                    error_log("M-Pesa Callback: Error updating transaction: " . $e->getMessage());
                }
            }
            
            // Process the payment (for both STK Push and PayBill)
            if ($amount > 0 && !empty($mpesaReceipt)) {
                try {
                    $this->processMpesaPayment([
                        'amount' => $amount,
                        'mpesa_receipt' => $mpesaReceipt,
                        'phone_number' => $phoneNumber,
                        'transaction_date' => $transactionDate,
                        'account_number' => $accountNumber,
                        'merchant_request_id' => $merchantRequestId,
                        'checkout_request_id' => $checkoutRequestId
                    ]);
                    error_log("M-Pesa Callback: Payment processed successfully - Receipt: $mpesaReceipt, Type: " . ($isStkCallback ? 'STK Push' : 'PayBill'));
                } catch (Exception $e) {
                    error_log("M-Pesa Callback: Error processing payment (transaction already updated): " . $e->getMessage());
                    // Transaction status already updated above, so status check will find it
                }
            } else {
                // Update transaction status even if payment details incomplete
                if (!empty($checkoutRequestId)) {
                    $db = Database::getInstance()->getConnection();
                    // Update transaction with whatever data we have
                    $stmt = $db->prepare("UPDATE mpesa_transactions 
                                         SET result_code = ?, result_desc = ?, status = 'completed', 
                                             mpesa_receipt_number = COALESCE(NULLIF(?, ''), mpesa_receipt_number),
                                             amount = COALESCE(NULLIF(?, 0), amount)
                                         WHERE checkout_request_id = ?");
                    $stmt->execute([$resultCode, $resultDesc, $mpesaReceipt, $amount, $checkoutRequestId]);
                    
                    error_log("M-Pesa Callback: Updated transaction status - CheckoutRequestID: $checkoutRequestId, Receipt: $mpesaReceipt, ResultCode: $resultCode");
                    
                    // If we have receipt and amount, try to process payment even if callback metadata was incomplete
                    if (!empty($mpesaReceipt) && $amount > 0 && $resultCode == 0) {
                        // Get transaction details to process payment
                        $stmt2 = $db->prepare("SELECT * FROM mpesa_transactions WHERE checkout_request_id = ? LIMIT 1");
                        $stmt2->execute([$checkoutRequestId]);
                        $txn = $stmt2->fetch(PDO::FETCH_ASSOC);
                        
                        if ($txn && empty($txn['payment_id'])) {
                            // Process payment with available data
                            $this->processMpesaPayment([
                                'amount' => $amount,
                                'mpesa_receipt' => $mpesaReceipt,
                                'phone_number' => $phoneNumber,
                                'transaction_date' => $transactionDate,
                                'account_number' => $txn['account_number'] ?? '',
                                'merchant_request_id' => $merchantRequestId,
                                'checkout_request_id' => $checkoutRequestId
                            ]);
                        }
                    }
                }
            }
        }
        
        // Handle failed/cancelled payments too
        if ($resultCode != 0) {
            $db = Database::getInstance()->getConnection();
            
            // For STK Push, update by checkout_request_id
            if ($isStkCallback) {
                $checkoutRequestId = $callback['CheckoutRequestID'] ?? '';
                if (!empty($checkoutRequestId)) {
                    try {
                        $stmt = $db->prepare("UPDATE mpesa_transactions 
                                             SET result_code = ?, result_desc = ?, status = 'failed'
                                             WHERE checkout_request_id = ?");
                        $stmt->execute([$resultCode, $resultDesc, $checkoutRequestId]);
                        error_log("M-Pesa Callback: Updated STK Push transaction to failed - CheckoutRequestID: $checkoutRequestId, ResultCode: $resultCode");
                    } catch (Exception $e) {
                        error_log("M-Pesa Callback: Error updating failed transaction: " . $e->getMessage());
                    }
                }
            }
            // For PayBill, update by receipt number if available
            elseif ($isPayBillCallback && !empty($mpesaReceipt)) {
                try {
                    $stmt = $db->prepare("UPDATE mpesa_transactions 
                                         SET result_code = ?, result_desc = ?, status = 'failed'
                                         WHERE mpesa_receipt_number = ?");
                    $stmt->execute([$resultCode, $resultDesc, $mpesaReceipt]);
                    error_log("M-Pesa Callback: Updated PayBill transaction to failed - Receipt: $mpesaReceipt, ResultCode: $resultCode");
                } catch (Exception $e) {
                    error_log("M-Pesa Callback: Error updating failed PayBill transaction: " . $e->getMessage());
                }
            }
        }
        
        // Always return success to M-Pesa (even if processing failed)
        error_log("M-Pesa Callback: Returning success response to M-Pesa");
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback received successfully']);
        exit;
    }
    
    /**
     * Process M-Pesa payment and update student fees
     */
    private function processMpesaPayment($paymentData) {
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Extract student admission number from account number
            // Format: business_number#admission_number (e.g., 12345#100)
            $accountNumber = $paymentData['account_number'];
            $admissionNumber = $this->extractAdmissionNumber($accountNumber);
            
            if (empty($admissionNumber)) {
                error_log("M-Pesa Payment: Could not extract admission number from account number: " . $accountNumber);
                $db->rollBack();
                return;
            }
            
            // Find student by admission number
            $stmt = $db->prepare("SELECT id, first_name, last_name FROM students WHERE admission_number = ? AND status = 'active'");
            $stmt->execute([$admissionNumber]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                error_log("M-Pesa Payment: Student not found for admission number: " . $admissionNumber);
                $db->rollBack();
                return;
            }
            
            $studentId = $student['id'];
            $studentName = $student['first_name'] . ' ' . $student['last_name'];
            
            // Check if this payment was already processed (by receipt or checkout_request_id)
            $stmt = $db->prepare("SELECT id, payment_id FROM mpesa_transactions 
                                 WHERE (mpesa_receipt_number = ? OR checkout_request_id = ?) 
                                 ORDER BY id DESC LIMIT 1");
            $stmt->execute([$paymentData['mpesa_receipt'], $paymentData['checkout_request_id']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing && $existing['payment_id']) {
                error_log("M-Pesa Payment: Payment already processed - Receipt: " . $paymentData['mpesa_receipt']);
                $db->rollBack();
                return;
            }
            
            // If transaction exists but not reconciled, we'll update it instead of creating new
            $existingTransactionId = ($existing && empty($existing['payment_id'])) ? $existing['id'] : null;
            
            // Get current academic year and term
            $currentYear = date('Y') . '/' . (date('Y') + 1);
            $currentTerm = 1; // Default to term 1, can be made configurable
            
            // Get or create invoice for this term
            $invoiceModel = $this->model('Invoice');
            $invoices = $invoiceModel->getByStudent($studentId, $currentYear);
            $invoice = null;
            
            foreach ($invoices as $inv) {
                if ($inv['term'] == $currentTerm) {
                    $invoice = $inv;
                    break;
                }
            }
            
            if (!$invoice) {
                error_log("M-Pesa Payment: No invoice found for student ID: " . $studentId);
                $db->rollBack();
                return;
            }
            
            // Create payment record
            $paymentModel = $this->model('Payment');
            $paymentDataRecord = [
                'invoice_id' => $invoice['id'],
                'student_id' => $studentId,
                'payment_method' => 'mpesa',
                'amount' => $paymentData['amount'],
                'payment_date' => date('Y-m-d'),
                'receipt_number' => $paymentModel->generateReceiptNumber(),
                'mpesa_receipt' => $paymentData['mpesa_receipt'],
                'mpesa_transaction_id' => $paymentData['checkout_request_id'],
                'reference_number' => $paymentData['account_number'],
                'received_by' => 1, // System user
                'remarks' => 'Auto-reconciled M-Pesa payment via STK Push'
            ];
            
            $paymentId = $paymentModel->create($paymentDataRecord);
            
            if ($paymentId) {
                // Update invoice balance (invoice model will use payments table totals)
                $invoiceModel->updateBalance($invoice['id']);
                
                // Update or insert M-Pesa transaction
                if ($existingTransactionId) {
                    // Update existing transaction
                    $stmt = $db->prepare("UPDATE mpesa_transactions 
                                        SET payment_id = ?, merchant_request_id = ?, result_code = 0, 
                                            result_desc = 'Success', amount = ?, mpesa_receipt_number = ?, 
                                            transaction_date = ?, phone_number = ?, account_number = ?, 
                                            student_id = ?, status = 'completed', reconciled = 1
                                        WHERE id = ?");
                    $stmt->execute([
                        $paymentId,
                        $paymentData['merchant_request_id'],
                        $paymentData['amount'],
                        $paymentData['mpesa_receipt'],
                        $paymentData['transaction_date'],
                        $paymentData['phone_number'],
                        $paymentData['account_number'],
                        $studentId,
                        $existingTransactionId
                    ]);
                } else {
                    // Insert new transaction
                    $stmt = $db->prepare("INSERT INTO mpesa_transactions 
                                        (payment_id, merchant_request_id, checkout_request_id, result_code, 
                                         result_desc, amount, mpesa_receipt_number, transaction_date, 
                                         phone_number, account_number, student_id, status, reconciled) 
                                        VALUES (?, ?, ?, 0, 'Success', ?, ?, ?, ?, ?, ?, 'completed', 1)");
                    $stmt->execute([
                        $paymentId,
                        $paymentData['merchant_request_id'],
                        $paymentData['checkout_request_id'],
                        $paymentData['amount'],
                        $paymentData['mpesa_receipt'],
                        $paymentData['transaction_date'],
                        $paymentData['phone_number'],
                        $paymentData['account_number'],
                        $studentId
                    ]);
                }
                
                $db->commit();
                error_log("M-Pesa Payment: Successfully processed payment - Receipt: " . $paymentData['mpesa_receipt'] . ", Student: " . $studentId . ", Payment ID: " . $paymentId);
                
                // Send SMS notification to parent with updated fee status
                require_once APP_PATH . '/helpers/PaymentNotificationHelper.php';
                PaymentNotificationHelper::sendFeePaymentSms($studentId, $paymentData['amount'], $paymentData['mpesa_receipt'], $invoice['id']);
            } else {
                $db->rollBack();
                error_log("M-Pesa Payment: Failed to create payment record");
            }
        } catch (Exception $e) {
            $db->rollBack();
            error_log("M-Pesa Payment Error: " . $e->getMessage());
        }
    }
    
    /**
     * Extract admission number from account number
     * Format: business_number#admission_number (e.g., 12345#100)
     * Also supports: admission_number (if no # separator)
     */
    private function extractAdmissionNumber($accountNumber) {
        // Check if account number contains # separator
        if (strpos($accountNumber, '#') !== false) {
            $parts = explode('#', $accountNumber);
            // Return the part after # (admission number)
            return trim($parts[1] ?? '');
        }
        
        // If no # separator, check for prefix
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mpesa_paybill_account_prefix'");
        $stmt->execute();
        $prefixResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $prefix = $prefixResult['setting_value'] ?? '';
        
        if (!empty($prefix) && strpos($accountNumber, $prefix) === 0) {
            return substr($accountNumber, strlen($prefix));
        }
        
        // If no prefix, assume entire account number is admission number
        return trim($accountNumber);
    }
    
    /**
     * Send SMS confirmation to parent when payment is received
     */
    private function sendPaymentConfirmationSMS($studentId, $amount, $receiptNumber, $studentName) {
        try {
            require_once APP_PATH . '/helpers/SmsHelper.php';
            
            // Get parent phone number
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT p.phone, p.first_name, p.last_name 
                                 FROM parents p 
                                 INNER JOIN student_parents sp ON p.id = sp.parent_id 
                                 WHERE sp.student_id = ? AND p.status = 'active' 
                                 LIMIT 1");
            $stmt->execute([$studentId]);
            $parent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$parent || empty($parent['phone'])) {
                error_log("M-Pesa Payment SMS: No parent phone found for student ID: " . $studentId);
                return;
            }
            
            // Get school name
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'school_name'");
            $stmt->execute();
            $schoolResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $schoolName = $schoolResult['setting_value'] ?? APP_NAME;
            
            // Format amount
            $formattedAmount = number_format($amount, 2);
            
            // Create SMS message
            $message = "Dear {$parent['first_name']}, Payment of KES {$formattedAmount} received for {$studentName} (Receipt: {$receiptNumber}). Thank you! - {$schoolName}";
            
            // Send SMS
            $smsHelper = new SmsHelper();
            $result = $smsHelper->sendSms($parent['phone'], $message);
            
            if ($result['success']) {
                error_log("M-Pesa Payment SMS: Successfully sent to " . $parent['phone']);
            } else {
                error_log("M-Pesa Payment SMS: Failed to send to " . $parent['phone'] . " - " . ($result['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            error_log("M-Pesa Payment SMS Error: " . $e->getMessage());
        }
    }
    
    /**
     * Fetch M-Pesa PayBill transactions
     * Since M-Pesa doesn't provide a direct API to fetch all PayBill transactions,
     * this method uses Account Balance API and processes transactions based on
     * webhook callbacks or manual reconciliation
     */
    public function fetchTransactions() {
        // Prevent any output before JSON
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set JSON header
        header('Content-Type: application/json');
        
        try {
            Auth::requireAuth();
            if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            require_once APP_PATH . '/helpers/MpesaHelper.php';
            
            $days = intval($_GET['days'] ?? 7);
            $startDate = date('Y-m-d', strtotime("-{$days} days"));
            $endDate = date('Y-m-d');
            
            $db = Database::getInstance()->getConnection();
            
            // Get M-Pesa PayBill number from settings
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mpesa_api_shortcode' LIMIT 1");
            $stmt->execute();
            $paybillResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $paybillNumber = $paybillResult['setting_value'] ?? '';
            
            if (empty($paybillNumber)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'M-Pesa PayBill number is not configured. Please configure it in Settings > Payment Settings.'
                ]);
                exit;
            }
            
            // Get all active students with their admission numbers
            $stmt = $db->prepare("SELECT id, admission_number, first_name, last_name FROM students WHERE status = 'active' AND admission_number IS NOT NULL AND admission_number != ''");
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $newTransactions = 0;
            $matchedTransactions = 0;
            
            // Note: M-Pesa doesn't provide a direct API to fetch all PayBill transactions
            // Transactions should be received via webhook callbacks configured in M-Pesa Business Portal
            // This method serves as a trigger to process any pending webhook data
            
            // Check for any pending STK Push transactions that might need status updates
            $stmt = $db->prepare("SELECT * FROM mpesa_transactions 
                                 WHERE status = 'pending' 
                                 AND checkout_request_id IS NOT NULL 
                                 AND checkout_request_id != ''
                                 AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                                 ORDER BY created_at DESC");
            $stmt->execute([$days]);
            $pendingTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($pendingTransactions as $transaction) {
                if (!empty($transaction['checkout_request_id'])) {
                    $queryResult = MpesaHelper::queryTransactionStatus($transaction['checkout_request_id']);
                    
                    if ($queryResult['success'] && isset($queryResult['data']['ResultCode']) && $queryResult['data']['ResultCode'] == 0) {
                        // Transaction confirmed, update it
                        $metadata = $queryResult['data']['CallbackMetadata']['Item'] ?? [];
                        $mpesaReceipt = '';
                        $amount = 0;
                        $phoneNumber = '';
                        $transactionDate = '';
                        
                        foreach ($metadata as $item) {
                            switch ($item['Name']) {
                                case 'MpesaReceiptNumber':
                                    $mpesaReceipt = $item['Value'];
                                    break;
                                case 'Amount':
                                    $amount = floatval($item['Value']);
                                    break;
                                case 'PhoneNumber':
                                    $phoneNumber = $item['Value'];
                                    break;
                                case 'TransactionDate':
                                    $transactionDate = $item['Value'];
                                    break;
                            }
                        }
                        
                        if (!empty($mpesaReceipt)) {
                            $updateStmt = $db->prepare("UPDATE mpesa_transactions 
                                                       SET mpesa_receipt_number = ?, 
                                                           amount = ?,
                                                           phone_number = ?,
                                                           transaction_date = ?,
                                                           status = 'completed',
                                                           result_code = 0,
                                                           result_desc = 'Success'
                                                       WHERE id = ?");
                            $updateStmt->execute([$mpesaReceipt, $amount, $phoneNumber, $transactionDate, $transaction['id']]);
                            $newTransactions++;
                        }
                    }
                }
            }
            
            // Process and match existing transactions to students (including those without account_number initially)
            $stmt = $db->prepare("SELECT * FROM mpesa_transactions 
                                 WHERE (student_id IS NULL OR student_id = 0)
                                 AND status = 'completed'
                                 AND mpesa_receipt_number IS NOT NULL
                                 AND mpesa_receipt_number != ''
                                 AND (transaction_date >= ? OR created_at >= DATE_SUB(NOW(), INTERVAL ? DAY))
                                 ORDER BY transaction_date DESC, created_at DESC");
            $startDateFormatted = date('YmdHis', strtotime($startDate));
            $stmt->execute([$startDateFormatted, $days]);
            $unmatchedTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($unmatchedTransactions as $transaction) {
                $admissionNumber = null;
                $studentId = null;
                
                // Try to extract admission number from account_number
                if (!empty($transaction['account_number'])) {
                    $admissionNumber = $this->extractAdmissionNumber($transaction['account_number']);
                }
                
                // If we have admission number, find student
                if (!empty($admissionNumber)) {
                    $stmt = $db->prepare("SELECT id FROM students WHERE admission_number = ? AND status = 'active' LIMIT 1");
                    $stmt->execute([$admissionNumber]);
                    $student = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($student) {
                        $studentId = $student['id'];
                    }
                }
                
                // If still no match and we have receipt, try to match by checking if receipt was used in payments table
                if (!$studentId && !empty($transaction['mpesa_receipt_number'])) {
                    $stmt = $db->prepare("SELECT student_id FROM payments WHERE mpesa_receipt = ? LIMIT 1");
                    $stmt->execute([$transaction['mpesa_receipt_number']]);
                    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($payment && !empty($payment['student_id'])) {
                        $studentId = $payment['student_id'];
                    }
                }
                
                // Update transaction with matched student
                if ($studentId) {
                    $updateStmt = $db->prepare("UPDATE mpesa_transactions 
                                               SET student_id = ?,
                                                   account_number = COALESCE(NULLIF(?, ''), account_number)
                                               WHERE id = ?");
                    $updateStmt->execute([$studentId, $transaction['account_number'] ?? '', $transaction['id']]);
                    $matchedTransactions++;
                    error_log("Matched transaction {$transaction['mpesa_receipt_number']} to student ID: $studentId");
                }
            }
            
            // Also try to auto-reconcile matched transactions that haven't been reconciled yet
            $stmt = $db->prepare("SELECT mt.* FROM mpesa_transactions mt
                                 INNER JOIN students s ON mt.student_id = s.id
                                 WHERE mt.student_id IS NOT NULL
                                 AND mt.student_id > 0
                                 AND (mt.reconciled = 0 OR mt.payment_id IS NULL)
                                 AND mt.status = 'completed'
                                 AND mt.mpesa_receipt_number IS NOT NULL
                                 AND mt.mpesa_receipt_number != ''
                                 AND mt.amount > 0
                                 AND (mt.transaction_date >= ? OR mt.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY))
                                 ORDER BY mt.transaction_date DESC
                                 LIMIT 50");
            $stmt->execute([$startDateFormatted, $days]);
            $reconcilableTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $reconciledCount = 0;
            foreach ($reconcilableTransactions as $transaction) {
                try {
                    // Check if already reconciled
                    if (!empty($transaction['payment_id'])) {
                        continue;
                    }
                    
                    // Try to process payment using processMpesaPayment
                    $this->processMpesaPayment([
                        'amount' => $transaction['amount'],
                        'mpesa_receipt' => $transaction['mpesa_receipt_number'],
                        'phone_number' => $transaction['phone_number'] ?? '',
                        'transaction_date' => $transaction['transaction_date'] ?? date('YmdHis'),
                        'account_number' => $transaction['account_number'] ?? '',
                        'merchant_request_id' => $transaction['merchant_request_id'] ?? '',
                        'checkout_request_id' => $transaction['checkout_request_id'] ?? ''
                    ]);
                    $reconciledCount++;
                    error_log("Auto-reconciled transaction: {$transaction['mpesa_receipt_number']}");
                } catch (Exception $e) {
                    error_log("Error auto-reconciling transaction {$transaction['mpesa_receipt_number']}: " . $e->getMessage());
                }
            }
            
            $message = "Transaction processing completed.";
            if ($newTransactions > 0) {
                $message .= " Updated {$newTransactions} pending transaction(s).";
            }
            if ($matchedTransactions > 0) {
                $message .= " Matched {$matchedTransactions} transaction(s) to students.";
            }
            if ($reconciledCount > 0) {
                $message .= " Auto-reconciled {$reconciledCount} transaction(s).";
            }
            if ($newTransactions == 0 && $matchedTransactions == 0 && $reconciledCount == 0) {
                $message .= " No new transactions to process. Make sure webhooks are configured in M-Pesa Business Portal. Callback URL: " . BASE_URL . "/mpesa/callback";
            }
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'new_transactions' => $newTransactions,
                'matched_transactions' => $matchedTransactions,
                'reconciled_transactions' => $reconciledCount
            ]);
            exit;
            
        } catch (Exception $e) {
            error_log("Fetch Transactions Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error fetching transactions: ' . $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * Reconcile payment by M-Pesa receipt number
     * This allows manual reconciliation of PayBill payments made directly (without STK Push)
     */
    public function reconcileReceipt() {
        // Prevent any output before JSON
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set JSON header first
        header('Content-Type: application/json');
        
        try {
            // Check authentication without redirecting
            if (!Auth::isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authentication required']);
                exit;
            }
            
            if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            // Verify CSRF token
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }
        
            $invoiceId = intval($_POST['invoice_id'] ?? 0);
            $receiptNumber = trim(sanitize($_POST['receipt_number'] ?? ''));
            $amount = floatval($_POST['amount'] ?? 0);
            $studentId = intval($_POST['student_id'] ?? 0);
        
            if (empty($receiptNumber)) {
                echo json_encode(['success' => false, 'message' => 'Receipt number is required']);
                exit;
            }
            
            if ($amount <= 0) {
                echo json_encode(['success' => false, 'message' => 'Payment amount is required']);
                exit;
            }
            
            $db = Database::getInstance()->getConnection();
            
            try {
            // Check if this receipt was already processed (in payments table or mpesa_transactions)
            $stmt = $db->prepare("SELECT id FROM payments WHERE mpesa_receipt = ? LIMIT 1");
            $stmt->execute([$receiptNumber]);
            $existingPayment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingPayment) {
                echo json_encode(['success' => false, 'message' => 'This receipt has already been processed']);
                exit;
            }
            
            // Also check mpesa_transactions table
            $stmt = $db->prepare("SELECT id, payment_id FROM mpesa_transactions WHERE mpesa_receipt_number = ? AND reconciled = 1 LIMIT 1");
            $stmt->execute([$receiptNumber]);
            $existingTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingTransaction && $existingTransaction['payment_id']) {
                echo json_encode(['success' => false, 'message' => 'This receipt has already been processed']);
                exit;
            }
            
            // If we have a pending transaction with checkout_request_id, try to process it first
            $stmt = $db->prepare("SELECT * FROM mpesa_transactions WHERE mpesa_receipt_number = ? AND reconciled = 0 LIMIT 1");
            $stmt->execute([$receiptNumber]);
            $pendingTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pendingTransaction && !empty($pendingTransaction['checkout_request_id'])) {
                // Query transaction status from M-Pesa API
                require_once APP_PATH . '/helpers/MpesaHelper.php';
                $queryResult = MpesaHelper::queryTransactionStatus($pendingTransaction['checkout_request_id']);
                
                if ($queryResult['success'] && isset($queryResult['data']['ResultCode']) && $queryResult['data']['ResultCode'] == 0) {
                    // Transaction confirmed, process it
                    $this->processMpesaPayment([
                        'amount' => $pendingTransaction['amount'] ?: $amount,
                        'mpesa_receipt' => $receiptNumber,
                        'phone_number' => $pendingTransaction['phone_number'],
                        'transaction_date' => date('YmdHis'),
                        'account_number' => $pendingTransaction['account_number'],
                        'merchant_request_id' => $pendingTransaction['merchant_request_id'],
                        'checkout_request_id' => $pendingTransaction['checkout_request_id']
                    ]);
                    
                    echo json_encode(['success' => true, 'message' => 'Payment reconciled successfully', 'amount' => $pendingTransaction['amount'] ?: $amount]);
                    exit;
                }
            }
            
            // If no pending transaction found, create payment from invoice
            // This handles manual PayBill payments where parent paid directly
            // Find invoice if not provided
            if (!$invoiceId) {
                if ($studentId) {
                    // Find invoice for student
                    $invoiceModel = $this->model('Invoice');
                    $currentYear = date('Y') . '/' . (date('Y') + 1);
                    $invoices = $invoiceModel->getByStudent($studentId, $currentYear);
                    
                    // Find invoice with balance
                    foreach ($invoices as $inv) {
                        if (floatval($inv['balance']) > 0) {
                            $invoiceId = $inv['id'];
                            break;
                        }
                    }
                    
                    // If still no invoice found, use first invoice
                    if (!$invoiceId && !empty($invoices)) {
                        $invoiceId = $invoices[0]['id'];
                    }
                } else {
                    // Try to find student from pending transaction
                    if ($pendingTransaction && $pendingTransaction['student_id']) {
                        $studentId = $pendingTransaction['student_id'];
                        $invoiceModel = $this->model('Invoice');
                        $currentYear = date('Y') . '/' . (date('Y') + 1);
                        $invoices = $invoiceModel->getByStudent($studentId, $currentYear);
                        
                        foreach ($invoices as $inv) {
                            if (floatval($inv['balance']) > 0) {
                                $invoiceId = $inv['id'];
                                break;
                            }
                        }
                        
                        if (!$invoiceId && !empty($invoices)) {
                            $invoiceId = $invoices[0]['id'];
                        }
                    }
                }
            }
            
            if ($invoiceId) {
                $invoiceModel = $this->model('Invoice');
                $invoice = $invoiceModel->findById($invoiceId);
                
                if ($invoice) {
                    $studentModel = $this->model('Student');
                    $student = $studentModel->findById($invoice['student_id']);
                    
                    if ($invoice) {
                        // Get student if not already retrieved
                        if (!$student) {
                            $studentModel = $this->model('Student');
                            $student = $studentModel->findById($invoice['student_id']);
                        }
                        
                        if (!$student) {
                            echo json_encode(['success' => false, 'message' => 'Student not found for this invoice']);
                            exit;
                        }
                        
                        // Validate amount doesn't exceed balance (allow partial payments)
                        $invoiceBalance = floatval($invoice['balance']);
                        if ($amount > $invoiceBalance) {
                            echo json_encode([
                                'success' => false, 
                                'message' => 'Amount (KES ' . number_format($amount, 2) . ') exceeds invoice balance of KES ' . number_format($invoiceBalance, 2)
                            ]);
                            exit;
                        }
                        
                        // Create payment record for this manual PayBill payment
                        $db->beginTransaction();
                        
                        try {
                            $paymentModel = $this->model('Payment');
                            $paymentData = [
                                'invoice_id' => $invoiceId,
                                'student_id' => $invoice['student_id'],
                                'payment_method' => 'mpesa',
                                'amount' => $amount,
                                'payment_date' => date('Y-m-d'),
                                'receipt_number' => $paymentModel->generateReceiptNumber(),
                                'mpesa_receipt' => $receiptNumber,
                                'reference_number' => $student['admission_number'],
                                'received_by' => Auth::userId(),
                                'remarks' => 'Manual PayBill payment reconciled by receipt number'
                            ];
                            
                            $paymentId = $paymentModel->create($paymentData);
                            
                            if ($paymentId) {
                                // Update invoice balance
                                $invoiceModel->updateBalance($invoiceId);
                                
                                // Record or update M-Pesa transaction
                                if ($pendingTransaction) {
                                    // Update existing transaction
                                    $stmt = $db->prepare("UPDATE mpesa_transactions 
                                        SET payment_id = ?, amount = ?, mpesa_receipt_number = ?, 
                                            transaction_date = ?, account_number = ?, student_id = ?, 
                                            status = 'completed', reconciled = 1
                                        WHERE id = ?");
                                    $stmt->execute([
                                        $paymentId,
                                        $amount,
                                        $receiptNumber,
                                        date('YmdHis'),
                                        $pendingTransaction['account_number'] ?: $student['admission_number'],
                                        $invoice['student_id'],
                                        $pendingTransaction['id']
                                    ]);
                                } else {
                                    // Insert new transaction
                                    $stmt = $db->prepare("INSERT INTO mpesa_transactions 
                                        (payment_id, amount, mpesa_receipt_number, transaction_date, 
                                         account_number, student_id, status, reconciled) 
                                        VALUES (?, ?, ?, ?, ?, ?, 'completed', 1)");
                                    $stmt->execute([
                                        $paymentId,
                                        $amount,
                                        $receiptNumber,
                                        date('YmdHis'),
                                        $student['admission_number'],
                                        $invoice['student_id']
                                    ]);
                                }
                                
                                $db->commit();
                                
                                // Send SMS notification
                                require_once APP_PATH . '/helpers/PaymentNotificationHelper.php';
                                PaymentNotificationHelper::sendFeePaymentSms(
                                    $invoice['student_id'], 
                                    $amount, 
                                    $receiptNumber, 
                                    $invoiceId
                                );
                                
                                echo json_encode([
                                    'success' => true, 
                                    'message' => 'Payment reconciled successfully', 
                                    'amount' => $amount,
                                    'receipt_number' => $paymentData['receipt_number']
                                ]);
                                exit;
                            } else {
                                $db->rollBack();
                                echo json_encode(['success' => false, 'message' => 'Failed to create payment record']);
                                exit;
                            }
                        } catch (Exception $e) {
                            $db->rollBack();
                            throw $e;
                        }
                    }
                }
            }
            
            // If we reach here, we couldn't find an invoice
            echo json_encode([
                'success' => false, 
                'message' => 'Could not find invoice for this payment. Please ensure the student has an active invoice, or specify the invoice ID.'
            ]);
            exit;
            
            } catch (Exception $e) {
                error_log("Reconcile receipt error: " . $e->getMessage());
                error_log("Reconcile receipt stack trace: " . $e->getTraceAsString());
                if (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error processing receipt: ' . $e->getMessage()]);
                exit;
            } catch (Error $e) {
                error_log("Reconcile receipt fatal error: " . $e->getMessage());
                error_log("Reconcile receipt stack trace: " . $e->getTraceAsString());
                if (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
                exit;
            } catch (Throwable $e) {
                error_log("Reconcile receipt throwable: " . $e->getMessage());
                error_log("Reconcile receipt stack trace: " . $e->getTraceAsString());
                if (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
                exit;
            }
        } catch (Exception $e) {
            error_log("Reconcile receipt outer error: " . $e->getMessage());
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * Query transaction status by checkout request ID
     */
    public function queryStatus() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $checkoutRequestId = $_GET['checkout_request_id'] ?? $_POST['checkout_request_id'] ?? '';
        
        if (empty($checkoutRequestId)) {
            $this->json(['success' => false, 'message' => 'Checkout request ID is required']);
            return;
        }
        
        require_once APP_PATH . '/helpers/MpesaHelper.php';
        $result = MpesaHelper::queryTransactionStatus($checkoutRequestId);
        
        $this->json($result);
    }
    
    /**
     * Manually add PayBill transaction
     * This allows adding PayBill payments that weren't automatically captured via webhook
     */
    public function addPayBillTransaction() {
        // Prevent any output before JSON
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set JSON header
        header('Content-Type: application/json');
        
        try {
            Auth::requireAuth();
            if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }
            
            // Verify CSRF token
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }
            
            $receiptNumber = trim(sanitize($_POST['receipt_number'] ?? ''));
            $amount = floatval($_POST['amount'] ?? 0);
            $phoneNumber = trim(sanitize($_POST['phone_number'] ?? ''));
            $accountNumber = trim(sanitize($_POST['account_number'] ?? ''));
            $transactionDate = trim(sanitize($_POST['transaction_date'] ?? ''));
            
            // Validate input
            if (empty($receiptNumber)) {
                echo json_encode(['success' => false, 'message' => 'Receipt number is required']);
                exit;
            }
            
            if ($amount <= 0) {
                echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
                exit;
            }
            
            if (empty($phoneNumber)) {
                echo json_encode(['success' => false, 'message' => 'Phone number is required']);
                exit;
            }
            
            if (empty($accountNumber)) {
                echo json_encode(['success' => false, 'message' => 'Account reference is required']);
                exit;
            }
            
            // Format transaction date
            if (empty($transactionDate)) {
                $transactionDate = date('YmdHis');
            } else {
                $transactionDate = date('YmdHis', strtotime($transactionDate));
            }
            
            $db = Database::getInstance()->getConnection();
            
            // Check if transaction already exists
            $stmt = $db->prepare("SELECT id FROM mpesa_transactions WHERE mpesa_receipt_number = ? LIMIT 1");
            $stmt->execute([$receiptNumber]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'This receipt number already exists in the system']);
                exit;
            }
            
            // Extract admission number from account reference
            $admissionNumber = $this->extractAdmissionNumber($accountNumber);
            $studentId = null;
            
            // Try to find student by admission number
            if (!empty($admissionNumber)) {
                $stmt = $db->prepare("SELECT id FROM students WHERE admission_number = ? AND status = 'active' LIMIT 1");
                $stmt->execute([$admissionNumber]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($student) {
                    $studentId = $student['id'];
                }
            }
            
            // Insert transaction
            $stmt = $db->prepare("INSERT INTO mpesa_transactions 
                                (mpesa_receipt_number, amount, phone_number, transaction_date, account_number, 
                                 student_id, status, result_code, result_desc, reconciled, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, 'completed', 0, 'Success', 0, NOW())");
            
            $stmt->execute([
                $receiptNumber,
                $amount,
                $phoneNumber,
                $transactionDate,
                $accountNumber,
                $studentId
            ]);
            
            $transactionId = $db->lastInsertId();
            
            error_log("PayBill Transaction Added: Receipt: $receiptNumber, Amount: $amount, Student ID: " . ($studentId ?? 'N/A'));
            
            echo json_encode([
                'success' => true,
                'message' => 'Transaction added successfully',
                'transaction_id' => $transactionId,
                'student_matched' => !empty($studentId)
            ]);
            exit;
            
        } catch (Exception $e) {
            error_log("Add PayBill Transaction Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error adding transaction: ' . $e->getMessage()]);
            exit;
        }
    }
}

