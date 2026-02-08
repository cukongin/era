@extends('layouts.app')

@section('title', $pageContext['title'])

@section('content')
<div class="flex flex-col h-[calc(100vh-80px)]" x-data="promotionPage()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                @if($pageContext['type'] == 'graduation')
                    <span class="material-symbols-outlined text-indigo-600">school</span>
                @endif
                {{ $pageContext['title'] }}
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">
                Sistem otomatis menghitung rekomendasi status akhir siswa berdasarkan aturan penilaian.
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
            <form id="filter-kelas" action="{{ route('promotion.index') }}" method="GET">
                <select name="class_id" onchange="this.form.submit()" class="bg-white dark:bg-[#1a2332] border border-slate-200 dark:border-[#2a3441] text-slate-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary block w-48 p-2.5 shadow-sm font-bold">
                    @foreach($allClasses as $c)
                        <option value="{{ $c->id }}" {{ isset($selectedClass) && $selectedClass->id == $c->id ? 'selected' : '' }}>
                            {{ $c->nama_kelas }} ({{ strtoupper(optional($c->jenjang)->kode) }})
                        </option>
                    @endforeach
                </select>
            </form>

            <button type="button" onclick="confirmProcessAll()" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-blue-700 shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">published_with_changes</span>
                Hitung Ulang
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
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $pageContext['success_label'] }}</p>
                <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $metrics['promoted'] }}</p>
                <p class="text-xs text-slate-400 mt-1">Memenuhi syarat</p>
            </div>
            <div class="size-12 rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-2xl">check_circle</span>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1a2332] p-5 rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Tidak Memenuhi Syarat</p>
                <p class="text-3xl font-bold text-amber-500 dark:text-amber-400 mt-1">{{ $metrics['retained'] }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $pageContext['fail_label'] }}</p>
            </div>
            <div class="size-12 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-amber-500 dark:text-amber-400 text-2xl">warning</span>
            </div>
        </div>
    </div>

    <!-- DEBUG ALERT FOR USER FEEDBACK -->
    <div class="bg-indigo-100 border-l-4 border-indigo-500 text-indigo-700 p-4 mb-4 rounded shadow-sm">
        <p class="font-bold text-sm">🕵️ DEBUGGING INFO (KELULUSAN) - VISIBLE TO EVERYONE:</p>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-xs font-mono mt-2">
            <div>
               <span class="block font-bold">SETTING MI:</span>
               {{ \App\Models\GlobalSetting::val('final_grade_mi', 6) }}
            </div>
            <div>
               <span class="block font-bold">SETTING MTS:</span>
               {{ \App\Models\GlobalSetting::val('final_grade_mts', 9) }}
            </div>
            <div>
               <span class="block font-bold">KELAS TERPILIH:</span>
               {{ $selectedClass->nama_kelas ?? 'NONE' }}
            </div>
            <div>
               <span class="block font-bold">JENJANG DETECTED (Code):</span>
               {{ isset($selectedClass->jenjang) ? $selectedClass->jenjang->kode : 'NULL' }}
            </div>
            <div class="col-span-2">
               <span class="block font-bold">STATUS IS_FINAL_YEAR:</span>
               @if(isset($isFinalYear) && $isFinalYear)
                    <span class="bg-green-200 text-green-800 px-1 rounded font-bold">TRUE (LULUS)</span>
               @else
                    <span class="bg-red-200 text-red-800 px-1 rounded font-bold">FALSE (NAIK)</span>
               @endif
            </div>
            <div class="col-span-2">
                <span class="block font-bold">RAW:</span>
                MI Match: {{ ($isMi ?? '0') }} | MTS Match: {{ ($isMts ?? '0') }} | Level OK: {{ (($gradeLevel == 9 || $gradeLevel == 3) ? 'YES' : 'NO') }}
            </div>
        </div>
        <p class="text-[10px] mt-2 italic text-slate-500">
            Logic: (Jenjang MTS && (Level == FinalMTS OR (FinalMTS == 9 && Level == 3)))
        </p>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-[#1a2332] rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm overflow-hidden flex flex-col flex-1">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-[#2a3441] flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50 dark:bg-[#1e2837]">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-400">table_view</span>
                <h3 class="font-bold text-slate-700 dark:text-white">Daftar Rekomendasi</h3>
            </div>
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                     <span class="material-symbols-outlined text-slate-400 text-sm">search</span>
                </span>
                <input type="text" x-model="search" placeholder="Cari nama santri..." class="bg-white dark:bg-[#1a2332] border border-slate-300 dark:border-[#2a3441] text-slate-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary block w-full pl-10 p-2">
            </div>
        </div>
        <div class="overflow-auto flex-1 relative">
            <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-[#1a2332] dark:text-slate-400 font-bold sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-6 py-3 border-b border-slate-200">Nama Santri</th>
                        <th scope="col" class="px-6 py-3 text-center border-b border-slate-200">Rata-rata</th>
                        <th scope="col" class="px-6 py-3 text-center border-b border-slate-200">Mapel < KKM</th>
                        <th scope="col" class="px-6 py-3 text-center border-b border-slate-200">Sikap</th>
                        <th scope="col" class="px-6 py-3 text-center border-b border-slate-200">Kehadiran</th>
                        <th scope="col" class="px-6 py-3 text-center border-b border-slate-200">Rekomendasi</th>
                        <th scope="col" class="px-6 py-3 text-left border-b border-slate-200 w-64">Catatan</th>
                        <th scope="col" class="px-6 py-3 text-right border-b border-slate-200 w-48">Status Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#2a3441]">
                    @foreach($students as $st)
                    <tr x-show="matchesSearch('{{ strtolower($st->nama_siswa) }}')" class="bg-white dark:bg-[#1a2332] hover:bg-slate-50 dark:hover:bg-[#1e2837] transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-white whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                                    {{ $loop->iteration }}
                                </div>
                                <div>
                                    <div class="font-bold">{{ $st->nama_siswa }}</div>
                                    <div class="text-xs text-slate-400">NIS: {{ $st->nis }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center font-mono font-bold">{{ $st->average_score }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($st->failed_kkm_count > 0)
                                <span class="text-red-600 font-bold bg-red-50 px-2 py-0.5 rounded">{{ $st->failed_kkm_count }}</span>
                            @else
                                <span class="text-slate-300">0</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center font-bold {{ $st->attitude_grade == 'C' || $st->attitude_grade == 'D' ? 'text-red-500' : 'text-green-600' }}">
                            {{ $st->attitude_grade }}
                        </td>
                        <td class="px-6 py-4 text-center font-mono">
                            <span class="border-b-2 {{ $st->attendance_percent < 85 ? 'border-red-500 text-red-600' : 'border-emerald-500 text-emerald-600' }}">{{ $st->attendance_percent }}%</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($st->system_recommendation == 'promoted' || $st->system_recommendation == 'graduated')
                                <span class="inline-flex w-fit items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                    {{ $pageContext['success_badge'] }}
                                </span>
                            @elseif($st->system_recommendation == 'conditional')
                                <span class="inline-flex w-fit items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <span class="material-symbols-outlined text-[14px]">warning</span>
                                    Bersyarat
                                </span>
                            @else
                                <span class="inline-flex w-fit items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                    <span class="material-symbols-outlined text-[14px]">cancel</span>
                                    {{ $pageContext['fail_badge'] }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 leading-snug break-words">
                            {{ $st->notes }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex flex-col items-end gap-2">
                                <select 
                                    onchange="updateDecision({{ $st->id }}, this.value)" 
                                    class="bg-white border text-xs font-bold rounded-lg p-2 w-32 shadow-sm focus:ring-primary focus:border-primary
                                    {{ $st->final_decision == 'promoted' || $st->final_decision == 'graduated' ? 'border-emerald-500 text-emerald-700 bg-emerald-50' : 
                                       ($st->final_decision == 'retained' || $st->final_decision == 'not_graduated' ? 'border-red-500 text-red-700 bg-red-50 ml-auto' : 'border-slate-300') }}"
                                    {{ isset($isLocked) && $isLocked ? 'disabled' : '' }}
                                >
                                    @if($pageContext['type'] == 'graduation')
                                        <option value="graduated" {{ $st->final_decision == 'graduated' ? 'selected' : '' }}>LULUS</option>
                                        <option value="not_graduated" {{ $st->final_decision == 'not_graduated' ? 'selected' : '' }}>TIDAK LULUS</option>
                                        <option value="pending" {{ $st->final_decision == 'pending' ? 'selected' : '' }}>Ditangguhkan</option>
                                    @else
                                        <option value="promoted" {{ $st->final_decision == 'promoted' ? 'selected' : '' }}>Naik Kelas</option>
                                        <option value="conditional" {{ $st->final_decision == 'conditional' ? 'selected' : '' }}>Naik Bersyarat</option>
                                        <option value="retained" {{ $st->final_decision == 'retained' ? 'selected' : '' }}>Tinggal Kelas</option>
                                        <option value="pending" {{ $st->final_decision == 'pending' ? 'selected' : '' }}>Ditangguhkan</option>
                                    @endif
                                </select>
                            </div>
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
    <!-- LOGIC TRACE FOR DEBUGGING -->
    @if(isset($debugLog) && count($debugLog) > 0)
    <div class="mt-8 mb-4">
        <details class="group bg-slate-50 dark:bg-[#1e2837] border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
            <summary class="flex items-center justify-between p-4 cursor-pointer font-bold text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wider hover:bg-slate-100 dark:hover:bg-[#253041] transition-colors">
                <span>🛠️ System Logic Trace (Click to Expand)</span>
                <span class="material-symbols-outlined transform group-open:rotate-180 transition-transform text-slate-400">expand_more</span>
            </summary>
            <div class="p-4 border-t border-slate-200 dark:border-slate-700 font-mono text-xs text-slate-600 dark:text-slate-300 space-y-1">
                @foreach($debugLog as $log)
                    <div class="flex gap-2">
                        <span class="text-slate-400">>></span>
                        <span>{{ $log }}</span>
                    </div>
                @endforeach
            </div>
        </details>
    </div>
    @endif
</div>
@endsection
