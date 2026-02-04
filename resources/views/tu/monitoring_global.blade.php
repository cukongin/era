@extends('layouts.app')

@section('title', 'Global Monitoring Nilai')

@section('content')
<div class="flex flex-col h-full overflow-hidden relative">
    
    <!-- Header & Filters -->
    <div class="px-8 py-5 bg-white dark:bg-[#1a2e22] border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 sticky top-0 z-30">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">travel_explore</span>
                Global Monitoring
            </h1>
            <p class="text-xs text-slate-500 mt-1">Pantau progress penginputan nilai seluruh kelas.</p>
        </div>

        <!-- Unified Filter -->
        <form action="{{ route('tu.monitoring.global') }}" method="GET" class="w-full md:w-auto flex flex-col md:flex-row items-stretch md:items-center gap-3">
            <input type="hidden" name="year_id" value="{{ $activeYear->id }}">
            
            <!-- Jenjang Selector -->
            <div class="relative group w-full md:w-auto">
                <select name="jenjang" class="w-full appearance-none bg-none pl-9 pr-8 h-[40px] text-sm font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all md:min-w-[140px]" onchange="this.form.submit()">
                    <option value="" {{ empty(request('jenjang')) ? 'selected' : '' }}>Semua Jenjang</option>
                    @foreach(['MI', 'MTS'] as $j)
                        <option value="{{ $j }}" {{ request('jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[18px]">school</span>
                </div>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <span class="material-symbols-outlined text-[18px]">expand_more</span>
                </div>
            </div>

            <!-- Class Selector -->
            <div class="relative group w-full md:w-auto md:min-w-[200px]">
                <select name="kelas_id" class="w-full appearance-none bg-none pl-9 pr-8 h-[40px] text-sm font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    @if(isset($allClasses))
                        @foreach($allClasses as $kls)
                            <option value="{{ $kls->id }}" {{ request('kelas_id') == $kls->id ? 'selected' : '' }}>
                                {{ $kls->nama_kelas }}
                            </option>
                        @endforeach
                    @endif
                </select>
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[18px]">class</span>
                </div>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                     <span class="material-symbols-outlined text-[18px]">expand_more</span>
                </div>
            </div>

            <!-- Period Selector -->
            <div class="relative group w-full md:w-auto">
                <select name="period_id" class="w-full appearance-none bg-none pl-9 pr-8 h-[40px] text-sm font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all md:min-w-[160px]" onchange="this.form.submit()">
                    <option value="all" {{ $selectedPeriodId == 'all' ? 'selected' : '' }}>Semua Periode</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" {{ ($currentPeriod && $currentPeriod->id == $p->id) ? 'selected' : '' }}>
                            {{ $p->nama_periode }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                </div>
                 <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                     <span class="material-symbols-outlined text-[18px]">expand_more</span>
                </div>
            </div>

        </form>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto px-8 py-6 bg-slate-50 dark:bg-[#121c16]">
        
        @if(count($monitoringData) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($monitoringData as $data)
            <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all p-5 flex flex-col gap-4 relative overflow-hidden group">
                <!-- Progress Background -->
                <div class="absolute bottom-0 left-0 h-1 bg-slate-100 dark:bg-slate-700 w-full">
                    <div class="h-full bg-{{ $data->color }}-500 transition-all duration-1000" style="width: {{ $data->progress }}%"></div>
                </div>

                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $data->class->nama_kelas }}</h3>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold">{{ $data->class->wali_kelas->name ?? 'No Wali' }}</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                        {{ $data->student_count }} Siswa
                    </span>
                </div>

                <div class="flex items-center gap-4 my-2">
                    <div class="relative size-16">
                        <svg class="size-full rotate-[-90deg]" viewBox="0 0 36 36">
                            <path class="text-slate-100 dark:text-slate-700" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" />
                            <path class="text-{{ $data->color }}-500 transition-all duration-1000" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="{{ $data->progress }}, 100" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center flex-col">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $data->progress }}%</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 flex-1">
                        <div class="text-xs text-slate-500">Status Pengisian</div>
                        <div class="text-sm font-bold text-{{ $data->color }}-600">{{ $data->status }}</div>
                    </div>
                </div>

                <div class="mt-auto pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs text-slate-500">
                    <span>{{ $data->mapel_count }} Mapel</span>
                    <span>{{ $data->period_label }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-[50vh] text-slate-400">
            <span class="material-symbols-outlined text-6xl mb-4 opacity-20">search_off</span>
            <p class="text-lg font-medium">Tidak ada data kelas ditemukan.</p>
            <p class="text-sm">Coba ubah filter di atas.</p>
        </div>
        @endif
        
    </div>
</div>
@endsection
