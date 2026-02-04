<?php

namespace App\Traits;

use App\Models\Periode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait HasDeadlineCheck
{
    /**
     * Check if the grading period is open or user is whitelisted.
     *
     * @param int $periodeId
     * @return bool|string True if allowed, or error message string if denied.
     */
    public function checkDeadlineAccess($periodeId, $kelasId = null)
    {
        $periode = Periode::find($periodeId);
        
        if (!$periode) {
            return 'Periode tidak ditemukan.';
        }

        // 0. Admin Bypass
        if (Auth::user()->role === 'admin') {
            return true;
        }

        // 0.1 Wali Kelas Bypass (Must own the class)
        if ($kelasId) {
            $kelas = \App\Models\Kelas::find($kelasId);
            if ($kelas && $kelas->id_wali_kelas === Auth::id()) {
                return true;
            }
        }

        // 1. If no deadline set, it's open
        if (!$periode->end_date) {
            return true;
        }

        // 2. If deadline passed
        if (now() > $periode->end_date) {
            // 3. Check Whitelist
            $isWhitelisted = DB::table('grading_whitelist')
                ->where('id_guru', Auth::id())
                ->where('id_periode', $periodeId)
                ->where('valid_until', '>', now())
                ->exists();
            
            if ($isWhitelisted) {
                return true;
            }

            return 'Waktu pengisian nilai untuk periode ini SUDAH HABIS pada ' . \Carbon\Carbon::parse($periode->end_date)->translatedFormat('d F Y H:i');
        }

        return true;
    }
}
