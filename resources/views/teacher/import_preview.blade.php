@extends('layouts.app')

@section('title', 'Preview Import Nilai')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary">Dashboard</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <a href="{{ route('teacher.input-nilai', ['kelas' => $assignment->id_kelas, 'mapel' => $assignment->id_mapel]) }}" class="hover:text-primary">Input Nilai</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="text-slate-900 dark:text-white font-medium">Preview Import</span>
    </div>

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Hasil Validasi Import</h1>
            <p class="text-slate-500">Periksa kembali data sebelum diproses masuk ke sistem.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('teacher.input-nilai', ['kelas' => $assignment->id_kelas, 'mapel' => $assignment->id_mapel]) }}" class="px-4 py-2 bg-white text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 font-bold text-sm">Batal</a>
            
            @if(count($validData) > 0)
            <form action="{{ route('teacher.input-nilai.process') }}" method="POST">
                @csrf
                <input type="hidden" name="import_key" value="{{ $importKey }}">
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-green-600 font-bold text-sm shadow-lg shadow-primary/30 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    Proses {{ count($validData) }} Data Valid
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-[#1a2e22] p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Data</span>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ count($validData) + count($importErrors) }} Baris</p>
        </div>
        <div class="bg-green-50 dark:bg-green-900/10 p-4 rounded-xl border border-green-100 dark:border-green-800 shadow-sm">
            <span class="text-green-600 text-xs font-bold uppercase tracking-wider">Valid</span>
            <p class="text-2xl font-bold text-green-700 mt-1">{{ count($validData) }}</p>
        </div>
        <div class="bg-red-50 dark:bg-red-900/10 p-4 rounded-xl border border-red-100 dark:border-red-800 shadow-sm">
            <span class="text-red-600 text-xs font-bold uppercase tracking-wider">Error</span>
            <p class="text-2xl font-bold text-red-700 mt-1">{{ count($importErrors) }}</p>
        </div>
    </div>

    @if(count($importErrors) > 0)
    <div class="bg-red-50 border border-red-100 rounded-xl p-4">
        <h3 class="font-bold text-red-800 flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined">warning</span> Data Error (Tidak akan diproses)
        </h3>
        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
            @foreach($importErrors as $err)
            <li>Baris {{ $err['row'] }}: {{ $err['message'] }}</li>
            @endforeach
        </ul>
        <p class="mt-3 text-xs text-red-600 italic">Silakan perbaiki data di file Excel dan upload ulang.</p>
    </div>
    @endif

    <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="border-b border-slate-200 dark:border-slate-800 px-6 py-4">
            <h3 class="font-bold text-slate-900 dark:text-white">Preview Data Valid</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 uppercase text-xs font-bold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3">NIS</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3 text-center">Harian</th>
                        <th class="px-6 py-3 text-center">
                            {{ $assignment->kelas->jenjang->kode == 'MI' ? 'Ujian Cawu' : 'PTS' }}
                        </th>
                        @if($assignment->kelas->jenjang->kode !== 'MI')
                        <th class="px-6 py-3 text-center">EHB</th>
                        @endif
                        <th class="px-6 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($validData as $row)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                        <td class="px-6 py-3 font-mono text-slate-600">{{ $row['nis'] }}</td>
                        <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ $row['nama'] }}</td>
                        <td class="px-6 py-3 text-center">{{ $row['harian'] }}</td>
                        <td class="px-6 py-3 text-center">{{ $row['uts'] }}</td>
                        @if($assignment->kelas->jenjang->kode !== 'MI')
                        <td class="px-6 py-3 text-center">{{ $row['uas'] }}</td>
                        @endif
                        <td class="px-6 py-3 text-slate-500 italic">{{ $row['catatan'] ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">Tidak ada data valid untuk ditampilkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
