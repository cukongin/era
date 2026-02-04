<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DataGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MasterTeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'teacher')->with(['data_guru', 'kelas_wali', 'mapel_ajar']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $teachers = $query->paginate(20);

        return view('master.teachers.index', compact('teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'foto' => 'nullable|image|mimetypes:image/jpeg,image/png,image/gif|max:2048|dimensions:min_width=100,min_height=100',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('password'), // Default password
            'role' => 'teacher',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $safeName = preg_replace('/[^a-zA-Z0-9_.]/', '', $file->getClientOriginalName()); 
            $filename = 'teacher_' . $user->id . '_' . time() . '_' . $safeName;
            $file->move(public_path('storage/teachers'), $filename);
            $fotoPath = 'storage/teachers/' . $filename;
        }

        $guru = DataGuru::create([
            'id_user' => $user->id,
            'nik' => $request->nik,
            'npwp' => $request->npwp,
            'pendidikan_terakhir' => $request->pendidikan_terakhir,
            'riwayat_pesantren' => $request->riwayat_pesantren, // Legacy/Simple Text
            'mapel_ajar_text' => $request->mapel_ajar_text,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'foto' => $fotoPath,
        ]);

        // Process Dynamic Education History if available
        if ($request->has('pendidikan') && is_array($request->pendidikan)) {
            foreach ($request->pendidikan as $edu) {
                if (!empty($edu['nama_instansi'])) {
                    \App\Models\RiwayatPendidikanGuru::create([
                        'data_guru_id' => $guru->id,
                        'jenjang' => $edu['jenjang'] ?? null,
                        'nama_instansi' => $edu['nama_instansi'],
                        'tahun_masuk' => $edu['tahun_masuk'] ?? null,
                        'tahun_lulus' => $edu['tahun_lulus'] ?? null,
                    ]);
                }
            }
        }

        return back()->with('success', 'Guru berhasil ditambahkan');
    }

    public function downloadTemplate()
    {
        // New Headers based on User Request
        $headers = [
            'NIK',
            'Nama',
            'Jenis Kelamin (L/P)',
            'Tempat lahir',
            'Tanggal lahir (dd-mm-yyyy)',
            'Alamat',
            'NPWP',
            'Riwayat Pendidikan (Format: Jenjang;Nama;Masuk;Lulus (Pemisah | ))',
            'Mapel yang di ajarkan',
            'Email (System Login)'
        ];

        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            // Example Row
            fputcsv($file, [
                "'3500000000000001", // NIK
                "Kyai Ahmad",
                "L",
                "Jombang",
                "01-01-1970",
                "Jl. Pesantren No. 1",
                "'1234567890", // NPWP
                "S1;UIN Malang;2010;2014|Pesantren;Tebuireng;2000;2006", // Import Format
                "Fiqih, Aqidah Akhlak",
                "ahmad@nurulainy.sch.id"
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=form_data_guru_baru+pendidikan.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx|max:2048'
        ]);

        $file = $request->file('file');
        try {
            $csvData = array_map('str_getcsv', file($file));
            $header = array_shift($csvData); 
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca CSV.');
        }

        $count = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            if (count($row) < 3) continue; // Basic validation

            // Mapping (Based on New Header Order with Education Column)
            // 0: NIK, 1: Nama, 2: JK, 3: Tempat, 4: Tgl, 5: Alamat, 6: NPWP, 7: RiwayatPendidikan, 8: Mapel, 9: Email
            
            $nik = trim(str_replace("'", "", $row[0] ?? ''));
            $name = trim($row[1] ?? '');
            $jk = strtoupper(trim($row[2] ?? ''));
            $tempat = trim($row[3] ?? '');
            $tgl = trim($row[4] ?? '');
            $alamat = trim($row[5] ?? '');
            $npwp = trim(str_replace("'", "", $row[6] ?? ''));
            $rawPendidikan = trim($row[7] ?? ''); // New Format
            $mapel = trim($row[8] ?? '');
            $email = trim($row[9] ?? ''); 

            if (empty($name)) continue;

            if (empty($email)) {
                // generate short email: shortest word of name + random 3 digits
                $nameParts = explode(' ', strtolower(preg_replace('/[^a-zA-Z ]/', '', $name)));
                $shortest = $nameParts[0] ?? 'guru';
                foreach ($nameParts as $part) {
                    if (strlen($part) >= 3 && strlen($part) < strlen($shortest)) {
                        $shortest = $part;
                    }
                }
                // Fallback if all parts < 3 chars (e.g. "Al")
                if (strlen($shortest) < 3 && count($nameParts) > 0) {
                     $shortest = $nameParts[0]; 
                }
                
                $candidate = $shortest . '@nurulainy.sch.id';
                $uniqueFound = false;
                
                // First try without numbers
                if (!User::where('email', $candidate)->exists()) {
                    $email = $candidate;
                    $uniqueFound = true;
                }

                // If exists, append 2 digit random number
                while(!$uniqueFound) {
                    $candidate = $shortest . rand(10, 99) . '@nurulainy.sch.id';
                    if (!User::where('email', $candidate)->exists()) {
                        $email = $candidate;
                        $uniqueFound = true;
                    }
                }
            }

            if (User::where('email', $email)->exists()) {
                $errors[] = "Baris " . ($index + 2) . ": Email/User '$email' sudah ada.";
                continue;
            }

            // Date Parsing (Support dd-mm-yyyy, d/m/Y, yyyy-mm-dd)
            $parsedDate = null;
            if (!empty($tgl)) {
                // Remove potential quotes first
                $tgl = str_replace("'", "", $tgl);
                try { 
                    // Try exact ID format first
                    $parsedDate = \Carbon\Carbon::createFromFormat('d-m-Y', $tgl)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        // Try slash separator
                        $parsedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $tgl)->format('Y-m-d');
                    } catch(\Exception $e2) {
                        try {
                            // Fallback to standard parser
                             $parsedDate = \Carbon\Carbon::parse($tgl)->format('Y-m-d');
                        } catch (\Exception $e3) {}
                    }
                }
            }

            try {
                \Illuminate\Support\Facades\DB::beginTransaction();

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('123456'),
                    'role' => 'teacher'
                ]);

                $guru = DataGuru::create([
                    'id_user' => $user->id,
                    'nik' => $nik,
                    'jenis_kelamin' => in_array($jk, ['L', 'P']) ? $jk : null,
                    'tempat_lahir' => $tempat,
                    'tanggal_lahir' => $parsedDate,
                    'alamat' => $alamat,
                    'npwp' => $npwp,
                    'mapel_ajar_text' => $mapel,
                    // 'pendidikan_terakhir' => ... (Could extract highest level if needed, but let's leave it blank or take first one)
                ]);

                // Parse Education String: "S1;UIN;2010;2014|SMA;SMAN1;2007;2010"
                if (!empty($rawPendidikan)) {
                    $entries = explode('|', $rawPendidikan);
                    foreach ($entries as $entry) {
                        $parts = explode(';', $entry);
                        // Expected: Jenjang, Nama, Masuk, Lulus
                        if (count($parts) >= 2) {
                            \App\Models\RiwayatPendidikanGuru::create([
                                'data_guru_id' => $guru->id,
                                'jenjang' => trim($parts[0] ?? ''),
                                'nama_instansi' => trim($parts[1] ?? ''),
                                'tahun_masuk' => trim($parts[2] ?? null),
                                'tahun_lulus' => trim($parts[3] ?? null),
                            ]);
                        }
                    }
                }

                \Illuminate\Support\Facades\DB::commit();
                $count++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                $errors[] = "Baris " . ($index+2) . ": Gagal simpan (" . $e->getMessage() . ")";
            }
        }

        $msg = "$count Guru berhasil diimport (Dengan Riwayat Pendidikan).";
        if (count($errors) > 0) $msg .= " Ada " . count($errors) . " error.";

        return back()->with('success', $msg)->with('import_errors', $errors);
    }

    public function show($id)
    {
        $teacher = User::with(['data_guru.riwayat_pendidikan', 'mapel_ajar.kelas.tahun_ajaran', 'mapel_ajar.mapel', 'kelas_wali'])->findOrFail($id);
        $activeYear = \App\Models\TahunAjaran::where('status', 'aktif')->first();
        
        return view('master.teachers.show', compact('teacher', 'activeYear'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // Update User
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Handle Photo
            $fotoPath = $user->data_guru->foto;
            if ($request->hasFile('foto')) {
                // Delete old photo if exists
                if ($fotoPath && file_exists(public_path($fotoPath))) {
                    unlink(public_path($fotoPath));
                }
                $file = $request->file('foto');
                $filename = 'teacher_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('storage/teachers'), $filename);
                $fotoPath = 'storage/teachers/' . $filename;
            }

            // Update Data Guru
            $guruAttributes = [
                'nik' => $request->nik,
                'nuptk' => $request->nuptk,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'foto' => $fotoPath,
                'mapel_ajar_text' => $request->mapel_ajar_text,
                'pendidikan_terakhir' => $request->pendidikan_terakhir,
                'riwayat_pesantren' => $request->riwayat_pesantren,
            ];

            if ($user->data_guru) {
                $user->data_guru->update($guruAttributes);
                $guruId = $user->data_guru->id;
            } else {
                $guru = DataGuru::create(array_merge(['id_user' => $user->id], $guruAttributes));
                $guruId = $guru->id;
            }

            // Sync Education History
            // Strategy: Delete all existing and re-insert (Simple & Effective for small lists)
            if ($request->has('pendidikan') && is_array($request->pendidikan)) {
                $user->data_guru->riwayat_pendidikan()->delete();
                
                foreach ($request->pendidikan as $edu) {
                    if (!empty($edu['nama_instansi'])) { // Filter empty rows
                        \App\Models\RiwayatPendidikanGuru::create([
                            'data_guru_id' => $guruId,
                            'jenjang' => $edu['jenjang'] ?? null,
                            'nama_instansi' => $edu['nama_instansi'],
                            'tahun_masuk' => $edu['tahun_masuk'] ?? null,
                            'tahun_lulus' => $edu['tahun_lulus'] ?? null,
                        ]);
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Data guru berhasil diperbarui');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            // Optional: Check if teacher has dependencies (classes, grades)
            // But user requested "hapus", so we default to forceful delete or cascade.
            // DataGuru constraint is cascade on delete usually, but check User.
            
            $user->delete(); // This should cascade to DataGuru usually if foreign key set, or we assume User delete is enough.
            // If DataGuru foreign key is on User(id), it cascades if migration set.
            // Earlier migration: $table->foreignId('id_user')->constrained('users')->onDelete('cascade'); -> Yes.
            
            return back()->with('success', 'Guru berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function destroyAll()
    {
        try {
            $count = 0;
            // Delete all users with role teacher
            $teachers = User::where('role', 'teacher')->get();
            foreach ($teachers as $teacher) {
                $teacher->delete();
                $count++;
            }
            return back()->with('success', "Berhasil menghapus $count data guru.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal reset data guru: ' . $e->getMessage());
        }
    }
}
