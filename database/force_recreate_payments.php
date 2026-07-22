<?php
/**
 * Force recreate cms_payments table
 * Use this if you're still getting table not found errors
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/core/Database.php';

$db = Database::getInstance()->getConnection();

echo "Dropping cms_payments table if exists...\n";
try {
    $db->exec("DROP TABLE IF EXISTS cms_payments");
    echo "✓ Dropped\n\n";
} catch (PDOException $e) {
    echo "Note: " . $e->getMessage() . "\n\n";
}

echo "Creating cms_payments table...\n";
$createSql = "CREATE TABLE cms_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    fee_id INT NULL,
    payment_type ENUM('setup', 'maintenance', 'subscription', 'other') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'KES',
    payment_method ENUM('mpesa', 'bank_transfer', 'cash', 'cheque', 'other') NOT NULL,
    transaction_reference VARCHAR(255),
    payment_date DATE NOT NULL,
    received_by INT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_school_id (school_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_payment_type (payment_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->exec($createSql);
    echo "✓ Table created successfully!\n\n";
    
    // Verify
    $stmt = $db->query("SHOW TABLES LIKE 'cms_payments'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Verification: Table exists\n";
    } else {
        echo "✗ Verification failed\n";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

