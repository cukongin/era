@extends('layouts.app')

@section('title', 'Data Nilai Ijazah (DKN) - ' . $kelas->nama_kelas)

@php
    $user = auth()->user();
    $isWaliOnly = $user->isWaliKelas() && !$user->isAdmin() && !$user->isStaffTu();
    
    // Calculate passing percentage
    $passRate = $stats['total'] > 0 ? ($stats['pass'] / $stats['total']) * 100 : 0;
@endphp

@section('content')
<!-- Header Stats & Actions -->
<div class="flex flex-col gap-6 mb-8">
    
    <!-- Top Bar -->
    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                 <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                 <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                 <span>Ijazah</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white flex items-center gap-3 tracking-tight">
                <span class="bg-gradient-to-r from-primary to-primary-dark text-transparent bg-clip-text">Data Nilai Ijazah</span>
            </h1>
            <div class="flex items-center gap-3 mt-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light border border-primary/20 dark:border-primary/30">
                    Kelas {{ $kelas->nama_kelas }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                    T.A. {{ $activeYear->nama_tahun }}
                </span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
             {{-- Import / Export Group --}}
            <div class="flex items-center bg-white dark:bg-slate-800 rounded-xl p-1.5 border border-slate-200 dark:border-slate-700 shadow-sm">
                <a href="{{ route('ijazah.template', ['kelas_id' => $kelas->id, 'ts' => time()]) }}" class="px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">download</span> <span class="hidden sm:inline">Template</span>
                </a>
                <div class="w-px h-4 bg-slate-200 dark:bg-slate-700 mx-1"></div>
                <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary-light hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">upload</span> <span class="hidden sm:inline">Import</span>
                </button>
            </div>

            {{-- Auto Rapor --}}
            @if(!$isWaliOnly)
            <form action="{{ route('ijazah.generate-avg') }}" method="POST"
                  data-confirm-delete="true"
                  data-title="Tarik Auto-Rapor?"
                  data-message="Sistem akan menghitung ulang Nilai Rapor (RR) siswa dari database nilai semester."
                  data-confirm-text="Ya, Tarik Data!"
                  data-confirm-color="#4f46e5"
                  data-icon="info">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <button type="submit" class="bg-primary hover:bg-primary-dark active:bg-primary-dark text-white px-4 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 transition-all shadow-lg shadow-primary/20 hover:shadow-primary/40 transform hover:-translate-y-0.5" title="Auto-Rapor">
                    <span class="material-symbols-outlined text-[20px]">autorenew</span> <span class="hidden md:inline">Auto-Rapor</span>
                </button>
            </form>
            @endif

            @php
                // Check Lock Status
                $allGrades = $grades->flatten();
                $isLocked = $allGrades->contains('status', 'final');
            @endphp
            
            <a href="{{ route('ijazah.print-dkn', $kelas->id) }}" target="_blank" class="bg-white hover:bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 border border-slate-200 dark:border-slate-700 shadow-sm transition-all">
                <span class="material-symbols-outlined text-[20px]">print</span> <span class="hidden md:inline">Cetak DKN</span>
            </a>

            <a href="{{ route('ijazah.print-transcript', $kelas->id) }}" target="_blank" class="bg-white hover:bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 border border-slate-200 dark:border-slate-700 shadow-sm transition-all">
                <span class="material-symbols-outlined text-[20px]">workspace_premium</span> <span class="hidden md:inline">Cetak Transkip</span>
            </a>

            @if($isLocked)
                <div class="px-4 py-2.5 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl text-sm font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">lock</span> <span class="hidden md:inline">Terkunci</span>
                </div>
                
                @if(auth()->user()->isAdmin())
                <form action="{{ route('ijazah.store') }}" method="POST"
                      data-confirm-delete="true"
                      data-title="Buka Kunci Nilai?"
                      data-message="Status nilai akan kembali menjadi Draft dan bisa diedit kembali."
                      data-confirm-text="Ya, Buka Kunci!"
                      data-confirm-color="#dc2626"
                      data-icon="question">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                    <input type="hidden" name="action" value="unlock">
                    {{-- Admin Unlock Trigger --}}
                    <button type="submit" class="bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm transition-all ml-2" title="Buka Kunci (Admin)">
                        <span class="material-symbols-outlined text-[20px]">lock_open</span>
                        <span class="hidden md:inline">Buka Kunci</span>
                    </button>
                </form>
                @endif

            @else
                <button type="submit" name="action" value="draft" form="dknForm" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm transition-all">
                    <span class="material-symbols-outlined text-[20px]">save</span> <span class="hidden md:inline">Simpan Draft</span>
                </button>
                <button type="button" onclick="confirmFinalize()" class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 shadow-lg shadow-emerald-500/30 transform hover:-translate-y-0.5 transition-all">
                    <span class="material-symbols-outlined text-[20px]">lock</span> <span class="hidden md:inline">Finalisasi</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Average Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                 <span class="material-symbols-outlined text-6xl text-primary">analytics</span>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Rata-Rata Kelas</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($stats['average'], 2) }}</h3>
                <span class="text-xs font-bold text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full">NA</span>
            </div>
            <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5 dark:bg-slate-700">
                <div class="bg-primary h-1.5 rounded-full" style="width: {{ min(($stats['average'] / 100) * 100, 100) }}%"></div>
            </div>
        </div>

        <!-- Highest Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                 <span class="material-symbols-outlined text-6xl text-emerald-600">emoji_events</span>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nilai Tertinggi</p>
            <div class="flex flex-col">
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $stats['highest']['score'] }}</h3>
                <p class="text-xs font-medium text-slate-500 truncate" title="{{ $stats['highest']['student'] }}">
                    {{ $stats['highest']['student'] }}
                </p>
            </div>
        </div>

        <!-- Lowest Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                 <span class="material-symbols-outlined text-6xl text-rose-600">trending_down</span>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nilai Terendah</p>
            <div class="flex flex-col">
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $stats['lowest']['score'] }}</h3>
                <p class="text-xs font-medium text-slate-500 truncate" title="{{ $stats['lowest']['student'] }}">
                    {{ $stats['lowest']['student'] }}
                </p>
            </div>
        </div>

        <!-- Pass Rate Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                 <span class="material-symbols-outlined text-6xl text-purple-600">school</span>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kelulusan</p>
            <div class="flex items-center gap-3">
                <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ number_format($passRate, 0) }}%</h3>
                <div class="flex flex-col text-[10px] font-bold">
                    <span class="text-emerald-600">{{ $stats['pass'] }} Lulus</span>
                    <span class="text-rose-500">{{ $stats['fail'] }} Belum</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formula Info (Collapsible) -->
<div x-data="{ open: false }" class="mb-6">
    <button @click="open = !open" class="flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-blue-600 transition-colors uppercase tracking-wider mb-2">
        <span class="material-symbols-outlined text-[16px]">info</span> Informasi Rumus & Bobot
        <span class="material-symbols-outlined text-[16px] transform transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    <div x-show="open" x-collapse class="bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800 rounded-xl p-4 text-sm text-slate-600 dark:text-slate-400">
        @php
            $bRapor = \App\Models\GlobalSetting::val('ijazah_bobot_rapor', 60);
            $bUjian = \App\Models\GlobalSetting::val('ijazah_bobot_ujian', 40);
            $minLulus = \App\Models\GlobalSetting::val('ijazah_min_lulus', 60);
        @endphp
        <p class="mb-2"><strong>Rumus Nilai Akhir (NA):</strong></p>
        <code class="bg-white dark:bg-slate-800 px-2 py-1 rounded border border-blue-200 dark:border-blue-700 font-mono text-blue-700 dark:text-blue-300">
            NA = (Rata-Rata Rapor Ã— {{ $bRapor }}%) + (Nilai Ujian Ã— {{ $bUjian }}%)
        </code>
        <p class="mt-2 text-xs opacity-70">
            * Kriteria Kelulusan: Nilai Akhir minimal <strong>{{ number_format($minLulus, 2) }}</strong>.
        </p>
    </div>
</div>

@php
    $disabledAttr = $isLocked ? 'disabled' : '';
@endphp

<!-- Main Table Card -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col h-[70vh] relative">
    
    <form id="dknForm" action="{{ route('ijazah.store') }}" method="POST" class="flex flex-col h-full">
        @csrf
        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
        
        <div class="flex-1 overflow-auto custom-scrollbar relative">
            <!-- Restored w-full per user request ('separoh' fix), balanced columns -->
            <table class="w-full text-left text-sm border-collapse shadow-sm rounded-lg overflow-hidden">
                <thead class="bg-slate-900 text-white sticky top-0 z-40 shadow-lg border-b border-slate-700">
                    <tr>
                        <th class="p-3 text-center w-12 border-r border-slate-700 font-bold uppercase text-xs">No</th>
                        <th class="p-3 text-left w-[25%] min-w-[250px] border-r border-slate-700 font-bold uppercase text-xs">Peserta Didik</th>
                        <th class="p-3 text-center w-12 border-r border-slate-700 font-bold uppercase text-xs">L/P</th>
                        <th class="p-3 text-left w-auto border-r border-slate-700 font-bold uppercase text-xs">Mata Pelajaran</th>
                        <th class="p-3 text-center w-24 border-r border-slate-700 font-bold uppercase text-xs text-slate-400">RR</th>
                        <th class="p-3 text-center w-24 border-r border-slate-700 font-bold uppercase text-xs text-amber-400">UM</th>
                        <th class="p-3 text-center w-24 border-r border-slate-700 font-bold uppercase text-xs text-emerald-400">NA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($students as $index => $s)
                        @php 
                            $sumIjazah = 0; $countMapel = 0; 
                            $mapelCount = $mapels->count();
                            $rowSpan = $mapelCount + 2; // Mapels + Avg + Status
                        @endphp

                        @foreach($mapels as $mIndex => $mapel)
                            @php
                                $studentGrades = $grades->get($s->id_siswa);
                                $g = $studentGrades ? $studentGrades->where('id_mapel', $mapel->id)->first() : null;
                                $rr = $g->rata_rata_rapor ?? '';
                                $um = $g->nilai_ujian_madrasah ?? '';
                                $ijazah = $g->nilai_ijazah ?? '';
                                
                                if(is_numeric($ijazah) && $ijazah > 0) {
                                    $sumIjazah += $ijazah;
                                    $countMapel++;
                                }
                            @endphp

                            <tr class="group hover:bg-primary/5 transition-colors {{ $index % 2 == 0 ? 'bg-white' : 'bg-slate-50/50' }}">
                                @if($mIndex == 0)
                                    <!-- Sticky Identity Cols for First Row of Student -->
                                    <!-- Use align-top to keep name at top -->
                                    <td rowspan="{{ $rowSpan }}" class="p-3 text-center text-xs font-bold text-slate-500 border-r border-slate-200 bg-white align-top sticky left-0 z-30">
                                        {{ $index + 1 }}
                                    </td>
                                    <td rowspan="{{ $rowSpan }}" class="p-3 border-r border-slate-200 bg-white align-top sticky left-12 z-30 shadow-[4px_0_8px_rgba(0,0,0,0.05)] w-[25%] min-w-[250px]">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-sm text-slate-800 truncate max-w-[220px]" title="{{ $s->siswa->nama_lengkap }}">
                                                {{ $s->siswa->nama_lengkap }}
                                            </span>
                                            <span class="text-[10px] font-mono text-slate-400">{{ $s->siswa->nis_lokal ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td rowspan="{{ $rowSpan }}" class="p-3 text-center text-xs font-bold text-slate-400 border-r border-slate-200 bg-white align-top">
                                        {{ $s->siswa->jenis_kelamin }}
                                    </td>
                                @endif

                                <!-- Mapel Name -->
                                <td class="px-3 py-1.5 border-r border-slate-200 text-xs font-medium text-slate-700">
                                    {{ $mapel->nama_mapel }}
                                </td>

                                <!-- RR Display -->
                                <td class="px-1 py-1.5 text-center border-r border-slate-200 relative bg-slate-50/30">
                                    <span class="text-[11px] font-bold text-slate-600 block py-1">{{ $rr ?: '-' }}</span>
                                    <input type="hidden" name="grades[{{ $s->id_siswa }}][{{ $mapel->id }}][rata_rata]" value="{{ $rr }}">
                                </td>

                                <!-- UM Input -->
                                <td class="px-1 py-1.5 text-center border-r border-slate-200">
                                    <input type="number" step="1" name="grades[{{ $s->id_siswa }}][{{ $mapel->id }}][ujian]" 
                                        value="{{ $um !== '' ? number_format((float)$um, 0, '.', '') : '' }}" 
                                        {{ $disabledAttr }}
                                        class="w-16 text-center text-xs font-black text-black bg-white border border-slate-400 rounded shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 hover:border-amber-400 focus:outline-none py-1 mx-auto block default-input"
                                        placeholder="-">
                                </td>

                                <!-- NA Display -->
                                <td class="px-1 py-1.5 text-center border-r border-slate-200 bg-slate-50/30">
                                    <span class="text-[11px] font-bold text-emerald-600">
                                        {{ is_numeric($ijazah) ? number_format((float)$ijazah, 2) : '-' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach

                        @php
                            $avg = $countMapel > 0 ? $sumIjazah / $countMapel : 0;
                            $isPass = $avg >= $minLulus; 
                        @endphp
                        
                        <!-- Rata-Rata Row -->
                        <tr class="bg-primary/5 border-t border-slate-200">
                            <td class="px-3 py-2 border-r border-slate-200 text-xs font-bold text-primary text-right">
                                RATA-RATA TOTAL
                            </td>
                            <td colspan="3" class="px-3 py-2 text-center border-r border-slate-200">
                                <span class="text-sm font-black text-primary">{{ number_format($avg, 2) }}</span>
                            </td>
                        </tr>

                        <!-- Status Row -->
                        <tr class="bg-primary/5 border-b-4 border-slate-300">
                            <td class="px-3 py-2 border-r border-slate-200 text-xs font-bold text-slate-700 text-right">
                                STATUS KELULUSAN
                            </td>
                            <td colspan="3" class="px-3 py-2 text-center border-r border-slate-200">
                                @if($avg > 0)
                                    @if($isPass)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border-2 border-emerald-200">
                                            LULUS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border-2 border-rose-200">
                                            BELUM LULUS
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                        </tr>

                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
</div>

<!-- Import Modal (Preserved) -->
<div id="importModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-700">
            <form action="{{ route('ijazah.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <div class="bg-white dark:bg-slate-800 px-6 pt-6 pb-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary/10 sm:h-12 sm:w-12">
                            <span class="material-symbols-outlined text-primary text-2xl">upload_file</span>
                        </div>
                        <div class="mt-1 w-full">
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white" id="modal-title">
                                Import Nilai Ujian
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                                    Upload file Excel template yang sudah diisi. Pastikan format sesuai dengan Template yang didownload.
                                </p>
                                
                                <label class="block mb-2 text-sm font-medium text-slate-900 dark:text-white">Pilih File Excel</label>
                                <input type="file" name="file" accept=".xlsx, .xls" class="block w-full text-sm text-slate-500
                                    file:mr-4 file:py-2.5 file:px-4
                                    file:rounded-lg file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-primary/5 file:text-primary
                                    hover:file:bg-primary/10 dark:file:bg-primary/10 dark:file:text-primary
                                    border border-slate-200 rounded-lg cursor-pointer
                                " required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/50 px-6 py-4 flex flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-bold text-white hover:bg-primary-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Import Data
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-slate-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors" onclick="document.getElementById('importModal').classList.add('hidden')">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmFinalize() {
        Swal.fire({
            title: 'Finalisasi Nilai Ijazah?',
            text: "Data akan DIKUNCI dan tidak dapat diedit lagi. Pastikan semua nilai sudah benar!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#059669', // emerald-600
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Finalisasi!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl',
                cancelButton: 'rounded-xl'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.getElementById('dknForm');
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'action';
                input.value = 'finalize';
                form.appendChild(input);
                form.submit();
            }
        });
    }
</script>
@endpush

