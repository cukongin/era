<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PengajarMapel;
use App\Models\NilaiSiswa;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\Siswa;
use App\Models\BobotPenilaian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GradeImportController extends Controller
{
    use \App\Traits\HasDeadlineCheck;

    protected function checkAccess($kelasId)
    {
        $user = Auth::user();
        // dd('DEBUG ACCESS', $user->id, $user->role, $kelasId); 

        $kelas = Kelas::findOrFail($kelasId);
        
        // Debugging for User
        // if ($kelas->id_wali_kelas != $user->id && $user->role != 'admin') {
        //      dd("ACCESS DENIED. Logged User: {$user->id}, Wali Kelas: {$kelas->id_wali_kelas}");
        // }

        $isWali = $kelas->id_wali_kelas == $user->id; // Loose comparison to be safe
        $isAdmin = $user->role === 'admin';
        
        if (!$isWali && !$isAdmin) {
            abort(403, 'Akses ditolak. Fitur ini khusus Wali Kelas atau Admin.');
        }
        
        return $kelas;
    }

    public function index($kelasId)
    {
        $kelas = $this->checkAccess($kelasId);
        
        // Count Mapels
        $mapelCount = PengajarMapel::where('id_kelas', $kelasId)->count();
        
        return view('teacher.grade_import.index', compact('kelas', 'mapelCount'));
    }

    public function downloadTemplate(Request $request, $kelasId)
    {
        $kelas = $this->checkAccess($kelasId);
        $jenjang = $kelas->jenjang->kode;
        $activeJenjang = strtoupper($jenjang);
        $type = $request->get('type', 'period'); // 'period' (default) or 'tahunan'
        
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        if ($type == 'tahunan') {
            // Get ALL Active Periods for this Jenjang
            $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
                ->where('lingkup_jenjang', $jenjang)
                ->orderBy('id', 'asc') // Ensure chronological order
                ->get();
            $filename = "Template_Tahunan_{$kelas->nama_kelas}_{$activeYear->nama_tahun}.csv";
        } else {
            // Single Active Period
            $periods = collect([Periode::where('id_tahun_ajaran', $activeYear->id)
                ->where('status', 'aktif')
                ->where('lingkup_jenjang', $jenjang)
                ->firstOrFail()]);
            $filename = "Template_Kolektif_{$kelas->nama_kelas}_{$periods->first()->nama_periode}.csv";
        }

        // Get Mapels Assigned to Class
        $assignedMapels = PengajarMapel::with('mapel')
            ->where('id_kelas', $kelasId)
            ->get()
            ->sortBy(function($item) {
                return $item->mapel->kategori . '_' . $item->mapel->nama_mapel;
            });

        // Get Students
        $students = $kelas->anggota_kelas()->with('siswa')
            ->get()
            ->sortBy(fn($ak) => $ak->siswa->nama_lengkap);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($students, $assignedMapels, $jenjang, $periods) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM

            // Row 1: Instructions
            fputcsv($file, ["PETUNJUK: Jangan ubah ID Siswa atau ID Mapel di Header. Cukup isi nilai 0-100."]);
            
            // Row 2: Headers
            $headerRow = ['NO', 'NIS', 'NAMA SISWA'];
            
            // 1. Mapel Columns
            foreach ($assignedMapels as $am) {
                $mapel = $am->mapel;
                $basePrefix = "[{$mapel->id}] {$mapel->nama_mapel}";
                
                foreach ($periods as $p) {
                    $pSuffix = $periods->count() > 1 ? " ({$p->nama_periode})" : "";
                    
                    $headerRow[] = "$basePrefix$pSuffix (Harian)";
                    $headerRow[] = ($jenjang == 'MI') ? "$basePrefix$pSuffix (Ujian Cawu)" : "$basePrefix$pSuffix (PTS)";
                    
                    if ($jenjang == 'MTS') {
                        $headerRow[] = "$basePrefix$pSuffix (PAS/PAT)";
                    }
                }
            }
            
            // 2. Attendance & Personality Columns (Per Period)
            foreach ($periods as $p) {
                 $pSuffix = $periods->count() > 1 ? " ({$p->nama_periode})" : "";
                 
                 // Attendance
                 $headerRow[] = "Sakit$pSuffix";
                 $headerRow[] = "Izin$pSuffix";
                 $headerRow[] = "Tanpa Keterangan$pSuffix";
                 
                 // Personality
                 $headerRow[] = "Sikap: Kelakuan$pSuffix";
                 $headerRow[] = "Sikap: Kerajinan$pSuffix";
                 $headerRow[] = "Sikap: Kebersihan$pSuffix";
            }
            
            fputcsv($file, $headerRow);

            // Data Rows
            foreach ($students as $index => $ak) {
                $row = [
                    $index + 1,
                    $ak->siswa->nis_lokal,
                    $ak->siswa->nama_lengkap
                ];
                
                // Empty slots for Mapels
                foreach ($assignedMapels as $am) {
                    foreach ($periods as $p) {
                         $row[] = ''; // Harian
                         $row[] = ''; // UTS
                         if ($jenjang == 'MTS') $row[] = ''; // UAS
                    }
                }
                
                // Empty slots for Attendance & Personality
                foreach ($periods as $p) {
                    // Attendance
                    $row[] = ''; // S
                    $row[] = ''; // I
                    $row[] = ''; // A
                    
                    // Personality (Default 'Baik' or Empty? Let's use 'Baik' to be helpful)
                    $row[] = 'Baik'; // Kelakuan
                    $row[] = 'Baik'; // Kerajinan
                    $row[] = 'Baik'; // Kebersihan
                }
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        }, 200, $headers);
    }

    public function preview(Request $request, $kelasId)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,text/plain,application/csv,text/comma-separated-values|max:5120'
        ]);

        $kelas = $this->checkAccess($kelasId);
        $jenjang = $kelas->jenjang->kode;
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // Context: Class-Level Import (Wali Kelas)
        $file = $request->file('file');
        
        // Read file content first
        $content = file_get_contents($file->getRealPath());
        $lines = explode(PHP_EOL, $content);
        $rows = array_map('str_getcsv', $lines);
        
        if (count($rows) < 2) return back()->with('error', 'File kosong atau format salah.');

        // 1. Dynamic Header Detection
        $headerRow = null; 
        $headerRowIndex = -1;
        foreach ($rows as $index => $r) {
            $rowString = implode(' ', array_map('strtoupper', $r));
            // Robust check: Look for NIS and NAME
            if (str_contains($rowString, 'NIS') && (str_contains($rowString, 'NAMA') || str_contains($rowString, 'SISWA'))) {
                $headerRow = $r;
                $headerRowIndex = $index;
                break;
            }
        }

        if (!$headerRow) $headerRow = $rows[0]; // Fallback

        // 2. Parse Columns (Super Logic)
        $allPeriods = Periode::where('id_tahun_ajaran', $activeYear->id)->where('lingkup_jenjang', $jenjang)->get();
        $mapelCols = [];
        $structure = ['grades' => [], 'non_academic' => []];

        foreach ($headerRow as $idx => $colRaw) {
            $col = trim($colRaw);
            if (empty($col)) continue;

            // A. Academic Column: [123] Mapel Name (Period)
            if (preg_match('/\[(\d+)\]/', $col, $matches)) {
                $mapelId = $matches[1];
                
                // Detect Type
                $type = 'harian'; // Default
                if (stripos($col, 'Ujian Cawu') !== false || stripos($col, 'PTS') !== false) $type = 'uts';
                if (stripos($col, 'PAS') !== false || stripos($col, 'PAT') !== false || stripos($col, 'UAS') !== false) $type = 'uas';
                
                // Detect Period from Column Name
                $pId = 0;
                foreach ($allPeriods as $p) {
                    if (stripos($col, "({$p->nama_periode})") !== false) {
                        $pId = $p->id;
                        break;
                    }
                }
                // Fallback to Active Period if not found in column name
                if ($pId === 0) {
                     $activeP = $allPeriods->firstWhere('status', 'aktif');
                     $pId = $activeP ? $activeP->id : ($allPeriods->first()->id ?? 0);
                }

                $meta = ['mapel_id' => $mapelId, 'period_id' => $pId, 'type' => $type];
                $mapelCols[$idx] = $meta;
                
                // Build Structure for View
                $mapelName = trim(preg_replace('/\[\d+\]/', '', $col));
                // Remove (Periode) suffix for cleaner name
                foreach($allPeriods as $p) $mapelName = str_replace("({$p->nama_periode})", "", $mapelName);
                $mapelName = trim(str_replace(['(Harian)', '(PTS)', '(PAS)', '(PAT)', '(Ujian Cawu)', '(UAS)'], '', $mapelName));
                
                $structure['grades'][$pId][$mapelId] = [
                    'nama_mapel' => $mapelName,
                    'mapel_id' => $mapelId
                ];
            }
            // B. Non-Academic Column: Sakit / Izin / Alpa / Kelakuan / etc
            else {
                $field = null;
                // Attendance
                if (stripos($col, 'Sakit') !== false) $field = 'sakit';
                elseif (stripos($col, 'Izin') !== false) $field = 'izin';
                elseif (stripos($col, 'Tanpa Keterangan') !== false || stripos($col, 'Alpa') !== false || stripos($col, 'Alpha') !== false) $field = 'tanpa_keterangan';
                // Personality
                elseif (stripos($col, 'Kelakuan') !== false || stripos($col, 'Sikap') !== false) $field = 'kelakuan';
                elseif (stripos($col, 'Kerajinan') !== false) $field = 'kerajinan';
                elseif (stripos($col, 'Kebersihan') !== false) $field = 'kebersihan';
                
                if ($field) {
                    // Detect Period
                    $pId = 0;
                    foreach ($allPeriods as $p) {
                         if (stripos($col, "({$p->nama_periode})") !== false) {
                             $pId = $p->id;
                             break;
                         }
                    }
                    if ($pId === 0) {
                         $activeP = $allPeriods->firstWhere('status', 'aktif');
                         $pId = $activeP ? $activeP->id : ($allPeriods->first()->id ?? 0);
                    }

                    $mapelCols[$idx] = ['is_non_academic' => true, 'field' => $field, 'period_id' => $pId];
                    
                    // Add to Structure if not exists
                    if (!isset($structure['non_academic'][$pId])) $structure['non_academic'][$pId] = [];
                    if (!in_array($field, $structure['non_academic'][$pId])) {
                        $structure['non_academic'][$pId][] = $field; 
                    }
                }
            }
        }
        
        // 3. Parse Rows
        $parsedData = [];
        $importErrors = [];
        
        // Start after header
        $startRow = ($headerRowIndex == -1) ? 1 : $headerRowIndex + 1;
        
        for ($i = $startRow; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row) || count($row) < 3) continue;

            // Strategy: Try to find NIS column. In Template, it is usually Col 2 (Index 1).
            // But we can be smarter.
            $nisIndex = 1; // Default
            foreach ($headerRow as $hEx => $hVal) {
                if (stripos($hVal, 'NIS') !== false) { $nisIndex = $hEx; break; }
            }
            
            $nis = isset($row[$nisIndex]) ? trim($row[$nisIndex]) : '';
            if (empty($nis)) continue;

            $siswa = \App\Models\Siswa::where('nis_lokal', $nis)->first();
            if (!$siswa) {
                 // $importErrors[] = "Baris " . ($i+1) . ": NIS '$nis' not found.";
                 continue; 
            }
            
            // Check Class Membership
            $inClass = \App\Models\AnggotaKelas::where('id_kelas', $kelas->id)->where('id_siswa', $siswa->id)->exists();
            if (!$inClass) {
                 // Skip students not in this class
                 continue;
            }

            $sGrades = [];
            $sNonAcademic = [];
            
            foreach ($mapelCols as $colIdx => $meta) {
                 $rawVal = isset($row[$colIdx]) ? trim($row[$colIdx]) : null;
                 
                 if (isset($meta['is_non_academic'])) {
                     if ($rawVal !== null && $rawVal !== '') {
                         $field = $meta['field'];
                         // Integer for Attendance
                         if (in_array($field, ['sakit', 'izin', 'tanpa_keterangan'])) {
                             $val = (int)$rawVal;
                         } else {
                             // Clean string for Attitude
                             $val = trim($rawVal); 
                             // Remove quotes if excel added them
                             $val = trim($val, "'\"");
                         }
                         $sNonAcademic[$meta['period_id']][$field] = $val;
                     }
                 } else {
                     // Academic
                     if ($rawVal !== null && $rawVal !== '') {
                         $val = floatval($rawVal);
                         if ($val > 100) $val = 100; if ($val < 0) $val = 0;
                         
                         $mId = $meta['mapel_id'];
                         $pId = $meta['period_id'];
                         $type = $meta['type'];

                        if (!isset($sGrades[$pId][$mId])) {
                            $sGrades[$pId][$mId] = [
                                'harian' => null,
                                'uts' => null,
                                'uas' => null
                            ];
                        }
                         
                         $sGrades[$pId][$mId][$type] = $val;
                     }
                 }
            }

            $parsedData[] = [
                'siswa' => $siswa,
                'grades' => $sGrades,
                'non_academic' => $sNonAcademic
            ];
        }

        $mapelNames = \App\Models\Mapel::whereIn('id', array_keys(($structure['grades'][$allPeriods->first()->id??0] ?? [])))->pluck('nama_mapel', 'id'); // Approximate names
        if ($mapelNames->isEmpty()) {
             // Fallback: Get all mapels involved
             $mIds = [];
             foreach($structure['grades'] as $p => $ms) $mIds = array_merge($mIds, array_keys($ms));
             $mapelNames = \App\Models\Mapel::whereIn('id', array_unique($mIds))->pluck('nama_mapel', 'id');
        }

        $importKey = 'bulk_import_' . Auth::id() . '_' . time();
        Cache::put($importKey, [
            'kelas_id' => $kelasId,
            'data' => $parsedData
        ], 3600);
        
        return view('teacher.grade_import.preview', compact('kelas', 'parsedData', 'importErrors', 'importKey', 'jenjang', 'mapelNames', 'structure'));
    }

    public function store(Request $request)
    {
        $key = $request->import_key;
        $cached = Cache::get($key);
        
        if (!$cached) return redirect()->back()->with('error', 'Sesi import kadaluarsa. Silakan upload ulang.');
        
        $kelasId = $cached['kelas_id'];
        
        // Collect ALL involved Period IDs from the request data
        $uniquePeriodIds = [];
        if ($request->grades) {
            foreach ($request->grades as $periods) {
                foreach (array_keys($periods) as $pId) $uniquePeriodIds[$pId] = true;
            }
        }
        if ($request->non_academic) {
            foreach ($request->non_academic as $periods) {
                foreach (array_keys($periods) as $pId) $uniquePeriodIds[$pId] = true;
            }
        }
        $uniquePeriodIds = array_keys($uniquePeriodIds);
        
        // SECURITY CHECK: Deadline for ALL involved periods
        foreach ($uniquePeriodIds as $pCheckId) {
             $check = $this->checkDeadlineAccess($pCheckId, $kelasId);
             if ($check !== true) {
                 $pName = Periode::find($pCheckId)->nama_periode ?? $pCheckId;
                 return redirect()->back()->with('error', "Akses Ditolak untuk Periode $pName: $check");
             }
        }

        $kelas = Kelas::findOrFail($kelasId);
        $jenjang = $kelas->jenjang->kode;
        $activeJenjang = strtoupper($jenjang);
        $activeYear = $kelas->tahun_ajaran;

        // Fetch Settings & Rules Once
        $bobotSettings = BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)
            ->where('jenjang', $jenjang)
            ->first();
            
        // Default Weights if not set
        $bH = $bobotSettings ? $bobotSettings->bobot_harian : 1; 
        $bT = $bobotSettings ? $bobotSettings->bobot_uts_cawu : 1;
        $bA = $bobotSettings ? $bobotSettings->bobot_uas : 1;
        
        $rules = DB::table('predikat_nilai')
            ->where('jenjang', $activeJenjang)
            ->orderBy('min_score', 'desc')
            ->get();
            
        $gradingSettings = DB::table('grading_settings')
            ->where('jenjang', $activeJenjang)
            ->first();
            
        $shouldRound = $gradingSettings ? $gradingSettings->rounding_enable : true;
        $kkmDefault = $gradingSettings->kkm_default ?? 70;

        // Process Submitted Data (Allow Edits from Form)
        // Request format: grades[siswa_id][mapel_id][harian/uts/uas]
        $formGrades = $request->grades;
        $count = 0;

        if ($formGrades) {
            foreach ($formGrades as $siswaId => $periods) {
                foreach ($periods as $pId => $mapels) {
                    foreach ($mapels as $mapelId => $scores) {
                        
                        $harian = isset($scores['harian']) && $scores['harian'] !== '' ? $scores['harian'] : null;
                        $uts    = isset($scores['uts']) && $scores['uts'] !== '' ? $scores['uts'] : null;
                        $uas    = isset($scores['uas']) && $scores['uas'] !== '' ? $scores['uas'] : null;
                        
                        // CRITICAL FIX: If ALL inputs are empty, SKIP update to preserve existing data.
                        // This allows "Attendance Only" import without wiping grades.
                        if ($harian === null && $uts === null && $uas === null) {
                            continue;
                        }

                        // Calculation
                        // If any component is null, final should be null (Incomplete)
                        $hVal = $harian ?? 0;
                        $tVal = $uts ?? 0;
                        $aVal = $uas ?? 0;

                        $realBH = $bH / 100;
                        $realBT = $bT / 100;
                        $realBA = $bA / 100;
                        
                        // Check completeness
                        $isComplete = true;
                        if ($bH > 0 && $harian === null) $isComplete = false;
                        if ($bT > 0 && $uts === null) $isComplete = false;
                        if ($jenjang == 'MTS' && $bA > 0 && $uas === null) $isComplete = false;

                        if ($isComplete) {
                             $final = ($hVal * $realBH) + ($tVal * $realBT) + ($aVal * $realBA);
                             
                             if ($shouldRound) {
                                 $final = round($final);
                             } else {
                                 $final = round($final, 2);
                             }
                        } else {
                            $final = null;
                        }
                        
                        // Predicate
                        $predikat = 'D';
                        foreach ($rules as $rule) {
                            if ($final >= $rule->min_score) {
                                $predikat = $rule->grade;
                                break;
                            }
                        }
    
                        // Save
                        NilaiSiswa::updateOrCreate(
                            [
                                'id_siswa'   => $siswaId,
                                'id_kelas'   => $kelasId,
                                'id_mapel'   => $mapelId,
                                'id_periode' => $pId // Use dynamic Period ID
                            ],
                            [
                                'id_guru'        => Auth::id(), 
                                'nilai_harian'   => $harian,
                                'nilai_uts_cawu' => $uts,
                                'nilai_uas'      => $uas,
                                'nilai_akhir'    => $final,
                                'nilai_akhir_asli' => $final,
                                'is_katrol'        => false,
                                'katrol_note'      => null,
                                'predikat'       => $predikat,
                                'status'         => 'draft' 
                            ]
                        );
                        $count++;
                    }
                }
            }
        }
        
        // Process Non-Academic (Attendance/Personality) if exists
        $formNonAcademic = $request->non_academic; 
        if ($formNonAcademic) {
            foreach ($formNonAcademic as $siswaId => $periods) {
                 foreach ($periods as $pId => $data) {
                      // Filter valid columns
                      $updateData = [];
                      foreach ($data as $key => $val) {
                          if ($val !== null && $val !== '') {
                              // Only allow known columns
                              if (in_array($key, ['sakit', 'izin', 'tanpa_keterangan', 'kelakuan', 'kerajinan', 'kebersihan'])) {
                                   $updateData[$key] = $val;
                              }
                          }
                      }
                      
                      if (!empty($updateData)) {
                          \App\Models\CatatanKehadiran::updateOrCreate(
                            [
                                'id_siswa'   => $siswaId,
                                'id_kelas'   => $kelasId,
                                'id_periode' => $pId
                            ],
                            array_merge($updateData, ['updated_at' => now()])
                          );
                      }
                 }
            }
        }

        Cache::forget($key);
        
        return redirect()->route('walikelas.monitoring')->with('success', "Berhasil menyimpan $count data nilai dan data kehadiran!");
    }
    // --- GLOBAL IMPORT (ADMIN) ---

    public function indexGlobal()
    {
        return view('admin.grade_import.index');
    }

    public function downloadTemplateGlobal(Request $request, $jenjang)
    {
        $jenjang = strtoupper($jenjang);
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // Get All Active Periods for this Jenjang
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('lingkup_jenjang', $jenjang)
            ->orderBy('id', 'asc')
            ->get();
            
        // Filter by Grade (Tingkat) if requested
        $targetGrade = $request->get('grade'); // e.g., 1, 2, 7, 8
            
        // Get All Mapels used in this Jenjang/Grade (via Plotting)
        // More accurate: Get all mapels assigned to any class in this jenjang/grade
        $classQuery = Kelas::where('id_tahun_ajaran', $activeYear->id)
            ->whereHas('jenjang', function($q) use ($jenjang) {
                $q->where('kode', $jenjang);
            });
            
        if ($targetGrade) {
            $classQuery->where('tingkat_kelas', $targetGrade);
        }
            
        $classIds = $classQuery->pluck('id');
            
        $mapelIds = PengajarMapel::whereIn('id_kelas', $classIds)->pluck('id_mapel')->unique();
        $mapels = Mapel::whereIn('id', $mapelIds)
            ->orderBy('kategori', 'asc')
            ->orderBy('nama_mapel', 'asc')
            ->get();

        // Get All Students in this Jenjang/Grade
        $students = \App\Models\AnggotaKelas::whereIn('id_kelas', $classIds)
            ->with(['siswa', 'kelas'])
            ->get()
            ->sortBy(function($ak) {
                return $ak->kelas->nama_kelas . '_' . $ak->siswa->nama_lengkap;
            });

        $gradeLabel = $targetGrade ? "Kelas{$targetGrade}" : $jenjang;
        $filename = "Template_Global_{$gradeLabel}_{$activeYear->nama_tahun}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($students, $mapels, $jenjang, $periods) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ["PETUNJUK: Jangan ubah ID Siswa/Mapel. Kolom ID KELAS wajib sesuai."]);
            
            // Row 2: Headers
            $headerRow = ['NO', 'ID KELAS', 'NAMA KELAS', 'NIS', 'NAMA SISWA'];
            
            foreach ($mapels as $mapel) {
                $basePrefix = "[{$mapel->id}] {$mapel->nama_mapel}";
                foreach ($periods as $p) {
                    $pSuffix = $periods->count() > 1 ? " ({$p->nama_periode})" : "";
                    
                    $headerRow[] = "$basePrefix$pSuffix (Harian)";
                    $headerRow[] = ($jenjang == 'MI') ? "$basePrefix$pSuffix (Ujian Cawu)" : "$basePrefix$pSuffix (PTS)";
                    if ($jenjang == 'MTS') $headerRow[] = "$basePrefix$pSuffix (PAS/PAT)";
                }
            }

            // 2. Attendance & Personality Columns
            foreach ($periods as $p) {
                 $pSuffix = $periods->count() > 1 ? " ({$p->nama_periode})" : "";
                 $headerRow[] = "Sakit$pSuffix";
                 $headerRow[] = "Izin$pSuffix";
                 $headerRow[] = "Tanpa Keterangan$pSuffix";
                 $headerRow[] = "Sikap: Kelakuan$pSuffix";
                 $headerRow[] = "Sikap: Kerajinan$pSuffix";
                 $headerRow[] = "Sikap: Kebersihan$pSuffix";
            }
            
            fputcsv($file, $headerRow);

            foreach ($students as $index => $ak) {
                $row = [
                    $index + 1,
                    $ak->id_kelas,
                    $ak->kelas->nama_kelas,
                    $ak->siswa->nis_lokal,
                    $ak->siswa->nama_lengkap
                ];
                
                // Empty slots
                foreach ($mapels as $m) {
                    foreach ($periods as $p) {
                         $row[] = ''; 
                         $row[] = ''; 
                         if ($jenjang == 'MTS') $row[] = ''; 
                    }
                }

                // Empty slots for Attendance & Personality
                foreach ($periods as $p) {
                    $row[] = ''; $row[] = ''; $row[] = ''; // S, I, A
                    $row[] = 'Baik'; $row[] = 'Baik'; $row[] = 'Baik'; // Attitude
                }
                fputcsv($file, $row);
            }
            fclose($file);
        }, 200, $headers);
    }

    public function previewGlobal(Request $request, $jenjang)
    {
        $request->validate(['file' => 'required|file|mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,text/plain,application/csv,text/comma-separated-values|max:5120']);
        $jenjang = strtoupper($jenjang);
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        $file = $request->file('file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        if (count($rows) < 3) return back()->with('error', 'File terlalu pendek.');

        $headerRow = null; 
        $headerRowIndex = -1;

        // Find Header Row (row containing 'NIS' and 'NAMA SISWA')
        foreach ($rows as $index => $r) {
            $rowString = implode(' ', array_map('strtoupper', $r));
            if (str_contains($rowString, 'NIS') && (str_contains($rowString, 'NAMA SISWA') || str_contains($rowString, 'SISWA'))) {
                $headerRow = $r;
                $headerRowIndex = $index;
                break;
            }
        }

        if (!$headerRow) {
            // Fallback for empty/weird files: assume row 1 if exists, else fail
            if (isset($rows[1])) {
                $headerRow = $rows[1];
                $headerRowIndex = 1;
            } else {
                 return back()->with('error', 'Format file tidak dikenali. Header tidak ditemukan.');
            }
        }
        
        // Parse Period & Mapel Cols
        $allPeriods = Periode::where('id_tahun_ajaran', $activeYear->id)->where('lingkup_jenjang', $jenjang)->get();
        // Fallback period
        $defaultPeriod = $allPeriods->first();

        $mapelCols = []; 
        foreach ($headerRow as $idx => $col) {
            if (preg_match('/\[(\d+)\]/', $col, $matches)) {
                
                $mapelId = $matches[1];
                $type = stripos($col, 'Ujian Cawu') !== false || stripos($col, 'PTS') !== false ? 'uts' : 
                       (stripos($col, 'PAS') !== false || stripos($col, 'PAT') !== false ? 'uas' : 'harian');
                
                $pId = $defaultPeriod->id ?? 0;
                foreach ($allPeriods as $p) {
                    if (stripos($col, "({$p->nama_periode})") !== false) {
                        $pId = $p->id;
                        break;
                    }
                }
                
                $mapelCols[$idx] = ['mapel_id' => $mapelId, 'period_id' => $pId, 'type' => $type];
                $mapelCols[$idx] = ['mapel_id' => $mapelId, 'period_id' => $pId, 'type' => $type];
            } else {
                // Check for Non-Academic
                // Detect Period
                $pId = 0;
                foreach ($allPeriods as $p) {
                    if (stripos($col, "({$p->nama_periode})") !== false) {
                        $pId = $p->id;
                        break;
                    }
                }
                // If Period matches failed, use Default Active Period (First one or explicitly query active)
                if ($pId === 0) {
                     $activeP = $allPeriods->firstWhere('status', 'aktif');
                     $pId = $activeP ? $activeP->id : ($allPeriods->first()->id ?? 0);
                }
                
                $field = null;
                // Attendance Keywords
                if (stripos($col, 'Sakit') !== false) $field = 'sakit';
                elseif (stripos($col, 'Izin') !== false) $field = 'izin';
                elseif (stripos($col, 'Tanpa Keterangan') !== false || stripos($col, 'Alpa') !== false || stripos($col, 'Alpha') !== false) $field = 'tanpa_keterangan';
                // Personality Keywords
                elseif (stripos($col, 'Kelakuan') !== false) $field = 'kelakuan';
                elseif (stripos($col, 'Kerajinan') !== false) $field = 'kerajinan';
                elseif (stripos($col, 'Kebersihan') !== false) $field = 'kebersihan';
                
                if ($field) {
                    $mapelCols[$idx] = ['is_non_academic' => true, 'field' => $field, 'period_id' => $pId];
                }
            }
        }
        
        $mapelNames = Mapel::whereIn('id', array_unique(array_column($mapelCols, 'mapel_id')))->pluck('nama_mapel', 'id');
        
        // Parse Data
        $parsedData = [];
        $importErrors = [];
        
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (count($row) < 5) continue; // Min cols

            $kelasId = trim($row[1]);
            $nis = trim($row[3]);
            
            $siswa = Siswa::where('nis_lokal', $nis)->first();
            if (!$siswa) {
                $importErrors[] = "Row " . ($i+1) . ": NIS $nis not found.";
                continue;
            }
            
            $inClass = \App\Models\AnggotaKelas::where('id_kelas', $kelasId)->where('id_siswa', $siswa->id)->exists();
            if (!$inClass) {
                $importErrors[] = "Row " . ($i+1) . ": Siswa $nis tidak ada di Kelas ID $kelasId.";
            }

            $currentGrades = [];
            foreach ($mapelCols as $colIdx => $meta) {
                 if (isset($meta['is_non_academic'])) {
                     $val = isset($row[$colIdx]) ? trim($row[$colIdx]) : null;
                     if ($val !== null && $val !== '') {
                         // Parse int for attendance, keep string for attitude
                         if (in_array($meta['field'], ['sakit', 'izin', 'tanpa_keterangan'])) $val = (int)$val;
                         $currentGrades['non_academic'][$meta['period_id']][$meta['field']] = $val;
                     }
                 } else {
                     $val = isset($row[$colIdx]) ? floatval($row[$colIdx]) : 0;
                     if ($val > 100) $val = 100; if ($val < 0) $val = 0;
                     $currentGrades['grades'][$meta['period_id']][$meta['mapel_id']][$meta['type']] = $val;
                 }
            }
            
            $parsedData[] = [
                'siswa' => $siswa,
                'kelas_id' => $kelasId, 
                'kelas_id' => $kelasId, 
                'grades' => $currentGrades['grades'] ?? [],
                'non_academic' => $currentGrades['non_academic'] ?? []
            ];
        }
        
        // Fetch Class Names
        $kelasIds = array_unique(array_column($parsedData, 'kelas_id'));
        $kelasNames = \App\Models\Kelas::whereIn('id', $kelasIds)->pluck('nama_kelas', 'id');
        
        $importKey = 'global_import_' . Auth::id() . '_' . time();
        Cache::put($importKey, ['data' => $parsedData, 'jenjang' => $jenjang], 3600);

        return view('admin.grade_import.preview_global', compact('parsedData', 'importErrors', 'importKey', 'jenjang', 'mapelNames', 'mapelCols', 'allPeriods', 'kelasNames'));
    }

    public function storeGlobal(Request $request)
    {
        $cached = Cache::get($request->import_key);
        if (!$cached) return redirect()->back()->with('error', 'Expired.');
        
        $data = $cached['data'];
        $jenjang = $cached['jenjang'];
        $activeYear = TahunAjaran::where('status', 'aktif')->first();

        // Note: Global Import usually done by Admin, so Trait will allow it. 
        // But if Teacher accesses this (unlikely but possible), it needs protection.
        // Also the Period ID varies per row/column in Global Import!
        // This is complex. For now, let's assume Global Import is ADMIN ONLY feature.
        // But let's verify role.
        if (Auth::user()->role !== 'admin') {
             return redirect()->back()->with('error', 'Fitur ini khusus Admin.');
        }

        $bobot = BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)->where('jenjang', $jenjang)->first();
        $bH = $bobot->bobot_harian ?? 1; 
        $bT = $bobot->bobot_uts_cawu ?? 1;
        $bA = $bobot->bobot_uas ?? 1;
        $realBH = $bH/100; $realBT = $bT/100; $realBA = $bA/100;
        
        $rules = DB::table('predikat_nilai')->where('jenjang', $jenjang)->orderBy('min_score', 'desc')->get();
        $grading = DB::table('grading_settings')->where('jenjang', $jenjang)->first();
        $shouldRound = $grading->rounding_enable ?? true;
        
        $count = 0;
        
        foreach ($data as $row) {
            $sId = $row['siswa']->id;
            $cId = $row['kelas_id'];
            
            foreach ($row['grades'] as $pId => $mapels) {
                foreach ($mapels as $mId => $scores) {
                    $h = $scores['harian'] ?? 0; 
                    $u = $scores['uts'] ?? 0; 
                    $a = $scores['uas'] ?? 0;
                    
                    $final = ($h * $realBH) + ($u * $realBT) + ($a * $realBA);
                    $final = $shouldRound ? round($final) : round($final, 2);
                    
                    $predikat = 'D';
                    foreach ($rules as $r) { if ($final >= $r->min_score) { $predikat = $r->grade; break; }}
                    
                    NilaiSiswa::updateOrCreate(
                        ['id_siswa' => $sId, 'id_mapel' => $mId, 'id_periode' => $pId],
                        [
                            'id_kelas' => $cId, // Ensure class ID is updated if moved
                            'id_guru' => Auth::id(),
                            'nilai_harian' => $h, 'nilai_uts_cawu' => $u, 'nilai_uas' => $a,
                            'nilai_akhir' => $final, 'nilai_akhir_asli' => $final, 'is_katrol' => false,
                            'predikat' => $predikat, 'status' => 'draft'
                        ]
                    );
                    $count++;
                }
            }

            
            // Save Non-Academic (Attendance & Attitude) - MOVED INSIDE LOOP
            if (isset($row['non_academic'])) {
                foreach ($row['non_academic'] as $pId => $fields) {
                    if (!empty($fields)) {
                        \App\Models\CatatanKehadiran::updateOrCreate(
                            ['id_siswa' => $sId, 'id_kelas' => $cId, 'id_periode' => $pId],
                            array_merge($fields, ['updated_at' => now()])
                        );
                    }
                }
            }
        }
        
        Cache::forget($request->import_key);
        return redirect()->route('dashboard')->with('success', "Sukses import global ($count nilai).");
    }

}
