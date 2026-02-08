@extends('layouts.app')

@section('title', 'Cetak Rapor')

@section('content')
<div class="flex flex-col h-[calc(100vh-80px)]">
    <!-- Header & Filters Stack -->
    <div class="mb-6 space-y-4">
        <!-- Header Title -->
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Cetak Rapor</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Pilih siswa untuk mencetak Rapor Capaian Kompetensi.</p>
        </div>
        
        <!-- Filters Toolbar (Left Aligned) -->
        <div class="flex flex-wrap items-center gap-3">
            
            <!-- Year Selector (Admin/TU) -->
            @if(isset($years) && count($years) > 0)
            <form action="{{ route('reports.index') }}" method="GET">
                <div class="relative group">
                    <select name="year_id" class="appearance-none pl-10 pr-8 py-2.5 text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer min-w-[180px] shadow-sm transition-all" onchange="this.form.submit()">
                        @foreach($years as $y)
                            <option value="{{ $y->id }}" {{ isset($selectedYear) && $selectedYear->id == $y->id ? 'selected' : '' }}>
                                {{ $y->nama }} {{ $y->status == 'aktif' ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                    </div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>
            </form>
            @endif

            <!-- Class Selector -->
            <form action="{{ route('reports.index') }}" method="GET" class="flex gap-2">
                @if(isset($selectedYear))
                <input type="hidden" name="year_id" value="{{ $selectedYear->id }}">
                @endif
                
                @if(count($classes) > 1)
                <div class="relative group">
                    <select name="class_id" class="appearance-none pl-10 pr-8 py-2.5 text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer min-w-[200px] shadow-sm transition-all" onchange="this.form.submit()">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ isset($selectedClass) && $selectedClass->id == $c->id ? 'selected' : '' }}>
                                {{ $c->nama_kelas }}
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
                @elseif(count($classes) == 1)
                     <div class="flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-700 bg-slate-100/50 border-2 border-slate-200 rounded-xl dark:bg-[#1a2332] dark:text-white dark:border-slate-700">
                        <span class="material-symbols-outlined text-slate-400">class</span>
                        {{ $classes->first()->nama_kelas }}
                     </div>
                @else
                    <div class="px-4 py-2 text-sm text-red-500 font-medium bg-red-50 border border-red-200 rounded-xl flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">error</span>
                        Tidak Ada Kelas
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Student List -->
    <div class="bg-white dark:bg-[#1a2332] rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm flex-1 overflow-hidden flex flex-col">
        @if($selectedClass)
            <div class="p-4 border-b border-slate-100 dark:border-[#2a3441] flex justify-between items-center bg-slate-50 dark:bg-[#1e2837]">
                <span class="font-semibold text-slate-700 dark:text-slate-300">Daftar Siswa ({{ $students->count() }})</span>
                
                @if($students->count() > 0)
                <a href="{{ route('reports.print.all', $selectedClass->id) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                    <span class="material-symbols-outlined text-[20px]">download</span>
                    Download Semua Rapor
                </a>
                @endif
            </div>
            
            <div class="overflow-auto flex-1">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-[#1e2837] dark:text-slate-400 border-b border-slate-100 dark:border-[#2a3441] sticky top-0">
                        <tr>
                            <th class="px-6 py-3 font-semibold">NIS / NISN</th>
                            <th class="px-6 py-3 font-semibold">Nama Lengkap</th>
                            <th class="px-6 py-3 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#2a3441]">
                        @forelse($students as $member)
                        <tr class="hover:bg-slate-50 dark:hover:bg-[#253041] transition-colors group">
                            <td class="px-6 py-3 font-medium text-slate-500">{{ $member->siswa->nis_lokal }} / {{ $member->siswa->nisn }}</td>
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ $member->siswa->nama_lengkap }}</td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex gap-1 justify-center">
                                    <a href="{{ route('reports.print.cover', ['student' => $member->siswa->id, 'year_id' => $selectedYear->id ?? null]) }}" target="_blank" class="px-2 py-1 text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 rounded hover:bg-slate-200" title="Cetak Cover">
                                        Cover
                                    </a>
                                    <a href="{{ route('reports.print.biodata', ['student' => $member->siswa->id, 'year_id' => $selectedYear->id ?? null]) }}" target="_blank" class="px-2 py-1 text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 rounded hover:bg-slate-200" title="Cetak Biodata">
                                        Biodata
                                    </a>
                                    <a href="{{ route('reports.print.transcript', ['student' => $member->siswa->id, 'year_id' => $selectedYear->id ?? null]) }}" target="_blank" class="px-2 py-1 text-xs font-bold text-purple-600 bg-purple-50 border border-purple-200 rounded hover:bg-purple-100" title="Cetak Transkip">
                                        Transkip
                                    </a>
                                    <a href="{{ route('reports.print', ['student' => $member->siswa->id, 'year_id' => $selectedYear->id ?? null]) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold text-white bg-emerald-600 rounded hover:bg-emerald-700 shadow-sm border border-emerald-700">
                                        <span class="material-symbols-outlined text-[16px]">print</span>
                                        Rapor
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-slate-500">
                                Tidak ada siswa di kelas ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center flex-1 p-8 text-center cursor-pointer">
                <div class="bg-slate-100 dark:bg-slate-800 p-4 rounded-full mb-4">
                    <span class="material-symbols-outlined text-4xl text-slate-400">print_disabled</span>
                </div>
                <h3 class="text-lg font-medium text-slate-900 dark:text-white">Belum Ada Kelas Dipilih</h3>
                <p class="text-slate-500 dark:text-slate-400 mt-1 max-w-sm">Siapa takut? Silakan pilih kelas di atas untuk mulai mencetak rapor.</p>
            </div>
        @endif
    </div>
</div>
@endsection
