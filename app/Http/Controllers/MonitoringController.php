<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\Jenjang;
use App\Models\Kelas;
use App\Models\PengajarMapel;
use App\Models\NilaiSiswa;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        // DEBUG - CHECK IF HIT
        // dd('Monitoring Controller Reached');

        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) return view('monitoring.index', ['error' => 'Tahun Ajaran belum aktif']);

        // 1. Get Active Periods (MI & MTs)
        $periods = Periode::where('id_tahun_ajaran', $activeYear->id)
            ->where('status', 'aktif')
            ->get()
            ->groupBy('lingkup_jenjang'); // ['MI' => [P1, P2], 'MTS' => [P1]]

        // MI Data (Could be multiple periods)
        $dataMI = [];
        if (isset($periods['MI'])) {
            foreach ($periods['MI'] as $p) {
                $dataMI[] = array_merge(
                    $this->getProgressData('MI', $activeYear->id, $p->id),
                    ['periode_nama' => $p->nama_periode]
                );
            }
        }

        // MTS Data
        $dataMTS = [];
        if (isset($periods['MTS'])) {
            foreach ($periods['MTS'] as $p) {
                $dataMTS[] = array_merge(
                    $this->getProgressData('MTS', $activeYear->id, $p->id),
                    ['periode_nama' => $p->nama_periode]
                );
            }
        }
        
        // Pass flattened periods for Teacher assignments check? 
        // Actually getIncompleteTeachers needs logic update too to check ALL active periods.
        $allActivePeriodIds = $periods->flatten()->pluck('id')->toArray();
        $incompleteTeachers = $this->getIncompleteTeachers($activeYear->id, $allActivePeriodIds);

        return view('monitoring.index', compact('activeYear', 'dataMI', 'dataMTS', 'incompleteTeachers'));
    }

    private function getProgressData($jenjangKode, $yearId, $periodeId)
    {
        if (!$periodeId) return ['classes' => [], 'percent' => 0];

        // Get Classes for this Jenjang
        $jenjangId = Jenjang::where('kode', $jenjangKode)->value('id');
        $classes = Kelas::where('id_tahun_ajaran', $yearId)
            ->where('id_jenjang', $jenjangId)
            ->orderBy('tingkat_kelas')
            ->orderBy('nama_kelas')
            ->withCount('mapel_diajar') // Total Mapel assigned
            ->get();

        $totalMapelAll = 0;
        $totalFinalizedAll = 0;

        foreach ($classes as $kelas) {
            // Count Finalized Mapel for this Class
            // Logic: Count DISTINCT(id_mapel) in NilaiSiswa where id_kelas=X AND id_periode=Y AND status='final'
            $finalizedCount = NilaiSiswa::where('id_kelas', $kelas->id)
                ->where('id_periode', $periodeId)
                ->where('status', 'final')
                ->distinct('id_mapel')
                ->count('id_mapel');

            $kelas->finalized_count = $finalizedCount;
            $kelas->progress_percent = $kelas->mapel_diajar_count > 0 
                ? round(($finalizedCount / $kelas->mapel_diajar_count) * 100) 
                : 0;

            $totalMapelAll += $kelas->mapel_diajar_count;
            $totalFinalizedAll += $finalizedCount;
        }

        $overallPercent = $totalMapelAll > 0 ? round(($totalFinalizedAll / $totalMapelAll) * 100) : 0;

        return [
            'classes' => $classes,
            'percent' => $overallPercent
        ];
    }

    private function getIncompleteTeachers($yearId, $activePeriodIds)
    {
        // 1. Get all Assignments
        $assignments = PengajarMapel::whereHas('kelas', function($q) use ($yearId) {
            $q->where('id_tahun_ajaran', $yearId);
        })->with(['guru', 'mapel', 'kelas.jenjang'])
          ->get();

        // 2. Prepare Data
        $activePeriodsMap = Periode::whereIn('id', $activePeriodIds)->get()->groupBy('lingkup_jenjang');
        
        $teachersMap = [];

        foreach ($assignments as $asg) {
            if (!$asg->guru || !$asg->kelas || !$asg->mapel || !$asg->kelas->jenjang) continue;

            $jenjang = $asg->kelas->jenjang->kode;
            $periodsForThisJenjang = $activePeriodsMap[$jenjang] ?? collect([]);

            foreach ($periodsForThisJenjang as $periode) {
                // Check Status per Period
                $isFinal = NilaiSiswa::where('id_kelas', $asg->id_kelas)
                    ->where('id_mapel', $asg->id_mapel)
                    ->where('id_periode', $periode->id)
                    ->where('status', 'final')
                    ->exists();

                if (!$isFinal) {
                    // Calculate Progress %
                    $totalStudents = \App\Models\AnggotaKelas::where('id_kelas', $asg->id_kelas)->count();
                    $gradedStudents = NilaiSiswa::where('id_kelas', $asg->id_kelas)
                        ->where('id_mapel', $asg->id_mapel)
                        ->where('id_periode', $periode->id)
                        ->count();
                    
                    $percent = $totalStudents > 0 ? round(($gradedStudents / $totalStudents) * 100) : 0;
                    
                    // Determine textual status
                    $isDraft = $gradedStudents > 0;
                    $statusText = $isDraft ? 'Draft (' . $percent . '%)' : 'Belum Mulai';

                    $teacherId = $asg->guru->id;

                    // Initialize Teacher Entry if not exists
                    if (!isset($teachersMap[$teacherId])) {
                        $teachersMap[$teacherId] = (object) [
                            'id' => $teacherId,
                            'name' => $asg->guru->name,
                            'email' => $asg->guru->email,
                            'items' => collect([]) // Use collection for easier push
                        ];
                    }

                    // Add Assignment Item
                    $teachersMap[$teacherId]->items->push((object) [
                        'mapel' => $asg->mapel->nama_mapel,
                        'kelas' => $asg->kelas->nama_kelas,
                        'jenjang' => $jenjang,
                        'periode' => $periode->nama_periode,
                        'status' => $statusText,
                        'percent' => $percent,
                        'graded' => $gradedStudents,
                        'total' => $totalStudents
                    ]);
                }
            }
        }

        // 3. Convert to Collection & Paginate
        $collection = collect($teachersMap)->sortByDesc(function($teacher) {
            return $teacher->items->count(); // Sort by most incomplete items
        });

        $page = request()->get('page', 1);
        $perPage = 5; // Show 5 Teachers per page (since they are now grouped cards)
        
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $collection->forPage($page, $perPage),
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginated;
    }
}
