@extends('layouts.app')

@section('title', 'Leger Nilai')

@section('content')
<div class="flex flex-col h-[calc(100vh-80px)]">
    <!-- Header & Filters Stack -->
    <div class="mb-6 space-y-4">
        <!-- Header Title -->
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Leger Nilai</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Lihat dan cetak Leger Nilai siswa untuk arsip dan evaluasi.</p>
        </div>
        
        <!-- Filters Toolbar (Responsive Grid) -->
        <div class="grid grid-cols-2 md:flex md:flex-row md:flex-wrap items-center gap-3">
            
            <!-- Year Selector -->
            @if(isset($years) && count($years) > 0)
            <form action="{{ route('reports.leger') }}" method="GET" class="col-span-1 w-full md:w-auto">
                <div class="relative group">
                    <select name="year_id" class="w-full appearance-none pl-3 pr-8 py-2.5 text-xs font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all" onchange="this.form.submit()">
                        @foreach($years as $y)
                            <option value="{{ $y->id }}" {{ isset($selectedYear) && $selectedYear->id == $y->id ? 'selected' : '' }}>
                                {{ $y->nama }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>
            </form>
            @endif

            <!-- Jenjang Selector (Converted to Dropdown) -->
            <form action="{{ route('reports.leger') }}" method="GET" class="col-span-1 w-full md:w-auto">
                <input type="hidden" name="year_id" value="{{ $selectedYear->id }}">
                <div class="relative group">
                    <select name="jenjang" class="w-full appearance-none pl-3 pr-8 py-2.5 text-xs font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all" onchange="this.form.submit()">
                        @foreach(['MI', 'MTS'] as $jenjang)
                        <option value="{{ $jenjang }}" {{ (isset($selectedJenjang) && $selectedJenjang == $jenjang) ? 'selected' : '' }}>
                            {{ $jenjang }}
                        </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>
            </form>

            <!-- Class Selector -->
            <form action="{{ route('reports.leger') }}" method="GET" class="col-span-2 md:col-span-auto w-full md:w-auto flex gap-2">
                @if(isset($selectedYear))
                <input type="hidden" name="year_id" value="{{ $selectedYear->id }}">
                <input type="hidden" name="jenjang" value="{{ $selectedJenjang ?? 'MI' }}">
                @endif
                
                @if(count($classes) > 1)
                <div class="relative group w-full md:w-auto">
                    <select name="class_id" class="w-full appearance-none pl-9 pr-8 py-2.5 text-xs font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer md:min-w-[150px] shadow-sm transition-all" onchange="this.form.submit()">
                        <option value="">Pilih Kelas...</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ isset($selectedClass) && $selectedClass->id == $c->id ? 'selected' : '' }}>
                                {{ $c->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[18px]">class</span>
                    </div>
                </div>
                @elseif(count($classes) == 1)
                     <div class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-slate-700 bg-slate-100/50 border-2 border-slate-200 rounded-xl dark:bg-[#1a2332] dark:text-white dark:border-slate-700 w-full md:w-auto justify-center">
                        <span class="material-symbols-outlined text-slate-400 text-[18px]">class</span>
                        {{ $classes->first()->nama_kelas }}
                     </div>
                     <input type="hidden" name="class_id" value="{{ $classes->first()->id }}">
                @else
                    <div class="px-4 py-2 text-xs text-red-500 font-medium bg-red-50 border border-red-200 rounded-xl flex items-center justify-center gap-2 col-span-2 w-full">
                        <span class="material-symbols-outlined text-[18px]">error</span>
                        Tidak Ada Kelas
                    </div>
                @endif
            </form>

            <!-- Period Selector -->
            @if($selectedClass && isset($periodes) && count($periodes) > 0)
            <form action="{{ route('reports.leger') }}" method="GET" class="col-span-2 md:col-span-auto w-full md:w-auto">
                <input type="hidden" name="year_id" value="{{ $selectedYear->id }}">
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                <input type="hidden" name="jenjang" value="{{ $selectedJenjang ?? 'MI' }}">
                @if(request('show_original'))
                <input type="hidden" name="show_original" value="1">
                @endif
                
                <div class="relative group w-full">
                    <select name="period_id" class="w-full appearance-none pl-9 pr-8 py-2.5 text-xs font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer md:min-w-[150px] shadow-sm transition-all" onchange="this.form.submit()">
                        @foreach($periodes as $p)
                            <option value="{{ $p->id }}" {{ isset($periode) && $periode->id == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_periode }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[18px]">date_range</span>
                    </div>
                </div>
            </form>
            @endif

            <!-- X-Ray Toggle -->
            @if($selectedClass && isset($periodes))
            <form action="{{ route('reports.leger') }}" method="GET" class="col-span-2 md:col-span-auto w-full md:w-auto flex items-center justify-end md:justify-start">
                <input type="hidden" name="year_id" value="{{ $selectedYear->id }}">
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                <input type="hidden" name="jenjang" value="{{ $selectedJenjang ?? 'MI' }}">
                <input type="hidden" name="period_id" value="{{ $periode->id ?? '' }}">
                
                <button type="submit" name="show_original" value="{{ $showOriginal ? '0' : '1' }}" 
                    class="w-full md:w-auto flex items-center justify-center gap-2 px-4 py-2 text-xs font-bold rounded-xl border-2 transition-all {{ $showOriginal ? 'bg-amber-100 border-amber-400 text-amber-800' : 'bg-white border-slate-200 text-slate-500 hover:border-primary/50' }}">
                    <span class="material-symbols-outlined {{ $showOriginal ? 'text-amber-600' : 'text-slate-400' }} text-[18px]">visibility</span>
                    {{ $showOriginal ? 'Mode Nilai Asli: ON' : 'Lihat Nilai Asli' }}
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Leger Content -->
    <div class="bg-white dark:bg-[#1a2332] rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm flex-1 overflow-hidden flex flex-col">
        @if($selectedClass && isset($periode))
            <div class="p-4 border-b border-slate-100 dark:border-[#2a3441] flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-50 dark:bg-[#1e2837]">
                <div class="flex flex-col">
                    <span class="font-semibold text-slate-700 dark:text-slate-300">Leger Kelas {{ $selectedClass->nama_kelas }}</span>
                    <span class="text-xs text-slate-500">Periode: {{ $periode->nama_periode }}</span>
                </div>
                
                <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                    <a href="{{ route('reports.leger.rekap.export', ['year_id' => $selectedYear->id, 'class_id' => $selectedClass->id]) }}" target="_blank" class="flex items-center justify-center gap-2 px-3 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-sm border border-indigo-700 w-full md:w-auto">
                        <span class="material-symbols-outlined text-[18px]">table_view</span>
                        Export Rekap Tahunan
                    </a>
                    <a href="{{ route('reports.leger.export', ['year_id' => $selectedYear->id, 'class_id' => $selectedClass->id, 'period_id' => $periode->id, 'show_original' => $showOriginal]) }}" target="_blank" class="flex items-center justify-center gap-2 px-3 py-2 text-sm font-bold text-white {{ $showOriginal ? 'bg-amber-600 hover:bg-amber-700 border-amber-700' : 'bg-emerald-600 hover:bg-emerald-700 border-emerald-700' }} rounded-lg transition shadow-sm border w-full md:w-auto">
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        Export Excel ({{ $showOriginal ? 'Nilai Murni' : 'Semester' }})
                    </a>
                </div>
            </div>
            
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-auto flex-1 relative">
                <div class="min-w-max"> <!-- Container to allow full width for wide table -->
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 uppercase text-xs font-bold text-slate-500 sticky top-0 z-20">
                            <tr>
                                <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 sticky left-0 bg-slate-50 dark:bg-slate-800 z-30 min-w-[50px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">No</th>
                                <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 sticky left-[50px] bg-slate-50 dark:bg-slate-800 z-30 min-w-[250px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">Nama Siswa</th>
                                <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[80px] text-center">L/P</th>
                                
                                @foreach($mapels as $mapel)
                                <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[100px] text-center" title="{{ $mapel->nama_mapel }}">
                                    <div class="truncate max-w-[100px] font-arabic">{{ $mapel->nama_mapel }}</div>
                                    <span class="text-[10px] items-center text-slate-400 font-normal">KKM: {{ $kkm[$mapel->id] ?? 70 }}</span>
                                </th>
                                @endforeach

                                <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[100px] text-center bg-blue-50/50 text-blue-800">Total</th>
                                <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[100px] text-center bg-green-50/50 text-green-800">Rata2</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($students as $index => $ak)
                            @php
                                $totalScore = 0;
                                $countMapel = 0;
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                <td class="px-4 py-3 border-r border-slate-100 dark:border-slate-800 sticky left-0 bg-white dark:bg-surface-dark z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] text-center">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 border-r border-slate-100 dark:border-slate-800 sticky left-[50px] bg-white dark:bg-surface-dark z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] font-medium text-slate-900 dark:text-white truncate max-w-[250px] group relative">
                                    <a href="{{ route('reports.student.analytics', $ak->siswa->id) }}" target="_blank" class="hover:text-primary hover:underline flex items-center gap-1">
                                        {{ $ak->siswa->nama_lengkap }}
                                        <span class="material-symbols-outlined text-[14px] opacity-0 group-hover:opacity-100 text-slate-400 transition-opacity">monitoring</span>
                                    </a>
                                     <div class="text-[10px] text-slate-400 font-normal">{{ $ak->siswa->nis_lokal }}</div>
                                </td>
                                <td class="px-4 py-3 text-center border-r border-slate-100 dark:border-slate-800">{{ $ak->siswa->jenis_kelamin }}</td>

                                <!-- Grades -->
                                @foreach($mapels as $mapel)
                                @php
                                    $grade = $grades->get($ak->id_siswa . '-' . $mapel->id);
                                    
                                    // Default values
                                    $score = 0;
                                    $original = 0;
                                    $isKatrol = false;
                                    
                                    if ($grade) {
                                        $score = $grade->nilai_akhir;
                                        // Use database column if available, else fallback to current score (assuming no change)
                                        $original = $grade->nilai_akhir_asli ?? $grade->nilai_akhir;
                                        
                                        // Detect if adjustment happened
                                        // Use explicit flag OR value difference
                                        $isKatrol = ($score != $original) || ($grade->is_katrol ?? false);
                                    }
                                    
                                    // Determine Replaced Value
                                    $displayScore = ($showOriginal && $grade) ? $original : $score;
                                    
                                    // Accumulate for Total (Always use FINAL score for Total/Avg unless we want X-Ray Total?? Usually Total follows Display?)
                                    // Let's keep Total based on FINAL score to avoid confusion on "Real vs Rapor".
                                    // OR: If viewing Original, maybe Total should be Original?
                                    // Decision: Keep Total as FINAL (RAPOR) reference, but highlight difference? 
                                    // Better: Calculate total based on DISPLAYED value for consistency.
                                    if ($grade) {
                                        $totalScore += $displayScore; 
                                        $countMapel++;
                                    }
                                    
                                    $kkmLocal = $kkm[$mapel->id] ?? 70; 
                                    $isBelowKkm = $displayScore < $kkmLocal;
                                @endphp
                                <td class="px-4 py-3 text-center border-r border-slate-100 dark:border-slate-800 
                                    {{ ($showOriginal && $isKatrol) ? 'bg-amber-100 text-amber-900 font-bold border-amber-200' : '' }}
                                    {{ $isBelowKkm ? 'text-red-600 font-bold' : (($showOriginal && $isKatrol) ? '' : 'text-slate-700 dark:text-slate-300') }}
                                    "
                                    title="{{ $showOriginal && $isKatrol ? 'Nilai Rapor: ' . round($score) : '' }}">
                                    {{ $grade ? number_format($displayScore, 0) : '-' }}
                                    
                                    @if($showOriginal && $isKatrol)
                                        <div class="text-[9px] text-amber-700/70 font-normal leading-none mt-0.5">
                                            Rapor: {{ round($score) }}
                                        </div>
                                    @endif
                                </td>
                                @endforeach

                                <!-- Summary -->
                                <td class="px-4 py-3 text-center font-bold text-blue-600 bg-blue-50/10 border-r border-slate-100 dark:border-slate-800">
                                    {{ $totalScore > 0 ? number_format($totalScore, 0) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-green-600 bg-green-50/10 border-r border-slate-100 dark:border-slate-800">
                                    {{ $countMapel > 0 ? number_format($totalScore / $countMapel, 2) : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden flex flex-col gap-4 p-4 overflow-y-auto flex-1">
                @foreach($students as $index => $ak)
                @php
                    $totalScore = 0;
                    $countMapel = 0;
                    
                    // Pre-calculate Loop for Summary
                     foreach($mapels as $mapel) {
                        $grade = $grades->get($ak->id_siswa . '-' . $mapel->id);
                        $score = 0;
                        $original = 0;
                        if ($grade) {
                             $score = $grade->nilai_akhir;
                             $original = $grade->nilai_akhir_asli ?? $grade->nilai_akhir;
                        }
                        $displayScore = ($showOriginal && $grade) ? $original : $score;
                        if ($grade) {
                             $totalScore += $displayScore; 
                             $countMapel++;
                        }
                     }
                     $avg = $countMapel > 0 ? $totalScore / $countMapel : 0;
                @endphp
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 transition-all" x-data="{ expanded: false }">
                    <!-- Header Summary (Always Visible) -->
                    <div class="p-4 flex flex-col gap-3" @click="expanded = !expanded">
                        <div class="flex items-center gap-3">
                             <!-- Rank Badge (Placeholder) -->
                             <div class="w-10 h-10 flex-shrink-0 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center font-bold border border-indigo-200 shadow-sm">
                                <span class="text-xs uppercase absolute -mt-6 bg-white px-1 rounded text-[8px] text-slate-400 tracking-wider">No</span>
                                {{ $index + 1 }}
                             </div>
                             <div class="flex-1">
                                <h4 class="font-bold text-slate-900 dark:text-white line-clamp-1">{{ $ak->siswa->nama_lengkap }}</h4>
                                <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                    <span>{{ $ak->siswa->nis_lokal }}</span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span>{{ $ak->siswa->jenis_kelamin }}</span>
                                </div>
                             </div>
                             <div class="flex items-center gap-2">
                                <a href="{{ route('reports.student.analytics', $ak->siswa->id) }}" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200 hover:bg-blue-100" title="Analisis Siswa">
                                    <span class="material-symbols-outlined text-[16px]">monitoring</span>
                                </a>
                                <button class="w-8 h-8 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180 bg-indigo-50 border-indigo-200 text-indigo-600' : ''">
                                    <span class="material-symbols-outlined">expand_more</span>
                                </button>
                             </div>
                        </div>
                        
                        <!-- Quick Stats -->
                        <div class="flex gap-2">
                            <div class="flex-1 bg-blue-50/50 border border-blue-100 rounded-lg p-2.5 flex justify-between items-center">
                                <span class="text-[10px] text-blue-600 font-bold uppercase">Total</span>
                                <span class="font-bold text-blue-700">{{ number_format($totalScore, 0) }}</span>
                            </div>
                            <div class="flex-1 bg-green-50/50 border border-green-100 rounded-lg p-2.5 flex justify-between items-center">
                                <span class="text-[10px] text-green-600 font-bold uppercase">Rata2</span>
                                <span class="font-bold text-green-700">{{ number_format($avg, 2) }}</span>
                            </div>
                        </div>
                    </div>
        
                    <!-- Detail Accordion (Hidden by default) -->
                    <div x-show="expanded" x-collapse style="display: none;" class="border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-surface-dark rounded-b-xl p-4">
                        <div class="grid grid-cols-1 gap-2">
                            <div class="grid grid-cols-12 text-[10px] font-bold text-slate-400 uppercase mb-1 px-2">
                                <div class="col-span-8">Mapel</div>
                                <div class="col-span-2 text-center">KKM</div>
                                <div class="col-span-2 text-right">Nilai</div>
                            </div>
                            @foreach($mapels as $mapel)
                            @php
                                $grade = $grades->get($ak->id_siswa . '-' . $mapel->id);
                                $score = 0;
                                $original = 0;
                                $isKatrol = false;
                                if ($grade) {
                                    $score = $grade->nilai_akhir;
                                    $original = $grade->nilai_akhir_asli ?? $grade->nilai_akhir;
                                    $isKatrol = ($score != $original) || ($grade->is_katrol ?? false);
                                }
                                $displayScore = ($showOriginal && $grade) ? $original : $score;
                                $kkmLocal = $kkm[$mapel->id] ?? 70;
                                $isFail = $displayScore < $kkmLocal && $grade;
                            @endphp
                            <div class="grid grid-cols-12 items-center p-2 rounded-lg {{ $isFail ? 'bg-red-50 border border-red-100' : 'hover:bg-slate-50 border border-transparent' }} {{ ($showOriginal && $isKatrol) ? 'bg-amber-50 border-amber-100' : '' }}">
                                <div class="col-span-8 flex flex-col">
                                    <span class="text-xs font-medium {{ $isFail ? 'text-red-800' : 'text-slate-700 dark:text-slate-300' }} {{ ($showOriginal && $isKatrol) ? 'text-amber-900' : '' }}">{{ $mapel->nama_mapel }}</span>
                                    @if($showOriginal && $isKatrol)
                                        <span class="text-[9px] text-amber-600">Rapor: {{ round($score) }} (Asli: {{ $original }})</span>
                                    @endif
                                </div>
                                <div class="col-span-2 text-center">
                                    <span class="text-xs {{ $isFail ? 'text-red-500' : 'text-slate-400' }}">{{ $kkmLocal }}</span>
                                </div>
                                <div class="col-span-2 text-right">
                                    <span class="text-sm font-bold {{ $isFail ? 'text-red-700' : 'text-slate-800 dark:text-white' }} {{ ($showOriginal && $isKatrol) ? 'text-amber-700' : '' }}">
                                        {{ $grade ? number_format($displayScore, 0) : '-' }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center flex-1 p-8 text-center cursor-pointer">
                <div class="bg-slate-100 dark:bg-slate-800 p-4 rounded-full mb-4">
                    <span class="material-symbols-outlined text-4xl text-slate-400">table_view</span>
                </div>
                <h3 class="text-lg font-medium text-slate-900 dark:text-white">Belum Ada Kelas Dipilih</h3>
                <p class="text-slate-500 dark:text-slate-400 mt-1 max-w-sm">Silakan pilih Tahun Ajaran dan Kelas di atas untuk melihat Leger Nilai.</p>
            </div>
        @endif
    </div>
</div>
@endsection

