@extends('layouts.app')

@section('title', 'Leger Nilai - ' . $kelas->nama_kelas)

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('walikelas.dashboard') }}" class="hover:text-primary">Dashboard Wali Kelas</a>
                <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                <span>Leger</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Leger Nilai Kelas {{ $kelas->nama_kelas }}</h1>
            <p class="text-sm text-slate-500">
                Periode: <strong>{{ $periode->nama_periode }}</strong> • Total Siswa: <strong>{{ $students->count() }}</strong>
            </p>
        </div>
        <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
            <a href="{{ route('walikelas.leger.export') }}" target="_blank" class="bg-emerald-600 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-emerald-700 transition-all flex items-center justify-center gap-2 w-full md:w-auto">
                <span class="material-symbols-outlined">download</span> Export Excel (Semester)
            </a>
            <a href="{{ route('walikelas.leger.rekap') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-indigo-700 transition-all flex items-center justify-center gap-2 w-full md:w-auto">
                <span class="material-symbols-outlined">table_view</span> Lihat Rekap Tahunan
            </a>
        </div>
    </div>

    <!-- Desktop Leger Container -->
    <div class="hidden md:block bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col h-[75vh]">
        <div class="overflow-auto flex-1 relative">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/50 uppercase text-xs font-bold text-slate-500 sticky top-0 z-20">
                    <tr>
                        <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 sticky left-0 bg-slate-50 dark:bg-slate-800 z-30 min-w-[50px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">No</th>
                        <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 sticky left-[50px] bg-slate-50 dark:bg-slate-800 z-30 min-w-[250px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">Nama Siswa</th>
                        <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[80px] text-center">L/P</th>
                        
                        @foreach($mapels as $mapel)
                        <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[100px] text-center" title="{{ $mapel->nama_mapel }}">
                            <div class="truncate max-w-[100px]">{{ $mapel->nama_mapel }}</div>
                            @if($mapel->nama_kitab)
                                <div class="text-[10px] text-slate-500 truncate max-w-[100px]">{{ $mapel->nama_kitab }}</div>
                            @endif
                            <span class="text-[10px] items-center text-slate-400 font-normal">KKM: {{ $kkm[$mapel->id] ?? 70 }}</span>
                        </th>
                        @endforeach

                        <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[100px] text-center bg-blue-50/50 text-blue-800">Total</th>
                        <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[100px] text-center bg-green-50/50 text-green-800">Rata2</th>
                        <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[80px] text-center bg-yellow-50/50 text-yellow-800">Rank</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($students as $index => $ak)
                    @php
                        $studentGrades = $grades[$ak->id_siswa] ?? collect([]);
                        $totalScore = 0;
                        $countMapel = 0;
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                        <td class="px-4 py-3 border-r border-slate-100 dark:border-slate-800 sticky left-0 bg-white dark:bg-[#1a2e22] z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] text-center">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 border-r border-slate-100 dark:border-slate-800 sticky left-[50px] bg-white dark:bg-[#1a2e22] z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] font-medium text-slate-900 dark:text-white truncate max-w-[250px]">
                            {{ $ak->siswa->nama_lengkap }}
                             <div class="text-[10px] text-slate-400 font-normal">{{ $ak->siswa->nis_lokal }}</div>
                        </td>
                        <td class="px-4 py-3 text-center border-r border-slate-100 dark:border-slate-800">{{ $ak->siswa->jenis_kelamin }}</td>

                        <!-- Grades -->
                        @foreach($mapels as $mapel)
                        @php
                            $grade = $grades->get($ak->id_siswa . '-' . $mapel->id);
                            $score = $grade ? $grade->nilai_akhir : 0;
                            if($grade) {
                                $totalScore += $score;
                                $countMapel++;
                            }
                            $kkmLocal = $kkm[$mapel->id] ?? 70; 
                        @endphp
                        <td class="px-4 py-3 text-center border-r border-slate-100 dark:border-slate-800 {{ $score < $kkmLocal && $grade ? 'text-red-600 font-bold' : 'text-slate-700 dark:text-slate-300' }}">
                            {{ $grade ? number_format($score, 0) : '-' }}
                        </td>
                        @endforeach

                        <!-- Summary -->
                        <td class="px-4 py-3 text-center font-bold text-blue-600 bg-blue-50/10 border-r border-slate-100 dark:border-slate-800">
                            {{ $totalScore > 0 ? number_format($totalScore, 0) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-green-600 bg-green-50/10 border-r border-slate-100 dark:border-slate-800">
                            {{ $countMapel > 0 ? number_format($totalScore / $countMapel, 2) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-yellow-600 bg-yellow-50/10">
                            {{ $index + 1 }} <!-- Simple Rank based on index for now -->
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
            $totalScore = 0;
            $countMapel = 0;
            // Calculate Total first for Card Summary
            foreach($mapels as $mapel) {
                $grade = $grades->get($ak->id_siswa . '-' . $mapel->id);
                if($grade) {
                    $totalScore += $grade->nilai_akhir;
                    $countMapel++;
                }
            }
            $avg = $countMapel > 0 ? number_format($totalScore / $countMapel, 2) : 0;
        @endphp
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 transition-all" x-data="{ expanded: false }">
            <!-- Header Summary (Always Visible) -->
            <div class="p-4 flex flex-col gap-3" @click="expanded = !expanded">
                <div class="flex items-center gap-3">
                     <!-- Rank Badge -->
                     <div class="w-10 h-10 flex-shrink-0 bg-yellow-100 text-yellow-700 rounded-lg flex items-center justify-center font-bold border border-yellow-200 shadow-sm">
                        <span class="text-xs uppercase absolute -mt-6 bg-white px-1 rounded text-[8px] text-slate-400 tracking-wider">Rank</span>
                        #{{ $index + 1 }}
                     </div>
                     <div class="flex-1">
                        <h4 class="font-bold text-slate-900 dark:text-white line-clamp-1">{{ $ak->siswa->nama_lengkap }}</h4>
                        <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                            <span>{{ $ak->siswa->nis_lokal }}</span>
                            <span class="text-slate-300">•</span>
                            <span>{{ $ak->siswa->jenis_kelamin }}</span>
                        </div>
                     </div>
                     <button class="w-8 h-8 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180 bg-indigo-50 border-indigo-200 text-indigo-600' : ''">
                        <span class="material-symbols-outlined">expand_more</span>
                     </button>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-blue-50/50 border border-blue-100 rounded-lg p-2.5 flex justify-between items-center">
                        <span class="text-xs text-blue-600 font-bold uppercase">Total Nilai</span>
                        <span class="font-bold text-blue-700">{{ number_format($totalScore, 0) }}</span>
                    </div>
                    <div class="bg-green-50/50 border border-green-100 rounded-lg p-2.5 flex justify-between items-center">
                        <span class="text-xs text-green-600 font-bold uppercase">Rata-Rata</span>
                        <span class="font-bold text-green-700">{{ $avg }}</span>
                    </div>
                </div>
            </div>

            <!-- Detail Accordion (Hidden by default) -->
            <div x-show="expanded" x-collapse style="display: none;" class="border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-[#1a2e22] rounded-b-xl p-4">
                <div class="grid grid-cols-1 gap-2">
                    <div class="grid grid-cols-12 text-[10px] font-bold text-slate-400 uppercase mb-1 px-2">
                        <div class="col-span-8">Mata Pelajaran</div>
                        <div class="col-span-2 text-center">KKM</div>
                        <div class="col-span-2 text-right">Nilai</div>
                    </div>
                    @foreach($mapels as $mapel)
                    @php
                        $grade = $grades->get($ak->id_siswa . '-' . $mapel->id);
                        $score = $grade ? $grade->nilai_akhir : 0;
                        $kkmLocal = $kkm[$mapel->id] ?? 70;
                        $isFail = $score < $kkmLocal && $grade;
                    @endphp
                    <div class="grid grid-cols-12 items-center p-2 rounded-lg {{ $isFail ? 'bg-red-50 border border-red-100' : 'hover:bg-slate-50 border border-transparent' }}">
                        <div class="col-span-8 flex flex-col">
                            <span class="text-xs font-medium {{ $isFail ? 'text-red-800' : 'text-slate-700 dark:text-slate-300' }}">{{ $mapel->nama_mapel }}</span>
                        </div>
                        <div class="col-span-2 text-center">
                            <span class="text-xs {{ $isFail ? 'text-red-500' : 'text-slate-400' }}">{{ $kkmLocal }}</span>
                        </div>
                        <div class="col-span-2 text-right">
                            <span class="text-sm font-bold {{ $isFail ? 'text-red-700' : 'text-slate-800 dark:text-white' }}">
                                {{ $grade ? number_format($score, 0) : '-' }}
                            </span>
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
        @page {
            size: landscape;
            margin: 5mm;
        }
        body * {
            visibility: hidden;
        }
        .bg-white.rounded-xl.shadow-sm.overflow-hidden, .bg-white.rounded-xl.shadow-sm.overflow-hidden * {
            visibility: visible;
        }
        .bg-white.rounded-xl.shadow-sm.overflow-hidden {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: auto;
            overflow: visible !important;
            border: none;
            box-shadow: none;
        }
        /* Hide scrollbars/sticky shadows in print */
        .sticky { position: static !important; box-shadow: none !important; }
        .overhead, button, a { display: none !important; }
        
        /* Ensure table fits */
        table { width: 100%; }
        td, th { padding: 4px !important; font-size: 10px !important; }
    }
</style>
@endsection
