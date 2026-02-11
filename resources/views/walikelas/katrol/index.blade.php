@extends('layouts.app')

@section('title', 'Katrol Nilai (Grade Adjustment)')

@section('content')
<div class="flex flex-col h-[calc(100vh-80px)]">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Katrol Nilai (Penggemukan)</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Sesuaikan nilai secara massal untuk mencapai ketuntasan atau pemerataan.</p>
        </div>
        
        <!-- Filter Tools -->
        <form action="{{ route('walikelas.katrol.index') }}" method="GET" class="flex gap-2">
            <select name="kelas_id" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg focus:outline-none dark:bg-[#1a2332] dark:text-slate-300 dark:border-slate-600" onchange="this.form.submit()">
                @foreach($classes as $cls)
                    <option value="{{ $cls->id }}" {{ $kelasId == $cls->id ? 'selected' : '' }}>{{ $cls->nama_kelas }}</option>
                @endforeach
            </select>
            <select name="mapel_id" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg focus:outline-none dark:bg-[#1a2332] dark:text-slate-300 dark:border-slate-600" onchange="this.form.submit()">
                @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}" {{ $mapelId == $subj->id ? 'selected' : '' }}>{{ $subj->nama_mapel }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Action Card -->
    <div class="bg-white dark:bg-[#1a2332] p-5 rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm mb-6">
        <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">engineering</span>
            Alat Katrol Massal
        </h3>
        
        <form action="{{ route('walikelas.katrol.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
            <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
            
            <!-- Method 1: Tuntas KKM -->
            <div class="border border-slate-200 dark:border-[#2a3441] rounded-lg p-4 hover:border-primary/50 transition-colors">
                <div class="flex items-center gap-3 mb-2">
                    <input type="radio" name="method_type" value="kkm" id="method_kkm" class="w-4 h-4 text-primary bg-slate-100 border-slate-300 focus:ring-primary dark:bg-slate-700 dark:border-slate-600" checked>
                    <label for="method_kkm" class="font-medium text-slate-900 dark:text-white">Tuntaskan ke KKM</label>
                </div>
                <p class="text-xs text-slate-500 ml-7 mb-3">Hanya menaikkan nilai di bawah KKM ({{ $currentKkm }}) menjadi tepat {{ $currentKkm }}. Nilai di atas KKM tidak berubah.</p>
                <div class="ml-7">
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-primary rounded hover:bg-blue-600 transition-colors">
                        Terapkan KKM
                    </button>
                </div>
            </div>

            <!-- Method 2: Tambah Poin (Fair Boost) -->
            <div class="border border-slate-200 dark:border-[#2a3441] rounded-lg p-4 hover:border-primary/50 transition-colors">
                <div class="flex items-center gap-3 mb-2">
                    <input type="radio" name="method_type" value="points" id="method_points" class="w-4 h-4 text-primary bg-slate-100 border-slate-300 focus:ring-primary dark:bg-slate-700 dark:border-slate-600">
                    <label for="method_points" class="font-medium text-slate-900 dark:text-white">Penggemukan (Tambah Poin)</label>
                </div>
                <p class="text-xs text-slate-500 ml-7 mb-3">Menambah poin ke SEMUA siswa secara merata namun dibatasi oleh Plafon Atas.</p>
                <div class="ml-7 flex flex-wrap gap-2 items-center">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Tambah:</span>
                        <input type="number" name="boost_points" value="5" min="1" max="100" class="w-16 px-2 py-1 text-xs border rounded dark:bg-slate-800 dark:border-slate-600">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Max (Plafon):</span>
                        <input type="number" name="max_ceiling" value="95" min="1" max="100" class="w-16 px-2 py-1 text-xs border rounded dark:bg-slate-800 dark:border-slate-600">
                    </div>
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 rounded hover:bg-emerald-700 transition-colors ml-auto">
                        Terapkan
                    </button>
                </div>
            </div>
        </form>
            
        <div class="mt-4 flex justify-end">
             <form action="{{ route('walikelas.katrol.store') }}" method="POST">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
                <input type="hidden" name="method_type" value="reset">
                <button type="submit" class="text-xs text-red-500 hover:text-red-600 underline" onclick="return confirm('Reset semua nilai ke nilai murni?')">Reset ke Nilai Murni</button>
             </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-[#1a2332] rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm overflow-hidden flex flex-col flex-1">
        <div class="overflow-auto flex-1">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-[#1e2837] dark:text-slate-400 border-b border-slate-100 dark:border-[#2a3441] sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Nama Siswa</th>
                        <th class="px-6 py-3 font-semibold text-center">Nilai Murni</th>
                        <th class="px-6 py-3 font-semibold text-center">Nilai Rapor</th>
                        <th class="px-6 py-3 font-semibold text-center">Selisih</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#2a3441]">
                    @foreach($grades as $g)
                    @php
                        $asli = $g->nilai_akhir_asli ?? $g->nilai_akhir;
                        $akhir = $g->nilai_akhir;
                        $selisih = $akhir - $asli;
                        $isKatrol = $g->is_katrol;
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#253041] transition-colors">
                        <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ $g->siswa->nama_lengkap }}</td>
                        <td class="px-6 py-3 text-center text-slate-500">{{ $asli }}</td>
                        <td class="px-6 py-3 text-center font-bold text-slate-900 dark:text-white">
                            {{ $akhir }}
                        </td>
                        <td class="px-6 py-3 text-center">
                            @if($selisih > 0)
                                <span class="text-emerald-600 font-bold">+{{ $selisih }}</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center">
                            @if($isKatrol)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400">
                                    Terkatrol
                                </span>
                            @endif
                             @if($akhir < $currentKkm)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 ml-1">
                                    <span class="material-symbols-outlined text-[10px] mr-1">warning</span> Remedial
                                </span>
                             @endif
                        </td>
                    </tr>
                    @endforeach
                    @if(count($grades) == 0)
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">Belum ada data nilai untuk mapel ini.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

