<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../app/config/config.php';

echo "Database Configuration:\n";
echo "Host: " . DB_HOST . "\n";
echo "Database: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully!\n\n";
    
    // Check if cms_payments exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'cms_payments'");
    if ($stmt->rowCount() > 0) {
        echo "✓ cms_payments table EXISTS\n";
        
        // Show structure
        $stmt = $pdo->query("DESCRIBE cms_payments");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "\nTable structure:\n";
        foreach($cols as $col) {
            echo "  - " . $col['Field'] . "\n";
        }
        
        // Try the actual query
        echo "\nTesting query from CmsPayment model...\n";
        $sql = "SELECT p.*, s.school_name, s.subdomain, 
                       COALESCE(ca.first_name, ca.email, 'System') as received_by_name 
                FROM cms_payments p
                LEFT JOIN cms_schools s ON p.school_id = s.id
                LEFT JOIN cms_admins ca ON p.received_by = ca.id
                WHERE 1=1
                ORDER BY p.payment_date DESC, p.created_at DESC
                LIMIT 20 OFFSET 0";
        
        $stmt = $pdo->query($sql);
        echo "✓ Query executed successfully! Found " . $stmt->rowCount() . " rows\n";
        
    } else {
        echo "✗ cms_payments table DOES NOT EXIST\n";
        echo "\nCreating table now...\n";
        
        // Create the table
        $createSql = "CREATE TABLE IF NOT EXISTS cms_payments (
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
        
        $pdo->exec($createSql);
        echo "✓ Table created successfully!\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

