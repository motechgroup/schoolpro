<?php
define('BASE_PATH', __DIR__ . '/..');
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/app/core/Helper.php';
require_once BASE_PATH . '/app/core/Autoloader.php';

Autoloader::register();

$feeHeadPaymentModel = new FeeHeadPayment();
$breakdown = $feeHeadPaymentModel->getTuitionVsOtherBreakdown(null, null, null);

echo "=== BREAKDOWN SUMMARY ===\n";
print_r($breakdown['tuition']);
print_r($breakdown['other']);
print_r($breakdown['total']);

echo "\n=== HEAD BREAKDOWN ===\n";
foreach ($breakdown['headBreakdown'] as $h) {
    echo "ID {$h['fee_head_id']} | {$h['fee_head_name']} ({$h['fee_head_code']}) -> Billed: KES {$h['total_billed']} | Collected: KES {$h['total_collected']} | Balance: KES {$h['balance']}\n";
}
