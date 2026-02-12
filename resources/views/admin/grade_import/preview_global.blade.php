@extends('layouts.app')

@section('title', 'Preview Import Nilai Global')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Preview Import Global ({{ $jenjang }})</h1>
            <p class="text-slate-500">Periksa data sebelum disimpan. Pastikan kolom mapel dan nilai sudah benar.</p>
        </div>
        <a href="{{ route('grade.import.global.index') }}" class="text-slate-500 hover:text-slate-700 font-medium">
            &larr; Batal / Upload Ulang
        </a>
    </div>

    @if(!empty($importErrors))
    <div class="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200">
        <h3 class="font-bold mb-2">Terjadi Kesalahan / Warning:</h3>
        <ul class="list-disc list-inside text-sm">
            @foreach($importErrors as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <p class="mt-2 text-xs">Data dengan error mungkin tidak akan ditampilkan atau disimpan.</p>
    </div>
    @endif

    <form action="{{ route('grade.import.global.store') }}" method="POST">
        @csrf
        <input type="hidden" name="import_key" value="{{ $importKey }}">

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
                        <tr>
                            <th rowspan="2" class="px-4 py-3 border-b border-r min-w-[50px]">No</th>
                            <th rowspan="2" class="px-4 py-3 border-b border-r min-w-[100px]">Kelas</th>
                            <th rowspan="2" class="px-4 py-3 border-b border-r min-w-[200px] sticky left-0 bg-slate-50 dark:bg-slate-700 z-10">Nama Siswa</th>
                            
                            {{-- Group Headers by Mapel ID to avoid chaos? Or just flat list as per CSV? 
                                 Let's do flat list to match CSV columns exactly --}}
                            @foreach($mapelCols as $colIdx => $meta)
                                @php
                                    $pId = $meta['period_id'];
                                    $period = $allPeriods->firstWhere('id', $pId);
                                    $pName = $period ? $period->nama_periode : '?';
                                @endphp
                                
                                @if(isset($meta['is_non_academic']))
                                    @php
                                        $labels = [
                                            'sakit' => 'S', 'izin' => 'I', 'tanpa_keterangan' => 'A',
                                            'kelakuan' => 'Akhlak', 'kerajinan' => 'Rajin', 'kebersihan' => 'Bersih'
                                        ];
                                        $label = $labels[$meta['field']] ?? ucfirst($meta['field']);
                                        $bgColor = in_array($meta['field'], ['sakit','izin','tanpa_keterangan']) ? 'bg-yellow-50 text-yellow-700' : 'bg-green-50 text-green-700';
                                    @endphp
                                    <th class="px-2 py-2 border-b text-center min-w-[60px] {{ $bgColor }}">
                                        <div class="line-clamp-1 font-bold">
                                            {{ $label }}
                                        </div>
                                        <div class="text-[10px] opacity-70 font-normal normal-case mt-0.5">
                                            {{ $pName }}
                                        </div>
                                    </th>
                                @else
                                    @php
                                        $mId = $meta['mapel_id'];
                                        $typeLabel = $meta['type'] == 'harian' ? 'UH' : ($meta['type'] == 'uts' ? ($jenjang=='MI'?'UC':'PTS') : 'PAS');
                                    @endphp
                                    <th class="px-2 py-2 border-b text-center min-w-[100px]">
                                        <div class="line-clamp-1" title="{{ $mapelNames[$mId] ?? $mId }}">
                                            {{ $mapelNames[$mId] ?? $mId }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-normal normal-case mt-0.5">
                                            {{ $pName }} - {{ $typeLabel }}
                                        </div>
                                    </th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($parsedData as $index => $row)
                        <tr class="bg-white dark:bg-slate-800 hover:bg-slate-50">
                            <td class="px-4 py-2 border-r">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border-r font-medium whitespace-nowrap">
                                {{ $kelasNames[$row['kelas_id']] ?? $row['kelas_id'] }}
                            </td> 
                            <td class="px-4 py-2 border-r font-medium whitespace-nowrap sticky left-0 bg-white dark:bg-slate-800 z-10 box-border border-r-slate-200">
                                {{ $row['siswa']->nama_lengkap }}
                            </td>
                            
                            @foreach($mapelCols as $colIdx => $meta)
                                @php
                                    $pId = $meta['period_id'];
                                @endphp
                                
                                @if(isset($meta['is_non_academic']))
                                    @php
                                        $val = $row['non_academic'][$pId][$meta['field']] ?? '-';
                                    @endphp
                                    <td class="px-2 py-2 text-center border-r last:border-0 {{ $val === '-' ? 'bg-slate-50 text-slate-300' : 'bg-yellow-50/50' }}">
                                        {{ $val }}
                                    </td>
                                @else
                                    @php
                                        $mId = $meta['mapel_id'];
                                        $type = $meta['type'];
                                        $val = $row['grades'][$pId][$mId][$type] ?? '-';
                                    @endphp
                                    <td class="px-2 py-2 text-center border-r last:border-0 {{ $val === '-' ? 'bg-slate-50 text-slate-300' : '' }}">
                                        @if($val !== '-')
                                            <span class="font-mono bg-primary/10 text-primary px-1.5 py-0.5 rounded text-xs font-bold">
                                                {{ $val }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count($mapelCols) + 3 }}" class="px-4 py-8 text-center text-slate-500">Tidak ada data valid yang ditemukan.</td>
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
                    <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-lg font-bold hover:bg-primary-dark transition-colors shadow-lg shadow-primary/30 flex items-center">
                        <span class="material-symbols-outlined mr-2">save</span>
                        Simpan Semua Nilai
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

