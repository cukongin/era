<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\Siswa;
use App\Models\CatatanKehadiran;
use App\Models\CatatanWaliKelas; // Usually separate but maybe user wants attitude here?
// Wait, user said "Absensi & Kepribadian".
// Kepribadian usually in CatatanKehadiran (kelakuan, kerajinan, kebersihan) based on previous checks.
// Yes, I checked WaliKelasController previously, it uses CatatanKehadiran for these 3 fields.

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AttendanceImportController extends Controller
{

    public function downloadTemplateGlobal(Request $request, $jenjang)
    {
        $jenjang = strtoupper($jenjang);
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // Get Periods "3 Periode" usually implies all periods in the year.
        // MI has 3 (Cawu), MTS has 2 (Semester).
        // I will fetch ALL periods for this year.
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', $jenjang)
            ->orderBy('id', 'asc')
            ->get();
            
        // Get Classes (Filtered by Grade if requested)
        $targetGrade = $request->get('grade');

        $classesQuery = Kelas::where('id_tahun_ajaran', $activeYear->id)
            ->whereHas('jenjang', function($q) use ($jenjang) {
                $q->where('kode', $jenjang);
            });
            
        if ($targetGrade) {
            $classesQuery->where('tingkat_kelas', $targetGrade);
            $gradeLabel = "Kelas{$targetGrade}";
        } else {
            $gradeLabel = $jenjang;
        }

        $classes = $classesQuery->with(['anggota_kelas.siswa'])
            ->get()
            ->sortBy('nama_kelas');

        $filename = "Template_Kehadiran_Global_{$gradeLabel}_{$activeYear->nama_tahun}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($classes, $periods) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ["PETUNJUK: ID KELAS dan NIS jangan diubah. Isi Sakit/Izin/Alpa dengan angka. Isi Sikap dengan 'Baik', 'Cukup', 'Kurang'."]);
            
            // Header
            $headerRow = ['NO', 'ID KELAS', 'NAMA KELAS', 'NIS', 'NAMA SISWA'];
            
            foreach ($periods as $p) {
                $pName = $p->nama_periode;
                // Absensi
                $headerRow[] = "Sakit ($pName)";
                $headerRow[] = "Izin ($pName)";
                $headerRow[] = "Alpa ($pName)";
                // Kepribadian
                $headerRow[] = "Kelakuan ($pName)";
                $headerRow[] = "Kerajinan ($pName)";
                $headerRow[] = "Kebersihan ($pName)";
            }
            fputcsv($file, $headerRow);

            // Data
            $no = 1;
            foreach ($classes as $kelas) {
                foreach ($kelas->anggota_kelas as $ak) {
                    $row = [
                        $no++,
                        $kelas->id,
                        $kelas->nama_kelas,
                        $ak->siswa->nis_lokal,
                        $ak->siswa->nama_lengkap
                    ];
                    
                    // Empty slots
                    foreach ($periods as $p) {
                         $row[] = ''; // Sakit
                         $row[] = ''; // Izin
                         $row[] = ''; // Alpa
                         $row[] = ''; // Kelakuan
                         $row[] = ''; // Kerajinan
                         $row[] = ''; // Kebersihan
                    }
                    fputcsv($file, $row);
                }
            }
            fclose($file);
        }, 200, $headers);
    }

    public function previewGlobal(Request $request, $jenjang)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls']);
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        $file = $request->file('file');
        $lines = file($file->getRealPath());

        if (count($lines) < 3) return back()->with('error', 'File terlalu pendek.');

        // Detect Delimiter (semicolon vs comma)
        $firstLine = $lines[0];
        $delimiter = str_contains($firstLine, ';') ? ';' : ',';

        // Parse CSV
        $rows = array_map(function($l) use ($delimiter) {
            return str_getcsv($l, $delimiter);
        }, $lines);
        
        $headerRow = null;
        $headerRowIndex = -1;

        // Find Header Row (row containing 'NIS' and 'NAMA SISWA')
        foreach ($rows as $index => $r) {
            // Flatten to string for flexible searching
            $rowString = implode(' ', array_map(function($cell) {
                return strtoupper(trim($cell)); // Trim whitespace!
            }, $r));
            
            if (str_contains($rowString, 'NIS') && (str_contains($rowString, 'NAMA SISWA') || str_contains($rowString, 'SISWA'))) {
                $headerRow = array_map('trim', $r); // Trim headers!
                $headerRowIndex = $index;
                break;
            }
        }

        if (!$headerRow) {
             // Fallback to row 2 (index 1) if row 1 is instruction
             if (isset($rows[1])) {
                $headerRow = array_map('trim', $rows[1]);
                $headerRowIndex = 1;
            } else {
                 return back()->with('error', 'Format file tidak dikenali. Header (NIS/Siswa) tidak ditemukan.');
            }
        }
        
        // Parse Columns
        // Expected Pattern: "Sakit (Cawu 1)", "Kelakuan (Cawu 1)"
        // We map Column Index => { 'field' => 'sakit', 'period_id' => 123 }
        
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', $jenjang)
            ->get();
        $colMap = [];
        
        foreach ($headerRow as $idx => $col) {
            foreach ($periods as $p) {
                if (str_contains($col, "($p->nama_periode)")) {
                    $field = null;
                    if (str_starts_with($col, 'Sakit')) $field = 'sakit';
                    if (str_starts_with($col, 'Izin')) $field = 'izin';
                    if (str_starts_with($col, 'Alpa')) $field = 'tanpa_keterangan'; // Map Alpa -> tanpa_keterangan
                    if (str_starts_with($col, 'Kelakuan')) $field = 'kelakuan';
                    if (str_starts_with($col, 'Kerajinan')) $field = 'kerajinan';
                    if (str_starts_with($col, 'Kebersihan')) $field = 'kebersihan';
                    
                    if ($field) {
                        $colMap[$idx] = ['field' => $field, 'period_id' => $p->id];
                    }
                }
            }
        }
        
        $parsedData = [];
        $previewErrors = [];
        
        // Rows
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (count($row) < 5) continue;
            
            $kelasId = trim($row[1]);
            $namaKelas = trim($row[2]); // Capture Name
            $nis = trim($row[3]);
            
            $siswa = Siswa::where('nis_lokal', $nis)->first();
            if (!$siswa) {
                $previewErrors[] = "Baris " . ($i+1) . ": NIS $nis tidak ditemukan. Pastikan NIS benar.";
                continue;
            }
            
            $entry = [
                'siswa' => $siswa,
                'kelas_id' => $kelasId,
                'nama_kelas' => $namaKelas,
                'data' => [] 
            ];
            
            foreach ($colMap as $colIdx => $meta) {
                $val = isset($row[$colIdx]) ? trim($row[$colIdx]) : null;
                if ($val !== null && $val !== '') {
                    $entry['data'][$meta['period_id']][$meta['field']] = $val;
                }
            }
            
            if (!empty($entry['data'])) {
                $parsedData[] = $entry;
            }
        }
        
        $importKey = 'att_import_' . Auth::id() . '_' . time();
        Cache::put($importKey, ['data' => $parsedData], 3600);
        
        return view('admin.attendance_import.preview', compact('parsedData', 'previewErrors', 'importKey', 'jenjang', 'periods'));
    }

    public function storeGlobal(Request $request)
    {
        $cached = Cache::get($request->import_key);
        if (!$cached) return redirect()->back()->with('error', 'Sesi habis.');
        
        $data = $cached['data'];
        $count = 0;
        
        foreach ($data as $row) {
            $idSiswa = $row['siswa']->id;
            $idKelas = $row['kelas_id'];
            
            foreach ($row['data'] as $pId => $fields) {
                // Update Or Create CatatanKehadiran
                $updateData = [];
                // Only update fields that are present in the import
                if (isset($fields['sakit'])) $updateData['sakit'] = (int)$fields['sakit'];
                if (isset($fields['izin'])) $updateData['izin'] = (int)$fields['izin'];
                if (isset($fields['tanpa_keterangan'])) $updateData['tanpa_keterangan'] = (int)$fields['tanpa_keterangan'];
                if (isset($fields['kelakuan'])) $updateData['kelakuan'] = $fields['kelakuan'];
                if (isset($fields['kerajinan'])) $updateData['kerajinan'] = $fields['kerajinan'];
                if (isset($fields['kebersihan'])) $updateData['kebersihan'] = $fields['kebersihan'];
                
                if (!empty($updateData)) {
                    CatatanKehadiran::updateOrCreate(
                        [
                            'id_siswa' => $idSiswa,
                            'id_kelas' => $idKelas,
                            'id_periode' => $pId
                        ],
                        $updateData
                    );
                    $count++;
                }
            }
        }
        
        Cache::forget($request->import_key);
        return redirect()->route('grade.import.global.index')->with('success', "Berhasil import $count data kehadiran!")->with('active_tab', 'attendance');
    }
}
