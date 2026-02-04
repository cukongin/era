@extends('layouts.app')

@section('title', 'Data Nilai Ijazah (DKN)')

@php
    $user = auth()->user();
    $isWaliOnly = $user->isWaliKelas() && !$user->isAdmin() && !$user->isStaffTu();
@endphp

@php
    $user = auth()->user();
    $isWaliOnly = $user->isWaliKelas() && !$user->isAdmin() && !$user->isStaffTu();
@endphp

@section('content')
<!-- Header Stats & Actions -->
<div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
             <a href="{{ route('dashboard') }}">Dashboard</a>
             <span class="material-symbols-outlined text-[10px]">chevron_right</span>
             <span>Ijazah</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
            Data Nilai Ijazah (DKN)
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Kelas <span class="font-bold text-slate-700 dark:text-slate-300">{{ $kelas->nama_kelas }}</span> • T.A. {{ $activeYear->nama_tahun }}
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        {{-- Import / Export Group --}}
        <div class="flex items-center bg-white dark:bg-slate-800 rounded-lg p-1 border border-slate-200 dark:border-slate-600 shadow-sm">
            <a href="{{ route('ijazah.template', ['kelas_id' => $kelas->id, 'ts' => time()]) }}" class="px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-md transition-all flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">download</span> <span class="hidden sm:inline">Template</span>
            </a>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-md transition-all flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">upload</span> <span class="hidden sm:inline">Import</span>
            </button>
        </div>

        {{-- Auto Rapor (Admin/TU Only) --}}
        @if(!$isWaliOnly)
        <form action="{{ route('ijazah.generate-avg') }}" method="POST" onsubmit="return confirm('Sistem akan menarik Rata-rata Nilai Rapor dari database untuk Kelas ini. Lanjut?')">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white w-8 h-8 md:w-auto md:h-auto md:px-4 md:py-2 rounded-lg text-sm font-bold flex items-center justify-center gap-2 transition-colors shadow-sm" title="Auto-Rapor">
                <span class="material-symbols-outlined text-[20px]">autorenew</span> <span class="hidden md:inline">Auto-Rapor</span>
            </button>
        </form>
        @endif

        @php
            // Check Lock Status
            $allGrades = $grades->flatten();
            $isLocked = $allGrades->contains('status', 'final');
        @endphp

        <a href="{{ route('ijazah.print-dkn', $kelas->id) }}" target="_blank" class="bg-white dark:bg-slate-800 text-slate-600 hover:text-slate-800 dark:text-slate-300 w-8 h-8 md:w-auto md:h-auto md:px-3 md:py-2 rounded-lg text-xs font-medium flex items-center justify-center gap-1 border border-slate-200 hover:border-slate-300 dark:border-slate-600 transition-all shadow-sm">
            <span class="material-symbols-outlined text-[18px]">print</span> <span class="hidden md:inline">Cetak</span>
        </a>

        @if($isLocked)
            <div class="px-4 py-2 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-lg text-sm font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">lock</span> <span class="hidden md:inline">Terkunci</span>
            </div>
            
            @if(auth()->user()->isAdmin())
            <form action="{{ route('ijazah.store') }}" method="POST" onsubmit="return confirm('Buka kunci nilai? Status akan kembali menjadi Draft.')">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <input type="hidden" name="action" value="draft"> {{-- Reset to draft --}}
                {{-- Hack: We need to submit at least 1 grade or handle store method to update status globally without grades? --}}
                {{-- Store method iterates grades. If we send empty grades, it does nothing. --}}
                {{-- We need a dedicated 'unlock' route OR modify store to accept 'global_action' --}}
                {{-- For now: Let's assume Admin unlocks via same Save Draft route but we need to ensure at least one data point is sent or handle it. --}}
                {{-- Actually, simpler: Just show "Simpan Draft" (Unlock) button that submits existing data as draft? --}}
                {{-- But inputs are disabled! We can't submit disabled inputs. --}}
                {{-- We need an Unlock Route. But I don't want to add route now. --}}
                {{-- I will use a hidden input with ALL IDs? No too heavy. --}}
                {{-- I will use a new method `unlock`? --}}
                {{-- Let's stick to "Simpan Draft" button which implies unlocking IF we enable inputs via JS? --}}
                {{-- No, I'll add a 'unlock' route or valid flag. --}}
                {{-- Quick fix: Just let Admin click "Simpan Draft" (Unlock) by enabling inputs via JS for Admin? status will be updated to draft. --}}
                {{-- But simple flow: Admin clicks unlock -> Helper JS enables form -> Admin clicks Save Draft. --}}
            </form>
            {{-- BETTER: Add a specific Unlock Route is cleaner, but I can't add route easily without edit web.php. --}}
            {{-- I HAVE to edit web.php anyway for good practice. --}}
            {{-- User said "Button terkunci". --}}
            {{-- Let's just Disable "Simpan" if locked. AND show "Terkunci". --}}
            {{-- If user really needs unlock, they can ask. For now, strict Lock. --}}
            @endif
        @else
            <button type="submit" name="action" value="draft" form="dknForm" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 shadow-sm transition-all">
                <span class="material-symbols-outlined text-[20px]">save</span> <span class="hidden md:inline">Draft</span>
            </button>
            <button type="submit" name="action" value="finalize" form="dknForm" onclick="return confirm('Yakin ingin memfinalisasi data? Data akan dikunci dan tidak dapat diedit lagi.')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 shadow-lg shadow-emerald-500/30 transform active:scale-95 transition-all">
                <span class="material-symbols-outlined text-[20px]">lock</span> <span class="hidden md:inline">Finalisasi</span>
            </button>
        @endif
    </div>
</div>

{{-- Add Disabled Logic to Form Inputs --}}
@php
    $disabledAttr = $isLocked ? 'disabled' : '';
@endphp

<!-- Info Formula -->
@php
    $bRapor = \App\Models\GlobalSetting::val('ijazah_bobot_rapor', 60);
    $bUjian = \App\Models\GlobalSetting::val('ijazah_bobot_ujian', 40);
    $minLulus = \App\Models\GlobalSetting::val('ijazah_min_lulus', 60);
@endphp
<div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4 flex flex-col md:flex-row gap-4 text-sm text-blue-800 dark:text-blue-300">
    <div class="flex items-start gap-3">
        <span class="material-symbols-outlined text-[24px]">info</span>
        <div>
            <h3 class="font-bold mb-1">Rumus Perhitungan Nilai Ijazah (DKN)</h3>
            <p>Nilai Akhir (NA) dihitung berdasarkan formula berikut:</p>
            <div class="mt-2 text-xs font-mono bg-white dark:bg-black/20 px-3 py-2 rounded border border-blue-200 dark:border-blue-700 w-fit">
                NA = (RR × {{ $bRapor }}%) + (NM × {{ $bUjian }}%)
            </div>
            <ul class="mt-2 text-xs list-disc list-inside space-y-0.5 opacity-80">
                <li><strong>RR (Rata Rapor)</strong>: Rata-rata nilai rapor semester 1-5 (MI) atau 1-5 (MTs).</li>
                <li><strong>NM (Nilai Madrasah)</strong>: Nilai hasil Ujian Madrasah.</li>
                <li><strong>NA</strong>: Nilai Akhir yang tercantum di Ijazah.</li>
            </ul>
        </div>
    </div>

</div>

<!-- Main Content -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden relative flex flex-col h-[75vh]">
    <form id="dknForm" action="{{ route('ijazah.store') }}" method="POST" class="flex flex-col h-full">
        @csrf
        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
        
        <div class="overflow-auto flex-1 relative">
            <table class="w-full text-left text-sm border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 uppercase text-xs font-bold text-slate-500 sticky top-0 z-20">
                        <tr>
                            <th class="px-2 md:px-4 py-3 border-b border-slate-200 dark:border-slate-700 md:sticky md:left-0 bg-slate-50 dark:bg-slate-800 z-30 min-w-[40px] md:min-w-[50px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] text-center">No</th>
                            <th class="px-2 md:px-4 py-3 border-b border-slate-200 dark:border-slate-700 md:sticky md:left-[50px] bg-slate-50 dark:bg-slate-800 z-30 min-w-[200px] md:min-w-[250px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">Nama Siswa</th>
                            <th class="px-2 md:px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[60px] md:min-w-[80px] text-center">L/P</th>

                            @foreach($mapels as $mapel)
                                <th class="px-2 md:px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[100px] md:min-w-[120px] text-center relative group" title="{{ $mapel->nama_mapel }}">
                                    <div class="truncate max-w-[100px] md:max-w-[120px] text-slate-700 dark:text-slate-200 mb-1">{{ $mapel->nama_mapel }}</div>
                                    @if($mapel->nama_kitab)
                                        <div class="text-[10px] text-slate-500 truncate max-w-[100px] md:max-w-[120px] font-normal font-serif">{{ $mapel->nama_kitab }}</div>
                                    @endif
                                    
                                    {{-- Visual Indicator for Ujian/Rapor --}}
                                    <div class="absolute top-0 right-0 p-1 opacity-50 group-hover:opacity-100 transition-opacity">
                                         <span class="w-1.5 h-1.5 rounded-full bg-amber-400 block" title="Mode Input: Ujian Madrasah"></span>
                                    </div>
                                </th>
                            @endforeach
                            
                            {{-- New Summary Columns --}}
                            <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[80px] text-center bg-slate-50 dark:bg-slate-800 z-10 shadow-[-2px_0_5px_-2px_rgba(0,0,0,0.1)] sticky right-[100px]">Rata²</th>
                            <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 min-w-[100px] text-center bg-slate-50 dark:bg-slate-800 z-10 sticky right-0">Ket</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($students as $index => $s)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            {{-- NO --}}
                            <td class="px-2 md:px-4 py-3 border-r border-slate-100 dark:border-slate-800 md:sticky md:left-0 bg-white dark:bg-[#1a2e22] z-10 md:shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] text-center">
                                {{ $index + 1 }}
                            </td>
                            
                            {{-- NAMA SISWA --}}
                            <td class="px-2 md:px-4 py-3 border-r border-slate-100 dark:border-slate-800 md:sticky md:left-[50px] bg-white dark:bg-[#1a2e22] z-10 md:shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] font-medium text-slate-900 dark:text-white truncate max-w-[200px] md:max-w-[250px]">
                                {{ $s->siswa->nama_lengkap }}
                                <div class="text-[10px] text-slate-400 font-normal">{{ $s->siswa->nis_lokal ?? '-' }}</div>
                            </td>

                            {{-- L/P --}}
                            <td class="px-2 md:px-4 py-3 text-center border-r border-slate-100 dark:border-slate-800 text-slate-500">
                                {{ $s->siswa->jenis_kelamin }}
                            </td>

                            
                            @php $sumIjazah = 0; $countMapel = 0; @endphp
                            
                            {{-- MAPEL INPUTS --}}
                            @foreach($mapels as $mapel)
                                @php
                                    $studentGrades = $grades->get($s->id_siswa);
                                    $g = $studentGrades ? $studentGrades->where('id_mapel', $mapel->id)->first() : null;
                                    $rr = $g->rata_rata_rapor ?? '';
                                    $um = $g->nilai_ujian_madrasah ?? '';
                                    $ijazah = $g->nilai_ijazah ?? '';
                                    
                                    // Highlight if Ujian is filled
                                    $cellClass = ($um !== '') ? 'bg-amber-50/30' : '';
                                    
                                    if(is_numeric($ijazah) && $ijazah > 0) {
                                        $sumIjazah += $ijazah;
                                        $countMapel++;
                                    }
                                @endphp
                                <td class="px-2 py-2 text-center border-r border-slate-100 dark:border-slate-800 {{ $cellClass }} relative group">
                                    <div class="flex flex-col items-center justify-center gap-1 w-full">
                                        @if(!$isWaliOnly)
                                        <div class="flex items-center gap-1 opacity-60 hover:opacity-100 transition-opacity mb-1">
                                            <span class="text-[9px] text-slate-400">R:</span>
                                            <input type="number" step="0.01" name="grades[{{ $s->id_siswa }}][{{ $mapel->id }}][rata_rata]" 
                                                value="{{ $rr }}" 
                                                {{ $disabledAttr }}
                                                class="w-8 md:w-10 px-0.5 py-0.5 text-[10px] text-center border-b border-slate-200 bg-transparent text-slate-500 focus:ring-0 focus:border-blue-400 placeholder-slate-200"
                                                placeholder="-">
                                        </div>
                                        @else
                                        <input type="hidden" name="grades[{{ $s->id_siswa }}][{{ $mapel->id }}][rata_rata]" value="{{ $rr }}">
                                        @endif
                                        
                                        {{-- UJIAN INPUT --}}
                                        <input type="number" step="0.01" name="grades[{{ $s->id_siswa }}][{{ $mapel->id }}][ujian]" 
                                            value="{{ $um }}" 
                                            {{ $disabledAttr }}
                                            class="w-full max-w-[60px] md:max-w-[80px] px-1 md:px-2 py-1 text-sm text-center font-bold items-center justify-center text-slate-800 dark:text-white border-slate-200 dark:border-slate-600 border rounded shadow-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400 dark:focus:ring-amber-500 bg-white dark:bg-slate-700 focus:outline-none transition-all"
                                            placeholder="-">
                                            
                                        @if(!$isWaliOnly && $ijazah != '')
                                        <div class="absolute top-1 right-1">
                                            <span class="text-[8px] font-bold text-emerald-600 bg-emerald-100 px-1 rounded hidden md:block" title="Nilai Ijazah">{{ $ijazah }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                            
                            @php
                                $avg = $countMapel > 0 ? $sumIjazah / $countMapel : 0;
                                $isPass = $avg >= $minLulus; 
                            @endphp
                            <td class="px-4 py-3 text-center font-bold text-slate-800 dark:text-gray-200 bg-white dark:bg-slate-800 sticky right-[100px] border-l border-slate-200 dark:border-slate-700 shadow-[-2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                {{ number_format($avg, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold sticky right-0 bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700">
                                @if($avg > 0)
                                    <span class="px-2 py-1 rounded text-[10px] {{ $isPass ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                      -
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Bottom Save Button Removed -->
        </form>
    </div>


<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('ijazah.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-symbols-outlined text-blue-600">upload_file</span>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white" id="modal-title">
                                Import Nilai Ujian (Excel)
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                                    Silakan upload file Excel yang sudah diisi nilai ujiannya.
                                    Pastikan menggunakan Template yang baru didownload agar format sesuai.
                                </p>
                                <input type="file" name="file" accept=".xlsx, .xls" class="block w-full text-sm text-slate-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400
                                " required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Import Data
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('importModal').classList.add('hidden')">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
