@extends('layouts.app')

@section('title', 'Preview Import Absensi')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Preview Data Absensi</h1>
            <p class="text-sm text-slate-500">
                Silakan periksa data sebelum disimpan ke database.
            </p>
        </div>
    </div>

    @if(!empty($previewErrors))
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl">
            <h4 class="font-bold mb-2">Terjadi Kesalahan pada beberapa baris:</h4>
            <ul class="list-disc list-inside text-sm max-h-40 overflow-y-auto">
                @foreach($previewErrors as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.attendance.import.store') }}" method="POST">
        @csrf
        <input type="hidden" name="import_key" value="{{ $importKey }}">

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
                        <tr>
                            <th rowspan="2" class="px-4 py-3 border-b border-r min-w-[50px]">No</th>
                            <th rowspan="2" class="px-4 py-3 border-b border-r min-w-[100px]">Kelas</th>
                            <th rowspan="2" class="px-4 py-3 border-b border-r min-w-[200px] sticky left-0 bg-slate-50 dark:bg-slate-700 z-10 shadow-[1px_0_0_0_rgba(0,0,0,0.1)]">Nama Siswa</th>
                            
                            @foreach($periods as $p)
                                <th colspan="6" class="px-2 py-2 border-b border-r text-center bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary">
                                    {{ $p->nama_periode }}
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($periods as $p)
                                <!-- Absensi -->
                                <th class="px-2 py-2 border-b border-r min-w-[60px] text-center text-[10px] text-slate-500">Sakit</th>
                                <th class="px-2 py-2 border-b border-r min-w-[60px] text-center text-[10px] text-slate-500">Izin</th>
                                <th class="px-2 py-2 border-b border-r min-w-[60px] text-center text-[10px] text-slate-500">Alpa</th>
                                <!-- Sikap -->
                                <th class="px-2 py-2 border-b border-r min-w-[80px] text-center text-[10px] text-slate-500 bg-slate-100">Kelakuan</th>
                                <th class="px-2 py-2 border-b border-r min-w-[80px] text-center text-[10px] text-slate-500 bg-slate-100">Kerajinan</th>
                                <th class="px-2 py-2 border-b border-r min-w-[80px] text-center text-[10px] text-slate-500 bg-slate-100">Kebersihan</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($parsedData as $index => $row)
                        <tr class="bg-white dark:bg-slate-800 hover:bg-slate-50">
                            <td class="px-4 py-2 border-r">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border-r font-medium whitespace-nowrap">
                                {{ $row['nama_kelas'] ?? $row['kelas_id'] }}
                            </td>
                            <td class="px-4 py-2 border-r font-medium whitespace-nowrap sticky left-0 bg-white dark:bg-slate-800 z-10 shadow-[1px_0_0_0_rgba(0,0,0,0.05)] border-r-slate-200">
                                {{ $row['siswa']->nama_lengkap }}
                                <div class="text-[10px] text-slate-400 font-normal">NIS: {{ $row['siswa']->nis_lokal }}</div>
                            </td>

                            @foreach($periods as $p)
                                @php 
                                    $d = $row['data'][$p->id] ?? [];
                                    $s = $d['sakit'] ?? '-';
                                    $i = $d['izin'] ?? '-';
                                    $a = $d['tanpa_keterangan'] ?? '-';
                                    $k1 = $d['kelakuan'] ?? '-';
                                    $k2 = $d['kerajinan'] ?? '-';
                                    $k3 = $d['kebersihan'] ?? '-';
                                @endphp
                                <!-- Values -->
                                <td class="px-2 py-2 text-center border-r {{ $s==='-'?'text-slate-300':'' }}">{{ $s }}</td>
                                <td class="px-2 py-2 text-center border-r {{ $i==='-'?'text-slate-300':'' }}">{{ $i }}</td>
                                <td class="px-2 py-2 text-center border-r {{ $a==='-'?'text-slate-300':'' }}">{{ $a }}</td>
                                
                                <td class="px-2 py-2 text-center border-r bg-slate-50/50 {{ $k1==='-'?'text-slate-300':'' }}">{{ $k1 }}</td>
                                <td class="px-2 py-2 text-center border-r bg-slate-50/50 {{ $k2==='-'?'text-slate-300':'' }}">{{ $k2 }}</td>
                                <td class="px-2 py-2 text-center border-r bg-slate-50/50 {{ $k3==='-'?'text-slate-300':'' }}">{{ $k3 }}</td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 3 + (count($periods) * 6) }}" class="px-6 py-8 text-center text-slate-500">Tidak ada data valid yang ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500">
                        Total Siswa: <b>{{ count($parsedData) }}</b>
                    </div>
                    <div class="flex gap-4">
                        <a href="{{ route('grade.import.global.index') }}" class="px-6 py-2.5 rounded-lg font-bold text-slate-600 hover:bg-slate-200 transition-colors">Batal</a>
                        <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-lg font-bold hover:bg-primary-dark transition-colors shadow-lg shadow-primary/30 flex items-center">
                            <span class="material-symbols-outlined mr-2">save</span>
                            Simpan Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

