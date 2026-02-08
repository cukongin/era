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

    public function preview_OLD(Request $request)
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
    
    public function loadPreset(Request $request)
    {
        $preset = $request->query('preset');
        $type = $request->query('type', 'rapor');
        
        $content = '';
        
        switch ($preset) {
            case 'kemenag_mi':
                $content = $this->getPresetKemenagMI($type);
                break;
            case 'diknas_smp':
                $content = $this->getPresetDiknasSMP($type);
                break;
            case 'simple':
                $content = $this->getPresetSimple($type);
                break;
            case 'transcript_simple':
                $content = $this->getPresetTranscriptSimple();
                break;
            default:
                $content = '<p>Template Kosong</p>';
        }
        
        return response()->json(['content' => $content]);
    }

    private function getPresetKemenagMI($type) {
        if ($type == 'cover') {
            return '<div style="text-align: center;">
    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Logo_Kementerian_Agama_Republik_Indonesia_baru.png/1200px-Logo_Kementerian_Agama_Republik_Indonesia_baru.png" style="width: 120px; height: auto;">
    <h2 style="margin-top: 20px;">LAPORAN HASIL BELAJAR</h2>
    <h3>MADRASAH IBTIDAIYAH (MI)</h3>
    <br><br><br>
    <p>NAMA PESERTA DIDIK:</p>
    <div style="border: 1px solid #000; padding: 10px; display: inline-block; min-width: 300px;">[[NAMA_SISWA]]</div>
    <br><br>
    <p>NIS / NISN:</p>
    <div style="border: 1px solid #000; padding: 10px; display: inline-block; min-width: 300px;">[[NIS]] / [[NISN]]</div>
    <br><br><br><br>
    <h3>KEMENTERIAN AGAMA REPUBLIK INDONESIA</h3>
</div>';
        }
        
        return '<h3 style="text-align: center;">CAPAIAN HASIL BELAJAR</h3>
<table border="0" style="width: 100%; margin-bottom: 20px;">
    <tr><td width="20%">Nama</td><td width="2%">:</td><td width="40%">[[NAMA_SISWA]]</td><td width="15%">Kelas</td><td width="2%">:</td><td>[[KELAS]]</td></tr>
    <tr><td>NIS / NISN</td><td>:</td><td>[[NIS]] / [[NISN]]</td><td>Semester</td><td>:</td><td>[[SEMESTER]]</td></tr>
    <tr><td>Nama Madrasah</td><td>:</td><td>[[NAMA_SEKOLAH]]</td><td>Tahun Pelajaran</td><td>:</td><td>[[TAHUN_AJARAN]]</td></tr>
</table>

<h4>A. SIKAP</h4>
<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <tr style="background-color: #f0f0f0;"><th width="10%">No</th><th width="30%">Aspek</th><th>Deskripsi</th></tr>
    [[TABEL_KEPRIBADIAN]]
</table>

<h4>B. PENGETAHUAN DAN KETERAMPILAN</h4>
[[TABEL_NILAI]]

<h4>C. EKSTRAKURIKULER</h4>
<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <tr style="background-color: #f0f0f0;"><th width="10%">No</th><th width="30%">Kegiatan</th><th>Nilai</th><th>Keterangan</th></tr>
    [[TABEL_EKSKUL]]
</table>

<h4>D. PRESTASI</h4>
<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <tr style="background-color: #f0f0f0;"><th width="10%">No</th><th width="30%">Jenis Prestasi</th><th>Keterangan</th></tr>
    [[TABEL_PRESTASI]]
</table>

<h4>E. KETIDAKHADIRAN</h4>
<table border="1" cellpadding="5" cellspacing="0" style="width: 50%; border-collapse: collapse;">
    [[TABEL_KETIDAKHADIRAN]]
</table>

<h4>F. CATATAN WALI KELAS</h4>
<div style="border: 1px solid #000; padding: 10px; min-height: 50px;">[[CATATAN_WALI]]</div>

<h4>G. TANGGAPAN ORANG TUA/WALI</h4>
<div style="border: 1px solid #000; padding: 10px; min-height: 50px;">&nbsp;</div>

<h4>H. KEPUTUSAN</h4>
<div style="border: 1px solid #000; padding: 10px; margin-top: 10px; font-weight: bold;">
    Berdasarkan hasil yang dicapai pada semester ini, peserta didik ditetapkan:<br>
    [[STATUS_KENAIKAN]]
</div>

<br>
<table border="0" style="width: 100%;">
    <tr>
        <td width="30%" align="center">Mengetahui,<br>Orang Tua/Wali<br><br><br><br>..........................</td>
        <td width="40%"></td>
        <td width="30%" align="center">[[TANGGAL_RAPOR]]<br>Wali Kelas<br><br><br><br><b>[[WALI_KELAS]]</b></td>
    </tr>
    <tr>
        <td colspan="3" align="center">Mengetahui,<br>Kepala Madrasah<br><br><br><br><b>[[KEPALA_SEKOLAH]]</b></td>
    </tr>
</table>';
    }

    private function getPresetDiknasSMP($type) {
         if ($type == 'cover') return $this->getPresetKemenagMI('cover'); // Reuse for now
         
         return '<h3 style="text-align: center;">LAPORAN HASIL BELAJAR PESERTA DIDIK</h3>
<br>
<table border="0" style="width: 100%;">
    <tr><td width="150">Nama Sekolah</td><td>: [[NAMA_SEKOLAH]]</td><td width="150">Kelas</td><td>: [[KELAS]]</td></tr>
    <tr><td>Alamat</td><td>: [[ALAMAT_SISWA]]</td><td>Semester</td><td>: [[SEMESTER]]</td></tr>
    <tr><td>Nama Peserta Didik</td><td>: <b>[[NAMA_SISWA]]</b></td><td>Tahun Pelajaran</td><td>: [[TAHUN_AJARAN]]</td></tr>
    <tr><td>No Induk/NISN</td><td>: [[NIS]] / [[NISN]]</td><td></td><td></td></tr>
</table>
<br><hr><br>

<h4>A. SIKAP SPIRITUAL DAN SOSIAL</h4>
[[TABEL_KEPRIBADIAN]]

<h4>B. PENGETAHUAN DAN KETERAMPILAN</h4>
<p>Kriteria Ketuntasan Minimal (KKM): 75</p>
[[TABEL_NILAI]]

<h4>C. EKSTRAKURIKULER</h4>
[[TABEL_EKSKUL]]

<h4>D. KETIDAKHADIRAN</h4>
<table border="1" cellpadding="5" cellspacing="0" style="width: 300px; border-collapse: collapse;">
    [[TABEL_KETIDAKHADIRAN]]
</table>

<br>
<div style="float: right; width: 300px;">
    [[TANGGAL_RAPOR]]<br>
    Wali Kelas,<br><br><br><br>
    <b>[[WALI_KELAS]]</b>
</div>
<div style="clear: both;"></div>';
    }

    private function getPresetSimple($type) {
        if ($type == 'cover') return '<h1 style="text-align: center; margin-top: 100px;">RAPOR SISWA</h1><h2 style="text-align: center;">[[NAMA_SISWA]]</h2>';
        
        return '<h2 style="text-align: center;">RAPOR SEDERHANA</h2>
<p>Nama: [[NAMA_SISWA]] | Kelas: [[KELAS]]</p>
<hr>
<h3>Nilai Akademik</h3>
[[TABEL_NILAI]]
<br>
<h3>Catatan</h3>
[[CATATAN_WALI]]';
    }

    private function getPresetTranscriptSimple() {
        return '<div style="text-align: center;">
    <h3>TRANSKRIP NILAI AKHIR</h3>
    <h4>TAHUN PELAJARAN [[TAHUN_AJARAN]]</h4>
</div>
<br>
<table border="0" style="width: 100%; margin-bottom: 20px;">
    <tr><td width="20%">Nama</td><td>: [[NAMA_SISWA]]</td></tr>
    <tr><td>NIS / NISN</td><td>: [[NIS]] / [[NISN]]</td></tr>
    <tr><td>Kelas Terakhir</td><td>: [[KELAS]]</td></tr>
</table>

<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <tr style="background-color: #eee;">
        <th width="5%">No</th>
        <th>Mata Pelajaran</th>
        <th width="15%">Nilai Ujian</th>
        <th width="15%">Nilai Rata Rapor</th>
        <th width="15%">Nilai Akhir</th>
    </tr>
    [[LOOP_NILAI_START]]
    <tr>
        <td align="center">[[NO]]</td>
        <td>[[MAPEL]]</td>
        <td align="center">[[NILAI]]</td>
        <td align="center">-</td>
        <td align="center">[[NILAI]]</td>
    </tr>
    [[LOOP_NILAI_END]]
</table>
<br>
<p>Keterangan: LULUS / TIDAK LULUS</p>
<br>
<table border="0" style="width: 100%;">
    <tr>
        <td width="70%"></td>
        <td align="center">
            [[TANGGAL_RAPOR]]<br>
            Kepala Sekolah,<br><br><br>
            <b>[[KEPALA_SEKOLAH]]</b>
        </td>
    </tr>
</table>';
    }

    public function preview(Request $request)
    {
        // Debugging Blank Page
        // dd($request->all());
        
        $content = html_entity_decode($request->input('content')); // Decode entities (CKEditor Safe)
        $type = $request->input('type', 'rapor');
        
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
                        'predikat' => $g->predikat,
                        'rata_rapor' => $g->nilai_akhir, // Mock for preview
                        'nilai_ujian' => $g->nilai_akhir // Mock for preview
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
                    $row = str_replace('[[NILAI_RAPOR]]', $grade['rata_rapor'] ?? '-', $row);
                    $row = str_replace('[[NILAI_UJIAN]]', $grade['nilai_ujian'] ?? '-', $row);
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
