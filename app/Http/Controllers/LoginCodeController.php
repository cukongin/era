<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AuditLog;

class LoginCodeController extends Controller
{
    // Tampilkan Halaman Login Kode
    public function showLoginForm()
    {
        return view('auth.login-code');
    }

    // Proses Login dengan Kode
    // Proses Login dengan Kode (Real-time Google Sheet Check)
    public function login(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string|size:6'
        ]);

        $inputCode = $request->input('access_code');
        
        // 1. Get Sheet ID
        $sheetId = \App\Models\GlobalSetting::val('teacher_sheet_id');

        if (!$sheetId) {
            return back()->withErrors(['access_code' => 'Sistem belum dikonfigurasi (Sheet ID Missing). Hubungi Admin.']);
        }

        // 2. Fetch CSV from Google
        $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv";

        try {
            // Set timeout 5 seconds
            $ctx = stream_context_create(['http' => ['timeout' => 5]]);
            $csvData = @file_get_contents($csvUrl, false, $ctx);

            if ($csvData === false) {
                throw new \Exception("Gagal menghubungi Google Sheets. Cek ID atau Koneksi.");
            }

            $lines = explode(PHP_EOL, $csvData);
            $foundUser = null;

            // 3. Parse & Find
            foreach ($lines as $line) {
                $data = str_getcsv($line);
                // Expecting: Col A = Nama, Col B = Kode
                if (count($data) < 2) continue;

                $sheetName = trim($data[0]);
                $sheetCode = trim($data[1]);

                // Verify Code
                if ($sheetCode === $inputCode) {
                    // Code Matched! Now find local user by Name
                    // Exact match preferred, fallback to email check if name fails
                    $foundUser = \App\Models\User::where('name', $sheetName)
                                ->orWhere('email', $sheetName) 
                                ->first();
                    
                    if ($foundUser) break;
                }
            }

            if ($foundUser) {
                Auth::login($foundUser);
                
                // Redirect based on Role / Position
                if ($foundUser->isWaliKelas()) {
                    return redirect()->route('walikelas.dashboard');
                }
                
                if ($foundUser->isTeacher()) {
                    return redirect()->route('teacher.dashboard');
                }

                if ($foundUser->isStaffTu()) {
                    return redirect()->route('tu.dashboard');
                }

                return redirect()->route('dashboard');
            } else {
                 return back()->withErrors(['access_code' => 'Kode Valid, tapi User tidak ditemukan di database aplikasi. Pastikan Nama di Sheet SAMA PERSIS dengan Nama User.']);
            }

        } catch (\Throwable $e) {
             return back()->withErrors(['access_code' => 'Error: ' . $e->getMessage()]);
        }
    }
}
