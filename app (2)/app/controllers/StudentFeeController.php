<?php
/**
 * Student Fee Controller
 * Handles student fee head assignments and payment reconciliation
 */

class StudentFeeController extends Controller {
    
    public function __construct() {
        Auth::requireAuth();
        if (!Auth::hasAnyRole(['super_admin', 'school_admin', 'bursar', 'accountant'])) {
            http_response_code(403);
            die("Access denied");
        }
    }
    
    /**
     * Assign fee heads to student
     */
    /**
     * Assign fee heads to student
     */
    public function assign($studentId) {
        $studentModel = $this->model('Student');
        $feeHeadModel = $this->model('FeeHead');
        $studentFeeHeadModel = $this->model('StudentFeeHead');
        $feeHeadPaymentModel = $this->model('FeeHeadPayment');
        
        $student = $studentModel->getStudentWithDetails($studentId);
        
        if (!$student) {
            $this->setFlash('error', 'Student not found');
            $this->redirect('/students');
            return;
        }
        
        $term = $_GET['term'] ?? 1;
        $academicYear = $_GET['academic_year'] ?? date('Y') . '/' . (date('Y') + 1);
        
        $feeHeads = $feeHeadModel->getActive();
        $assignedFeeHeads = $studentFeeHeadModel->getStudentFeeHeads($studentId, $term, $academicYear);
        
        // Create map of assigned fee heads with payment info
        $assignedMap = [];
        $feeHeadsWithPayments = [];
        foreach ($assignedFeeHeads as $assigned) {
            $assignedMap[$assigned['fee_head_id']] = $assigned['amount'];
            
            // Check if this assignment has payments
            $paidAmount = $feeHeadPaymentModel->getTotalPaid($assigned['id']);
            if ($paidAmount > 0) {
                $feeHeadsWithPayments[$assigned['fee_head_id']] = [
                    'student_fee_head_id' => $assigned['id'],
                    'amount' => $assigned['amount'],
                    'paid' => $paidAmount
                ];
            }
        }
        
        $data = [
            'title' => 'Assign Fee Heads - ' . APP_NAME,
            'student' => $student,
            'feeHeads' => $feeHeads,
            'assignedFeeHeads' => $assignedMap,
            'feeHeadsWithPayments' => $feeHeadsWithPayments,
            'term' => $term,
            'academicYear' => $academicYear,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->view('fees/assign', $data);
    }
    
    /**
     * Save fee head assignments
     */
    public function saveAssignments($studentId) {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
            return;
        }
        
        $studentFeeHeadModel = $this->model('StudentFeeHead');
        $feeHeadPaymentModel = $this->model('FeeHeadPayment');
        
        $feeHeadModel = $this->model('FeeHead');
        
        $term = intval($_POST['term'] ?? 1);
        $academicYear = sanitize($_POST['academic_year'] ?? '');
        $selectedFeeHeads = $_POST['fee_heads'] ?? []; // Array of fee head IDs that are checked
        $feeHeadAmounts = $_POST['fee_head_amounts'] ?? []; // Amounts (should use default amounts)
        
        // Get all fee heads to get default amounts
        $allFeeHeads = $feeHeadModel->getActive();
        $feeHeadDefaults = [];
        foreach ($allFeeHeads as $fh) {
            $feeHeadDefaults[$fh['id']] = $fh['default_amount'] ?? 0;
        }
        
        // Get existing assignments to preserve fee heads with payments
        $existingAssignments = $studentFeeHeadModel->getStudentFeeHeads($studentId, $term, $academicYear);
        $existingMap = [];
        $lockedFeeHeads = [];
        foreach ($existingAssignments as $existing) {
            $existingMap[$existing['fee_head_id']] = $existing;
            // Check if this assignment has payments
            $paidAmount = $feeHeadPaymentModel->getTotalPaid($existing['id']);
            if ($paidAmount > 0) {
                $lockedFeeHeads[$existing['fee_head_id']] = $existing['amount'];
            }
        }
        
        // Build fee head assignment data using default amounts
        $feeHeadData = [];
        
        // Include locked fee heads (those with payments) - they must remain
        foreach ($lockedFeeHeads as $feeHeadId => $amount) {
            $feeHeadData[$feeHeadId] = $amount;
        }
        
        // Process selected fee heads (checkboxes that are checked)
        foreach ($selectedFeeHeads as $feeHeadId => $value) {
            $feeHeadId = intval($feeHeadId);
            
            // Skip if already locked (has payments)
            if (isset($lockedFeeHeads[$feeHeadId])) {
                continue;
            }
            
            // Use default amount from fee head definition
            $defaultAmount = $feeHeadDefaults[$feeHeadId] ?? 0;
            
            if ($defaultAmount > 0) {
                $feeHeadData[$feeHeadId] = $defaultAmount;
            } elseif (isset($feeHeadAmounts[$feeHeadId])) {
                // Fallback to submitted amount if default is 0
                $feeHeadData[$feeHeadId] = floatval($feeHeadAmounts[$feeHeadId]);
            }
        }
        
        if ($studentFeeHeadModel->assignFeeHeads($studentId, $feeHeadData, $term, $academicYear)) {
            // Generate/update invoice
            $this->generateInvoice($studentId, $term, $academicYear);
            
            $this->json([
                'success' => true,
                'message' => 'Fee heads assigned successfully',
                'redirect' => BASE_URL . '/students/show/' . $studentId
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to assign fee heads']);
        }
    }
    
    /**
     * Generate invoice for student based on fee heads
     */
    private function generateInvoice($studentId, $term, $academicYear) {
        $studentFeeHeadModel = $this->model('StudentFeeHead');
        $invoiceModel = $this->model('Invoice');
        
        // Calculate total from fee heads
        $totalAmount = $studentFeeHeadModel->calculateTotalFees($studentId, $term, $academicYear);
        
        if ($totalAmount <= 0) {
            return; // No fees to invoice
        }
        
        // Check if invoice already exists
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM invoices WHERE student_id = ? AND term = ? AND academic_year = ?");
        $stmt->execute([$studentId, $term, $academicYear]);
        $existingInvoice = $stmt->fetch();
        
        if ($existingInvoice) {
            // Update existing invoice
            $invoiceItems = $studentFeeHeadModel->getStudentFeeHeads($studentId, $term, $academicYear);
            
            // Delete old items if invoice_items table exists
            try {
                $db->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$existingInvoice['id']]);
                
                // Add new items using fee_head_id
                $itemStmt = $db->prepare("INSERT INTO invoice_items (invoice_id, fee_head_id, amount, description) VALUES (?, ?, ?, ?)");
                foreach ($invoiceItems as $item) {
                    $itemStmt->execute([
                        $existingInvoice['id'], 
                        $item['fee_head_id'], 
                        $item['amount'],
                        $item['fee_head_name'] ?? 'Fee'
                    ]);
                }
            } catch (Exception $e) {
                // invoice_items table might not exist or have different structure, skip
                error_log("Invoice items update skipped: " . $e->getMessage());
            }
            
            // Update invoice total
            $updateStmt = $db->prepare("UPDATE invoices SET total_amount = ?, balance = total_amount - COALESCE(paid_amount, 0) WHERE id = ?");
            $updateStmt->execute([$totalAmount, $existingInvoice['id']]);
        } else {
            // Create new invoice
            $invoiceNumber = $invoiceModel->generateInvoiceNumber();
            $dueDate = date('Y-m-d', strtotime('+30 days'));
            
            $invoiceStmt = $db->prepare("INSERT INTO invoices 
                                        (invoice_number, student_id, term, academic_year, total_amount, balance, status, due_date) 
                                        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
            $invoiceStmt->execute([$invoiceNumber, $studentId, $term, $academicYear, $totalAmount, $totalAmount, $dueDate]);
            
            $invoiceId = $db->lastInsertId();
            
            // Add invoice items if table exists
            try {
                $invoiceItems = $studentFeeHeadModel->getStudentFeeHeads($studentId, $term, $academicYear);
                $itemStmt = $db->prepare("INSERT INTO invoice_items (invoice_id, fee_head_id, amount, description) VALUES (?, ?, ?, ?)");
                foreach ($invoiceItems as $item) {
                    $itemStmt->execute([
                        $invoiceId, 
                        $item['fee_head_id'], 
                        $item['amount'],
                        $item['fee_head_name'] ?? 'Fee'
                    ]);
                }
            } catch (Exception $e) {
                // invoice_items table might not exist, skip
                error_log("Invoice items creation skipped: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Payment reconciliation - select student and add payment
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
                $student['invoices'] = $invoices;
                $student['total_balance'] = 0;
                foreach ($invoices as $inv) {
                    if ($inv['term'] == $term) {
                        $student['total_balance'] += $inv['balance'];
                    }
                }
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
        $studentModel = $this->model('Student');
        $invoiceModel = $this->model('Invoice');
        
        $student = $studentModel->getStudentWithDetails($studentId);
        
        if (!$student) {
            $this->json(['success' => false, 'message' => 'Student not found']);
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
            // Return HTML error message
            echo '<div class="text-red-600 p-4">No invoice found for this term. Please assign fee heads first.</div>';
            return;
        }
        
        $data = [
            'student' => $student,
            'invoice' => $currentInvoice,
            'csrf_token' => generateCSRFToken()
        ];
        
        $this->viewPartial('fees/payment_form', $data);
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
        $amount = floatval($_POST['amount'] ?? 0);
        $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash');
        
        if (empty($invoiceId) || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid payment details']);
            return;
        }
        
        $invoiceModel = $this->model('Invoice');
        $paymentModel = $this->model('Payment');
        
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
        
        $paymentId = $paymentModel->create($paymentData);
        
        if ($paymentId) {
            // Update invoice balance
            $invoiceModel->updateBalance($invoiceId);

            // Send SMS notification to parent about payment and fee status
            try {
                require_once APP_PATH . '/helpers/PaymentNotificationHelper.php';
                PaymentNotificationHelper::sendFeePaymentSms($studentId, $amount, $paymentData['receipt_number'], $invoiceId);
            } catch (Exception $e) {
                error_log("Payment SMS dispatch error (StudentFeeController): " . $e->getMessage());
            }
            
            $this->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'redirect' => BASE_URL . '/fees/reconcile?class_id=' . ($_GET['class_id'] ?? '') . '&term=' . ($_GET['term'] ?? 1)
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to record payment']);
        }
    }
}

