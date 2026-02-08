<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TahunAjaran;
use App\Models\NilaiSiswa;
use App\Models\AnggotaKelas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\BobotPenilaian;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    /**
     * HEALTH CHECK: MAGIC FIX (The "Tombol Ajaib")
     * Runs multiple maintenance tasks in sequence:
     * 1. Clear Cache/Logs
     * 2. Cleanup Orphans
     * 3. Deduplicate
     * 4. Fix Class Level
     * 5. Trim Data
     * 6. Sync Student Status
     */
    public function magicFix(Request $request)
    {
         if (auth()->user()->role !== 'admin') return back()->with('error', 'Unauthorized.');

         try {
             $log = [];

             // 1. Clear Logs
             $logFile = storage_path('logs/laravel.log');
             if (file_exists($logFile)) file_put_contents($logFile, '');
             $log[] = "Log Error dibersihkan.";

             // 2. Trim Data
             DB::statement("UPDATE siswa SET nama_lengkap = TRIM(nama_lengkap), nis_lokal = TRIM(nis_lokal), nisn = TRIM(nisn)");
             // DB::statement("UPDATE data_guru SET nip = TRIM(nip), nuptk = TRIM(nuptk)"); // Columns removed
             DB::statement("UPDATE users SET name = TRIM(name), email = TRIM(email)");
             $log[] = "Spasi nama/NIS dirapikan.";

             // 3. Fix Class Level (Jenjang)
             $miId = DB::table('jenjang')->where('kode', 'MI')->value('id');
             $mtsId = DB::table('jenjang')->where('kode', 'MTS')->value('id') ?? DB::table('jenjang')->where('kode', 'MTs')->value('id');
             
             if ($miId && $mtsId) {
                  $classes = Kelas::all();
                  $fixedCount = 0;
                  foreach ($classes as $c) {
                      $firstChar = substr(trim($c->nama_kelas), 0, 1);
                      if (in_array($firstChar, ['1','2','3','4','5','6']) && $c->id_jenjang != $miId) {
                          $c->update(['id_jenjang' => $miId]); $fixedCount++;
                      } elseif (in_array($firstChar, ['7','8','9']) && $c->id_jenjang != $mtsId) {
                          $c->update(['id_jenjang' => $mtsId]); $fixedCount++;
                      }
                  }
                  if ($fixedCount > 0) $log[] = "$fixedCount jenjang kelas diperbaiki.";
             }

             // 4. Cleanup Orphans
             // Members
             $orphMembers = DB::table('anggota_kelas')
                ->leftJoin('siswa', 'anggota_kelas.id_siswa', '=', 'siswa.id')
                ->leftJoin('kelas', 'anggota_kelas.id_kelas', '=', 'kelas.id')
                ->whereNull('siswa.id')->orWhereNull('kelas.id')->pluck('anggota_kelas.id');
             if ($orphMembers->isNotEmpty()) {
                 DB::table('anggota_kelas')->whereIn('id', $orphMembers)->delete();
                 $log[] = $orphMembers->count() . " anggota kelas hantu dihapus.";
             }
             
             // Grades
             $orphGrades = DB::table('nilai_siswa')
                ->leftJoin('siswa', 'nilai_siswa.id_siswa', '=', 'siswa.id')
                ->whereNull('siswa.id')->pluck('nilai_siswa.id');
             if ($orphGrades->isNotEmpty()) {
                 DB::table('nilai_siswa')->whereIn('id', $orphGrades)->delete();
                 $log[] = $orphGrades->count() . " nilai hantu dihapus.";
             }

             // 5. Sync Student Status (Active/Non-Active)
             $activeYear = TahunAjaran::where('status', 'aktif')->first();
             if ($activeYear) {
                 $enrolled = DB::table('anggota_kelas')
                    ->join('kelas', 'anggota_kelas.id_kelas', '=', 'kelas.id')
                    ->where('kelas.id_tahun_ajaran', $activeYear->id)
                    ->pluck('anggota_kelas.id_siswa')->unique();
                 
                 Siswa::whereIn('id', $enrolled)->where('status_siswa', '!=', 'aktif')->update(['status_siswa' => 'aktif']);
                 Siswa::whereNotIn('id', $enrolled)->where('status_siswa', 'aktif')->update(['status_siswa' => 'non-aktif']);
                 $log[] = "Status Aktif/Non-Aktif siswa disinkronkan.";
             }

             // 6. Clear Cache (Final)
              \Illuminate\Support\Facades\Artisan::call('optimize:clear');
              \Illuminate\Support\Facades\Artisan::call('view:clear');

             return back()->with('success', "MAGIC FIX SELESAI! 🪄✨ " . implode(' ', $log));

         } catch (\Exception $e) {
             return back()->with('error', "Magic Fix Gagal: " . $e->getMessage());
         }
    }


    /**
     * HEALTH CHECK 1: RESET KENAIKAN KELAS
     * Reverts the system state if a promotion event went wrong.
     */
    public function resetPromotion(Request $request)
    {
        // Security Check
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Akses Ditolak. Hanya Admin yang boleh melakukan operasi ini.');
        }

        try {
            DB::beginTransaction();

            $activeYear = TahunAjaran::where('status', 'aktif')->first();

            // 1. CLEAR DECISIONS
            DB::table('promotion_decisions')->delete();

            // 2. DETECT & CLEAN FUTURE YEAR
            $futureYear = null;
            if ($activeYear) {
                // Scenario: Active is old, Latest is new (Future)
                $latestYear = TahunAjaran::orderBy('id', 'desc')->first();
                
                if ($latestYear && $latestYear->id > $activeYear->id) {
                    $futureYear = $latestYear;
                } elseif ($latestYear && $latestYear->id == $activeYear->id) {
                     // Scenario: Active is new (Already promoted). Return to previous.
                     $prevYear = TahunAjaran::where('id', '<', $activeYear->id)->orderBy('id', 'desc')->first();
                     if ($prevYear) {
                         $futureYear = $activeYear;
                         $activeYear = $prevYear;
                     }
                }
            }
            
            $deletedCount = 0;
            if ($futureYear) {
                 $classIds = Kelas::where('id_tahun_ajaran', $futureYear->id)->pluck('id');
                 
                 if ($classIds->isNotEmpty()) {
                     DB::table('anggota_kelas')->whereIn('id_kelas', $classIds)->delete();
                     DB::table('pengajar_mapel')->whereIn('id_kelas', $classIds)->delete();
                     DB::table('kelas')->whereIn('id', $classIds)->delete();
                     $deletedCount = $classIds->count();
                 }

                 // Revert Active Status
                 TahunAjaran::query()->update(['status' => 'non-aktif']);
                 $activeYear->update(['status' => 'aktif']);
            }

            DB::commit();
            
            $msg = "RESET BERHASIL! Keputusan kenaikan telah dikosongkan.";
            if ($futureYear) {
                $msg .= " Data T.A. {$futureYear->nama} ({$deletedCount} kelas) telah dibersihkan. Sistem kembali ke T.A. {$activeYear->nama}.";
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Gagal Reset: " . $e->getMessage());
        }
    }

    /**
     * HEALTH CHECK 2: CLEANUP ORPHANS
     * Removes grades and class memberships that reference non-existent students or classes.
     */
    public function cleanupOrphans(Request $request)
    {
        if (auth()->user()->role !== 'admin') return back()->with('error', 'Unauthorized.');

        try {
            // 1. Clean AnggotaKelas (Orphaned from Student or Class)
            $orphanMembersValues = DB::table('anggota_kelas')
                ->leftJoin('siswa', 'anggota_kelas.id_siswa', '=', 'siswa.id')
                ->leftJoin('kelas', 'anggota_kelas.id_kelas', '=', 'kelas.id')
                ->whereNull('siswa.id')
                ->orWhereNull('kelas.id')
                ->pluck('anggota_kelas.id');

            $countMembers = $orphanMembersValues->count();
            if ($countMembers > 0) {
                DB::table('anggota_kelas')->whereIn('id', $orphanMembersValues)->delete();
            }

            // 2. Clean NilaiSiswa (Orphaned from Student)
            // Note: Orphaned from Period is less critical but can be checked too.
            $orphanGradesValues = DB::table('nilai_siswa')
                ->leftJoin('siswa', 'nilai_siswa.id_siswa', '=', 'siswa.id')
                ->whereNull('siswa.id')
                ->pluck('nilai_siswa.id');

            $countGrades = $orphanGradesValues->count();
            if ($countGrades > 0) {
                DB::table('nilai_siswa')->whereIn('id', $orphanGradesValues)->delete();
            }

            // 3. Clean PengajarMapel (Orphaned from Class or Teacher?)
            // Usually orphan from Class is most common after Class deletion.
            $orphanPengajar = DB::table('pengajar_mapel')
                ->leftJoin('kelas', 'pengajar_mapel.id_kelas', '=', 'kelas.id')
                ->whereNull('kelas.id')
                ->pluck('pengajar_mapel.id');
            
            $countPengajar = $orphanPengajar->count();
            if ($countPengajar > 0) {
                DB::table('pengajar_mapel')->whereIn('id', $orphanPengajar)->delete();
            }

            // 4. Clean Double Enrollment (Same Student, Same Year, Multiple Classes)
            // Finds students registered in >1 class in the same academic year.
            $duplicates = DB::select("
                SELECT a.id_siswa, k.id_tahun_ajaran, COUNT(*) as c
                FROM anggota_kelas a
                JOIN kelas k ON a.id_kelas = k.id
                GROUP BY a.id_siswa, k.id_tahun_ajaran
                HAVING c > 1
            ");
            
            $countDouble = 0;
            foreach ($duplicates as $dup) {
                // Get all Enrollment IDs for this student & year, Ordered by ID DESC (Latest First)
                $ids = DB::table('anggota_kelas as a')
                    ->join('kelas as k', 'a.id_kelas', '=', 'k.id')
                    ->where('a.id_siswa', $dup->id_siswa)
                    ->where('k.id_tahun_ajaran', $dup->id_tahun_ajaran)
                    ->orderBy('a.id', 'desc') 
                    ->pluck('a.id')
                    ->toArray();
                
                // Keep the first one (Latest / Highest ID), delete the rest
                $keep = array_shift($ids); 
                
                if (!empty($ids)) {
                    DB::table('anggota_kelas')->whereIn('id', $ids)->delete();
                    $countDouble += count($ids);
                }
            }

            return back()->with('success', "Pembersihan Data Sampah Selesai. Dihapus: $countMembers anggota kelas invalid, $countGrades nilai siswa invalid, $countPengajar pengajar invalid, dan $countDouble siswa ganda dibereskan.");

        } catch (\Exception $e) {
            return back()->with('error', "Error Cleanup: " . $e->getMessage());
        }
    }

    /**
     * HEALTH CHECK 8: SYNC PROMOTION HISTORY
     * Pulls historical status from AnggotaKelas and saves to PromotionDecisions.
     */
    public function syncPromotionHistory(Request $request)
    {
        if (auth()->user()->role !== 'admin') return back()->with('error', 'Unauthorized.');

        $history = DB::table('anggota_kelas')
            ->join('kelas', 'anggota_kelas.id_kelas', '=', 'kelas.id')
            ->whereIn('anggota_kelas.status', ['naik_kelas', 'tinggal_kelas', 'lulus'])
            ->select('anggota_kelas.*', 'kelas.id_tahun_ajaran')
            ->get();
            
        $count = 0;
        foreach ($history as $h) {
             $decision = null;
             if ($h->status == 'naik_kelas') $decision = 'promoted';
             elseif ($h->status == 'tinggal_kelas') $decision = 'retained';
             elseif ($h->status == 'lulus') $decision = 'graduated';
             
             if ($decision) {
                 DB::table('promotion_decisions')->updateOrInsert(
                     [
                         'id_siswa' => $h->id_siswa,
                         'id_kelas' => $h->id_kelas,
                         'id_tahun_ajaran' => $h->id_tahun_ajaran
                     ],
                     [
                        'final_decision' => $decision,
                        'system_recommendation' => $decision, 
                        'notes' => 'Migrasi Data Riwayat (Anggota Kelas)',
                        'updated_at' => now()
                     ]
                 );
                 $count++;
             }
        }
        
        return back()->with('success', "Sinkronisasi Riwayat Selesai. $count data riwayat berhasil ditarik ke sistem baru.");
    }
    
    /**
     * HEALTH CHECK 3: DEDUPLICATE GRADES
     * Removes duplicate grade entries, keeping the most recently updated one.
     */
    public function deduplicateGrades(Request $request)
    {
        if (auth()->user()->role !== 'admin') return back()->with('error', 'Unauthorized.');

        // Logic: Find (id_siswa, id_mapel, id_periode) having count > 1
        $duplicates = DB::select("
            SELECT id_siswa, id_mapel, id_periode, COUNT(*) as c 
            FROM nilai_siswa 
            GROUP BY id_siswa, id_mapel, id_periode 
            HAVING c > 1
        ");

        $deletedTotal = 0;

        foreach ($duplicates as $dup) {
            // Get all IDs for this combo, ordered by updated_at desc (keep latest)
            $ids = DB::table('nilai_siswa')
                ->where('id_siswa', $dup->id_siswa)
                ->where('id_mapel', $dup->id_mapel)
                ->where('id_periode', $dup->id_periode)
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->toArray();
            
            // Remove the first one (the one to KEEP) from the list
            array_shift($ids);

            // Delete the rest
            if (count($ids) > 0) {
                DB::table('nilai_siswa')->whereIn('id', $ids)->delete();
                $deletedTotal += count($ids);
            }
        }

        return back()->with('success', "Dedup Selesai. $deletedTotal nilai ganda telah dihapus (versi terbaru dipertahankan).");
    }

    /**
     * HEALTH CHECK 4: FORCE RECALCULATION
     * Triggers recalculation for all levels active in the current year.
     */
    public function forceFullRecalculation(Request $request)
    {
        if (auth()->user()->role !== 'admin') return back()->with('error', 'Unauthorized.');

        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        $jenjangs = ['MI', 'MTS'];
        $totalProcessed = 0;

        foreach ($jenjangs as $jenjang) {
            $bobot = BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)
                ->where('jenjang', $jenjang)
                ->first();
            
            if (!$bobot) continue;

            $rules = \App\Models\PredikatNilai::where('id_tahun_ajaran', $activeYear->id)
                ->where('jenjang', $jenjang)
                ->orderBy('min_score', 'desc')
                ->get();
            
            $settings = \App\Models\GlobalSetting::all()->pluck('value', 'key');
            $shouldRound = isset($settings['rounding_enable']) ? (bool)$settings['rounding_enable'] : true;
            
            $periodIds = Periode::where('id_tahun_ajaran', $activeYear->id)
                ->where('lingkup_jenjang', $jenjang)
                ->pluck('id');

            NilaiSiswa::whereIn('id_periode', $periodIds)
                ->chunk(200, function ($grades) use ($bobot, $rules, $shouldRound, &$totalProcessed, $jenjang) {
                    foreach ($grades as $grade) {
                        $h = $grade->nilai_harian;
                        $u = $grade->nilai_uts_cawu;
                        $a = $grade->nilai_uas;

                        // DYNAMIC CALCULATION (Normalized)
                        // Removes hardcoded "MI" logic in favor of actual DB weights.
                        // Handles cases where weights sum != 100 (e.g. Harian Disabled = 0)
                        
                        $bH = $bobot->bobot_harian;
                        $bT = $bobot->bobot_uts_cawu;
                        $bA = $bobot->bobot_uas;
                        
                        $totalWeight = $bH + $bT + $bA;
                        
                        $score = 0;
                        if ($totalWeight > 0) {
                            $weightedSum = ($h * $bH) + ($u * $bT) + ($a * $bA);
                            $score = $weightedSum / $totalWeight;
                        }

                        if ($shouldRound) {
                            $score = round($score);
                        } else {
                            $score = round($score, 2);
                        }

                        $pred = 'D';
                        foreach ($rules as $r) {
                            if ($score >= $r->min_score) {
                                $pred = $r->grade;
                                break;
                            }
                        }

                        if ($grade->nilai_akhir != $score || $grade->predikat != $pred) {
                            $grade->nilai_akhir = $score;
                            $grade->predikat = $pred;
                            $grade->save(); 
                            $totalProcessed++;
                        }
                    }
                });
        }

        return back()->with('success', "Sinkronisasi Total Selesai. $totalProcessed nilai diperbarui agar sesuai dengan rumus bobot terbaru.");
    }

    /**
     * HEALTH CHECK 5: SYNC STUDENT STATUS
     * Updates student 'status' (Aktif/Non-Aktif) based on their enrollment in the currently active academic year.
     */
    public function syncStudentStatus(Request $request)
    {
        if (auth()->user()->role !== 'admin') return back()->with('error', 'Unauthorized.');

        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // 1. Get IDs of students enrolled in Active Year
        $enrolledStudentIds = DB::table('anggota_kelas')
            ->join('kelas', 'anggota_kelas.id_kelas', '=', 'kelas.id')
            ->where('kelas.id_tahun_ajaran', $activeYear->id)
            ->pluck('anggota_kelas.id_siswa')
            ->unique();

        // 2. Set Status = 'aktif' for enrolled students
        $activated = Siswa::whereIn('id', $enrolledStudentIds)
            ->where('status_siswa', '!=', 'aktif')
            ->update(['status_siswa' => 'aktif']);

        // 3. Set Status = 'non-aktif' for others
        $deactivated = Siswa::whereNotIn('id', $enrolledStudentIds)
            ->where('status_siswa', 'aktif') 
            ->update(['status_siswa' => 'non-aktif']);

        return back()->with('success', "Sinkronisasi Status Selesai. $activated siswa diaktifkan, $deactivated siswa dinon-aktifkan.");
    }

    /**
     * HEALTH CHECK 6: GENERATE MISSING ACCOUNTS
     * Creates User accounts for Teachers and Students who don't have one yet.
     */
    public function generateMissingAccounts(Request $request)
    {
        if (auth()->user()->role !== 'admin') return back()->with('error', 'Unauthorized.');

        $createdCount = 0;

        // A. Teachers
        $teachers = \App\Models\DataGuru::whereNull('id_user')->get();
        foreach ($teachers as $guru) {
            $username = $guru->nip ?? $guru->nuptk ?? 'guru'.$guru->id;
            $email = preg_replace('/[^a-zA-Z0-9]/', '', $username) . '@nurulainy.sch.id';
            
            if (\App\Models\User::where('email', $email)->exists()) {
                $email = 'guru'.$guru->id.rand(100,999).'@nurulainy.sch.id';
            }

            $user = \App\Models\User::create([
                'name' => $guru->nip ?? 'Guru ' . $guru->id,
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make('guru123'),
                'role' => 'teacher'
            ]);
            
            $guru->update(['id_user' => $user->id]);
            $createdCount++;
        }

        // B. Students
        $students = Siswa::whereNull('id_user')->get();
        foreach ($students as $siswa) {
            $username = $siswa->nis_lokal ?? $siswa->nisn ?? 'siswa'.$siswa->id;
            $email = preg_replace('/[^a-zA-Z0-9]/', '', $username) . '@nurulainy.sch.id';

             if (\App\Models\User::where('email', $email)->exists()) {
                $email = 'siswa'.$siswa->id.rand(100,999).'@nurulainy.sch.id';
            }

            $user = \App\Models\User::create([
                'name' => $siswa->nama,
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make('siswa123'),
                'role' => 'student'
            ]);

            $siswa->update(['id_user' => $user->id]);
            $createdCount++;
        }

        return back()->with('success', "Generate Akun Selesai. $createdCount akun baru berhasil dibuat.");
    }

    /**
     * HEALTH CHECK 7: SYSTEM DETOX (CACHE CLEAR)
     * Clears application cache, view cache, and config cache.
     */
    public function clearSystemCache(Request $request)
    {
        if (auth()->user()->role !== 'admin') return back()->with('error', 'Unauthorized.');

        try {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');

            return back()->with('success', "Detox Selesai! Cache sistem telah dibersihkan.");
        } catch (\Exception $e) {
            return back()->with('error', "Gagal Detox: " . $e->getMessage());
        }
    }

    /**
     * HEALTH CHECK 8: AUTO-FIX JENJANG (CLASS LEVEL THERAPY)
     * Automatically assigns 'MI' or 'MTS' jenjang based on class name (1-6 -> MI, 7-9 -> MTS).
     */
    public function fixClassLevel(Request $request)
    {
        if (auth()->user()->role !== 'admin') return back()->with('error', 'Unauthorized.');

        // 1. Resolve Jenjang IDs
        $miId = DB::table('jenjang')->where('kode', 'MI')->value('id');
        $mtsId = DB::table('jenjang')->where('kode', 'MTS')->value('id') 
                 ?? DB::table('jenjang')->where('kode', 'MTs')->value('id'); 

        if (!$miId || !$mtsId) {
            return back()->with('error', "Gagal: Data Master Jenjang (MI/MTS) tidak ditemukan.");
        }

        $countMI = 0;
        $countMTS = 0;

        // 2. Iterate Classes and Fix
        $classes = Kelas::all();
        foreach ($classes as $kelas) {
            $name = trim($kelas->nama_kelas);
            $firstChar = substr($name, 0, 1);
            
            $newJenjangId = null;

            // Logic: 1-6 = MI
            if (in_array($firstChar, ['1', '2', '3', '4', '5', '6'])) {
                $newJenjangId = $miId;
                if ($kelas->id_jenjang != $newJenjangId) {
                    $kelas->id_jenjang = $newJenjangId;
                    $kelas->save();
                    $countMI++;
                }
            }
            // Logic: 7-9 = MTS
            elseif (in_array($firstChar, ['7', '8', '9'])) {
                $newJenjangId = $mtsId;
                if ($kelas->id_jenjang != $newJenjangId) {
                    $kelas->id_jenjang = $newJenjangId;
                    $kelas->save();
                    $countMTS++;
                }
            }
        }

        return back()->with('success', "Terapi Identitas Selesai. $countMI kelas diperbaiki jadi MI, $countMTS kelas diperbaiki jadi MTS.");
    }

    /**
     * HEALTH CHECK 9: PLASTIC SURGERY (TRIM DATA)
     * Trims whitespace from critical text fields in Students and Teachers.
     */
    public function trimData(Request $request)
    {
        if (auth()->user()->role !== 'admin') return back()->with('error', 'Unauthorized.');

        // 1. Trim Students (Siswa)
        DB::statement("UPDATE siswa SET nama_lengkap = TRIM(nama_lengkap), nis_lokal = TRIM(nis_lokal), nisn = TRIM(nisn)");
        // 2. Trim Teachers (DataGuru: No name column, only NIP/NUPTK)
        DB::statement("UPDATE data_guru SET nip = TRIM(nip), nuptk = TRIM(nuptk)");
        // 3. Trim Users
        DB::statement("UPDATE users SET name = TRIM(name), email = TRIM(email)");

        return back()->with('success', "Operasi Plastik Selesai! Spasi berlebih pada Nama, NIS, NIP telah dibuang. Data jadi lebih rapi.");
    }

    /**
     * HEALTH CHECK 10: SYSTEM SCRUB (LOG CLEANER)
     * Truncates the Laravel log file to free up space.
     */
    public function clearLogs(Request $request)
    {
        if (auth()->user()->role !== 'admin') return back()->with('error', 'Unauthorized.');

        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            // Truncate file to 0 bytes
            file_put_contents($logFile, '');
            return back()->with('success', "Luluran Sistem Selesai! Log file telah dikosongkan.");
        }

        return back()->with('warning', "File log tidak ditemukan atau sudah kosong.");
    }
    /**
     * HEALTH CHECK 11: FACTORY RESET (DOOMSDAY BUTTON)
     * Resets ALL transactional data (Students, Grades, Classes)
     * Preserves: Admin & TU Accounts, Master Data (Mapel, Tahun Ajaran)
     */
    public function resetSystem(Request $request) 
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'AKSES DITOLAK KERAS! Hanya Admin yang boleh menekan tombol kiamat ini.');
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // 1. Transactional Data (Grades, Attendance, Notes)
            DB::table('nilai_siswa')->truncate();
            DB::table('catatan_kehadiran')->truncate();
            DB::table('catatan_wali_kelas')->truncate();
            DB::table('promotion_decisions')->truncate();
            DB::table('riwayat_perubahan_nilai')->truncate();
            DB::table('notifications')->truncate();
            
             // 2. Class & Schedule Data
            DB::table('pengajar_mapel')->truncate();
            DB::table('anggota_kelas')->truncate();
            DB::table('kelas')->truncate();
            
            // 3. User Data (Students & Teachers)
            DB::table('siswa')->truncate();
            DB::table('data_guru')->truncate(); // Assuming this is purely profile data
            
            // 4. Delete Users (Keep Admin & TU)
            // Using whereNotIn role ['admin', 'staff_tu']
            $deletedUsers = DB::table('users')
                ->whereNotIn('role', ['admin', 'staff_tu'])
                ->delete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return back()->with('success', "FACTORY RESET BERHASIL. Sistem telah bersih total. Semua data siswa, guru, kelas, dan nilai telah dihapus. ($deletedUsers akun pengguna dihapus).");

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Ensure safety
            return back()->with('error', "GAGAL RESET: " . $e->getMessage());
        }
    }
    /**
     * HEALTH CHECK 12: EXECUTE MIGRATION (DB STRUCTURE UPDATE)
     * Runs 'php artisan migrate' to ensure database tables are up to date.
     */
    public function migrateDatabase(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized.');
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            if (empty(trim($output))) {
                return back()->with('success', "Database sudah Up-to-Date. Tidak ada perubahan struktur.");
            }

            return back()->with('success', "Update Struktur Database Berhasil! Log: " . $output);

        } catch (\Exception $e) {
            return back()->with('error', "Gagal Update Struktur: " . $e->getMessage());
        }
    }
}
