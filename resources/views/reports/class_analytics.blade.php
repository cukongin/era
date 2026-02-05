@extends('layouts.app')

@section('title', 'Analisa Prestasi - ' . $class->nama_kelas)

@section('content')
<div class="flex flex-col space-y-8">
    
    <!-- 1. Header & Filters -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('tu.monitoring.global') }}" class="text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Analisa Prestasi</h1>
                @if($isAnnual ?? false)
                    <span class="bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 text-xs font-bold px-2 py-1 rounded-full border border-purple-200 dark:border-purple-800">Mode Tahunan</span>
                @endif
            </div>
            <p class="text-slate-500 dark:text-slate-400 ml-8">Ranking detail dan analisa nilai siswa kelas <span class="font-bold text-primary">{{ $class->nama_kelas }}</span>.</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Period Selector -->
            <form action="{{ route('reports.class.analytics', $class->id) }}" method="GET">
                <div class="relative group">
                    <select name="period_id" class="appearance-none pl-10 pr-8 py-2.5 text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer min-w-[220px] shadow-sm transition-all" onchange="this.form.submit()">
                        <option value="annual" {{ ($isAnnual ?? false) ? 'selected' : '' }} class="font-bold text-purple-600">🏆 Analisa Tahunan (Semua)</option>
                        <option disabled>──────────</option>
                        @foreach($periodes as $p)
                            <option value="{{ $p->id }}" {{ isset($periode) && $periode->id == $p->id && !($isAnnual ?? false) ? 'selected' : '' }}>
                                {{ $p->nama_periode }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px] {{ ($isAnnual ?? false) ? 'text-purple-500' : '' }}">calendar_month</span>
                    </div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- 2. Podium Section (Top 3) -->
    @if(count($podium) >= 1)
    <div class="relative pt-10 pb-4">
        <!-- Background Decoration -->
        <div class="absolute inset-0 bg-gradient-to-b from-indigo-50/50 to-transparent dark:from-indigo-900/10 dark:to-transparent rounded-3xl -z-10"></div>
        
        <div class="flex justify-center items-end gap-4 md:gap-8 px-4">
            
            <!-- Rank 2 -->
            @if(isset($podium[1]))
            <div class="flex flex-col items-center group relative top-4">
                <div class="relative mb-3">
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-full border-4 border-slate-300 dark:border-slate-600 overflow-hidden shadow-lg bg-white">
                        <!-- Placeholder/Avatar -->
                        <div class="w-full h-full bg-slate-200 flex items-center justify-center text-2xl font-bold text-slate-400">
                            {{ substr($podium[1]['student']->nama_lengkap, 0, 1) }}
                        </div>
                    </div>
                    <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 bg-slate-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black border-4 border-slate-50 dark:border-[#121c16] shadow-md z-10">
                        2
                    </div>
                </div>
                <div class="text-center mt-2">
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm md:text-base line-clamp-1 max-w-[120px]">{{ $podium[1]['student']->nama_lengkap }}</h3>
                    <div class="text-xs font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full inline-block mt-1">
                        {{ number_format($podium[1]['total'], 2) }} {{ ($isAnnual ?? false) ? 'Avg Poin' : 'Poin' }}
                    </div>
                </div>
                <!-- Podium Base -->
                <div class="h-24 md:h-32 w-24 md:w-32 bg-gradient-to-t from-slate-200 to-slate-100 dark:from-slate-700 dark:to-slate-600 rounded-t-lg mt-3 shadow-inner opacity-80"></div>
            </div>
            @endif

            <!-- Rank 1 -->
            @if(isset($podium[0]))
            <div class="flex flex-col items-center group z-10">
                <div class="relative mb-3 transform scale-110">
                    <div class="absolute -top-10 left-1/2 -translate-x-1/2 text-4xl animate-bounce">👑</div>
                    <div class="w-24 h-24 md:w-32 md:h-32 rounded-full border-4 border-amber-400 overflow-hidden shadow-2xl bg-white ring-4 ring-amber-100/50">
                         <div class="w-full h-full bg-amber-50 flex items-center justify-center text-4xl font-bold text-amber-300">
                            {{ substr($podium[0]['student']->nama_lengkap, 0, 1) }}
                        </div>
                    </div>
                    <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 bg-amber-500 text-white w-10 h-10 flex items-center justify-center rounded-full font-black border-4 border-slate-50 dark:border-[#121c16] shadow-lg z-10 text-xl">
                        1
                    </div>
                </div>
                 <div class="text-center mt-2">
                    <h3 class="font-black text-slate-900 dark:text-white text-base md:text-lg line-clamp-1 max-w-[150px]">{{ $podium[0]['student']->nama_lengkap }}</h3>
                     <div class="text-sm font-bold text-amber-600 bg-amber-100 dark:bg-amber-900/40 px-3 py-0.5 rounded-full inline-block mt-1">
                        {{ number_format($podium[0]['total'], 2) }} {{ ($isAnnual ?? false) ? 'Avg Poin' : 'Poin' }}
                    </div>
                    @if(isset($podium[0]['tie_reason']))
                         <div class="text-[10px] text-amber-600 mt-1 font-medium animate-pulse">
                            🏆 {{ $podium[0]['tie_reason'] }}
                        </div>
                    @endif
                </div>
                <!-- Podium Base -->
                <div class="h-32 md:h-40 w-28 md:w-40 bg-gradient-to-t from-amber-200 to-amber-100 dark:from-amber-700 dark:to-amber-600 rounded-t-lg mt-3 shadow-md relative overflow-hidden">
                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-white/20 skew-y-6 transform origin-bottom-left"></div>
                </div>
            </div>
            @endif

            <!-- Rank 3 -->
            @if(isset($podium[2]))
            <div class="flex flex-col items-center group relative top-8">
                <div class="relative mb-3">
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-full border-4 border-orange-300 dark:border-orange-800 overflow-hidden shadow-lg bg-white">
                        <div class="w-full h-full bg-orange-50 flex items-center justify-center text-2xl font-bold text-orange-300">
                            {{ substr($podium[2]['student']->nama_lengkap, 0, 1) }}
                        </div>
                    </div>
                    <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 bg-orange-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black border-4 border-slate-50 dark:border-[#121c16] shadow-md z-10">
                        3
                    </div>
                </div>
               <div class="text-center mt-2">
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm md:text-base line-clamp-1 max-w-[120px]">{{ $podium[2]['student']->nama_lengkap }}</h3>
                     <div class="text-xs font-bold text-orange-600 bg-orange-100 dark:bg-orange-900/40 px-2 py-0.5 rounded-full inline-block mt-1">
                        {{ number_format($podium[2]['total'], 2) }} {{ ($isAnnual ?? false) ? 'Avg Poin' : 'Poin' }}
                    </div>
                </div>
                <!-- Podium Base -->
                <div class="h-20 md:h-24 w-24 md:w-32 bg-gradient-to-t from-orange-200 to-orange-100 dark:from-orange-800 dark:to-orange-700 rounded-t-lg mt-3 shadow-inner opacity-80"></div>
            </div>
            @endif

        </div>
    </div>
    @endif

    <!-- 3. Ranking Table -->
    <div class="bg-white dark:bg-[#1a2332] rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 dark:bg-[#1e2837] border-b border-slate-100 dark:border-[#2a3441] flex justify-between items-center">
             <h3 class="font-bold text-slate-700 dark:text-slate-300">Daftar Peringkat {{ ($isAnnual ?? false) ? 'Tahunan' : 'Periode' }}</h3>
             @if($isAnnual ?? false)
                 <span class="text-xs text-purple-600 bg-purple-50 px-2 py-1 rounded border border-purple-100">Kumulatif Semua Periode</span>
             @endif
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-[#1e2837] dark:text-slate-400 border-b border-slate-100 dark:border-[#2a3441]">
                    <tr>
                        <th class="px-6 py-4 font-bold text-center w-20">Rank</th>
                        <th class="px-6 py-4 font-bold">Nama Siswa / NIS</th>
                        <th class="px-6 py-4 font-bold text-center">{{ ($isAnnual ?? false) ? 'Total Rata-rata' : 'Total Nilai' }}</th>
                        <th class="px-6 py-4 font-bold text-center">Rata-rata</th>
                        <th class="px-6 py-4 font-bold text-center">Kehadiran (Absen)</th>
                        <th class="px-6 py-4 font-bold text-center">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#2a3441]">
                    @foreach($rankingData as $data)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#253041] transition-colors group {{ isset($data['tie_reason']) ? 'bg-amber-50/30 dark:bg-amber-900/10' : '' }}">
                        <td class="px-6 py-4 text-center">
                            @if($data['rank'] <= 3)
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white shadow-sm mx-auto
                                    {{ $data['rank'] == 1 ? 'bg-amber-500' : ($data['rank'] == 2 ? 'bg-slate-500' : 'bg-orange-600') }}">
                                    {{ $data['rank'] }}
                                </div>
                            @else
                                <span class="font-bold text-slate-500 font-mono text-lg">#{{ $data['rank'] }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-900 dark:text-white text-base">{{ $data['student']->nama_lengkap }}</span>
                                <span class="text-xs text-slate-500">{{ $data['student']->nis_lokal }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-black text-indigo-600 dark:text-indigo-400 text-lg">{{ number_format($data['total'], 2) }}</span>
                            @if(!($isAnnual ?? false))
                            <div class="text-[10px] text-slate-400 mt-0.5">dari {{ $data['grades_count'] }} Mapel</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center font-medium text-slate-700 dark:text-slate-300">
                             {{ number_format($data['avg'], 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($data['absence'] == 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    Perfekt (0)
                                </span>
                            @else
                                <span class="font-bold text-slate-600">{{ $data['absence'] }} Hari</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($data['tie_reason'])
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border
                                    {{ str_contains($data['tie_reason'], 'Menang') 
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                                        : 'bg-slate-50 text-slate-500 border-slate-200' }}">
                                    @if(str_contains($data['tie_reason'], 'Menang'))
                                        <span class="material-symbols-outlined text-[16px]">verified</span>
                                    @else
                                        <span class="material-symbols-outlined text-[16px]">info</span>
                                    @endif
                                    {{ $data['tie_reason'] }}
                                </div>
                            @else
                                <span class="text-slate-300 transform scale-x-150 inline-block">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
