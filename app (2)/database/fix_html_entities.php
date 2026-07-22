<?php
/**
 * Fix HTML Entity Encoding in Database
 * This script decodes HTML entities (like &#039;) in student, parent, and user names
 * Run this once to fix existing data that has double-encoded HTML entities
 */

// Define constants before including config
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getInstance()->getConnection();

echo "Starting HTML entity fix...\n\n";

// Fix students table
echo "Fixing students table...\n";
$tables = [
    'students' => ['first_name', 'middle_name', 'last_name'],
    'parents' => ['first_name', 'middle_name', 'last_name'],
    'teachers' => ['first_name', 'middle_name', 'last_name'],
    'users' => ['first_name', 'last_name']
];

$totalFixed = 0;

foreach ($tables as $table => $columns) {
    echo "\nProcessing table: $table\n";
    
    foreach ($columns as $column) {
        // Get all records with HTML entities
        $stmt = $db->prepare("SELECT id, $column FROM $table WHERE $column LIKE '%&#%' OR $column LIKE '%&apos;%' OR $column LIKE '%&quot;%'");
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $fixed = 0;
        foreach ($records as $record) {
            $original = $record[$column];
            // Decode HTML entities (may need multiple passes for double-encoding)
            $decoded = html_entity_decode($original, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            if ($decoded !== $original) {
                $updateStmt = $db->prepare("UPDATE $table SET $column = ? WHERE id = ?");
                $updateStmt->execute([$decoded, $record['id']]);
                $fixed++;
                echo "  Fixed ID {$record['id']}: '$original' -> '$decoded'\n";
            }
        }
        
        if ($fixed > 0) {
            echo "  Column $column: Fixed $fixed record(s)\n";
            $totalFixed += $fixed;
        }
    }
}

echo "\n\nTotal records fixed: $totalFixed\n";
echo "HTML entity fix completed!\n";

