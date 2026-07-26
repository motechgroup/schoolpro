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
     * Term Fee Report & Summary Dashboard (Default action)
     */
    public function index() {
        $this->terms();
    }

    /**
     * Term-by-term fee summary report
     */
    public function terms() {
        if (Auth::hasRole('parent')) {
            $this->setFlash('error', 'Access denied');
            $this->redirect('/parent/dashboard');
            return;
        }

        $invoiceModel = $this->model('Invoice');
        $feeHeadPaymentModel = $this->model('FeeHeadPayment');
        $academicYearModel = $this->model('AcademicYear');
        $classModel = $this->model('ClassModel');

        $academicYears = $academicYearModel->getAll();
        $classes = $classModel->getAllWithDetails();

        $selectedYear = $_GET['academic_year'] ?? getAcademicYearName();
        if ($selectedYear === 'all') {
            $selectedYear = null;
        }

        $selectedTerm = !empty($_GET['term']) ? intval($_GET['term']) : null;
        $selectedClass = !empty($_GET['class_id']) ? intval($_GET['class_id']) : null;
        $selectedStatus = $_GET['status'] ?? 'all';
        $searchQuery = trim($_GET['search'] ?? '');

        $summaryMetrics = $invoiceModel->getTermSummaryMetrics($selectedYear, $selectedClass);
        $classBreakdown = $invoiceModel->getClassTermBreakdown($selectedYear);
        $feeHeadBreakdown = $feeHeadPaymentModel->getFeeHeadCollectionByTerm($selectedYear);
        $studentList = $invoiceModel->getStudentTermBalanceList($selectedYear, $selectedClass, $selectedTerm, $selectedStatus, $searchQuery);

        $data = [
            'title' => 'Term Fee Summary & Report - ' . APP_NAME,
            'academicYears' => $academicYears,
            'classes' => $classes,
            'summaryMetrics' => $summaryMetrics,
            'classBreakdown' => $classBreakdown,
            'feeHeadBreakdown' => $feeHeadBreakdown,
            'studentList' => $studentList,
            'filters' => [
                'academic_year' => $_GET['academic_year'] ?? getAcademicYearName(),
                'term' => $selectedTerm,
                'class_id' => $selectedClass,
                'status' => $selectedStatus,
                'search' => $searchQuery
            ]
        ];

        $this->view('fees/report_terms', $data);
    }

    /**
     * Export Term Fee Summary & Student Balances to CSV
     */
    public function exportCsv() {
        if (Auth::hasRole('parent')) {
            http_response_code(403);
            die("Access denied");
        }

        $invoiceModel = $this->model('Invoice');
        
        $selectedYear = $_GET['academic_year'] ?? getAcademicYearName();
        if ($selectedYear === 'all') {
            $selectedYear = null;
        }

        $selectedTerm = !empty($_GET['term']) ? intval($_GET['term']) : null;
        $selectedClass = !empty($_GET['class_id']) ? intval($_GET['class_id']) : null;
        $selectedStatus = $_GET['status'] ?? 'all';
        $searchQuery = trim($_GET['search'] ?? '');

        $summaryMetrics = $invoiceModel->getTermSummaryMetrics($selectedYear, $selectedClass);
        $classBreakdown = $invoiceModel->getClassTermBreakdown($selectedYear);
        $studentList = $invoiceModel->getStudentTermBalanceList($selectedYear, $selectedClass, $selectedTerm, $selectedStatus, $searchQuery);

        $filename = 'Fee_Term_Report_' . date('Y-m-d_His') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // 1. Title Section
        fputcsv($output, ['FEE REPORT & SUMMARY FOR TERMS - ' . APP_NAME]);
        fputcsv($output, ['Academic Year: ' . ($selectedYear ?: 'All Academic Years'), 'Generated On: ' . date('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // 2. Executive Term Summary
        fputcsv($output, ['--- EXECUTIVE TERM SUMMARY ---']);
        fputcsv($output, ['Term', 'Total Billed (KES)', 'Total Paid (KES)', 'Balance (KES)', 'Collection Rate (%)', 'Invoices Issued']);
        
        foreach ([1, 2, 3] as $t) {
            $tm = $summaryMetrics['terms'][$t] ?? ['billed' => 0, 'paid' => 0, 'balance' => 0, 'rate' => 0, 'invoices' => 0];
            fputcsv($output, [
                'Term ' . $t,
                number_format($tm['billed'], 2, '.', ''),
                number_format($tm['paid'], 2, '.', ''),
                number_format($tm['balance'], 2, '.', ''),
                $tm['rate'] . '%',
                $tm['invoices']
            ]);
        }
        $ov = $summaryMetrics['overall'];
        fputcsv($output, [
            'OVERALL YEAR',
            number_format($ov['billed'], 2, '.', ''),
            number_format($ov['paid'], 2, '.', ''),
            number_format($ov['balance'], 2, '.', ''),
            $ov['rate'] . '%',
            $ov['invoices']
        ]);
        fputcsv($output, []);

        // 3. Class-Wise Breakdown
        fputcsv($output, ['--- CLASS-WISE TERM BREAKDOWN ---']);
        fputcsv($output, ['Class Name', 'Enrolled Students', 'T1 Billed', 'T1 Paid', 'T2 Billed', 'T2 Paid', 'T3 Billed', 'T3 Paid', 'Total Billed', 'Total Paid', 'Total Balance', 'Collection Rate (%)']);
        
        foreach ($classBreakdown as $c) {
            fputcsv($output, [
                $c['class_name'],
                $c['student_count'],
                number_format($c['term_1']['billed'], 2, '.', ''),
                number_format($c['term_1']['paid'], 2, '.', ''),
                number_format($c['term_2']['billed'], 2, '.', ''),
                number_format($c['term_2']['paid'], 2, '.', ''),
                number_format($c['term_3']['billed'], 2, '.', ''),
                number_format($c['term_3']['paid'], 2, '.', ''),
                number_format($c['total_billed'], 2, '.', ''),
                number_format($c['total_paid'], 2, '.', ''),
                number_format($c['total_balance'], 2, '.', ''),
                $c['collection_rate'] . '%'
            ]);
        }
        fputcsv($output, []);

        // 4. Student Term Balances
        fputcsv($output, ['--- STUDENT TERM BALANCE REPORT ---']);
        fputcsv($output, ['Admission No', 'Student Name', 'Class', 'Gender', 'Term 1 Paid/Billed', 'Term 2 Paid/Billed', 'Term 3 Paid/Billed', 'Net Cumulative Balance (KES)', 'Fee Status']);
        
        foreach ($studentList as $s) {
            $t1Str = number_format($s['t1']['paid'], 2, '.', '') . ' / ' . number_format($s['t1']['billed'], 2, '.', '');
            $t2Str = number_format($s['t2']['paid'], 2, '.', '') . ' / ' . number_format($s['t2']['billed'], 2, '.', '');
            $t3Str = number_format($s['t3']['paid'], 2, '.', '') . ' / ' . number_format($s['t3']['billed'], 2, '.', '');
            
            fputcsv($output, [
                $s['admission_number'],
                $s['first_name'] . ' ' . $s['last_name'],
                $s['class_name'],
                ucfirst($s['gender']),
                $t1Str,
                $t2Str,
                $t3Str,
                number_format($s['net_balance'], 2, '.', ''),
                strtoupper($s['fee_status'])
            ]);
        }
        
        fclose($output);
        exit;
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

