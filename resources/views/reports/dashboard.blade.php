@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard Akademik</h1>
            <p class="text-sm text-gray-500">Tahun Ajaran Aktif: <span class="font-semibold text-primary">{{ $activeYear->nama }}</span></p>
        </div>
        <div>
            <!-- Date Filter or Actions could go here -->
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Siswa -->
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-slate-800 dark:ring-white/10">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                    <span class="material-symbols-outlined text-2xl">school</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Siswa</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $countSiswa }}</p>
                </div>
            </div>
        </div>

        <!-- Guru -->
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-slate-800 dark:ring-white/10">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                    <span class="material-symbols-outlined text-2xl">person_apron</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Guru</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $countGuru }}</p>
                </div>
            </div>
        </div>

        <!-- Kelas -->
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-slate-800 dark:ring-white/10">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-900/20 dark:text-violet-400">
                    <span class="material-symbols-outlined text-2xl">meeting_room</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rombel Aktif</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $countKelas }}</p>
                </div>
            </div>
        </div>

        <!-- Mapel -->
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-slate-800 dark:ring-white/10">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400">
                    <span class="material-symbols-outlined text-2xl">menu_book</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Mata Pelajaran</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $countMapel }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-8">
        <!-- Grade Distribution -->
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-slate-800 dark:ring-white/10">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Distribusi Predikat Nilai</h3>
            <div class="relative h-64 w-full">
                <canvas id="gradeChart"></canvas>
            </div>
        </div>

        <!-- Class Performance -->
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-slate-800 dark:ring-white/10">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Rata-Rata Nilai per Kelas</h3>
            <div class="relative h-64 w-full">
                <canvas id="classChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Progress Table -->
    @if(isset($progressData) && count($progressData) > 0)
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-slate-800 dark:ring-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Progress Penilaian per Kelas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 font-medium">
                    <tr>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Wali Kelas</th>
                        <th class="px-6 py-3 text-center">Total Siswa</th>
                        <th class="px-6 py-3">Progress</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($progressData as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $item['kelas'] }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $item['wali_kelas'] }}</td>
                        <td class="px-6 py-4 text-center">{{ $item['total_siswa'] }}</td>
                        <td class="px-6 py-4 w-1/3">
                            <div class="flex items-center gap-2">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $item['percentage'] }}%"></div>
                                </div>
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 w-8 text-right">{{ $item['percentage'] }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($item['percentage'] < 100)
                            <form action="{{ route('dashboard.remind') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="wali_id" value="{{ $item['wali_id'] }}">
                                <input type="hidden" name="kelas_name" value="{{ $item['kelas'] }}">
                                <button type="submit" class="text-amber-600 hover:text-amber-800 dark:text-amber-500 dark:hover:text-amber-400 font-medium text-xs flex items-center gap-1 justify-end ml-auto" title="Ingatkan Wali Kelas">
                                    <span class="material-symbols-outlined text-[16px]">notifications_active</span> Ingatkan
                                </button>
                            </form>
                            @else
                                <span class="text-emerald-500 flex items-center justify-end gap-1 text-xs font-bold">
                                    <span class="material-symbols-outlined text-[16px]">check_circle</span> Komplit
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data from Controller
    const predikatData = @json($chartPredikat);
    const classData = @json($classPerformance);

    // 1. Grade Distribution Pie
    const ctxGrade = document.getElementById('gradeChart').getContext('2d');
    new Chart(ctxGrade, {
        type: 'doughnut',
        data: {
            labels: ['Excellent (A)', 'Good (B)', 'Average (C)', 'Poor (D)'],
            datasets: [{
                data: [predikatData.A, predikatData.B, predikatData.C, predikatData.D],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });

    // 2. Class Performance Bar
    const ctxClass = document.getElementById('classChart').getContext('2d');
    new Chart(ctxClass, {
        type: 'bar',
        data: {
            labels: classData.map(c => c.nama_kelas),
            datasets: [{
                label: 'Rata-Rata Kelas',
                data: classData.map(c => c.average),
                backgroundColor: '#6366f1',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, max: 100 }
            }
        }
    });
</script>
@endsection
