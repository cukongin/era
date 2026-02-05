<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class MigrationController extends Controller
{
    // List of tables to migrate, in order of dependency.
    // Parent tables first, then children.
    protected $tables = [
        'tahun_ajaran',
        'global_settings',
        'grading_settings',
        'grading_whitelist',
        'predikat_nilai',
        'bobot_penilaian',
        'jenjang', 
        'mapel',
        'kkm_mapel',
        'users', // Special handling: Skip admins
        'data_guru',
        'data_siswa',
        'kelas',
        'anggota_kelas',
        'periode',
        'pengajar_mapel',
        'nilai_siswa',
        'absensi', // catatan_kehadiran alias? Check DB
        'catatan_wali_kelas',
        'promotion_decisions',
        'ekstrakurikuler',
        'nilai_ekstrakurikuler',
        'prestasi_siswa'
    ];

    public function export(Request $request) 
    {
        if (auth()->user()->role !== 'admin') abort(403);

        try {
            $data = [
                'meta' => [
                    'version' => '1.0',
                    'exported_at' => now(),
                    'exported_by' => auth()->user()->name,
                    'app_name' => \App\Models\GlobalSetting::val('app_name', 'ERapor')
                ],
                'tables' => []
            ];

            foreach ($this->tables as $table) {
                if (!Schema::hasTable($table)) continue;

                $query = DB::table($table);

                // Filter Logic
                if ($table === 'users') {
                    // EXCLUDE ADMINS (ID 1 or Role Admin) to prevent locking out the target system
                    $query->where('role', '!=', 'admin')->where('id', '!=', 1);
                }
                
                // If 'absensi' table missing, try 'catatan_kehadiran'
                if ($table === 'absensi' && !Schema::hasTable('absensi')) {
                    if (Schema::hasTable('catatan_kehadiran')) {
                        $query = DB::table('catatan_kehadiran');
                    } else {
                        continue;
                    }
                }

                $rows = $query->get();
                
                // Encode rows (handle binary if any? usually text is fine)
                $data['tables'][$table] = $rows;
            }

            $fileName = 'erapor_full_backup_' . date('Y-m-d_H-i-s') . '.json';
            $jsonContent = json_encode($data, JSON_PRETTY_PRINT);

            return response()->streamDownload(function () use ($jsonContent) {
                echo $jsonContent;
            }, $fileName, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            return back()->with('error', 'Export Gagal: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        
        $request->validate([
            'backup_file' => 'required|file|mimes:json'
        ]);

        DB::beginTransaction();
        try {
            $path = $request->file('backup_file')->getRealPath();
            $content = file_get_contents($path);
            $data = json_decode($content, true);

            if (!$data || !isset($data['tables'])) {
                throw new \Exception("Format file tidak valid.");
            }

            $log = [];
            $countTotal = 0;

            // Disable Foreign Key Checks for bulk insert
            Schema::disableForeignKeyConstraints();

            foreach ($this->tables as $table) {
                // Name mapping alias
                $targetTable = $table;
                if ($table === 'absensi' && !Schema::hasTable('absensi') && Schema::hasTable('catatan_kehadiran')) {
                    $targetTable = 'catatan_kehadiran';
                }

                if (!isset($data['tables'][$table])) continue;
                if (!Schema::hasTable($targetTable)) continue;

                $rows = $data['tables'][$table];
                $count = 0;

                foreach ($rows as $row) {
                    // Convert array to clean array
                    $row = (array)$row;

                    // SMART UPSERT LOGIC
                    // Ideally we identify Unique Keys. 
                    // But for "Super Migration", usually ID retention is desired if syncing clones.
                    // If ID exists, UPDATE. If not, INSERT.
                    
                    if (isset($row['id'])) {
                        $exists = DB::table($targetTable)->where('id', $row['id'])->exists();
                        if ($exists) {
                            DB::table($targetTable)->where('id', $row['id'])->update($row);
                        } else {
                            DB::table($targetTable)->insert($row);
                        }
                    } else {
                        // Table without ID? (Pivot tables like anggota_kelas might not have ID in some legacy schemas, but ideally they do)
                        // If no ID column, try composite insert catch
                        try {
                            DB::table($targetTable)->insert($row);
                        } catch (\Exception $e) {
                            // Ignore duplicates if no ID
                        }
                    }
                    $count++;
                }
                
                if ($count > 0) {
                    $log[] = "$targetTable: $count data diproses.";
                    $countTotal += $count;
                }
            }

            Schema::enableForeignKeyConstraints();
            DB::commit();

            return back()->with('success', "Migrasi Sukses! Total $countTotal data berhasil diproses dari " . count($log) . " tabel.");

        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
            DB::rollBack();
            Log::error("Import Failed: " . $e->getMessage());
            return back()->with('error', "Import Gagal: " . $e->getMessage());
        }
    }
}
