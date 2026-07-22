<?php
/**
 * Test CMS Database Connection
 * Mimics exactly what CMS does
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Load configuration (same as CMS index.php)
require_once APP_PATH . '/config/config.php';

echo "Database Config:\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n\n";

// Load Database class
require_once APP_PATH . '/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "✓ Database connection established\n\n";
    
    // Test the exact query from CmsPayment
    echo "Testing CmsPayment::getAll() query...\n";
    $sql = "SELECT p.*, s.school_name, s.subdomain, 
                   COALESCE(ca.first_name, ca.email, 'System') as received_by_name 
            FROM cms_payments p
            LEFT JOIN cms_schools s ON p.school_id = s.id
            LEFT JOIN cms_admins ca ON p.received_by = ca.id
            WHERE 1=1
            ORDER BY p.payment_date DESC, p.created_at DESC
            LIMIT 20 OFFSET 0";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    echo "✓ Query executed successfully!\n";
    echo "Found " . count($results) . " payment records\n";
    
    // Check if tables exist
    echo "\nChecking all CMS tables:\n";
    $tables = ['cms_payments', 'cms_schools', 'cms_admins', 'cms_school_fees'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "  ✓ $table exists\n";
        } else {
            echo "  ✗ $table MISSING\n";
        }
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
}

