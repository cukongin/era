<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Adapter\Local;

class BackupController extends Controller
{
    // Folder to store backups
    private $backupFolder = 'backups';

    public function index()
    {
        // Ensure folder exists
        if (!Storage::exists($this->backupFolder)) {
            Storage::makeDirectory($this->backupFolder);
        }

        $files = Storage::files($this->backupFolder);
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'path' => $file,
                'size' => $this->humanFileSize(Storage::size($file)),
                'created_at' => \Carbon\Carbon::createFromTimestamp(Storage::lastModified($file))->format('d M Y H:i:s'),
                'timestamp' => Storage::lastModified($file) // for sorting
            ];
        }

        // Sort by newest first
        usort($backups, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // This view will be a partial included in settings.index or a full view if needed
        return view('settings.backup', compact('backups'));
    }

    public function store()
    {
        try {
            // Database configuration
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            $dbHost = env('DB_HOST', '127.0.0.1');

            // Filename: backup_eranurul_2024-02-07_14-00-00.sql
            $filename = 'backup_' . $dbName . '_' . date('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('app/' . $this->backupFolder . '/' . $filename);

            // Ensure folder exists
            if (!Storage::exists($this->backupFolder)) {
                Storage::makeDirectory($this->backupFolder);
            }

            // Construct command
            // On Windows (XAMPP), mysqldump might not be in PATH globally.
            // Try explicit path if default fails, but for now assume PATH or relative to XAMPP.
            // Since environment is XAMPP, let's try generic first.
            
            // Note: Password argument syntax is -pPASSWORD (no space)
            $passwordPart = $dbPass ? "-p\"$dbPass\"" : "";
            
            // Use --routines to include procedures/functions if any
            // Use --routines to include procedures/functions if any
            // Check for mysqldump path
            $dumpPath = 'c:\xampp\mysql\bin\mysqldump.exe';
            if (!file_exists($dumpPath)) {
                $dumpPath = 'mysqldump'; 
            }
            
            // Use --routines to include procedures/functions if any
            // Use --add-drop-table to ensure restore works on existing DB
            // Use --hex-blob to handle binary data safely
            // Use --result-file to avoid shell encoding issues (UTF-16 vs UTF-8)
            // This ensures the file is written directly by mysqldump in correct UTF-8 format
            $command = "\"$dumpPath\" -u $dbUser $passwordPart -h $dbHost --routines --add-drop-table --hex-blob --result-file=\"$path\" $dbName 2>&1";

            // Execute command
            // Use exec or process. exec is simpler for this one-liner.
            $output = null;
            $resultCode = null;
            exec($command, $output, $resultCode);

            if ($resultCode === 0) {
                return back()->with('success', 'Backup database berhasil dibuat: ' . $filename);
            } else {
                \Illuminate\Support\Facades\Log::error("Backup Failed. Code: $resultCode. Command: $command");
                return back()->with('error', 'Gagal membuat backup. Pastikan mysqldump dapat diakses. Kode Error: ' . $resultCode);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $path = $this->backupFolder . '/' . $filename;
        if (Storage::exists($path)) {
            return Storage::download($path);
        }
        return back()->with('error', 'File tidak ditemukan.');
    }

    public function destroy($filename)
    {
        $path = $this->backupFolder . '/' . $filename;
        if (Storage::exists($path)) {
            Storage::delete($path);
            return back()->with('success', 'File backup berhasil dihapus.');
        }
        return back()->with('error', 'File tidak ditemukan.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt' // .sql often detected as text/plain
        ]);

        try {
            $file = $request->file('backup_file');
            $path = $file->getRealPath();

            // Database configuration
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            $dbHost = env('DB_HOST', '127.0.0.1');

            $passwordPart = $dbPass ? "-p\"$dbPass\"" : "";
            
            // Restore Command
            // Try explicit XAMPP path first, then fallback to global
            $mysqlPath = 'c:\xampp\mysql\bin\mysql.exe';
            if (!file_exists($mysqlPath)) {
                $mysqlPath = 'mysql'; // Fallback to PATH
            }

            // Add charset to prevent encoding errors (BOM or wide characters)
            $command = "\"$mysqlPath\" --default-character-set=utf8 -u $dbUser $passwordPart -h $dbHost --max_allowed_packet=512M $dbName < \"$path\" 2>&1";

            $output = [];
            $resultCode = null;
            exec($command, $output, $resultCode);

            if ($resultCode === 0) {
                // Clear cache after restore to ensure new data reflects immediately
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                \Illuminate\Support\Facades\Artisan::call('view:clear');
                
                return back()->with('success', 'Database berhasil di-restore! Silakan login ulang jika diperlukan.');
            } else {
                \Illuminate\Support\Facades\Log::error("Restore Failed. Code: $resultCode. Command: $command. Output: " . implode("\n", $output));
                return back()->with('error', 'Gagal restore database. Kode Error: ' . $resultCode . '. Detail: ' . implode(" ", $output));
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat restore: ' . $e->getMessage());
        }
    }
    
    // Internal Helper for Restore from Existing File (Settings Page)
    public function restoreFromLocal($filename)
    {
         $path = storage_path('app/' . $this->backupFolder . '/' . $filename);
         if (!file_exists($path)) {
             return back()->with('error', 'File tidak ditemukan di server.');
         }
         
         // Database configuration
         $dbName = env('DB_DATABASE');
         $dbUser = env('DB_USERNAME');
         $dbPass = env('DB_PASSWORD');
         $dbHost = env('DB_HOST', '127.0.0.1');

         $passwordPart = $dbPass ? "-p\"$dbPass\"" : "";
         
         // Check for mysql path
         $mysqlPath = 'c:\xampp\mysql\bin\mysql.exe';
         if (!file_exists($mysqlPath)) {
             $mysqlPath = 'mysql'; 
         }
         
         // Restore Command
         $command = "\"$mysqlPath\" --default-character-set=utf8 -u $dbUser $passwordPart -h $dbHost --max_allowed_packet=512M $dbName < \"$path\" 2>&1";

         $output = [];
         $resultCode = null;
         exec($command, $output, $resultCode);

         if ($resultCode === 0) {
             \Illuminate\Support\Facades\Artisan::call('cache:clear');
             \Illuminate\Support\Facades\Artisan::call('view:clear');
             return back()->with('success', "Database berhasil di-restore dari $filename.");
         } else {
             \Illuminate\Support\Facades\Log::error("Restore Local Failed. Code: $resultCode. Command: $command. Output: " . implode("\n", $output));
             return back()->with('error', "Gagal restore. Kode: $resultCode. Detail: " . implode(" ", $output));
         }
    }

    private function humanFileSize($size, $unit = "") {
        if( (!$unit && $size >= 1<<30) || $unit == "GB")
            return number_format($size/(1<<30),2)."GB";
        if( (!$unit && $size >= 1<<20) || $unit == "MB")
            return number_format($size/(1<<20),2)."MB";
        if( (!$unit && $size >= 1<<10) || $unit == "KB")
            return number_format($size/(1<<10),2)."KB";
        return number_format($size)." bytes";
    }
}
