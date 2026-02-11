@extends('layouts.app')

@section('title', 'Import Nilai Kolektif')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('walikelas.dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                <span>Import Kolektif</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Import Nilai Kolektif Kelas {{ $kelas->nama_kelas }}</h1>
            <p class="text-sm text-slate-500">
                Isi nilai untuk {{ $mapelCount }} mata pelajaran sekaligus dalam satu file Excel.
            </p>
        </div>
        <a href="{{ route('walikelas.dashboard') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 text-sm font-semibold transition-colors">
            Kembali
        </a>
    </div>

    <!-- Step 1: Template -->
    <div class="bg-primary/5 border border-primary/20 rounded-xl p-6 flex flex-col md:flex-row items-center gap-6">
        <div class="h-16 w-16 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-3xl text-primary">download</span>
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Langkah 1: Download Template Master</h3>
            <p class="text-slate-600 text-sm mb-4">
                Download template khusus kelas ini. Template berisi kolom untuk semua mapel yang diajarkan di kelas {{ $kelas->nama_kelas }}.
                <br><span class="text-red-500 font-bold">PENTING: Jangan mengubah ID di header kolom (misal: [123]).</span>
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('grade.import.template', $kelas->id) }}" class="inline-flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-lg font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined">download</span>
                    Download Template (Periode Aktif)
                </a>
                <a href="{{ route('grade.import.template', ['kelas' => $kelas->id, 'type' => 'tahunan']) }}" class="inline-flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-lg font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined">calendar_month</span>
                    Download Template Tahunan (3 Periode)
                </a>
            </div>
        </div>
    </div>

    <!-- Step 2: Upload -->
    <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 text-sm font-bold">2</span>
            Upload File & Validasi
        </h3>

        <form action="{{ route('grade.import.preview', $kelas->id) }}" method="POST" enctype="multipart/form-data" class="max-w-xl">
            @csrf
            <div class="space-y-4">
                <div>
                   <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Pilih File (.csv, .xlsx)</label>
                   <input type="file" name="file" required class="block w-full text-sm text-slate-500
                      file:mr-4 file:py-2.5 file:px-4
                      file:rounded-full file:border-0
                      file:text-sm file:font-bold
                      file:bg-primary/10 file:text-primary
                      hover:file:bg-primary/20
                      transition-all
                    "/>
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-xl font-bold hover:bg-green-600 transition-all shadow-lg shadow-primary/30 flex items-center gap-2">
                        <span class="material-symbols-outlined">table_view</span>
                        Preview & Edit Data
                    </button>
                    <p class="text-xs text-slate-500 mt-2">
                        Anda akan masuk ke halaman preview untuk mengecek dan mengedit data sebelum disimpan permanen.
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
