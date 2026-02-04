<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajarMapel;
use App\Models\NilaiSiswa;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\BobotPenilaian;
use App\Models\AnggotaKelas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherDashboardController extends Controller
{
    use \App\Traits\HasDeadlineCheck;

    public function index()
    {
        $user = Auth::user();
        
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        
        $assignments = PengajarMapel::with(['kelas.jenjang', 'mapel'])
            ->where('id_guru', $user->id)
            ->whereHas('kelas', function($q) use ($activeYear) {
                if ($activeYear) {
                    $q->where('id_tahun_ajaran', $activeYear->id);
                }
            })
            ->get();

        $activePeriods = collect([]); 
        $activePeriodIds = []; 

        if ($activeYear) {
            $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
                ->where('status', 'aktif')
                ->get();
            
            $activePeriods = $periods;
            $activePeriodIds = $periods->pluck('id', 'lingkup_jenjang')->toArray();
        }

        $classProgress = [];
        $totalClasses = $assignments->count();
        $classesCompleted = 0;
        
        foreach ($assignments as $asg) {
            if (!$asg->kelas) continue;

            $jenjang = $asg->kelas->jenjang->kode; 
            $periodeId = $activePeriodIds[$jenjang] ?? null;

            $totalStudents = AnggotaKelas::where('id_kelas', $asg->id_kelas)->count();
            
            $gradedCount = 0;
            if ($periodeId) {
                $gradedCount = NilaiSiswa::where('id_kelas', $asg->id_kelas)
                    ->where('id_mapel', $asg->id_mapel)
                    ->where('id_periode', $periodeId)
                    ->count();
            }

            $percentage = $totalStudents > 0 ? round(($gradedCount / $totalStudents) * 100) : 0;
            if ($percentage == 100 && $totalStudents > 0) $classesCompleted++;

            // Period Status
            $status = 'locked';
            if ($periodeId) $status = 'open';
            if ($percentage == 100) $status = 'completed';
            if ($percentage == 0 && $periodeId) $status = 'not_started';
            if ($percentage > 0 && $percentage < 100) $status = 'in_progress';

            $classProgress[] = (object) [
                'id_kelas' => $asg->id_kelas,
                'nama_kelas' => $asg->kelas->nama_kelas,
                'mapel' => $asg->mapel->nama_mapel . ($asg->mapel->nama_kitab ? ' (' . $asg->mapel->nama_kitab . ')' : ''),
                'id_mapel' => $asg->id_mapel,
                'jenjang' => $jenjang,
                'total_siswa' => $totalStudents,
                'graded_count' => $gradedCount,
                'percentage' => $percentage,
                'status' => $status,
                'is_active' => (bool) $periodeId
            ];
        }

        $totalClasses = count($classProgress);
        $pendingClasses = $totalClasses - $classesCompleted;

        return view('teacher.dashboard', compact('classProgress', 'totalClasses', 'classesCompleted', 'pendingClasses', 'activePeriods', 'user', 'activeYear'));
    }

    public function inputNilai(Request $request, $kelasId, $mapelId)
    {
        $user = Auth::user();
        
        $assignment = PengajarMapel::with('kelas')
            ->where('id_kelas', $kelasId)
            ->where('id_mapel', $mapelId)
            ->firstOrFail();

        // Check Permissions: Must be Assigned Teacher OR Wali Kelas OR Admin
        $isTeacher = $assignment->id_guru === $user->id;
        $isWali = $assignment->kelas->id_wali_kelas === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isTeacher && !$isWali && !$isAdmin) {
            abort(403, 'Anda tidak memiliki akses untuk menginput nilai di kelas ini.');
        }

        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        $jenjang = $assignment->kelas->jenjang->kode;
        
        // Check for override period (e.g. from Admin Monitoring)
        if ($request->has('periode_id')) {
            $periode = Periode::find($request->periode_id);
        } else {
            $periode = Periode::where('id_tahun_ajaran', $activeYear->id)
                ->where('status', 'aktif')
                ->where('lingkup_jenjang', $jenjang)
                ->first();
        }

        if (!$periode) {
            return back()->with('error', 'Periode penilaian untuk ' . $jenjang . ' sedang ditutup.');
        }

        $blueprint = [
            'harian' => true,
            'uts' => true,    
            'uas' => ($jenjang === 'MTS'), 
            'label_uts' => ($jenjang === 'MI') ? 'Ujian Cawu' : 'PTS',
            'label_uas' => 'PAS/PAT'
        ];

        $students = AnggotaKelas::with('siswa')
            ->where('id_kelas', $kelasId)
            ->orderBy(DB::raw('(SELECT nama_lengkap FROM siswa WHERE id = anggota_kelas.id_siswa)'))
            ->get();

        $grades = NilaiSiswa::where('id_kelas', $kelasId)
            ->where('id_mapel', $mapelId)
            ->where('id_periode', $periode->id)
            ->get()
            ->keyBy('id_siswa');

        $bobot = BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang', strtoupper($jenjang))
            ->first();

        $kkm = \App\Models\KkmMapel::where('id_tahun_ajaran', $activeYear->id)
            ->where('id_mapel', $mapelId)
            ->where('jenjang_target', $jenjang)
            ->first();
            
        $nilaiKkm = $kkm ? $kkm->nilai_kkm : 70;

        $predicateRules = DB::table('predikat_nilai')
            ->where('jenjang', strtoupper($jenjang))
            ->orderBy('min_score', 'desc')
            ->get();

        $gradingSettings = DB::table('grading_settings')
            ->where('jenjang', strtoupper($jenjang))
            ->first();

        return view('teacher.input_nilai', compact('assignment', 'students', 'grades', 'periode', 'bobot', 'nilaiKkm', 'blueprint', 'predicateRules', 'gradingSettings'));
    }

    public function storeNilai(Request $request)
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        $status = $request->action === 'finalize' ? 'final' : 'draft';

        // VALIDATION: Prevent Finalize if incomplete
        if ($status === 'final') {
            $totalSiswa = \App\Models\AnggotaKelas::where('id_kelas', $request->id_kelas)->count();
            $grades = $request->grades ?? [];
            $submittedCount = count($grades);

            if ($submittedCount < $totalSiswa) {
                 return back()->with('error', 'GAGAL FINALISASI: Data siswa tidak lengkap.');
            }

            // Deep Check: Ensure NO FIELD IS EMPTY/NULL (Unless Weight is 0)
            $bH = $request->bobot_harian;
            $bT = $request->bobot_uts;
            $bA = $request->bobot_uas;

            foreach ($grades as $sid => $g) {
                // Check Harian (If Weight > 0)
                if ($bH > 0 && (!isset($g['harian']) || $g['harian'] === null || $g['harian'] === '')) {
                    return back()->with('error', 'GAGAL FINALISASI: Nilai Harian ada yang kosong! (Bobot > 0)');
                }
                // Check UTS (If Weight > 0)
                if ($bT > 0 && (!isset($g['uts']) || $g['uts'] === null || $g['uts'] === '')) {
                    return back()->with('error', 'GAGAL FINALISASI: Nilai UTS/Cawu ada yang kosong!');
                }
                // Check UAS (If Weight > 0 & Field Exists)
                if ($bA > 0 && isset($g['uas']) && ($g['uas'] === null || $g['uas'] === '')) {
                     return back()->with('error', 'GAGAL FINALISASI: Nilai UAS ada yang kosong!');
                }
            }
        }

        $check = $this->checkDeadlineAccess($request->id_periode, $request->id_kelas);
        if ($check !== true) {
            return back()->with('error', $check);
        }
        
        $jenjang = \App\Models\Kelas::find($request->id_kelas)->jenjang->kode;
        $activeJenjang = strtoupper($jenjang);

        $rules = DB::table('predikat_nilai')
            ->where('jenjang', $activeJenjang)
            ->orderBy('min_score', 'desc')
            ->get();

        $settings = DB::table('grading_settings')
            ->where('jenjang', $activeJenjang)
            ->first();

        $shouldRound = $settings ? $settings->rounding_enable : true;

        foreach ($request->grades as $siswaId => $data) {
            
            $harian = isset($data['harian']) && $data['harian'] !== '' ? $data['harian'] : null;
            $uts = isset($data['uts']) && $data['uts'] !== '' ? $data['uts'] : null;
            $uas = isset($data['uas']) && $data['uas'] !== '' ? $data['uas'] : null;

            // Calculation (treat null as 0 for calc, but keep null in DB logic if needed? 
            // Actually, if harian is null, it should probably be treated as 0 for score calc, 
            // OR we calculate based on filled assignments? 
            // Existing logic uses 0 for calc. Let's keep that but save NULL to DB.
            // Force Round Inputs to Integer (User Request: "Pupuhan Bilangan")
            $harianVal = round($harian ?? 0);
            $utsVal = round($uts ?? 0);
            $uasVal = round($uas ?? 0);
            
            // Re-assign processed values so they are saved as Integers
            if ($harian !== null) $harian = $harianVal;
            if ($uts !== null) $uts = $utsVal;
            if ($uas !== null) $uas = $uasVal;

            $bobotHarian = $request->bobot_harian / 100;
            $bobotUts = $request->bobot_uts / 100;
            $bobotUas = $request->bobot_uas / 100;
            
            // Check completeness for calculation
            // If any component is NULL, the final grade should be NULL (Incomplete/Draft)
            // Unless the bobot is 0 (meaning that component is not required)
            
            $isComplete = true;
            if ($bobotHarian > 0 && $harian === null) $isComplete = false;
            if ($bobotUts > 0 && $uts === null) $isComplete = false;
            if ($bobotUas > 0 && $uas === null) $isComplete = false;

            if ($isComplete) {
                // $nilaiAkhir = ($harianVal * $bobotHarian) + ($utsVal * $bobotUts) + ($uasVal * $bobotUas); // OLD (Unsafe)
                $nilaiAkhir = $this->calculateSmartScore($harianVal, $utsVal, $uasVal, $request->bobot_harian, $request->bobot_uts, $request->bobot_uas); // NEW (Normalized)
                
                $nilaiAsli = $nilaiAkhir; 

                if ($shouldRound) {
                    $nilaiAkhir = round($nilaiAkhir);
                    $nilaiAsli = round($nilaiAsli);
                } else {
                    $nilaiAkhir = round($nilaiAkhir, 2);
                    $nilaiAsli = round($nilaiAsli, 2);
                }
            } else {
                $nilaiAkhir = null;
                $nilaiAsli = null;
            }

            $isKatrol = isset($data['is_katrol']) && $data['is_katrol'] == '1';
            
            $kkmTarget = 70; 
            if (isset($settings->kkm_default)) $kkmTarget = $settings->kkm_default;
            
            $kkmMapel = \App\Models\KkmMapel::where('id_mapel', $request->id_mapel)
                ->where('id_tahun_ajaran', $activeYear->id)
                ->where('jenjang_target', $activeJenjang)
                ->value('nilai_kkm');
            if ($kkmMapel) $kkmTarget = $kkmMapel;

            if ($isKatrol && $nilaiAkhir < $kkmTarget) {
                $nilaiAkhir = $kkmTarget; 
            } elseif (!$isKatrol) {
                $nilaiAsli = null; 
            }
            
            $predikat = 'D'; 
            foreach ($rules as $rule) {
                if ($nilaiAkhir >= $rule->min_score) {
                    $predikat = $rule->grade;
                    break; 
                }
            }

            $existing = NilaiSiswa::where('id_siswa', $siswaId)
                ->where('id_kelas', $request->id_kelas)
                ->where('id_mapel', $request->id_mapel)
                ->where('id_periode', $request->id_periode)
                ->first();

            $oldData = $existing ? [
                'nilai_harian' => $existing->nilai_harian,
                'nilai_uts_cawu' => $existing->nilai_uts_cawu,
                'nilai_uas' => $existing->nilai_uas,
                'nilai_akhir' => $existing->nilai_akhir,
                'predikat' => $existing->predikat,
                'catatan' => $existing->catatan,
                'status' => $existing->status
            ] : null;

            $newData = [
                'nilai_harian' => $harian,
                'nilai_uts_cawu' => $uts,
                'nilai_uas' => $uas,
                'nilai_akhir' => $nilaiAkhir,
                'nilai_akhir_asli' => $nilaiAsli, 
                'is_katrol' => $isKatrol,
                'predikat' => $predikat,
                'catatan' => $data['catatan'] ?? null,
                'status' => $status
            ];

            if (!$existing || $oldData != $newData) {
                $nilaiSiswa = NilaiSiswa::updateOrCreate(
                    [
                        'id_siswa' => $siswaId,
                        'id_kelas' => $request->id_kelas,
                        'id_mapel' => $request->id_mapel,
                        'id_periode' => $request->id_periode,
                    ],
                    array_merge(['id_guru' => Auth::id()], $newData)
                );

                if ($existing) {
                    \App\Models\RiwayatPerubahanNilai::create([
                        'id_nilai_siswa' => $nilaiSiswa->id,
                        'id_user' => Auth::id(),
                        'data_lama' => $oldData,
                        'data_baru' => $newData
                    ]);
                }
            }
        }

        $msg = $status === 'final' ? 'Nilai berhasil difinalisasi dan dikunci.' : 'Nilai disimpan sebagai draft.';

        return back()->with('success', $msg);
    }

    public function unlockNilai(Request $request)
    {
        $user = Auth::user();
        
        // Find Class to check ownership
        $kelas = \App\Models\Kelas::find($request->id_kelas);
        if (!$kelas) return back()->with('error', 'Kelas tidak valid.');

        // Permission Check: Admin or Wali Kelas OR Assigned Teacher
        $isWali = $kelas->id_wali_kelas === $user->id;
        $isAdmin = $user->role === 'admin';
        
        // Subject teacher logic
        $isTeacher = false;
        if (!$isWali && !$isAdmin) {
             $assignment = PengajarMapel::where('id_kelas', $request->id_kelas)
                 ->where('id_mapel', $request->id_mapel)
                 ->where('id_guru', $user->id)
                 ->exists();
             
             if ($assignment) {
                 // Teacher MUST check deadline
                 $check = $this->checkDeadlineAccess($request->id_periode);
                 if ($check === true) {
                     $isTeacher = true;
                 } else {
                     return back()->with('error', 'Deadline terlewati. Hubungi Admin/Wali Kelas untuk membuka kunci.');
                 }
             }
        }

        if (!$isWali && !$isAdmin && !$isTeacher) {
             return back()->with('error', 'Akses ditolak.');
        }

        // Action: Revert status to 'draft'
        NilaiSiswa::where('id_kelas', $request->id_kelas)
            ->where('id_mapel', $request->id_mapel)
            ->where('id_periode', $request->id_periode)
            ->update(['status' => 'draft']);

        return back()->with('success', 'Nilai berhasil DIBUKA KEMBALI. Silakan edit.');
    }

    public function downloadTemplate($kelasId, $mapelId)
    {
        $user = Auth::user();
        $assignment = PengajarMapel::with('kelas')
            ->where('id_kelas', $kelasId)
            ->where('id_mapel', $mapelId)
            ->firstOrFail();

        // Check Permissions
        $isTeacher = $assignment->id_guru === $user->id;
        $isWali = $assignment->kelas->id_wali_kelas === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isTeacher && !$isWali && !$isAdmin) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh template ini.');
        }
        $kelas = $assignment->kelas;
        $jenjang = $kelas->jenjang->kode; 
        $mapel = $assignment->mapel;

        $students = AnggotaKelas::with('siswa')
            ->where('id_kelas', $kelasId)
            ->orderBy(DB::raw('(SELECT nama_lengkap FROM siswa WHERE id = anggota_kelas.id_siswa)'))
            ->get();

        $filename = "Template_Nilai_{$jenjang}_{$kelas->nama_kelas}_{$mapel->nama_mapel}.csv";

        $colLabel1 = "NILAI HARIAN";
        $colLabel2 = $jenjang == 'MI' ? "NILAI UJIAN CAWU" : "NILAI PTS";
        $colLabel3 = $jenjang == 'MI' ? "NILAI UAS (Tidak Dipakai)" : "NILAI PAS/PAT";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($students, $jenjang, $colLabel1, $colLabel2, $colLabel3) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Row 1: Instructions
            fputcsv($file, ["PETUNJUK: Jangan ubah NIS siswa. Isi nilai 0-100."]);
            
            // Row 2: Spacer
            fputcsv($file, []);

            // Row 3: Header
            // Setup columns dynamically based on Jenjang
            $headerRow = ['NO', 'NIS (JANGAN UBAH)', 'NAMA SISWA', $colLabel1, $colLabel2];
            
            // Only add UAS column if NOT MI (or if required)
            // User requested to remove it for MI
            if ($jenjang !== 'MI') {
                $headerRow[] = $colLabel3;
            }
            
            $headerRow[] = 'CATATAN';

            fputcsv($file, $headerRow);

            // Row 4+: Data
            foreach ($students as $index => $ak) {
                $row = [
                    $index + 1,
                    $ak->siswa->nis_lokal,
                    $ak->siswa->nama_lengkap,
                    0, 
                    0
                ];
                
                if ($jenjang !== 'MI') {
                    $row[] = 0; // Checkbox/Score for UAS
                }
                
                $row[] = ''; // Catatan
                
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importGrades(Request $request, $kelasId, $mapelId)
    {
        $request->validate([
            'file' => 'required|max:2048'
        ]);

        $user = Auth::user();
        $assignment = PengajarMapel::with('kelas')
            ->where('id_kelas', $kelasId)
            ->where('id_mapel', $mapelId)
            ->firstOrFail();

        // Check Permissions
        $isTeacher = $assignment->id_guru === $user->id;
        $isWali = $assignment->kelas->id_wali_kelas === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isTeacher && !$isWali && !$isAdmin) {
            abort(403, 'Anda tidak memiliki akses untuk mengimport nilai di kelas ini.');
        }
        $jenjang = $assignment->kelas->jenjang->kode;
        
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        $periode = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('status', 'aktif')
            ->where('lingkup_jenjang', $jenjang)
            ->first();

        if (!$periode) return back()->with('error', 'Periode tidak aktif.');

        $file = $request->file('file');
        $rawContent = file_get_contents($file->getRealPath());

        // --- ENCODING REPAIR ---
        $bom = substr($rawContent, 0, 2);
        if ($bom === "\xFF\xFE") { // UTF-16 LE
            $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-16LE');
        } elseif ($bom === "\xFE\xFF") { // UTF-16 BE
            $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-16BE');
        } else {
            $content = $rawContent;
            if (!mb_check_encoding($content, 'UTF-8')) {
                 $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8'); 
            }
        }
        
        // Vars
        $diagSampleRows = [];
        $diagHeaderFound = false;
        $diagHeaderIndex = -1;
        $diagTotalRows = 0;
        $diagFileType = 'Unknown';
        $diagColMap = [];
        $validData = [];
        $errors = [];
        
        // Debug Content
        $debugContent = substr($content, 0, 500);

        // Defaults (Will be overwritten if header found)
        $idxNis = 1; $idxHarian = 3; $idxUts = 4; $idxUas = 5; $idxCatatan = 6;

        // Detect HTML Table vs CSV
        if (stripos($content, '<html') !== false || stripos($content, '<table') !== false || stripos($content, '<tr') !== false) {
            libxml_use_internal_errors(true); 
            $dom = new \DOMDocument;
            @$dom->loadHTML($content);
            libxml_clear_errors();
            
            // Clean Scripts/Styles
            $scripts = $dom->getElementsByTagName('script');
            $styles = $dom->getElementsByTagName('style');
            $heads = $dom->getElementsByTagName('head');
            $removeNodes = [];
            foreach($scripts as $node) $removeNodes[] = $node;
            foreach($styles as $node) $removeNodes[] = $node;
            foreach($heads as $node) $removeNodes[] = $node;
            foreach($removeNodes as $node) if ($node->parentNode) $node->parentNode->removeChild($node);

            // Select Table
            $tables = $dom->getElementsByTagName('table');
            $targetTable = null;
            if ($tables->length > 0) {
                foreach ($tables as $tbl) {
                    $rowsText = $tbl->textContent; 
                    if (stripos($rowsText, 'NIS') !== false && (stripos($rowsText, 'NAMA') !== false || stripos($rowsText, 'SISWA') !== false)) {
                        $targetTable = $tbl;
                        break;
                    }
                }
            }
            
            $rows = $targetTable ? $targetTable->getElementsByTagName('tr') : $dom->getElementsByTagName('tr');
            $diagFileType = $targetTable ? 'HTML Table (Smart Select)' : 'HTML Table (All Rows)';
            $diagTotalRows = count($rows);
            
            // Fallback Logic
            if ($diagTotalRows < 5) {
                 $skeletonContent = strip_tags($content, '<tr><td><th>'); 
                 preg_match_all('/<tr\b[^>]*>(.*?)<\/tr>/is', $skeletonContent, $trMatches);
                 $regexRows = []; 
                 foreach($trMatches[1] as $trInner) {
                     $cols = [];
                     preg_match_all('/<t[dh]\b[^>]*>(.*?)<\/t[dh]>/is', $trInner, $tdMatches);
                     foreach($tdMatches[1] as $tdInner) $cols[] = strip_tags($tdInner);
                     if (!empty($cols)) $regexRows[] = $cols; 
                 }
                 if (count($regexRows) > $diagTotalRows) {
                     $rows = $regexRows;
                     $diagTotalRows = count($rows);
                     $diagFileType .= ' (Fallback: StripTags+Regex)';
                 }
            }

            // Normalize Rows
            $rowList = [];
            foreach ($rows as $r) {
                if (is_object($r)) {
                     $tds = $r->getElementsByTagName('td');
                     if ($tds->length == 0) $tds = $r->getElementsByTagName('th');
                     $cellData = [];
                     foreach ($tds as $td) $cellData[] = $td->textContent;
                     $rowList[] = $cellData;
                } else {
                     $rowList[] = $r;
                }
            }

        } else {
            // CSV
            $diagFileType = 'CSV / Plain';
            $content = str_replace(["\r\n", "\r"], "\n", $content);
            $firstLine = strtok($content, "\n");
            $delimiters = [';' => 0, ',' => 0, "\t" => 0];
            foreach ($delimiters as $delim => $count) $delimiters[$delim] = substr_count($firstLine, $delim);
            $bestDelim = array_search(max($delimiters), $delimiters); // Pick best
            
            $diagFileType .= " (Delim: '$bestDelim')";
            $lines = explode("\n", $content);
            $rowList = [];
            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $rowList[] = str_getcsv($line, $bestDelim);
            }
            $diagTotalRows = count($rowList);
        }

        // --- SHARED PROCESSING ---
        // 1. Scan Header
        $dataStartIndex = -1;
        foreach ($rowList as $i => $cells) {
            // Strict check: min 3 cols
            if (count($cells) < 3) continue;

            $foundNisAt = -1;
            $foundNamaAt = -1;
            
            foreach ($cells as $k => $cellVal) {
                $txt = strtoupper(trim($cellVal));
                if (strpos($txt, 'NIS') !== false) $foundNisAt = $k;
                if (strpos($txt, 'NAMA') !== false || strpos($txt, 'SISWA') !== false) $foundNamaAt = $k;
            }

            if ($foundNisAt !== -1 && $foundNamaAt !== -1 && $foundNisAt !== $foundNamaAt) {
                $diagHeaderFound = true;
                $diagHeaderIndex = $i;
                $dataStartIndex = $i + 1;
                
                // Reset defaults to -1 to prevent collision
                $idxNis = -1; $idxHarian = -1; $idxUts = -1; $idxUas = -1; $idxCatatan = -1;

                foreach ($cells as $k => $cellVal) {
                    $txt = strtoupper(trim($cellVal));
                    if (strpos($txt, 'NIS') !== false) $idxNis = $k;
                    elseif (strpos($txt, 'HARIAN') !== false) $idxHarian = $k;
                    elseif (strpos($txt, 'UJIAN') !== false || strpos($txt, 'UTS') !== false || strpos($txt, 'PTS') !== false) $idxUts = $k;
                    elseif (strpos($txt, 'UAS') !== false || strpos($txt, 'PAS') !== false) $idxUas = $k;
                    elseif (strpos($txt, 'CATATAN') !== false) $idxCatatan = $k;
                }

                $diagColMap = [
                    'NIS' => $idxNis, 'Harian' => $idxHarian, 'UTS' => $idxUts, 'UAS' => $idxUas, 'Catatan' => $idxCatatan
                ];
                break;
            }
        }

        // 2. Process Rows
        foreach ($rowList as $i => $cells) {
            if ($i < $dataStartIndex && $dataStartIndex != -1) continue; 
            
            if (count($diagSampleRows) < 5) {
                $diagSampleRows[] = [
                    'index' => $i,
                    'cols' => array_slice($cells, 0, 8),
                    'status' => (count($cells) < 3) ? 'Cols Mismatch' : 'Check NIS' 
                ];
            }

            // Safety check for min columns
            if (count($cells) < 3) continue;

            // Safe Read
            $nisRaw = ($idxNis != -1 && isset($cells[$idxNis])) ? $cells[$idxNis] : '';
            $nis = trim(html_entity_decode($nisRaw, ENT_QUOTES | ENT_HTML5));
            $nis = str_replace(["\xC2\xA0", "&nbsp;", " "], '', $nis); 
            $nis = preg_replace('/[\x00-\x1F\x7F]/u', '', $nis);

            if (empty($nis)) continue; 
            
            if (!is_numeric($nis)) {
                 foreach($diagSampleRows as &$s) if($s['index'] == $i) $s['status'] = "NIS Invalid: '$nis'";
                continue; 
            }

            foreach($diagSampleRows as &$s) if($s['index'] == $i) $s['status'] = "OK";

            // Safe Read Data
            // Safe Read Data + FORCE ROUND (User Request: "Pupuhan Bilangan")
            $harianRaw = ($idxHarian != -1 && isset($cells[$idxHarian])) ? (float) trim(strip_tags($cells[$idxHarian])) : 0;
            $harian = round($harianRaw);

            $utsRaw = ($idxUts != -1 && isset($cells[$idxUts])) ? (float) trim(strip_tags($cells[$idxUts])) : 0;
            $uts = round($utsRaw);

            $uasRaw = ($idxUas != -1 && isset($cells[$idxUas])) ? (float) trim(strip_tags($cells[$idxUas])) : 0;
            $uas = round($uasRaw);

            $catatan = ($idxCatatan != -1 && isset($cells[$idxCatatan])) ? trim(strip_tags($cells[$idxCatatan])) : '';
            
            $this->validateAndPush($validData, $errors, $i+1, $nis, $kelasId, $harian, $uts, $uas, $catatan);
        }

        if (empty($validData)) {
            return view('teacher.import_diagnostic', [
                'fileType' => $diagFileType,
                'headerFound' => $diagHeaderFound,
                'headerRowIndex' => $diagHeaderIndex,
                'totalRows' => $diagTotalRows,
                'columnMapping' => $diagColMap,
                'sampleRows' => $diagSampleRows,
                'debugContent' => $debugContent,
                'validationErrors' => $errors // Pass specific validation errors
            ]);
        }

        $importKey = 'import_nilai_' . Auth::id();
        \Illuminate\Support\Facades\Cache::put($importKey, [
            'kelas_id' => $kelasId,
            'mapel_id' => $mapelId,
            'periode_id' => $periode->id,
            'data' => $validData
        ], 3600);

        return view('teacher.import_preview', [
            'assignment' => $assignment,
            'validData' => $validData,
            'importErrors' => $errors,
            'importKey' => $importKey
        ]);
    }


    private function validateAndPush(&$validData, &$errors, $rowIndex, $nis, $kelasId, $harian, $uts, $uas, $catatan) {
        $siswa = \App\Models\Siswa::where('nis_lokal', $nis)->first();
        
        if (!$siswa) {
            $errors[] = ['row' => $rowIndex, 'message' => "NIS '$nis' tidak ditemukan."];
            return;
        }

        $inClass = AnggotaKelas::where('id_kelas', $kelasId)->where('id_siswa', $siswa->id)->exists();
        if (!$inClass) {
            $errors[] = ['row' => $rowIndex, 'message' => "Siswa '$nis' bukan anggota kelas ini."];
            return;
        }

        if ($harian < 0 || $harian > 100 || $uts < 0 || $uts > 100 || $uas < 0 || $uas > 100) {
            $errors[] = ['row' => $rowIndex, 'message' => "Nilai harus 0-100."];
            return;
        }

        $validData[] = [
            'id_siswa' => $siswa->id,
            'nama' => $siswa->nama_lengkap,
            'nis' => $nis,
            'harian' => $harian,
            'uts' => $uts,
            'uas' => $uas,
            'catatan' => $catatan
        ];
    }

    public function processImportGrades(Request $request)
    {
        $key = $request->import_key;
        $cached = \Illuminate\Support\Facades\Cache::get($key);

        if (!$cached) return redirect()->route('teacher.dashboard')->with('error', 'Sesi kadaluarsa.');

        $kelasId = $cached['kelas_id'];
        $mapelId = $cached['mapel_id'];
        $periodeId = $cached['periode_id'];
        $data = $cached['data'];

        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $jenjang = \App\Models\Kelas::find($kelasId)->jenjang->kode;
        
        $bobot = BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)->where('jenjang', strtoupper($jenjang))->first();
        if (!$bobot) return back()->with('error', 'Bobot belum diatur admin.');
        
        $gradingSettings = DB::table('grading_settings')->where('jenjang', $jenjang)->first();
        $shouldRound = $gradingSettings ? $gradingSettings->rounding_enable : true;
        
        $rules = DB::table('predikat_nilai')->where('jenjang', $jenjang)->orderBy('min_score', 'desc')->get();

        $count = 0;
        foreach ($data as $row) {
             $harian = $row['harian'];
             $uts = $row['uts'];
             $uas = $row['uas'];
             
             $nilaiAkhir = ($harian * ($bobot->bobot_harian/100)) + 
                           ($uts * ($bobot->bobot_uts_cawu/100)) + 
                           ($uas * ($bobot->bobot_uas/100));
                           
             if ($shouldRound) $nilaiAkhir = round($nilaiAkhir);
             else $nilaiAkhir = round($nilaiAkhir, 2);
             
             $predikat = 'D';
             foreach ($rules as $rule) {
                 if ($nilaiAkhir >= $rule->min_score) {
                     $predikat = $rule->grade;
                     break;
                 }
             }

             NilaiSiswa::updateOrCreate(
                 [
                     'id_siswa' => $row['id_siswa'],
                     'id_mapel' => $mapelId,
                     'id_periode' => $periodeId,
                 ],
                 [
                     'id_kelas' => $kelasId, // Update class if student moved
                     'id_guru' => Auth::id(),
                     'nilai_harian' => $harian,
                     'nilai_uts_cawu' => $uts,
                     'nilai_uas' => $uas,
                     'nilai_akhir' => $nilaiAkhir,
                     'predikat' => $predikat,
                     'catatan' => $row['catatan'],
                     'status' => 'draft'
                 ]
             );
             $count++;
        }

        \Illuminate\Support\Facades\Cache::forget($key);
        return redirect()->route('teacher.input-nilai', ['kelas' => $kelasId, 'mapel' => $mapelId])->with('success', "$count Nilai Berhasil Diimport!");
    }


    /**
     * Helper to Calculate Weighted Score Checking for Total Weight
     * Prevents logic errors if total weight != 100% (e.g. user disabled one component)
     */
    private function calculateSmartScore($h, $u, $a, $bH, $bT, $bA) 
    {
        $totalWeight = $bH + $bT + $bA;
        
        if ($totalWeight <= 0) return 0; // Prevent div by zero if all weights 0
        
        // Normalize: (Score * Weight) / TotalWeight
        // Example: H=0, U=50. Total=50. Score = (0 + U*50) / 50 = U. (Correct)
        $weightedSum = ($h * $bH) + ($u * $bT) + ($a * $bA);
        
        return $weightedSum / $totalWeight;
    }
}
