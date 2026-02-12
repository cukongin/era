@extends('layouts.app')

@section('title', 'Monitoring Progres Nilai')

@section('content')
<div class="flex flex-col gap-8">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Monitoring Progres Nilai</h1>
            <p class="text-slate-500 text-sm">Pantau kelengkapan input nilai guru secara real-time.</p>
        </div>
        <div class="flex items-center gap-3">
             <span class="bg-primary/10 text-primary px-4 py-2 rounded-lg font-bold text-sm">
                Tahun Ajaran: {{ $activeYear->nama ?? '-' }}
             </span>
        </div>
    </div>

    @if(isset($error))
    <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200">
        {{ $error }}
    </div>
    @else

    <!-- Dual Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- MI Dashboard -->
        <div class="flex flex-col gap-8">
            @forelse($dataMI as $data)
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-primary-dark text-white shadow-xl shadow-primary/10">
                <!-- Decorative Circle -->
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
                
                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="rounded-md bg-white/20 px-2 py-0.5 text-xs font-bold text-white uppercase tracking-wider">MI</span>
                                <span class="rounded-md bg-white/20 px-2 py-0.5 text-xs font-bold text-white uppercase tracking-wider">{{ $data['periode_nama'] }}</span>
                            </div>
                            <h2 class="text-2xl font-bold">Progress Penilaian</h2>
                        </div>
                        <div class="text-right">
                            <div class="text-4xl font-black tracking-tight">{{ $data['percent'] }}<span class="text-2xl">%</span></div>
                            <div class="text-xs text-white/70 font-medium">Global Completion</div>
                        </div>
                    </div>

                    <!-- Mini Stats -->
                    <div class="mt-6 grid grid-cols-3 gap-2 border-t border-white/10 pt-4">
                        <div class="text-center">
                            <div class="text-xs text-white/70">Total Kelas</div>
                            <div class="font-bold text-lg">{{ count($data['classes']) }}</div>
                        </div>
                        <div class="text-center border-l border-white/10">
                            <div class="text-xs text-white/70">Mapel Tuntas</div>
                            <div class="font-bold text-lg">{{ collect($data['classes'])->sum('finalized_count') }}</div>
                        </div>
                         <div class="text-center border-l border-white/10">
                            <div class="text-xs text-white/70">Total Mapel</div>
                            <div class="font-bold text-lg">{{ collect($data['classes'])->sum('mapel_diajar_count') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Class Grid -->
                <div class="bg-white dark:bg-surface-dark p-6">
                    <h3 class="font-bold text-slate-700 dark:text-slate-300 mb-4 text-xs uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">grid_view</span> Detail Per Kelas
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @forelse($data['classes'] as $kelas)
                        <div class="group relative rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/20 p-3 hover:border-primary/40 hover:bg-primary/5 dark:hover:border-primary/40 dark:hover:bg-primary/10 transition-all">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-sm text-slate-700 dark:text-slate-200">{{ $kelas->nama_kelas }}</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $kelas->progress_percent == 100 ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $kelas->progress_percent }}%
                                </span>
                            </div>
                            
                            <div class="w-full bg-slate-200 rounded-full h-1.5 dark:bg-slate-700 overflow-hidden mb-1">
                                <div class="{{ $kelas->progress_percent == 100 ? 'bg-green-500' : ($kelas->progress_percent > 50 ? 'bg-primary' : 'bg-orange-500') }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $kelas->progress_percent }}%"></div>
                            </div>
                            
                            <div class="flex justify-between text-[10px] text-slate-500 dark:text-slate-400">
                                <span>{{ $kelas->finalized_count }} Selesai</span>
                                <span>{{ $kelas->mapel_diajar_count - $kelas->finalized_count }} Pending</span>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-2 text-center py-4 text-slate-400 text-xs">Belum ada data kelas.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            @empty
             <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-8 text-center text-slate-400 flex flex-col items-center">
                <span class="material-symbols-outlined text-4xl mb-2">event_busy</span>
                <span>Tidak ada periode aktif (MI)</span>
            </div>
            @endforelse
        </div>

        <!-- MTs Dashboard (Sama tapi Tema Indigo) -->
        <div class="flex flex-col gap-8">
            @forelse($dataMTS as $data)
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-secondary to-primary-dark text-white shadow-xl shadow-primary/10">
                 <!-- Decorative Circle -->
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>

                <div class="relative p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="rounded-md bg-white/20 px-2 py-0.5 text-xs font-bold text-white uppercase tracking-wider">MTS</span>
                                <span class="rounded-md bg-white/20 px-2 py-0.5 text-xs font-bold text-white uppercase tracking-wider">{{ $data['periode_nama'] }}</span>
                            </div>
                            <h2 class="text-2xl font-bold">Progress Penilaian</h2>
                        </div>
                        <div class="text-right">
                            <div class="text-4xl font-black tracking-tight">{{ $data['percent'] }}<span class="text-2xl">%</span></div>
                            <div class="text-xs text-white/70 font-medium">Global Completion</div>
                        </div>
                    </div>

                    <!-- Mini Stats -->
                    <div class="mt-6 grid grid-cols-3 gap-2 border-t border-white/10 pt-4">
                        <div class="text-center">
                            <div class="text-xs text-indigo-200">Total Kelas</div>
                            <div class="font-bold text-lg">{{ count($data['classes']) }}</div>
                        </div>
                        <div class="text-center border-l border-white/10">
                            <div class="text-xs text-white/70">Mapel Tuntas</div>
                            <div class="font-bold text-lg">{{ collect($data['classes'])->sum('finalized_count') }}</div>
                        </div>
                         <div class="text-center border-l border-white/10">
                            <div class="text-xs text-white/70">Total Mapel</div>
                            <div class="font-bold text-lg">{{ collect($data['classes'])->sum('mapel_diajar_count') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Class Grid -->
                <div class="bg-white dark:bg-surface-dark p-6">
                    <h3 class="font-bold text-slate-700 dark:text-slate-300 mb-4 text-xs uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">grid_view</span> Detail Per Kelas
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @forelse($data['classes'] as $kelas)
                        <div class="group relative rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/20 p-3 hover:border-secondary/40 hover:bg-secondary/5 dark:hover:border-secondary/40 dark:hover:bg-secondary/10 transition-all">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-sm text-slate-700 dark:text-slate-200">{{ $kelas->nama_kelas }}</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $kelas->progress_percent == 100 ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $kelas->progress_percent }}%
                                </span>
                            </div>
                            
                            <div class="w-full bg-slate-200 rounded-full h-1.5 dark:bg-slate-700 overflow-hidden mb-1">
                                <div class="{{ $kelas->progress_percent == 100 ? 'bg-green-500' : ($kelas->progress_percent > 50 ? 'bg-white' : 'bg-orange-500') }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $kelas->progress_percent }}%"></div>
                            </div>
                            
                            <div class="flex justify-between text-[10px] text-slate-500 dark:text-slate-400">
                                <span>{{ $kelas->finalized_count }} Selesai</span>
                                <span>{{ $kelas->mapel_diajar_count - $kelas->finalized_count }} Pending</span>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-2 text-center py-4 text-slate-400 text-xs">Belum ada data kelas.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-8 text-center text-slate-400 flex flex-col items-center">
                <span class="material-symbols-outlined text-4xl mb-2">event_busy</span>
                <span>Tidak ada periode aktif (MTs)</span>
            </div>
            @endforelse
        </div>
    </div>

    <!-- The Naughty List (Teacher Cards) -->
    <div class="flex flex-col gap-4 mt-2 mb-10">
        <div class="flex justify-between items-center">
             <h3 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-red-500">warning</span>
                Guru Belum Tuntas ({{ $incompleteTeachers->total() }})
            </h3>
        </div>
        
        <div class="grid grid-cols-1 gap-6">
            @forelse($incompleteTeachers as $teacher)
            <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col md:flex-row">
                
                <!-- Teacher Profile (Left) -->
                <div class="p-6 md:w-1/3 bg-slate-50 dark:bg-slate-800/50 border-r border-slate-100 dark:border-slate-800 flex flex-col items-center text-center justify-center gap-3">
                    <div class="w-20 h-20 rounded-full bg-primary/10 text-primary flex items-center justify-center text-2xl font-bold border-4 border-white shadow-sm">
                        {{ substr($teacher->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 dark:text-slate-100">{{ $teacher->name }}</h4>
                        <p class="text-xs text-slate-500">{{ $teacher->email }}</p>
                    </div>
                    
                    @php
                        $msg = "Assalamualaikum Ustadz/ah " . $teacher->name . ", mohon segera selesaikan nilai rapor. Berikut detail tanggungan: \n";
                        foreach($teacher->items as $item) {
                            $msg .= "- " . $item->mapel . " (" . $item->kelas . "): " . $item->status . "\n";
                        }
                        $msg .= "Terima kasih.";
                        $waLink = "https://wa.me/628?text=" . urlencode($msg);
                    @endphp
                    <a href="{{ $waLink }}" target="_blank" class="w-full mt-2 inline-flex justify-center items-center gap-2 bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-green-600 transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">chat</span>
                        Ingatkan via WA
                    </a>
                </div>

                <!-- Incomplete Items (Right) -->
                <div class="p-6 md:w-2/3 flex flex-col justify-center">
                    <h5 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Tanggungan Mapel</h5>
                    <div class="flex flex-col gap-4">
                        @foreach($teacher->items as $item)
                        <div class="flex flex-col gap-1">
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-bold text-slate-700 dark:text-slate-300">
                                    {{ $item->mapel }} <span class="text-slate-400 font-normal"> - {{ $item->kelas }}</span>
                                </span>
                                <span class="text-xs font-bold {{ $item->percent > 0 ? 'text-yellow-600' : 'text-red-500' }}">
                                    {{ $item->status }}
                                </span>
                            </div>
                            <!-- Progress Bar -->
                            <div class="w-full bg-slate-100 rounded-full h-2 dark:bg-slate-700 overflow-hidden relative group" title="{{ $item->graded }} dari {{ $item->total }} siswa">
                                <div class="bg-primary h-2 rounded-full transition-all duration-500 relative" style="width: {{ $item->percent }}%"></div>
                            </div>
                            <div class="text-[10px] text-slate-400 text-right">
                                Progres: {{ $item->graded }}/{{ $item->total }} Siswa
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @empty
            <div class="p-10 bg-green-50 text-green-700 rounded-xl text-center border border-green-200">
                <span class="material-symbols-outlined text-4xl mb-2">check_circle</span>
                <p class="font-bold">Luar Biasa!</p>
                <p class="text-sm">Semua guru sudah menyelesaikan input nilai.</p>
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            {{ $incompleteTeachers->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

