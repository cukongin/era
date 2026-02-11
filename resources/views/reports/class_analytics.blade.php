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

        <div class="hidden">
            <!-- Period Selector Removed (Forced Annual) -->
        </div>
    </div>
    
    <!-- Annual Mode Info Banner -->
    @if($isAnnual ?? false)
    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 rounded-xl p-4 flex items-start gap-3">
        <span class="material-symbols-outlined text-purple-600 dark:text-purple-400 mt-0.5">info</span>
        <div>
            <h3 class="font-bold text-purple-800 dark:text-purple-300 text-sm">Mode Analisa Tahunan Aktif</h3>
            <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">
                Data yang ditampilkan adalah <strong>akumulasi dari semua periode</strong> di tahun ajaran ini.
                <br>â€¢ <strong>Total Rata-rata:</strong> Rata-rata dari nilai akhir setiap mapel (Lintas Periode).
                <br>â€¢ <strong>Kehadiran:</strong> Total jumlah ketidakhadiran (Sakit/Izin/Alpa) selama satu tahun penuh.
            </p>
        </div>
    </div>
    @endif


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
                    <div class="absolute -top-10 left-1/2 -translate-x-1/2 text-4xl animate-bounce">ðŸ‘‘</div>
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
                            ðŸ† {{ $podium[0]['tie_reason'] }}
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
    
    <!-- 2.5 Advanced Analytics Dashboard (Mapel & Anomaly & Role Models) -->
    @if(isset($mapelAnalysis) || (isset($anomalies) && count($anomalies) > 0) || (isset($roleModels) && count($roleModels) > 0))
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <!-- Peta Mapel (Neraka/Surga) -->
        <!-- Comparison: Non-Anomaly Students -->
        @if(isset($roleModels) && count($roleModels) > 0)
        <div class="bg-white dark:bg-[#1a2332] rounded-xl border border-slate-200 dark:border-[#2a3441] p-4 shadow-sm">
            <h3 class="font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500">verified_user</span> Siswa Berprestasi (Normal)
            </h3>
            <div class="space-y-2">
                @foreach($roleModels as $goodStudent)
                <div onclick="showAnalyticsModal('insight', 'Siswa Berprestasi Normal âœ…', '{{ $goodStudent['student']->nama_lengkap }} adalah pembanding positif.', 'Rank #{{ $goodStudent['rank'] }} dengan {{ $goodStudent['alpha'] }} Alpha (Wajar).')"
                     class="bg-indigo-50 dark:bg-indigo-900/10 p-2 rounded-lg border border-indigo-100 dark:border-indigo-800/30 flex justify-between items-center cursor-pointer hover:bg-indigo-100 transition-colors">
                    <div>
                        <div class="font-bold text-slate-800 dark:text-white text-sm">
                             Rank #{{ $goodStudent['rank'] }} - {{ $goodStudent['student']->nama_lengkap }}
                        </div>
                        <div class="text-xs text-indigo-600 dark:text-indigo-400">
                             <strong>{{ $goodStudent['alpha'] }} Alpha</strong> (Non-Paradox)
                        </div>
                    </div>
                     <span class="material-symbols-outlined text-indigo-400 text-sm">check_circle</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Anomaly Detection -->
         @if(isset($anomalies) && count($anomalies) > 0)
        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800/50 p-4 shadow-sm relative overflow-hidden">
             <!-- Background Warning Icon -->
            <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-9xl text-amber-500/10 pointer-events-none">warning</span>
            
            <h3 class="font-bold text-amber-800 dark:text-amber-300 mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined">warning</span> Deteksi Anomali (Paradoks)
            </h3>
            <div class="space-y-2">
                @foreach($anomalies as $badStudent)
                <div onclick="showAnalyticsModal('anomaly', 'Deteksi Paradoks âš ï¸', '{{ $badStudent['student']->nama_lengkap }} ada di Top 5 tapi Alpha Tinggi.', 'Rank #{{ $badStudent['rank'] }} dengan {{ $badStudent['alpha'] }} Alpha. Cek alasan bolos!')"
                     class="bg-white/80 dark:bg-[#121c16]/50 p-2 rounded-lg border border-amber-200/50 flex justify-between items-center cursor-pointer hover:bg-amber-100 transition-colors">
                    <div>
                        <div class="font-bold text-slate-800 dark:text-white text-sm">
                             Rank #{{ $badStudent['rank'] }} - {{ $badStudent['student']->nama_lengkap }}
                        </div>
                        <div class="text-xs text-amber-700 dark:text-amber-400">
                            Prestasi Tinggi tapi <strong>{{ $badStudent['alpha'] }} Alpha</strong>
                        </div>
                    </div>
                     <span class="material-symbols-outlined text-amber-500 animate-pulse">priority_high</span>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <!-- Empty State for Anomalies (Good thing) -->
         <div class="bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-[#2a3441] p-4 flex flex-col items-center justify-center text-center opacity-70">
             <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">check_circle</span>
             <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400">Tidak Ada Anomali</h3>
             <p class="text-xs text-slate-400">Semua siswa top disiplin.</p>
         </div>
        @endif
        
        <!-- Role Models Comparison above -->
        
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
        
        <!-- Mobile Card View (Visible only on mobile) -->
        <div class="md:hidden space-y-3 p-4 bg-slate-50 dark:bg-[#1e2837] border-b border-slate-100 dark:border-[#2a3441]">
             <h3 class="font-bold text-slate-700 dark:text-slate-300 mb-2">Daftar Peringkat {{ ($isAnnual ?? false) ? 'Tahunan' : 'Periode' }}</h3>
             
             @foreach($rankingData as $data)
             <div class="bg-white dark:bg-[#1f2937] p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div class="flex gap-3">
                         <!-- Rank Badge -->
                        <div class="flex flex-col items-center gap-1">
                             <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white shadow-sm border-2 border-white dark:border-slate-700
                                {{ $data['rank'] == 1 ? 'bg-amber-500' : ($data['rank'] == 2 ? 'bg-slate-500' : ($data['rank'] == 3 ? 'bg-orange-600' : 'bg-indigo-500')) }}">
                                #{{ $data['rank'] }}
                            </div>
                            
                             <!-- Mobile Trend Indicator -->
                            @if(isset($data['trend_status']))
                                @if($data['trend_status'] == 'rising')
                                    <button onclick="showAnalyticsModal('rising', 'Rocket Star ðŸš€', '{{ $data['student']->nama_lengkap }} melesat naik {{ $data['trend_diff'] }} peringkat!', 'Dari Ranking #{{ $data['prev_rank'] }} ke #{{ $data['rank'] }}')" 
                                        class="material-symbols-outlined text-emerald-500 text-lg animate-bounce">rocket_launch</button>
                                @elseif($data['trend_status'] == 'falling')
                                    <button onclick="showAnalyticsModal('falling', 'Perlu Evaluasi ðŸ“‰', '{{ $data['student']->nama_lengkap }} turun {{ abs($data['trend_diff']) }} peringkat!', 'Dari Ranking #{{ $data['prev_rank'] }} anjlok ke #{{ $data['rank'] }}')" 
                                        class="material-symbols-outlined text-rose-500 text-lg">trending_down</button>
                                @elseif($data['trend_status'] == 'comeback')
                                    <button onclick="showAnalyticsModal('rising', 'Raja Comeback ðŸ‘‘', '{{ $data['student']->nama_lengkap }} berhasil bangkit!', 'Awal: Rank #{{ $data['start_rank'] }} âž” Akhir: Rank #{{ $data['end_rank'] }}')" 
                                        class="material-symbols-outlined text-purple-500 text-lg animate-pulse">crown</button>
                                @elseif($data['trend_status'] == 'dropped')
                                    <button onclick="showAnalyticsModal('falling', 'Early Bird ðŸ“‰', '{{ $data['student']->nama_lengkap }} turun di akhir.', 'Awal: Rank #{{ $data['start_rank'] }} âž” Akhir: Rank #{{ $data['end_rank'] }}')" 
                                        class="material-symbols-outlined text-orange-500 text-lg">history_toggle_off</button>
                                @endif
                            @endif
                        </div>
                        
                        <!-- Details -->
                        <div>
                            <div class="font-bold text-slate-800 dark:text-white line-clamp-1">{{ $data['student']->nama_lengkap }}</div>
                            <div class="text-xs text-slate-400 mb-1">{{ $data['student']->nis_lokal }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded border border-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800">
                                    {{ number_format($data['total'], 2) }} Poin
                                </span>
                                <span class="text-xs text-slate-500">Avg: {{ number_format($data['avg'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Insight Badge (Full Width on Mobile) -->
                 @if(!empty($data['insight']))
                 <div class="mt-3 pt-3 border-t border-slate-50 dark:border-slate-800">
                     <div onclick="showAnalyticsModal('insight', 'Detail Predikat Siswa', '{{ $data['insight'] }}', 'Total Nilai: {{ number_format($data['total'], 2) }} â€¢ Rata-rata: {{ number_format($data['avg'], 2) }} â€¢ Alpha: {{ $data['alpha'] }}')"
                        class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold border cursor-pointer hover:brightness-95 transition-all
                        {{ str_contains($data['insight'], 'Kalah') || str_contains($data['insight'], 'Perhatian')
                            ? 'bg-red-50 text-red-700 border-red-200' 
                            : (str_contains($data['insight'], 'Menang') || str_contains($data['insight'], 'Juara') || str_contains($data['insight'], 'Sempurna') || str_contains($data['insight'], 'Raja') || str_contains($data['insight'], 'Dewa')
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                : 'bg-blue-50 text-blue-700 border-blue-200') }}">
                        @if(str_contains($data['insight'], 'Menang') || str_contains($data['insight'], 'Juara') || str_contains($data['insight'], 'Sempurna'))
                            <span class="material-symbols-outlined text-[16px]">verified</span>
                        @elseif(str_contains($data['insight'], 'Kalah') || str_contains($data['insight'], 'Perhatian'))
                            <span class="material-symbols-outlined text-[16px]">warning</span>
                        @else
                            <span class="material-symbols-outlined text-[16px]">auto_awesome</span>
                        @endif
                        {{ $data['insight'] }}
                    </div>
                 </div>
                 @endif
                 
                 <!-- Absence Indicator (Absolute or inline) -->
                 @if($data['alpha'] > 0)
                 <div class="absolute top-2 right-2 flex items-center gap-1 text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                     <span class="material-symbols-outlined text-[10px]">cancel</span> {{ $data['alpha'] }} Alpha
                 </div>
                 @endif
             </div>
             @endforeach
        </div>

        <!-- Desktop Table (Hidden on Mobile) -->
        <!-- Desktop Table (Hidden on Mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-[#1e2837] dark:text-slate-400 border-b border-slate-100 dark:border-[#2a3441]">
                    <tr>
                        <th class="px-6 py-4 font-bold text-center w-20">Rank</th>
                        <th class="px-6 py-4 font-bold">Nama Siswa / NIS</th>
                        <th class="px-6 py-4 font-bold text-center">{{ ($isAnnual ?? false) ? 'Total Rata-rata' : 'Total Nilai' }}</th>
                        <th class="px-6 py-4 font-bold text-center">Rata-rata</th>
                        <th class="px-6 py-4 font-bold text-center">Alpha (Tanpa Keterangan)</th>
                        <th class="px-6 py-4 font-bold text-center">Kepribadian</th>
                        <th class="px-6 py-4 font-bold text-center">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#2a3441]">
                    @foreach($rankingData as $data)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#1f2937]/50 border-b border-slate-100 dark:border-slate-800 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2 relative">
                                <span class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 font-bold py-1 px-3 rounded-full text-sm">
                                    #{{ $data['rank'] }}
                                </span>
                                
                                <!-- Trend Indicator -->
                                @if(isset($data['trend_status']))
                                    @if($data['trend_status'] == 'rising')
                                        <button onclick="showAnalyticsModal('rising', 'Rocket Star ðŸš€', '{{ $data['student']->nama_lengkap }} melesat naik {{ $data['trend_diff'] }} peringkat!', 'Dari Ranking #{{ $data['prev_rank'] }} ke #{{ $data['rank'] }}')" 
                                            class="material-symbols-outlined text-emerald-500 text-lg animate-bounce cursor-pointer hover:scale-125 transition-transform" 
                                            title="Klik untuk detail">rocket_launch</button>
                                    @elseif($data['trend_status'] == 'falling')
                                        <button onclick="showAnalyticsModal('falling', 'Perlu Evaluasi ðŸ“‰', '{{ $data['student']->nama_lengkap }} turun {{ abs($data['trend_diff']) }} peringkat.', 'Dari Ranking #{{ $data['prev_rank'] }} anjlok ke #{{ $data['rank'] }}')" 
                                            class="material-symbols-outlined text-rose-500 text-lg cursor-pointer hover:scale-125 transition-transform" 
                                            title="Klik untuk detail">trending_down</button>
                                    
                                    {{-- Annual Trends --}}
                                    @elseif($data['trend_status'] == 'comeback')
                                         <button onclick="showAnalyticsModal('rising', 'Raja Comeback ðŸ‘‘', '{{ $data['student']->nama_lengkap }} berhasil bangkit dari peringkat bawah!', 'Awal: Rank #{{ $data['start_rank'] }} âž” Akhir: Rank #{{ $data['end_rank'] }}')" 
                                            class="material-symbols-outlined text-purple-500 text-lg animate-pulse cursor-pointer hover:scale-125 transition-transform" 
                                            title="Klik untuk detail">crown</button>
                                    @elseif($data['trend_status'] == 'dropped')
                                         <button onclick="showAnalyticsModal('falling', 'Early Bird ðŸ“‰', '{{ $data['student']->nama_lengkap }} mengalami penurunan performa di akhir tahun.', 'Awal: Rank #{{ $data['start_rank'] }} âž” Akhir: Rank #{{ $data['end_rank'] }}')" 
                                            class="material-symbols-outlined text-orange-500 text-lg cursor-pointer hover:scale-125 transition-transform" 
                                            title="Klik untuk detail">history_toggle_off</button>
                                    @elseif($data['trend_status'] == 'stable_high')
                                         <button onclick="showAnalyticsModal('stable', 'Dewa Stabil ðŸ›¡ï¸', '{{ $data['student']->nama_lengkap }} konsisten di papan atas sepanjang tahun.', 'Selalu berada di Top Tier peringkat kelas.')" 
                                            class="material-symbols-outlined text-blue-500 text-lg cursor-pointer hover:scale-125 transition-transform" 
                                            title="Klik untuk detail">shield</button>
                                    
                                    @elseif($data['trend_status'] == 'stable')
                                         <button onclick="showAnalyticsModal('stable', 'Performa Stabil âš“', '{{ $data['student']->nama_lengkap }} mempertahankan posisinya.', 'Tidak ada perubahan peringkat yang signifikan.')" 
                                            class="material-symbols-outlined text-slate-400 text-lg cursor-pointer hover:scale-125 transition-transform" 
                                            title="Klik untuk detail">remove</button>
                                            
                                    {{-- Minor Trends --}}
                                    @elseif($data['trend_status'] == 'up' || $data['trend_status'] == 'improved')
                                        <span class="material-symbols-outlined text-emerald-400 text-base" title="Naik dari sebelumnya">arrow_upward</span>
                                    @elseif($data['trend_status'] == 'down')
                                        <span class="material-symbols-outlined text-rose-400 text-base" title="Turun dari sebelumnya">arrow_downward</span>
                                    @endif
                                @endif
                                
                                <!-- Journey Path (Annual Only) -->
                                @if(isset($data['rank_journey']) && count($data['rank_journey']) > 1)
                                <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-white dark:bg-slate-700 text-[9px] font-mono font-bold text-slate-500 border border-slate-200 dark:border-slate-600 rounded-md px-2 py-1 shadow-lg whitespace-nowrap z-20 hidden group-hover:block transition-all animate-fade-in pointer-events-none">
                                    <div class="text-[8px] text-slate-400 mb-0.5 border-b border-slate-100 pb-0.5">Riwayat Ranking</div>
                                    <div class="flex items-center gap-1">
                                    @foreach($data['rank_journey'] as $j)
                                        <span class="{{ $loop->last ? 'text-indigo-600 font-black' : '' }}">#{{ $j['rank'] }}</span>
                                        @if(!$loop->last) <span class="text-slate-300">âžœ</span> @endif
                                    @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
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
                            @if($data['alpha'] == 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    Nihil (0)
                                </span>
                            @else
                                <span class="font-bold text-slate-600 dark:text-slate-400">{{ $data['alpha'] }} Alpha</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center max-w-[150px]">
                            <span class="text-xs text-slate-600 dark:text-slate-400 italic line-clamp-2" title="{{ $data['personality'] ?? '-' }}">
                                {{ $data['personality'] ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if(!empty($data['insight']))
                                <div onclick="showAnalyticsModal('insight', 'Detail Predikat Siswa', '{{ $data['insight'] }}', 'Total Nilai: {{ number_format($data['total'], 2) }} â€¢ Alpha: {{ $data['alpha'] }} â€¢ Sikap: {{ $data['personality'] ?? '-' }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border max-w-[200px] leading-tight cursor-pointer hover:scale-105 transition-transform shadow-sm select-none
                                    {{ str_contains($data['insight'], 'Kalah') || str_contains($data['insight'], 'Perhatian') || str_contains($data['insight'], 'Awas')
                                        ? 'bg-red-50 text-red-700 border-red-200' 
                                        : (str_contains($data['insight'], 'Menang') || str_contains($data['insight'], 'Juara') || str_contains($data['insight'], 'Sempurna') || str_contains($data['insight'], 'Raja') || str_contains($data['insight'], 'Dewa')
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                            : 'bg-blue-50 text-blue-700 border-blue-200') }}">
                                    @if(str_contains($data['insight'], 'Menang') || str_contains($data['insight'], 'Juara') || str_contains($data['insight'], 'Sempurna'))
                                        <span class="material-symbols-outlined text-[16px]">verified</span>
                                    @elseif(str_contains($data['insight'], 'Kalah') || str_contains($data['insight'], 'Perhatian') || str_contains($data['insight'], 'Awas'))
                                        <span class="material-symbols-outlined text-[16px]">warning</span>
                                    @else
                                        <span class="material-symbols-outlined text-[16px]">auto_awesome</span>
                                    @endif
                                    {{ $data['insight'] }}
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

@push('scripts')
<script>
    function showAnalyticsModal(type, title, message, subtext = '') {
        const modal = document.getElementById('analyticsModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        const modalIcon = document.getElementById('modalIcon');
        
        // Reset Classes
        modalIcon.className = 'material-symbols-outlined text-4xl mb-2';
        
        // Content
        modalTitle.innerText = title;
        modalBody.innerHTML = `<p class="font-bold text-lg">${message}</p><p class="text-slate-500 text-sm mt-1">${subtext}</p>`;
        
        // Styling based on Type
        if (type === 'rising') {
            modalIcon.classList.add('text-emerald-500');
            modalIcon.innerText = 'rocket_launch';
        } else if (type === 'falling') {
            modalIcon.classList.add('text-rose-500');
            modalIcon.innerText = 'trending_down';
        } else if (type === 'anomaly') {
            modalIcon.classList.add('text-amber-500');
            modalIcon.innerText = 'warning';
        } else if (type === 'stable') {
            modalIcon.classList.add('text-blue-500');
            modalIcon.innerText = 'shield';
        }
        
        // Show
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeAnalyticsModal() {
        const modal = document.getElementById('analyticsModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush

<!-- Analytics Detail Modal -->
<div id="analyticsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all">
    <div class="bg-white dark:bg-[#1f2937] rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center transform scale-100 transition-transform relative">
        <button onclick="closeAnalyticsModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <span class="material-symbols-outlined">close</span>
        </button>
        
        <div class="flex flex-col items-center">
            <span id="modalIcon" class="material-symbols-outlined text-4xl mb-2">info</span>
            <h3 id="modalTitle" class="text-xl font-black text-slate-800 dark:text-white mb-2">Detail Analisa</h3>
            <div id="modalBody" class="text-slate-600 dark:text-slate-300">
                <!-- Content -->
            </div>
            
            <button onclick="closeAnalyticsModal()" class="mt-6 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200 font-bold py-2 px-6 rounded-full hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

