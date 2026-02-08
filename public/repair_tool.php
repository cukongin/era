<?php
// public/repair_tool.php (Renamed for clarity)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>System Repair Tool (Public Access)</h1>";
echo "<pre style='background:#eee; padding:10px; border:1px solid #ccc;'>";

// Detect Paths (We are in /public, so project root is ../)
$publicDir = __DIR__;
$baseDir = dirname($publicDir); // Go up one level

echo "[INFO] Current Dir: " . $publicDir . "\n";
echo "[INFO] Project Root: " . $baseDir . "\n";

$pathsToCheck = [
    $baseDir . '/vendor/autoload.php',
    $baseDir . '/bootstrap/app.php'
];

$validRoot = false;
foreach($pathsToCheck as $p) {
    if (file_exists($p)) {
        echo "[OK] Found critical file: " . basename($p) . "\n";
        $validRoot = true;
    } else {
        echo "[ERR] Missing critical file: " . $p . "\n";
    }
}

if (!$validRoot) {
    echo "\n[CRITICAL] Cannot locate project root. Check where you placed this file.\n";
    // Try to be smart: maybe we are IN root (if public_html contains app, bootstrap, etc directly)
    if (file_exists($publicDir . '/vendor/autoload.php')) {
        echo "[INFO] Found vendor in Current Dir. adjusting root...\n";
        $baseDir = $publicDir;
    }
}

// 1. Clear Bootstrap Cache
$cacheDir = $baseDir . '/bootstrap/cache';
if (is_dir($cacheDir)) {
    echo "\n[-] Clearing Bootstrap Cache ($cacheDir)...\n";
    $files = glob($cacheDir . '/*.php');
    foreach ($files as $file) {
        if (basename($file) != '.gitignore') {
             if (unlink($file)) echo "    Deleted: " . basename($file) . "\n";
             else echo "    Failed to delete: " . basename($file) . " (Permission?)\n";
        }
    }
} else {
    echo "\n[?] Cache dir not found: $cacheDir\n";
}

// 2. Try Composer Dump-Autoload
echo "\n[-] Trying Composer Dump-Autoload...\n";

// Method A: Shell Exec
$output = shell_exec("cd $baseDir && composer dump-autoload 2>&1");
echo "    Shell Output: " . ($output ?: 'No output') . "\n";

// Method B: PHP Passthru (if shell blocked)
// echo "    Trying exec...\n";
// exec("cd $baseDir && composer dump-autoload", $out2, $ret);
// echo "    Exec Return: $ret\n";

echo "\n[DONE] Cache should be cleared. Try opening your app now.\n";
echo "If still error, delete 'bootstrap/cache/*.php' manually via File Manager.";
echo "</pre>";
