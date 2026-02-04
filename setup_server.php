<?php
// setup_server.php - "Tenang Jaya" Deployment Helper
// Upload this file to your public_html folder and access it via browser.

// Optimization for Hostinger Web Process
set_time_limit(600); 
ini_set('memory_limit', '1024M');

// Mode: Debug Log
if (isset($_GET['mode']) && $_GET['mode'] === 'log') {
    $logFile = 'storage/logs/laravel.log';
    if (file_exists($logFile)) {
        echo "<h1>Last 50 Lines of Error Log:</h1><pre>";
        $lines = file($logFile);
        $lines = array_slice($lines, -50);
        foreach ($lines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
    } else {
        echo "Log file empty or not found.";
    }
    exit;
}

// Security: Password Protection
$password = "bismillah"; 
if (!isset($_GET['auth']) || $_GET['auth'] !== $password) {
    die("Akses Ditolak. <br><a href='?auth=bismillah'>Login Script</a> | <a href='?auth=bismillah&mode=log'>Cek Error Log</a>");
}

echo "<h1>🛠️ Setup Server E-Rapor (Tenang Jaya)</h1>";
echo "<a href='?auth=bismillah&mode=log' target='_blank' style='background:red; color:white; padding:10px; text-decoration:none;'>🔴 CEK PENYEBAB ERROR 500</a><hr>";

echo "PHP Version: " . phpversion() . "<br>";
flush();

function run_command($cmd) {
    echo "<strong>Running:</strong> $cmd<br>";
    flush();
    echo "<pre>";
    $output = shell_exec($cmd . " 2>&1");
    echo $output;
    echo "</pre><hr>";
    flush();
}

// 0. FIX PERMISSIONS FIRST (Critical for 500 Error)
echo "<h3>0. Fixing Permissions</h3>";
run_command("chmod -R 777 storage bootstrap/cache");
run_command("chmod -R 775 public");

// 1. Cek Environment
echo "<h3>1. Cek Environment</h3>";
if (!file_exists('.env')) {
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "✅ Berhasil membuat file .env dari .env.example<br>";
        echo "⚠️ <strong>PENTING:</strong> Edit file .env di File Manager, isi Database dan URL!<br>";
    } else {
        echo "❌ File .env.example tidak ditemukan.<br>";
    }
} else {
    echo "✅ File .env sudah ada.<br>";
}

// 2. Composer Install (Install Library)
// 2. Install Library (Composer)
echo "<h3>2. Install Library (Composer)</h3>";

if (file_exists('vendor') && is_dir('vendor')) {
    echo "✅ Folder <strong>vendor</strong> ditemukan (Manual Upload Detected).<br>";
    echo "Running: composer dump-autoload (Optimizing)...<br>";
    flush();
    // Try dump-autoload
    run_command("composer dump-autoload --optimize");
} else {
    // Hostinger usually supports 'composer' or 'php /usr/bin/composer'
    if (file_exists('composer.lock')) {
        // Try standard composer
        run_command("composer install --optimize-autoloader --no-dev");
    } else {
        echo "⚠️ File composer.lock tidak ditemukan. Pastikan Git Clone berhasil.<br>";
    }
}
    echo "⚠️ File composer.lock tidak ditemukan. Pastikan Git Clone berhasil.<br>";
}

// 3. Key Generate
echo "<h3>3. Generate Application Key</h3>";
run_command("php artisan key:generate --force");

// 4. Migrasi Database (Buat Tabel)
echo "<h3>4. Migrasi Database</h3>";
run_command("php artisan migrate --force");

// 5. Storage Link (Agar gambar muncul)
echo "<h3>5. Link Storage</h3>";
run_command("php artisan storage:link");

// 6. Config Cache
echo "<h3>6. Optimasi Config</h3>";
run_command("php artisan config:cache");
run_command("php artisan route:cache");
run_command("php artisan view:cache");

// 7. Fix Document Root (Redirect to public/)
echo "<h3>7. Fixing Document Root</h3>";
$htaccess = "
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/setup_server.php
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
";
file_put_contents('.htaccess', trim($htaccess));
echo "✅ Berhasil membuat file .htaccess (Safe Mode).<br>";

echo "<br><strong>🏁 Setup Selesai! Hapus file ini setelah sukses.</strong>";
echo "<br><a href='/portal-masuk'>Klik disini untuk Login (portal-masuk)</a>";
