<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Jenjang;
use App\Models\TahunAjaran;
use App\Models\Mapel;
use App\Models\PengajarMapel;
use App\Models\User;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
        
        $query = Kelas::with(['jenjang', 'tahun_ajaran', 'wali_kelas'])
            ->withCount(['anggota_kelas', 'pengajar_mapel']) // Eager load count for both
            ->where('id_tahun_ajaran', $activeYear->id ?? 0);

        // Filter by Jenjang
        if ($request->has('id_jenjang') && $request->id_jenjang != '') {
            $query->where('id_jenjang', $request->id_jenjang);
        }

        // Filter by Search (Name or Wali Kelas)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhereHas('wali_kelas', function($q_wali) use ($search) {
                      $q_wali->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $classes = $query->orderBy('id_jenjang')
            ->orderBy('tingkat_kelas')
            ->orderBy('nama_kelas')
            ->get();
            
        // Correct Stats for Tabs (Active Year Only)
        $levels = Jenjang::all();
        $stats = [
            'total_classes' => Kelas::where('id_tahun_ajaran', $activeYear->id ?? 0)->count(),
        ];
        foreach($levels as $lvl) {
            $stats['jenjang_' . $lvl->id] = Kelas::where('id_tahun_ajaran', $activeYear->id ?? 0)
                ->where('id_jenjang', $lvl->id)
                ->count();
        }
            
        $academicYears = TahunAjaran::where('status', 'aktif')->get();
        $teachers = User::where('role', 'teacher')->get();

        // Get Teachers who are ALREADY Wali Kelas in THIS Active Year
        $takenTeachers = [];
        if ($activeYear) {
            $takenTeachers = Kelas::where('id_tahun_ajaran', $activeYear->id)
                ->whereNotNull('id_wali_kelas')
                ->pluck('id_wali_kelas')
                ->toArray();
        }

        return view('classes.index', compact('classes', 'levels', 'academicYears', 'teachers', 'stats', 'takenTeachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required',
            'id_jenjang' => 'required',
            'tingkat_kelas' => 'required',
            'id_tahun_ajaran' => 'required',
        ]);

        // Validation: One Teacher One Class (Active Year)
        if ($request->id_wali_kelas) {
            $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
            if ($activeYear) {
                $conflict = Kelas::where('id_tahun_ajaran', $activeYear->id)
                    ->where('id_wali_kelas', $request->id_wali_kelas)
                    ->first();
                
                if ($conflict) {
                    return back()->with('error', "Guru ini sudah menjadi Wali Kelas di kelas lain (Kelas {$conflict->nama_kelas}).");
                }
            }
        }

        Kelas::create($request->all());

        return back()->with('success', 'Kelas berhasil dibuat');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas' => 'required',
            'id_jenjang' => 'required',
            'tingkat_kelas' => 'required',
            'id_wali_kelas' => 'nullable|exists:users,id',
        ]);

        $kelas = Kelas::findOrFail($id);

        // Validation: One Teacher One Class (Same Year)
        // Only specific check if the Wali Kelas is CHANGED.
        // If they keep the same duplicate wali (legacy data), let them save other fields logic.
        if ($request->id_wali_kelas && $request->id_wali_kelas != $kelas->id_wali_kelas) {
            // Check existence in SAME Year, excluding THIS class
            $conflict = Kelas::where('id_tahun_ajaran', $kelas->id_tahun_ajaran)
                ->where('id_wali_kelas', $request->id_wali_kelas)
                ->where('id', '!=', $id)
                ->first();
            
            if ($conflict) {
                return back()->with('error', "Gagal disimpan: Guru ini sudah menjadi Wali Kelas di tempat lain (Kelas {$conflict->nama_kelas}). Silakan kosongkan atau ganti.");
            }
        }

        $kelas->update($request->all());

        return back()->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        
        // Security check: Don't delete if students exist? Or soft delete? 
        // User has "Reset Button" for mass delete, but single delete is nice too.
        // For now just standard delete, assuming FKs handles restriction or cascade.
        try {
            $kelas->delete();
            return back()->with('success', 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus kelas (mungkin masih ada siswanya?)');
        }
    }

    public function show($id)
    {
        $class = Kelas::with(['jenjang', 'tahun_ajaran', 'wali_kelas', 'pengajar_mapel.guru', 'pengajar_mapel.mapel'])
            ->withCount('anggota_kelas')
            ->findOrFail($id);
        
        // Filter Mapel berdasarkan Jenjang Kelas + Mapel Umum (SEMUA)
        $jenjangKelas = $class->jenjang->kode; // MI atau MTS
        $subjects = Mapel::whereIn('target_jenjang', [$jenjangKelas, 'SEMUA'])->orderBy('nama_mapel')->get();
        
        $teachers = User::where('role', 'teacher')->get();

        // Hitung Kesiapan
        $totalMapel = $class->pengajar_mapel->count();
        $mapelTerisi = $class->pengajar_mapel->whereNotNull('id_guru')->count();
        $readiness = $totalMapel > 0 ? round(($mapelTerisi / $totalMapel) * 100) : 0;

        // Get Enrolled Students (For initial load)
        $enrolledStudentsRaw = $class->anggota_kelas()->with('siswa')->get();
        $enrolledStudents = $enrolledStudentsRaw->map(function($m){
            // Safety check in case student is deleted
            if (!$m->siswa) return null;
            return [
                'id' => $m->siswa->id,
                'nama_lengkap' => $m->siswa->nama_lengkap,
                'nis' => $m->siswa->nis_lokal,
                'initial' => substr($m->siswa->nama_lengkap, 0, 1)
            ];
        })->filter()->values();

        return view('classes.show', compact('class', 'subjects', 'teachers', 'readiness', 'enrolledStudents'));
    }

    public function getCandidates(Request $request, $classId)
    {
        try {
            $class = Kelas::findOrFail($classId);
            $search = $request->search;
            $activeYearId = $class->id_tahun_ajaran;

            // Get students already in a class for this year
            $bookedStudentIds = \App\Models\AnggotaKelas::whereHas('kelas', function($q) use ($activeYearId) {
                $q->where('id_tahun_ajaran', $activeYearId);
            })->pluck('id_siswa');

            $query = \App\Models\Siswa::whereNotIn('id', $bookedStudentIds);

            // SPECIAL LOGIC: Grade 7 MTs can take Grade 6 MI Graduates (Status 'lulus')
            // But MUST NOT take Active MI Students (Grade 1-6).
            $isMtsGrade7 = ($class->tingkat_kelas == 7 && optional($class->jenjang)->kode == 'MTS');

            if ($isMtsGrade7) {
                $query->where(function($q) use ($class) {
                    // 1. Alumni / Lulusan ONLY FROM MI
                    $q->where(function($sub) {
                        $sub->where('status_siswa', 'lulus')
                            ->whereHas('jenjang', function($j) {
                                $j->where('kode', 'MI');
                            });
                    });
                    
                    // 2. OR Active Students who are ALREADY MTs (New Registrants)
                    $q->orWhere(function($sub) use ($class) {
                        $sub->where('status_siswa', 'aktif')
                            ->where('id_jenjang', $class->id_jenjang);
                    });
                });
            } else {
                // NORMAL LOGIC: Strict Active & Jenjang
                $query->where('status_siswa', 'aktif');
                if ($class->id_jenjang) {
                    $query->where('id_jenjang', $class->id_jenjang);
                }
            }
            
            // Note: The previous jenjang check block is removed/merged into above logic
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nis_lokal', 'like', "%{$search}%");
                });
            }

            $students = $query->orderBy('nama_lengkap')->limit(50)->get();

            return response()->json($students);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GetCandidates Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat data santri.'], 500);
        }
    }

    public function addStudent(Request $request, $classId)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:siswa,id'
        ]);

        $count = 0;
        foreach ($request->student_ids as $studentId) {
            $exists = \App\Models\AnggotaKelas::where('id_kelas', $classId)
                ->where('id_siswa', $studentId)
                ->exists();
            
            if (!$exists) {
                \App\Models\AnggotaKelas::create([
                    'id_kelas' => $classId,
                    'id_siswa' => $studentId,
                    'status' => 'aktif'
                ]);

                // AUTO-UPDATE: If Jenjang differs or Status was 'lulus', update Student
                $student = \App\Models\Siswa::find($studentId);
                $class = \App\Models\Kelas::find($classId);
                
                if ($student && $class) {
                    $updates = [];
                    // 1. If 'lulus', make 'aktif'
                    if ($student->status_siswa == 'lulus') {
                        $updates['status_siswa'] = 'aktif';
                    }
                    // 2. If MI entering MTs (Jenjang Mismatch), update Jenjang
                    if ($student->id_jenjang != $class->id_jenjang) {
                        $updates['id_jenjang'] = $class->id_jenjang;
                    }
                    
                    if (!empty($updates)) {
                        $student->update($updates);
                    }
                }

                $count++;
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "{$count} Santri berhasil ditambahkan."]);
        }
        return back()->with('success', "{$count} Santri berhasil ditambahkan.");
    }

    public function removeStudent(Request $request, $classId, $studentId)
    {
        \App\Models\AnggotaKelas::where('id_kelas', $classId)
            ->where('id_siswa', $studentId)
            ->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Santri berhasil dikeluarkan.']);
        }
        return back()->with('success', 'Santri berhasil dikeluarkan dari kelas.');
    }

    public function assignSubject(Request $request, $classId)
    {
        $request->validate([
            'id_mapel' => 'required|exists:mapel,id',
            'id_guru' => 'nullable|exists:users,id',
        ]);

        // Cek apakah mapel sudah ada di kelas ini
        $exists = PengajarMapel::where('id_kelas', $classId)
            ->where('id_mapel', $request->id_mapel)
            ->exists();

        if ($exists) {
            return back()->withErrors(['message' => 'Mata pelajaran ini sudah ada di kelas ini.']);
        }

        PengajarMapel::create([
            'id_kelas' => $classId,
            'id_mapel' => $request->id_mapel,
            'id_guru' => $request->id_guru,
        ]);

        return back()->with('success', 'Mata pelajaran berhasil ditambahkan ke kelas.');
    }
    public function updateSubjectTeacher(Request $request, $classId)
    {
        $request->validate([
            'id_mapel' => 'required|exists:mapel,id',
            'id_guru' => 'required|exists:users,id',
        ]);

        $assignment = PengajarMapel::where('id_kelas', $classId)
            ->where('id_mapel', $request->id_mapel)
            ->firstOrFail();

        $assignment->update([
            'id_guru' => $request->id_guru
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Guru pengampu berhasil diperbarui.']);
        }
        return back()->with('success', 'Guru pengampu berhasil diperbarui.');
    }

    public function autoAssignSubjects(Request $request, $classId)
    {
        try {
            $class = Kelas::with('jenjang')->findOrFail($classId);
            $jenjangKode = $class->jenjang->kode; // MI / MTS
            
            // LOGIC BARU: Cari Kelas Referensi (Sibling) yang sudah punya Mapel
            // Syarat: Tahun sama, Jenjang sama, Tingkat sama, tapi bukan kelas ini.
            $referenceClass = Kelas::where('id_tahun_ajaran', $class->id_tahun_ajaran)
                ->where('id_jenjang', $class->id_jenjang)
                ->where('tingkat_kelas', $class->tingkat_kelas)
                ->where('id', '!=', $class->id)
                ->whereHas('pengajar_mapel') // Harus punya mapel
                ->first();

            $mapelsToSync = collect([]);

            if ($referenceClass) {
                // Ambil Mapel dari referensi (Hasil Plotting)
                $refMapelIds = PengajarMapel::where('id_kelas', $referenceClass->id)->pluck('id_mapel');
                $mapelsToSync = Mapel::whereIn('id', $refMapelIds)->get();
                $sourceMsg = "dari Referensi Kelas {$referenceClass->nama_kelas}";
            } else {
                // FALLBACK: Jika tidak ada referensi, ambil Semua Mapel sesuai Jenjang (Logic Lama)
                // Tapi user bilang "jangan ambil semua".
                // Jadi kita coba cari referensi dari tingkat "Lain" tapi jenjang sama? Jangan.
                // Kita tetap fallback ke logic lama TAPI kasih warning/info?
                // Atau, kita batasi hanya kategori "UMUM" dan "AGAMA"?
                // Sesuai request "sitemnya tidak ngambil data dari hasil ploting",
                // Jika tidak ada referensi, mungkin lebih baik KOSONG atau AMBIL SEMUA?
                // Default ke AMBIL SEMUA tapi hanya yang targetnya SPESIFIK jenjang ini (bukan 'SEMUA' wildcard)?
                // Untuk sekarang fallback ke logic lama tapi beri pesan.
                
                $mapelsToSync = Mapel::whereIn('target_jenjang', [$jenjangKode, 'SEMUA'])->get();
                $sourceMsg = "dari Master Mapel (Default)";
            }
            
            if ($mapelsToSync->isEmpty()) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Tidak ada mapel yang cocok.']);
                }
                return back()->with('error', 'Tidak ada mapel yang cocok.');
            }

            $countAdded = 0;
            $targetMapelIds = $mapelsToSync->pluck('id')->toArray();

            // 1. Add Missing Mapels
            foreach ($mapelsToSync as $mapel) {
                $exists = PengajarMapel::where('id_kelas', $class->id)
                    ->where('id_mapel', $mapel->id)
                    ->exists();

                if (!$exists) {
                    PengajarMapel::create([
                        'id_kelas' => $class->id,
                        'id_mapel' => $mapel->id,
                        'id_guru' => null 
                    ]);
                    $countAdded++;
                }
            }

            // 2. Remove Invalid Mapels (Sync Logic)
            $countRemoved = PengajarMapel::where('id_kelas', $class->id)
                ->whereNotIn('id_mapel', $targetMapelIds)
                ->delete();

            $msg = "Sync Selesai ($sourceMsg): $countAdded ditambah, $countRemoved dihapus.";

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return back()->with('success', $msg);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AutoAssign Error: ' . $e->getMessage());
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.'], 500);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function resetSubjects(Request $request, $classId)
    {
        $count = \App\Models\PengajarMapel::where('id_kelas', $classId)->delete();
        return back()->with('success', "Berhasil menghapus $count mata pelajaran dari kelas ini.");
    }

    public function getSourceClasses(Request $request, Kelas $class)
    {
        // 1. Find Previous Year
        $currentYear = $class->tahun_ajaran;
        $prevYear = TahunAjaran::where('id', '!=', $currentYear->id)
            ->where('status', '!=', 'aktif')
            ->orderBy('id', 'desc')
            ->first();

        if (!$prevYear) {
            return response()->json(['error' => 'Tahun ajaran sebelumnya tidak ditemukan.'], 404);
        }

        // 2. Determine Target Grade (Tingkat Kelas - 1)
        $currentGrade = $class->tingkat_kelas;
        $targetGrade = $currentGrade - 1;
        
        // Special Logic: Grade 7 MTs pulls from Grade 6 (Any Jenjang, effectively MI)
        // If current is 7, target is 6.
        // Queries classes in Prev Year with Target Grade.
        
        $sources = Kelas::where('id_tahun_ajaran', $prevYear->id)
            ->where('tingkat_kelas', $targetGrade)
            ->with('jenjang')
            ->orderBy('nama_kelas')
            ->get()
            ->map(function($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->nama_kelas . " (" . $c->jenjang->nama . ")",
                    'count' => $c->anggota_kelas()->count()
                ];
            });

        return response()->json([
            'year' => $prevYear->nama,
            'target_grade' => $targetGrade,
            'sources' => $sources
        ]);
    }

    public function pullStudents(Request $request, Kelas $class)
    {
        $request->validate([
            'source_class_id' => 'required|exists:kelas,id'
        ]);

        $sourceClass = Kelas::findOrFail($request->source_class_id);
        
        // 1. Get Promoted Students from Source Class
        $promotedStudentIds = \Illuminate\Support\Facades\DB::table('promotion_decisions')
            ->where('id_kelas', $sourceClass->id)
            ->whereIn('final_decision', ['promoted', 'graduated']) 
            ->pluck('id_siswa');

        if ($promotedStudentIds->isEmpty()) {
            return response()->json(['message' => 'Tidak ada siswa yang berstatus NAIK KELAS / LULUS di kelas asal.'], 422);
        }

        // 2. Filter already enrolled
        $existingIds = $class->anggota_kelas()->pluck('id_siswa')->toArray();
        $newIds = $promotedStudentIds->diff($existingIds);

        if ($newIds->isEmpty()) {
            return response()->json(['message' => 'Semua siswa yang naik kelas sudah ada di kelas ini.'], 422);
        }

        // 3. Insert
        $count = 0;
        foreach ($newIds as $sid) {
            \App\Models\AnggotaKelas::create([
                'id_kelas' => $class->id,
                'id_siswa' => $sid
            ]);
            $count++;
        }

        return response()->json([
            'message' => "Berhasil menarik $count siswa dari kelas {$sourceClass->nama_kelas}.",
            'count' => $count
        ]);
    }
    public function bulkPromote(Request $request)
    {
        // 1. Setup Context
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->firstOrFail();
        $prevYear = \App\Models\TahunAjaran::where('id', '<', $activeYear->id) // Must be older than active
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$prevYear) return back()->with('error', 'Tahun ajaran sebelumnya tidak ditemukan.');

        $jenjangId = $request->id_jenjang; // Optional filter
        
        // --- STEP 1: RESET / DELETE ONLY ---
        if ($request->has('reset_first') && $request->reset_first == '1') {
            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                $query = Kelas::where('id_tahun_ajaran', $activeYear->id);
                if ($jenjangId) $query->where('id_jenjang', $jenjangId);
                
                $classIds = $query->pluck('id');
                $count = $classIds->count();
                
                if ($classIds->isNotEmpty()) {
                    \Illuminate\Support\Facades\DB::table('anggota_kelas')->whereIn('id_kelas', $classIds)->delete();
                    \Illuminate\Support\Facades\DB::table('pengajar_mapel')->whereIn('id_kelas', $classIds)->delete();
                    \Illuminate\Support\Facades\DB::table('kelas')->whereIn('id', $classIds)->delete();
                }
                \Illuminate\Support\Facades\DB::commit(); 
                
                // FALLTHROUGH to Step 2 (Promote)
                // return back()->with('success', "BERHASIL MENGHAPUS $count KELAS...");
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                return back()->with('error', "Gagal menghapus data lama: " . $e->getMessage());
            }
        }

        // --- STEP 2: PROMOTE ---
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 2. Get Source Classes (Previous Year)
            $query = Kelas::where('id_tahun_ajaran', $prevYear->id);
            if ($jenjangId) {
                $query->where('id_jenjang', $jenjangId);
            }
            $prevClasses = $query->orderBy('tingkat_kelas', 'desc')->get(); 
            
            $countPromoted = 0;
            $countGraduated = 0;

            foreach ($prevClasses as $oldClass) {
                // Determine Logic based on Jenjang
                $jenjangKode = $oldClass->jenjang->kode; // MI / MTS
                $maxGrade = ($jenjangKode == 'MI') ? 6 : 9; 
                
                // --- 1. HANDLE PROMOTION/GRADUATION ---
                $promotedIds = \Illuminate\Support\Facades\DB::table('promotion_decisions')
                    ->where('id_kelas', $oldClass->id)
                    ->whereIn('final_decision', ['promoted', 'graduated']) 
                    ->pluck('id_siswa');
                
                if ($promotedIds->isNotEmpty()) {
                    // Case A: GRADUATION
                    if ($oldClass->tingkat_kelas >= $maxGrade) {
                        \App\Models\Siswa::whereIn('id', $promotedIds)->update(['status_siswa' => 'lulus']);
                        $countGraduated += $promotedIds->count();
                    } 
                    // Case B: PROMOTION (N -> N+1)
                    else {
                        $targetGrade = $oldClass->tingkat_kelas + 1;
                        // Name: "1-A" -> "2-A"
                        $targetName = preg_replace('/^\d+/', $targetGrade, $oldClass->nama_kelas);
                        if ($targetName == $oldClass->nama_kelas) $targetName = $targetGrade . " " . $oldClass->nama_kelas;

                        $targetClass = Kelas::firstOrCreate(
                            [
                                'id_tahun_ajaran' => $activeYear->id,
                                'nama_kelas' => $targetName,
                                'id_jenjang' => $oldClass->id_jenjang
                            ],
                            [
                                'tingkat_kelas' => $targetGrade,
                                'id_wali_kelas' => null
                            ]
                        );
                        
                        foreach ($promotedIds as $sid) {
                            \App\Models\AnggotaKelas::firstOrCreate(['id_kelas' => $targetClass->id, 'id_siswa' => $sid]);
                            $countPromoted++;
                        }
                    }
                }

                // --- 2. HANDLE RETENTION (TINGGAL KELAS) ---
                $retainedIds = \Illuminate\Support\Facades\DB::table('promotion_decisions')
                    ->where('id_kelas', $oldClass->id)
                    ->where('final_decision', 'retained') 
                    ->pluck('id_siswa');
                    
                if ($retainedIds->isNotEmpty()) {
                    // Stay in Same Grade (N -> N)
                    // Name: "1-A" -> "1-A" (Same Name)
                    $targetClassRetention = Kelas::firstOrCreate(
                        [
                            'id_tahun_ajaran' => $activeYear->id,
                            'nama_kelas' => $oldClass->nama_kelas, // Keep original name
                            'id_jenjang' => $oldClass->id_jenjang
                        ],
                        [
                            'tingkat_kelas' => $oldClass->tingkat_kelas, // Keep same grade
                            'id_wali_kelas' => null
                        ]
                    );
                    
                    foreach ($retainedIds as $sid) {
                        \App\Models\AnggotaKelas::firstOrCreate(['id_kelas' => $targetClassRetention->id, 'id_siswa' => $sid]);
                    }
                }
            }
            \Illuminate\Support\Facades\DB::commit();
            
            return back()->with('success', "Proses Selesai! $countPromoted Siswa Naik, $countGraduated Siswa Lulus.");
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', "Terjadi kesalahan: " . $e->getMessage());
        }
    }

    public function resetActiveClasses(Request $request)
    {
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        $classIds = Kelas::where('id_tahun_ajaran', $activeYear->id)->pluck('id');
        
        if ($classIds->isEmpty()) {
            return back()->with('error', 'Data sudah kosong boss!');
        }

        // FORCE DELETE via DB Query Builder (Nuclear Option)
        try {
            \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
            
            \Illuminate\Support\Facades\DB::table('anggota_kelas')->whereIn('id_kelas', $classIds)->delete();
            \Illuminate\Support\Facades\DB::table('pengajar_mapel')->whereIn('id_kelas', $classIds)->delete();
            // Add any other potential dependencies here if discovered later
            \Illuminate\Support\Facades\DB::table('kelas')->whereIn('id', $classIds)->delete();
            
            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
            
            return back()->with('success', "BERHASIL! Data tahun ini sudah dibersihkan total.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
            return back()->with('error', "Gagal reset: " . $e->getMessage());
        }
    }
}
