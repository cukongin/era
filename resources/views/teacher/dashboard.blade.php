@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="flex-1 flex flex-col h-full overflow-hidden relative">
    <!-- Top Header Desktop (User profile, etc) -->
    <header class="hidden lg:flex items-center justify-between px-8 py-5 bg-background-light dark:bg-[#1a2e22]">
        <div>
            <!-- Breadcrumb or simple title can go here if needed -->
            <nav class="text-sm font-medium text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <span class="text-primary font-bold">Portal Guru</span>
                        <span class="mx-2">/</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-slate-700 dark:text-slate-200">Dashboard</span>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="flex items-center gap-4">
            <button class="relative p-2 rounded-full hover:bg-white dark:hover:bg-white/10 transition-colors text-text-secondary dark:text-slate-400">
                <span class="material-symbols-outlined">notifications</span>
                <!-- Notification Dot (Static for now) -->
                <span class="absolute top-2 right-2 size-2 bg-red-500 rounded-full border border-white dark:border-background-dark"></span>
            </button>
            <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-700 mx-2"></div>
            <div class="flex items-center gap-3 pl-2">
                <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 border-2 border-white dark:border-slate-700 shadow-sm flex items-center justify-center bg-primary/10 text-primary uppercase font-bold text-lg">
                    {{ substr($user->name, 0, 2) }}
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-900 dark:text-white leading-none">{{ $user->name }}</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 mt-1">NIP: {{ $user->nip ?? '-' }}</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto px-4 lg:px-8 pb-10 scroll-smooth">
        <div class="max-w-6xl mx-auto flex flex-col gap-8 pt-2">

            <!-- Welcome & Filters -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="flex flex-col gap-2">
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Assalamu'alaikum, {{ $user->name }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm md:text-base">
                        Pantau progres penilaian siswa untuk Tahun Ajaran {{ $activeYear->nama ?? '-' }}.
                    </p>
                </div>
                <div class="flex items-center gap-3 bg-white dark:bg-[#1a2e22] p-1.5 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800">
                    <label class="sr-only">Pilih Periode</label>
                    <div class="pl-3 text-slate-500 dark:text-slate-400">
                        <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                    </div>
                    <!-- Static Select for now, or populate with active periods -->
                    <select class="bg-transparent border-none text-sm font-medium text-slate-900 dark:text-white focus:ring-0 cursor-pointer py-2 pr-8">
                        @foreach($activePeriods as $periode)
                        <option value="{{ $periode->id }}">{{ $periode->nama_periode }}</option>
                        @endforeach
                        @if($activePeriods->isEmpty())
                        <option>Tidak ada periode aktif</option>
                        @endif
                    </select>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Total Classes -->
                <div class="bg-white dark:bg-[#1a2e22] p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between group">
                    <div class="flex flex-col gap-1">
                        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Kelas Diampu</p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $totalClasses }}</h3>
                    </div>
                    <div class="size-12 rounded-full bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-900 dark:text-white group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined">class</span>
                    </div>
                </div>
                <!-- Completed -->
                <div class="bg-white dark:bg-[#1a2e22] p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between group">
                    <div class="flex flex-col gap-1">
                        <p class="text-primary dark:text-primary text-sm font-medium">Kelas Selesai</p>
                        <h3 class="text-3xl font-bold text-primary dark:text-primary">{{ $classesCompleted }}</h3>
                    </div>
                    <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                </div>
                <!-- Pending -->
                <div class="bg-white dark:bg-[#1a2e22] p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between group">
                    <div class="flex flex-col gap-1">
                        <p class="text-orange-500 text-sm font-medium">Belum Selesai</p>
                        <h3 class="text-3xl font-bold text-orange-500">{{ $pendingClasses }}</h3>
                    </div>
                    <div class="size-12 rounded-full bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined">pending_actions</span>
                    </div>
                </div>
            </div>

            <!-- Class Cards Grid -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Daftar Kelas &amp; Mata Pelajaran</h3>
                    <div class="hidden sm:flex relative">
                        <!-- Search functionality could be implemented with Alpine.js or Livewire later -->
                        <input class="pl-10 pr-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#1a2e22] text-sm focus:ring-primary focus:border-primary w-64" placeholder="Cari kelas atau mapel..." type="text"/>
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($classProgress as $item)
                        @php
                            // Styling based on status
                            $cardBorder = 'border-slate-100 dark:border-slate-800';
                            $statusBadge = '';
                            $progressBarColor = 'bg-primary';

                            switch($item->status) {
                                case 'completed':
                                    $statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary text-xs font-bold"><span class="size-1.5 rounded-full bg-primary"></span> Selesai</span>';
                                    break;
                                case 'in_progress':
                                    $cardBorder = 'border-primary border-opacity-30 dark:border-primary/40'; // Highlight
                                    $statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary text-xs font-bold">Akses Terbuka</span>';
                                    break;
                                case 'not_started':
                                    $statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 text-xs font-bold">Belum Mulai</span>';
                                    break;
                                case 'locked':
                                    $statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-xs font-bold"><span class="material-symbols-outlined text-[14px]">lock</span> Terkunci</span>';
                                    $item->percentage = 0; // Force 0 visual
                                    break;
                            }
                        @endphp

                        <div class="bg-white dark:bg-[#1a2e22] rounded-2xl p-6 border {{ $cardBorder }} shadow-sm hover:shadow-md transition-shadow flex flex-col gap-5 relative overflow-hidden {{ $item->status == 'locked' ? 'opacity-75 grayscale-[0.5]' : '' }}">
                            @if($item->status == 'in_progress')
                                <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
                            @endif

                            <div class="flex justify-between items-start">
                                <div class="flex gap-4">
                                    <div class="size-12 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center font-bold text-lg">
                                        {{ explode(' ', $item->nama_kelas)[0] }}
                                    </div>
                                    <div>
                                        <h4 class="text-base font-bold text-slate-900 dark:text-white font-arabic">{{ $item->mapel }}</h4>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $item->nama_kelas }}</p>
                                    </div>
                                </div>
                                {!! $statusBadge !!}
                            </div>

                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">Progres Nilai</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $item->percentage }}%</span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary rounded-full" style="width: {{ $item->percentage }}%"></div>
                                </div>
                                @if($item->status == 'in_progress')
                                    <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">Tinggal {{ $item->total_siswa - $item->graded_count }} siswa belum dinilai</p>
                                @endif
                                @if($item->status == 'locked')
                                    <p class="text-xs text-slate-500 dark:text-slate-500 mt-1 italic">Menunggu jadwal input nilai</p>
                                @endif
                            </div>

                            <div class="pt-2 border-t border-slate-50 dark:border-white/5 flex gap-3 mt-auto">
                                @if($item->status != 'locked')
                                    <a href="{{ route('teacher.input-nilai', ['kelas' => $item->id_kelas, 'mapel' => $item->id_mapel]) }}" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg bg-primary text-white text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all">
                                        @if($item->status == 'completed')
                                            Edit Nilai
                                        @elseif($item->status == 'in_progress')
                                            <span class="material-symbols-outlined text-[18px]">edit</span> Lanjutkan Input
                                        @else
                                            Mulai Input Nilai
                                        @endif
                                    </a>
                                @else
                                    <button class="w-full py-2.5 px-4 rounded-lg bg-slate-100 dark:bg-white/5 text-slate-400 text-sm font-bold cursor-not-allowed" disabled>
                                        Input Belum Dibuka
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer Info -->
            <div class="mt-8 text-center text-xs text-slate-500 dark:text-slate-500 pb-4">
                <p>© {{ date('Y') }} Rapor Madrasah. All rights reserved.</p>
            </div>
        </div>
    </div>
</div>
@endsection
