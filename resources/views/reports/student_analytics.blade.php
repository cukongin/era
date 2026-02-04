@extends('layouts.app')

@section('title', 'Analytics: ' . $student->nama_lengkap)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Analisis Perkembangan Siswa</h1>
            <p class="text-slate-500">{{ $student->nama_lengkap }} ({{ $student->nis_lokal }}) - {{ $kelas->nama_kelas }}</p>
        </div>
        <a href="{{ route('reports.leger', ['class_id' => $kelas->id]) }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg font-medium hover:bg-slate-200 transition">
            &larr; Kembali ke Leger
        </a>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- 1. Current Performance (Bar Chart) -->
        <div class="bg-white dark:bg-[#1a2332] p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500">bar_chart_4_bars</span>
                Perbandingan Nilai: Rapor vs Murni
            </h3>
            <div class="h-[300px]">
                <canvas id="gradeComparisonChart"></canvas>
            </div>
            <p class="text-xs text-center text-slate-400 mt-2">Periode: {{ $periode->nama_periode }}</p>
        </div>

        <!-- 2. Historical Trend (Line Chart) -->
        <div class="bg-white dark:bg-[#1a2332] p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-500">ssid_chart</span>
                Tren Rata-Rata Nilai (GPA)
            </h3>
            <div class="h-[300px]">
                <canvas id="trendChart"></canvas>
            </div>
            <p class="text-xs text-center text-slate-400 mt-2">Riwayat Akademik</p>
        </div>
    </div>

</div>

<!-- Auto-Import Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Data from Controller
        const mapels = @json($mapelNames);
        const finalGrades = @json($finalGrades);
        const originalGrades = @json($originalGrades);
        
        const periods = @json($periodNames);
        const trends = @json($gpaTrends);

        // 1. Comparison Chart
        const ctx1 = document.getElementById('gradeComparisonChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: mapels,
                datasets: [
                    {
                        label: 'Nilai Rapor',
                        data: finalGrades,
                        backgroundColor: 'rgba(99, 102, 241, 0.7)', // Indigo
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Nilai Murni (Guru)',
                        data: originalGrades,
                        backgroundColor: 'rgba(251, 191, 36, 0.7)', // Amber
                        borderColor: 'rgba(251, 191, 36, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            afterBody: function(context) {
                                const idx = context[0].dataIndex;
                                const diff = finalGrades[idx] - originalGrades[idx];
                                return diff > 0 ? `Katrol: +${diff}` : '';
                            }
                        }
                    }
                }
            }
        });

        // 2. Trend Chart
        const ctx2 = document.getElementById('trendChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: periods,
                datasets: [{
                    label: 'Rata-Rata Nilai',
                    data: trends,
                    borderColor: 'rgba(16, 185, 129, 1)', // Emerald
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(16, 185, 129, 1)',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 50,
                        max: 100
                    }
                }
            }
        });
    });
</script>
@endsection
