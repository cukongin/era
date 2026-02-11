@extends('layouts.app')

@section('title', 'Validasi Import Siswa')

@section('content')
<div class="max-w-7xl mx-auto flex flex-col gap-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('master.students.index') }}" class="hover:text-primary transition-colors">Data Siswa</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="text-slate-900 dark:text-white font-medium">Validasi Import</span>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
                <span class="material-symbols-outlined">list</span>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-500 uppercase">Total Baris</span>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $totalRows }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
            <div>
                <span class="text-xs font-bold text-green-600 uppercase">Baris Valid</span>
                <p class="text-2xl font-black text-green-600">{{ $validRows }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                <span class="material-symbols-outlined">warning</span>
            </div>
            <div>
                <span class="text-xs font-bold text-red-600 uppercase">Baris Error</span>
                <p class="text-2xl font-black text-red-600">{{ count($importErrors) }}</p>
            </div>
        </div>
    </div>

    @if(count($importErrors) > 0)
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <span class="material-symbols-outlined text-yellow-500">info</span>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Perhatian</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Anda dapat mengedit data error langsung pada tabel di bawah ini, lalu klik <b>"Cek Ulang"</b>.</p>
                    <p>Atau klik <b>"Proses Data Valid Saja"</b> untuk melewati baris yang error.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
            <h3 class="font-bold text-lg">Preview Data</h3>

            <div class="flex gap-2">
                @if($validRows > 0)
                <form action="{{ route('master.students.import.process') }}" method="POST" onsubmit="return confirm('Yakin ingin memproses {{ $validRows }} data valid? Data error akan dilewati.');">
                    @csrf
                    <input type="hidden" name="import_key" value="{{ $importKey }}">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-slate-900 font-bold hover:bg-green-500 transition-colors shadow-sm text-sm">
                        Proses ({{ $validRows }} Data Valid)
                    </button>
                </form>
                @endif

                <button form="editForm" type="submit" class="px-4 py-2 rounded-lg bg-amber-600 text-white font-bold hover:bg-amber-700 transition-colors shadow-sm text-sm">
                    Cek Ulang
                </button>
            </div>
        </div>

        <form id="editForm" action="{{ route('master.students.import') }}" method="POST" class="overflow-x-auto">
            @csrf

            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="p-3 w-10">#</th>
                        <th class="p-3 min-w-[150px]">Nama Lengkap</th>
                        <th class="p-3 w-32">NIS</th>
                        <th class="p-3 w-32">NISN</th>
                        <th class="p-3 w-20">Jenjang</th>
                        <th class="p-3 w-20">Gender</th>
                        <th class="p-3 w-32">Tmp Lahir</th>
                        <th class="p-3 w-32">Tgl Lahir</th>
                         <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($allData as $idx => $row)
                    @php
                        $isError = !empty($row['_error']);
                        $bgClass = $isError ? 'bg-red-50 dark:bg-red-900/10' : '';
                        $borderClass = $isError ? 'border-red-300 focus:border-red-500 ring-red-200' : 'border-slate-200 focus:border-primary';
                    @endphp
                    <tr class="{{ $bgClass }} hover:bg-slate-50/80 transition-colors">
                        <td class="p-3 text-center">{{ $idx + 1 }}</td>

                        <!-- Inputs -->
                        <td class="p-2">
                            <input type="text" name="rows[{{ $idx }}][nama]" value="{{ $row['nama_lengkap'] }}"
                                class="w-full px-2 py-1 rounded text-xs border {{ $borderClass }} bg-white dark:bg-slate-800">
                        </td>
                        <td class="p-2">
                            <input type="text" name="rows[{{ $idx }}][nis]" value="{{ $row['nis_lokal'] }}"
                                class="w-full px-2 py-1 rounded text-xs border {{ $borderClass }} bg-white dark:bg-slate-800">
                        </td>
                        <td class="p-2">
                            <input type="text" name="rows[{{ $idx }}][nisn]" value="{{ $row['nisn'] }}"
                                class="w-full px-2 py-1 rounded text-xs border {{ $borderClass }} bg-white dark:bg-slate-800">
                        </td>
                        <td class="p-2">
                            <select name="rows[{{ $idx }}][jenjang]" class="w-full px-2 py-1 rounded text-xs border {{ $borderClass }} bg-white dark:bg-slate-800">
                                <option value="MI" {{ optional(\App\Models\Jenjang::find($row['id_jenjang']))->kode == 'MI' ? 'selected' : '' }}>MI</option>
                                <option value="MTS" {{ optional(\App\Models\Jenjang::find($row['id_jenjang']))->kode == 'MTS' ? 'selected' : '' }}>MTS</option>
                            </select>
                        </td>
                         <td class="p-2">
                            <select name="rows[{{ $idx }}][gender]" class="w-full px-2 py-1 rounded text-xs border {{ $borderClass }} bg-white dark:bg-slate-800">
                                <option value="L" {{ $row['jenis_kelamin'] == 'L' ? 'selected' : '' }}>L</option>
                                <option value="P" {{ $row['jenis_kelamin'] == 'P' ? 'selected' : '' }}>P</option>
                            </select>
                        </td>
                        <td class="p-2">
                            <input type="text" name="rows[{{ $idx }}][tempat_lahir]" value="{{ $row['tempat_lahir'] }}"
                                class="w-full px-2 py-1 rounded text-xs border {{ $borderClass }} bg-white dark:bg-slate-800">
                        </td>
                        <td class="p-2">
                            <input type="text" name="rows[{{ $idx }}][tanggal_lahir]" value="{{ $row['tanggal_lahir'] }}" placeholder="YYYY-MM-DD"
                                class="w-full px-2 py-1 rounded text-xs border {{ $borderClass }} bg-white dark:bg-slate-800">
                        </td>

                        <!-- Hidden Fields for others -->
                        <input type="hidden" name="rows[{{ $idx }}][nama_ayah]" value="{{ $row['nama_ayah'] }}">
                        <input type="hidden" name="rows[{{ $idx }}][nama_ibu]" value="{{ $row['nama_ibu'] }}">
                        <input type="hidden" name="rows[{{ $idx }}][pekerjaan_ayah]" value="{{ $row['pekerjaan_ayah'] }}">
                        <input type="hidden" name="rows[{{ $idx }}][pekerjaan_ibu]" value="{{ $row['pekerjaan_ibu'] }}">
                        <input type="hidden" name="rows[{{ $idx }}][alamat]" value="{{ $row['alamat'] }}">

                        <td class="p-2">
                            @if($isError)
                                <span class="text-red-600 font-bold flex items-center gap-1" title="{{ $row['_error'] }}">
                                    <span class="material-symbols-outlined text-[16px]">error</span>
                                    <span class="truncate max-w-[150px]">{{ $row['_error'] }}</span>
                                </span>
                            @else
                                <span class="text-green-600 font-bold flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">check_circle</span> OK
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </form>

        @if(count($allData) > 50)
        <div class="p-4 bg-slate-50 text-center text-xs text-slate-500">
            Hanya menampilkan 50 baris pertama untuk performa. Total data: {{ count($allData) }}
        </div>
        @endif
    </div>
</div>
@endsection

