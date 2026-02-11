@extends('layouts.app')

@section('title', 'Diagnosa Import')

@section('content')
<div class="bg-white dark:bg-[#1a2e22] rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="flex items-center gap-3 mb-6">
        <div class="p-3 bg-red-100 rounded-full text-red-600">
            <span class="material-symbols-outlined text-2xl">bug_report</span>
        </div>
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Diagnosa Gagal Import</h2>
            <p class="text-slate-500 text-sm">Sistem gagal membaca data siswa. Berikut adalah hasil analisa file Anda.</p>
        </div>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
            <div class="text-xs text-slate-500 uppercase font-bold">Jenis File Terdeteksi</div>
            <div class="font-bold text-lg text-slate-900 dark:text-white">{{ $fileType }}</div>
        </div>
        <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
            <div class="text-xs text-slate-500 uppercase font-bold">Header Kolom</div>
            <div class="font-bold text-lg {{ $headerFound ? 'text-green-600' : 'text-red-500' }}">
                {{ $headerFound ? 'DITEMUKAN' : 'TIDAK DITEMUKAN' }}
            </div>
            @if($headerFound)
            <div class="text-xs text-green-600">Baris ke-{{ $headerRowIndex + 1 }}</div>
            @endif
        </div>
        <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
            <div class="text-xs text-slate-500 uppercase font-bold">Total Baris Dibaca</div>
            <div class="font-bold text-lg text-slate-900 dark:text-white">{{ $totalRows }}</div>
        </div>
    </div>

    <!-- Mapping Info -->
    <div class="mb-6">
        <h3 class="font-bold text-slate-900 dark:text-white mb-2">Mapping Kolom (Otomatis)</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($columnMapping as $col => $idx)
            <span class="px-2 py-1 rounded bg-primary/10 text-primary text-sm border border-primary/20">
                <b>{{ $col }}:</b> Index {{ $idx }}
            </span>
            @endforeach
        </div>
    </div>

    <!-- Data Preview -->
    <h3 class="font-bold text-slate-900 dark:text-white mb-2">Sample 5 Baris Data Pertama:</h3>
    <div class="overflow-x-auto border rounded-lg border-slate-200 dark:border-slate-700 mb-6">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-100 dark:bg-slate-800 font-bold">
                <tr>
                    <th class="p-2 border">Row #</th>
                    <th class="p-2 border">Raw Content (Cols Extracted)</th>
                    <th class="p-2 border">Status Validasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sampleRows as $row)
                <tr class="border-t dark:border-slate-800">
                    <td class="p-2 border-r dark:border-slate-800 font-mono">{{ $row['index'] }}</td>
                    <td class="p-2 border-r dark:border-slate-800">
                        <div class="flex flex-wrap gap-1">
                            @foreach($row['cols'] as $k => $v)
                            <span class="px-1.5 py-0.5 bg-slate-100 rounded text-xs border text-slate-600" title="Index {{ $k }}">
                                [{{ $k }}] {{ Str::limit($v, 20) }}
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="p-2 text-xs">
                        @if($row['status'] == 'OK')
                            <span class="text-green-600 font-bold">OK - Valid</span>
                        @else
                            <span class="text-red-500 font-bold">{{ $row['status'] }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(isset($validationErrors) && count($validationErrors) > 0)
    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
        <h3 class="font-bold text-red-700 dark:text-red-400 mb-2">Detail Error Validasi:</h3>
        <ul class="list-disc pl-5 text-sm text-red-600 dark:text-red-300 space-y-1">
            @foreach(array_slice($validationErrors, 0, 10) as $err)
                <li>
                    <b>Baris {{ $err['row'] ?? '?' }}:</b> {{ $err['message'] ?? 'Error tidak diketahui' }}
                </li>
            @endforeach
            @if(count($validationErrors) > 10)
                <li class="italic font-bold">... dan {{ count($validationErrors) - 10 }} error lainnya.</li>
            @endif
        </ul>
    </div>
    @endif

    <div class="flex justify-end gap-3">
        <a href="{{ url()->previous() }}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 font-bold">Kembali</a>
    </div>
</div>
@endsection
