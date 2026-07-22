<?php
/**
 * Fee Report Controller
 * Generates detailed fee reports per student
 */

class FeeReportController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant', 'parent'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * Student fee report - detailed breakdown by fee head
     */
    public function student($studentId) {
        $studentModel = $this->model('Student');
        $feeHeadPaymentModel = $this->model('FeeHeadPayment');
        $invoiceModel = $this->model('Invoice');
        
        $student = $studentModel->getStudentWithDetails($studentId);
        
        if (!$student) {
            $this->setFlash('error', 'Student not found');
            $this->redirect('/students');
            return;
        }
        
        // Check if parent is viewing their own child
        if (Auth::hasRole('parent')) {
            $parentModel = $this->model('ParentModel');
            $parent = $parentModel->findByUserId(Auth::userId());
            
            if (!$parent || $student['parent_id'] != $parent['id']) {
                $this->setFlash('error', 'Access denied');
                $this->redirect('/parent/dashboard');
                return;
            }
        }
        
        $term = intval($_GET['term'] ?? 1);
        $academicYear = $_GET['academic_year'] ?? date('Y') . '/' . (date('Y') + 1);
        
        // Get fee head breakdown (per fee structure)
        $feeHeadBreakdown = $feeHeadPaymentModel->getStudentFeeHeadBreakdown($studentId, $term, $academicYear);
        
        // Get payment details for each fee head
        // Also get all payments for the invoice to show complete payment history
        $paymentModel = $this->model('Payment');
        $paymentDetailsCache = [];
        
        foreach ($feeHeadBreakdown as &$feeHead) {
            $payments = $feeHeadPaymentModel->getByStudentFeeHead($feeHead['id']);
            $feeHead['payments'] = $payments;
            
            // Collect all payment IDs for this fee head
            foreach ($payments as $payment) {
                if (!empty($payment['payment_id']) && !isset($paymentDetailsCache[$payment['payment_id']])) {
                    // Get full payment details including M-Pesa transaction code
                    $fullPayment = $paymentModel->findById($payment['payment_id']);
                    if ($fullPayment) {
                        $paymentDetailsCache[$payment['payment_id']] = $fullPayment;
                    }
                }
            }
        }
        unset($feeHead);
        
        // Enhance payment data with M-Pesa transaction details
        foreach ($feeHeadBreakdown as &$feeHead) {
            foreach ($feeHead['payments'] as &$payment) {
                if (!empty($payment['payment_id']) && isset($paymentDetailsCache[$payment['payment_id']])) {
                    $fullPayment = $paymentDetailsCache[$payment['payment_id']];
                    $payment['mpesa_receipt'] = $fullPayment['mpesa_receipt'] ?? '';
                    $payment['mpesa_transaction_id'] = $fullPayment['mpesa_transaction_id'] ?? '';
                    $payment['created_at'] = $fullPayment['created_at'] ?? $payment['payment_date'];
                }
            }
            unset($payment);
        }
        unset($feeHead);
        
        // Calculate totals based on invoice so they match SMS/fee balances
        $totalAmount = 0;
        $totalPaid = 0;
        $totalBalance = 0;
        $invoice = null;
        
        $invoices = $invoiceModel->getByStudent($studentId, $academicYear);
        foreach ($invoices as $inv) {
            if ($inv['term'] == $term) {
                // Ensure invoice balance is up to date
                $invoiceModel->updateBalance($inv['id']);
                $updated = $invoiceModel->findById($inv['id']);
                if ($updated) {
                    $invoice = $updated;
                    $totalAmount += $updated['total_amount'];
                    $totalPaid += $updated['paid_amount'];
                    $totalBalance += $updated['balance'];
                }
            }
        }
        
        // Get all payments for this invoice with full details
        $allInvoicePayments = [];
        if ($invoice) {
            $paymentModel = $this->model('Payment');
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT p.*, 
                                          u.first_name as received_by_first_name,
                                          u.last_name as received_by_last_name
                                   FROM payments p
                                   LEFT JOIN users u ON p.received_by = u.id
                                   WHERE p.invoice_id = ?
                                   ORDER BY p.payment_date DESC, p.created_at DESC");
            $stmt->execute([$invoice['id']]);
            $allInvoicePayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $data = [
            'title' => 'Fee Report - ' . APP_NAME,
            'student' => $student,
            'feeHeadBreakdown' => $feeHeadBreakdown,
            'allPayments' => $allInvoicePayments,
            'term' => $term,
            'academicYear' => $academicYear,
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'totalBalance' => $totalBalance
        ];
        
        $this->view('fees/report_student', $data);
    }
    
    /**
     * Record payment for specific fee head
     */
    public function recordPayment($studentId) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $studentFeeHeadId = intval($_POST['student_fee_head_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash');
        $term = intval($_POST['term'] ?? 1);
        $academicYear = sanitize($_POST['academic_year'] ?? '');
        
        if (empty($studentFeeHeadId) || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid payment details']);
            return;
        }
        
        $studentFeeHeadModel = $this->model('StudentFeeHead');
        $feeHeadPaymentModel = $this->model('FeeHeadPayment');
        $paymentModel = $this->model('Payment');
        $invoiceModel = $this->model('Invoice');
        
        // Get student fee head details
        $studentFeeHead = $studentFeeHeadModel->findById($studentFeeHeadId);
        if (!$studentFeeHead || $studentFeeHead['student_id'] != $studentId) {
            $this->json(['success' => false, 'message' => 'Invalid fee head assignment']);
            return;
        }
        
        // Check balance
        $paidAmount = $feeHeadPaymentModel->getTotalPaid($studentFeeHeadId);
        $balance = $studentFeeHead['amount'] - $paidAmount;
        
        if ($amount > $balance) {
            $this->json(['success' => false, 'message' => 'Amount exceeds fee head balance']);
            return;
        }
        
        // Get or create invoice for this term
        $invoices = $invoiceModel->getByStudent($studentId, $academicYear);
        $invoice = null;
        foreach ($invoices as $inv) {
            if ($inv['term'] == $term) {
                $invoice = $inv;
                break;
            }
        }
        
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found. Please assign fee heads first.']);
            return;
        }
        
        // Create payment record
        $paymentData = [
            'invoice_id' => $invoice['id'],
            'student_id' => $studentId,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'payment_date' => date('Y-m-d'),
            'receipt_number' => $paymentModel->generateReceiptNumber(),
            'reference_number' => sanitize($_POST['reference_number'] ?? ''),
            'received_by' => Auth::userId(),
            'remarks' => sanitize($_POST['remarks'] ?? '') . ' [Fee Head Payment]'
        ];
        
        $paymentId = $paymentModel->create($paymentData);
        
        if ($paymentId) {
            // Record fee head payment
            $feeHeadPaymentModel->createPayments($paymentId, [$studentFeeHeadId => $amount]);
            
            // Update invoice balance
            $invoiceModel->updateBalance($invoice['id']);
            
            $this->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'redirect' => BASE_URL . '/feereport/student/' . $studentId . '?term=' . $term . '&academic_year=' . urlencode($academicYear)
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to record payment']);
        }
    }
}

