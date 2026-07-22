<?php
/**
 * M-Pesa Webhook Controller
 * Handles M-Pesa payment callbacks for automatic fee updates
 */

class MpesaController extends Controller {
    
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
        
        // Get raw POST data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // Log the callback for debugging
        error_log("M-Pesa Callback Received: " . $json);
        error_log("M-Pesa Callback URL: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
        error_log("M-Pesa Callback Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
        
        // Verify this is a valid M-Pesa callback
        if (!isset($data['Body']) || !isset($data['Body']['stkCallback'])) {
            error_log("M-Pesa Callback: Invalid format - missing Body or stkCallback");
            echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback format']);
            exit;
        }
        
        $callback = $data['Body']['stkCallback'];
        $resultCode = $callback['ResultCode'] ?? 1;
        $resultDesc = $callback['ResultDesc'] ?? 'Unknown error';
        
        // If payment was successful
        if ($resultCode == 0 && isset($callback['CallbackMetadata'])) {
            $metadata = $callback['CallbackMetadata']['Item'];
            
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
            
            // Get merchant and checkout request IDs
            $merchantRequestId = $callback['MerchantRequestID'] ?? '';
            $checkoutRequestId = $callback['CheckoutRequestID'] ?? '';
            
            // If account number is not in metadata, get it from stored transaction
            if (empty($accountNumber) && !empty($checkoutRequestId)) {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT account_number, student_id FROM mpesa_transactions WHERE checkout_request_id = ? LIMIT 1");
                $stmt->execute([$checkoutRequestId]);
                $storedTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($storedTransaction) {
                    $accountNumber = $storedTransaction['account_number'];
                }
            }
            
            // Process the payment
            if ($amount > 0 && !empty($mpesaReceipt)) {
                $this->processMpesaPayment([
                    'amount' => $amount,
                    'mpesa_receipt' => $mpesaReceipt,
                    'phone_number' => $phoneNumber,
                    'transaction_date' => $transactionDate,
                    'account_number' => $accountNumber,
                    'merchant_request_id' => $merchantRequestId,
                    'checkout_request_id' => $checkoutRequestId
                ]);
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
     * Fetch M-Pesa transactions from API
     * This method can be called periodically to fetch and process transactions
     */
    public function fetchTransactions() {
        // This endpoint requires authentication
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        require_once APP_PATH . '/helpers/MpesaHelper.php';
        
        // Get last processed transaction date
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT MAX(transaction_date) as last_date FROM mpesa_transactions WHERE reconciled = 1");
        $lastDateResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $lastDate = $lastDateResult['last_date'] ?? date('YmdHis', strtotime('-7 days'));
        
        // Fetch transactions using M-Pesa Account Balance API or Transaction Status Query
        // Note: This is a placeholder - actual implementation depends on M-Pesa API availability
        // For now, transactions are processed via webhook callback
        
        $this->json([
            'success' => true,
            'message' => 'Transaction fetching is handled via webhook callbacks. Transactions are automatically processed when payments are made.',
            'note' => 'To manually fetch transactions, use the M-Pesa Business Payment API or configure webhook callbacks.'
        ]);
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
}

