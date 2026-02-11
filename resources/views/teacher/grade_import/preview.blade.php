@extends('layouts.app')

@section('title', 'Preview Import Nilai')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Validasi & Edit Data Import</h1>
        <a href="{{ route('grade.import.index', $kelas->id) }}" class="text-slate-500 hover:text-slate-700 text-sm font-semibold flex items-center gap-1">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Batal / Upload Ulang
        </a>
    </div>

    @if(!empty($importErrors))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center gap-2 text-red-700 font-bold mb-2">
            <span class="material-symbols-outlined">error</span> File Anda mengandung kesalahan:
        </div>
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($importErrors as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <div class="mt-4">
            <p class="text-xs text-slate-500">Baris dengan ID Siswa yang tidak valid tidak ditampilkan di tabel bawah. Anda bisa memperbaiki di Excel dan upload ulang, atau lanjutkan dengan data yang valid saja.</p>
        </div>
    </div>
    @endif

    <form action="{{ route('grade.import.store') }}" method="POST" id="importForm">
        @csrf
        <input type="hidden" name="import_key" value="{{ $importKey }}">

        <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white">Data Terbaca: {{ count($parsedData) }} Siswa</h3>

                    <p class="text-xs text-slate-500 mt-1">Silakan periksa dan edit nilai jika diperlukan sebelum menyimpan.</p>
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-xl font-bold hover:bg-green-600 transition-all shadow-lg shadow-primary/30 flex items-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    Simpan Semua Data
                </button>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto max-h-[70vh]">
                <table class="w-full text-left font-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-500 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-4 py-3 min-w-[50px] bg-slate-100" rowspan="2">No</th>
                            <th class="px-4 py-3 min-w-[200px] sticky left-0 z-20 bg-slate-100 border-r border-slate-200" rowspan="2">Nama Siswa</th>

                            @php
                                $structGrades = $structure['grades'] ?? [];
                                $structNonAcademic = $structure['non_academic'] ?? [];
                            @endphp

                            {{-- Mapel Headers --}}
                            @foreach($structGrades as $pId => $mapels)
                                @foreach($mapels as $mId => $meta)
                                    <th class="px-2 py-3 text-center border-l bg-slate-50 min-w-[150px]" colspan="{{ $jenjang == 'MTS' ? 3 : 2 }}">
                                        @php
                                            $pName = count($structGrades) > 1 ? "(P$pId) " : "";
                                        @endphp
                                        <div class="text-[10px] text-slate-400">{{ $pName }}</div>
                                        {{ $meta['nama_mapel'] }}
                                    </th>
                                @endforeach
                            @endforeach

                            {{-- Non-Academic Headers --}}
                            @foreach($structNonAcademic as $pId => $fields)
                                <th class="px-2 py-3 text-center border-l bg-blue-50/50 min-w-[200px]" colspan="{{ count($fields) }}">
                                     @php $pName = count($structNonAcademic) > 1 ? "(P$pId) " : ""; @endphp
                                     <div class="text-[10px] text-slate-400">{{ $pName }}</div>
                                     Kehadiran & Sikap
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            <!-- Sub Headers Mapel -->
                            @foreach($structGrades as $pId => $mapels)
                                @foreach($mapels as $mId => $meta)
                                    <th class="px-1 py-1 text-center bg-white text-[10px] border-l border-b">H</th>
                                    <th class="px-1 py-1 text-center bg-white text-[10px] border-b">{{ $jenjang == 'MI' ? 'Cawu' : 'PTS' }}</th>
                                    @if($jenjang == 'MTS')
                                    <th class="px-1 py-1 text-center bg-white text-[10px] border-b">PAS</th>
                                    @endif
                                @endforeach
                            @endforeach

                            <!-- Sub Headers Non-Academic -->
                            @foreach($structNonAcademic as $pId => $fields)
                                @foreach($fields as $field)
                                    @php
                                        $label = ucfirst($field);
                                        $widthClass = 'min-w-[100px]'; // Default for Personality

                                        if($field == 'sakit') { $label = 'S'; $widthClass = 'min-w-[50px]'; }
                                        elseif($field == 'izin') { $label = 'I'; $widthClass = 'min-w-[50px]'; }
                                        elseif($field == 'tanpa_keterangan') { $label = 'A'; $widthClass = 'min-w-[50px]'; }
                                        elseif($field == 'kelakuan') $label = 'Kelakuan';
                                        elseif($field == 'kerajinan') $label = 'Kerajinan';
                                        elseif($field == 'kebersihan') $label = 'Kebersihan';
                                    @endphp
                                    <th class="px-1 py-1 text-center bg-white text-[10px] border-l border-b {{ $widthClass }}" title="{{ ucfirst(str_replace('_', ' ', $field)) }}">{{ $label }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($parsedData as $idx => $row)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2 text-center text-slate-500">{{ $idx + 1 }}</td>
                                <td class="px-4 py-2 font-medium text-slate-900 sticky left-0 bg-white border-r border-slate-100 hover:bg-slate-50">
                                    {{ $row['siswa']->nama_lengkap }}
                                    <div class="text-[10px] text-slate-400">{{ $row['siswa']->nis_lokal }}</div>
                                </td>

                                {{-- Grades Inputs --}}
                                @foreach($structGrades as $pId => $mapels)
                                    @foreach($mapels as $mId => $meta)
                                        @php
                                            $g = $row['grades'][$pId][$mId] ?? ['harian'=>null, 'uts'=>null, 'uas'=>null];
                                        @endphp
                                        <!-- Harian -->
                                        <td class="p-1 border-l border-slate-100">
                                            <input type="number"
                                                   name="grades[{{ $row['siswa']->id }}][{{ $pId }}][{{ $mId }}][harian]"
                                                   value="{{ $g['harian'] }}"
                                                   class="w-full text-center text-xs border-0 bg-transparent focus:ring-1 focus:ring-primary rounded py-1 px-0 hover:bg-slate-100"
                                                   min="0" max="100">
                                        </td>
                                        <!-- UTS -->
                                        <td class="p-1">
                                            <input type="number"
                                                   name="grades[{{ $row['siswa']->id }}][{{ $pId }}][{{ $mId }}][uts]"
                                                   value="{{ $g['uts'] }}"
                                                   class="w-full text-center text-xs border-0 bg-transparent focus:ring-1 focus:ring-primary rounded py-1 px-0 hover:bg-slate-100"
                                                   min="0" max="100">
                                        </td>
                                        <!-- UAS -->
                                        @if($jenjang == 'MTS')
                                        <td class="p-1">
                                            <input type="number"
                                                   name="grades[{{ $row['siswa']->id }}][{{ $pId }}][{{ $mId }}][uas]"
                                                   value="{{ $g['uas'] }}"
                                                   class="w-full text-center text-xs border-0 bg-transparent focus:ring-1 focus:ring-primary rounded py-1 px-0 hover:bg-slate-100"
                                                   min="0" max="100">
                                        </td>
                                        @endif
                                    @endforeach
                                @endforeach

                                {{-- Non-Academic Inputs --}}
                                @foreach($structNonAcademic as $pId => $fields)
                                    @foreach($fields as $field)
                                        @php
                                            $val = $row['non_academic'][$pId][$field] ?? '';
                                            $displayVal = $val !== null ? (string)$val : '';
                                        @endphp
                                        <td class="p-1 border-l border-slate-100 bg-primary/5 relative group">
                                            <input type="text"
                                                   name="non_academic[{{ $row['siswa']->id }}][{{ $pId }}][{{ $field }}]"
                                                   value="{{ $displayVal }}"
                                                   class="w-full text-center text-xs border-0 bg-transparent focus:ring-1 focus:ring-primary rounded py-1 px-0 hover:bg-slate-100"
                                                   placeholder="-">
                                        </td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden flex flex-col gap-4 p-4">
                @foreach($parsedData as $idx => $row)
                <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm" x-data="{ expanded: false }">
                    <div class="p-4 flex items-center gap-3 cursor-pointer" @click="expanded = !expanded">
                         <div class="w-8 h-8 flex-shrink-0 bg-slate-100 text-slate-600 rounded-lg flex items-center justify-center font-bold text-xs">
                             {{ $idx + 1 }}
                         </div>
                         <div class="flex-1">
                            <h4 class="font-bold text-slate-900 dark:text-white line-clamp-1">{{ $row['siswa']->nama_lengkap }}</h4>
                            <div class="text-[10px] text-slate-400">{{ $row['siswa']->nis_lokal }}</div>
                         </div>
                         <button class="text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''">
                            <span class="material-symbols-outlined">expand_more</span>
                         </button>
                    </div>

                    <div x-show="expanded" x-collapse style="display: none;" class="border-t border-slate-100 dark:border-slate-700 p-4">
                        <div class="flex flex-col gap-4">
                            {{-- Grades Inputs --}}
                            @foreach($structGrades as $pId => $mapels)
                                @foreach($mapels as $mId => $meta)
                                    @php
                                        $g = $row['grades'][$pId][$mId] ?? ['harian'=>null, 'uts'=>null, 'uas'=>null];
                                    @endphp
                                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3">
                                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 border-b border-slate-200 pb-1 font-arabic">
                                            {{ $meta['nama_mapel'] }} <span class="text-slate-400 font-normal ml-1 font-sans">(P{{ $pId }})</span>
                                        </div>
                                        <div class="grid grid-cols-{{ $jenjang == 'MTS' ? '3' : '2' }} gap-2">
                                            <div class="flex flex-col gap-1">
                                                <label class="text-[9px] text-slate-400 uppercase">Harian</label>
                                                <input type="number" name="grades[{{ $row['siswa']->id }}][{{ $pId }}][{{ $mId }}][harian]" value="{{ $g['harian'] }}" class="w-full text-center text-sm border-slate-200 rounded focus:border-primary focus:ring-1 focus:ring-primary" min="0" max="100">
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <label class="text-[9px] text-slate-400 uppercase">{{ $jenjang == 'MI' ? 'Cawu' : 'PTS' }}</label>
                                                <input type="number" name="grades[{{ $row['siswa']->id }}][{{ $pId }}][{{ $mId }}][uts]" value="{{ $g['uts'] }}" class="w-full text-center text-sm border-slate-200 rounded focus:border-primary focus:ring-1 focus:ring-primary" min="0" max="100">
                                            </div>
                                            @if($jenjang == 'MTS')
                                            <div class="flex flex-col gap-1">
                                                <label class="text-[9px] text-slate-400 uppercase">PAS</label>
                                                <input type="number" name="grades[{{ $row['siswa']->id }}][{{ $pId }}][{{ $mId }}][uas]" value="{{ $g['uas'] }}" class="w-full text-center text-sm border-slate-200 rounded focus:border-primary focus:ring-1 focus:ring-primary" min="0" max="100">
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach

                            {{-- Non-Academic Inputs --}}
                            @if(count($structNonAcademic) > 0)
                            <div class="bg-primary/5 rounded-lg p-3 border border-primary/20">
                                <span class="text-xs font-bold text-primary block mb-2">Kehadiran & Sikap</span>
                                @foreach($structNonAcademic as $pId => $fields)
                                    <div class="grid grid-cols-2 gap-3">
                                    @foreach($fields as $field)
                                        @php
                                            $val = $row['non_academic'][$pId][$field] ?? '';
                                            $displayVal = $val !== null ? (string)$val : '';
                                        @endphp
                                        <div class="flex flex-col gap-1">
                                            <label class="text-[9px] text-slate-500 uppercase">{{ ucfirst(str_replace('_', ' ', $field)) }}</label>
                                            <input type="text" name="non_academic[{{ $row['siswa']->id }}][{{ $pId }}][{{ $field }}]" value="{{ $displayVal }}" class="w-full text-center text-sm border-slate-200 rounded focus:border-primary focus:ring-1 focus:ring-primary" placeholder="-">
                                        </div>
                                    @endforeach
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </form>
</div>
@endsection
