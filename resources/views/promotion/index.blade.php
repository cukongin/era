@extends('layouts.app')

@section('title', 'Kalkulasi Kenaikan Kelas')

@section('content')
<div class="flex flex-col h-[calc(100vh-80px)]" x-data="promotionPage()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Kalkulasi Kenaikan Kelas</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">
                Sistem otomatis menghitung rekomendasi kenaikan kelas berdasarkan aturan penilaian.
                @if(isset($isLocked) && $isLocked)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        <span class="material-symbols-outlined text-[14px] mr-1">lock</span> Mode Baca
                    </span>
                @endif
            </p>
            @if(isset($warningMessage) && $warningMessage)
            <div class="mt-4 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-amber-500">warning</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700 font-bold">
                            {{ $warningMessage }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- DEBUG PANEL (TEMPORARY) -->
            @if(isset($debugInfo))
            <div class="mt-4 bg-slate-800 text-green-400 p-4 rounded-lg font-mono text-xs overflow-auto border border-slate-700">
                <p class="font-bold text-white border-b border-slate-600 pb-2 mb-2">🕵️ DEBUG MODE ACTIVATED</p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($debugInfo as $k => $v)
                        <div class="text-slate-400">{{ $k }}:</div>
                        <div class="font-bold">{{ $v }}</div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        <div class="flex gap-2">
            <!-- Filter Kelas -->
                </select>
            </form>

            <button type="button" onclick="confirmProcessAll()" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-blue-700 shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">published_with_changes</span>
                Hitung Semua
            </button>
            <form id="form-process-all" action="{{ route('promotion.process_all') }}" method="POST" class="hidden">@csrf</form>

            <button type="button" onclick="confirmFinalize()" class="px-4 py-2 text-sm font-medium text-white bg-slate-800 rounded-lg hover:bg-black shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">lock</span>
                Kunci Permanen
            </button>
            <form id="form-finalize" action="{{ route('promotion.finalize') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1a2332] p-5 rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Santri</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $metrics['total'] }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $selectedClass->nama_kelas ?? '-' }}</p>
            </div>
            <div class="size-12 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary text-2xl">groups</span>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1a2332] p-5 rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Siap Naik Kelas</p>
                <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $metrics['promoted'] }}</p>
                <p class="text-xs text-slate-400 mt-1">Memenuhi semua syarat</p>
            </div>
            <div class="size-12 rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-2xl">check_circle</span>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1a2332] p-5 rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Perlu Peninjauan</p>
                <p class="text-3xl font-bold text-amber-500 dark:text-amber-400 mt-1">{{ $metrics['retained'] }}</p>
                <p class="text-xs text-slate-400 mt-1">Tidak memenuhi syarat</p>
            </div>
            <div class="size-12 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-amber-500 dark:text-amber-400 text-2xl">warning</span>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-[#1a2332] rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm overflow-hidden flex flex-col flex-1">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-[#2a3441] flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50 dark:bg-[#1e2837]">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">table_view</span>
                <h3 class="font-bold text-slate-900 dark:text-white">Daftar Rekomendasi Kenaikan</h3>
            </div>
        </div>
        <div class="overflow-auto flex-1">
            <table class="w-full text-sm text-left relative">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-[#1e2837] dark:text-slate-400 border-b border-slate-100 dark:border-[#2a3441] sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Nama Santri</th>
                        <th class="px-6 py-4 font-semibold text-center">Rata-rata<br/>Tahunan</th>
                        <th class="px-6 py-4 font-semibold text-center">Gagal KKM<br/>(Mapel)</th>
                        <th class="px-6 py-4 font-semibold text-center">Sikap</th>
                        <th class="px-6 py-4 font-semibold text-center">Kehadiran</th>
                        <th class="px-6 py-4 font-semibold">Rekomendasi Sistem</th>
                        <th class="px-6 py-4 font-semibold">Status Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#2a3441]">
                    @foreach($students as $st)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#253041] transition-colors {{ $st->system_recommendation == 'retained' ? 'bg-amber-50/30' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-primary/10 text-primary h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold">
                                    {{ substr($st->nama_lengkap, 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $st->nama_lengkap }}</p>
                                    <p class="text-xs text-slate-500">NIS: {{ $st->nis }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center font-medium">{{ $st->average_score }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $st->kkm_failure_count > 0 ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-800' }}">
                                {{ $st->kkm_failure_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center font-bold">{{ $st->attitude_grade }}</td>
                        <td class="px-6 py-4 text-center">{{ $st->attendance_percent }}%</td>
                        
                        <!-- System Recommendation -->
                        <td class="px-6 py-4">
                             @if($st->system_recommendation == 'promoted')
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Naik Kelas
                                    </span>
                                </div>
                             @elseif($st->system_recommendation == 'conditional')
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Naik Bersyarat
                                    </span>
                                    <span class="text-[10px] text-amber-600 dark:text-amber-400 pl-1">{{ $st->notes }}</span>
                                </div>
                             @else
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Tidak Naik
                                    </span>
                                    <span class="text-[10px] text-red-600 dark:text-red-400 pl-1">{{ $st->notes }}</span>
                                </div>
                             @endif
                        </td>

                        <!-- Manual Override -->
                        <td class="px-6 py-4">
                            <select @change="updateDecision({{ $st->id }}, $event.target.value)" 
                                class="block w-full py-1.5 text-xs border-slate-300 rounded-lg focus:ring-primary focus:border-primary dark:bg-[#101622] dark:border-slate-700 bg-slate-50 {{ $st->final_decision == 'retained' ? 'border-red-300 ring-red-300/50' : ($st->final_decision == 'conditional' ? 'border-amber-300 ring-amber-300/50' : '') }} disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ isset($isLocked) && $isLocked ? 'disabled' : '' }}
                            >
                                <option value="promoted" {{ $st->final_decision == 'promoted' ? 'selected' : '' }}>Naik Kelas</option>
                                <option value="conditional" {{ $st->final_decision == 'conditional' ? 'selected' : '' }}>Naik Bersyarat</option>
                                <option value="retained" {{ $st->final_decision == 'retained' ? 'selected' : '' }}>Tinggal Kelas</option>
                                <option value="pending" {{ $st->final_decision == 'pending' ? 'selected' : '' }}>Ditangguhkan</option>
                            </select>
                        </td>
                    </tr>
                    @endforeach

                    @if(count($students) == 0)
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-500">
                            Belum ada data nilai untuk kelas ini.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function promotionPage() {
        return {
            async updateDecision(id, status) {
                try {
                    const res = await fetch("{{ route('promotion.update') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ decision_id: id, status: status })
                    });
                    
                    if (res.ok) {
                        // Optional: Show toast
                    } else {
                        alert('Gagal menyimpan perubahan');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Error network');
                }
            }
        }
    }

    function confirmProcessAll() {
        if(confirm('Apakah Anda yakin ingin menghitung ulang status kenaikan untuk SEMUA KELAS?\nProses ini akan memastikan seluruh siswa memiliki status kenaikan.')) {
            document.getElementById('form-process-all').submit();
        }
    }

    function confirmFinalize() {
        if(confirm('Apakah Anda yakin ingin MENGUNCI PERMANEN status kenaikan kelas saat ini?\n\nSetelah dikunci, tombol "Hitung Semua" TIDAK AKAN mengubah data lagi.\nIni adalah langkah akhir sebelum cetak Rapor/Leger.')) {
            document.getElementById('form-finalize').submit();
        }
    }
</script>
@endsection
