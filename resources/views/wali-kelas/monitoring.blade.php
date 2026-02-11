@extends('layouts.app')

@section('title', 'Monitoring Nilai')

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col gap-4">
        @if(auth()->user()->isAdmin() || auth()->user()->isTu())

        <div class="mb-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Filter (Admin Mode)</h3>
            <form action="{{ route('walikelas.monitoring') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center gap-3">

                <!-- Jenjang Selector -->
                <div class="relative group w-full md:w-auto">
                    <select name="jenjang" class="w-full appearance-none bg-none pl-9 pr-8 h-[46px] text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all md:min-w-[140px]" onchange="this.form.submit()">
                        @foreach(['MI', 'MTS'] as $j)
                            <option value="{{ $j }}" {{ (request('jenjang') == $j || (empty(request('jenjang')) && $loop->first)) ? 'selected' : '' }}>
                                {{ $j }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">school</span>
                    </div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>

                <!-- Class Selector -->
                <div class="relative group w-full md:w-auto md:min-w-[200px]">
                    <select name="kelas_id" class="w-full appearance-none bg-none pl-10 pr-8 h-[46px] text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all" onchange="this.form.submit()">
                        @php
                            $yId = $activeYear->id ?? $kelas->id_tahun_ajaran;
                            $q = \App\Models\Kelas::where('id_tahun_ajaran', $yId)->orderBy('nama_kelas');
                            if(request('jenjang')) {
                                $q->whereHas('jenjang', function($query) {
                                    $query->where('kode', request('jenjang'));
                                });
                            }
                            $allClassesInYear = $q->get();
                        @endphp

                        @if($allClassesInYear->count() == 0)
                            <option value="">Tidak ada kelas</option>
                        @endif

                        @foreach($allClassesInYear as $kls)
                            <option value="{{ $kls->id }}" {{ $kelas->id == $kls->id ? 'selected' : '' }}>
                                {{ $kls->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">class</span>
                    </div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>

                <!-- Period Selector -->
                <div class="relative group w-full md:w-auto">
                @if(isset($allPeriods) && $allPeriods->count() > 0)
                    <select name="periode_id" class="w-full appearance-none bg-none pl-9 pr-8 h-[46px] text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all md:min-w-[160px]" onchange="this.form.submit()">
                        @foreach($allPeriods as $prd)
                            <option value="{{ $prd->id }}" {{ $periode->id == $prd->id ? 'selected' : '' }}>
                                {{ $prd->nama_periode }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                    </div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                @else
                    <input type="hidden" name="periode_id" value="{{ $periode->id }}">
                @endif
                </div>
            </form>
        </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="{{ route('walikelas.dashboard') }}" class="hover:text-primary">Dashboard Wali Kelas</a>
                    <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                    <span>Monitoring Nilai</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                    Monitoring Nilai Kelas {{ $kelas->nama_kelas }} - {{ $kelas->jenjang->kode }}
                </h1>
                <div class="flex flex-col gap-1 text-sm text-slate-500">
                    <p>
                        Wali Kelas: <strong class="text-slate-800 dark:text-slate-300">{{ $kelas->wali_kelas->name ?? 'Belum ditentukan' }}</strong>
                    </p>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                            <span>Aman (> 86)</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span>Perlu Katrol (< 86)</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="flex items-center gap-3 w-full md:w-auto">
                @if(isset($allLocked) && $allLocked)
                    <div class="flex-1 md:flex-none flex items-center justify-center gap-1.5 px-3 py-2 bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-lg text-sm font-bold border border-green-200 dark:border-green-800 cursor-default">
                        <span class="material-symbols-outlined text-[18px]">lock</span>
                        <span>Terkunci</span>
                    </div>
                @else
                    <form action="{{ route('walikelas.monitoring.finalize') }}" method="POST"
                          data-confirm-delete="true"
                          data-title="Kunci Nilai (Final)?"
                          data-message="Nilai akan dikunci dan siap dicetak. Pastikan semua nilai sudah benar."
                          data-confirm-text="Ya, Kunci Nilai!"
                          data-confirm-color="#059669"
                          data-icon="question"
                          class="w-full md:w-auto">
                        @csrf
                        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                        <input type="hidden" name="periode_id" value="{{ $periode->id }}">
                        <button type="submit" class="w-full md:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-700 transition-all shadow-sm hover:shadow-md">
                            <span class="material-symbols-outlined text-[18px]">lock_open</span>
                            Kunci Nilai
                        </button>
                    </form>
                @endif
            </div>
        </div>
        </div>
    </div>

    <!-- MOBILE CARD VIEW -->
    <div class="grid grid-cols-1 gap-4 md:hidden">
        @forelse($monitoringData as $data)
            @php
                $isSafe = $data->status === 'aman';
                // Mobile Card Style
                $cardBorder = $isSafe ? 'border-slate-200 dark:border-slate-700' : 'border-amber-300 dark:border-amber-700/50 bg-amber-50/50 dark:bg-amber-900/10';
            @endphp
            <div class="bg-white dark:bg-surface-dark rounded-xl border {{ $cardBorder }} shadow-sm p-4 flex flex-col gap-4">
                <!-- Header: Mapel & Status -->
                <div class="flex justify-between items-start">
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-800 dark:text-white text-lg font-arabic">{{ $data->nama_mapel }}</span>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="h-5 w-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px] text-slate-500 font-bold">
                                {{ substr($data->nama_guru, 0, 1) }}
                            </div>
                            <span class="text-xs text-slate-500">{{ $data->nama_guru }}</span>
                        </div>
                    </div>
                    @if(!$isSafe)
                    <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wide">
                        Perlu Katrol
                    </span>
                    @endif
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex flex-col p-2 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-700/50 text-center">
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Rata-Rata</span>
                        <span class="text-lg font-bold text-slate-700 dark:text-slate-300">{{ $data->avg_score }}</span>
                    </div>
                    <div class="flex flex-col p-2 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-700/50 text-center">
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Terendah</span>
                        <span class="text-lg font-bold {{ !$isSafe ? 'text-red-500' : 'text-slate-700' }}">{{ $data->min_score }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-2 gap-2 mt-auto">
                    <a href="{{ route('teacher.input-nilai', ['kelas' => $kelas->id, 'mapel' => $data->id, 'periode_id' => $periode->id]) }}"
                       class="flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-bold rounded-lg bg-white border border-slate-300 text-slate-700 shadow-sm active:bg-slate-50">
                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                        Input
                    </a>
                    <a href="{{ route('walikelas.katrol.index', ['kelas_id' => $kelas->id, 'mapel_id' => $data->id]) }}"
                       class="flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-bold rounded-lg text-white shadow-sm transition-all
                       {{ $isSafe ? 'bg-slate-500' : 'bg-primary' }}">
                        <span class="material-symbols-outlined text-[18px]">{{ $isSafe ? 'visibility' : 'upgrade' }}</span>
                        {{ $isSafe ? 'Lihat' : 'Katrol' }}
                    </a>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-slate-400">
                Belum ada data mata pelajaran.
            </div>
        @endforelse
    </div>

    <!-- DESKTOP TABLE VIEW -->
    <div class="hidden md:block bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-xs font-semibold text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Mata Pelajaran</th>
                        <th class="px-6 py-4">Guru Pengampu</th>
                        <th class="px-6 py-4 text-center">Rata-Rata</th>
                        <th class="px-6 py-4 text-center">Terendah</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($monitoringData as $data)
                        @php
                            $isSafe = $data->status === 'aman';
                            $rowClass = $isSafe ? 'hover:bg-slate-50 dark:hover:bg-slate-800/50' : 'bg-amber-50 hover:bg-amber-100/50 dark:bg-amber-900/10 dark:hover:bg-amber-900/20';
                        @endphp
                        <tr class="{{ $rowClass }} transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                {{ $data->nama_mapel }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-slate-200 flex items-center justify-center text-xs text-slate-500 font-bold">
                                        {{ substr($data->nama_guru, 0, 1) }}
                                    </div>
                                    <span>{{ $data->nama_guru }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $data->avg_score }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold {{ !$isSafe ? 'text-red-500' : 'text-slate-700' }}">{{ $data->min_score }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($isSafe)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        Aman
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 animate-pulse">
                                        Perlu Katrol ({{ $data->below_count }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('teacher.input-nilai', ['kelas' => $kelas->id, 'mapel' => $data->id, 'periode_id' => $periode->id]) }}"
                                       class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg shadow-sm text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all"
                                       title="Input Nilai sebagai Wali Kelas">
                                        <span class="material-symbols-outlined text-[16px]">edit_note</span>
                                        <span>Input</span>
                                    </a>

                                    <a href="{{ route('walikelas.katrol.index', ['kelas_id' => $kelas->id, 'mapel_id' => $data->id]) }}"
                                       class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all
                                       {{ $isSafe ? 'bg-slate-500 hover:bg-slate-600 focus:ring-slate-500 opacity-70 hover:opacity-100' : 'bg-primary hover:bg-green-600 focus:ring-primary shadow-lg shadow-primary/30' }}">
                                        <span class="material-symbols-outlined text-[16px]">{{ $isSafe ? 'visibility' : 'upgrade' }}</span>
                                        <span>{{ $isSafe ? 'Lihat' : 'Katrol' }}</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                Belum ada data mata pelajaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

