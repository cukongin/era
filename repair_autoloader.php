<?php
// repair_autoloader.php
// Script to fix "Class not found" or "Failed to open stream" errors after file deletion on Shared Hosting

echo "<h1>System Repair Tool</h1>";
echo "<pre>";

// 1. Define paths
$baseDir = __DIR__;
$bootstrapCache = $baseDir . '/bootstrap/cache';

// 2. Clear Bootstrap Cache (Laravel)
echo "[-] Clearing Laravel Bootstrap Cache...\n";
$files = glob($bootstrapCache . '/*.php');
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
        echo "    Deleted: " . basename($file) . "\n";
    }
}

// 3. Try to run Composer Dump-Autoload via Shell
echo "[-] Attempting 'composer dump-autoload' via shell_exec...\n";
try {
    // Try standard composer
    $output = shell_exec('composer dump-autoload 2>&1');
    echo "    Output: " . ($output ?: 'No output (might be disabled)') . "\n";
    
    // Try php composer.phar if exists
    if (file_exists($baseDir . '/composer.phar')) {
        echo "    Found composer.phar, trying execution...\n";
        $output2 = shell_exec('php composer.phar dump-autoload 2>&1');
        echo "    Output: " . $output2 . "\n";
    }
} catch (Exception $e) {
    echo "    Error: " . $e->getMessage() . "\n";
}

// 4. Try Artisan Optimize Clear (if Laravel can boot)
echo "[-] Attempting 'php artisan optimize:clear'...\n";
try {
    $output3 = shell_exec('php artisan optimize:clear 2>&1');
    echo "    Output: " . ($output3 ?: 'No output') . "\n";
} catch (Exception $e) {
    echo "    Error: " . $e->getMessage() . "\n";
}

echo "\n[DONE] Process Completed. Please try accessing your application now.";
echo "</pre>";
