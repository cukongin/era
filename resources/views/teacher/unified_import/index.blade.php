@extends('layouts.app')

@section('title', 'Import Leger (Unified)')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('walikelas.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="text-slate-900 dark:text-white font-medium">Import Leger</span>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex flex-col gap-4">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Import Leger Lengkap (One Click)</h1>

            <!-- Context Badge -->
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold border border-primary/20">
                    {{ $jenjangLabel }}
                </span>
                <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold border border-primary/20">
                    Periode: {{ $periodName }}
                </span>
            </div>

            <p class="text-slate-500 dark:text-slate-400">
                Fitur ini memungkinkan Anda mengimpor <b>Nilai, Absensi, dan Sikap</b> sekaligus dari satu file Excel (CSV).
                <br>Cocok untuk pengisian rapor massal (Leger).
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-4">
                <!-- Step 1: Download -->
                <div class="border border-primary/10 bg-primary/5 dark:bg-primary/10 dark:border-primary/20 rounded-xl p-6 flex flex-col gap-4">
                    <div class="flex items-center gap-3 text-primary dark:text-primary">
                        <span class="material-symbols-outlined text-3xl">download</span>
                        <h3 class="font-bold text-lg">Langkah 1: Download Template</h3>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Download template leger sesuai periode aktif. Template sudah berisi nama siswa dan kolom mapel yang sesuai.
                    </p>
                    <a href="{{ route('unified.import.template', $kelas->id) }}" class="mt-auto flex items-center justify-center gap-2 bg-primary text-white py-2.5 px-4 rounded-lg font-bold hover:bg-primary/90 transition-transform active:scale-95 shadow-lg shadow-primary/20">
                        Download Template Leger (.csv)
                    </a>
                </div>

                <!-- Step 2: Upload -->
                <div class="border border-primary/10 bg-primary/5 dark:bg-primary/10 dark:border-primary/20 rounded-xl p-6 flex flex-col gap-4">
                    <div class="flex items-center gap-3 text-primary dark:text-primary">
                        <span class="material-symbols-outlined text-3xl">upload_file</span>
                        <h3 class="font-bold text-lg">Langkah 2: Upload Leger</h3>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Upload file yang sudah diisi. Pastikan format <b>.CSV (Comma Delimited)</b>.
                    </p>

                    <form action="{{ route('unified.import.process', $kelas->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                        @csrf
                        <input type="file" name="file" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-colors">

                        <button type="submit" class="flex items-center justify-center gap-2 bg-primary text-white py-2.5 px-4 rounded-lg font-bold hover:bg-primary/90 transition-transform active:scale-95 shadow-lg shadow-primary/20">
                            <span class="material-symbols-outlined">cloud_upload</span>
                            Proses Import
                        </button>
                    </form>
                </div>
            </div>

            <!-- Notes -->
            <div class="mt-6 p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-400 text-sm border border-amber-200 dark:border-amber-800">
                <b>Catatan Penting:</b>
                <ul class="list-disc pl-5 mt-1 space-y-1">
                    <li>Jangan mengubah <b>Header Kolom</b> (Baris 1 & 2) pada file template.</li>
                    <li>Sistem akan mencocokkan Mapel berdasarkan ID yang ada di header (contoh: <code>[12]</code>).</li>
                    <li>Untuk Absensi, isi dengan angka (jumlah hari).</li>
                    <li>Untuk Sikap, saat ini sistem hanya menerima input manual di aplikasi (Kolom sikap di excel akan diabaikan sementara menunggu update struktur database). *Fokus Mapel & Absensi dulu*.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

