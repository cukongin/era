<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeadlineController extends Controller
{
    public function index()
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) return back()->with('error', 'Tahun Ajaran belum aktif.');

        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->orderBy('id', 'asc')
            ->get();

        // Get Whitelisted Teachers
        // For now raw query or create Model if needed. Using DB for simplicity or generic relation
        $whitelist = DB::table('grading_whitelist')
            ->join('users', 'grading_whitelist.id_guru', '=', 'users.id')
            ->join('periode', 'grading_whitelist.id_periode', '=', 'periode.id')
            ->select('grading_whitelist.*', 'users.name as guru_name', 'periode.nama_periode')
            ->orderBy('grading_whitelist.created_at', 'desc')
            ->get();

        return view('settings.deadline', compact('activeYear', 'periods', 'whitelist'));
    }

    public function update(Request $request)
    {
        // Validasi array input "deadlines" [id => datetime]
        foreach ($request->deadlines as $id => $datetime) {
            $periode = Periode::find($id);
            if ($periode) {
                $periode->update(['end_date' => $datetime]);
            }
        }

        return back()->with('success', 'Tenggat waktu berhasil diperbarui.');
    }

    public function toggleLock(Request $request, $id)
    {
        $periode = Periode::findOrFail($id);
        
        // Manual Emergency Lock: Just clear the deadline or set to past?
        // Better: We rely on "end_date".
        // Lock Now = Set end_date to NOW().
        // Unlock = Set end_date to Future or Null.
        
        if ($request->action == 'lock') {
            $periode->update(['end_date' => now()]);
            return back()->with('success', 'Akses segera DIKUNCI.');
        } else {
            // Unlock 24 hours
            $periode->update(['end_date' => now()->addHours(24)]);
            return back()->with('success', 'Akses DIBUKA selama 24 jam.');
        }
    }

    public function storeWhitelist(Request $request)
    {
        // Add teacher to whitelist
        $request->validate([
            'id_guru' => 'required', // Assuming ID passed from a search/select
            'id_periode' => 'required',
        ]);

        // Check if teacher user exists
        $teacher = User::findOrFail($request->id_guru);
        
        // Ensure it's a teacher? Optional, but good practice.
        if ($teacher->role !== 'teacher') {
             return back()->with('error', 'User dipilih bukan Guru.');
        }

        // Determine duration (default 1 day)
        $days = (int) $request->input('duration', 1);
        
        DB::table('grading_whitelist')->insert([
            'id_guru' => $teacher->id,
            'id_periode' => $request->id_periode,
            'alasan' => $request->alasan,
            'valid_until' => now()->addDays($days),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Akses khusus diberikan kepada ' . $teacher->name);
    }
    
    public function removeWhitelist($id)
    {
        DB::table('grading_whitelist')->where('id', $id)->delete();
        return back()->with('success', 'Akses khusus dicabut.');
    }
}
