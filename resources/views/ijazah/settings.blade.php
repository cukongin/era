@extends('layouts.app')

@section('title', 'Setting Ujian Ijazah')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Setting Mapel Ujian Ijazah</h1>
        <p class="text-slate-500 dark:text-slate-400">Pilih mata pelajaran yang akan dimunculkan pada form input nilai ijazah (DKN).</p>
    </div>

    <form action="{{ route('settings.ijazah.update') }}" method="POST">
        @csrf
        
        <!-- WEIGHT CONFIG -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-amber-500">calculate</span>
                Rumus & Kriterian Kelulusan
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Bobot Rata-rata Rapor (%)</label>
                    <input type="number" name="bobot_rapor" value="{{ \App\Models\GlobalSetting::val('ijazah_bobot_rapor', 60) }}" class="w-full rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600 focus:ring-indigo-500" min="0" max="100" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Bobot Nilai Ujian (%)</label>
                    <input type="number" name="bobot_ujian" value="{{ \App\Models\GlobalSetting::val('ijazah_bobot_ujian', 40) }}" class="w-full rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600 focus:ring-indigo-500" min="0" max="100" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Minimal Kelulusan (NA)</label>
                    <input type="number" name="min_lulus" value="{{ \App\Models\GlobalSetting::val('ijazah_min_lulus', 60) }}" class="w-full rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600 focus:ring-indigo-500" min="0" max="100" step="0.01" required>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-2">* Total Bobot harus 100%.</p>
        </div>
        
        <!-- RANGE CONFIG -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-primary">history_edu</span>
                Sumber Nilai Rata-rata Rapor
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                Pilih <strong>Tingkat Kelas</strong> yang nilainya akan diambil untuk perhitungan Rata-rata Rapor. 
                <br><span class="text-xs text-slate-500">(Default: Kelas 4, 5, 6 untuk MI dan Kelas 7, 8, 9 untuk MTs).</span>
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- MI Range --}}
                <div>
                    <h3 class="font-bold text-sm text-slate-700 dark:text-slate-300 mb-2 border-b pb-1">Jenjang MI</h3>
                    <div class="flex flex-wrap gap-2">
                        @php 
                            $rangeMi = explode(',', \App\Models\GlobalSetting::val('ijazah_range_mi', '4,5,6')); 
                        @endphp
                        @foreach([1,2,3,4,5,6] as $lvl)
                            <label class="inline-flex items-center bg-slate-50 dark:bg-slate-700 rounded px-3 py-2 border border-slate-200 dark:border-slate-600 cursor-pointer hover:bg-slate-100 transition-colors">
                                <input type="checkbox" name="range_mi[]" value="{{ $lvl }}" class="rounded text-primary focus:ring-primary" {{ in_array($lvl, $rangeMi) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-200">Kelas {{ $lvl }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- MTs Range --}}
                <div>
                    <h3 class="font-bold text-sm text-slate-700 dark:text-slate-300 mb-2 border-b pb-1">Jenjang MTs</h3>
                    <div class="flex flex-wrap gap-2">
                        @php 
                            $rangeMts = explode(',', \App\Models\GlobalSetting::val('ijazah_range_mts', '7,8,9')); 
                        @endphp
                        @foreach([7,8,9] as $lvl)
                            <label class="inline-flex items-center bg-slate-50 dark:bg-slate-700 rounded px-3 py-2 border border-slate-200 dark:border-slate-600 cursor-pointer hover:bg-slate-100 transition-colors">
                                <input type="checkbox" name="range_mts[]" value="{{ $lvl }}" class="rounded text-primary focus:ring-primary" {{ in_array($lvl, $rangeMts) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-200">Kelas {{ $lvl }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- KONFIGURASI MI -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                    <h2 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary">school</span>
                        Jenjang MI
                    </h2>
                    <span class="text-xs font-bold px-2 py-1 bg-secondary/10 text-secondary rounded">Kelas 6</span>
                </div>
                <div class="p-6 max-h-[60vh] overflow-y-auto">
                    @foreach($mapels as $mapel)
                        <label class="flex items-center gap-3 p-2 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-lg cursor-pointer transition-colors">
                            <input type="checkbox" name="mapel_mi[]" value="{{ $mapel->id }}" 
                                class="rounded border-slate-300 text-secondary focus:ring-secondary w-5 h-5"
                                {{ in_array($mapel->id, $selectedMI) ? 'checked' : '' }}>
                            <div class="flex-1">
                                <div class="font-medium text-slate-700 dark:text-slate-200">{{ $mapel->nama_mapel }}</div>
                                <div class="text-xs text-slate-500">{{ $mapel->kategori }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- KONFIGURASI MTS -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                    <h2 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">domain</span>
                        Jenjang MTs
                    </h2>
                    <span class="text-xs font-bold px-2 py-1 bg-primary/10 text-primary rounded">Kelas 9</span>
                </div>
                <div class="p-6 max-h-[60vh] overflow-y-auto">
                    @foreach($mapels as $mapel)
                        <label class="flex items-center gap-3 p-2 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-lg cursor-pointer transition-colors">
                            <input type="checkbox" name="mapel_mts[]" value="{{ $mapel->id }}" 
                                class="rounded border-slate-300 text-primary focus:ring-primary w-5 h-5"
                                {{ in_array($mapel->id, $selectedMTS) ? 'checked' : '' }}>
                            <div class="flex-1">
                                <div class="font-medium text-slate-700 dark:text-slate-200">{{ $mapel->nama_mapel }}</div>
                                <div class="text-xs text-slate-500">{{ $mapel->kategori }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 shadow-lg shadow-primary/20 transform active:scale-95 transition-all">
                <span class="material-symbols-outlined">save</span>
                Simpan Konfigurasi
            </button>
        </div>
    </form>
</div>
@endsection

