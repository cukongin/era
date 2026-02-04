<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\KkmMapel;
use App\Models\BobotPenilaian;
use App\Models\Mapel;
use Illuminate\Http\Request;

class GradingSettingsController extends Controller
{
    public function index()
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        
        if (!$activeYear) {
            return redirect()->back()->with('error', 'Belum ada tahun ajaran aktif.');
        }

        // Get Periode
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)->get();

        // Get Bobot
        $bobotMI = BobotPenilaian::firstOrCreate(
            ['id_tahun_ajaran' => $activeYear->id, 'jenjang' => 'MI'],
            ['bobot_harian' => 50, 'bobot_uts_cawu' => 50, 'bobot_uas' => 0]
        );
        
        $bobotMTS = BobotPenilaian::firstOrCreate(
            ['id_tahun_ajaran' => $activeYear->id, 'jenjang' => 'MTS'],
            ['bobot_harian' => 40, 'bobot_uts_cawu' => 30, 'bobot_uas' => 30]
        );

        // Get KKM (Grouped by Mapel)
        // Logic: Should show all mapels. If KKM entry exists, show value, else default 70.
        $mapels = Mapel::orderBy('nama_mapel')->get();
        $kkms = KkmMapel::where('id_tahun_ajaran', $activeYear->id)->get()->keyBy(function($item) {
            return $item->id_mapel . '-' . $item->jenjang_target;
        });

        return view('settings.grading', compact('activeYear', 'periods', 'bobotMI', 'bobotMTS', 'mapels', 'kkms'));
    }

    public function storeWeights(Request $request)
    {
        $request->validate([
            'jenjang' => 'required|in:MI,MTS',
            'bobot_harian' => 'required|numeric|min:0|max:100',
            'bobot_uts_cawu' => 'required|numeric|min:0|max:100',
            'bobot_uas' => 'nullable|numeric|min:0|max:100',
        ]);

        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();

        BobotPenilaian::updateOrCreate(
            ['id_tahun_ajaran' => $activeYear->id, 'jenjang' => $request->jenjang],
            [
                'bobot_harian' => $request->bobot_harian,
                'bobot_uts_cawu' => $request->bobot_uts_cawu,
                'bobot_uas' => $request->bobot_uas ?? 0,
            ]
        );

        return back()->with('success', 'Bobot penilaian berhasil disimpan.');
    }

    public function storeKkm(Request $request)
    {
        $activeYear = TahunAjaran::where('status', 'aktif')->firstOrFail();
        
        // Loop through input. Form expected: kkm[id_mapel][jenjang] = value
        foreach ($request->kkm as $mapelId => $jenjangData) {
            foreach ($jenjangData as $jenjang => $nilai) {
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

        return back()->with('success', 'KKM berhasil diperbarui.');
    }

    public function togglePeriod($id)
    {
        $periode = Periode::findOrFail($id);
        
        // Logic: If opening a period, should we close others of same type? 
        // User request: "Membuka akses periode...".
        // Let's allow simple toggle for now. 
        
        $periode->status = $periode->status == 'aktif' ? 'tutup' : 'aktif';
        $periode->save();

        return back()->with('success', 'Status periode diperbarui: ' . $periode->nama_periode . ' (' . $periode->status . ')');
    }
}
