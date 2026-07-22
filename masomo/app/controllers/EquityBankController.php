<?php
/**
 * Equity Bank Controller
 * Handles Equity Bank transaction fetching and reconciliation via Jenga API
 */

class EquityBankController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * Display Equity Bank transactions
     */
    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // Get Equity Bank account from settings
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'equity_bank_account'");
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
                $jenga = new JengaHelper();
                
                // Fetch transactions
                $transactions = $jenga->getAccountTransactions($accountNumber, $startDate, $endDate);
                
                // Fetch account balance
                try {
                    $balanceData = $jenga->getAccountBalance($accountNumber);
                    $balance = $balanceData['availableBalance'] ?? $balanceData['balance'] ?? null;
                } catch (Exception $e) {
                    // Balance fetch is optional, don't fail if it doesn't work
                    error_log("Jenga Balance Error: " . $e->getMessage());
                }
                
                // Process transactions - match to students
                foreach ($transactions as &$transaction) {
                    $student = $jenga->matchTransactionToStudent($transaction);
                    $transaction['matched_student'] = $student;
                    
                    // Check if already reconciled
                    $transaction['reconciled'] = $this->isTransactionReconciled($transaction);
                }
                unset($transaction);
                
            } catch (Exception $e) {
                $error = $e->getMessage();
                error_log("Jenga API Error: " . $error);
            }
        }
        
        $data = [
            'title' => 'Equity Bank Transactions - ' . APP_NAME,
            'account_number' => $accountNumber,
            'transactions' => $transactions,
            'balance' => $balance,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'error' => $error,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('equitybank/index', $data);
    }
    
    /**
     * Fetch transactions from Jenga API (AJAX)
     */
    public function fetchTransactions() {
        $db = Database::getInstance()->getConnection();
        
        // Get Equity Bank account
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'equity_bank_account'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $accountNumber = $result['setting_value'] ?? '';
        
        if (empty($accountNumber)) {
            $this->json(['success' => false, 'message' => 'Equity Bank account not configured']);
            return;
        }
        
        $startDate = $_POST['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_POST['end_date'] ?? date('Y-m-d');
        
        try {
            $jenga = new JengaHelper();
            
            $transactions = $jenga->getAccountTransactions($accountNumber, $startDate, $endDate);
            
            // Process transactions
            foreach ($transactions as &$transaction) {
                $student = $jenga->matchTransactionToStudent($transaction);
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
            $stmt = $db->prepare("SELECT id FROM equity_transactions WHERE transaction_id = ? AND reconciled = 1");
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
                'payment_method' => 'equity',
                'amount' => $amount,
                'payment_date' => $transactionDate,
                'receipt_number' => $paymentModel->generateReceiptNumber(),
                'reference_number' => $reference,
                'received_by' => Auth::userId(),
                'remarks' => 'Reconciled from Equity Bank transaction: ' . $transactionId
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
                
                // Record Equity transaction
                $stmt = $db->prepare("INSERT INTO equity_transactions 
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
            error_log("Equity Reconciliation Error: " . $e->getMessage());
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
        
        $stmt = $db->prepare("SELECT id FROM equity_transactions WHERE transaction_id = ? AND reconciled = 1");
        $stmt->execute([$transactionId]);
        return $stmt->fetch() !== false;
    }
}

