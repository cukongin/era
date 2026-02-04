<?php
// setup_server.php - "Tenang Jaya" Deployment Helper
// Upload this file to your public_html folder and access it via browser.

// Optimization for Hostinger Web Process
set_time_limit(600); // 10 Minutes
ini_set('memory_limit', '1024M'); // 1GB Memory

// Disable Output Buffering for Real-time Progress
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', 1);
}
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
while (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(1);

// Security: Password Protection
$password = "bismillah"; // Ganti jika perlu
if (!isset($_GET['auth']) || $_GET['auth'] !== $password) {
    die("Akses Ditolak. Tambahkan ?auth=bismillah di URL.");
}

echo "<h1>🛠️ Setup Server E-Rapor (Tenang Jaya)</h1>";
echo "PHP Version Running: <strong>" . phpversion() . "</strong> (Must be >= 8.0)<hr>";
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
echo "<h3>2. Install Library (Composer)</h3>";
// Hostinger usually supports 'composer' or 'php /usr/bin/composer'
if (file_exists('composer.lock')) {
    // Try standard composer
    run_command("composer install --optimize-autoloader --no-dev");
} else {
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

echo "<br><strong>🏁 Setup Selesai! Hapus file ini setelah sukses.</strong>";
echo "<br><a href='/portal-masuk'>Klik disini untuk Login (portal-masuk)</a>";
