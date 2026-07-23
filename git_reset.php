<?php
/**
 * SchoolPro Live Git Reset & Cleanup Tool
 * Resets local server git working tree so cPanel Git Pull can execute cleanly.
 */

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/app/config/config.php';

header('Content-Type: text/plain');

echo "=== SCHOOLPRO LIVE GIT RESET & CLEANUP ===\n\n";

if (!function_exists('exec')) {
    echo "❌ Error: exec() function is disabled on this server.\n";
    echo "Please use 'Update via GitHub Zip' in deploy.php instead.\n";
    exit;
}

echo "1. Discarding uncommitted local file changes (git checkout -- .)...\n";
$out1 = [];
exec('git checkout -- . 2>&1', $out1);
echo implode("\n", $out1) . "\n";

echo "2. Cleaning untracked files (git clean -fd)...\n";
$out2 = [];
exec('git clean -fd 2>&1', $out2);
echo implode("\n", $out2) . "\n";

echo "3. Pulling latest code from GitHub (git pull origin main)...\n";
$out3 = [];
$res = 0;
exec('git pull origin main 2>&1', $out3, $res);
echo implode("\n", $out3) . "\n";

if ($res === 0) {
    echo "\n✅ SUCCESS: Server Git working tree is reset and fully updated to GitHub main!";
    echo "\nYou can now use cPanel Git Version Control without errors.";
} else {
    echo "\n⚠ Notice: Git pull returned code $res.";
}
