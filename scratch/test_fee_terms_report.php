<?php
define('BASE_PATH', __DIR__ . '/..');
define('APP_PATH', BASE_PATH . '/app');
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/core/Helper.php';
require_once APP_PATH . '/core/Autoloader.php';
Autoloader::register();

echo "=== Testing Term Fee Summary Models ===\n";

$invoiceModel = new Invoice();
$feeHeadPaymentModel = new FeeHeadPayment();

$summary = $invoiceModel->getTermSummaryMetrics();
echo "Overall Billed: " . $summary['overall']['billed'] . "\n";
echo "Overall Paid: " . $summary['overall']['paid'] . "\n";
echo "Overall Balance: " . $summary['overall']['balance'] . "\n";
echo "Overall Rate: " . $summary['overall']['rate'] . "%\n";

echo "\nTerm 1 Billed: " . $summary['terms'][1]['billed'] . ", Paid: " . $summary['terms'][1]['paid'] . "\n";
echo "Term 2 Billed: " . $summary['terms'][2]['billed'] . ", Paid: " . $summary['terms'][2]['paid'] . "\n";
echo "Term 3 Billed: " . $summary['terms'][3]['billed'] . ", Paid: " . $summary['terms'][3]['paid'] . "\n";

$classMatrix = $invoiceModel->getClassTermBreakdown();
echo "\nClass breakdown count: " . count($classMatrix) . "\n";
if (!empty($classMatrix)) {
    echo "First class: " . $classMatrix[0]['class_name'] . " - Total Billed: " . $classMatrix[0]['total_billed'] . "\n";
}

$feeHeadsByTerm = $feeHeadPaymentModel->getFeeHeadCollectionByTerm();
echo "\nFee heads breakdown count: " . count($feeHeadsByTerm) . "\n";

$studentsList = $invoiceModel->getStudentTermBalanceList();
echo "\nStudent term balance list count: " . count($studentsList) . "\n";
if (!empty($studentsList)) {
    echo "First student: " . $studentsList[0]['first_name'] . " " . $studentsList[0]['last_name'] . " - Net Balance: " . $studentsList[0]['net_balance'] . " (" . $studentsList[0]['fee_status'] . ")\n";
}

echo "\n=== ALL MODEL TESTS COMPLETED SUCCESSFULLY ===\n";
