<?php
// public/repair_tool.php (V2: Surgical Access)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>System Repair Tool (V2: Surgery Mode)</h1>";
echo "<pre style='background:#eee; padding:10px; border:1px solid #ccc;'>";

// Detect Paths
$publicDir = __DIR__;
$baseDir = dirname($publicDir); // Go up one level

echo "[INFO] Project Root: " . $baseDir . "\n";

// 1. Clear Bootstrap Cache
$cacheDir = $baseDir . '/bootstrap/cache';
if (is_dir($cacheDir)) {
    echo "\n[-] Clearing Bootstrap Cache...\n";
    $files = glob($cacheDir . '/*.php');
    foreach ($files as $file) {
        if (basename($file) != '.gitignore') {
             @unlink($file);
             echo "    Deleted: " . basename($file) . "\n";
        }
    }
}

// 2. SURGICAL REMOVAL of Deleted Classes from Composer
$vendorDir = $baseDir . '/vendor/composer';
$filesToPatch = ['autoload_classmap.php', 'autoload_static.php', 'autoload_psr4.php'];

// List of deleted classes causing issues
$targets = [
    'GradingFormula', 
    'SchoolIdentity',
    'SchoolSettingController',
    'AcademicSettingController',
    'GradeAdjustmentController',
    'GradingRuleController'
];

echo "\n[-] Patching Composer Autoload Files (Surgery)...\n";

foreach ($filesToPatch as $f) {
    $path = $vendorDir . '/' . $f;
    if (file_exists($path)) {
        echo "    Checking $f... ";
        $content = file_get_contents($path);
        
        $lines = explode("\n", $content);
        $newLines = [];
        $foundCount = 0;
        
        foreach($lines as $line) {
             $hit = false;
             foreach($targets as $target) {
                 // Check if line contains the class name (and likely the path)
                 if (strpos($line, $target) !== false) {
                     $hit = true;
                     // Only echo if verified it's a map entry
                     echo "\n      [BAD] Removed: " . trim(substr($line, 0, 100)) . "...";
                     break; 
                 }
             }
             
             if (!$hit) {
                 $newLines[] = $line;
             } else {
                 $foundCount++;
             }
        }
        
        if ($foundCount > 0) {
            file_put_contents($path, implode("\n", $newLines));
            echo "\n      [SUCCESS] Removed $foundCount stale entries.\n";
        } else {
             echo "Clean.\n";
        }
    } else {
        echo "    [SKIP] $f not found.\n";
    }
}

echo "\n[DONE] Process Completed.\n";
echo "1. If you see 'Removed' messages, the corrupt references are gone.\n";
echo "2. Please try accessing your dashboard again.\n";
echo "</pre>";
