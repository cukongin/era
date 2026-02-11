@extends('layouts.app')

@section('title', 'Preview Import Global')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Preview Import {{ $jenjang }}</h1>
        <a href="{{ route('grade.import.global.index') }}" class="text-slate-500">Batal</a>
    </div>

    @if(!empty($errors))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        <strong class="font-bold">Ditemukan {{ count($errors) }} Masalah:</strong>
        <ul class="mt-2 list-disc list-inside text-sm max-h-40 overflow-y-auto">
            @foreach($errors as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
        <p class="mt-2 font-bold">Data yang error (baris tersebut) akan DILEWATI. Data valid tetap akan diproses.</p>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <p class="mb-4 text-slate-600">
            Total Data Valid: <strong>{{ count($parsedData) }} Siswa</strong>.
            <br>Klik "Proses Import Sekarang" untuk menyimpan data ke database.
        </p>

        <!-- Preview Table (Limit 10 rows) -->
        <div class="overflow-x-auto border rounded-lg mb-6">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Kelas</th>
                        <th class="px-4 py-2 text-left">Nama Siswa</th>
                        <th class="px-4 py-2 text-center">Jumlah Nilai Diupdate</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach(array_slice($parsedData, 0, 10) as $row)
                    @php
                        $totalGrades = 0;
                        foreach($row['grades'] as $periods) {
                            foreach($periods as $mapel) {
                                $totalGrades++; // Simplification
                            }
                        }
                    @endphp
                    <tr>
                        <td class="px-4 py-2">{{ $row['kelas_id'] }} (ID)</td>
                        <td class="px-4 py-2 font-medium">{{ $row['siswa']->nama_lengkap }}</td>
                        <td class="px-4 py-2 text-center">{{ $totalGrades }} Mapel/Periode</td>
                    </tr>
                    @endforeach
                    @if(count($parsedData) > 10)
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-center text-slate-500 italic">... dan {{ count($parsedData) - 10 }} siswa lainnya.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <form action="{{ route('grade.import.global.store') }}" method="POST">
            @csrf
            <input type="hidden" name="import_key" value="{{ $importKey }}">
            <button type="submit" class="w-full md:w-auto bg-primary text-white px-6 py-3 rounded-lg font-bold hover:bg-primary/90 transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">save</span>
                Proses Import Sekarang
            </button>
        </form>
    </div>
</div>
@endsection
