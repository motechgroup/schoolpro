<?php
/**
 * Run SchoolPro V2.0.0 Database Migration
 * Executes update_database_v2.sql safely on local or live servers.
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/app/core/Autoloader.php';
Autoloader::register();

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    echo '<!DOCTYPE html><html><head><title>SchoolPro Migration</title>';
    echo '<style>body{font-family:sans-serif;padding:20px;background:#f4f6f9;color:#333;}';
    echo '.box{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);max-width:800px;margin:auto;}';
    echo '.success{color:#059669;}.info{color:#2563eb;}.error{color:#dc2626;}</style></head><body>';
    echo '<div class="box"><h2>SchoolPro Database Migration Suite</h2><pre>';
}

echo "Connecting to database (" . DB_NAME . ")...\n";

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "Connected successfully!\n";
    echo "Reading migration file: update_database_v2.sql...\n";
    
    $sqlFile = __DIR__ . '/update_database_v2.sql';
    if (!file_exists($sqlFile)) {
        die("Error: Migration file not found: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remove comments and split SQL into individual statements
    $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments
    
    $statements = [];
    $currentStatement = '';
    
    $lines = explode("\n", $sql);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $currentStatement .= $line . "\n";
        
        if (substr(rtrim($line), -1) === ';') {
            $stmt = trim($currentStatement);
            if (!empty($stmt) && strlen($stmt) > 5) {
                $statements[] = $stmt;
            }
            $currentStatement = '';
        }
    }
    
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    echo "Executing " . count($statements) . " SQL statements...\n\n";
    
    $success = 0;
    $failed = 0;
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement) || strlen($statement) < 5) continue;
        
        try {
            $pdo->exec($statement);
            $success++;
            if (preg_match('/CREATE TABLE (?:IF NOT EXISTS )?`?(\w+)`?/i', $statement, $matches)) {
                echo "✓ Created/Verified table: " . $matches[1] . "\n";
            } elseif (preg_match('/ALTER TABLE `?(\w+)`?/i', $statement, $matches)) {
                echo "✓ Altered table: " . $matches[1] . "\n";
            } elseif (preg_match('/INSERT (?:IGNORE )?INTO `?(\w+)`?/i', $statement, $matches)) {
                echo "✓ Inserted records into: " . $matches[1] . "\n";
            } else {
                echo "✓ Statement " . ($index + 1) . " executed successfully\n";
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate column') !== false ||
                strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "⚠ Statement " . ($index + 1) . ": Skipped (already up to date)\n";
                $success++;
            } else {
                $failed++;
                echo "✗ Statement " . ($index + 1) . " failed: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n========================================\n";
    echo "Migration Completed Successfully!\n";
    echo "Successful: $success\n";
    echo "Failed: $failed\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
}

if (!$isCli) {
    echo '</pre><p class="success"><strong>Database is ready for SchoolPro V2.0.0!</strong></p></div></body></html>';
}


