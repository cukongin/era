<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

try {
    $kernel->call('view:clear');
    echo "View Cache Cleared! <br>";
    
    $kernel->call('route:clear');
    echo "Route Cache Cleared! <br>";
    
    $kernel->call('config:clear');
    echo "Config Cache Cleared! <br>";
    
    echo "<h3>SUKSES! Silakan coba refresh halaman Manajemen Kelas sekarang.</h3>";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<hr>";
echo "<h2>DIAGNOSA SERVER:</h2>";

// 1. Check Permissions
echo "<h3>1. Cek Permission Folder:</h3>";
$dirs = ['../storage', '../storage/framework', '../storage/framework/views', '../storage/logs'];
echo "<ul>";
foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    $writable = is_writable($path) ? "<span style='color:green'>WRITABLE (OK)</span>" : "<span style='color:red'>NOT WRITABLE (GAGAL)</span>";
    echo "<li>$dir: $writable</li>";
}
echo "</ul>";

// 2. Parsed Last Error
echo "<h3>2. Pesan Error Terakhir (Jelas):</h3>";
$logPath = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    $lines = array_slice($lines, -500); // Read last 500 lines
    $lines = array_reverse($lines); // Reverse to find latest first
    
    $foundError = false;
    foreach ($lines as $line) {
        if (strpos($line, '.ERROR:') !== false) {
            echo "<div style='background:#ffdddd; border:1px solid red; padding:15px; border-radius:10px;'>";
            echo "<strong>WAKTU:</strong> " . substr($line, 0, 21) . "<br>";
            echo "<strong>PESAN ERROR:</strong><br><span style='font-size:1.2em; color:red; font-weight:bold;'>" . substr($line, 22) . "</span>";
            echo "</div>";
            $foundError = true;
            break; // Stop at the first (latest) error
        }
    }
    
    if (!$foundError) {
        echo "<p>Tidak ditemukan error baru di 500 baris terakhir log.</p>";
    }
} else {
    echo "Log file tidak ditemukan.";
}

echo "<h3>3. Log Mentah (Stack Trace):</h3>";
echo "<div style='background:#eee; padding:10px; border:1px solid #999; overflow:auto; max-height:300px;'><pre>";
if (file_exists($logPath)) {
    $fp = fopen($logPath, 'r');
    fseek($fp, -4096, SEEK_END); 
    echo htmlspecialchars(fread($fp, 4096));
    fclose($fp);
}
echo "</pre></div>";
