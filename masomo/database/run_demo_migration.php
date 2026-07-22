<?php
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/core/Database.php';

$db = Database::getInstance()->getConnection();
$sql = file_get_contents(__DIR__ . '/add_demo_requests.sql');

// Split by semicolon and execute each statement
$statements = explode(';', $sql);
foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if (!empty($stmt) && !preg_match('/^--/', $stmt)) {
        try {
            $db->exec($stmt);
            echo "✓ Executed statement\n";
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "Migration completed!\n";

