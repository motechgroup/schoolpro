<?php
/**
 * SchoolPro Web Deployment & Migration Runner
 * Designed for shared hosting environments without SSH access.
 */

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/app/config/config.php';

$action = $_GET['action'] ?? 'dashboard';
$secretKey = $_GET['key'] ?? '';

// Simple security check (Optional: add ?key=secret in URL if desired)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolPro Web Deployer & Migrator</title>
    <style>
        * { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        body { background: #f1f5f9; color: #1e293b; margin: 0; padding: 40px 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #ffffff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); padding: 32px; }
        h1 { font-size: 24px; color: #0f172a; margin-top: 0; }
        p { color: #64748b; line-height: 1.6; }
        .btn { display: inline-flex; align-items: center; justify-content: center; background: #2563eb; color: white; padding: 12px 20px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; margin-right: 10px; margin-bottom: 10px; border: none; cursor: pointer; transition: 0.2s; }
        .btn:hover { background: #1d4ed8; }
        .btn-green { background: #059669; }
        .btn-green:hover { background: #047857; }
        .btn-purple { background: #7c3aed; }
        .btn-purple:hover { background: #6d28d9; }
        .console { background: #0f172a; color: #38bdf8; padding: 20px; border-radius: 8px; font-family: 'Courier New', Courier, monospace; font-size: 13px; line-height: 1.5; white-space: pre-wrap; overflow-x: auto; margin-top: 20px; }
        .alert { padding: 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-info { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .alert-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .alert-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    </style>
</head>
<body>
<div class="container">
    <h1>🚀 SchoolPro Deployment & Database Tool</h1>
    <p>Use this web panel to update your codebase and run database migrations directly from your browser on shared hosting.</p>

    <div style="margin-bottom: 30px;">
        <a href="?action=git_pull" class="btn">1. Update Code (Git Pull)</a>
        <a href="?action=zip_update" class="btn btn-purple">1b. Update via GitHub Zip</a>
        <a href="?action=run_migration" class="btn btn-green">2. Run DB Migration</a>
        <a href="?action=allocate_payments" class="btn btn-green">3. Allocate Real Payments</a>
    </div>

    <?php if ($action === 'git_pull'): ?>
        <h2>Execution Result: Git Reset & Pull</h2>
        <div class="console"><?php
            if (function_exists('exec')) {
                $output = [];
                $returnCode = 0;
                exec('git checkout -- . 2>&1 && git clean -fd 2>&1 && git pull origin main 2>&1', $output, $returnCode);
                echo implode("\n", $output);
                if ($returnCode === 0) {
                    echo "\n\n✓ Code updated successfully via Git!";
                } else {
                    echo "\n\n⚠ Git pull exited with code $returnCode. Try 'Update via GitHub Zip'.";
                }
            } else {
                echo "exec() function is disabled by hosting provider. Please click 'Update via GitHub Zip'.";
            }
        ?></div>

    <?php elseif ($action === 'zip_update'): ?>
        <h2>Execution Result: GitHub Zip Updater</h2>
        <div class="console"><?php
            $zipUrl = 'https://github.com/motechgroup/schoolpro/archive/refs/heads/main.zip';
            $tempZip = BASE_PATH . '/temp_update.zip';

            echo "Downloading latest code package from GitHub...\n";
            $zipContent = @file_get_contents($zipUrl);
            if ($zipContent === false) {
                // Try cURL fallback
                $ch = curl_init($zipUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $zipContent = curl_exec($ch);
                curl_close($ch);
            }

            if ($zipContent) {
                file_put_contents($tempZip, $zipContent);
                echo "Downloaded (" . round(strlen($zipContent)/1024/1024, 2) . " MB). Extracting...\n";

                $zip = new ZipArchive();
                if ($zip->open($tempZip) === TRUE) {
                    $extractPath = BASE_PATH . '/temp_extract';
                    $zip->extractTo($extractPath);
                    $zip->close();
                    unlink($tempZip);

                    // Copy extracted files to BASE_PATH
                    $extractedFolders = glob($extractPath . '/*', GLOB_ONLYDIR);
                    $sourceDir = !empty($extractedFolders) ? $extractedFolders[0] : $extractPath . '/schoolpro-main';
                    if (is_dir($sourceDir)) {
                        $iterator = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
                            RecursiveIteratorIterator::SELF_FIRST
                        );
                        $count = 0;
                        foreach ($iterator as $item) {
                            $subPath = substr($item->getPathname(), strlen($sourceDir));
                            $targetPath = BASE_PATH . $subPath;

                            if ($item->isDir()) {
                                if (!is_dir($targetPath)) mkdir($targetPath, 0755, true);
                            } else {
                                // Don't overwrite .env
                                if (basename($targetPath) === '.env') continue;
                                
                                if (file_exists($targetPath)) {
                                    @chmod($targetPath, 0666);
                                    @unlink($targetPath);
                                }

                                $copied = @copy($item->getPathname(), $targetPath);
                                if (!$copied) {
                                    @file_put_contents($targetPath, file_get_contents($item->getPathname()));
                                }
                                $count++;
                            }
                        }

                        // Clean up temp extraction
                        function rrmdir($dir) {
                            foreach (scandir($dir) as $item) {
                                if ($item === '.' || $item === '..') continue;
                                $path = $dir . '/' . $item;
                                is_dir($path) ? rrmdir($path) : unlink($path);
                            }
                            rmdir($dir);
                        }
                        rrmdir($extractPath);

                        echo "✓ Extracted and updated $count files successfully!\n";
                        echo "✓ Live code updated to latest GitHub main release.";
                    } else {
                        echo "❌ Error: Could not find extracted files inside zip archive.";
                    }
                } else {
                    echo "❌ Failed to open downloaded zip archive.";
                }
            } else {
                echo "❌ Failed to download code package from GitHub.";
            }
        ?></div>

    <?php elseif ($action === 'run_migration'): ?>
        <h2>Execution Result: Database Migration</h2>
        <div class="console"><?php
            define('MIGRATION_RUNNER', true);
            ob_start();
            include BASE_PATH . '/database/run_migration.php';
            $res = ob_get_clean();
            echo strip_tags($res);
        ?></div>

    <?php elseif ($action === 'allocate_payments'): ?>
        <h2>Execution Result: Real Payment Allocation</h2>
        <div class="console"><?php
            ob_start();
            include BASE_PATH . '/database/allocate_payments.php';
            $res = ob_get_clean();
            echo strip_tags($res);
        ?></div>

    <?php elseif ($action === 'run_seeder'): ?>
        <h2>Execution Result: Fee Head Seeder</h2>
        <div class="console"><?php
            ob_start();
            include BASE_PATH . '/database/seed_fee_head_billing.php';
            $res = ob_get_clean();
            echo strip_tags($res);
        ?></div>

    <?php else: ?>
        <div class="alert alert-info">
            <strong>System Ready:</strong> Click an option above to update your application code or run database updates.
        </div>
    <?php endif; ?>

    <div style="margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
        <p style="font-size: 13px;">Direct Links:</p>
        <ul style="font-size: 13px; color: #64748b;">
            <li>Run Database Migration manually: <a href="database/run_migration.php" target="_blank">database/run_migration.php</a></li>
            <li>Run Real Payment Allocator manually: <a href="database/allocate_payments.php" target="_blank">database/allocate_payments.php</a></li>
        </ul>
    </div>
</div>
</body>
</html>
