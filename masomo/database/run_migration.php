<?php
/**
 * Run CMS Extended Features Migration
 * This script will create all necessary tables for the extended CMS features
 */

// Database configuration
$dbHost = 'localhost';
$dbName = 'masomo_school_db';
$dbUser = 'root';
$dbPass = '';

echo "Connecting to database...\n";

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully!\n";
    echo "Reading migration file...\n";
    
    $sqlFile = __DIR__ . '/add_cms_extended_features.sql';
    if (!file_exists($sqlFile)) {
        die("Error: Migration file not found: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remove comments and split SQL into individual statements
    $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments
    
    // Split by semicolon, but keep CREATE TABLE statements together
    $statements = [];
    $currentStatement = '';
    
    $lines = explode("\n", $sql);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $currentStatement .= $line . "\n";
        
        // If line ends with semicolon and we have a statement, add it
        if (substr(rtrim($line), -1) === ';') {
            $stmt = trim($currentStatement);
            if (!empty($stmt) && strlen($stmt) > 10) { // Minimum length check
                $statements[] = $stmt;
            }
            $currentStatement = '';
        }
    }
    
    // Add any remaining statement
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    echo "Executing " . count($statements) . " SQL statements...\n\n";
    
    $success = 0;
    $failed = 0;
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement) || strlen($statement) < 10) continue;
        
        try {
            $pdo->exec($statement);
            $success++;
            // Extract table name for better feedback
            if (preg_match('/CREATE TABLE (?:IF NOT EXISTS )?`?(\w+)`?/i', $statement, $matches)) {
                echo "✓ Created table: " . $matches[1] . "\n";
            } elseif (preg_match('/ALTER TABLE `?(\w+)`?/i', $statement, $matches)) {
                echo "✓ Altered table: " . $matches[1] . "\n";
            } elseif (preg_match('/INSERT INTO `?(\w+)`?/i', $statement, $matches)) {
                echo "✓ Inserted into: " . $matches[1] . "\n";
            } else {
                echo "✓ Statement " . ($index + 1) . " executed successfully\n";
            }
        } catch (PDOException $e) {
            // Ignore "table already exists" and "duplicate column" errors
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "⚠ Statement " . ($index + 1) . ": Already exists (skipped)\n";
                $success++;
            } else {
                $failed++;
                echo "✗ Statement " . ($index + 1) . " failed: " . $e->getMessage() . "\n";
                echo "   SQL: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
    
    echo "\n========================================\n";
    echo "Migration completed!\n";
    echo "Successful: $success\n";
    echo "Failed: $failed\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
}

