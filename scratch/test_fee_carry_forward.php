<?php
/**
 * Test Multi-Term Fee Carry Forward Logic
 */
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/app/core/Helper.php';
require_once BASE_PATH . '/app/core/Autoloader.php';

Autoloader::register();

echo "==========================================\n";
echo "Testing Multi-Term Fee Carry Forward Logic\n";
echo "==========================================\n\n";

$db = Database::getInstance()->getConnection();
$invoiceModel = new Invoice();
$paymentModel = new Payment();

// Find a test student
$stmt = $db->query("SELECT id, first_name, last_name, admission_number FROM students LIMIT 1");
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("No student found in database to test.\n");
}

echo "Testing with Student: {$student['first_name']} {$student['last_name']} (ID: {$student['id']}, Adm: {$student['admission_number']})\n\n";

$testYear = "2026/2027";

// Clean up any existing test invoices for this test year
$db->prepare("DELETE FROM payments WHERE student_id = ? AND invoice_id IN (SELECT id FROM invoices WHERE student_id = ? AND academic_year = ?)")
   ->execute([$student['id'], $student['id'], $testYear]);
$db->prepare("DELETE FROM invoices WHERE student_id = ? AND academic_year = ?")
   ->execute([$student['id'], $testYear]);

echo "1. Creating Term 1 Invoice (Fee: KES 10,000)...\n";
$inv1Num = $invoiceModel->generateInvoiceNumber();
$stmt = $db->prepare("INSERT INTO invoices (invoice_number, student_id, term, academic_year, total_amount, paid_amount, balance, status, due_date) 
                      VALUES (?, ?, 1, ?, 10000.00, 0.00, 10000.00, 'pending', NOW())");
$stmt->execute([$inv1Num, $student['id'], $testYear]);
$inv1Id = $db->lastInsertId();

echo "   Term 1 Invoice Created (ID: $inv1Id, Num: $inv1Num)\n";

echo "\n2. Logging Term 1 partial payment of KES 4,000...\n";
$pData1 = [
    'invoice_id' => $inv1Id,
    'student_id' => $student['id'],
    'payment_method' => 'cash',
    'amount' => 4000.00,
    'payment_date' => date('Y-m-d'),
    'receipt_number' => $paymentModel->generateReceiptNumber(),
    'received_by' => 1,
    'remarks' => 'Test Term 1 Partial Payment'
];
$pId1 = $paymentModel->create($pData1);
$invoiceModel->updateBalance($inv1Id);

echo "   Payment recorded (ID: $pId1). Term 1 invoice balance updated.\n";

echo "\n3. Creating Term 2 Invoice (Fee: KES 15,000)...\n";
$inv2Num = $invoiceModel->generateInvoiceNumber();
$stmt = $db->prepare("INSERT INTO invoices (invoice_number, student_id, term, academic_year, total_amount, paid_amount, balance, status, due_date) 
                      VALUES (?, ?, 2, ?, 15000.00, 0.00, 15000.00, 'pending', NOW())");
$stmt->execute([$inv2Num, $student['id'], $testYear]);
$inv2Id = $db->lastInsertId();

echo "   Term 2 Invoice Created (ID: $inv2Id, Num: $inv2Num)\n";

echo "\n4. Fetching Multi-Term Fee Breakdown via getStudentTermBalances()...\n";
$summary = $invoiceModel->getStudentTermBalances($student['id'], $testYear);

echo "   Term 1 -> Billed: KES {$summary['invoices'][0]['term_fee']}, Paid: KES {$summary['invoices'][0]['term_paid']}, Carried In: KES {$summary['invoices'][0]['carried_in']}, Net Balance: KES {$summary['invoices'][0]['net_term_balance']}\n";
echo "   Term 2 -> Billed: KES {$summary['invoices'][1]['term_fee']}, Paid: KES {$summary['invoices'][1]['term_paid']}, Carried In: KES {$summary['invoices'][1]['carried_in']}, Total Due: KES {$summary['invoices'][1]['total_payable']}, Net Balance: KES {$summary['invoices'][1]['net_term_balance']}\n";
echo "   TOTAL YEAR NET BALANCE: KES {$summary['net_balance']}\n";

assert($summary['invoices'][1]['carried_in'] == 6000.00, "Term 2 carried-in arrears should be KES 6,000");
assert($summary['invoices'][1]['total_payable'] == 21000.00, "Term 2 total payable should be KES 21,000 (15,000 + 6,000)");
assert($summary['net_balance'] == 21000.00, "Net year balance should be KES 21,000");

echo "   ✓ Carry-Forward Calculation Passed!\n";

echo "\n5. Testing Payment Auto-Allocation across Term 1 & Term 2 (Paying KES 11,000)...\n";
$allocRes = $invoiceModel->allocatePaymentAcrossInvoices($student['id'], 11000.00, [
    'payment_method' => 'mpesa',
    'academic_year' => $testYear,
    'received_by' => 1,
    'remarks' => 'Test Multi-Term Payment'
]);

echo "   Allocation result:\n";
foreach ($allocRes['allocated'] as $alloc) {
    echo "   - Term {$alloc['term']} (Invoice #{$alloc['invoice_id']}): KES {$alloc['amount']} (Receipt: {$alloc['receipt_number']})\n";
}

$updatedSummary = $invoiceModel->getStudentTermBalances($student['id'], $testYear);
echo "\n6. Post-Payment Summary:\n";
echo "   Term 1 Net Balance: KES {$updatedSummary['invoices'][0]['net_term_balance']} (Status: {$updatedSummary['invoices'][0]['status']})\n";
echo "   Term 2 Net Balance: KES {$updatedSummary['invoices'][1]['net_term_balance']} (Status: {$updatedSummary['invoices'][1]['status']})\n";
echo "   NEW TOTAL YEAR NET BALANCE: KES {$updatedSummary['net_balance']}\n";

assert($updatedSummary['invoices'][0]['status'] === 'paid', "Term 1 should be fully paid");
assert($updatedSummary['invoices'][1]['net_term_balance'] == 10000.00, "Term 2 net balance should be 10,000 (21,000 - 11,000)");

echo "   ✓ Payment Allocation Passed!\n";

// Cleanup test records
$db->prepare("DELETE FROM payments WHERE student_id = ? AND invoice_id IN (SELECT id FROM invoices WHERE student_id = ? AND academic_year = ?)")
   ->execute([$student['id'], $student['id'], $testYear]);
$db->prepare("DELETE FROM invoices WHERE student_id = ? AND academic_year = ?")
   ->execute([$student['id'], $testYear]);

echo "\n==========================================\n";
echo "ALL MULTI-TERM FEE CARRY FORWARD TESTS PASSED SUCCESSFULLY! ✓\n";
echo "==========================================\n";
