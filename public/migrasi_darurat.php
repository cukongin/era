<?php
// Emergency Migration Script
// Usage: Visit domain.com/migrasi_darurat.php
define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "<h1>Status Migrasi Darurat</h1>";
echo "<p>Menjalankan 'php artisan migrate'...</p>";

try {
    Artisan::call('migrate', ['--force' => true]);
    echo "<pre style='background:#f0f0f0; padding:15px; border:1px solid #ddd;'>" . Artisan::output() . "</pre>";
    echo "<h2 style='color:green'>SUCCESS! Database Updated.</h2>";
    echo "<p>Silakan <a href='/settings/formula'>Kembali ke Menu Formula</a></p>";
} catch (\Exception $e) {
    echo "<h2 style='color:red'>ERROR: " . $e->getMessage() . "</h2>";
}
