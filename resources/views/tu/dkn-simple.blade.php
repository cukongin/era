@extends('layouts.app')

@section('title', 'Monitor DKN - ' . $kelas->nama_kelas)

@php
    $passRate = $stats['total'] > 0 ? ($stats['pass'] / $stats['total']) * 100 : 0;
@endphp

@section('content')
<div class="flex-1 flex flex-col h-full overflow-hidden relative bg-slate-50 dark:bg-slate-900">

    <!-- Top Bar -->
    <div class="px-6 py-5 bg-white dark:bg-surface-dark border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row justify-between md:items-center gap-4 z-20 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('tu.dkn.index') }}" class="p-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 hover:text-primary transition-all">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-3 tracking-tight">
                    <span class="bg-gradient-to-r from-primary to-emerald-500 text-transparent bg-clip-text">Monitor DKN</span>
                </h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-primary/10 text-primary border border-primary/20 dark:bg-primary/20 dark:text-primary dark:border-primary/20">
                        {{ $kelas->nama_kelas }}
                    </span>
                    <span class="text-xs text-slate-400">&bull; Mode Monitoring</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
             <a href="{{ route('tu.dkn.archive', $kelas->id) }}" class="px-4 py-2.5 bg-white border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">history_edu</span> Detail Arsip
            </a>
            <div class="px-4 py-2.5 bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 rounded-xl text-xs font-bold flex items-center gap-2 cursor-not-allowed border border-transparent opacity-70" title="Mode Baca Saja">
                <span class="material-symbols-outlined text-[16px]">visibility</span> Read Only
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="px-4 md:px-6 py-4 md:py-6 flex overflow-x-auto snap-x md:grid md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 pb-6 md:pb-6 no-scrollbar">
        <!-- Average Card -->
        <div class="min-w-[85%] md:min-w-0 flex-shrink-0 snap-center bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                 <span class="material-symbols-outlined text-6xl text-primary">analytics</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Rata-Rata Kelas</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['average'], 2) }}</h3>
                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">NA</span>
            </div>
            <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5 dark:bg-slate-700 overflow-hidden">
                <div class="bg-primary h-1.5 rounded-full" style="width: {{ min(($stats['average'] / 100) * 100, 100) }}%"></div>
            </div>
        </div>

        <!-- Highest Card -->
        <div class="min-w-[85%] md:min-w-0 flex-shrink-0 snap-center bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                 <span class="material-symbols-outlined text-6xl text-emerald-500">emoji_events</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tertinggi</p>
            <div class="flex flex-col">
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $stats['highest']['score'] }}</h3>
                <p class="text-xs font-bold text-slate-500 truncate" title="{{ $stats['highest']['student'] }}">
                    {{ $stats['highest']['student'] }}
                </p>
            </div>
        </div>

        <!-- Lowest Card -->
        <div class="min-w-[85%] md:min-w-0 flex-shrink-0 snap-center bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                 <span class="material-symbols-outlined text-6xl text-rose-500">trending_down</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Terendah</p>
            <div class="flex flex-col">
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $stats['lowest']['score'] }}</h3>
                <p class="text-xs font-bold text-slate-500 truncate" title="{{ $stats['lowest']['student'] }}">
                    {{ $stats['lowest']['student'] }}
                </p>
            </div>
        </div>

        <!-- Pass Rate Card -->
        <div class="min-w-[85%] md:min-w-0 flex-shrink-0 snap-center bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                 <span class="material-symbols-outlined text-6xl text-purple-500">school</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Kelulusan</p>
            <div class="flex items-center gap-3">
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($passRate, 0) }}%</h3>
                <div class="flex flex-col text-[10px] font-bold">
                    <span class="text-emerald-600">{{ $stats['pass'] }} Lulus</span>
                    <span class="text-rose-500">{{ $stats['fail'] }} Belum</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="flex-1 overflow-hidden px-4 pb-4 md:px-6 md:pb-6" x-data>
        <div class="border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-slate-900/50 bg-white dark:bg-surface-dark relative h-full flex flex-col overflow-hidden">
            <div class="overflow-auto flex-1 custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-700 sticky top-0 z-40 shadow-sm border-b border-slate-300">
                        <tr>
                            <th rowspan="2" class="p-3 text-center text-xs font-bold uppercase border-r border-slate-200 w-12 md:sticky md:left-0 z-50 bg-slate-50">No</th>
                            <th rowspan="2" class="p-3 text-left text-xs font-bold uppercase border-r border-slate-200 w-64 min-w-[200px] md:min-w-[250px] md:sticky md:left-12 z-50 bg-slate-50 shadow-[4px_0_8px_rgba(0,0,0,0.05)]">
                                Peserta Didik
                            </th>
                            @foreach($mapels as $mapel)
                                <th colspan="3" class="px-2 py-2 text-center border-r border-slate-200 bg-slate-50 last:border-r-0">
                                    <div class="text-[11px] font-bold text-slate-700 truncate max-w-[120px]" title="{{ $mapel->nama_mapel }}">{{ $mapel->nama_mapel }}</div>
                                </th>
                            @endforeach
                            <th rowspan="2" class="p-3 text-center text-xs font-bold uppercase border-l border-slate-200 bg-slate-50 md:sticky md:right-[100px] z-40 min-w-[80px] shadow-[-4px_0_8px_rgba(0,0,0,0.05)]">RataÂ²</th>
                            <th rowspan="2" class="p-3 text-center text-xs font-bold uppercase border-l border-slate-200 bg-slate-50 md:sticky md:right-0 z-40 min-w-[100px]">Status</th>
                        </tr>
                        <tr>
                            @foreach($mapels as $mapel)
                                <th class="py-1 px-1 text-center text-[10px] font-semibold text-slate-500 bg-slate-100 border-r border-slate-200 border-t border-slate-200 w-14">RR</th>
                                <th class="py-1 px-1 text-center text-[10px] font-semibold text-slate-500 bg-amber-50/50 border-r border-slate-200 border-t border-slate-200 w-14">UM</th>
                                <th class="py-1 px-1 text-center text-[10px] font-semibold text-slate-500 bg-emerald-50/50 border-r border-slate-200 border-t border-slate-200 w-14">NA</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($dknData as $index => $row)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors group">
                                <td class="p-3 text-center text-xs font-bold text-slate-500 border-r border-slate-100 dark:border-slate-700 md:sticky md:left-0 bg-white dark:bg-surface-dark z-30 group-hover:bg-slate-50 dark:group-hover:bg-slate-700/30">
                                    {{ $index + 1 }}
                                </td>
                                <td class="p-3 border-r border-slate-100 dark:border-slate-700 md:sticky md:left-12 bg-white dark:bg-surface-dark z-30 shadow-[4px_0_8px_rgba(0,0,0,0.03)] group-hover:bg-slate-50 dark:group-hover:bg-slate-700/30">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm text-slate-800 dark:text-white truncate max-w-[150px] md:max-w-[200px]" title="{{ $row['student']->nama_lengkap }}">
                                            {{ $row['student']->nama_lengkap }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $row['student']->nis_lokal }}</span>
                                    </div>
                                </td>

                                @php $summary = $row['summary']; @endphp
                                @php $sumNA = 0; $countMapel = 0; @endphp

                                @foreach($mapels as $mapel)
                                    @php
                                        $rr = $summary['rr'][$mapel->id] ?? null;
                                        $um = $summary['um'][$mapel->id] ?? null;
                                        $na = $summary['na'][$mapel->id] ?? null;

                                        if($na > 0) { $sumNA += $na; $countMapel++; }
                                    @endphp

                                    <td class="border-r border-slate-100 dark:border-slate-700 p-1 text-center">
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                            {{ $rr !== null ? number_format((float)$rr, 2) : '-' }}
                                        </span>
                                    </td>

                                    <td class="border-r border-slate-100 dark:border-slate-700 p-1 text-center bg-amber-50/20 dark:bg-amber-900/10">
                                         <span class="text-[11px] font-bold text-amber-700 dark:text-amber-500">
                                            {{ $um !== null ? number_format((float)$um, 0) : '-' }}
                                         </span>
                                    </td>

                                    <td class="border-r border-slate-100 dark:border-slate-700 p-1 text-center bg-emerald-50/10">
                                        @if($na !== null)
                                            <span class="text-[11px] font-bold {{ $na < 75 ? 'text-red-500' : 'text-emerald-600' }}">
                                                {{ number_format((float)$na, 2) }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-300">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                @php
                                    $avg = $countMapel > 0 ? $sumNA / $countMapel : 0;
                                    $minLulus = \App\Models\GlobalSetting::val('ijazah_min_lulus', 60);
                                    $isPass = $avg >= $minLulus;
                                @endphp

                                <td class="p-3 text-center border-l border-primary/20 dark:border-primary/20 bg-primary/10 dark:bg-primary/20 md:sticky md:right-[100px] z-30 font-black text-primary dark:text-primary shadow-[-4px_0_8px_rgba(0,0,0,0.03)]">
                                    {{ number_format($avg, 2) }}
                                </td>

                                <td class="p-3 text-center border-l border-slate-100 dark:border-slate-700 md:sticky md:right-0 z-30 bg-white dark:bg-surface-dark group-hover:bg-slate-50 dark:group-hover:bg-slate-700/30">
                                     @if($avg > 0)
                                        @if($isPass)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                LULUS
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                BELUM
                                            </span>
                                        @endif
                                     @else
                                        -
                                     @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + ($mapels->count() * 3) }}" class="p-8 text-center text-slate-500 italic">
                                    Data siswa tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

