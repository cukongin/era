<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use App\Models\NilaiIjazah;
use App\Models\SchoolIdentity;
use App\Models\GradingFormula; // [MOD]
use App\Services\FormulaEngine; // [MOD]

class IjazahController extends Controller
{
    private function getContext()
    {
        $user = Auth::user();
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // Determine Class
        // Admin/TU can select any class
        // Teacher limited to their class
        $kelas = null;
        
        if ($user->isAdmin() || $user->isTu()) {
            $kelasId = request('kelas_id');
            if ($kelasId) {
                $kelas = Kelas::find($kelasId);
            } else {
                // Default to first final year class (Grade 6 or 9)
                $kelas = Kelas::where('id_tahun_ajaran', $activeYear->id)
                    ->where(function($q) {
                        $q->where('tingkat_kelas', 6)
                          ->orWhere('tingkat_kelas', 9)
                          ->orWhere('tingkat_kelas', 12);
                    })
                    ->first();
            }
        } else {
            // Wali Kelas
            $kelas = Kelas::where('id_wali_kelas', $user->id)
                ->where('id_tahun_ajaran', $activeYear->id)
                ->first();
        }

        return [$kelas, $activeYear];
    }

    public function index()
    {
        list($kelas, $activeYear) = $this->getContext();

        if (!$kelas) {
            return view('ijazah.no-class', ['year' => $activeYear]);
        }

        // Validate Final Year
        $isFinalYear = in_array($kelas->tingkat_kelas, [6, 9, 12]);
        if (!$isFinalYear) {
             return view('ijazah.not-final-year', ['kelas' => $kelas]);
        }

        // Get Mapels (Religion + General + Mulok)
        // Similar to Report Controller
        // For Ijazah, usually we need ALL mapels that appear in Ijazah
        // Filter Mapel Ujian (If Configured)
        $jenjang = $kelas->jenjang->kode ?? ($kelas->tingkat_kelas <= 6 ? 'MI' : 'MTS');
        $selectedMapelIds = \App\Models\UjianMapel::where('id_tahun_ajaran', $activeYear->id)
                                ->where('jenjang', $jenjang)
                                ->pluck('id_mapel');

        if ($selectedMapelIds->isNotEmpty()) {
            // Prioritize Admin Settings (UjianMapel) purely
            $mapelIds = $selectedMapelIds; 
        } else {
             // Fallback: Use Class Plotted Subjects
             $mapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
        }

        $mapels = Mapel::whereIn('id', $mapelIds)
            ->orderBy('kategori', 'asc')
            ->orderBy('nama_mapel', 'asc')
            ->get();

        // Get Students
        $students = $kelas->anggota_kelas()->with('siswa')->get()->sortBy('siswa.nama_lengkap');

        // Get Existing Ijazah Grades
        $grades = NilaiIjazah::whereIn('id_siswa', $students->pluck('id_siswa'))
            ->get()
            ->groupBy('id_siswa');

        // Get KKM
        $jenjangKode = $kelas->jenjang->kode ?? ($kelas->tingkat_kelas <= 6 ? 'MI' : 'MTS');
        $kkm = \App\Models\KkmMapel::whereIn('id_mapel', $mapels->pluck('id'))
            ->where('jenjang_target', $jenjangKode)
            ->where('id_tahun_ajaran', $activeYear->id)
            ->pluck('nilai_kkm', 'id_mapel');

        return view('ijazah.index', compact('kelas', 'activeYear', 'students', 'mapels', 'grades', 'kkm'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'grades' => 'required|array',
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);
        
        $action = $request->input('action', 'draft'); // draft or finalize
        $status = ($action === 'finalize') ? 'final' : 'draft';

        $count = 0;
        foreach ($request->grades as $siswaId => $mapels) {
            foreach ($mapels as $mapelId => $data) {
                // Data: ['rata_rata' => ..., 'ujian' => ...]
                
                $rata = $data['rata_rata'] ?? null;
                $ujian = $data['ujian'] ?? null;
                
                // Allow saving empty/null to clear data? 
                // Using updateOrCreate will create even if nulls if we don't filter.
                // But usually we want to clear if empty string passed. 
                // Input type is number, empty string becomes null usually.
                
                // Calculate Final (Dynamic Formula)
                $bRapor = \App\Models\GlobalSetting::val('ijazah_bobot_rapor', 60);
                $bUjian = \App\Models\GlobalSetting::val('ijazah_bobot_ujian', 40);
                
                $nilaiIjazah = null;
                if ($rata !== null && $ujian !== null) {
                    $nilaiIjazah = ($rata * ($bRapor/100)) + ($ujian * ($bUjian/100));
                    $nilaiIjazah = round($nilaiIjazah, 2); 
                } elseif ($rata !== null) {
                    $nilaiIjazah = $rata; // If Ujian empty? Or respect weight? Usually if Ujian empty, NA not valid.
                } elseif ($ujian !== null) {
                    $nilaiIjazah = $ujian;
                }

                NilaiIjazah::updateOrCreate(
                    [
                        'id_siswa' => $siswaId,
                        'id_mapel' => $mapelId
                    ],
                    [
                        'rata_rata_rapor' => $rata,
                        'nilai_ujian_madrasah' => $ujian,
                        'nilai_ijazah' => $nilaiIjazah,
                        'status' => $status,
                        'updated_at' => now()
                    ]
                );
                $count++;
            }
        }

        // If Finalizing, ensure ALL records for this class are marked final?
        // The loop updates only submitted fields.
        // Assuming form contains all fields.
        
        if ($action === 'finalize') {
             return back()->with('success', "Data Nilai Ijazah berhasil DIKUNCI (FINAL).");
        }

        return back()->with('success', "Berhasil menyimpan $count data nilai ijazah (DRAFT).");
    }

    public function generateRataRata(Request $request)
    {
        // Automation Tool: Pull Average from Rapor (Existing NilaiSiswa)
        // Logic: Pull average of last X semesters.
        // For MI: Kelas 4, 5, 6 (Sem 1-5).
        // For MTS: Kelas 7, 8, 9 (Sem 1-5).
        
        $kelasId = $request->kelas_id;
        $kelas = Kelas::findOrFail($kelasId);
        $jenjang = $kelas->jenjang->kode;
        
        // Determine Target Semesters/Levels
        $levels = [];
        if ($jenjang === 'MI') {
            $val = \App\Models\GlobalSetting::val('ijazah_range_mi', '4,5,6');
            $levels = $val ? explode(',', $val) : [4,5,6];
        } elseif ($jenjang === 'MTS') {
            $val = \App\Models\GlobalSetting::val('ijazah_range_mts', '7,8,9');
            $levels = $val ? explode(',', $val) : [7,8,9];
        } // MA: 10,11,12 ?

        $students = $kelas->anggota_kelas()->pluck('id_siswa');
        $mapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
        
        $count = 0;
        foreach ($students as $siswaId) {
            foreach ($mapelIds as $mapelId) {
                // Fetch All "Final" Grades for this student & mapel, filtered by Level
                $query = \App\Models\NilaiSiswa::where('id_siswa', $siswaId)
                    ->where('id_mapel', $mapelId)
                    ->whereNotNull('nilai_akhir');

                if (!empty($levels)) {
                    $query->whereHas('kelas', function($q) use ($levels) {
                        $q->whereIn('tingkat_kelas', $levels);
                    });
                }

                $allGrades = $query->get();
                
                if ($allGrades->isEmpty()) continue;
                
                $avg = $allGrades->avg('nilai_akhir');
                
                // Update specific column only (don't overwrite Ujian)
                $entry = NilaiIjazah::updateOrCreate(
                    ['id_siswa' => $siswaId, 'id_mapel' => $mapelId],
                    ['rata_rata_rapor' => round($avg, 2)]
                );
                
                // Recalculate Final if Ujian exists (Dynamic)
                // [MOD] Use Hybrid Formula
                $jenjang = strtolower($kelas->jenjang->kode ?? ($kelas->tingkat_kelas <= 6 ? 'mi' : 'mts'));
                $context = ($jenjang === 'mts') ? 'ijazah_mts' : 'ijazah_mi';
                $activeFormula = GradingFormula::where('context', $context)->where('is_active', true)->first();

                if ($entry->rata_rata_rapor !== null && $entry->nilai_ujian_madrasah !== null) {
                    $final = 0;
                    if ($activeFormula) {
                        $vars = [
                            '[Rata_Rapor_MTS]' => $entry->rata_rata_rapor,
                            '[Rata_Rapor_MI]' => $entry->rata_rata_rapor, // Alias
                            '[Nilai_Ujian]' => $entry->nilai_ujian_madrasah
                        ];
                        $final = FormulaEngine::calculate($activeFormula->formula, $vars);
                    } else {
                        // Fallback: Hardcode Logic
                        $bRapor = \App\Models\GlobalSetting::val('ijazah_bobot_rapor', 60);
                        $bUjian = \App\Models\GlobalSetting::val('ijazah_bobot_ujian', 40);
                        $final = ($entry->rata_rata_rapor * ($bRapor/100)) + ($entry->nilai_ujian_madrasah * ($bUjian/100));
                    }
                    
                    $entry->nilai_ijazah = round($final, 2);
                    $entry->save();
                }

                $count++;
            }
        }
        
        $levelInfo = !empty($levels) ? ' (Kelas ' . implode(',', $levels) . ')' : ' (Semua Semester)';
        return back()->with('success', "Auto-Generate Selesai. $count rata-rata rapor diperbarui dari database$levelInfo.");
    }

    public function printDKN($kelasId)
    {
        $kelas = Kelas::with(['jenjang', 'tahun_ajaran', 'wali_kelas'])->findOrFail($kelasId);
        // Filter Mapel Ujian (If Configured)
        $jenjang = $kelas->jenjang->kode ?? ($kelas->tingkat_kelas <= 6 ? 'MI' : 'MTS');
        $selectedMapelIds = \App\Models\UjianMapel::where('id_tahun_ajaran', $kelas->id_tahun_ajaran)
                                ->where('jenjang', $jenjang)
                                ->pluck('id_mapel');

        if ($selectedMapelIds->isNotEmpty()) {
             $mapelIds = $selectedMapelIds;
        } else {
             $mapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
        }

        $mapels = Mapel::whereIn('id', $mapelIds)
            ->orderBy('kategori', 'asc')
            ->orderBy('nama_mapel', 'asc') 
            ->get();

        $students = $kelas->anggota_kelas()->with('siswa')->get()->sortBy('siswa.nama_lengkap');
        
        $grades = NilaiIjazah::whereIn('id_siswa', $students->pluck('id_siswa'))
            ->get()
            ->groupBy('id_siswa');

        $school = SchoolIdentity::first();

        return view('ijazah.print_dkn', compact('kelas', 'mapels', 'students', 'grades', 'school'));
    }

    public function downloadTemplate(Request $request)
    {
        $kelasId = $request->kelas_id;
        $kelas = Kelas::findOrFail($kelasId);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $sheet->setCellValue('A1', 'NO');
        $sheet->setCellValue('B1', 'ID_SISWA'); // Hidden ID for safety
        $sheet->setCellValue('C1', 'NISN');
        $sheet->setCellValue('D1', 'NAMA SISWA');
        
        // Filter Mapel Ujian (If Configured)
        $jenjang = $kelas->jenjang->kode ?? ($kelas->tingkat_kelas <= 6 ? 'MI' : 'MTS');
        $selectedMapelIds = \App\Models\UjianMapel::where('id_tahun_ajaran', $kelas->id_tahun_ajaran)
                                ->where('jenjang', $jenjang)
                                ->pluck('id_mapel');

        if ($selectedMapelIds->isNotEmpty()) {
             $mapelIds = $selectedMapelIds;
        } else {
             $mapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelas->id)->pluck('id_mapel');
        }

        $mapels = Mapel::whereIn('id', $mapelIds)
            ->orderBy('kategori', 'asc')
            ->orderBy('nama_mapel', 'asc')
            ->get();
            
        $col = 'E';
        foreach ($mapels as $mapel) {
            $sheet->setCellValue($col . '1', $mapel->nama_mapel);
            // Store Mapel ID in row 2 or keep simple by usage name matching? 
            // Matching by name is risky if duplicates. Let's look up by name or use ID in header comment?
            // Simple approach: Match by Name. Mapel names are usually Unique per Class.
            $col++;
        }
        
        // Students
        $students = $kelas->anggota_kelas()->with('siswa')->get()->sortBy('siswa.nama_lengkap');
        $row = 2;
        $no = 1;
        foreach ($students as $s) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $s->id_siswa);
            $sheet->setCellValue('C' . $row, $s->siswa->nisn);
            $sheet->setCellValue('D' . $row, $s->siswa->nama_lengkap);
            $row++;
        }
        
        foreach(range('A','D') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = "Template_Nilai_Ujian_" . $kelas->nama_kelas . ".xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName) .'"');
        $writer->save('php://output');
        exit;
    }

    public function importGrades(Request $request)
    {
         $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        if (count($rows) < 2) return back()->with('error', 'File kosong atau format salah.');
        
        // Map Headers to Mapel IDs
        $headers = $rows[0];
        $mapelMap = []; // index => mapel_id
        
        // Fetch valid mapels for this class (Consistent with Download/Index logic)
        $kelasId = $request->kelas_id;
        $kelas = Kelas::findOrFail($kelasId);
        $jenjang = $kelas->jenjang->kode ?? ($kelas->tingkat_kelas <= 6 ? 'MI' : 'MTS');
        
        $selectedMapelIds = \App\Models\UjianMapel::where('id_tahun_ajaran', $kelas->id_tahun_ajaran)
                                ->where('jenjang', $jenjang)
                                ->pluck('id_mapel');

        if ($selectedMapelIds->isNotEmpty()) {
             $validMapelIds = $selectedMapelIds; 
        } else {
             $validMapelIds = \App\Models\PengajarMapel::where('id_kelas', $kelasId)->pluck('id_mapel');
        }

        $validMapels = Mapel::whereIn('id', $validMapelIds)->pluck('id', 'nama_mapel')->toArray(); // name => id
        
        foreach ($headers as $index => $header) {
            if ($index < 4) continue; // Skip No, ID, NISN, Nama
            if (empty($header)) continue;
            
            // Normalize header (trim)
            $mapelName = trim($header);
            
            // Find ID
            // Try exact match
            if (isset($validMapels[$mapelName])) {
                $mapelMap[$index] = $validMapels[$mapelName];
            } else {
                // Try case insensitive?
                // For now, require match.
            }
        }
        
        $count = 0;
        
        // Fetch Settings
        $bRapor = \App\Models\GlobalSetting::val('ijazah_bobot_rapor', 60);
        $bUjian = \App\Models\GlobalSetting::val('ijazah_bobot_ujian', 40);

        // Process Rows
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $siswaId = $row[1]; // Column B is ID_SISWA
            
            if (!$siswaId) continue;
            
            foreach ($mapelMap as $colIndex => $mapelId) {
                $score = $row[$colIndex];
                
                if (is_numeric($score)) {
                    // Update/Create NilaiIjazah (Update UJIAN only)
                    // We must NOT overwrite Rata Rapor if it exists.
                    // But firstOrCreate might leave Rata empty.
                    // Use updateOrCreate with specific field update.
                    
                    \App\Models\NilaiIjazah::updateOrCreate(
                        ['id_siswa' => $siswaId, 'id_mapel' => $mapelId],
                        ['nilai_ujian_madrasah' => $score]
                    );
                    
                    // Trigger Re-calc of Final?
                    // We need to fetch the fresh model to get rata_rata_rapor and calc final.
                    $entry = \App\Models\NilaiIjazah::where('id_siswa', $siswaId)
                        ->where('id_mapel', $mapelId)->first();
                        
                    if ($entry->rata_rata_rapor !== null && $entry->nilai_ujian_madrasah !== null) {
                         $final = ($entry->rata_rata_rapor * ($bRapor/100)) + ($entry->nilai_ujian_madrasah * ($bUjian/100));
                         $entry->nilai_ijazah = round($final, 2);
                         $entry->save();
                    }
                    
                    $count++;
                }
            }
        }
        
        return back()->with('success', "Import Selesai. $count nilai ujian berhasil disimpan.");
    }

    // --- ADMIN SETTINGS ---
    public function settings()
    {
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) return back()->with('error', 'Tahun ajaran aktif tidak ditemukan.');

        // Get All Mapels Grouped by Category
        $mapels = \App\Models\Mapel::orderBy('kategori', 'asc')->orderBy('nama_mapel', 'asc')->get();

        // Get Existing Selections
        $selectedMI = \App\Models\UjianMapel::where('id_tahun_ajaran', $activeYear->id)
                        ->where('jenjang', 'MI')->pluck('id_mapel')->toArray();
        $selectedMTS = \App\Models\UjianMapel::where('id_tahun_ajaran', $activeYear->id)
                        ->where('jenjang', 'MTS')->pluck('id_mapel')->toArray();

        return view('ijazah.settings', compact('activeYear', 'mapels', 'selectedMI', 'selectedMTS'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'mapel_mi' => 'array',
            'mapel_mts' => 'array',
            'bobot_rapor' => 'required|numeric|min:0|max:100',
            'bobot_ujian' => 'required|numeric|min:0|max:100',
            'min_lulus' => 'required|numeric|min:0|max:100',
            'range_mi' => 'array',
            'range_mts' => 'array',
        ]);

        // Save Weights
        \App\Models\GlobalSetting::updateOrCreate(['key' => 'ijazah_bobot_rapor'], ['value' => $request->bobot_rapor]);
        \App\Models\GlobalSetting::updateOrCreate(['key' => 'ijazah_bobot_ujian'], ['value' => $request->bobot_ujian]);
        \App\Models\GlobalSetting::updateOrCreate(['key' => 'ijazah_min_lulus'], ['value' => $request->min_lulus]);

        // Save Ranges (Implode array to CSV string)
        $rangeMi = implode(',', $request->input('range_mi', []));
        $rangeMts = implode(',', $request->input('range_mts', []));
        
        // If empty (user unchecked all), maybe save '0'? Or specific handling.
        // For Ijazah, usually at least one class needed. If empty, maybe assume default?
        // Let's save what user sent. If empty, it's empty.
        
        \App\Models\GlobalSetting::updateOrCreate(['key' => 'ijazah_range_mi'], ['value' => $rangeMi]);
        \App\Models\GlobalSetting::updateOrCreate(['key' => 'ijazah_range_mts'], ['value' => $rangeMts]);

        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) return back()->with('error', 'Tahun ajaran aktif tidak ditemukan.');

        // Clear existing for this year
        \App\Models\UjianMapel::where('id_tahun_ajaran', $activeYear->id)->delete();

        $insertData = [];
        $timestamp = now();

        // MI
        if ($request->has('mapel_mi')) {
            foreach ($request->mapel_mi as $mapelId) {
                $insertData[] = [
                    'id_tahun_ajaran' => $activeYear->id,
                    'jenjang' => 'MI',
                    'id_mapel' => $mapelId,
                    'created_at' => $timestamp, 'updated_at' => $timestamp
                ];
            }
        }

        // MTS
        if ($request->has('mapel_mts')) {
            foreach ($request->mapel_mts as $mapelId) {
                $insertData[] = [
                    'id_tahun_ajaran' => $activeYear->id,
                    'jenjang' => 'MTS',
                    'id_mapel' => $mapelId,
                    'created_at' => $timestamp, 'updated_at' => $timestamp
                ];
            }
        }

        if (!empty($insertData)) {
            \App\Models\UjianMapel::insert($insertData);
        }

        return back()->with('success', 'Setting Mata Pelajaran Ujian berhasil disimpan.');
    }
}
