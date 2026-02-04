@extends('layouts.app')

@section('title', 'Monitor DKN - ' . $kelas->nama_kelas)

@section('content')
<div class="flex-1 flex flex-col h-full overflow-hidden relative">
    
    <!-- Top Bar -->
    <div class="px-8 py-5 bg-white dark:bg-[#1a2e22] border-b border-slate-200 dark:border-slate-800 flex justify-between items-center z-20">
        <div class="flex items-center gap-4">
            <a href="{{ route('tu.dkn.index') }}" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    Data Nilai Ijazah 
                    <span class="px-2 py-0.5 rounded text-xs bg-teal-100 text-teal-700 border border-teal-200">{{ $kelas->nama_kelas }}</span>
                </h1>
                <p class="text-xs text-slate-500 mt-1">Mode Monitoring: Melihat data Nilai Rata-Rata Rapor & Ujian Madrasah</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
             <a href="{{ route('tu.dkn.archive', $kelas->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-50 transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">history_edu</span> Detail Arsip
            </a>
            <div class="px-3 py-1.5 bg-slate-100 text-slate-500 rounded-lg text-xs font-bold flex items-center gap-2 cursor-not-allowed" title="Mode Baca Saja">
                <span class="material-symbols-outlined text-[16px]">visibility</span> Read Only
            </div>
        </div>
    </div>

    <!-- Alert / Info -->
    <div class="px-8 pt-6 pb-2">
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-start gap-3">
             <span class="material-symbols-outlined text-slate-400">info</span>
             <div class="text-sm text-slate-600">
                <p class="font-bold mb-1">Status Data:</p>
                <div class="flex gap-4 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-slate-400"></span> <strong>RR:</strong> Rata-Rata Rapor (Sem 1-5)
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-amber-400"></span> <strong>UM:</strong> Nilai Ujian Madrasah
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-emerald-500"></span> <strong>NA:</strong> Nilai Akhir Ijazah
                    </div>
                </div>
             </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="flex-1 overflow-auto px-8 pb-8 scroll-smooth" x-data>
        
        <div class="border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm bg-white dark:bg-[#1a2e22] relative h-full flex flex-col overflow-hidden">
            <div class="overflow-auto flex-1 custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-800 text-white sticky top-0 z-20 shadow-md">
                        <tr>
                            <th rowspan="2" class="p-3 text-center text-xs font-bold uppercase border-b border-r border-slate-700 w-12 sticky left-0 z-30 bg-slate-800">No</th>
                            <th rowspan="2" class="p-3 text-left text-xs font-bold uppercase border-b border-r border-slate-700 w-64 min-w-[250px] sticky left-12 z-30 bg-slate-800 shadow-[4px_0_8px_rgba(0,0,0,0.1)]">
                                Peserta Didik
                            </th>
                            @foreach($mapels as $mapel)
                                <th colspan="3" class="p-2 text-center text-xs font-bold uppercase border-b border-r border-slate-600 whitespace-nowrap bg-slate-800">
                                    {{ $mapel->nama_mapel }}
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($mapels as $mapel)
                                <th class="p-2 text-center text-[10px] font-bold uppercase border-r border-slate-600 bg-slate-700 w-20">RR</th>
                                <th class="p-2 text-center text-[10px] font-bold uppercase border-r border-slate-600 bg-amber-900/40 text-amber-200 w-20">UM</th>
                                <th class="p-2 text-center text-[10px] font-bold uppercase border-r border-slate-600 bg-emerald-900/40 text-emerald-300 w-20">NA</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($dknData as $index => $row)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                                <td class="p-3 text-center text-xs font-medium text-slate-500 border-r border-slate-100 dark:border-slate-800 sticky left-0 bg-white dark:bg-[#1a2e22] z-10 group-hover:bg-slate-50 dark:group-hover:bg-slate-800/30">
                                    {{ $index + 1 }}
                                </td>
                                <td class="p-3 border-r border-slate-100 dark:border-slate-800 sticky left-12 bg-white dark:bg-[#1a2e22] z-10 shadow-[4px_0_8px_rgba(0,0,0,0.02)] group-hover:bg-slate-50 dark:group-hover:bg-slate-800/30">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm text-slate-800 dark:text-white truncate max-w-[200px]" title="{{ $row['student']->nama_lengkap }}">
                                            {{ $row['student']->nama_lengkap }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $row['student']->nis_lokal }}</span>
                                    </div>
                                </td>
                                
                                @php $summary = $row['summary']; @endphp
                                
                                @foreach($mapels as $mapel)
                                    @php
                                        $rr = $summary['rr'][$mapel->id] ?? null;
                                        $um = $summary['um'][$mapel->id] ?? null;
                                        $na = $summary['na'][$mapel->id] ?? null;
                                    @endphp
                                    
                                    <!-- RR Display -->
                                    <td class="border-r border-slate-100 dark:border-slate-800 p-2 text-center">
                                        <span class="text-xs text-slate-600 dark:text-slate-400">{{ $rr ?? '-' }}</span>
                                    </td>
                                    
                                    <!-- UM Display -->
                                    <td class="border-r border-slate-100 dark:border-slate-800 p-2 text-center bg-amber-50/20 dark:bg-amber-900/10">
                                         <span class="text-xs font-bold text-amber-700 dark:text-amber-500">{{ $um ?? '-' }}</span>
                                    </td>
                                    
                                    <!-- NA Display -->
                                    <td class="border-r border-slate-100 dark:border-slate-800 p-2 text-center bg-emerald-50/10">
                                        @if($na)
                                            <span class="text-xs font-bold {{ $na < 75 ? 'text-red-500' : 'text-emerald-600' }}">{{ $na }}</span>
                                        @else
                                            <span class="text-xs text-slate-300">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + ($mapels->count() * 3) }}" class="p-8 text-center text-slate-500 italic">
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

<style>
    /* Custom Scrollbar for Details */
    .custom-scrollbar::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
        border: 2px solid #f1f5f9;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endsection
