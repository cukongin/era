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
