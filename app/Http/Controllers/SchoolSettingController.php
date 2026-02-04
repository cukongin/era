<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IdentitasSekolah;
use Illuminate\Support\Facades\Storage;

class SchoolSettingController extends Controller
{
    public function index()
    {
        $mi = IdentitasSekolah::where('jenjang', 'MI')->firstOrFail();
        $mts = IdentitasSekolah::where('jenjang', 'MTS')->firstOrFail();
        
        return view('settings.school', compact('mi', 'mts'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'jenjang' => 'required|in:MI,MTS',
            'nama_sekolah' => 'required',
            'kepala_madrasah' => 'required',
            'logo' => 'nullable|image|max:2048',
        ]);

        $school = IdentitasSekolah::where('jenjang', $request->jenjang)->firstOrFail();
        $data = $request->except(['logo', '_token']);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            // Unique filename to avoid cache issues or conflict
            $filename = 'logo_' . strtolower($request->jenjang) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/img'), $filename);
            $data['logo'] = 'assets/img/' . $filename;
        }

        $school->update($data);

        return back()->with('success', 'Identitas ' . $request->jenjang . ' berhasil diperbarui.');
    }
}
