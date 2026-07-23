<?php
define('BASE_PATH', __DIR__ . '/..');
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/app/core/Helper.php';
require_once BASE_PATH . '/app/core/Autoloader.php';

Autoloader::register();

$db = Database::getInstance()->getConnection();

echo "=== FEE HEADS ===\n";
$fh = $db->query("SELECT id, name, code FROM fee_heads")->fetchAll(PDO::FETCH_ASSOC);
print_r($fh);

echo "\n=== STUDENT FEE HEADS COUNT BY FEE_HEAD_ID ===\n";
$sfh = $db->query("SELECT fee_head_id, academic_year, term, COUNT(*) as cnt, SUM(amount) as total FROM student_fee_heads GROUP BY fee_head_id, academic_year, term")->fetchAll(PDO::FETCH_ASSOC);
print_r($sfh);

echo "\n=== INVOICES TOTAL vs STUDENT FEE HEADS TOTAL ===\n";
$inv = $db->query("SELECT academic_year, term, COUNT(*) as cnt, SUM(total_amount) as total_inv FROM invoices GROUP BY academic_year, term")->fetchAll(PDO::FETCH_ASSOC);
print_r($inv);
