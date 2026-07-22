<?php
/**
 * KCB Bank Controller
 * Handles KCB Bank transaction fetching and reconciliation via Buni API
 */

class KcbBankController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * Display KCB Bank transactions
     */
    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // Get KCB Bank account from settings
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'kcb_bank_account'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $accountNumber = $result['setting_value'] ?? '';
        
        // Get date range
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $transactions = [];
        $balance = null;
        $error = null;
        
        if (!empty($accountNumber)) {
            try {
                $kcb = new KcbBuniHelper();
                
                // Fetch transactions
                $transactions = $kcb->getAccountTransactions($accountNumber, $startDate, $endDate);
                
                // Ensure transactions is always an array
                if (!is_array($transactions)) {
                    $transactions = [];
                }
                
                // Fetch account balance
                try {
                    $balanceData = $kcb->getAccountBalance($accountNumber);
                    if (is_array($balanceData)) {
                        $balance = $balanceData['availableBalance'] ?? $balanceData['balance'] ?? $balanceData['available_balance'] ?? null;
                    }
                } catch (Exception $e) {
                    // Balance fetch is optional, don't fail if it doesn't work
                    error_log("KCB Buni Balance Error: " . $e->getMessage());
                }
                
                // Process transactions - match to students
                foreach ($transactions as &$transaction) {
                    // Ensure transaction is an array before processing
                    if (!is_array($transaction)) {
                        continue;
                    }
                    
                    $student = $kcb->matchTransactionToStudent($transaction);
                    $transaction['matched_student'] = $student;
                    
                    // Check if already reconciled
                    $transaction['reconciled'] = $this->isTransactionReconciled($transaction);
                }
                unset($transaction);
                
            } catch (Exception $e) {
                $error = $e->getMessage();
                error_log("KCB Buni API Error: " . $error);
            }
        }
        
        $data = [
            'title' => 'KCB Bank Transactions - ' . APP_NAME,
            'account_number' => $accountNumber,
            'transactions' => $transactions,
            'balance' => $balance,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'error' => $error,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('kcbbank/index', $data);
    }
    
    /**
     * Fetch transactions from KCB Buni API (AJAX)
     */
    public function fetchTransactions() {
        $db = Database::getInstance()->getConnection();
        
        // Get KCB Bank account
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'kcb_bank_account'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $accountNumber = $result['setting_value'] ?? '';
        
        if (empty($accountNumber)) {
            $this->json(['success' => false, 'message' => 'KCB Bank account not configured']);
            return;
        }
        
        $startDate = $_POST['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_POST['end_date'] ?? date('Y-m-d');
        
        try {
            $kcb = new KcbBuniHelper();
            
            $transactions = $kcb->getAccountTransactions($accountNumber, $startDate, $endDate);
            
            // Ensure transactions is always an array
            if (!is_array($transactions)) {
                $transactions = [];
            }
            
            // Process transactions
            foreach ($transactions as &$transaction) {
                // Ensure transaction is an array before processing
                if (!is_array($transaction)) {
                    continue;
                }
                
                $student = $kcb->matchTransactionToStudent($transaction);
                $transaction['matched_student'] = $student;
                $transaction['reconciled'] = $this->isTransactionReconciled($transaction);
            }
            unset($transaction);
            
            $this->json([
                'success' => true,
                'transactions' => $transactions,
                'count' => count($transactions)
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Reconcile a transaction with a student payment
     */
    public function reconcile() {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $transactionId = sanitize($_POST['transaction_id'] ?? '');
        $studentId = intval($_POST['student_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $transactionDate = sanitize($_POST['transaction_date'] ?? date('Y-m-d'));
        $reference = sanitize($_POST['reference'] ?? '');
        
        if (empty($transactionId) || $studentId <= 0 || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid transaction data']);
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Check if already reconciled
            $stmt = $db->prepare("SELECT id FROM kcb_transactions WHERE transaction_id = ? AND reconciled = 1");
            $stmt->execute([$transactionId]);
            if ($stmt->fetch()) {
                $this->json(['success' => false, 'message' => 'Transaction already reconciled']);
                $db->rollBack();
                return;
            }
            
            // Get current academic year and term
            $currentYear = date('Y') . '/' . (date('Y') + 1);
            $currentTerm = 1; // Default, can be made configurable
            
            // Get or create invoice
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
                $this->json(['success' => false, 'message' => 'No invoice found for student']);
                $db->rollBack();
                return;
            }
            
            // Create payment record
            $paymentModel = $this->model('Payment');
            $paymentData = [
                'invoice_id' => $invoice['id'],
                'student_id' => $studentId,
                'payment_method' => 'kcb',
                'amount' => $amount,
                'payment_date' => $transactionDate,
                'receipt_number' => $paymentModel->generateReceiptNumber(),
                'reference_number' => $reference,
                'received_by' => Auth::userId(),
                'remarks' => 'Reconciled from KCB Bank transaction: ' . $transactionId
            ];
            
            $paymentId = $paymentModel->create($paymentData);
            
            if ($paymentId) {
                // Allocate payment to fee heads
                $feeHeadPaymentModel = $this->model('FeeHeadPayment');
                $feeHeadBreakdown = $feeHeadPaymentModel->getStudentFeeHeadBreakdown($studentId, $currentTerm, $currentYear);
                
                if (!empty($feeHeadBreakdown)) {
                    $totalBalance = 0;
                    foreach ($feeHeadBreakdown as $fh) {
                        $totalBalance += $fh['balance'];
                    }
                    
                    if ($totalBalance > 0) {
                        $allocations = [];
                        foreach ($feeHeadBreakdown as $fh) {
                            if ($fh['balance'] > 0) {
                                $proportion = $fh['balance'] / $totalBalance;
                                $allocatedAmount = min($amount * $proportion, $fh['balance']);
                                if ($allocatedAmount > 0) {
                                    $allocations[$fh['id']] = $allocatedAmount;
                                }
                            }
                        }
                        
                        if (!empty($allocations)) {
                            $feeHeadPaymentModel->createPayments($paymentId, $allocations);
                        }
                    }
                }
                
                // Update invoice balance
                $invoiceModel->updateBalance($invoice['id']);
                
                // Record KCB transaction
                $stmt = $db->prepare("INSERT INTO kcb_transactions 
                                    (transaction_id, student_id, payment_id, amount, transaction_date, 
                                     reference, reconciled, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
                                    ON DUPLICATE KEY UPDATE 
                                    student_id = VALUES(student_id),
                                    payment_id = VALUES(payment_id),
                                    reconciled = 1");
                $stmt->execute([
                    $transactionId,
                    $studentId,
                    $paymentId,
                    $amount,
                    $transactionDate,
                    $reference
                ]);
                
                $db->commit();
                $this->json([
                    'success' => true,
                    'message' => 'Transaction reconciled successfully',
                    'payment_id' => $paymentId
                ]);
            } else {
                $db->rollBack();
                $this->json(['success' => false, 'message' => 'Failed to create payment record']);
            }
        } catch (Exception $e) {
            $db->rollBack();
            error_log("KCB Reconciliation Error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Check if transaction is already reconciled
     */
    private function isTransactionReconciled($transaction) {
        $db = Database::getInstance()->getConnection();
        $transactionId = $transaction['transactionId'] ?? $transaction['id'] ?? '';
        
        if (empty($transactionId)) {
            return false;
        }
        
        $stmt = $db->prepare("SELECT id FROM kcb_transactions WHERE transaction_id = ? AND reconciled = 1");
        $stmt->execute([$transactionId]);
        return $stmt->fetch() !== false;
    }
}

