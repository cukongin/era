<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Jenjang;
use Illuminate\Http\Request;

class MasterStudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Siswa::with(['jenjang', 'kelas_saat_ini.kelas']);

        // Filter by Level (MI/MTs)
        if ($request->has('level_id') && $request->level_id != 'all' && $request->level_id != '') {
            $query->where('id_jenjang', $request->level_id);
        }

        // TAB FILTER
        $tab = $request->get('tab', 'active');
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
        
        if ($tab == 'active') {
             $query->where('status_siswa', 'aktif');
             // Logic: ACTIVE AND Has Class for THIS Active Year
             if ($activeYear) {
                $query->whereHas('anggota_kelas', function($q) use ($activeYear) {
                    $q->whereHas('kelas', function($k) use ($activeYear) {
                        $k->where('id_tahun_ajaran', $activeYear->id);
                    });
                });
             }
        } elseif ($tab == 'new') {
            $query->where('status_siswa', 'aktif');
            // Logic: ACTIVE but NOT in any Class for THIS Active Year
            if ($activeYear) {
                $query->whereDoesntHave('anggota_kelas', function($q) use ($activeYear) {
                    $q->whereHas('kelas', function($k) use ($activeYear) {
                        $k->where('id_tahun_ajaran', $activeYear->id);
                    });
                });
            }
        } elseif ($tab == 'inactive') {
            $query->whereIn('status_siswa', ['lulus', 'mutasi', 'keluar', 'non-aktif', 'meninggal']);
        }

        // Filter by Status (Additional specific filter if needed inside tab)
        if ($request->has('status') && $request->status != 'all' && $request->status != '') {
            $query->where('status_siswa', $request->status);
        }
        
        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis_lokal', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(20)->withQueryString();
        $levels = Jenjang::all();
        
        // Statistics
        $stats = [
            'total' => Siswa::count(),
            'mi' => Siswa::whereHas('jenjang', function($q) { $q->where('kode', 'MI'); })->count(),
            'mts' => Siswa::whereHas('jenjang', function($q) { $q->where('kode', 'MTS'); })->count(),
            'inactive' => Siswa::whereIn('status_siswa', ['lulus', 'mutasi', 'keluar', 'non-aktif', 'meninggal'])->count(),
            'new' => 0, // Default
            'all_active' => 0 // Default
        ];

        if ($activeYear) {
            $stats['all_active'] = Siswa::where('status_siswa', 'aktif')
                ->whereHas('anggota_kelas', function($q) use ($activeYear) {
                    $q->whereHas('kelas', function($k) use ($activeYear) {
                        $k->where('id_tahun_ajaran', $activeYear->id);
                    });
                })->count();

            $stats['new'] = Siswa::where('status_siswa', 'aktif')
                ->whereDoesntHave('anggota_kelas', function($q) use ($activeYear) {
                    $q->whereHas('kelas', function($k) use ($activeYear) {
                        $k->where('id_tahun_ajaran', $activeYear->id);
                    });
                })->count();
        }

        return view('master.students.index', compact('students', 'levels', 'stats', 'tab'));
    }

    public function show($id)
    {
        $student = Siswa::with(['jenjang', 'kelas_saat_ini.kelas', 'riwayat_kelas.kelas.tahun_ajaran', 'riwayat_kelas.kelas.wali_kelas'])->findOrFail($id);
        
        // Fetch Grade History
        $grades = \App\Models\NilaiSiswa::with('periode')
            ->where('id_siswa', $id)
            ->get()
            ->groupBy('id_kelas');

        $gradeHistory = [];
        
        foreach($student->riwayat_kelas as $riwayat) {
            $classId = $riwayat->id_kelas;
            $classGrades = $grades[$classId] ?? collect([]);
            
            // Group by Period (Semester 1, 2, or Cawu 1, 2, 3)
            $byPeriod = $classGrades->groupBy(function($item) {
                return $item->periode->nama_periode ?? 'Other';
            });
            
            $averages = [];
            foreach($byPeriod as $periodName => $items) {
                // Calculate Average of Nilai Akhir (Exclude 0 values)
                $validItems = $items->where('nilai_akhir', '>', 0);
                
                if ($validItems->count() > 0) {
                    $avg = $validItems->avg('nilai_akhir');
                    $averages[$periodName] = number_format($avg, 2); 
                } else {
                     $averages[$periodName] = '0.00';
                }
            }
            
            $gradeHistory[$classId] = [
                'periods' => $averages
            ];
        }

        return view('master.students.show', compact('student', 'gradeHistory'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required',
            'nis_lokal' => 'nullable|unique:siswa,nis_lokal',
        ]);
        
        Siswa::create([
            'nama_lengkap' => $request->nama_lengkap,
            'nis_lokal' => $request->nis_lokal,
            'nisn' => $request->nisn,
            'nik' => $request->nik,
            'jenis_kelamin' => $request->jenis_kelamin ?? 'L',
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat_lengkap' => $request->alamat_lengkap,
            'nama_ayah' => $request->nama_ayah,
            'nama_ibu' => $request->nama_ibu,
            'no_telp_ortu' => $request->no_telp_ortu,
            'id_jenjang' => $request->id_jenjang,
            'tahun_masuk' => $request->tahun_masuk,
            'status_siswa' => 'aktif',
        ]);

        return back()->with('success', 'Siswa berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        
        $request->validate([
            'nama_lengkap' => 'required',
            'nis_lokal' => 'nullable|unique:siswa,nis_lokal,' . $id,
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except(['foto', '_token', '_method']);

        // Handle Photo Upload
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = 'student_' . $siswa->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/students'), $filename);
            $data['foto'] = 'storage/students/' . $filename;
        }

        $siswa->update($data);

        return back()->with('success', 'Data Siswa berhasil diperbarui');
    }

    public function destroy($id)
    {
        Siswa::findOrFail($id)->delete();
        return back()->with('success', 'Data Siswa berhasil dihapus');
    }

    // Logic "Migrasi ke MTs" (Simple implementation: Update Level ID)
    // Generic Status Update
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,lulus,mutasi,keluar,non-aktif',
            'catatan' => 'nullable|string'
        ]);

        $siswa = Siswa::findOrFail($id);
        
        // If status changes to inactive, maybe remove from specific active class assignments if needed
        // For now just update status. The filtering logic elsewhere will handle visibility.
        
        $siswa->update([
            'status_siswa' => $request->status
            // 'catatan_status' => $request->catatan // If column exists later
        ]);

        return back()->with('success', "Status siswa berhasil diubah menjadi: " . strtoupper($request->status));
    }

    public function restore($id)
    {
        $siswa = Siswa::findOrFail($id);
        $siswa->update(['status_siswa' => 'aktif']);

        // Try to Restore to Last Class
        $lastEnrollment = \App\Models\AnggotaKelas::where('id_siswa', $id)
            ->orderBy('id', 'desc')
            ->first();

        $message = "Siswa berhasil diaktifkan kembali.";

        if ($lastEnrollment) {
            $lastClass = $lastEnrollment->kelas;
            $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
            
            if ($activeYear && $lastClass) {
                // Find matching class in Active Year
                $targetClass = \App\Models\Kelas::where('id_tahun_ajaran', $activeYear->id)
                    ->where('nama_kelas', $lastClass->nama_kelas)
                    ->where('id_jenjang', $lastClass->id_jenjang)
                    ->first();
                
                if ($targetClass) {
                    \App\Models\AnggotaKelas::firstOrCreate([
                        'id_kelas' => $targetClass->id,
                        'id_siswa' => $id
                    ], ['status' => 'aktif']);
                    
                    $message .= " Dan dikembalikan ke kelas {$targetClass->nama_kelas}.";
                } else {
                    $message .= " Namun kelas terakhir ({$lastClass->nama_kelas}) tidak ditemukan di tahun aktif.";
                }
            }
        }

        return back()->with('success', $message);
    }

    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_siswa.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Nama Lengkap', 'NIS (Lokal)', 'NISN', 'Jenjang (MI/MTS)', 'Gender (L/P)', 'Tempat Lahir', 'Tanggal Lahir (YYYY-MM-DD)', 'Nama Ayah', 'Nama Ibu', 'Pekerjaan Ayah', 'Pekerjaan Ibu', 'Alamat'];
        $example = ['Ahmad Yusuf', '12345', '0012345678', 'MI', 'L', 'Surabaya', '2015-05-20', 'Budi', 'Siti', 'Wiraswasta', 'IRT', 'Jl. Mawar No. 1'];

        $callback = function() use ($columns, $example) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $example);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $rows = [];
        
        // CASE A: Uploaded File
        if ($request->hasFile('file')) {
            $request->validate(['file' => 'required|mimes:csv,txt|max:2048']);
            $file = $request->file('file');
            $csvData = array_map('str_getcsv', file($file));
            $header = array_shift($csvData); // Skip Header
            
            // Convert to associating array structure
            foreach($csvData as $idx => $row) {
                 if (count($row) < 4) continue;
                 $rows[] = [
                    'nama' => trim($row[0]),
                    'nis' => trim($row[1]),
                    'nisn' => trim($row[2]),
                    'jenjang' => trim($row[3]),
                    'gender' => trim($row[4]),
                    'tempat_lahir' => trim($row[5]),
                    'tanggal_lahir' => trim($row[6]),
                    'nama_ayah' => trim($row[7]),
                    'nama_ibu' => trim($row[8]),
                    'pekerjaan_ayah' => trim($row[9]),
                    'pekerjaan_ibu' => trim($row[10]),
                    'alamat' => trim($row[11]),
                 ];
            }
        } 
        // CASE B: Re-submission (Fixing Errors)
        elseif ($request->has('rows')) {
            $rows = $request->rows;
        }
        else {
            return back()->with('error', 'Tidak ada data yang diproses.');
        }

        $validData = [];
        $errors = [];
        $allData = []; // To repopulate form

        foreach ($rows as $idx => $row) {
            $rowError = null;
            $nama = $row['nama'] ?? '';
            $nis = $row['nis'] ?? '';
            $nisn = $row['nisn'] ?? '';
            $jenjangKode = strtoupper($row['jenjang'] ?? '');

            // 1. Validate Required
            if (empty($nama) || empty($nis)) {
                $rowError = 'Nama dan NIS Wajib diisi.';
            }
            // 2. Validate Unique NIS (Database Check)
            elseif (Siswa::where('nis_lokal', $nis)->exists()) {
                 $rowError = "NIS '$nis' sudah terdaftar.";
            }

            // 3. Resolve Jenjang
            $jenjang = Jenjang::where('kode', $jenjangKode)->first();
            if (!$jenjang) $jenjang = Jenjang::first();

            // 4. Validate & Normalize Date
            $tglLahir = trim($row['tanggal_lahir'] ?? '');
            $parsedDate = null;
            if (!empty($tglLahir)) {
                try {
                    // Try parsing 'd/m/Y' first (Indonesian format)
                    if (strpos($tglLahir, '/') !== false) {
                         $parts = explode('/', $tglLahir);
                         // Check if d/m/Y (3 parts)
                         if (count($parts) == 3) {
                             // Assuming d/m/Y
                             $parsedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $tglLahir)->format('Y-m-d');
                         }
                    } else {
                        // Standard Y-m-d or other parsable formats
                         $parsedDate = \Carbon\Carbon::parse($tglLahir)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    // Try alternative formats if Carbon fails standard parse
                     try {
                        $parsedDate = \Carbon\Carbon::parse($tglLahir)->format('Y-m-d');
                     } catch (\Exception $ex) {
                        $rowError = "Format Tanggal Lahir salah ('$tglLahir'). Gunakan YYYY-MM-DD atau DD/MM/YYYY.";
                     }
                }
            }

            $cleanRow = [
                'nama_lengkap' => $nama,
                'nis_lokal' => $nis,
                'nisn' => empty($nisn) ? null : $nisn,
                'id_jenjang' => $jenjang->id,
                'jenis_kelamin' => strtoupper($row['gender'] ?? '') == 'P' ? 'P' : 'L',
                'tempat_lahir' => $row['tempat_lahir'] ?? '',
                'tanggal_lahir' => $parsedDate,
                'nama_ayah' => $row['nama_ayah'] ?? '',
                'nama_ibu' => $row['nama_ibu'] ?? '',
                'pekerjaan_ayah' => $row['pekerjaan_ayah'] ?? '',
                'pekerjaan_ibu' => $row['pekerjaan_ibu'] ?? '',
                'alamat' => $row['alamat'] ?? '',
                // Keep Original for Form
                '_original' => $row,
                '_error' => $rowError
            ];

            $allData[$idx] = $cleanRow;

            if ($rowError) {
                $errors[] = ['index' => $idx, 'message' => $rowError, 'data' => $row];
            } else {
                $validData[] = array_diff_key($cleanRow, ['_original' => 0, '_error' => 0]);
            }
        }

        $isValid = count($errors) == 0 && count($validData) > 0;
        $importKey = 'import_siswa_' . auth()->id();
        
        // Cache Valid Data Only for "Process" step
        if (count($validData) > 0) {
            \Illuminate\Support\Facades\Cache::put($importKey, $validData, 3600);
        }

        return view('master.students.import_preview', [
            'isValid' => $isValid,
            'validRows' => count($validData),
            'totalRows' => count($allData),
            'importErrors' => $errors,
            'allData' => $allData, // Send all data back for the table
            'importKey' => $importKey
        ]);
    }

    public function processImport(Request $request)
    {
        $key = $request->import_key;
        $data = \Illuminate\Support\Facades\Cache::get($key);

        if (!$data) {
            return redirect()->route('master.students.index')->with('error', 'Sesi import kadaluarsa or data kosong.');
        }

        $count = 0;
        foreach ($data as $row) {
            // Defensive Date Parsing (in case logic skipped in validation or stale cache)
            if (!empty($row['tanggal_lahir'])) {
                try {
                     // If still contains slash, it wasn't parsed
                     if (strpos($row['tanggal_lahir'], '/') !== false) {
                        $row['tanggal_lahir'] = \Carbon\Carbon::createFromFormat('d/m/Y', $row['tanggal_lahir'])->format('Y-m-d');
                     } else {
                        // Ensure Y-m-d
                        $row['tanggal_lahir'] = \Carbon\Carbon::parse($row['tanggal_lahir'])->format('Y-m-d');
                     }
                } catch (\Exception $e) {
                    $row['tanggal_lahir'] = null; // Fallback to null if unparsable
                }
            }

            // Final check to avoid double submit issues
            if (!Siswa::where('nis_lokal', $row['nis_lokal'])->exists()) {
                Siswa::create($row);
                $count++;
            }
        }

        \Illuminate\Support\Facades\Cache::forget($key);

        return redirect()->route('master.students.index')->with('success', "Sukses! $count Siswa berhasil ditambahkan.");
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:siswa,id'
        ]);

        $count = Siswa::whereIn('id', $request->ids)->delete();

        return back()->with('success', "$count Data siswa berhasil dihapus.");
    }

    public function updateHistoryStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:aktif,pindah,naik_kelas,tinggal_kelas,lulus,keluar,mutasi'
        ]);

        $member = \App\Models\AnggotaKelas::findOrFail($id);
        $member->status = $request->status;
        $member->save();

        return back()->with('success', 'Status riwayat kelas berhasil diperbarui.');
    }
}
