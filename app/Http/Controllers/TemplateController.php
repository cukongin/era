<?php

namespace App\Http\Controllers;

use App\Models\ReportTemplate;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = ReportTemplate::orderBy('type')->orderBy('name')->get();
        return view('settings.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('settings.templates.edit', ['template' => new ReportTemplate()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'content' => 'required',
            'margins' => 'nullable|array',
            'orientation' => 'required'
        ]);
        
        $data['is_active'] = false; // Default off
        
        $tpl = ReportTemplate::create($data);
        return redirect()->route('settings.templates.index')->with('success', 'Template berhasil dibuat.');
    }

    public function edit(ReportTemplate $template)
    {
        return view('settings.templates.edit', compact('template'));
    }

    public function update(Request $request, ReportTemplate $template)
    {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'content' => 'required',
            'margins' => 'nullable|array',
            'orientation' => 'required'
        ]);

        $template->update($data);
        return redirect()->route('settings.templates.index')->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(ReportTemplate $template)
    {
        if ($template->is_active && ReportTemplate::where('type', $template->type)->count() > 1) {
             return back()->with('error', 'Tidak bisa menghapus template yang sedang aktif.');
        }
        $template->delete();
        return back()->with('success', 'Template dihapus.');
    }

    public function activate(ReportTemplate $template)
    {
        // Deactivate others of same type
        ReportTemplate::where('type', $template->type)->update(['is_active' => false]);
        
        $template->update(['is_active' => true]);
        
        return back()->with('success', 'Template ' . $template->name . ' diaktifkan.');
    }

    public function preview(Request $request)
    {
        $content = $request->input('content');
        
        // --- PREPARE DATA ---
        // --- PREPARE DATA FROM DB ---
        // Reuse ReportController logic partially
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
        $student = null;
        if ($activeYear) {
             // Try to find a student with grades in active year
             $student = \App\Models\Siswa::whereHas('nilai_siswa', function($q) use ($activeYear) {
                 $q->whereHas('periode', function($q2) use ($activeYear){
                     $q2->where('id_tahun_ajaran', $activeYear->id);
                 });
             })->with(['anggota_kelas.kelas', 'nilai_siswa'])->first();
        }
        
        // Fallback if no student with grades
        if (!$student) {
            $student = \App\Models\Siswa::with('anggota_kelas.kelas')->first();
        }

        // Determine Jenjang from Student/Class if found
        $jenjang = 'MI';
        if ($student) {
             $activeClassMember = $student->anggota_kelas->filter(function($ak) use ($activeYear) {
                 return $ak->kelas->id_tahun_ajaran == $activeYear->id;
             })->first();
             $kelasObj = $activeClassMember ? $activeClassMember->kelas : ($student->anggota_kelas->first()->kelas ?? null);
             if ($kelasObj) {
                 $jenjang = $kelasObj->jenjang->kode ?? 'MI';
             }
        }

        $school = \App\Models\IdentitasSekolah::where('jenjang', $jenjang)->first() ?? \App\Models\IdentitasSekolah::first();
        
        // Initialize placeholders (Empty or warning)
        $namaSiswa = '[SISWA TIDAK DITEMUKAN]';
        $nis = '-';
        $nisn = '-';
        $kelas = '-';
        $alamat = '-';
        $namaSekolah = '[DATA SEKOLAH BELUM DIISI]';
        $kepalaSekolah = '-';
        $tglRapor = date('d F Y');
        
        // Stats
        $totalNilai = 0;
        $rataRata = 0;
        $rank = '-';
        $totalSiswa = 0;
        
        // Real Grades for Loop
        $realGrades = [];

        if ($student && $activeYear) {
            $namaSiswa = strtoupper($student->nama_lengkap);
            // ... (rest logic check in next step, assumes student exists)
            $nis = $student->nis;
            $nisn = $student->nisn;
            $alamat = $student->alamat;
            
            // Get Class info
            $activeClassMember = $student->anggota_kelas->filter(function($ak) use ($activeYear) {
                 return $ak->kelas->id_tahun_ajaran == $activeYear->id;
            })->first();
            
            $kelasObj = $activeClassMember ? $activeClassMember->kelas : ($student->anggota_kelas->first()->kelas ?? null);
            $kelas = $kelasObj ? $kelasObj->nama_kelas : '-';
            $totalSiswa = $kelasObj ? $kelasObj->anggota_kelas()->count() : 0;
            
            // Get Grades
            $activePeriod = \App\Models\Periode::where('id_tahun_ajaran', $activeYear->id)->where('status','aktif')->first();
            if ($activePeriod) {
                $grades = \App\Models\NilaiSiswa::where('id_siswa', $student->id)
                    ->where('id_periode', $activePeriod->id)
                    ->with('mapel')
                    ->get();
                
                $totalNilai = $grades->sum('nilai_akhir');
                $count = $grades->count();
                $rataRata = $count > 0 ? round($totalNilai / $count, 1) : 0;
                
                // Prepare Data for Loop
                $no = 1;
                foreach($grades as $g) {
                    $realGrades[] = [
                        'no' => $no++,
                        'mapel' => $g->mapel->nama_mapel ?? 'Mapel Unknown',
                        'kkm' => 75, // Fetch from DB/Settings in real impl
                        'nilai' => $g->nilai_akhir,
                        'predikat' => $g->predikat
                    ];
                }
            }
        }
        
        if ($school) {
            $namaSekolah = strtoupper($school->nama_sekolah);
            $kepalaSekolah = $school->kepala_sekolah ?? '-';
            $tglRapor = ($school->kota ?? 'Kota') . ', ' . date('d F Y');
        }

        // Use ONLY Real Grades or Empty Array (No Dummy Fallback)
        // If empty, the loop will just produce empty content or regex default behavior
        $loopGrades = $realGrades;


        // Initialize Default Empty/Fallback Variables
        $htmlNilai = '<p style="color:red; font-style:italic;">[Data Nilai Tidak Ditemukan]</p>';
        $htmlEkskul = '-';
        $htmlPrestasi = '-';
        $htmlKepribadian = '-';
        $htmlAbsensi = '-';
        $note = '-';
        $decisionText = '-';

        if ($student && $activeYear && isset($activePeriod)) {
             // 1. Fetch Class
             $class = $kelasObj; // Already fetched above
             
             // 2. Fetch Mapel Groups
             $mapelGroups = \App\Models\PengajarMapel::with('mapel')
                ->where('id_kelas', $class->id)
                ->get()
                ->sortBy(function($item) {
                    return ($item->mapel->kategori ?? 'Z') . '#' . $item->mapel->nama_mapel;
                })
                ->groupBy('mapel.kategori')
                ->sortKeys();

             // 3. Fetch Grades (Cumulative - Simulating Single Period Preview)
             // For preview, we use the fetched grades as "Cumulative" for the active period
             // Structure: $cumulativeGrades[mapel_id][periode_id]
             $cumulativeGrades = [];
             foreach($grades as $g) {
                 $cumulativeGrades[$g->id_mapel][$activePeriod->id] = $g;
             }
             
             // 4. Other Data
             $allPeriods = collect([$activePeriod]); // Mock collection for partials
             $periodSlots = collect([$activePeriod->nama_periode]);
             
             // KKM
            $globalKkm = \App\Models\GlobalSetting::val('kkm_default', 70); 
            $kkmMapels = \Illuminate\Support\Facades\DB::table('kkm_mapel')
                ->where('id_tahun_ajaran', $activeYear->id)
                ->pluck('nilai_kkm', 'id_mapel');
             
             // Remarks & Attendance (Simplified for Preview)
             $remarks = collect([]); // Leaving empty for preview or fetch if needed
             $attendance = collect([]); 
             $ekskuls = collect([]);
             
             // Fetch Real Ekskul
             $realEkskuls = \Illuminate\Support\Facades\DB::table('nilai_ekskul')
                ->join('ekstrakurikuler', 'nilai_ekskul.id_ekskul', '=', 'ekstrakurikuler.id')
                ->where('nilai_ekskul.id_siswa', $student->id)
                ->where('nilai_ekskul.id_periode', $activePeriod->id)
                ->select('ekstrakurikuler.nama_ekskul', 'nilai_ekskul.predikat as nilai', 'nilai_ekskul.keterangan', 'nilai_ekskul.id_periode')
                ->get()
                ->groupBy('id_periode');
             
             if ($realEkskuls->isNotEmpty()) {
                 $ekskuls = $realEkskuls;
             }

             // Decision Text (Mock or Real)
             $statusNaik = true; // Default
             $decisionText = "Naik ke Kelas Berikutnya (Preview)";
             
             // Mock Stats for View
             $stats = [
                 $activePeriod->id => [
                     'total' => $totalNilai,
                     'average' => $rataRata,
                     'rank' => $rank,
                     'count' => $totalSiswa
                 ]
             ];

             // RENDER PARTIALS
             $dataForPartials = compact(
                'student', 'class', 'activeYear', 'allPeriods', 'activePeriod', 'periodSlots',
                'mapelGroups', 'cumulativeGrades', 'attendance', 'remarks', 'ekskuls', 'school',
                'stats', 'totalSiswa', 'kkmMapels', 'globalKkm', 'statusNaik', 'decisionText'
             );
             
             // We need to catch View Errors if partials fail due to missing data
             try {
                 $htmlNilai = view('reports.partials.academic_table', $dataForPartials)->render();
                 $htmlEkskul = view('reports.partials.ekskul_table', $dataForPartials)->render();
                 $htmlPrestasi = view('reports.partials.prestasi_table', $dataForPartials)->render();
                 $htmlKepribadian = view('reports.partials.personality_table', $dataForPartials)->render();
                 $htmlAbsensi = view('reports.partials.attendance_table', $dataForPartials)->render();
             } catch (\Exception $e) {
                 $htmlNilai = "Error generating preview table: " . $e->getMessage();
             }
        }

        $vars = [
            '[[NAMA_SISWA]]' => $namaSiswa,
            '[[NIS]]' => $nis,
            '[[NISN]]' => $nisn,
            '[[KELAS]]' => $kelas,
            '[[SEMESTER]]' => 'Ganjil/Genap',
            '[[TAHUN_AJARAN]]' => $activeYear->nama_tahun ?? date('Y'),
            '[[NAMA_SEKOLAH]]' => $namaSekolah,
            '[[KEPALA_SEKOLAH]]' => $kepalaSekolah,
            '[[WALI_KELAS]]' => 'Guru Wali Kelas',
            '[[TANGGAL_RAPOR]]' => $tglRapor,
            '[[CATATAN_WALI]]' => '-',
            '[[STATUS_KENAIKAN]]' => 'Naik ke Kelas Berikutnya',
            '[[ALAMAT_SISWA]]' => $alamat ?? '-',
            '[[JUMLAH_NILAI]]' => $totalNilai,
            '[[RATA_RATA]]' => $rataRata,
            '[[PERINGKAT]]' => $rank,
            '[[TOTAL_SISWA]]' => $totalSiswa,
            
            // REAL GENERATED TABLES
            '[[TABEL_NILAI]]' => $htmlNilai,
            '[[TABEL_EKSKUL]]' => $htmlEkskul,
            '[[TABEL_PRESTASI]]' => $htmlPrestasi,
            '[[TABEL_KEPRIBADIAN]]' => $htmlKepribadian,
            '[[TABEL_KETIDAKHADIRAN]]' => $htmlAbsensi,
        ];

        // --- 1. HANDLE LOOPS Custom (Prioritas sebelum replace biasa) ---
        // LOOP NILAI
        if (preg_match_all('/\[\[LOOP_NILAI_START\]\](.*?)\[\[LOOP_NILAI_END\]\]/s', $content, $matches)) {
            
            // USE REAL/DUMMY GRADES PREPARED ABOVE
            $gradesToUse = $loopGrades;

            foreach ($matches[1] as $index => $templateBlock) {
                $compiledRows = '';
                foreach ($gradesToUse as $grade) {
                    $row = $templateBlock;
                    $row = str_replace('[[NO]]', $grade['no'], $row);
                    $row = str_replace('[[MAPEL]]', $grade['mapel'], $row);
                    $row = str_replace('[[KKM]]', $grade['kkm'], $row);
                    $row = str_replace('[[NILAI]]', $grade['nilai'], $row);
                    $row = str_replace('[[PREDIKAT]]', $grade['predikat'], $row);
                    $compiledRows .= $row;
                }
                $content = str_replace($matches[0][$index], $compiledRows, $content);
            }
        }

        // --- 2. STANDARD REPLACEMENTS ---
        // Replace Placeholders
        foreach ($vars as $key => $val) {
            $content = str_replace($key, $val, $content);
        }

        // Mock Template Object to pass to view
        $template = new ReportTemplate();
        $template->content = $content;
        $template->margins = $request->input('margins');
        $template->orientation = $request->input('orientation');
        
        // Pass dummy variable to view to render it
        // We reuse reports.custom_print which expects $htmlContent
        return view('reports.custom_print', [
            'content' => $content, // Corrected variable name
            'template' => $template
        ]);
    }
    
    public function updateSettings(Request $request)
    {
        $limit = $request->has('rapor_use_custom_template') ? 1 : 0;
        
        \App\Models\GlobalSetting::updateOrCreate(
            ['key' => 'rapor_use_custom_template'],
            ['value' => $limit]
        );
        
        return back()->with('success', 'Pengaturan template diperbarui.');
    }
}
