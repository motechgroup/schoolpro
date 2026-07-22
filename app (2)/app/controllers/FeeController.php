<?php
/**
 * Fee Controller
 * Handles fee management operations
 */

class FeeController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * Fee management dashboard
     */
    public function index() {
        $invoiceModel = $this->model('Invoice');
        $feeHeadModel = $this->model('FeeHead');
        
        $db = Database::getInstance()->getConnection();
        
        // Get pending invoices
        $stmt = $db->query("SELECT COUNT(*) as count FROM invoices WHERE status = 'pending'");
        $pendingInvoices = $stmt->fetch()['count'];
        
        // Get total outstanding balance
        $stmt = $db->query("SELECT SUM(balance) as total FROM invoices WHERE status IN ('pending', 'partial')");
        $outstandingBalance = $stmt->fetch()['total'] ?? 0;
        
        // Get recent invoices
        $stmt = $db->query("SELECT i.*, s.first_name as student_first_name, s.last_name as student_last_name, s.admission_number
                           FROM invoices i
                           LEFT JOIN students s ON i.student_id = s.id
                           ORDER BY i.created_at DESC
                           LIMIT 10");
        $recentInvoices = $stmt->fetchAll();
        
        $feeHeads = $feeHeadModel->getActive();
        
        $data = [
            'title' => 'Fee Management - ' . APP_NAME,
            'pendingInvoices' => $pendingInvoices,
            'outstandingBalance' => $outstandingBalance,
            'recentInvoices' => $recentInvoices,
            'feeHeads' => $feeHeads
        ];
        
        $this->view('fees/index', $data);
    }
    
    /**
     * Payment reconciliation
     */
    public function reconcile() {
        $studentModel = $this->model('Student');
        $classModel = $this->model('ClassModel');
        $invoiceModel = $this->model('Invoice');
        
        $classId = $_GET['class_id'] ?? null;
        $term = $_GET['term'] ?? 1;
        $academicYear = $_GET['academic_year'] ?? date('Y') . '/' . (date('Y') + 1);
        
        $classes = $classModel->getAllWithDetails($academicYear);
        $students = [];
        
        if ($classId) {
            $students = $studentModel->getByClass($classId);
            
            // Get invoice info for each student
            foreach ($students as &$student) {
                $invoices = $invoiceModel->getByStudent($student['id'], $academicYear);
                
                // Update invoice balances to ensure they're current
                foreach ($invoices as $inv) {
                    $invoiceModel->updateBalance($inv['id']);
                }
                
                // Refetch invoices with updated balances
                $invoices = $invoiceModel->getByStudent($student['id'], $academicYear);
                $student['invoices'] = $invoices;
            }
        }
        
        $data = [
            'title' => 'Payment Reconciliation - ' . APP_NAME,
            'classes' => $classes,
            'students' => $students,
            'classId' => $classId,
            'term' => $term,
            'academicYear' => $academicYear,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('fees/reconcile', $data);
    }
    
    /**
     * Show payment form for student
     */
    public function paymentForm($studentId) {
        // Prevent any output before rendering
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set headers to prevent caching issues
        header('Content-Type: text/html; charset=utf-8');
        
        try {
            $studentModel = $this->model('Student');
            $invoiceModel = $this->model('Invoice');
            $feeHeadPaymentModel = $this->model('FeeHeadPayment');
            
            $student = $studentModel->getStudentWithDetails($studentId);
            
            if (!$student) {
                echo '<div class="text-red-600 p-4">Student not found</div>';
                return;
            }
            
            $term = intval($_GET['term'] ?? 1);
            $academicYear = $_GET['academic_year'] ?? date('Y') . '/' . (date('Y') + 1);
            
            // Get invoices for this term
            $invoices = $invoiceModel->getByStudent($studentId, $academicYear);
            $currentInvoice = null;
            
            foreach ($invoices as $inv) {
                if ($inv['term'] == $term) {
                    $currentInvoice = $inv;
                    break;
                }
            }
            
            if (!$currentInvoice) {
                echo '<div class="text-red-600 p-4">No invoice found for this term. Please assign fee heads first.</div>';
                return;
            }
            
            // Get fee heads with payment breakdown - wrap in try-catch for safety
            $feeHeadBreakdown = [];
            try {
                if (method_exists($feeHeadPaymentModel, 'getStudentFeeHeadBreakdown')) {
                    $feeHeadBreakdown = $feeHeadPaymentModel->getStudentFeeHeadBreakdown($studentId, $term, $academicYear);
                } else {
                    // Method doesn't exist, use empty array
                    error_log("FeeHeadPayment::getStudentFeeHeadBreakdown method not found");
                    $feeHeadBreakdown = [];
                }
            } catch (Exception $e) {
                error_log("Error getting fee head breakdown: " . $e->getMessage());
                $feeHeadBreakdown = []; // Continue with empty breakdown
            }
            
            $data = [
                'student' => $student,
                'invoice' => $currentInvoice,
                'feeHeadBreakdown' => $feeHeadBreakdown,
                'csrf_token' => generateCSRFToken()
            ];
            
            $this->viewPartial('fees/payment_form', $data);
        } catch (Exception $e) {
            error_log("Payment form error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo '<div class="text-red-600 p-4 border border-red-300 rounded">';
            echo '<p class="font-semibold">Error loading payment form</p>';
            echo '<p class="text-sm mt-2">' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p class="text-xs mt-2 text-gray-600">Please check server logs for more details.</p>';
            echo '</div>';
        }
    }
    
    /**
     * Process payment
     */
    public function processPayment($studentId) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $invoiceId = intval($_POST['invoice_id'] ?? 0);
        
        // Get the actual payment amount from the form (prioritize total_amount, fallback to amount)
        $submittedAmount = $_POST['total_amount'] ?? $_POST['amount'] ?? 0;
        $amount = floatval($submittedAmount);
        
        $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash');
        
        // If M-Pesa payment, don't process as regular payment - should use Confirm Payment instead
        if ($paymentMethod === 'mpesa') {
            $this->json([
                'success' => false, 
                'message' => 'For M-Pesa payments, please use "Confirm Payment" button after completing STK Push, or use "Reconcile by Receipt Number" if you paid manually.'
            ]);
            return;
        }
        
        // Log the payment details for debugging
        error_log("Payment submission - Invoice ID: $invoiceId, Submitted Amount: $submittedAmount, Processed Amount: $amount");
        
        if (empty($invoiceId) || $amount <= 0) {
            error_log("Payment validation failed - Invoice ID: $invoiceId, Amount: $amount");
            $this->json(['success' => false, 'message' => 'Invalid payment details. Amount must be greater than 0.']);
            return;
        }
        
        $invoiceModel = $this->model('Invoice');
        $paymentModel = $this->model('Payment');
        
        $invoice = $invoiceModel->findById($invoiceId);
        
        if (!$invoice) {
            error_log("Invoice not found - Invoice ID: $invoiceId");
            $this->json(['success' => false, 'message' => 'Invoice not found']);
            return;
        }
        
        // Validate amount doesn't exceed remaining balance
        if ($amount > $invoice['balance']) {
            error_log("Payment amount exceeds balance - Amount: $amount, Invoice Balance: {$invoice['balance']}");
            $this->json(['success' => false, 'message' => 'Amount exceeds invoice balance of ' . formatCurrency($invoice['balance'])]);
            return;
        }
        
        // Create payment record
        $paymentData = [
            'invoice_id' => $invoiceId,
            'student_id' => $studentId,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'payment_date' => date('Y-m-d'),
            'receipt_number' => $paymentModel->generateReceiptNumber(),
            'reference_number' => sanitize($_POST['reference_number'] ?? ''),
            'received_by' => Auth::userId(),
            'remarks' => sanitize($_POST['remarks'] ?? '')
        ];
        
        // Handle M-Pesa if selected
        if ($paymentMethod === 'mpesa' && !empty($_POST['phone_number'])) {
            require_once APP_PATH . '/helpers/MpesaHelper.php';
            
            // Use student admission number as AccountReference so M-Pesa callback can match by admission
            $studentModel = $this->model('Student');
            $student = $studentModel->findById($studentId);
            $accountReference = $student && !empty($student['admission_number'])
                ? $student['admission_number']
                : 'INV-' . $invoice['invoice_number']; // fallback

            $phoneNumber = formatPhone(sanitize($_POST['phone_number']));
            $result = MpesaHelper::initiateSTKPush(
                $phoneNumber,
                $amount,
                $accountReference,
                'School Fees Payment - ' . $invoice['invoice_number']
            );
            
            if ($result['success']) {
                $paymentData['mpesa_receipt'] = '';
                $paymentData['mpesa_transaction_id'] = $result['checkout_request_id'];
                $paymentData['remarks'] = 'M-Pesa payment pending confirmation';
            } else {
                $this->json(['success' => false, 'message' => $result['message'] ?? 'Failed to initiate M-Pesa payment']);
                return;
            }
        }
        
        try {
            error_log("Creating payment - Invoice ID: $invoiceId, Student ID: $studentId, Amount: $amount, Method: $paymentMethod");
            $paymentId = $paymentModel->create($paymentData);
            
            if (!$paymentId) {
                error_log("Payment creation failed for invoice ID: $invoiceId, amount: $amount");
                $this->json(['success' => false, 'message' => 'Failed to record payment. Please try again.']);
                return;
            }
            
            error_log("Payment created successfully - Payment ID: $paymentId, Amount: $amount");

            // Handle M-Pesa receipt if provided (manual payment)
            if ($paymentMethod === 'mpesa' && !empty($_POST['mpesa_receipt'])) {
                $paymentModel->update($paymentId, [
                    'mpesa_receipt' => sanitize($_POST['mpesa_receipt'])
                ]);
            }

            // Update invoice balance
            if (!$invoiceModel->updateBalance($invoiceId)) {
                error_log("Failed to update invoice balance for invoice ID: $invoiceId");
            }

            // Send SMS notification to parent about payment and fee status
            try {
                require_once APP_PATH . '/helpers/PaymentNotificationHelper.php';
                PaymentNotificationHelper::sendFeePaymentSms($studentId, $amount, $paymentData['receipt_number'], $invoiceId);
            } catch (Exception $e) {
                error_log("Payment SMS dispatch error (FeeController): " . $e->getMessage());
            }
            
            // Determine redirect based on referrer or default to reconcile
            $redirect = BASE_URL . '/students/show/' . $studentId;
            if (!empty($_GET['class_id'])) {
                $redirect = BASE_URL . '/fees/reconcile?class_id=' . $_GET['class_id'] . '&term=' . ($_GET['term'] ?? 1) . '&academic_year=' . urlencode($_GET['academic_year'] ?? (date('Y') . '/' . (date('Y') + 1)));
            }
            
            $this->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'redirect' => $redirect
            ]);
        } catch (Exception $e) {
            error_log("Payment processing error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->json(['success' => false, 'message' => 'An error occurred while processing payment: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Initiate STK Push from payment modal
     */
    public function initiateStkPush() {
        // Clear any previous output and start output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        // Set JSON header first to prevent any HTML output
        header('Content-Type: application/json');
        
        // Suppress any error output
        ini_set('display_errors', 0);
        error_reporting(E_ALL);
        
        try {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $invoiceId = intval($_POST['invoice_id'] ?? 0);
        $phoneNumber = sanitize($_POST['phone_number'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        
        if (empty($invoiceId) || empty($phoneNumber) || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid request. Please provide invoice ID, phone number, and amount.']);
            return;
        }
        
        $invoiceModel = $this->model('Invoice');
        $invoice = $invoiceModel->findById($invoiceId);
        
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found']);
            return;
        }
        
        // Validate amount doesn't exceed balance
        if ($amount > $invoice['balance']) {
            $this->json(['success' => false, 'message' => 'Amount exceeds invoice balance of ' . formatCurrency($invoice['balance'])]);
            return;
        }
        
        // Get student admission number for AccountReference
        $studentModel = $this->model('Student');
        $student = $studentModel->findById($invoice['student_id']);
        $accountReference = $student && !empty($student['admission_number'])
            ? $student['admission_number']
            : 'INV-' . $invoice['invoice_number'];
        
        require_once APP_PATH . '/helpers/MpesaHelper.php';
        
        // Verify M-Pesa credentials are configured
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('mpesa_api_consumer_key', 'mpesa_api_consumer_secret', 'mpesa_api_shortcode', 'mpesa_api_passkey')");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $mpesaSettings = [];
        foreach ($settings as $setting) {
            $key = str_replace('mpesa_api_', '', $setting['setting_key']);
            $mpesaSettings[$key] = trim($setting['setting_value'] ?? '');
        }
        
        // Debug logging
        error_log("M-Pesa Settings Check - Found " . count($settings) . " settings from database");
        error_log("M-Pesa Settings Check - Consumer Key: " . (empty($mpesaSettings['consumer_key']) ? 'EMPTY' : 'SET (' . strlen($mpesaSettings['consumer_key']) . ' chars)'));
        error_log("M-Pesa Settings Check - Consumer Secret: " . (empty($mpesaSettings['consumer_secret']) ? 'EMPTY' : 'SET (' . strlen($mpesaSettings['consumer_secret']) . ' chars)'));
        error_log("M-Pesa Settings Check - Shortcode: " . (empty($mpesaSettings['shortcode']) ? 'EMPTY' : 'SET (' . $mpesaSettings['shortcode'] . ')'));
        error_log("M-Pesa Settings Check - Passkey: " . (empty($mpesaSettings['passkey']) ? 'EMPTY' : 'SET (' . strlen($mpesaSettings['passkey']) . ' chars)'));
        
        if (empty($mpesaSettings['consumer_key']) || empty($mpesaSettings['consumer_secret'])) {
            $this->json([
                'success' => false, 
                'message' => 'M-Pesa API credentials are not configured. Please go to Settings > Payment Settings and enter your Consumer Key and Consumer Secret.'
            ]);
            return;
        }
        
        if (empty($mpesaSettings['shortcode']) || empty($mpesaSettings['passkey'])) {
            $this->json([
                'success' => false, 
                'message' => 'M-Pesa Shortcode or Passkey is missing. Please configure them in Settings > Payment Settings.'
            ]);
            return;
        }
        
        $formattedPhone = formatPhone($phoneNumber);
        
        if (empty($formattedPhone) || strlen($formattedPhone) < 10) {
            $this->json(['success' => false, 'message' => 'Invalid phone number format. Please use format: 254700000000']);
            return;
        }
        
        $result = MpesaHelper::initiateSTKPush(
            $formattedPhone,
            $amount,
            $accountReference,
            'School Fees Payment - ' . $invoice['invoice_number']
        );
        
        if ($result['success']) {
            // Store transaction info for later reconciliation
            $db = Database::getInstance()->getConnection();
            try {
                $stmt = $db->prepare("INSERT INTO mpesa_transactions 
                    (merchant_request_id, checkout_request_id, account_number, amount, 
                     phone_number, student_id, status, reconciled) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', 0)");
                $stmt->execute([
                    $result['merchant_request_id'],
                    $result['checkout_request_id'],
                    $accountReference,
                    $amount,
                    $formattedPhone,
                    $invoice['student_id']
                ]);
            } catch (Exception $e) {
                error_log("Failed to store STK Push transaction: " . $e->getMessage());
            }
            
            $this->json([
                'success' => true,
                'message' => $result['customer_message'] ?? 'STK Push sent successfully',
                'merchant_request_id' => $result['merchant_request_id'],
                'checkout_request_id' => $result['checkout_request_id']
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to initiate STK Push'
            ]);
        }
        } catch (Exception $e) {
            ob_end_clean();
            error_log("STK Push initiation error: " . $e->getMessage());
            error_log("STK Push stack trace: " . $e->getTraceAsString());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error initiating STK Push: ' . $e->getMessage()]);
            exit;
        } catch (Error $e) {
            ob_end_clean();
            error_log("STK Push fatal error: " . $e->getMessage());
            error_log("STK Push stack trace: " . $e->getTraceAsString());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
            exit;
        } catch (Throwable $e) {
            ob_end_clean();
            error_log("STK Push throwable: " . $e->getMessage());
            error_log("STK Push stack trace: " . $e->getTraceAsString());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unexpected error: ' . $e->getMessage()]);
            exit;
        } finally {
            // Clean any output buffer
            if (ob_get_level()) {
                ob_end_clean();
            }
        }
    }
    
    /**
     * Check STK Push payment status
     */
    public function checkPaymentStatus() {
        // Prevent any output before JSON
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set execution time limit for this request
        set_time_limit(30);
        
        // Set JSON header
        header('Content-Type: application/json');
        
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }
        
        $checkoutRequestId = sanitize($_POST['checkout_request_id'] ?? $_GET['checkout_request_id'] ?? '');
        $invoiceId = intval($_POST['invoice_id'] ?? $_GET['invoice_id'] ?? 0);
        
        if (empty($checkoutRequestId)) {
            echo json_encode(['success' => false, 'message' => 'Checkout request ID is required']);
            exit;
        }
        
        $db = Database::getInstance()->getConnection();
        
        try {
            // Add timeout to prevent hanging
            $db->setAttribute(PDO::ATTR_TIMEOUT, 5);
            // Strategy 1: Check mpesa_transactions table by checkout_request_id
            $stmt = $db->prepare("SELECT mt.*, p.id as payment_id, p.amount, p.receipt_number, p.payment_date, p.mpesa_receipt
                                 FROM mpesa_transactions mt
                                 LEFT JOIN payments p ON p.id = mt.payment_id
                                 WHERE mt.checkout_request_id = ? 
                                 ORDER BY mt.id DESC LIMIT 1");
            $stmt->execute([$checkoutRequestId]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($transaction) {
                // If payment_id exists, payment was processed
                if ($transaction['payment_id']) {
                    echo json_encode([
                        'success' => true,
                        'status' => 'completed',
                        'message' => 'Payment confirmed successfully!',
                        'payment_id' => $transaction['payment_id'],
                        'amount' => $transaction['amount'],
                        'receipt_number' => $transaction['receipt_number'],
                        'mpesa_receipt' => $transaction['mpesa_receipt'] ?? $transaction['mpesa_receipt_number'] ?? '',
                        'payment_date' => $transaction['payment_date']
                    ]);
                    exit;
                }
                
                // If transaction exists but not reconciled, check if it has mpesa_receipt_number (payment completed)
                if (!empty($transaction['mpesa_receipt_number']) && ($transaction['status'] === 'completed' || empty($transaction['payment_id']))) {
                    // Payment was confirmed by M-Pesa but not yet reconciled - reconcile it now
                    if ($this->reconcileMpesaTransaction($transaction, $invoiceId)) {
                        // Check again after reconciliation
                        $stmt->execute([$checkoutRequestId]);
                        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($transaction && $transaction['payment_id']) {
                            echo json_encode([
                                'success' => true,
                                'status' => 'completed',
                                'message' => 'Payment confirmed and reconciled!',
                                'payment_id' => $transaction['payment_id'],
                                'amount' => $transaction['amount'],
                                'receipt_number' => $transaction['receipt_number'],
                                'mpesa_receipt' => $transaction['mpesa_receipt'] ?? $transaction['mpesa_receipt_number'] ?? '',
                                'payment_date' => $transaction['payment_date']
                            ]);
                            exit;
                        }
                    }
                }
            }
            
            // Strategy 2: Check payments table directly by mpesa_transaction_id
            $stmt = $db->prepare("SELECT p.* FROM payments p 
                                 WHERE p.mpesa_transaction_id = ? 
                                 ORDER BY p.id DESC LIMIT 1");
            $stmt->execute([$checkoutRequestId]);
            $directPayment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($directPayment) {
                echo json_encode([
                    'success' => true,
                    'status' => 'completed',
                    'message' => 'Payment confirmed successfully!',
                    'payment_id' => $directPayment['id'],
                    'amount' => $directPayment['amount'],
                    'receipt_number' => $directPayment['receipt_number'],
                    'mpesa_receipt' => $directPayment['mpesa_receipt'] ?? '',
                    'payment_date' => $directPayment['payment_date']
                ]);
                exit;
            }
            
            // Strategy 3: Check recent M-Pesa payments for this invoice (last 10 minutes - increased window)
            if ($invoiceId) {
                $stmt = $db->prepare("SELECT p.* FROM payments p 
                                     WHERE p.invoice_id = ? 
                                     AND p.payment_method = 'mpesa' 
                                     AND p.created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                                     AND p.mpesa_receipt IS NOT NULL 
                                     AND p.mpesa_receipt != ''
                                     ORDER BY p.id DESC LIMIT 1");
                $stmt->execute([$invoiceId]);
                $recentPayment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($recentPayment) {
                    // Check if this payment matches our checkout request
                    $stmt2 = $db->prepare("SELECT checkout_request_id FROM mpesa_transactions WHERE payment_id = ? LIMIT 1");
                    $stmt2->execute([$recentPayment['id']]);
                    $mtCheck = $stmt2->fetch(PDO::FETCH_ASSOC);
                    
                    if ($mtCheck && $mtCheck['checkout_request_id'] === $checkoutRequestId) {
                        echo json_encode([
                            'success' => true,
                            'status' => 'completed',
                            'message' => 'Payment confirmed successfully!',
                            'payment_id' => $recentPayment['id'],
                            'amount' => $recentPayment['amount'],
                            'receipt_number' => $recentPayment['receipt_number'],
                            'mpesa_receipt' => $recentPayment['mpesa_receipt'] ?? '',
                            'payment_date' => $recentPayment['payment_date']
                        ]);
                        exit;
                    }
                }
            }
            
            // Strategy 4: Query M-Pesa API for transaction status (ALWAYS query if no payment_id found)
            require_once APP_PATH . '/helpers/MpesaHelper.php';
            
            // Query API immediately if transaction not yet reconciled (aggressive check)
            // Only skip if payment is already reconciled
            $queryAPI = true;
            if ($transaction && !empty($transaction['payment_id'])) {
                // Already reconciled, skip API query
                $queryAPI = false;
                error_log("M-Pesa Payment: Transaction already reconciled - Payment ID: " . $transaction['payment_id']);
            }
            
            // Always query API if no payment_id found (even if transaction doesn't exist or has no receipt)
            if ($queryAPI) {
                error_log("M-Pesa Payment: Querying API for status - CheckoutRequestID: $checkoutRequestId, Transaction exists: " . ($transaction ? 'Yes (ID: ' . $transaction['id'] . ')' : 'No'));
                $queryResult = MpesaHelper::queryTransactionStatus($checkoutRequestId);
                error_log("M-Pesa API Query Result: " . json_encode($queryResult));
            
            if ($queryResult['success'] && isset($queryResult['data'])) {
                $resultCode = $queryResult['data']['ResultCode'] ?? null;
                $resultDesc = $queryResult['data']['ResultDesc'] ?? '';
                
                error_log("M-Pesa API Query - ResultCode: $resultCode, ResultDesc: $resultDesc");
                
                if ($resultCode == 0) {
                    // Payment successful according to M-Pesa API
                    // Extract receipt number from callback metadata if available
                    $mpesaReceipt = '';
                    $amount = 0;
                    $phoneNumber = '';
                    $transactionDate = '';
                    
                    // Try different response formats
                    if (isset($queryResult['data']['CallbackMetadata']['Item'])) {
                        foreach ($queryResult['data']['CallbackMetadata']['Item'] as $item) {
                            if (isset($item['Name']) && isset($item['Value'])) {
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
                        }
                    }
                    
                    error_log("M-Pesa API Query Success - Receipt: $mpesaReceipt, Amount: $amount");
                    
                    // If we don't have receipt/amount from API but transaction exists, use transaction data
                    if ($transaction) {
                        if (empty($mpesaReceipt) && !empty($transaction['mpesa_receipt_number'])) {
                            $mpesaReceipt = $transaction['mpesa_receipt_number'];
                            error_log("M-Pesa API: Using receipt from transaction record: $mpesaReceipt");
                        }
                        if ($amount == 0 && !empty($transaction['amount']) && $transaction['amount'] > 0) {
                            $amount = $transaction['amount'];
                            error_log("M-Pesa API: Using amount from transaction record: $amount");
                        }
                    }
                    
                    // Get transaction from database (refresh)
                    $stmt->execute([$checkoutRequestId]);
                    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($transaction && $transaction['payment_id']) {
                        // Payment already reconciled
                        error_log("M-Pesa Payment: Already reconciled - Payment ID: " . $transaction['payment_id']);
                        echo json_encode([
                            'success' => true,
                            'status' => 'completed',
                            'message' => 'Payment confirmed successfully!',
                            'payment_id' => $transaction['payment_id'],
                            'amount' => $transaction['amount'],
                            'receipt_number' => $transaction['receipt_number'],
                            'mpesa_receipt' => $transaction['mpesa_receipt'] ?? $transaction['mpesa_receipt_number'] ?? '',
                            'payment_date' => $transaction['payment_date']
                        ]);
                        exit;
                    }
                    
                    // Transaction exists but not reconciled - update and reconcile immediately
                    if ($transaction) {
                        error_log("M-Pesa Payment: Transaction found, updating with API data and reconciling");
                        
                        // Update transaction with API data (use existing values if API doesn't provide)
                        $finalReceipt = !empty($mpesaReceipt) ? $mpesaReceipt : ($transaction['mpesa_receipt_number'] ?? '');
                        $finalAmount = $amount > 0 ? $amount : ($transaction['amount'] ?? 0);
                        
                        if (!empty($finalReceipt) && $finalAmount > 0) {
                            // Update transaction
                            $updateStmt = $db->prepare("UPDATE mpesa_transactions 
                                                       SET mpesa_receipt_number = ?, 
                                                           amount = ?,
                                                           status = 'completed',
                                                           transaction_date = COALESCE(NULLIF(?, ''), transaction_date, NOW())
                                                       WHERE checkout_request_id = ?");
                            $updateStmt->execute([$finalReceipt, $finalAmount, $transactionDate, $checkoutRequestId]);
                            
                            // Refresh transaction data
                            $stmt->execute([$checkoutRequestId]);
                            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                            $transaction['mpesa_receipt_number'] = $finalReceipt;
                            $transaction['amount'] = $finalAmount;
                            
                            error_log("M-Pesa Payment: Updated transaction - Receipt: $finalReceipt, Amount: $finalAmount");
                            
                            // Reconcile immediately
                            if ($this->reconcileMpesaTransaction($transaction, $invoiceId)) {
                                error_log("M-Pesa Payment: Reconciliation successful");
                                // Check again after reconciliation
                                $stmt->execute([$checkoutRequestId]);
                                $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                                if ($transaction && $transaction['payment_id']) {
                                    $this->json([
                                        'success' => true,
                                        'status' => 'completed',
                                        'message' => 'Payment confirmed and reconciled!',
                                        'payment_id' => $transaction['payment_id'],
                                        'amount' => $transaction['amount'],
                                        'receipt_number' => $transaction['receipt_number'],
                                        'mpesa_receipt' => $transaction['mpesa_receipt'] ?? $transaction['mpesa_receipt_number'] ?? '',
                                        'payment_date' => $transaction['payment_date']
                                    ]);
                                    return;
                                }
                            } else {
                                error_log("M-Pesa Payment: Reconciliation failed");
                            }
                        } else {
                            error_log("M-Pesa Payment: Missing receipt or amount - Receipt: $finalReceipt, Amount: $finalAmount");
                        }
                    } else {
                        // No transaction found - create one from API data if we have receipt
                        if (!empty($mpesaReceipt) && $amount > 0 && $invoiceId) {
                            error_log("M-Pesa Payment: No transaction found, creating from API data");
                            // Get invoice to find student
                            $invoiceModel = $this->model('Invoice');
                            $invoice = $invoiceModel->findById($invoiceId);
                            
                            if ($invoice) {
                                // Create transaction record
                                $insertStmt = $db->prepare("INSERT INTO mpesa_transactions 
                                    (checkout_request_id, amount, mpesa_receipt_number, transaction_date, 
                                     account_number, student_id, status, reconciled) 
                                    VALUES (?, ?, ?, ?, ?, ?, 'completed', 0)");
                                $insertStmt->execute([
                                    $checkoutRequestId,
                                    $amount,
                                    $mpesaReceipt,
                                    $transactionDate ?: date('YmdHis'),
                                    $invoice['student_id'], // Use student_id as account_number fallback
                                    $invoice['student_id']
                                ]);
                                
                                // Get the transaction and reconcile
                                $stmt->execute([$checkoutRequestId]);
                                $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if ($transaction && $this->reconcileMpesaTransaction($transaction, $invoiceId)) {
                                    $stmt->execute([$checkoutRequestId]);
                                    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                                    if ($transaction && $transaction['payment_id']) {
                                        $this->json([
                                            'success' => true,
                                            'status' => 'completed',
                                            'message' => 'Payment confirmed and reconciled!',
                                            'payment_id' => $transaction['payment_id'],
                                            'amount' => $transaction['amount'],
                                            'receipt_number' => $transaction['receipt_number'],
                                            'mpesa_receipt' => $transaction['mpesa_receipt'] ?? $transaction['mpesa_receipt_number'] ?? '',
                                            'payment_date' => $transaction['payment_date']
                                        ]);
                                        return;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Payment confirmed by API but reconciliation incomplete
                    // Try one more time to reconcile with available data
                    if ($transaction) {
                        // Use transaction data if API didn't provide receipt
                        if (empty($mpesaReceipt) && !empty($transaction['mpesa_receipt_number'])) {
                            $mpesaReceipt = $transaction['mpesa_receipt_number'];
                        }
                        if ($amount == 0 && !empty($transaction['amount'])) {
                            $amount = $transaction['amount'];
                        }
                        
                        // Try to reconcile again with combined data
                        if (!empty($mpesaReceipt) && $amount > 0) {
                            $transaction['mpesa_receipt_number'] = $mpesaReceipt;
                            $transaction['amount'] = $amount;
                            
                            if ($this->reconcileMpesaTransaction($transaction, $invoiceId)) {
                                $stmt->execute([$checkoutRequestId]);
                                $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                                if ($transaction && $transaction['payment_id']) {
                                    $this->json([
                                        'success' => true,
                                        'status' => 'completed',
                                        'message' => 'Payment confirmed and reconciled!',
                                        'payment_id' => $transaction['payment_id'],
                                        'amount' => $transaction['amount'],
                                        'receipt_number' => $transaction['receipt_number'],
                                        'mpesa_receipt' => $transaction['mpesa_receipt'] ?? $transaction['mpesa_receipt_number'] ?? '',
                                        'payment_date' => $transaction['payment_date']
                                    ]);
                                    return;
                                }
                            }
                        }
                    }
                    
                    // Payment confirmed but still processing reconciliation
                    $this->json([
                        'success' => true,
                        'status' => 'pending',
                        'message' => 'Payment confirmed by M-Pesa. Processing reconciliation...',
                        'result_desc' => $resultDesc,
                        'mpesa_receipt' => $mpesaReceipt
                    ]);
                } else if ($resultCode == 1032) {
                    // Request cancelled by user
                    $this->json([
                        'success' => false,
                        'status' => 'cancelled',
                        'message' => 'Payment was cancelled',
                        'result_code' => $resultCode,
                        'result_desc' => $resultDesc
                    ]);
                } else {
                    // Payment failed or other error
                    $this->json([
                        'success' => false,
                        'status' => 'failed',
                        'message' => 'Payment failed: ' . $resultDesc,
                        'result_code' => $resultCode,
                        'result_desc' => $resultDesc
                    ]);
                }
            } else {
                // API query failed or still pending - check if we have transaction record with receipt
                if ($transaction) {
                    // If transaction has receipt number, try to reconcile immediately
                    if (!empty($transaction['mpesa_receipt_number'])) {
                        error_log("M-Pesa Payment: Found transaction with receipt: " . $transaction['mpesa_receipt_number']);
                        if ($this->reconcileMpesaTransaction($transaction, $invoiceId)) {
                            $stmt->execute([$checkoutRequestId]);
                            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($transaction && $transaction['payment_id']) {
                                error_log("M-Pesa Payment: Successfully reconciled transaction with receipt");
                                $this->json([
                                    'success' => true,
                                    'status' => 'completed',
                                    'message' => 'Payment confirmed and reconciled!',
                                    'payment_id' => $transaction['payment_id'],
                                    'amount' => $transaction['amount'],
                                    'receipt_number' => $transaction['receipt_number'],
                                    'mpesa_receipt' => $transaction['mpesa_receipt'] ?? $transaction['mpesa_receipt_number'] ?? '',
                                    'payment_date' => $transaction['payment_date']
                                ]);
                                return;
                            }
                        }
                    } else {
                        // Transaction exists but no receipt yet - check if callback updated it
                        error_log("M-Pesa Payment: Transaction exists but no receipt yet - CheckoutRequestID: $checkoutRequestId");
                    }
                }
                
                // Still pending - log for debugging
                error_log("M-Pesa Payment Status Check: Still pending - CheckoutRequestID: $checkoutRequestId, Transaction exists: " . ($transaction ? 'Yes (ID: ' . $transaction['id'] . ')' : 'No'));
                
                $this->json([
                    'success' => true,
                    'status' => 'pending',
                    'message' => 'Waiting for payment confirmation...'
                ]);
            }
            }
        } catch (Exception $e) {
            error_log("Check payment status error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error checking payment status: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Reconcile M-Pesa transaction that was confirmed but not yet processed
     */
    private function reconcileMpesaTransaction($transaction, $invoiceId) {
        $db = Database::getInstance()->getConnection();
        
        try {
            error_log("M-Pesa Reconciliation: Starting - Transaction ID: " . ($transaction['id'] ?? 'N/A') . ", Receipt: " . ($transaction['mpesa_receipt_number'] ?? 'N/A') . ", Amount: " . ($transaction['amount'] ?? 'N/A'));
            
            if (empty($transaction['mpesa_receipt_number']) || empty($transaction['amount'])) {
                error_log("M-Pesa Reconciliation: Failed - Missing receipt or amount");
                return false;
            }
            
            // Check if already reconciled
            if (!empty($transaction['payment_id'])) {
                error_log("M-Pesa Reconciliation: Already reconciled - Payment ID: " . $transaction['payment_id']);
                return true;
            }
            
            // Get invoice if not provided
            if (!$invoiceId && !empty($transaction['student_id'])) {
                $invoiceModel = $this->model('Invoice');
                $currentYear = date('Y') . '/' . (date('Y') + 1);
                $invoices = $invoiceModel->getByStudent($transaction['student_id'], $currentYear);
                foreach ($invoices as $inv) {
                    if ($inv['term'] == 1) { // Default to term 1
                        $invoiceId = $inv['id'];
                        break;
                    }
                }
            }
            
            if (!$invoiceId) {
                error_log("M-Pesa Reconciliation: Failed - Invoice ID not found for student: " . ($transaction['student_id'] ?? 'N/A'));
                return false;
            }
            
            error_log("M-Pesa Reconciliation: Proceeding - Invoice ID: $invoiceId, Student ID: " . ($transaction['student_id'] ?? 'N/A'));
            
            $db->beginTransaction();
            
            // Check if payment already exists for this receipt
            $checkStmt = $db->prepare("SELECT id FROM payments WHERE mpesa_receipt = ? LIMIT 1");
            $checkStmt->execute([$transaction['mpesa_receipt_number']]);
            $existingPayment = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingPayment) {
                error_log("M-Pesa Reconciliation: Payment already exists - Payment ID: " . $existingPayment['id']);
                // Update transaction to link to existing payment
                $updateStmt = $db->prepare("UPDATE mpesa_transactions 
                                           SET payment_id = ?, reconciled = 1 
                                           WHERE id = ?");
                $updateStmt->execute([$existingPayment['id'], $transaction['id']]);
                $db->commit();
                return true;
            }
            
            // Create payment record
            $paymentModel = $this->model('Payment');
            $paymentData = [
                'invoice_id' => $invoiceId,
                'student_id' => $transaction['student_id'],
                'payment_method' => 'mpesa',
                'amount' => $transaction['amount'],
                'payment_date' => date('Y-m-d', strtotime($transaction['transaction_date'] ?? 'now')),
                'receipt_number' => $paymentModel->generateReceiptNumber(),
                'mpesa_receipt' => $transaction['mpesa_receipt_number'],
                'mpesa_transaction_id' => $transaction['checkout_request_id'] ?? '',
                'reference_number' => $transaction['account_number'] ?? '',
                'received_by' => 1, // System user
                'remarks' => 'Auto-reconciled M-Pesa payment via Confirm Payment'
            ];
            
            $paymentId = $paymentModel->create($paymentData);
            
            if ($paymentId) {
                error_log("M-Pesa Reconciliation: Payment created - Payment ID: $paymentId");
                
                // Update invoice balance
                $invoiceModel = $this->model('Invoice');
                $invoiceModel->updateBalance($invoiceId);
                
                // Update mpesa_transaction to mark as reconciled
                $stmt = $db->prepare("UPDATE mpesa_transactions 
                                     SET payment_id = ?, reconciled = 1 
                                     WHERE id = ?");
                $stmt->execute([$paymentId, $transaction['id']]);
                
                $db->commit();
                
                error_log("M-Pesa Reconciliation: Success - Payment ID: $paymentId, Receipt: " . $transaction['mpesa_receipt_number']);
                
                // Send SMS notification
                require_once APP_PATH . '/helpers/PaymentNotificationHelper.php';
                PaymentNotificationHelper::sendFeePaymentSms(
                    $transaction['student_id'], 
                    $transaction['amount'], 
                    $transaction['mpesa_receipt_number'], 
                    $invoiceId
                );
                
                return true;
            } else {
                error_log("M-Pesa Reconciliation: Failed to create payment record");
                $db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error reconciling M-Pesa transaction: " . $e->getMessage());
            return false;
        }
    }
}

