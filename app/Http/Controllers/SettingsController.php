<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\BobotPenilaian;
use App\Models\IdentitasSekolah;
use App\Models\KkmMapel;
use App\Models\Mapel;
use App\Models\NilaiSiswa;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class SettingsController extends Controller
{
    // Round 2: Safety Lock Helper (Safe Version)
    private function checkActiveYear() 
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) return true; // Setup mode
        
        // 1. Check Global Switch
        $allowEdit = \App\Models\GlobalSetting::val('allow_edit_past_data', 0);
        if ($allowEdit) return true;

        // 2. Check if Current Year is Latest
        $latestYear = TahunAjaran::orderBy('id', 'desc')->first();
        if ($latestYear && $activeYear->id === $latestYear->id) {
            return true; // Latest year is always editable
        }

        // If Old Year & Lock is ON -> Block
        return false;
    }

    public function index()
    {
        // Emergency: Clear View Cache on load to prevent 500 Errors after updates
        // This is necessary because Blade changes often cause stale cache on production
        try {
            \Illuminate\Support\Facades\Artisan::call('view:clear');
        } catch (\Exception $e) {
            // Ignore if permission denied, but log it
            \Illuminate\Support\Facades\Log::error('Failed to clear view cache: ' . $e->getMessage());
        }

        // 1. Academic Year Data
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $archivedYears = TahunAjaran::where('status', 'non-aktif')->orderBy('nama', 'desc')->get();

        // 3. Grading Weights (Init first to get defaults)
        $jenjang = request('jenjang', 'MI'); // Default MI

        // 2. Grading Period Data (Active Year & Jenjang)
        $periods = [];
        if ($activeYear) {
            $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
                ->where('lingkup_jenjang', $jenjang) // Filter by selected Jenjang
                ->orderBy('nama_periode') // Or custom order
                ->get();
        }

        // 3. Grading Weights Data (Active Year Only)
        $bobotMI = new BobotPenilaian(['bobot_harian' => 50, 'bobot_uts_cawu' => 50]); // Default
        $bobotMTS = new BobotPenilaian(['bobot_harian' => 30, 'bobot_uts_cawu' => 30, 'bobot_uas' => 40]); // Default

        if ($activeYear) {
            $bobotMI = BobotPenilaian::firstOrCreate(
                ['id_tahun_ajaran' => $activeYear->id, 'jenjang' => 'MI'],
                ['bobot_harian' => 50, 'bobot_uts_cawu' => 50, 'bobot_uas' => 0]
            );
            $bobotMTS = BobotPenilaian::firstOrCreate(
                ['id_tahun_ajaran' => $activeYear->id, 'jenjang' => 'MTS'],
                ['bobot_harian' => 30, 'bobot_uts_cawu' => 30, 'bobot_uas' => 40]
            );
        }

        // 4. KKM Data
        $mapels = Mapel::orderBy('nama_mapel')->get();
        $kkms = [];
        if ($activeYear) {
            $rawKkms = KkmMapel::where('id_tahun_ajaran', $activeYear->id)->get();
            foreach ($rawKkms as $k) {
                // Key: ID_MAPEL-JENJANG (e.g., "1-MI", "1-MTS")
                $kkms[$k->id_mapel . '-' . $k->jenjang_target] = $k;
            }
        }

        // 5. Grading Whitelist (Exceptions)
        $whitelist = \Illuminate\Support\Facades\DB::table('grading_whitelist')
            ->join('users', 'grading_whitelist.id_guru', '=', 'users.id')
            ->join('periode', 'grading_whitelist.id_periode', '=', 'periode.id')
            ->select('grading_whitelist.*', 'users.name as guru_name', 'periode.nama_periode')
            ->orderBy('grading_whitelist.created_at', 'desc')
            ->orderBy('grading_whitelist.created_at', 'desc')
            ->get();

        // 6. Teachers
        $teachers = \App\Models\User::where('role', 'teacher')->orderBy('name')->get();

        // 7. Calculate Locked Status
        $latestYear = TahunAjaran::orderBy('id', 'desc')->first();
        $isLocked = false;
        if ($activeYear && $latestYear && $activeYear->id !== $latestYear->id) {
             if (!\App\Models\GlobalSetting::val('allow_edit_past_data', 0)) {
                 $isLocked = true;
             }
        }

        // 8. Grading Data for Standard Form (Simplification)
        // $jenjang is already defined above
        $predicates = [];
        $gradingSettings = [];
        $activeBobot = null;

        if ($activeYear) {
            $predicates = \App\Models\PredikatNilai::where('id_tahun_ajaran', $activeYear->id)
                ->where('jenjang', $jenjang)
                ->orderBy('grade') // A, B, C, D
                ->get();
            
            // If empty, generate defaults for View
            // If empty, generate defaults for View from User Request (A-D)
            if ($predicates->isEmpty()) {
                $defaults = [
                    ['grade' => 'A', 'min' => 90, 'max' => 100, 'desk' => 'Sangat Baik'],
                    ['grade' => 'B', 'min' => 80, 'max' => 89, 'desk' => 'Baik'],
                    ['grade' => 'C', 'min' => 70, 'max' => 79, 'desk' => 'Cukup'],
                    ['grade' => 'D', 'min' => 0,  'max' => 69,  'desk' => 'Kurang'],
                ];
                foreach ($defaults as $d) {
                    $predicates[] = (object) [
                        'grade' => $d['grade'], 
                        'min_score' => $d['min'], 
                        'max_score' => $d['max'], 
                        'deskripsi' => $d['desk']
                    ];
                }
            }
            
            // Fetch Global Settings relevant to Grading
            $gradingSettings = \App\Models\GlobalSetting::whereIn('key', [
                'kkm_default', 'rounding_enable', 'promotion_max_kkm_failure', 
                'promotion_min_attendance', 'promotion_min_attitude', 'total_effective_days', 'scale_type', 'promotion_requires_all_periods',
                // Rapor Dates
                'titimangsa_mi', 'titimangsa_mts',
                'titimangsa_tempat_mi', 'titimangsa_tempat_mts',
                'titimangsa_2_mi', 'titimangsa_2_mts',
                // Transkrip Dates
                'titimangsa_transkrip_mi', 'titimangsa_transkrip_mts',
                'titimangsa_transkrip_tempat_mi', 'titimangsa_transkrip_tempat_mts',
                'titimangsa_transkrip_2_mi', 'titimangsa_transkrip_2_mts'
            ])->pluck('value', 'key')->toArray();

            $activeBobot = $jenjang == 'MI' ? $bobotMI : $bobotMTS;
            
            // Fetch School Identity for this Jenjang
            $school = \App\Models\IdentitasSekolah::where('jenjang', $jenjang)->firstOrNew(['jenjang' => $jenjang]);
        }

        // 9. Backups (For Backup Tab)
        $backupPath = 'backups';
        if (!\Illuminate\Support\Facades\Storage::exists($backupPath)) {
            \Illuminate\Support\Facades\Storage::makeDirectory($backupPath);
        }
        $files = \Illuminate\Support\Facades\Storage::files($backupPath);
        $backups = [];
        foreach ($files as $file) {
            $backups[] = (object) [
                'filename' => basename($file),
                'size' => round(\Illuminate\Support\Facades\Storage::size($file) / 1024, 2) . ' KB',
                'created_at' => \Carbon\Carbon::createFromTimestamp(\Illuminate\Support\Facades\Storage::lastModified($file)),
            ];
        }
        // Sort by newest
        usort($backups, function($a, $b) {
        return $b->created_at <=> $a->created_at;
        });

        // Fix Undefined Variable
        $school = \App\Models\IdentitasSekolah::first();

        return view('settings.index', compact(
            'activeYear', 
            'archivedYears', 
            'periods', 
            'bobotMI', 
            'bobotMTS', 
            'mapels', 
            'kkms',
            'whitelist',
            'teachers',
            'isLocked',
            // New Data for Standard Form
            'jenjang',
            'predicates',
            'gradingSettings',
            'activeBobot',
            'school', // Pass school identity
            'backups' // Pass backups
        ));
    }

    // --- Academic Year Logic ---

    public function storeYear(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:20|unique:tahun_ajaran,nama'
        ]);

        // Deactivate all existing
        TahunAjaran::query()->update(['status' => 'non-aktif']);

        // Create new
        $year = TahunAjaran::create([
            'nama' => $request->nama,
            'status' => 'aktif'
        ]);

        // Auto-seed Periods for this year
        // MI: Cawu 1, 2, 3
        Periode::create(['id_tahun_ajaran' => $year->id, 'nama_periode' => 'Cawu 1', 'lingkup_jenjang' => 'MI', 'status' => 'aktif', 'tipe' => 'CAWU']);
        Periode::create(['id_tahun_ajaran' => $year->id, 'nama_periode' => 'Cawu 2', 'lingkup_jenjang' => 'MI', 'status' => 'tutup', 'tipe' => 'CAWU']);
        Periode::create(['id_tahun_ajaran' => $year->id, 'nama_periode' => 'Cawu 3', 'lingkup_jenjang' => 'MI', 'status' => 'tutup', 'tipe' => 'CAWU']);
        
        // MTs: Ganjil, Genap
        Periode::create(['id_tahun_ajaran' => $year->id, 'nama_periode' => 'Semester Ganjil', 'lingkup_jenjang' => 'MTS', 'status' => 'aktif', 'tipe' => 'SEMESTER']);
        Periode::create(['id_tahun_ajaran' => $year->id, 'nama_periode' => 'Semester Genap', 'lingkup_jenjang' => 'MTS', 'status' => 'tutup', 'tipe' => 'SEMESTER']);

        // Auto-seed Predicates (A, B, C, D)
        $this->seedDefaultPredicates($year->id);

        return back()->with('success', 'Tahun Ajaran baru berhasil dibuat, periode & predikat otomatis digenerate.');
    }

    private function seedDefaultPredicates($yearId) 
    {
        $defaults = [
            ['grade' => 'A', 'min' => 90, 'max' => 100, 'deskripsi' => 'Sangat Baik'],
            ['grade' => 'B', 'min' => 80, 'max' => 89, 'deskripsi' => 'Baik'],
            ['grade' => 'C', 'min' => 70, 'max' => 79, 'deskripsi' => 'Cukup'],
            ['grade' => 'D', 'min' => 0,  'max' => 69,  'deskripsi' => 'Kurang'],
        ];
    
        $jenjangs = ['MI', 'MTS'];
    
        foreach ($jenjangs as $jenjang) {
            foreach ($defaults as $d) {
                \App\Models\PredikatNilai::create([
                    'id_tahun_ajaran' => $yearId,
                    'jenjang' => $jenjang,
                    'grade' => $d['grade'],
                    'min_score' => $d['min'],
                    'max_score' => $d['max'],
                    'deskripsi' => $d['deskripsi']
                ]);
            }
        }
    }

    public function toggleYear($id)
    {
        // Activate target, deactivate others
        TahunAjaran::query()->update(['status' => 'non-aktif']);
        TahunAjaran::where('id', $id)->update(['status' => 'aktif']);
        
        return back()->with('success', 'Tahun ajaran aktif berhasil diganti.');
    }

    public function regeneratePeriods($id)
    {
        $year = TahunAjaran::findOrFail($id);

        // Define expected periods
        $expected = [
            ['nama_periode' => 'Cawu 1', 'lingkup_jenjang' => 'MI', 'status' => 'aktif', 'tipe' => 'CAWU'],
            ['nama_periode' => 'Cawu 2', 'lingkup_jenjang' => 'MI', 'status' => 'tutup', 'tipe' => 'CAWU'],
            ['nama_periode' => 'Cawu 3', 'lingkup_jenjang' => 'MI', 'status' => 'tutup', 'tipe' => 'CAWU'],
            ['nama_periode' => 'Semester Ganjil', 'lingkup_jenjang' => 'MTS', 'status' => 'aktif', 'tipe' => 'SEMESTER'],
            ['nama_periode' => 'Semester Genap', 'lingkup_jenjang' => 'MTS', 'status' => 'tutup', 'tipe' => 'SEMESTER'],
        ];

        $count = 0;
        foreach ($expected as $p) {
            $exists = Periode::where('id_tahun_ajaran', $year->id)
                ->where('nama_periode', $p['nama_periode'])
                ->exists();
            
            if (!$exists) {
                Periode::create([
                    'id_tahun_ajaran' => $year->id,
                    'nama_periode' => $p['nama_periode'],
                    'lingkup_jenjang' => $p['lingkup_jenjang'],
                    'status' => $p['status'], // Default status if creating new
                    'tipe' => $p['tipe'],
                ]);
                $count++;
            }
        }

        return back()->with('success', "Berhasil memperbaiki data! $count periode yang hilang telah ditambahkan.");
    }

    public function destroyYear($id)
    {
        $year = TahunAjaran::findOrFail($id);

        if ($year->status == 'aktif') {
            return back()->with('error', 'Tidak bisa menghapus Tahun Ajaran yang sedang AKTIF. Silakan aktifkan tahun lain terlebih dahulu.');
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 1. Delete associated data (Cascading manually to be safe)
            // Delete Periods
            Periode::where('id_tahun_ajaran', $id)->delete();
            
            // Delete Classes (and their relations: Anggota Kelas, Pengajar Mapel)
            $classIds = \App\Models\Kelas::where('id_tahun_ajaran', $id)->pluck('id');
            if ($classIds->count() > 0) {
                \DB::table('anggota_kelas')->whereIn('id_kelas', $classIds)->delete();
                \DB::table('pengajar_mapel')->whereIn('id_kelas', $classIds)->delete();
                \DB::table('promotion_decisions')->whereIn('id_kelas', $classIds)->delete();
                \App\Models\Kelas::whereIn('id', $classIds)->delete();
            }

            // Delete Grades related to this year (via Periods? Or via Year ID if column exists?)
            // NilaiSiswa has 'id_periode', which we just deleted. 
            // Better to delete NilaiSiswa where id_periode in (deleted periods).
            // But since periods are soft deletes? No, likely hard delete.
            // Let's assume hard delete for cleanup.
            
            // Delete KKM Mapel
            KkmMapel::where('id_tahun_ajaran', $id)->delete();
            
            // Delete Bobot Penilaian
            BobotPenilaian::where('id_tahun_ajaran', $id)->delete();

            // Finally Delete Year
            $year->delete();

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Tahun Ajaran "' . $year->nama . '" berhasil dihapus beserta seluruh datanya.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // --- App Identity Logic ---


    public function updateGeneral(Request $request)
    {
        // Handle "Allow Edit Past Data" Toggle
        // Check for specific marker hidden input
        if ($request->has('safety_lock_marker')) {
            $val = $request->has('allow_edit_past_data') ? 1 : 0;
            \App\Models\GlobalSetting::updateOrCreate(
                ['key' => 'allow_edit_past_data'], 
                ['value' => $val]
            );
            $status = $val ? 'DIBUKA (UNLOCKED)' : 'DIKUNCI (LOCKED)';
            return back()->with('success', "Mode Edit Data Tahun Lalu: $status");
        }
        return back();
    }

    // --- Grading Logic ---

    public function storeWeights(Request $request)
    {
        if (!$this->checkActiveYear()) {
             return back()->with('error', '⚠️ AKSES DITOLAK: Periode terkunci. Buka kunci di menu Umum untuk mengedit.');
        }

        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        BobotPenilaian::updateOrCreate(
            ['id_tahun_ajaran' => $activeYear->id, 'jenjang' => $request->jenjang],
            [
                'bobot_harian' => $request->bobot_harian ?? 0,
                'bobot_uts_cawu' => $request->bobot_uts_cawu ?? 0,
                'bobot_uas' => $request->bobot_uas ?? 0
            ]
        );

        return back()->with('success', 'Bobot penilaian untuk jenjang ' . $request->jenjang . ' berhasil disimpan.');
    }

    public function storeGradingRules(Request $request)
    {
        if (!$this->checkActiveYear()) {
             return back()->with('error', '⚠️ AKSES DITOLAK: Periode terkunci.');
        }

        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        $jenjang = $request->jenjang; // MI or MTS

        // 1. Save Weights
        BobotPenilaian::updateOrCreate(
            ['id_tahun_ajaran' => $activeYear->id, 'jenjang' => $jenjang],
            [
                'bobot_harian' => $request->bobot_harian ?? 0,
                'bobot_uts_cawu' => $request->bobot_uts_cawu ?? 0,
                'bobot_uas' => $request->bobot_uas ?? 0
            ]
        );

        // 2. Save Predicates (Recreate)
        // Verify input structure
        if ($request->has('predikat') && is_array($request->predikat)) {
            \App\Models\PredikatNilai::where('id_tahun_ajaran', $activeYear->id)
                ->where('jenjang', $jenjang)
                ->delete();

            foreach ($request->predikat as $grade => $data) {
                \App\Models\PredikatNilai::create([
                    'id_tahun_ajaran' => $activeYear->id,
                    'jenjang' => $jenjang,
                    'grade' => $grade,
                    'min_score' => $data['min'] ?? 0,
                    'max_score' => $data['max'] ?? 0,
                    'deskripsi' => $data['deskripsi'] ?? ''
                ]);
            }
        }

        // 3. Save Global Settings
        $settingsToSave = [
            'kkm_default', 'rounding_enable', 'promotion_max_kkm_failure', 
            'promotion_min_attendance', 'promotion_min_attitude', 'total_effective_days', 'scale_type', 'promotion_requires_all_periods',
            'titimangsa_mi', 'titimangsa_mts',
            'titimangsa_2_mi', 'titimangsa_2_mts',
            'titimangsa_tempat_mi', 'titimangsa_tempat_mts',
            // Transkrip Dates
            'titimangsa_transkrip_mi', 'titimangsa_transkrip_mts',
            'titimangsa_transkrip_tempat_mi', 'titimangsa_transkrip_tempat_mts',
            'titimangsa_transkrip_2_mi', 'titimangsa_transkrip_2_mts',
            
            'final_grade_mi', 'final_grade_mts',
            'ijazah_range_mi', 'ijazah_range_mts', 'ijazah_range_ma'
        ];

        foreach ($settingsToSave as $key) {
            // Special handling for checkboxes: If missing, set to 0
            if (in_array($key, ['rounding_enable', 'promotion_requires_all_periods'])) {
                $value = $request->has($key) ? $request->input($key) : 0;
            } else {
                // For other inputs, only update if present to avoid overwriting with null
                if (!$request->has($key)) continue;
                $value = $request->input($key);
            }

            \App\Models\GlobalSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // CRITICAL FIX: Also update the 'grading_settings' table which is used by WaliKelasController
        // Map generic keys to table columns
        $gradingTableUpdate = [
            'updated_at' => now()
        ];
        
        if ($request->has('promotion_min_attendance')) $gradingTableUpdate['promotion_min_attendance'] = $request->promotion_min_attendance;
        if ($request->has('promotion_min_attitude')) $gradingTableUpdate['promotion_min_attitude'] = $request->promotion_min_attitude;
        if ($request->has('promotion_max_kkm_failure')) $gradingTableUpdate['promotion_max_kkm_failure'] = $request->promotion_max_kkm_failure;
        if ($request->has('promotion_requires_all_periods')) $gradingTableUpdate['promotion_requires_all_periods'] = $request->has('promotion_requires_all_periods') ? 1 : 0; // Checkbox
        if ($request->has('total_effective_days')) $gradingTableUpdate['effective_days_year'] = $request->total_effective_days; // Column name mapping

        \Illuminate\Support\Facades\DB::table('grading_settings')->updateOrInsert(
            ['jenjang' => $jenjang],
            $gradingTableUpdate
        );


        return redirect()->route('settings.index', ['tab' => 'grading', 'jenjang' => $jenjang])->with('success', "Konfigurasi Penilaian ($jenjang) berhasil disimpan!");
    }


    public function updatePeriod(Request $request, $id)
    {
        if (!$this->checkActiveYear()) {
             return response()->json(['message' => '⚠️ AKSES DITOLAK: Periode terkunci.'], 403);
        }

        $periode = Periode::findOrFail($id);
        
        $data = $request->validate([
            'status' => 'nullable|in:aktif,tutup',
            'end_date' => 'nullable' // Allow string or null
        ]);

        // 1. Handle Status Change
        if (isset($data['status'])) {
            // If activating, close others in same group
            if ($data['status'] == 'aktif' && $periode->status != 'aktif') {
                Periode::where('id_tahun_ajaran', $periode->id_tahun_ajaran)
                    ->where('lingkup_jenjang', $periode->lingkup_jenjang)
                    ->update(['status' => 'tutup']);
            }
            $periode->status = $data['status'];
        }

        // 2. Handle Deadline Change (Explicitly)
        if (array_key_exists('end_date', $data)) {
            // Convert empty string to null, otherwise carbon parse valid date
            $periode->end_date = empty($data['end_date']) ? null : $data['end_date'];
        }

        $periode->save();

        return response()->json([
            'message' => 'Berhasil disimpan',
            'period' => $periode
        ]);
    }

    public function togglePeriod($id)
    {
        if (!$this->checkActiveYear()) {
             return back()->with('error', '⚠️ AKSES DITOLAK: Periode terkunci.');
        }

        // Legacy toggle support if needed, or redirect to internal logic
        $periode = Periode::findOrFail($id);
        if ($periode->status == 'tutup') {
             Periode::where('id_tahun_ajaran', $periode->id_tahun_ajaran)
                ->where('lingkup_jenjang', $periode->lingkup_jenjang)
                ->update(['status' => 'tutup']);
             $periode->update(['status' => 'aktif']);
        } else {
             $periode->update(['status' => 'tutup']);
        }
        return redirect()->route('settings.index', ['tab' => 'grading', 'jenjang' => $periode->lingkup_jenjang])->with('success', 'Status periode diperbarui.');
    }

    public function storeKkm(Request $request)
    {
        if (!$this->checkActiveYear()) {
             return back()->with('error', '⚠️ AKSES DITOLAK: Periode terkunci.');
        }

        // DEBUG: Cek data yang dikirim dari form
        // dd($request->all());
        
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();

        foreach ($request->kkm as $mapelId => $jenjangData) {
            foreach ($jenjangData as $jenjang => $nilai) {
                if ($nilai) {
                    KkmMapel::updateOrCreate(
                        [
                            'id_tahun_ajaran' => $activeYear->id,
                            'id_mapel' => $mapelId,
                            'jenjang_target' => $jenjang
                        ],
                        ['nilai_kkm' => $nilai]
                    );
                }
            }
        }

        return back()->with('success', 'KKM berhasil diperbarui.');
    }

    // --- User Management Logic ---

    public function users(Request $request)
    {
        $query = \App\Models\User::with(['data_guru', 'data_siswa', 'wali_kelas_aktif']);

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        // 5. Data for User Management Tabs
        // Fetch Teachers who don't have an account yet
        $teachersWithoutAccount = \App\Models\DataGuru::whereDoesntHave('user')->get();
        
        // Fetch Permissions
        $permissions = \App\Models\GlobalSetting::all()->keyBy('key');

        return view('settings.users', compact('users', 'teachersWithoutAccount', 'permissions'));
    }

    public function updatePermissions(Request $request)
    {
        // Save all permission settings
        $settings = $request->except(['_token']);
        foreach ($settings as $key => $value) {
            \App\Models\GlobalSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        
        return back()->with('success', 'Pengaturan hak akses berhasil diperbarui.');
    }

    public function syncTeacherAccount(Request $request)
    {
        $teacherId = $request->teacher_id;
        $teacher = \App\Models\DataGuru::findOrFail($teacherId);

        if ($teacher->id_user) {
            return back()->with('error', 'Guru ini sudah punya akun.');
        }

        // Create User
        $user = \App\Models\User::create([
            'name' => $teacher->nama,
            'email' => $teacher->nip ? $teacher->nip.'@nurulainy.sch.id' : 'guru'.$teacher->id.'@nurulainy.sch.id',
            'password' => \Illuminate\Support\Facades\Hash::make('guru123'), // Default password
            'role' => 'teacher'
        ]);

        // Link
        $teacher->update(['id_user' => $user->id]);

        return back()->with('success', 'Akun guru berhasil dibuat dan disinkronkan.');
    }

    public function generateUserAccount($id)
    {
        $user = \App\Models\User::with(['data_guru', 'data_siswa'])->findOrFail($id);

        // Determine Identifier
        $identifier = 'user'.$user->id;
        if ($user->role == 'teacher' && $user->data_guru) {
            $identifier = $user->data_guru->nip ?? $user->data_guru->nuptk ?? $identifier;
        } elseif ($user->role == 'student' && $user->data_siswa) {
            $identifier = $user->data_siswa->nis_lokal ?? $user->data_siswa->nisn ?? $identifier;
        }

        // Clean identifier
        $identifier = preg_replace('/[^a-zA-Z0-9]/', '', $identifier);
        
        $email = $identifier . '@nurulainy.sch.id';
        $plainPassword = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT); 

        $user->update([
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($plainPassword),
        ]);

        return back()->with('generated_credential', [
            'name' => $user->name,
            'email' => $email,
            'password' => $plainPassword,
            'role' => $user->role
        ])->with('success', 'Akun berhasil digenerate ulang.');
    }

    public function bulkDestroyUsers(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        $ids = $request->ids;
        // Prevent deleting self
        if (in_array(auth()->id(), $ids)) {
             return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        \App\Models\User::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' User berhasil dihapus.');
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,teacher,student,staff_tu'
        ]);

        $user = \App\Models\User::findOrFail($id);
        
        // Prevent demoting yourself if you are the only admin? (Optional safety)
        if ($user->id == auth()->id() && $request->role != 'admin') {
             return back()->with('error', 'Anda tidak bisa menurunkan hak akses diri sendiri saat sedang login.');
        }

        $user->role = $request->role;
        $user->save();

        return back()->with('success', "Hak akses user {$user->name} berhasil diubah menjadi " . ucfirst($request->role));
    }

    public function impersonate($id)
    {
        // Security Check: Only allow if current user is Admin
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized action.');
        }

        $targetUser = \App\Models\User::findOrFail($id);
        
        // Prevent impersonating yourself
        if ($targetUser->id == auth()->id()) {
            return back()->with('error', 'Anda tidak bisa impersonate diri sendiri.');
        }

        // Store original admin ID to allow "Switch Back" later
        session(['impersonator_id' => auth()->id()]);

        // Login as target
        auth()->login($targetUser);

        // Redirect based on role
        if ($targetUser->role == 'teacher') {
            return redirect()->route('dashboard'); // Or teacher dashboard
        } elseif ($targetUser->role == 'student') {
            return redirect()->route('dashboard'); // Or student dashboard
        }

        return redirect()->route('dashboard')->with('success', "Login ajaib sebagai {$targetUser->name} berhasil!");
    }

    public function stopImpersonating()
    {
        if (session()->has('impersonator_id')) {
            $originalAdminId = session('impersonator_id');
            session()->forget('impersonator_id');
            
            $admin = \App\Models\User::findOrFail($originalAdminId);
            auth()->login($admin);
            
            return redirect()->route('settings.users.index')->with('success', 'Selamat datang kembali, Admin!');
        }
        
        return redirect()->route('dashboard');
    }

    public function massGenerateAndExport(Request $request)
    {
        // 1. Validate Role (Safety)
        if (!$request->role) {
            return back()->with('error', 'Silakan pilih Role (Guru/Siswa) terlebih dahulu untuk Export.');
        }

        $role = $request->role;
        $users = \App\Models\User::where('role', $role)->with(['data_guru', 'data_siswa'])->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada user dengan role tersebut.');
        }

        // 2. Prepare CSV Header
        $csvFileName = "akun_{$role}_" . date('Y-m-d_H-i') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // 3. Callback for Stream Download
        $callback = function() use ($users, $role) {
            $file = fopen('php://output', 'w');
            
            // Header Row
            fputcsv($file, ['No', 'Nama Lengkap', 'Role', 'Email Login', 'Password Baru']);

            foreach ($users as $index => $user) {
                // Generate Credential
                $identifier = 'user'.$user->id;
                if ($user->role == 'teacher' && $user->data_guru) {
                    $identifier = $user->data_guru->nip ?? $user->data_guru->nuptk ?? $identifier;
                } elseif ($user->role == 'student' && $user->data_siswa) {
                    $identifier = $user->data_siswa->nis_lokal ?? $user->data_siswa->nisn ?? $identifier;
                }

                $identifier = preg_replace('/[^a-zA-Z0-9]/', '', $identifier);
                
                // Ensure unique email slightly if collision? (assuming ID/NIP unique enough)
                $email = $identifier . '@nurulainy.sch.id';
                $plainPassword = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT); 

                // Update DB
                $user->update([
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make($plainPassword),
                ]);

                // Write to CSV
                fputcsv($file, [
                    $index + 1,
                    $user->name,
                    ucfirst($role),
                    $email,
                    $plainPassword // EXPOSING PASSWORD HERE
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    // --- Consolidated Grading Rules API (JSON) ---

    public function getGradingRules($jenjang)
    {
        try {
            // SELF-HEALING: Ensure Table and Columns Exist (Copied from GradingRuleController)
            if (Schema::hasTable('grading_settings')) {
                if (!Schema::hasColumn('grading_settings', 'effective_days_year')) {
                    Schema::table('grading_settings', function (Blueprint $table) {
                        $table->integer('effective_days_year')->default(200);
                    });
                }
                if (!Schema::hasColumn('grading_settings', 'promotion_requires_all_periods')) {
                    Schema::table('grading_settings', function (Blueprint $table) {
                        $table->boolean('promotion_requires_all_periods')->default(true);
                    });
                }
            } else {
                // Create Table if missing (Panic Mode)
                Schema::create('grading_settings', function (Blueprint $table) {
                    $table->id();
                    $table->enum('jenjang', ['MI', 'MTS'])->unique();
                    $table->integer('kkm_default')->default(70);
                    $table->string('scale_type')->default('0-100');
                    $table->boolean('rounding_enable')->default(true);
                    $table->integer('promotion_max_kkm_failure')->default(3);
                    $table->integer('promotion_min_attendance')->default(85);
                    $table->integer('effective_days_year')->default(200);
                    $table->string('promotion_min_attitude')->default('B');
                    $table->boolean('promotion_requires_all_periods')->default(true);
                    $table->timestamps();
                });
                // Seed
                \Illuminate\Support\Facades\DB::table('grading_settings')->insert([
                    ['jenjang' => 'MI'], ['jenjang' => 'MTS']
                ]);
            }

            $activeYear = TahunAjaran::where('status', 'aktif')->first();
            if (!$activeYear) return response()->json(['error' => 'No active year'], 400);

        // 1. Weights fallback (READ ONLY - NO CREATE)
        $weights = BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang', $jenjang)
            ->first();

        if (!$weights) {
            $weights = (object)[
                'bobot_harian' => 0, // Default 0 (Safe)
                'bobot_uts_cawu' => 0,
                'bobot_uas' => 0
            ];
        }

        // 2. Predicates Fallback (READ ONLY - NO CREATE)
        $predicates = \App\Models\PredikatNilai::where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang', $jenjang)
            ->orderBy('grade')
            ->get();
            
        if ($predicates->isEmpty()) {
            // Return Standard Defaults (Memory Only)
            $predicates = collect([
                (object)['id'=>null, 'grade'=>'A', 'min_score'=>90, 'max_score'=>100, 'deskripsi'=>'Sangat Baik'],
                (object)['id'=>null, 'grade'=>'B', 'min_score'=>80, 'max_score'=>89, 'deskripsi'=>'Baik'],
                (object)['id'=>null, 'grade'=>'C', 'min_score'=>70, 'max_score'=>79, 'deskripsi'=>'Cukup'],
                (object)['id'=>null, 'grade'=>'D', 'min_score'=>0,  'max_score'=>69, 'deskripsi'=>'Kurang'],
            ]);
        }

        // 3. Settings Construction
        // FORCE DB QUERY
        $dbSettings = \Illuminate\Support\Facades\DB::table('grading_settings')
            ->where('jenjang', $jenjang)
            ->first();

        // Manual Mapping - NO SHORTCUTS
        $cleanSettings = [];
        
        // Scale Type
        $cleanSettings['scale_type'] = $dbSettings ? ($dbSettings->scale_type ?? \App\Models\GlobalSetting::val('scale_type', '0-100')) : \App\Models\GlobalSetting::val('scale_type', '0-100');
        
        // KKM
        $cleanSettings['kkm_default'] = intval($dbSettings ? ($dbSettings->kkm_default ?? \App\Models\GlobalSetting::val('kkm_default', 70)) : \App\Models\GlobalSetting::val('kkm_default', 70));
        
        // Rounding
        $cleanSettings['rounding_enable'] = (bool)($dbSettings ? ($dbSettings->rounding_enable ?? \App\Models\GlobalSetting::val('rounding_enable', 1)) : \App\Models\GlobalSetting::val('rounding_enable', 1));
        
        // Promotion
        $cleanSettings['promotion_max_kkm_failure'] = intval($dbSettings ? ($dbSettings->promotion_max_kkm_failure ?? \App\Models\GlobalSetting::val('promotion_max_kkm_failure', 3)) : \App\Models\GlobalSetting::val('promotion_max_kkm_failure', 3));
        $cleanSettings['promotion_min_attendance'] = intval($dbSettings ? ($dbSettings->promotion_min_attendance ?? \App\Models\GlobalSetting::val('promotion_min_attendance', 85)) : \App\Models\GlobalSetting::val('promotion_min_attendance', 85));
        $cleanSettings['promotion_min_attitude'] = $dbSettings ? ($dbSettings->promotion_min_attitude ?? \App\Models\GlobalSetting::val('promotion_min_attitude', 'B')) : \App\Models\GlobalSetting::val('promotion_min_attitude', 'B');
        $cleanSettings['promotion_requires_all_periods'] = (bool)($dbSettings ? ($dbSettings->promotion_requires_all_periods ?? \App\Models\GlobalSetting::val('promotion_requires_all_periods', 1)) : \App\Models\GlobalSetting::val('promotion_requires_all_periods', 1));
        
        // Effective Days (The Trouble Maker)
        // MUST map effective_days_year (DB) -> to -> total_effective_days (JSON)
        $cleanSettings['total_effective_days'] = intval($dbSettings ? ($dbSettings->effective_days_year ?? \App\Models\GlobalSetting::val('total_effective_days', 220)) : \App\Models\GlobalSetting::val('total_effective_days', 220));



        return response()->json([
            'weights' => $weights,
            'predicates' => $predicates,
            'settings' => $cleanSettings
        ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data: ' . $e->getMessage()], 500);
        }
    }

    public function updateGradingRules(Request $request)
    {
        try {
            if (!$this->checkActiveYear()) {
                 return response()->json(['message' => '⚠️ AKSES DITOLAK: Periode terkunci.'], 403);
            }

            $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
            $jenjang = $request->jenjang;

            // 1. Update Weights
            if ($request->has('weights')) {
                BobotPenilaian::updateOrCreate(
                    ['id_tahun_ajaran' => $activeYear->id, 'jenjang' => $jenjang],
                    [
                        'bobot_harian' => $request->weights['bobot_harian'],
                        'bobot_uts_cawu' => $request->weights['bobot_uts_cawu'],
                        'bobot_uas' => $request->weights['bobot_uas'] ?? 0
                    ]
                );
            }

            // 2. Update Predicates
            if ($request->has('predicates')) {
                foreach ($request->predicates as $p) {
                    \App\Models\PredikatNilai::where('id', $p['id'])->update([
                        'min_score' => $p['min_score'],
                        'max_score' => $p['max_score']
                    ]);
                }
            }

            // 3. Update Settings (Per Jenjang in grading_settings TABLE)
            if ($request->has('settings')) {
                $s = $request->settings;

                
                // Map JSON keys to DB columns
                $updateData = [];
                if (isset($s['kkm_default'])) $updateData['kkm_default'] = $s['kkm_default'];
                if (isset($s['scale_type'])) $updateData['scale_type'] = $s['scale_type'];
                if (isset($s['rounding_enable'])) $updateData['rounding_enable'] = $s['rounding_enable'];
                
                // Promotion Rules
                if (isset($s['promotion_max_kkm_failure'])) $updateData['promotion_max_kkm_failure'] = $s['promotion_max_kkm_failure'];
                if (isset($s['promotion_min_attendance'])) $updateData['promotion_min_attendance'] = $s['promotion_min_attendance'];
                if (isset($s['promotion_min_attendance'])) $updateData['promotion_min_attendance'] = $s['promotion_min_attendance'];
                if (isset($s['promotion_min_attitude'])) $updateData['promotion_min_attitude'] = $s['promotion_min_attitude'];
                if (isset($s['promotion_requires_all_periods'])) $updateData['promotion_requires_all_periods'] = $s['promotion_requires_all_periods'];
                
                // Effective Days (Recently added column)
                if (isset($s['total_effective_days'])) {
                    // Also support legacy key or root level key if mapped
                    $updateData['effective_days_year'] = $s['total_effective_days'];
                }

                // Perform Update on grading_settings table
                if (!empty($updateData)) {
                    $updateData['updated_at'] = now();
                    
                    // Update specific Jenjang
                    \Illuminate\Support\Facades\DB::table('grading_settings')->updateOrInsert(
                        ['jenjang' => $jenjang],
                        $updateData
                    );
                }

                // Also keep GlobalSettings in sync for legacy read (Backwards Compatibility)
                // But prefer per-jenjang.
                foreach ($s as $key => $val) {
                     // Update Global Setting as fallback
                     \App\Models\GlobalSetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $val]
                    );

                    // Setup KKM Mapel if kkm_default changes
                    if ($key === 'kkm_default') {
                        // Update Mapel KKM for this Jenjang ONLY if kkm_default CHANGED significantly?
                        // Or blindly update. For now blindly update to ensure sync.
                        
                        $mapels = \App\Models\Mapel::whereIn('target_jenjang', [$jenjang, 'SEMUA'])->pluck('id');
                        foreach($mapels as $mapelId) {
                            \App\Models\KkmMapel::updateOrCreate(
                                [
                                    'id_tahun_ajaran' => $activeYear->id,
                                    'id_mapel' => $mapelId,
                                    'jenjang_target' => $jenjang 
                                ],
                                ['nilai_kkm' => $val]
                            );
                        }
                    }
                }
            }
                
            // Explicitly force save total_effective_days (JALUR SUPER) - Handled above in grading_settings
            // We also update Global for consistency
            if ($request->has('total_effective_days')) {
                $dayVal = $request->total_effective_days;
                \Illuminate\Support\Facades\DB::table('global_settings')->updateOrInsert(
                    ['key' => 'total_effective_days'],
                    ['value' => $dayVal, 'updated_at' => now()]
                );
                
                // Ensure grading_settings is updated if not covered by 'settings' array above
                if (\Illuminate\Support\Facades\Schema::hasColumn('grading_settings', 'effective_days_year')) {
                      \Illuminate\Support\Facades\DB::table('grading_settings')
                        ->where('jenjang', $jenjang)
                        ->update(['effective_days_year' => $dayVal, 'updated_at' => now()]);
                }
            }

            // 4. Update Titimangsa (Rapor & Transkrip) - Dynamic
            // Matches: titimangsa_mi, titimangsa_mts, titimangsa_2_mi, titimangsa_2_mts
            // AND titimangsa_transkrip_mi, titimangsa_transkrip_2_mi, etc.
            foreach ($request->all() as $key => $val) {
                if (str_starts_with($key, 'titimangsa_')) {
                    \App\Models\GlobalSetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $val]
                    );
                }
            }

            $msg = "Pengaturan berhasil disimpan!";
            return response()->json(['message' => $msg]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal Simpan: ' . $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }






    public function recalculateGrades(Request $request) {
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        $jenjang = $request->jenjang; // MI or MTS

        $bobot = BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang', $jenjang)
            ->first();
        
        if (!$bobot) return response()->json(['message' => 'Bobot belum diatur.'], 400);

        $rules = \App\Models\PredikatNilai::where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang', $jenjang)
            ->orderBy('min_score', 'desc')
            ->get();
        
        $settings = \App\Models\GlobalSetting::all()->pluck('value', 'key');
        $shouldRound = isset($settings['rounding_enable']) ? (bool)$settings['rounding_enable'] : true;
        $kkmDefault = $settings['kkm_default'] ?? 70;

        $count = 0;

        // Process in chunks to prevent memory issues
        // Filter grades by Period that matches Jenjang
        $periodIds = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', $jenjang)
            ->pluck('id');
            
        NilaiSiswa::whereIn('id_periode', $periodIds)
            ->chunk(100, function ($grades) use ($bobot, $rules, $shouldRound, $kkmDefault, &$count) {
                foreach ($grades as $grade) {
                    // Calculate using Standardized Helper (Supports Zero Harian Fallback)
                    // Logic Logic: (Score * Weight) / TotalWeight
                    
                    $bH = $bobot->bobot_harian;
                    $bT = $bobot->bobot_uts_cawu;
                    $bA = $bobot->bobot_uas;
                    $totalWeight = $bH + $bT + $bA;

                    $h = $grade->nilai_harian ?? 0;
                    $u = $grade->nilai_uts_cawu ?? 0;
                    $a = $grade->nilai_uas ?? 0;

                    $score = 0;
                    if ($totalWeight > 0) {
                         $weightedSum = ($h * $bH) + ($u * $bT) + ($a * $bA);
                         $score = $weightedSum / $totalWeight;
                    }

                    if ($shouldRound) $score = round($score);
                    else $score = round($score, 2);

                    // Predicate
                    $pred = 'D';
                    foreach ($rules as $r) {
                        if ($score >= $r->min_score) {
                            $pred = $r->grade;
                            break;
                        }
                    }

                    // Update
                    if ($grade->nilai_akhir != $score || $grade->predikat != $pred) {
                        $grade->nilai_akhir = $score;
                        $grade->predikat = $pred;
                        $grade->save(); 
                        $count++;
                    }
                }
            });

        return response()->json(['message' => "Berhasil menghitung ulang $count data nilai siswa ($jenjang)."]);
    }

    public function updateApplication()
    {
        // Security: Admin Only
        if (auth()->user()->role !== 'admin') abort(403);
        
        $log = "";

        try {
            // Use Symfony Process (Safe wrapper for proc_open)
            // exec() is disabled on Hostinger, but proc_open is active.
            
            // 1. Git Pull
            $process = \Symfony\Component\Process\Process::fromShellCommandline('git pull origin master');
            $process->setWorkingDirectory(base_path()); // Ensure we are in project root
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Symfony\Component\Process\Exception\ProcessFailedException($process);
            }
            
            $log .= "Git Output:\n" . $process->getOutput() . "\n";
            
            // 2. Clear Caches (Internal Artisan Call - Safe)
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            
            $log .= "Cache Cleared Successfully.\n";

            // 3. Run Migrations (Safe Mode)
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $log .= "Migrate Output: " . \Illuminate\Support\Facades\Artisan::output() . "\n";
            } catch (\Exception $migErr) {
                $log .= "Migrate Skipped/Error: " . $migErr->getMessage() . "\n";
            }

            // 4. Run Specific Seeders (If needed)
            // Formulas
            // 4. Run Specific Seeders (If needed)
            // Formulas (REMOVED)
            // if (\App\Models\GradingFormula::count() === 0) { ... }

            return back()->with('success', "Update Berhasil! Sistem via Git (Proc Open).\nLog:\n" . $log);

        } catch (\Throwable $e) {
             // Create a detailed error log
             $errorLog = "Error: " . $e->getMessage();
             if (method_exists($e, 'getProcess')) {
                 $errorLog .= "\nCommand Output: " . $e->getProcess()->getErrorOutput();
             }
             
             return back()->with('error', "Update Gagal (System Error):\n" . $errorLog);
        }
    }
    public function updateIdentity(Request $request)
    {
        // 1. App Info (Global Settings Only)
        if ($request->has('app_name')) \App\Models\GlobalSetting::set('app_name', $request->app_name);
        if ($request->has('app_tagline')) \App\Models\GlobalSetting::set('app_tagline', $request->app_tagline);
        
        // 2. Headmasters (Sync GlobalSetting AND IdentitasSekolah Table)
        
        // 2. Headmasters & School Identity (Sync GlobalSetting AND IdentitasSekolah Table)
        
        // --- MI ---
        if ($request->has('hm_name_mi')) {
            \App\Models\GlobalSetting::set('hm_name_mi', $request->hm_name_mi);
            
            $dataMi = ['kepala_madrasah' => $request->hm_name_mi];
            if ($request->has('hm_nip_mi')) {
                 \App\Models\GlobalSetting::set('hm_nip_mi', $request->hm_nip_mi);
                 $dataMi['nip_kepala_madrasah'] = $request->hm_nip_mi;
            }
            // Identity Fields MI
            if ($request->has('nama_sekolah_mi')) $dataMi['nama_sekolah'] = $request->nama_sekolah_mi;
            if ($request->has('nsm_mi')) $dataMi['nsm'] = $request->nsm_mi;
            if ($request->has('npsn_mi')) $dataMi['npsn'] = $request->npsn_mi;
            if ($request->has('alamat_mi')) $dataMi['alamat'] = $request->alamat_mi;
            if ($request->has('kabupaten_mi')) $dataMi['kabupaten'] = $request->kabupaten_mi; // For Titimangsa

            IdentitasSekolah::updateOrCreate(['jenjang' => 'MI'], $dataMi);
        }

        // --- MTs ---
        if ($request->has('hm_name_mts')) {
            \App\Models\GlobalSetting::set('hm_name_mts', $request->hm_name_mts);
            
            $dataMts = ['kepala_madrasah' => $request->hm_name_mts];
            if ($request->has('hm_nip_mts')) {
                 \App\Models\GlobalSetting::set('hm_nip_mts', $request->hm_nip_mts);
                 $dataMts['nip_kepala_madrasah'] = $request->hm_nip_mts;
            }
            // Identity Fields MTs
            if ($request->has('nama_sekolah_mts')) $dataMts['nama_sekolah'] = $request->nama_sekolah_mts;
            if ($request->has('nsm_mts')) $dataMts['nsm'] = $request->nsm_mts;
            if ($request->has('npsn_mts')) $dataMts['npsn'] = $request->npsn_mts;
            if ($request->has('alamat_mts')) $dataMts['alamat'] = $request->alamat_mts;
             if ($request->has('kabupaten_mts')) $dataMts['kabupaten'] = $request->kabupaten_mts;

            IdentitasSekolah::updateOrCreate(['jenjang' => 'MTS'], $dataMts);
        }
        


        // 3. Logo Upload (Sync All)
        if ($request->hasFile('app_logo')) {
            $file = $request->file('app_logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename); // Fixed path: public/uploads (no double public)
            
            $logoPath = 'uploads/' . $filename;
            \App\Models\GlobalSetting::set('app_logo', $logoPath);

            // Update All Identitas Sekolah rows with this logo
            IdentitasSekolah::query()->update(['logo' => $logoPath]);
        }

        return back()->with('success', 'Identitas Sekolah & Aplikasi berhasil diperbarui (Disinkronkan ke Database Rapor).');
    }
}
