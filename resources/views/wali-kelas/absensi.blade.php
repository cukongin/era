@extends('layouts.app')

@section('title', 'Input Absensi - ' . $kelas->nama_kelas)

@section('content')
@section('content')
<div class="flex flex-col gap-6">
    <!-- Check for Admin Filter -->
    @if(auth()->user()->isAdmin() || auth()->user()->isTu())
    <div class="mb-2">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Filter (Admin Mode)</h3>
        <form action="{{ url()->current() }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
            <!-- Jenjang Selector -->
            <div class="relative group w-full md:w-auto">
                <select name="jenjang" class="w-full appearance-none bg-none pl-9 pr-8 h-[46px] text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all md:min-w-[140px]" onchange="this.form.submit()">
                    @foreach(['MI', 'MTS'] as $j)
                        <option value="{{ $j }}" {{ (request('jenjang') == $j || (empty(request('jenjang')) && $loop->first)) ? 'selected' : '' }}>
                            {{ $j }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]">school</span>
                </div>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <span class="material-symbols-outlined text-[18px]">expand_more</span>
                </div>
            </div>
            
            <!-- Class Selector -->
            <div class="relative group w-full md:w-auto md:min-w-[200px]">
                <select name="kelas_id" class="w-full appearance-none bg-none pl-10 pr-8 h-[46px] text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all" onchange="this.form.submit()">
                    @if(isset($allClasses) && $allClasses->count() > 0)
                        @foreach($allClasses as $kls)
                            <option value="{{ $kls->id }}" {{ isset($kelas) && $kelas->id == $kls->id ? 'selected' : '' }}>
                                {{ $kls->nama_kelas }}
                            </option>
                        @endforeach
                    @else
                        <option value="">Tidak ada kelas</option>
                    @endif
                </select>
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 group-hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]">class</span>
                </div>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <span class="material-symbols-outlined text-[18px]">expand_more</span>
                </div>
            </div>

            <!-- Period Selector -->
            <div class="relative group w-full md:w-auto">
                <select name="periode_id" class="w-full appearance-none bg-none pl-9 pr-8 h-[46px] text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all md:min-w-[160px]" onchange="this.form.submit()">
                    @foreach($allPeriods as $p)
                        <option value="{{ $p->id }}" {{ $periode->id == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_periode }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                </div>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <span class="material-symbols-outlined text-[18px]">expand_more</span>
                </div>
            </div>
        </form>
    </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('walikelas.dashboard') }}" class="hover:text-primary">Dashboard Wali Kelas</a>
                <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                <span>Absensi</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 dark:text-white leading-tight">Input Ketidakhadiran & Kepribadian</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelas: <strong>{{ $kelas->nama_kelas }}</strong> • Periode: <strong>{{ $periode->nama_periode }}</strong>
            </p>
        </div>
        
        <!-- Actions Toolbar -->
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <!-- Template & Import -->
            <div class="flex items-center gap-2 mr-2 border-r border-slate-200 pr-4 hidden md:flex">
                <a href="{{ route('walikelas.absensi.template') }}" class="text-slate-600 hover:text-primary transition-colors hover:bg-slate-100 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">download</span> Template
                </a>
                
                <form action="{{ route('walikelas.absensi.import') }}" method="POST" enctype="multipart/form-data" class="inline-block relative">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                    <input type="file" name="file_absensi" id="file_absensi" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" onchange="this.form.submit()">
                    <button type="button" class="text-slate-600 hover:text-primary transition-colors hover:bg-slate-100 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 pointer-events-none">
                        <span class="material-symbols-outlined text-[18px]">upload</span> Import
                    </button>
                </form>
            </div>

            <!-- Mobile Only: Template & Import (Icon Only or Compact) -->
            <div class="flex md:hidden gap-2 w-full grid grid-cols-2 mb-2">
                 <a href="{{ route('walikelas.absensi.template') }}" class="flex items-center justify-center gap-2 bg-slate-100 text-slate-700 py-2.5 rounded-xl text-sm font-bold">
                    <span class="material-symbols-outlined text-[18px]">download</span> Template
                </a>
                <form action="{{ route('walikelas.absensi.import') }}" method="POST" enctype="multipart/form-data" class="relative">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                    <input type="file" name="file_absensi" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-20" onchange="this.form.submit()">
                    <button type="button" class="w-full flex items-center justify-center gap-2 bg-slate-100 text-slate-700 py-2.5 rounded-xl text-sm font-bold pointer-events-none">
                        <span class="material-symbols-outlined text-[18px]">upload</span> Import
                    </button>
                </form>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                 <button type="button" onclick="setNihil()" class="flex-1 md:flex-none bg-white text-slate-700 border border-slate-300 px-4 py-2.5 rounded-xl font-bold hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">restart_alt</span> <span class="md:hidden">Nihil</span> <span class="hidden md:inline">Set Nihil (0)</span>
                </button>
                <button type="submit" form="absensiForm" class="flex-1 md:flex-none bg-primary text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/30 hover:bg-green-600 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">save</span> Simpan <span class="hidden md:inline">Perubahan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Form Table -->
    <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <form id="absensiForm" action="{{ route('walikelas.absensi.store') }}" method="POST">
            @csrf
            <!-- Important: Pass Class ID for Admin context -->
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 uppercase text-xs font-bold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4 w-10">No</th>
                            <th class="px-6 py-4 min-w-[200px]">Nama Siswa</th>
                            <th class="px-6 py-4 text-center w-24 bg-blue-50/50 text-blue-700">Sakit</th>
                            <th class="px-6 py-4 text-center w-24 bg-purple-50/50 text-purple-700">Izin</th>
                            <th class="px-6 py-4 text-center w-24 bg-red-50/50 text-red-700">Alpa</th>
                            <th class="px-6 py-4 text-center w-24">Total</th>
                            <th class="px-6 py-4 text-center w-32 border-l">Kelakuan</th>
                            <th class="px-6 py-4 text-center w-32">Kerajinan</th>
                            <th class="px-6 py-4 text-center w-32">Kebersihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($students as $index => $ak)
                        @php
                            $absensi = $absensiRows[$ak->id_siswa] ?? null;
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-6 py-3 text-slate-500 text-center">{{ $index + 1 }}</td>
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">
                                {{ $ak->siswa->nama_lengkap }}
                                <div class="text-xs text-slate-400 font-normal mt-0.5">{{ $ak->siswa->nis_lokal }}</div>
                            </td>
                            
                            <!-- Inputs -->
                            <td class="px-4 py-2 bg-blue-50/30">
                                <input type="number" name="absensi[{{ $ak->id_siswa }}][sakit]" value="{{ $absensi->sakit ?? 0 }}" min="0" class="w-full text-center font-bold text-blue-700 rounded-lg border-slate-300 dark:bg-slate-800 dark:border-slate-700 focus:ring-blue-500 focus:border-blue-500 abs-input" data-target="total-{{ $ak->id }}">
                            </td>
                            <td class="px-4 py-2 bg-purple-50/30">
                                <input type="number" name="absensi[{{ $ak->id_siswa }}][izin]" value="{{ $absensi->izin ?? 0 }}" min="0" class="w-full text-center font-bold text-purple-700 rounded-lg border-slate-300 dark:bg-slate-800 dark:border-slate-700 focus:ring-purple-500 focus:border-purple-500 abs-input" data-target="total-{{ $ak->id }}">
                            </td>
                            <td class="px-4 py-2 bg-red-50/30">
                                <input type="number" name="absensi[{{ $ak->id_siswa }}][alpa]" value="{{ $absensi->tanpa_keterangan ?? 0 }}" min="0" class="w-full text-center font-bold text-red-700 rounded-lg border-slate-300 dark:bg-slate-800 dark:border-slate-700 focus:ring-red-500 focus:border-red-500 abs-input" data-target="total-{{ $ak->id }}">
                            </td>
                            
                            <!-- Total (Calculated) -->
                            <td class="px-6 py-3 text-center font-bold text-slate-800" id="total-{{ $ak->id }}">
                                {{ ($absensi->sakit ?? 0) + ($absensi->izin ?? 0) + ($absensi->tanpa_keterangan ?? 0) }}
                            </td>

                            <!-- Personality Inputs -->
                            <td class="px-4 py-2 border-l">
                                <select name="absensi[{{ $ak->id_siswa }}][kelakuan]" class="w-full text-sm border-slate-300 rounded-lg dark:bg-slate-800 dark:border-slate-700">
                                    <option value="Baik" {{ ($absensi->kelakuan ?? 'Baik') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="Cukup" {{ ($absensi->kelakuan ?? '') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                    <option value="Kurang" {{ ($absensi->kelakuan ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                </select>
                            </td>
                            <td class="px-4 py-2">
                                <select name="absensi[{{ $ak->id_siswa }}][kerajinan]" class="w-full text-sm border-slate-300 rounded-lg dark:bg-slate-800 dark:border-slate-700">
                                    <option value="Baik" {{ ($absensi->kerajinan ?? 'Baik') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="Cukup" {{ ($absensi->kerajinan ?? '') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                    <option value="Kurang" {{ ($absensi->kerajinan ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                </select>
                            </td>
                            <td class="px-4 py-2">
                                <select name="absensi[{{ $ak->id_siswa }}][kebersihan]" class="w-full text-sm border-slate-300 rounded-lg dark:bg-slate-800 dark:border-slate-700">
                                    <option value="Baik" {{ ($absensi->kebersihan ?? 'Baik') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="Cukup" {{ ($absensi->kebersihan ?? '') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                    <option value="Kurang" {{ ($absensi->kebersihan ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden flex flex-col gap-4 p-4">
                @foreach($students as $index => $ak)
                @php
                    $absensi = $absensiRows[$ak->id_siswa] ?? null;
                    $total = ($absensi->sakit ?? 0) + ($absensi->izin ?? 0) + ($absensi->tanpa_keterangan ?? 0);
                @endphp
                <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 flex flex-col gap-4">
                    <!-- Student Info Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-xs ring-1 ring-primary/20">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex flex-col">
                                <h4 class="font-bold text-slate-900 dark:text-white line-clamp-1">{{ $ak->siswa->nama_lengkap }}</h4>
                                <span class="text-[10px] text-slate-500 font-mono bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded w-fit">{{ $ak->siswa->nis_lokal }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                             <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total</span>
                             <span class="text-lg font-bold text-slate-800 dark:text-white" id="total-mob-{{ $ak->id }}">{{ $total }}</span>
                        </div>
                    </div>

                    <!-- Attendance Grid -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Ketidakhadiran</span>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-blue-600 uppercase text-center">Sakit</label>
                                <input type="number" name="absensi[{{ $ak->id_siswa }}][sakit]" value="{{ $absensi->sakit ?? 0 }}" min="0" class="w-full text-center font-bold text-blue-700 bg-white border border-blue-200 rounded-lg py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 abs-input-mobile" data-target="total-mob-{{ $ak->id }}">
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-purple-600 uppercase text-center">Izin</label>
                                <input type="number" name="absensi[{{ $ak->id_siswa }}][izin]" value="{{ $absensi->izin ?? 0 }}" min="0" class="w-full text-center font-bold text-purple-700 bg-white border border-purple-200 rounded-lg py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 abs-input-mobile" data-target="total-mob-{{ $ak->id }}">
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-red-600 uppercase text-center">Alpa</label>
                                <input type="number" name="absensi[{{ $ak->id_siswa }}][alpa]" value="{{ $absensi->tanpa_keterangan ?? 0 }}" min="0" class="w-full text-center font-bold text-red-700 bg-white border border-red-200 rounded-lg py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 abs-input-mobile" data-target="total-mob-{{ $ak->id }}">
                            </div>
                        </div>
                    </div>

                    <!-- Personality Stack -->
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Kepribadian</span>
                        <div class="flex flex-col gap-3">
                            <div class="grid grid-cols-12 items-center gap-3">
                                <span class="col-span-4 text-xs font-bold text-slate-600 dark:text-slate-300">Kelakuan</span>
                                <div class="col-span-8 relative">
                                    <select name="absensi[{{ $ak->id_siswa }}][kelakuan]" class="w-full text-sm font-medium rounded-lg bg-slate-50 border-slate-200 focus:border-primary focus:ring-primary py-1.5 pl-3 pr-8 appearance-none">
                                        <option value="Baik" {{ ($absensi->kelakuan ?? 'Baik') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="Cukup" {{ ($absensi->kelakuan ?? '') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                        <option value="Kurang" {{ ($absensi->kelakuan ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                        <span class="material-symbols-outlined text-[16px]">expand_more</span>
                                    </div>
                                </div>
                            </div>
                             <div class="grid grid-cols-12 items-center gap-3">
                                <span class="col-span-4 text-xs font-bold text-slate-600 dark:text-slate-300">Kerajinan</span>
                                <div class="col-span-8 relative">
                                    <select name="absensi[{{ $ak->id_siswa }}][kerajinan]" class="w-full text-sm font-medium rounded-lg bg-slate-50 border-slate-200 focus:border-primary focus:ring-primary py-1.5 pl-3 pr-8 appearance-none">
                                        <option value="Baik" {{ ($absensi->kerajinan ?? 'Baik') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="Cukup" {{ ($absensi->kerajinan ?? '') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                        <option value="Kurang" {{ ($absensi->kerajinan ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                        <span class="material-symbols-outlined text-[16px]">expand_more</span>
                                    </div>
                                </div>
                            </div>
                             <div class="grid grid-cols-12 items-center gap-3">
                                <span class="col-span-4 text-xs font-bold text-slate-600 dark:text-slate-300">Kebersihan</span>
                                <div class="col-span-8 relative">
                                    <select name="absensi[{{ $ak->id_siswa }}][kebersihan]" class="w-full text-sm font-medium rounded-lg bg-slate-50 border-slate-200 focus:border-primary focus:ring-primary py-1.5 pl-3 pr-8 appearance-none">
                                        <option value="Baik" {{ ($absensi->kebersihan ?? 'Baik') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="Cukup" {{ ($absensi->kebersihan ?? '') == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                        <option value="Kurang" {{ ($absensi->kebersihan ?? '') == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                        <span class="material-symbols-outlined text-[16px]">expand_more</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </form>
    </div>
</div>

<script>
    function setNihil() {
        if(!confirm('Yakin ingin mereset semua data absensi menjadi 0 (Nihil)?')) return;
        
        // Selector for both desktop and mobile inputs
        const selector = '.abs-input, .abs-input-mobile';
        
        document.querySelectorAll(selector).forEach(input => {
            input.value = 0;
            // Trigger recalculation
            input.dispatchEvent(new Event('input'));
        });
    }

    // Initialize calculation for all inputs
    function initCalculation() {
        const inputs = document.querySelectorAll('.abs-input, .abs-input-mobile');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                // Determine if this is mobile or desktop to find siblings correctly
                const isMobile = this.classList.contains('abs-input-mobile');
                const container = isMobile ? this.closest('.grid') : this.closest('tr');
                
                // Get all sibling inputs in the same row/container
                const siblingSelector = isMobile ? '.abs-input-mobile' : '.abs-input';
                const siblings = container.querySelectorAll(siblingSelector);
                
                let total = 0;
                siblings.forEach(inp => total += parseInt(inp.value) || 0);
                
                // Update target
                const targetId = this.dataset.target;
                const targetEl = document.getElementById(targetId);
                if(targetEl) {
                    targetEl.textContent = total;
                }
            });
        });
    }

    // Run init
    initCalculation();
</script>
@endsection
