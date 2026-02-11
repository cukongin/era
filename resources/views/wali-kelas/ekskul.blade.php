@extends('layouts.app')

@section('title', 'Input Nilai Ekskul - ' . $kelas->nama_kelas)

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('walikelas.dashboard') }}" class="hover:text-primary">Dashboard Wali Kelas</a>
                <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                <span>Ekskul</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Input Nilai Ekstrakurikuler</h1>
            <p class="text-sm text-slate-500">
                Pilih kegiatan dan berikan nilai untuk siswa. Maksimal 2 kegiatan per siswa.
            </p>
        </div>
        <button type="submit" form="ekskulForm" class="bg-primary text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/30 hover:bg-green-600 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">save</span> Simpan Perubahan
        </button>
    </div>

    <!-- Form Table -->
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <form id="ekskulForm" action="{{ route('walikelas.ekskul.store') }}" method="POST">
            @csrf
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 uppercase text-xs font-bold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4 w-10">No</th>
                            <th class="px-6 py-4 min-w-[200px]">Nama Siswa</th>
                            <th class="px-6 py-4 text-center">Kegiatan 1</th>
                            <th class="px-6 py-4 text-center">Kegiatan 2</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($students as $index => $ak)
                        @php
                            $nilai = $ekskulRows[$ak->id_siswa] ?? collect([]);
                            $ekskul1 = $nilai->get(0);
                            $ekskul2 = $nilai->get(1);
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-6 py-4 text-slate-500 text-center align-top pt-6">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white align-top pt-6">
                                {{ $ak->siswa->nama_lengkap }}
                                <div class="text-xs text-slate-400 font-normal mt-0.5">{{ $ak->siswa->nis_lokal }}</div>
                            </td>
                            
                            <!-- Kegiatan 1 -->
                            <td class="px-4 py-4 bg-slate-50/30 align-top">
                                <div class="flex flex-col gap-2">
                                    <input type="text" list="ekskulList" name="ekskul[{{ $ak->id_siswa }}][0][nama_ekskul]" value="{{ optional($ekskul1)->nama_ekskul }}" placeholder="Nama Kegiatan (ex: Pramuka)" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                    
                                    <div class="grid grid-cols-2 gap-2">
                                        <select name="ekskul[{{ $ak->id_siswa }}][0][nilai]" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                            <option value="">Nilai</option>
                                            <option value="A" {{ optional($ekskul1)->nilai == 'A' ? 'selected' : '' }}>A (Sangat Baik)</option>
                                            <option value="B" {{ optional($ekskul1)->nilai == 'B' ? 'selected' : '' }}>B (Baik)</option>
                                            <option value="C" {{ optional($ekskul1)->nilai == 'C' ? 'selected' : '' }}>C (Cukup)</option>
                                        </select>
                                        <input type="text" name="ekskul[{{ $ak->id_siswa }}][0][keterangan]" value="{{ optional($ekskul1)->keterangan }}" placeholder="Keterangan..." class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                    </div>
                                </div>
                            </td>

                            <!-- Kegiatan 2 -->
                            <td class="px-4 py-4 bg-slate-50/30 align-top">
                                <div class="flex flex-col gap-2">
                                    <input type="text" list="ekskulList" name="ekskul[{{ $ak->id_siswa }}][1][nama_ekskul]" value="{{ optional($ekskul2)->nama_ekskul }}" placeholder="Nama Kegiatan (ex: Futsal)" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                    
                                    <div class="grid grid-cols-2 gap-2">
                                        <select name="ekskul[{{ $ak->id_siswa }}][1][nilai]" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                            <option value="">Nilai</option>
                                            <option value="A" {{ optional($ekskul2)->nilai == 'A' ? 'selected' : '' }}>A (Sangat Baik)</option>
                                            <option value="B" {{ optional($ekskul2)->nilai == 'B' ? 'selected' : '' }}>B (Baik)</option>
                                            <option value="C" {{ optional($ekskul2)->nilai == 'C' ? 'selected' : '' }}>C (Cukup)</option>
                                        </select>
                                        <input type="text" name="ekskul[{{ $ak->id_siswa }}][1][keterangan]" value="{{ optional($ekskul2)->keterangan }}" placeholder="Keterangan..." class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
</div>

<datalist id="ekskulList">
    @foreach($ekskulOptions as $opt)
        <option value="{{ $opt }}">
    @endforeach
</datalist>

@endsection

