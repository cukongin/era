@extends('layouts.app')

@section('title', 'Dashboard Tata Usaha')

@section('content')
<div class="flex-1 flex flex-col h-full overflow-hidden relative">

    <!-- Header -->
    <header class="hidden lg:flex items-center justify-between px-8 py-5 bg-background-light dark:bg-[#1a2e22]">
        <div>
            <nav class="text-sm font-medium text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <span class="text-primary font-bold">Portal Tata Usaha</span>
                        <span class="mx-2">/</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-slate-700 dark:text-slate-200">Analitik & Arsip</span>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3 pl-2">
                <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 border-2 border-white dark:border-slate-700 shadow-sm flex items-center justify-center bg-primary text-white uppercase font-bold text-lg">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-900 dark:text-white leading-none">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 mt-1">Staff Tata Usaha</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto px-4 lg:px-8 pb-10 scroll-smooth">
        <div class="max-w-7xl mx-auto flex flex-col gap-8 pt-6">

            <!-- Welcome Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="flex flex-col gap-2">
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Halo, {{ auth()->user()->name }} 👋</h2>
                    <p class="text-slate-500 dark:text-slate-400">
                        Pantau tren performa akademik dan kelola arsip penilaian madrasah.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('tu.monitoring.global') }}" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-bold shadow-sm shadow-primary/30 flex items-center gap-2 transition-all">
                        <span class="material-symbols-outlined text-[18px]">travel_explore</span>
                        Global Monitoring
                    </a>
                    <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold border border-primary/20 shadow-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">analytics</span> Dashboard Analitik
                    </span>
                    <span class="text-slate-400 text-sm font-medium">{{ date('d F Y') }}</span>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Total Siswa -->
                <div class="bg-white dark:bg-[#1a2e22] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <span class="material-symbols-outlined text-6xl text-primary">groups</span>
                    </div>
                    <div class="flex flex-col gap-1 relative z-10">
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">Total Siswa Aktif</p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $totalSiswa }}</h3>
                    </div>
                </div>

                <!-- Total Kelas -->
                <div class="bg-white dark:bg-[#1a2e22] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <span class="material-symbols-outlined text-6xl text-orange-600">meeting_room</span>
                    </div>
                    <div class="flex flex-col gap-1 relative z-10">
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">Total Kelas</p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $totalKelas }}</h3>
                    </div>
                </div>

                <!-- Tahun Ajaran -->
                <div class="bg-gradient-to-br from-[#003e29] to-[#005a3c] text-white p-6 rounded-2xl shadow-lg shadow-primary/20 relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 opacity-20 transform rotate-12">
                        <span class="material-symbols-outlined text-9xl">calendar_month</span>
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div class="flex flex-col gap-1">
                            <p class="text-purple-100 text-xs font-bold uppercase tracking-wider">Tahun Ajaran Aktif</p>
                            <h3 class="text-2xl font-bold">{{ $activeYear->nama }}</h3>
                            <p class="mt-1 text-purple-200 text-sm flex items-center gap-1">
                                <span class="size-2 bg-green-400 rounded-full animate-pulse"></span> {{ ucfirst($activeYear->status ?? 'Aktif') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-8">

                <!-- Main Chart Section -->
                <div class="w-full flex flex-col gap-8">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">monitoring</span> Tren Nilai Per-Angkatan
                        </h3>

                        <!-- Filter Angkatan -->
                        <form action="{{ route('tu.dashboard') }}" method="GET" class="flex items-center gap-2">
                            <label class="text-xs font-bold text-slate-500">Pilih Angkatan:</label>
                            <select name="angkatan_id" onchange="this.form.submit()" class="pl-3 pr-8 py-1.5 rounded-lg text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                @foreach($availableAngkatan as $ta)
                                    <option value="{{ $ta->id }}" {{ $selectedAngkatanId == $ta->id ? 'selected' : '' }}>
                                        Masuk Thn {{ $ta->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="bg-white dark:bg-[#1a2e22] p-6 pb-12 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm min-h-[500px] flex flex-col relative z-20">
                        <div class="mb-4 flex justify-between items-start">
                             <div>
                                <h4 class="font-bold text-slate-800 dark:text-white">{{ $cohortDescription }}</h4>
                                <p class="text-xs text-slate-500">Grafik menunjukkan rata-rata nilai siswa.</p>
                             </div>

                             @if($trend == 'up')
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold border border-green-200 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">trending_up</span> Perform Naik
                                </span>
                             @elseif($trend == 'down')
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold border border-red-200 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">trending_down</span> Perform Turun
                                </span>
                             @else
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold border border-slate-200 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">trending_flat</span> Stabil
                                </span>
                             @endif
                        </div>
                        <div id="gradeTrendChart" class="w-full flex-1 min-h-[400px]"></div>
                    </div>

                    <!-- Achievement Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10 pt-4">
                        <!-- (Best Student & Best Class Cards - Unchanged) -->
                        <div class="bg-gradient-to-br from-primary to-emerald-600 rounded-xl p-4 text-white shadow-lg shadow-primary/20 relative overflow-hidden group">
                            <div class="absolute top-0 right-0 p-3 opacity-20">
                                <span class="material-symbols-outlined text-6xl">emoji_events</span>
                            </div>
                            <div class="relative z-10 flex flex-col h-full justify-between">
                                <div>
                                    <p class="text-emerald-100 text-xs font-bold uppercase tracking-wider mb-1">Siswa Terbaik Angkatan</p>
                                    @if($topStudent)
                                        <h4 class="text-xl font-bold truncate max-w-[90%]">{{ $topStudent['name'] }}</h4>
                                        <p class="text-xs text-emerald-100">Kelas: {{ $topStudent['class'] }}</p>
                                    @else
                                        <h4 class="text-lg font-bold italic opacity-50">Belum ada data</h4>
                                    @endif
                                </div>
                                <div class="mt-3">
                                    @if($topStudent)
                                        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-3 py-1 rounded-lg">
                                            <span class="text-2xl font-bold">{{ $topStudent['score'] }}</span>
                                            <span class="text-[10px] uppercase font-bold text-emerald-100">Rata-Rata Kumulatif</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl p-4 text-white shadow-lg shadow-emerald-500/20 relative overflow-hidden group">
                            <div class="absolute top-0 right-0 p-3 opacity-20">
                                <span class="material-symbols-outlined text-6xl">school</span>
                            </div>
                            <div class="relative z-10 flex flex-col h-full justify-between">
                                <div>
                                    <p class="text-emerald-100 text-xs font-bold uppercase tracking-wider mb-1">Kelas Terbaik (Tahun Terakhir)</p>
                                    @if($bestClass)
                                        <h4 class="text-xl font-bold">{{ $bestClass['name'] }}</h4>
                                    @else
                                        <h4 class="text-lg font-bold italic opacity-50">Belum ada data</h4>
                                    @endif
                                </div>
                                <div class="mt-3">
                                    @if($bestClass)
                                        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-3 py-1 rounded-lg">
                                            <span class="text-2xl font-bold">{{ $bestClass['score'] }}</span>
                                            <span class="text-[10px] uppercase font-bold text-emerald-100">Rata-Rata Kelas</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subject Analysis Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-4">

                        <!-- Hardest Subjects -->
                        <div class="bg-white dark:bg-[#1a2e22] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                            <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                                <span class="material-symbols-outlined text-red-500">warning</span> Mapel Tersulit
                            </h3>
                            <div class="flex flex-col gap-3">
                                @forelse($hardestSubjects as $s)
                                    <div class="flex flex-col gap-1">
                                        <div class="flex justify-between text-xs">
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $s->nama_mapel }}</span>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ round($s->avg_score, 1) }}</span>
                                        </div>
                                        <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-red-400 rounded-full" style="width: {{ $s->avg_score }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic">Belum ada data mapel.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Easiest Subjects -->
                        <div class="bg-white dark:bg-[#1a2e22] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
                            <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                                <span class="material-symbols-outlined text-emerald-500">stars</span> Mapel Termudah
                            </h3>
                            <div class="flex flex-col gap-3">
                                @forelse($easiestSubjects as $s)
                                    <div class="flex flex-col gap-1">
                                        <div class="flex justify-between text-xs">
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $s->nama_mapel }}</span>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ round($s->avg_score, 1) }}</span>
                                        </div>
                                        <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-emerald-400 rounded-full" style="width: {{ $s->avg_score }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic">Belum ada data mapel.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Grade Distribution Chart -->
                        <div class="bg-white dark:bg-[#1a2e22] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 flex flex-col items-center justify-center">
                            <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-2 w-full">
                                <span class="material-symbols-outlined text-primary">pie_chart</span> Sebaran Predikat
                            </h3>
                            <div id="gradeDistributionChart" class="w-full h-[200px]"></div>
                        </div>

                    </div>

                    <!-- Top Achievers Section (Split) -->
                    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <!-- Hall of Fame (All Time Best) -->
                        <div>
                            <div class="flex flex-col mb-4 gap-1">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="material-symbols-outlined text-yellow-500">military_tech</span> Hall of Fame
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Top 5 Siswa Terbaik <strong>Sepanjang Masa</strong> (Termasuk Alumni).
                                </p>
                            </div>

                            <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden h-[340px] flex flex-col">
                                <div class="absolute top-0 right-0 p-8 opacity-5">
                                    <span class="material-symbols-outlined text-[150px]">trophy</span>
                                </div>
                                <div class="flex flex-col gap-3 relative z-10 overflow-y-auto pr-2 custom-scrollbar">
                                    @forelse($hallOfFame as $index => $winner)
                                        <div class="flex items-center justify-between p-3 rounded-xl {{ $index == 0 ? 'bg-yellow-500/20 border border-yellow-500/50' : 'bg-white/5 border border-white/10' }}">
                                            <div class="flex items-center gap-3">
                                                <div class="size-8 rounded-full {{ $index == 0 ? 'bg-yellow-500 text-slate-900' : 'bg-slate-700 text-white' }} flex items-center justify-center font-bold text-sm">
                                                    {{ $index + 1 }}
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-sm">{{ $winner->siswa->nama_lengkap }}</h4>
                                                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">{{ $winner->class_name }}</p>
                                                </div>
                                            </div>
                                            <div class="font-bold text-lg {{ $index == 0 ? 'text-yellow-400' : 'text-slate-300' }}">
                                                {{ $winner->global_avg }}
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-10 text-slate-400 italic">Belum ada data.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Active Stars (Bintang Pelajar) -->
                        <div>
                            <div class="flex flex-col mb-4 gap-1">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">verified</span> 5 Kandidat Bintang Pelajar
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Siswa Terbaik (Aktif) sebagai acuan pemberian penghargaan sekolah.
                                </p>
                            </div>

                            <div class="bg-gradient-to-br from-primary to-emerald-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden h-[340px] flex flex-col">
                                <div class="absolute bottom-0 left-0 p-8 opacity-10">
                                    <span class="material-symbols-outlined text-[150px]">stars</span>
                                </div>

                                <div class="flex flex-col gap-3 h-full relative z-10 overflow-y-auto pr-2 custom-scrollbar">
                                    @forelse($activeStars as $index => $star)
                                        @php
                                            $medals = ['🥇', '🥈', '🥉'];
                                            $medal = $medals[$index] ?? '#' . ($index+1);
                                            $bgClass = $index == 0 ? 'bg-white/20 border-white/40' : 'bg-white/10 border-white/10';
                                        @endphp
                                        <div class="flex items-center justify-between p-3 rounded-xl border {{ $bgClass }} backdrop-blur-sm transition-transform hover:scale-[1.02]">
                                            <div class="flex items-center gap-3">
                                                <div class="text-2xl filter drop-shadow-md min-w-[30px] text-center">{{ $medal }}</div>
                                                <div>
                                                    <h4 class="font-bold text-sm">{{ $star->nama_lengkap }}</h4>
                                                    <p class="text-[10px] text-emerald-100 flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-[10px]">class</span> {{ $star->class_name }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex flex-col items-end">
                                                <span class="text-lg font-bold">{{ $star->avg_score }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="flex items-center justify-center h-full text-blue-200 italic">
                                            Belum ada siswa aktif dengan data cukup.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- ApexCharts via CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: [{
                name: 'Rata-Rata Angkatan',
                data: @json($chartData)
            }],
            chart: {
                height: '100%',
                type: 'area',
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            colors: ['#8b5cf6'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: @json($chartLabels),
                labels: {
                    style: { colors: '#64748b', fontSize: '12px' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                min: 0,
                max: 100,
                labels: {
                    style: { colors: '#64748b', fontSize: '12px' }
                }
            },
            grid: {
                borderColor: '#e2e8f0',
                strokeDashArray: 4,
                padding: { top: 0, right: 0, bottom: 0, left: 10 }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " Poin"
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#gradeTrendChart"), options);
        chart.render();

        // --- Grade Distribution Chart ---
        var gradeDist = @json($gradeDistribution);
        var pieOptions = {
            series: [gradeDist.A, gradeDist.B, gradeDist.C, gradeDist.D],
            labels: ['Sangat Baik (A)', 'Baik (B)', 'Cukup (C)', 'Kurang (D)'],
            chart: {
                type: 'donut',
                height: 220,
                fontFamily: 'Inter, sans-serif',
            },
            colors: ['#4ade80', '#60a5fa', '#facc15', '#f87171'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontSize: '11px' },
        };

        var pieChart = new ApexCharts(document.querySelector("#gradeDistributionChart"), pieOptions);
        pieChart.render();
    });
</script>
@endsection
