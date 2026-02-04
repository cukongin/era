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
echo "<h2>LOG SERVER TERAKHIR (Untuk Diagnosa):</h2>";
echo "<div style='background:#eee; padding:10px; border:1px solid #999; overflow:auto; max-height:500px;'><pre>";

$logPath = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logPath)) {
    // Read last 4KB of log file safely
    $fp = fopen($logPath, 'r');
    fseek($fp, -4096, SEEK_END); 
    $content = fread($fp, 4096);
    echo htmlspecialchars($content);
    fclose($fp);
} else {
    echo "Log file tidak ditemukan.";
}
echo "</pre></div>";
