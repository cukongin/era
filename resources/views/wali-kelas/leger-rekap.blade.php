@extends('layouts.app')

@section('title', 'Leger Rekap Tahunan - ' . $kelas->nama_kelas)

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('walikelas.dashboard') }}" class="hover:text-primary">Dashboard Wali Kelas</a>
                <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                <span>Leger Rekap</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Leger Rekap Tahunan {{ $kelas->nama_kelas }}</h1>
            <p class="text-sm text-slate-500">
                Tahun Ajaran: <strong>{{ $kelas->tahun_ajaran->nama_tahun }}</strong> • Total Siswa: <strong>{{ $students->count() }}</strong>
            </p>
        </div>
        <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
            <a href="{{ route('walikelas.leger.rekap.export') }}" target="_blank" class="bg-emerald-600 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-emerald-700 transition-all flex items-center justify-center gap-2 w-full md:w-auto">
                <span class="material-symbols-outlined">download</span> Export Excel (Tahunan)
            </a>
        </div>
    </div>

    <!-- Desktop Leger Container -->
    <div class="hidden md:block bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col h-[75vh]">
        <div class="overflow-auto flex-1 relative">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/50 uppercase text-xs font-bold text-slate-500 sticky top-0 z-20">
                    <tr>
                        <th rowspan="2" class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 sticky left-0 bg-slate-50 dark:bg-slate-800 z-30 min-w-[50px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">No</th>
                        <th rowspan="2" class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 sticky left-[50px] bg-slate-50 dark:bg-slate-800 z-30 min-w-[250px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">Nama Siswa</th>
                        <th rowspan="2" class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[50px] text-center">L/P</th>

                        @foreach($mapels as $mapel)
                        <th colspan="{{ $periods->count() + 1 }}" class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 text-center border-l dark:border-slate-700">
                            <div class="truncate max-w-[150px] mx-auto">{{ $mapel->nama_mapel }}</div>
                            <div class="text-[10px] text-slate-400 font-normal">KKM: {{ $kkm[$mapel->id] ?? 70 }}</div>
                        </th>
                        @endforeach

                        <th rowspan="2" class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[80px] text-center bg-primary/5 text-primary border-l">Rata-rata<br>Total</th>
                    </tr>
                    <tr>
                        <!-- Sub Columns for Periods -->
                        @foreach($mapels as $mapel)
                            @foreach($periods as $periode)
                            <th class="px-2 py-1 border-b border-slate-200 dark:border-slate-700 min-w-[50px] text-center text-[10px] bg-slate-100 dark:bg-slate-700/50">
                                {{ substr($periode->nama_periode, 0, 1) }}{{ filter_var($periode->nama_periode, FILTER_SANITIZE_NUMBER_INT) }}
                            </th>
                            @endforeach
                            <th class="px-2 py-1 border-b border-slate-200 dark:border-slate-700 min-w-[60px] text-center text-[10px] bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700">RATA</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($students as $index => $ak)
                    @php
                        $studentGradesAll = $grades[$ak->id_siswa] ?? collect([]);
                        $grandTotalAvg = 0;
                        $countMapelAvg = 0;
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                        <td class="px-4 py-3 border-r border-slate-100 dark:border-slate-800 sticky left-0 bg-white dark:bg-[#1a2e22] z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] text-center">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 border-r border-slate-100 dark:border-slate-800 sticky left-[50px] bg-white dark:bg-[#1a2e22] z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] font-medium text-slate-900 dark:text-white truncate max-w-[250px]">
                            {{ $ak->siswa->nama_lengkap }}
                        </td>
                        <td class="px-4 py-3 text-center border-r border-slate-100 dark:border-slate-800">{{ $ak->siswa->jenis_kelamin }}</td>

                        <!-- Grades Per Mapel -->
                        @foreach($mapels as $mapel)
                            @php
                                $mapelTotal = 0;
                                $mapelCount = 0;
                                $kkmLocal = $kkm[$mapel->id] ?? 70;
                            @endphp

                            @foreach($periods as $periodeId => $periodeObj)
                                @php
                                    // Search in collection
                                    $grade = $studentGradesAll->where('id_periode', $periodeId)->where('id_mapel', $mapel->id)->first();
                                    $score = $grade ? $grade->nilai_akhir : 0;
                                    if($grade) {
                                        $mapelTotal += $score;
                                        $mapelCount++;
                                    }
                                @endphp
                                <td class="px-2 py-3 text-center border-r border-slate-100 dark:border-slate-800 {{ $score < $kkmLocal && $grade ? 'text-red-600 font-bold' : '' }}">
                                    {{ $grade ? number_format($score, 0) : '-' }}
                                </td>
                            @endforeach

                            <!-- Average Per Mapel -->
                            @php
                                $avgMapel = $mapelCount > 0 ? $mapelTotal / $mapelCount : 0;
                                if ($mapelCount > 0) {
                                    $grandTotalAvg += $avgMapel;
                                    $countMapelAvg++;
                                }
                            @endphp
                            <td class="px-2 py-3 text-center font-bold text-yellow-700 bg-yellow-50/10 border-r border-slate-200 dark:border-slate-700">
                                {{ $mapelCount > 0 ? number_format($avgMapel, 2) : '-' }}
                            </td>
                        @endforeach

                        <!-- Grand Average -->
                        @php
                            $finalAvg = $countMapelAvg > 0 ? $grandTotalAvg / $countMapelAvg : 0;
                        @endphp
                        <td class="px-4 py-3 text-center font-bold text-primary bg-primary/5 border-l border-slate-200">
                            {{ $countMapelAvg > 0 ? number_format($finalAvg, 2) : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden flex flex-col gap-4">
        @foreach($students as $index => $ak)
        @php
            $studentGradesAll = $grades[$ak->id_siswa] ?? collect([]);
            $grandTotalAvg = 0;
            $countMapelAvg = 0;

            // Pre-calculate Grand Average for Card Summary
            foreach($mapels as $mapel) {
                 $mapelTotal = 0;
                 $mapelCount = 0;
                 foreach($periods as $periodeId => $periodeObj) {
                     $grade = $studentGradesAll->where('id_periode', $periodeId)->where('id_mapel', $mapel->id)->first();
                     if($grade) {
                         $mapelTotal += $grade->nilai_akhir;
                         $mapelCount++;
                     }
                 }
                 if ($mapelCount > 0) {
                     $grandTotalAvg += ($mapelTotal / $mapelCount);
                     $countMapelAvg++;
                 }
            }
            $finalAvg = $countMapelAvg > 0 ? $grandTotalAvg / $countMapelAvg : 0;
        @endphp
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 transition-all" x-data="{ expanded: false }">
            <!-- Header Summary (Always Visible) -->
            <div class="p-4 flex flex-col gap-3" @click="expanded = !expanded">
                <div class="flex items-center gap-3">
                     <!-- Rank Badge (Placeholder as logic is complex here) -->
                     <div class="w-10 h-10 flex-shrink-0 bg-primary/10 text-primary rounded-lg flex items-center justify-center font-bold border border-primary/20 shadow-sm">
                        <span class="text-xs uppercase absolute -mt-6 bg-white px-1 rounded text-[8px] text-slate-400 tracking-wider">No</span>
                        {{ $index + 1 }}
                     </div>
                     <div class="flex-1">
                        <h4 class="font-bold text-slate-900 dark:text-white line-clamp-1">{{ $ak->siswa->nama_lengkap }}</h4>
                        <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                            <span>{{ $ak->siswa->nis_lokal }}</span>
                            <span class="text-slate-300">•</span>
                            <span>{{ $ak->siswa->jenis_kelamin }}</span>
                        </div>
                     </div>
                     <button class="w-8 h-8 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180 bg-primary/10 border-primary/20 text-primary' : ''">
                        <span class="material-symbols-outlined">expand_more</span>
                     </button>
                </div>

                <!-- Quick Stats -->
                <div class="bg-primary/5 border border-primary/10 rounded-lg p-3 flex justify-between items-center">
                    <span class="text-xs text-primary font-bold uppercase">Rata-Rata Total</span>
                    <span class="font-bold text-lg text-primary">{{ number_format($finalAvg, 2) }}</span>
                </div>
            </div>

            <!-- Detail Accordion (Hidden by default) -->
            <div x-show="expanded" x-collapse style="display: none;" class="border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-[#1a2e22] rounded-b-xl p-4">
                <div class="grid grid-cols-1 gap-3">
                    @foreach($mapels as $mapel)
                        @php
                            $mapelTotal = 0;
                            $mapelCount = 0;
                            foreach($periods as $periodeId => $periodeObj) {
                                $grade = $studentGradesAll->where('id_periode', $periodeId)->where('id_mapel', $mapel->id)->first();
                                if($grade) {
                                    $mapelTotal += $grade->nilai_akhir;
                                    $mapelCount++;
                                }
                            }
                            $avgMapel = $mapelCount > 0 ? $mapelTotal / $mapelCount : 0;
                        @endphp
                        <div class="flex flex-col gap-1 border-b border-slate-50 pb-2 last:border-0">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $mapel->nama_mapel }}</span>
                                <span class="text-xs font-bold text-yellow-600">{{ $mapelCount > 0 ? number_format($avgMapel, 2) : '-' }}</span>
                            </div>
                            <!-- Period Breakdown (Optional, maybe too detailed for mobile? Let's show tiny badges) -->
                            <div class="flex gap-1 overflow-x-auto no-scrollbar">
                                 @foreach($periods as $periodeId => $periodeObj)
                                    @php
                                        $grade = $studentGradesAll->where('id_periode', $periodeId)->where('id_mapel', $mapel->id)->first();
                                    @endphp
                                    <div class="px-1.5 py-0.5 rounded text-[10px] {{ $grade ? 'bg-slate-100 text-slate-600' : 'bg-slate-50 text-slate-300' }}">
                                        {{ substr($periodeObj->nama_periode, -1) }}: {{ $grade ? $grade->nilai_akhir : '-' }}
                                    </div>
                                 @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
    @media print {
        @page { size: landscape; margin: 5mm; }
        body * { visibility: hidden; }
        .bg-white.rounded-xl.shadow-sm.overflow-hidden, .bg-white.rounded-xl.shadow-sm.overflow-hidden * { visibility: visible; }
        .bg-white.rounded-xl.shadow-sm.overflow-hidden { position: absolute; left: 0; top: 0; width: 100%; height: auto; overflow: visible !important; border: none; box-shadow: none; }
        .sticky { position: static !important; box-shadow: none !important; }
        .overhead, button, a { display: none !important; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 4px !important; font-size: 9px !important; border: 1px solid #ddd !important; }
    }
</style>
@endsection
