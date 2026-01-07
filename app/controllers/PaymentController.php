<?php
/**
 * Payment Controller
 * Handles payment processing including M-Pesa integration
 */

class PaymentController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant', 'parent'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * Display payments list or specific receipt
     */
    public function index() {
        $paymentModel = $this->model('Payment');
        $receiptNumber = $_GET['receipt'] ?? '';
        
        // If receipt number is provided, show receipt
        if (!empty($receiptNumber)) {
            $payment = $paymentModel->getByReceiptNumber($receiptNumber);
            
            if (!$payment) {
                $this->setFlash('error', 'Payment receipt not found');
                $this->redirect('/payments');
                return;
            }
            
            $data = [
                'title' => 'Payment Receipt - ' . APP_NAME,
                'payment' => $payment
            ];
            
            $this->view('payments/receipt', $data);
            return;
        }
        
        // Otherwise, show payments list
        $filters = [
            'student_id' => $_GET['student_id'] ?? null,
            'receipt_number' => $_GET['search'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null
        ];
        
        $payments = $paymentModel->getAllWithDetails($filters);
        
        $data = [
            'title' => 'Payments - ' . APP_NAME,
            'payments' => $payments,
            'filters' => $filters,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('payments/index', $data);
    }
    
    /**
     * Process M-Pesa payment
     */
    public function processMpesa() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        require_once APP_PATH . '/helpers/MpesaHelper.php';
        
        $invoiceId = intval($_POST['invoice_id'] ?? 0);
        $phoneNumber = sanitize($_POST['phone_number'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        
        // Validate input
        if (empty($invoiceId) || empty($phoneNumber) || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid payment details']);
            return;
        }
        
        if (!isValidPhone($phoneNumber)) {
            $this->json(['success' => false, 'message' => 'Invalid phone number format']);
            return;
        }
        
        // Get invoice details
        $invoiceModel = $this->model('Invoice');
        $invoice = $invoiceModel->findById($invoiceId);
        
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found']);
            return;
        }
        
        // Check if amount doesn't exceed balance
        if ($amount > $invoice['balance']) {
            $this->json(['success' => false, 'message' => 'Amount exceeds invoice balance']);
            return;
        }
        
        // Format phone number
        $phoneNumber = formatPhone($phoneNumber);
        
        // Build M-Pesa AccountReference using student admission number so callback can map payment to student
        $studentModel = $this->model('Student');
        $student = $studentModel->findById($invoice['student_id']);
        $accountReference = $student && !empty($student['admission_number'])
            ? $student['admission_number']
            : 'INV-' . $invoice['invoice_number']; // fallback

        // Initiate STK Push
        $result = MpesaHelper::initiateSTKPush(
            $phoneNumber,
            $amount,
            $accountReference,
            'School Fees Payment - ' . $invoice['invoice_number']
        );
        
        if ($result['success']) {
            // Create payment record (pending)
            $paymentModel = $this->model('Payment');
            $paymentData = [
                'invoice_id' => $invoiceId,
                'student_id' => $invoice['student_id'],
                'payment_method' => 'mpesa',
                'amount' => $amount,
                'payment_date' => date('Y-m-d'),
                'receipt_number' => $paymentModel->generateReceiptNumber(),
                'received_by' => Auth::userId(),
                'remarks' => 'M-Pesa payment pending confirmation'
            ];
            
            $paymentId = $paymentModel->create($paymentData);
            
            // Save M-Pesa transaction
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO mpesa_transactions 
                                  (payment_id, merchant_request_id, checkout_request_id, amount, phone_number, status) 
                                  VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([
                $paymentId,
                $result['merchant_request_id'],
                $result['checkout_request_id'],
                $amount,
                $phoneNumber
            ]);
            
            $this->json([
                'success' => true,
                'message' => $result['customer_message'] ?? 'Payment request sent. Please complete payment on your phone.',
                'checkout_request_id' => $result['checkout_request_id']
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to initiate payment'
            ]);
        }
    }
    
    /**
     * M-Pesa callback endpoint
     */
    public function mpesaCallback() {
        $callbackData = file_get_contents('php://input');
        
        require_once APP_PATH . '/helpers/MpesaHelper.php';
        MpesaHelper::processCallback($callbackData);
        
        // Return success response to M-Pesa
        $this->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
    
    /**
     * Record cash payment
     */
    public function recordCash() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $invoiceId = intval($_POST['invoice_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $referenceNumber = sanitize($_POST['reference_number'] ?? '');
        
        // Validate input
        if (empty($invoiceId) || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid payment details']);
            return;
        }
        
        // Get invoice
        $invoiceModel = $this->model('Invoice');
        $invoice = $invoiceModel->findById($invoiceId);
        
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found']);
            return;
        }
        
        if ($amount > $invoice['balance']) {
            $this->json(['success' => false, 'message' => 'Amount exceeds invoice balance']);
            return;
        }
        
        // Create payment record
        $paymentModel = $this->model('Payment');
        $paymentData = [
            'invoice_id' => $invoiceId,
            'student_id' => $invoice['student_id'],
            'payment_method' => 'cash',
            'amount' => $amount,
            'payment_date' => date('Y-m-d'),
            'receipt_number' => $paymentModel->generateReceiptNumber(),
            'reference_number' => $referenceNumber,
            'received_by' => Auth::userId(),
            'remarks' => sanitize($_POST['remarks'] ?? '')
        ];
        
        $paymentId = $paymentModel->create($paymentData);
        
        if ($paymentId) {
            // Update invoice balance
            $invoiceModel->updateBalance($invoiceId);

            // Send SMS notification to parent about payment and fee status
            try {
                require_once APP_PATH . '/helpers/PaymentNotificationHelper.php';
                PaymentNotificationHelper::sendFeePaymentSms($invoice['student_id'], $amount, $paymentData['receipt_number'], $invoiceId);
            } catch (Exception $e) {
                error_log("Payment SMS dispatch error (PaymentController::recordCash): " . $e->getMessage());
            }
            
            $this->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'payment_id' => $paymentId
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to record payment']);
        }
    }
}

