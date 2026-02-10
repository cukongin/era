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
    <div class="bg-white dark:bg-[#1a2332] rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm overflow-hidden flex flex-col flex-1 relative">
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
                        <th scope="col" class="p-4 w-4">
                            <div class="flex items-center">
                                <input id="checkbox-all" type="checkbox" @change="toggleAll($event)" class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                                <label for="checkbox-all" class="sr-only">checkbox</label>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 border-b border-slate-200">Nama Santri</th>
                        <th scope="col" class="px-6 py-3 text-center border-b border-slate-200">Mapel < KKM</th>
                        <th scope="col" class="px-6 py-3 text-center border-b border-slate-200">Rata-rata</th>
                        <th scope="col" class="px-6 py-3 text-center border-b border-slate-200">Sikap</th>
                        <th scope="col" class="px-6 py-3 text-center border-b border-slate-200">Kehadiran</th>
                        <th scope="col" class="px-6 py-3 text-center border-b border-slate-200">Rekomendasi</th>
                        <th scope="col" class="px-6 py-3 text-right border-b border-slate-200 w-64">Status Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#2a3441]">
                    @foreach($students as $st)
                    <tr x-show="matchesSearch('{{ strtolower($st->nama_siswa) }}')" class="bg-white dark:bg-[#1a2332] hover:bg-slate-50 dark:hover:bg-[#1e2837] transition-colors group">
                        <td class="w-4 p-4">
                            <div class="flex items-center">
                                <input type="checkbox" value="{{ $st->id }}" x-model="selectedIds" class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                            </div>
                        </td>
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
                         <td class="px-6 py-4 text-center">
                            @if($st->failed_kkm_count > 0)
                                <span class="text-red-600 font-bold bg-red-50 px-2 py-0.5 rounded">{{ $st->failed_kkm_count }}</span>
                            @else
                                <span class="text-slate-300">0</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center font-mono font-bold">{{ $st->average_score }}</td>
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
                        <td class="px-6 py-4 text-right">
                           <!-- SECURE LOCK UI -->
                           <div x-data="{ editing: false, currentStatus: '{{ $st->final_decision ?? $st->system_recommendation }}', loading: false }" class="flex justify-end relative">
                                <!-- DISPLAY MODE (LOCKED) -->
                                <div x-show="!editing" class="flex items-center gap-2">
                                    <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase border shadow-sm flex items-center gap-2"
                                         :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-200': currentStatus == 'promoted' || currentStatus == 'graduated',
                                            'bg-red-50 text-red-700 border-red-200': currentStatus == 'retained' || currentStatus == 'not_graduated',
                                            'bg-amber-50 text-amber-700 border-amber-200': currentStatus == 'conditional',
                                            'bg-slate-50 text-slate-600 border-slate-200': currentStatus == 'pending'
                                         }">
                                         <span class="material-symbols-outlined text-[14px]">lock</span>
                                         <span x-text="getStatusLabel(currentStatus)"></span>
                                    </span>
                                    
                                    @if(!isset($isLocked) || !$isLocked)
                                    <button @click="editing = true" class="text-slate-400 hover:text-blue-600 transition-colors p-1 rounded hover:bg-slate-100">
                                        <span class="material-symbols-outlined">edit</span>
                                    </button>
                                    @endif
                                </div>

                                <!-- EDIT MODE (UNLOCKED) -->
                                <div x-show="editing" @click.away="editing = false" class="absolute right-0 top-0 z-20 flex items-center gap-1 bg-white p-1 rounded-lg border shadow-lg">
                                    <select x-model="currentStatus" @change="loading = true; await updateDecision({{ $st->id }}, currentStatus); loading = false; editing = false;" 
                                            class="bg-white border text-xs font-bold rounded p-1 w-32 focus:ring-primary focus:border-primary">
                                        @if($pageContext['type'] == 'graduation')
                                            <option value="graduated">LULUS</option>
                                            <option value="not_graduated">TIDAK LULUS</option>
                                            <option value="pending">Ditangguhkan</option>
                                        @else
                                            <option value="promoted">Naik Kelas</option>
                                            <option value="conditional">Naik Bersyarat</option>
                                            <option value="retained">Tinggal Kelas</option>
                                            <option value="pending">Ditangguhkan</option>
                                        @endif
                                    </select>
                                    <button @click="editing = false" class="text-slate-400 hover:text-red-500">
                                        <span class="material-symbols-outlined">close</span>
                                    </button>
                                </div>
                                <div x-show="loading" class="absolute right-0 top-0 bg-white/80 p-1">
                                    <span class="material-symbols-outlined animate-spin text-primary text-sm">sync</span>
                                </div>
                           </div>
                        </td>
                    </tr>
                    @endforeach

                    @if(count($students) == 0)
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-500">
                            Belum ada data nilai untuk kelas ini.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- FLOATING BULK TOOLBAR -->
    <div x-show="selectedIds.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-20 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-20 opacity-0"
         class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50">
        <div class="bg-slate-900/90 backdrop-blur text-white rounded-full shadow-2xl px-6 py-3 flex items-center gap-6 border border-slate-700/50 ring-1 ring-white/10">
            <div class="flex items-center gap-3 border-r border-slate-700 pr-6">
                <span class="bg-primary text-white text-xs font-bold px-2 py-0.5 rounded-full" x-text="selectedIds.length"></span>
                <span class="font-bold text-sm">Terpilih</span>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400 font-bold mr-2 uppercase tracking-wider">Set Status:</span>
                
                @if($pageContext['type'] == 'graduation')
                    <button @click="bulkUpdate('graduated')" class="px-4 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-xs font-bold transition-colors shadow-lg shadow-emerald-900/20">
                        LULUS
                    </button>
                    <button @click="bulkUpdate('not_graduated')" class="px-4 py-1.5 rounded-full bg-red-600 hover:bg-red-500 text-xs font-bold transition-colors shadow-lg shadow-red-900/20">
                        TIDAK LULUS
                    </button>
                @else
                    <button @click="bulkUpdate('promoted')" class="px-4 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-xs font-bold transition-colors shadow-lg shadow-emerald-900/20">
                        NAIK KELAS
                    </button>
                    <button @click="bulkUpdate('retained')" class="px-4 py-1.5 rounded-full bg-red-600 hover:bg-red-500 text-xs font-bold transition-colors shadow-lg shadow-red-900/20">
                        TINGGAL KELAS
                    </button>
                @endif
            </div>
            
            <button @click="selectedIds = []" class="ml-2 text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
    </div>
</div>

<script>
    function promotionPage() {
        return {
            selectedIds: [],
            search: '',
            matchesSearch(name) {
                if(!this.search) return true;
                return name.includes(this.search.toLowerCase());
            },
            toggleAll(e) {
                if(e.target.checked) {
                    this.selectedIds = [
                        @foreach($students as $st)
                            {{ $st->id }},
                        @endforeach
                    ];
                } else {
                    this.selectedIds = [];
                }
            },
            getStatusLabel(status) {
                const labels = {
                    'promoted': 'NAIK KELAS',
                    'conditional': 'NAIK BERSYARAT',
                    'retained': 'TINGGAL KELAS',
                    'graduated': 'LULUS',
                    'not_graduated': 'TIDAK LULUS',
                    'pending': 'BELUM DITENTUKAN'
                };
                return labels[status] || status;
            },
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
                        const data = await res.json();
                        alert(data.message || 'Gagal menyimpan perubahan');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Error network');
                }
            },
            async bulkUpdate(status) {
                 if (!confirm(`Yakin set status ${status.toUpperCase().replace('_', ' ')} untuk ${this.selectedIds.length} santri terpilih?`)) return;

                 try {
                     const res = await fetch("{{ route('promotion.bulk_update') }}", {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         },
                         body: JSON.stringify({ decision_ids: this.selectedIds, status: status })
                     });
                     
                     if (res.ok) {
                         const data = await res.json();
                         alert(data.message);
                         window.location.reload();
                     } else {
                         alert('Gagal melakukan update massal');
                     }
                 } catch(e) {
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
