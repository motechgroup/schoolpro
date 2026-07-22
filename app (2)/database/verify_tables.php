<?php
/**
 * Verify CMS Extended Features Tables
 */

$dbHost = 'localhost';
$dbName = 'masomo_school_db';
$dbUser = 'root';
$dbPass = '';

$requiredTables = [
    'cms_school_owners',
    'cms_school_fees',
    'cms_payments',
    'cms_notifications',
    'cms_email_communications',
    'cms_system_logs',
    'school_system_logs',
    'cms_backups',
    'cms_settings'
];

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking required tables...\n\n";
    
    $missing = [];
    $exists = [];
    
    foreach ($requiredTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $exists[] = $table;
            echo "✓ $table exists\n";
        } else {
            $missing[] = $table;
            echo "✗ $table MISSING\n";
        }
    }
    
    // Check if cms_schools has new columns
    echo "\nChecking cms_schools columns...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM cms_schools LIKE 'setup_fee%'");
    if ($stmt->rowCount() > 0) {
        echo "✓ cms_schools has setup_fee columns\n";
    } else {
        echo "✗ cms_schools missing setup_fee columns\n";
        $missing[] = 'cms_schools columns';
    }
    
    echo "\n========================================\n";
    echo "Summary:\n";
    echo "Existing: " . count($exists) . "\n";
    echo "Missing: " . count($missing) . "\n";
    
    if (count($missing) > 0) {
        echo "\nMissing tables/columns:\n";
        foreach ($missing as $item) {
            echo "  - $item\n";
        }
        echo "\nPlease run: php run_migration.php\n";
    } else {
        echo "\nAll tables exist! ✓\n";
    }
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}

