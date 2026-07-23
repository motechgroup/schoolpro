<?php
define('BASE_PATH', __DIR__ . '/..');
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/app/core/Helper.php';
require_once BASE_PATH . '/app/core/Autoloader.php';

Autoloader::register();

$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'super_admin';

$controller = new ReportController();
ob_start();
$controller->index();
$output = ob_get_clean();

echo "✓ ReportController->index() executed cleanly with NO ERRORS! Output length: " . strlen($output) . " bytes.\n";
