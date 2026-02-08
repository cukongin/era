@extends('layouts.app')

@section('title', 'Konfigurasi Sistem')

@section('content')
<div class="flex flex-col gap-6" x-data="settingsPage">
    
    <!-- Header & Year Management -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Konfigurasi Sistem</h1>
            <p class="text-slate-500 text-sm">Pusat pengaturan Tahun Ajaran, Penilaian, dan Rapor.</p>
        </div>
        <div class="flex items-center gap-3">
             @if($activeYear)
            <div class="bg-green-100/50 text-green-700 px-3 py-1.5 rounded-lg text-sm font-bold flex items-center gap-2 border border-green-200">
                <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                {{ $activeYear->nama }}
            </div>
             <form action="{{ route('settings.year.regenerate', $activeYear->id) }}" method="POST"
                   data-confirm-delete="true"
                   data-title="Perbaiki Periode?"
                   data-message="Generate ulang periode default (Cawu/Semester) untuk tahun aktif ini?"
                   data-confirm-text="Ya, Perbaiki!"
                   data-confirm-color="#ca8a04"
                   data-icon="question">
                 @csrf
                 <button type="submit" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-1.5 rounded-lg shadow-sm transition-all flex items-center justify-center" title="Fix Periode">
                     <span class="material-symbols-outlined text-[20px]">build</span>
                 </button>
             </form>
            @else
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded-lg font-bold flex items-center gap-2">
                <span class="material-symbols-outlined">warning</span> Tahun Ajaran Kosong
            </div>
            @endif
            
            <!-- Backup Button -->
            <a href="{{ route('backup.store') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition-all flex items-center gap-2 text-sm">
                <span class="material-symbols-outlined text-[18px]">cloud_download</span> Backup DB
            </a>
            
            <button onclick="document.getElementById('yearModal').classList.remove('hidden')" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition-all flex items-center gap-2 text-sm">
                <span class="material-symbols-outlined text-[18px]">edit_calendar</span> Kelola Tahun
            </button>
        </div>
    </div>

    @if(!$activeYear)
        <div class="p-8 text-center bg-yellow-50 text-yellow-800 rounded-xl border border-yellow-200">
            <h3 class="font-bold text-lg">Sistem Belum Siap</h3>
            <p>Silakan buat Tahun Ajaran baru terlebih dahulu.</p>
        </div>
    @else

    <!-- TABS NAVIGATION -->
    <div class="border-b border-slate-200 dark:border-slate-700 overflow-x-auto no-scrollbar">
        <nav class="-mb-px flex space-x-6 min-w-max px-2" aria-label="Tabs">
            <button @click="activeTab = 'grading'"
                :class="activeTab === 'grading' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all">
                <span class="material-symbols-outlined mr-2" :class="activeTab === 'grading' ? 'text-primary' : 'text-slate-400 group-hover:text-slate-500'">tune</span>
                Aturan Penilaian
            </button>

            <button @click="activeTab = 'kkm'"
                :class="activeTab === 'kkm' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all">
                <span class="material-symbols-outlined mr-2" :class="activeTab === 'kkm' ? 'text-primary' : 'text-slate-400 group-hover:text-slate-500'">analytics</span>
                Target KKM
            </button>

            <button @click="activeTab = 'identity'"
                :class="activeTab === 'identity' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all">
                <span class="material-symbols-outlined mr-2" :class="activeTab === 'identity' ? 'text-primary' : 'text-slate-400 group-hover:text-slate-500'">branding_watermark</span>
                Identitas Aplikasi
            </button>

            <button @click="activeTab = 'general'"
                :class="activeTab === 'general' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all">
                <span class="material-symbols-outlined mr-2" :class="activeTab === 'general' ? 'text-primary' : 'text-slate-400 group-hover:text-slate-500'">settings_applications</span>
                Umum & Lainnya
            </button>

            <!-- MAINTENANCE TAB -->
            @if(auth()->user()->role === 'admin')
            <button @click="activeTab = 'backup'"
                :class="activeTab === 'backup' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-blue-600 hover:border-blue-300'"
                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all">
                <span class="material-symbols-outlined mr-2" :class="activeTab === 'backup' ? 'text-blue-600' : 'text-slate-400 group-hover:text-blue-500'">cloud_sync</span>
                Backup & Restore
            </button>

            <button @click="activeTab = 'maintenance'"
                :class="activeTab === 'maintenance' ? 'border-red-500 text-red-600' : 'border-transparent text-slate-500 hover:text-red-600 hover:border-red-300'"
                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all">
                <span class="material-symbols-outlined mr-2" :class="activeTab === 'maintenance' ? 'text-red-600' : 'text-slate-400 group-hover:text-red-500'">health_and_safety</span>
                Huru Hara
            </button>
            @endif
        </nav>
    </div>

    <!-- TAB CONTENT CONTAINER -->
    <div class="min-h-[400px]">

        <!-- TAB 1: ATURAN PENILAIAN (GRADING) -->
        <!-- TAB 1: ATURAN PENILAIAN (GRADING) -->
        <div x-show="activeTab === 'grading'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            
            <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 relative">
                
                <!-- Toolbar (Jenjang Switcher) -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="bg-slate-100 dark:bg-slate-800 p-1 rounded-lg inline-flex">
                        <a href="?tab=grading&jenjang=MI" 
                           class="px-4 py-1.5 rounded-md text-sm font-bold transition-all flex items-center gap-2 {{ $jenjang === 'MI' ? 'bg-white dark:bg-slate-700 shadow text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-700' }}">
                            MI (Cawu)
                        </a>
                        <a href="?tab=grading&jenjang=MTS" 
                           class="px-4 py-1.5 rounded-md text-sm font-bold transition-all flex items-center gap-2 {{ $jenjang === 'MTS' ? 'bg-white dark:bg-slate-700 shadow text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-700' }}">
                            MTs (Semester)
                        </a>
                    </div>
                </div>

                <!-- Hidden Forms for Period Toggles -->
                @forelse($periods as $periode)
                <form id="form-toggle-{{ $periode->id }}" action="{{ route('settings.period.toggle', $periode->id) }}" method="POST" class="hidden">
                    @csrf
                </form>
                @empty
                @endforelse

                <form action="{{ route('settings.grading.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="jenjang" value="{{ $jenjang }}">
                    <input type="hidden" name="tab" value="grading">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        
                        <!-- Left Column: Bobot & Kenaikan -->
                        <div class="lg:col-span-5 space-y-6">
                            
                            <!-- Card: Periode Input Nilai -->
                            <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 mb-6">
                                <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-sm mb-4">
                                    <span class="material-symbols-outlined text-orange-500 text-sm">lock_clock</span> Periode Input & Kunci Nilai
                                </h4>
                                <div class="flex flex-col gap-3">
                                    @foreach($periods as $periode)
                                        <div class="flex items-center justify-between p-3 rounded-lg border {{ $periode->status == 'aktif' ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' : 'bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-slate-700' }}">
                                            <div>
                                                <div class="font-bold text-sm text-slate-800 dark:text-white">{{ $periode->nama_periode }}</div>
                                                <div class="text-[10px] {{ $periode->status == 'aktif' ? 'text-green-600' : 'text-slate-500' }}">
                                                    {{ $periode->status == 'aktif' ? 'Aktif (Bisa Input)' : 'Terkunci' }}
                                                </div>
                                            </div>
                                            
                                            <button type="submit" form="form-toggle-{{ $periode->id }}" 
                                                class="w-10 h-6 rounded-full transition-colors relative {{ $periode->status == 'aktif' ? 'bg-green-500' : 'bg-slate-300' }}">
                                                <span class="absolute top-1 left-1 bg-white w-4 h-4 rounded-full transition-transform {{ $periode->status == 'aktif' ? 'translate-x-4' : '' }}"></span>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Card: Bobot -->
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-200 dark:border-indigo-800 p-5">
                                <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-sm mb-4">
                                    <span class="material-symbols-outlined text-indigo-500 text-sm">tune</span> Komponen & Bobot ({{ $jenjang }})
                                </h4>
                                
                                <div class="space-y-4">
                                    <!-- Harian -->
                                    <div class="bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200 dark:border-slate-700">
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-1">Nilai Harian (PH)</label>
                                        <div class="relative">
                                            <input type="number" name="bobot_harian" value="{{ $activeBobot->bobot_harian ?? 0 }}" {{ $isLocked ? 'disabled' : '' }}
                                                   class="w-full text-center font-bold rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-indigo-600">
                                            <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                                        </div>
                                    </div>
                                    
                                    <!-- UTS -->
                                    <div class="bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200 dark:border-slate-700">
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-1">{{ $jenjang === 'MI' ? 'Ujian Cawu (UTS)' : 'Nilai Tengah Semester (PTS)' }}</label>
                                        <div class="relative">
                                            <input type="number" name="bobot_uts_cawu" value="{{ $activeBobot->bobot_uts_cawu ?? 0 }}" {{ $isLocked ? 'disabled' : '' }}
                                                   class="w-full text-center font-bold rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-indigo-600">
                                            <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                                        </div>
                                    </div>

                                    <!-- UAS (MTS Only) -->
                                    @if($jenjang === 'MTS')
                                    <div class="bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200 dark:border-slate-700">
                                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-1">Nilai Akhir Semester (PAS)</label>
                                        <div class="relative">
                                            <input type="number" name="bobot_uas" value="{{ $activeBobot->bobot_uas ?? 0 }}" {{ $isLocked ? 'disabled' : '' }}
                                                   class="w-full text-center font-bold rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-indigo-600">
                                            <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <p class="text-[10px] text-slate-500 mt-2 italic">* Isi 0 jika tidak digunakan. Total disarankan 100% (tidak wajib).</p>
                            </div>



                            <!-- Card: Parameter Umum -->
                            <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                                <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-sm mb-4">
                                    <span class="material-symbols-outlined text-primary text-sm">settings</span> Parameter
                                </h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">KKM Default</label>
                                        <input type="number" name="kkm_default" value="{{ $gradingSettings['kkm_default'] ?? 70 }}" {{ $isLocked ? 'disabled' : '' }} 
                                               class="w-full text-center font-bold rounded-lg border-slate-300 focus:ring-primary text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Tipe Skala</label>
                                        <select name="scale_type" {{ $isLocked ? 'disabled' : '' }} class="w-full font-bold rounded-lg border-slate-300 focus:ring-primary text-sm">
                                            <option value="0-100" {{ ($gradingSettings['scale_type'] ?? '') == '0-100' ? 'selected' : '' }}>0 - 100</option>
                                            <option value="1-4" {{ ($gradingSettings['scale_type'] ?? '') == '1-4' ? 'selected' : '' }}>1 - 4</option>
                                            <option value="0-10" {{ ($gradingSettings['scale_type'] ?? '') == '0-10' ? 'selected' : '' }}>0 - 10</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="hidden" name="rounding_enable" value="0">
                                        <input type="checkbox" name="rounding_enable" value="1" {{ ($gradingSettings['rounding_enable'] ?? 0) ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }} 
                                               class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Bulatkan Nilai Akhir</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="lg:col-span-7 space-y-6">
                            
                             <!-- Card: Syarat Kenaikan -->
                             <div class="bg-slate-50 dark:bg-slate-900/30 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                                <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-sm mb-4">
                                    <span class="material-symbols-outlined text-primary text-sm">trending_up</span> Syarat Kenaikan & Akademik
                                </h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Hari Efektif /Thn</label>
                                        <input type="number" name="total_effective_days" value="{{ $gradingSettings['total_effective_days'] ?? 220 }}" {{ $isLocked ? 'disabled' : '' }} 
                                               class="w-full text-center font-bold rounded-lg border-slate-300 focus:ring-primary text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Max Mapel Gagal</label>
                                        <input type="number" name="promotion_max_kkm_failure" value="{{ $gradingSettings['promotion_max_kkm_failure'] ?? 3 }}" {{ $isLocked ? 'disabled' : '' }} 
                                               class="w-full text-center font-bold rounded-lg border-slate-300 focus:ring-primary text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Min Absensi (%)</label>
                                        <input type="number" name="promotion_min_attendance" value="{{ $gradingSettings['promotion_min_attendance'] ?? 85 }}" {{ $isLocked ? 'disabled' : '' }} 
                                               class="w-full text-center font-bold rounded-lg border-slate-300 focus:ring-primary text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Min Sikap</label>
                                        <select name="promotion_min_attitude" {{ $isLocked ? 'disabled' : '' }} class="w-full text-center font-bold rounded-lg border-slate-300 focus:ring-primary text-sm">
                                            <option value="A" {{ ($gradingSettings['promotion_min_attitude'] ?? '') == 'A' ? 'selected' : '' }}>A</option>
                                            <option value="B" {{ ($gradingSettings['promotion_min_attitude'] ?? '') == 'B' ? 'selected' : '' }}>B</option>
                                            <option value="C" {{ ($gradingSettings['promotion_min_attitude'] ?? '') == 'C' ? 'selected' : '' }}>C</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-span-2">
                                        <label class="flex items-start gap-3 p-3 bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-200 transition-colors">
                                            <input type="hidden" name="promotion_requires_all_periods" value="0">
                                            <input type="checkbox" name="promotion_requires_all_periods" value="1" 
                                                {{ ($gradingSettings['promotion_requires_all_periods'] ?? 1) ? 'checked' : '' }} 
                                                {{ $isLocked ? 'disabled' : '' }} 
                                                class="w-5 h-5 mt-0.5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                            <div>
                                                <span class="font-bold text-slate-700 dark:text-slate-300 text-sm block">Wajib Mengikuti SEMUA Periode Ujian</span>
                                                <span class="text-[10px] text-slate-500 block leading-tight">
                                                    Siswa <b>WAJIB</b> memiliki nilai di semua periode (Ganjil & Genap) dalam satu tahun ajaran. 
                                                    Jika tidak, akan otomatis bertanda <b class="text-amber-500">Perlu Ditinjau</b>.
                                                </span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                             </div>

                             <!-- Card: Predikat -->
                             <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                                    <h4 class="font-bold text-xs uppercase text-slate-500">Interval Predikat ({{ $jenjang }})</h4>
                                </div>
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-slate-500 uppercase bg-white border-b border-slate-100">
                                        <tr>
                                            <th class="px-4 py-2">Grade</th>
                                            <th class="px-4 py-2 text-center">Batas Bawah</th>
                                            <th class="px-4 py-2 text-center">Batas Atas</th>
                                            <th class="px-4 py-2">Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($predicates as $p)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-2">
                                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-700">{{ $p->grade }}</div>
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <input type="number" name="predikat[{{ $p->grade }}][min]" value="{{ $p->min_score }}" {{ $isLocked ? 'disabled' : '' }} 
                                                       class="w-16 text-center font-bold bg-transparent border-0 border-b-2 border-slate-100 focus:border-primary focus:ring-0 p-1">
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <input type="number" name="predikat[{{ $p->grade }}][max]" value="{{ $p->max_score }}" {{ $isLocked ? 'disabled' : '' }} 
                                                       class="w-16 text-center font-bold bg-transparent border-0 border-b-2 border-slate-100 focus:border-primary focus:ring-0 p-1">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="predikat[{{ $p->grade }}][deskripsi]" value="{{ $p->deskripsi ?? '' }}" {{ $isLocked ? 'disabled' : '' }} 
                                                       class="w-full text-sm bg-transparent border-0 focus:ring-0 disabled:opacity-50" placeholder="Deskripsi...">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                             </div>

                             <!-- Card: Pengaturan Titimangsa -->
                             <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5 mt-6">
                                <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-sm mb-4">
                                    <span class="material-symbols-outlined text-green-600 text-sm">calendar_month</span> Pengaturan Titimangsa Rapor
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Tempat Titimangsa</label>
                                        <input type="text" name="titimangsa_tempat_{{ strtolower($jenjang) }}" 
                                               value="{{ $gradingSettings['titimangsa_tempat_' . strtolower($jenjang)] ?? ($school->kabupaten ?? '') }}" 
                                               placeholder="Contoh: Jakarta"
                                               class="w-full font-bold rounded-lg border-slate-300 focus:ring-primary text-sm">
                                        <p class="text-[10px] text-slate-400 mt-1">Kota/Tempat di atas tanda tangan.</p>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Tanggal Rapor</label>
                                        <div class="relative">
                                            <input type="text" name="titimangsa_{{ strtolower($jenjang) }}" 
                                                value="{{ $gradingSettings['titimangsa_' . strtolower($jenjang)] ?? '' }}" 
                                                placeholder="Contoh: 20 Juli 2024"
                                                class="w-full font-bold rounded-lg border-slate-300 focus:ring-primary text-sm">
                                             <span class="absolute right-3 top-2 text-slate-400 material-symbols-outlined text-[18px]">calendar_today</span>
                                        </div>
                                        <p class="text-[10px] text-slate-400 mt-1">Tanggal pembagian rapor.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Card: Pengaturan Tingkat Akhir (Moved to Standalone Card) -->
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl shadow-sm border border-indigo-200 dark:border-indigo-800 p-5 mt-6">
                                <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-sm mb-4">
                                    <span class="material-symbols-outlined text-indigo-600 text-sm">school</span> Pengaturan Tingkat Akhir (Kelulusan)
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Kelas Akhir MI</label>
                                        <input type="number" name="final_grade_mi" 
                                            value="{{ \App\Models\GlobalSetting::val('final_grade_mi', 6) }}" 
                                            class="w-full font-bold rounded-lg border-indigo-200 focus:ring-indigo-500 text-indigo-700 text-sm" placeholder="6">
                                        <p class="text-[10px] text-slate-400 mt-1">Siswa kelas ini akan dianggap LULUS jika naik.</p>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Kelas Akhir MTs/MA</label>
                                        <input type="number" name="final_grade_mts" 
                                            value="{{ \App\Models\GlobalSetting::val('final_grade_mts', 9) }}" 
                                            class="w-full font-bold rounded-lg border-indigo-200 focus:ring-indigo-500 text-indigo-700 text-sm" placeholder="9 (atau 3)">
                                        <p class="text-[10px] text-slate-400 mt-1">Siswa kelas ini akan dianggap LULUS.</p>
                                    </div>
                                    
                                    <!-- NEW: Range Configuration -->
                                    <div class="col-span-1 md:col-span-2 pt-4 border-t border-indigo-100 dark:border-indigo-800">
                                        <h5 class="font-bold text-xs uppercase text-slate-500 mb-3">Rentang Kelas Perhitungan Ijazah (DKN)</h5>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Rentang MI</label>
                                                <input type="text" name="ijazah_range_mi" 
                                                    value="{{ \App\Models\GlobalSetting::val('ijazah_range_mi', '4,5,6') }}" 
                                                    class="w-full font-mono text-xs rounded-lg border-slate-300 focus:ring-indigo-500" placeholder="4,5,6">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Rentang MTs</label>
                                                <input type="text" name="ijazah_range_mts" 
                                                    value="{{ \App\Models\GlobalSetting::val('ijazah_range_mts', '7,8,9,1,2,3') }}" 
                                                    class="w-full font-mono text-xs rounded-lg border-slate-300 focus:ring-indigo-500" placeholder="7,8,9,1,2,3">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Rentang MA</label>
                                                <input type="text" name="ijazah_range_ma" 
                                                    value="{{ \App\Models\GlobalSetting::val('ijazah_range_ma', '10,11,12,1,2,3') }}" 
                                                    class="w-full font-mono text-xs rounded-lg border-slate-300 focus:ring-indigo-500" placeholder="10,11,12,1,2,3">
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-slate-400 mt-2 italic px-1">
                                            * Masukkan tingkat kelas yang akan diambil nilai Rapor-nya. Pisahkan dengan koma (contoh: 7,8,9).
                                            Dukungan untuk format relatif (1,2,3) juga disertakan.
                                        </p>
                                    </div>
                                </div>
                            </div>

                             <!-- Submit Button -->
                             <div class="flex justify-end pt-4">
                                @if(isset($isLocked) && $isLocked)
                                    <div class="bg-amber-100 text-amber-800 px-4 py-2 rounded-lg font-bold flex items-center gap-2 border border-amber-200">
                                        <span class="material-symbols-outlined">lock</span> Mode Baca
                                    </div>
                                @else
                                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
                                        <span class="material-symbols-outlined">save</span> Simpan Aturan
                                    </button>
                                @endif
                             </div>



                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 2: TARGET KKM (KKM) -->
        <div x-show="activeTab === 'kkm'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            
            <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                         <h3 class="font-bold text-lg text-slate-900 dark:text-white">Target KKM Mata Pelajaran</h3>
                         <p class="text-sm text-slate-500">Nilai minimum untuk ketuntasan belajar per mata pelajaran.</p>
                    </div>
                </div>
                
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <form id="kkmForm" action="{{ route('settings.kkm.store') }}" method="POST">
                        @csrf
                        <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex justify-end">
                             <button type="submit" x-show="!isLocked" class="bg-slate-800 text-white px-5 py-2 rounded-lg font-bold text-sm hover:bg-slate-700 transition-all shadow-sm">
                                Simpan KKM
                            </button>
                             <div x-show="isLocked" class="bg-slate-200 text-slate-500 px-5 py-2 rounded-lg font-bold text-sm flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">lock</span> Terkunci
                            </div>
                        </div>
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white dark:bg-slate-800 uppercase text-xs font-bold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-6 py-4">Mata Pelajaran</th>
                                    <th class="px-6 py-4 w-40 text-center bg-teal-50/50 text-teal-700">KKM MI</th>
                                    <th class="px-6 py-4 w-40 text-center bg-indigo-50/50 text-indigo-700">KKM MTs</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white">
                                @foreach($mapels as $mapel)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-3 font-semibold text-slate-700 dark:text-white">
                                        {{ $mapel->nama_mapel }}
                                        <span class="text-xs font-normal text-slate-400 block">{{ $mapel->kode_mapel }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-center bg-teal-50/20">
                                        @if($mapel->target_jenjang == 'MI' || $mapel->target_jenjang == 'SEMUA')
                                            <input type="number" name="kkm[{{ $mapel->id }}][MI]" value="{{ $kkms[$mapel->id.'-MI']->nilai_kkm ?? 70 }}" :disabled="isLocked" class="w-20 text-center font-bold text-teal-700 rounded border-slate-200 focus:border-teal-500 focus:ring-teal-500 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-center bg-indigo-50/20">
                                         @if($mapel->target_jenjang == 'MTS' || $mapel->target_jenjang == 'SEMUA')
                                            <input type="number" name="kkm[{{ $mapel->id }}][MTS]" value="{{ $kkms[$mapel->id.'-MTS']->nilai_kkm ?? 75 }}" :disabled="isLocked" class="w-20 text-center font-bold text-indigo-700 rounded border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB [NEW]: IDENTITAS APLIKASI -->
        <div x-show="activeTab === 'identity'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">storefront</span> Identitas Sekolah & Aplikasi
                </h3>

                <form action="{{ route('settings.identity.update') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <!-- Left: Info Aplikasi -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Aplikasi</label>
                                <input type="text" name="app_name" value="{{ \App\Models\GlobalSetting::val('app_name', 'E-Rapor') }}" class="w-full rounded-lg border-slate-300 dark:bg-slate-800 focus:ring-primary focus:border-primary">
                                <p class="text-xs text-slate-500 mt-1">Nama yang tampil di Tab Browser dan Login Page.</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Sub-Judul / Tagline</label>
                                <input type="text" name="app_tagline" value="{{ \App\Models\GlobalSetting::val('app_tagline', 'Integrated System') }}" class="w-full rounded-lg border-slate-300 dark:bg-slate-800 focus:ring-primary focus:border-primary">
                                <p class="text-xs text-slate-500 mt-1">Teks kecil di bawah nama aplikasi. Contoh: "Integrated System"</p>
                            </div>
                        </div>

                        <!-- Right: Logo Upload -->
                        <div class="space-y-4">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Logo Sekolah</label>
                            
                            <div class="flex items-start gap-4">
                                <div class="w-32 h-32 bg-slate-100 rounded-lg border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden relative group">
                                    @if(\App\Models\GlobalSetting::val('app_logo'))
                                        <img src="{{ asset('public/' . \App\Models\GlobalSetting::val('app_logo')) }}" class="w-full h-full object-contain p-2">
                                    @else
                                        <span class="material-symbols-outlined text-4xl text-slate-300">image</span>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="app_logo" class="block w-full text-sm text-slate-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-full file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-primary/10 file:text-primary
                                      hover:file:bg-primary/20" accept="image/*"
                                    >
                                    <p class="text-xs text-slate-500 mt-2">Format: PNG, JPG (Transparan disarankan). Maks 2MB.</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- HEADMASTER CONFIGURATION (PER JENJANG) -->
                    <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700">
                        <h4 class="font-bold text-base text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                             <span class="material-symbols-outlined text-indigo-500">supervisor_account</span> Kepala Madrasah (Tanda Tangan Rapor & DKN)
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- MI -->
                            <div class="bg-teal-50 dark:bg-teal-900/20 p-4 rounded-xl border border-teal-100 dark:border-teal-800">
                                <h5 class="font-bold text-sm text-teal-800 dark:text-teal-300 mb-3 border-b border-teal-200 pb-2">Tingkat MI</h5>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Nama Kepala MI</label>
                                        <input type="text" name="hm_name_mi" value="{{ \App\Models\GlobalSetting::val('hm_name_mi') }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500" placeholder="Nama Lengkap & Gelar">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">NIP Kepala MI</label>
                                        <input type="text" name="hm_nip_mi" value="{{ \App\Models\GlobalSetting::val('hm_nip_mi') }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500" placeholder="NIP e.g. 198...">
                                    </div>
                                </div>
                            </div>

                            <!-- MTs -->
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800">
                                <h5 class="font-bold text-sm text-indigo-800 dark:text-indigo-300 mb-3 border-b border-indigo-200 pb-2">Tingkat MTs</h5>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Nama Kepala MTs</label>
                                        <input type="text" name="hm_name_mts" value="{{ \App\Models\GlobalSetting::val('hm_name_mts') }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nama Lengkap & Gelar">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">NIP Kepala MTs</label>
                                        <input type="text" name="hm_nip_mts" value="{{ \App\Models\GlobalSetting::val('hm_nip_mts') }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="NIP">
                                    </div>
                                </div>
                            </div>

                            <!-- MA -->
                            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-xl border border-purple-100 dark:border-purple-800">
                                <h5 class="font-bold text-sm text-purple-800 dark:text-purple-300 mb-3 border-b border-purple-200 pb-2">Tingkat MA</h5>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Nama Kepala MA</label>
                                        <input type="text" name="hm_name_ma" value="{{ \App\Models\GlobalSetting::val('hm_name_ma') }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-purple-500 focus:border-purple-500" placeholder="Nama Lengkap & Gelar">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">NIP Kepala MA</label>
                                        <input type="text" name="hm_nip_ma" value="{{ \App\Models\GlobalSetting::val('hm_nip_ma') }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-purple-500 focus:border-purple-500" placeholder="NIP">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-700" x-show="!isLocked">
                    <button @click="saveRules()" :disabled="loading" class="bg-green-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-green-700 hover:scale-105 shadow-lg transition-all flex items-center gap-2 disabled:opacity-50 disabled:scale-100">
                        <span class="material-symbols-outlined" x-show="!loading">save_as</span>
                        <span class="material-symbols-outlined animate-spin" x-show="loading">sync</span>
                        <span x-text="loading ? 'Menyimpan...' : 'Simpan Konfigurasi'"></span>
                    </button>
                </div>       
                
                <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-700" x-show="isLocked">
                    <div class="px-6 py-3 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center gap-2 border border-slate-200">
                         <span class="material-symbols-outlined">lock</span> Mode Baca (Terkunci)
                    </div>
                </div>       </form>
            </div>
        </div>

        <!-- TAB 3: UMUM & PERIODE (GENERAL) -->
        <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            
             <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                 
                 <!-- LEFT COLUMN: DEADLINE & WHITELIST -->
                 <div class="space-y-6">
                     
                     <!-- Card: Safety Lock (NEW) -->
                     <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <span class="material-symbols-outlined text-6xl text-slate-800 dark:text-white">lock_person</span>
                        </div>
                        <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                             <span class="material-symbols-outlined text-amber-500">lock</span> Mode Edit Data Lama
                        </h3>
                        <p class="text-xs text-slate-500 mb-6 max-w-md">
                            Secara default, tahun ajaran lampau dikunci untuk menjaga validitas data rapor yang sudah terbit.
                            Aktifkan opsi ini jika Anda perlu memperbaiki kesalahan masa lalu.
                        </p>

                        <form action="{{ route('settings.general.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="safety_lock_marker" value="1">
                            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                <div>
                                    <h4 class="font-bold text-sm text-slate-700 dark:text-white">Izinkan Edit Tahun Lalu</h4>
                                    <p class="text-[10px] text-slate-500">Membuka kunci input nilai & kenaikan kelas.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="allow_edit_past_data" value="1" class="sr-only peer" onchange="this.form.submit()" {{ \App\Models\GlobalSetting::val('allow_edit_past_data') ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 dark:peer-focus:ring-amber-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-600"></div>
                                </label>
                            </div>
                        </form>
                     </div>

                     <!-- Card: Deadline Settings -->
                    <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                             <span class="material-symbols-outlined text-red-500">timer</span> Pengaturan Tenggat
                        </h3>
                        <form action="{{ route('settings.deadline.update') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                @foreach($periods as $periode)
                                <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 border {{ now() > $periode->end_date ? 'border-red-200' : 'border-green-200' }}">
                                    <div class="flex justify-between items-center mb-2">
                                        <div>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $periode->lingkup_jenjang }}</span>
                                            <h4 class="font-bold text-sm text-slate-800 dark:text-white">{{ $periode->nama_periode }}</h4>
                                        </div>
                                        <div>
                                            @if($periode->end_date)
                                                @if(now() > $periode->end_date)
                                                    <span class="text-[10px] font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded">TERKUNCI</span>
                                                @else
                                                    <span class="text-[10px] font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded">TERBUKA</span>
                                                @endif
                                            @else
                                                <span class="text-[10px] text-slate-400 italic">Belum diset</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex gap-2 items-end">
                                        <div class="flex-1">
                                            <input type="datetime-local" name="deadlines[{{ $periode->id }}]" 
                                                   value="{{ $periode->end_date ? \Carbon\Carbon::parse($periode->end_date)->format('Y-m-d\TH:i') : '' }}"
                                                   class="w-full rounded-lg border-slate-300 dark:bg-slate-800 text-xs font-bold">
                                        </div>
                                    </div>
                                    <!-- Quick Actions -->
                                    <div class="mt-2 text-right">
                                        @if(now() > $periode->end_date)
                                             <button type="button" @click="confirmAction('{{ route('settings.deadline.toggle', ['id' => $periode->id, 'action' => 'unlock']) }}', 'Buka Periode Ini 24 Jam?')" class="text-[10px] font-bold text-green-600 hover:underline">Buka 24 Jam</button>
                                        @else
                                             <button type="button" @click="confirmAction('{{ route('settings.deadline.toggle', ['id' => $periode->id, 'action' => 'lock']) }}', 'Kunci Periode Ini Sekarang?')" class="text-[10px] font-bold text-red-600 hover:underline">Kunci Sekarang</button>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-right">
                                <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-xs hover:bg-slate-800 shadow-lg">Simpan Tenggat</button>
                            </div>
                        </form>
                    </div>

                    <!-- Card: Whitelist -->
                    <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-green-500">verified_user</span> Whitelist
                            </h3>
                            <button onclick="document.getElementById('modalWhitelist').showModal()" class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg hover:bg-indigo-100">
                                + Guru
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] uppercase font-bold text-slate-500">
                                    <tr>
                                        <th class="p-2 pl-3">Guru</th>
                                        <th class="p-2">Berlaku Sampai</th>
                                        <th class="p-2 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    @forelse($whitelist as $item)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="p-2 pl-3">
                                            <span class="font-bold block">{{ $item->guru_name }}</span>
                                            <span class="text-[10px] text-slate-500">{{ $item->nama_periode }}</span>
                                        </td>
                                        <td class="p-2 text-green-600 font-bold">{{ \Carbon\Carbon::parse($item->valid_until)->format('d M H:i') }}</td>
                                        <td class="p-2 text-right">
                                            <form action="{{ route('settings.deadline.whitelist.remove', $item->id) }}" method="POST"
                                                  data-confirm-delete="true"
                                                  data-title="Cabut Akses?"
                                                  data-message="Guru tidak akan bisa input nilai lagi."
                                                  data-confirm-text="Ya, Cabut!"
                                                  data-confirm-color="#ef4444">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-bold">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="p-4 text-center text-slate-400 italic">Belum ada whitelist.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                 </div>

                 <!-- RIGHT COLUMN: RAPOR & SHORTCUTS -->
                 <div class="space-y-6">
                     <!-- Konfigurasi Rapor -->
                     <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-6">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 dark:text-white">Opsi Cetak Rapor</h3>
                            <p class="text-sm text-slate-500">Komponen opsional pada PDF Rapor.</p>
                        </div>
                        
                        <form action="{{ route('settings.users.permissions') }}" method="POST" class="space-y-4">
                            @csrf
                            <!-- Toggle Ekskul -->
                            <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200">
                                 <div class="flex items-center gap-3">
                                     <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                                         <span class="material-symbols-outlined text-lg">sports_soccer</span>
                                     </div>
                                     <span class="font-bold text-slate-700 text-sm">Tabel Ekstrakurikuler</span>
                                 </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="rapor_show_ekskul" value="0">
                                    <input type="checkbox" name="rapor_show_ekskul" value="1" class="sr-only peer" {{ \App\Models\GlobalSetting::val('rapor_show_ekskul', 1) ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-primary peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                                </label>
                            </div>
                            
                            <!-- Toggle Prestasi -->
                            <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                                        <span class="material-symbols-outlined text-lg">emoji_events</span>
                                    </div>
                                    <span class="font-bold text-slate-700 text-sm">Tabel Prestasi</span>
                                </div>
                               <label class="relative inline-flex items-center cursor-pointer">
                                   <input type="hidden" name="rapor_show_prestasi" value="0">
                                   <input type="checkbox" name="rapor_show_prestasi" value="1" class="sr-only peer" {{ \App\Models\GlobalSetting::val('rapor_show_prestasi', 0) ? 'checked' : '' }}>
                                   <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-primary peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                               </label>
                           </div>
    
                           <div class="pt-4 text-right">
                               <button type="submit" class="text-sm font-bold text-primary hover:text-green-700 hover:underline">Simpan Opsi Rapor</button>
                           </div>
                        </form>
                     </div>
    
                     <!-- Shortcut Links -->
                     <div>
                         <div class="grid grid-cols-1 gap-4">
                            <a href="{{ route('settings.users.index') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-primary/50 hover:bg-primary/5 transition-all group">
                                <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary">group</span>
                                <div class="text-left">
                                    <h4 class="font-bold text-slate-700 text-sm">User & Hak Akses</h4>
                                    <p class="text-xs text-slate-500">Kelola akun guru & siswa</p>
                                </div>
                            </a>
                            <a href="{{ route('settings.menus.index') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-primary/50 hover:bg-primary/5 transition-all group">
                                <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary">menu_open</span>
                                <div class="text-left">
                                    <h4 class="font-bold text-slate-700 text-sm">Menu Sidebar</h4>
                                    <p class="text-xs text-slate-500">Atur menu aplikasi</p>
                                </div>
                            </a>
                            <a href="{{ route('settings.pages.index') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-primary/50 hover:bg-primary/5 transition-all group">
                                <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-primary">article</span>
                                <div class="text-left">
                                    <h4 class="font-bold text-slate-700 text-sm">Halaman & Informasi</h4>
                                    <p class="text-xs text-slate-500">Edit konten halaman</p>
                                </div>
                            </a>
                         </div>
                     </div>


                 </div>

             </div>
        </div>

        <!-- TAB: BACKUP & RESTORE -->
        <div x-show="activeTab === 'backup'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                
                <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600">cloud_sync</span> Backup & Restore Database
                </h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Create Backup -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-xl border border-blue-200 dark:border-blue-800 flex flex-col justify-between">
                        <div>
                            <h4 class="font-bold text-blue-800 dark:text-blue-300 text-lg mb-2">Buat Backup Baru</h4>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                                Sistem akan membuat file `.sql` lengkap dari database saat ini. 
                                File akan disimpan di server dan bisa didownload.
                            </p>
                        </div>
                        <a href="{{ route('backup.store') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg text-center shadow-lg transition-transform hover:scale-105 flex items-center justify-center gap-2">
                             <span class="material-symbols-outlined">save</span> Proses Backup Sekarang
                        </a>
                    </div>

                    <!-- Upload Restore -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-200 dark:border-slate-700">
                        <h4 class="font-bold text-slate-800 dark:text-slate-300 text-lg mb-2">Restore dari File</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                            Upload file `.sql` dari komputer Anda untuk mengembalikan database.
                            <br><b class="text-red-500">PERHATIAN:</b> Data saat ini akan ditimpa!
                        </p>
                        <form action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data" class="flex gap-2">
                            @csrf
                            <input type="file" name="backup_file" accept=".sql" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-300 rounded-lg">
                            <button type="submit" onclick="return confirm('Yakin ingin merestore database? Data saat ini akan hilang!')" class="bg-slate-800 hover:bg-slate-700 text-white font-bold px-4 py-2 rounded-lg shadow-md">
                                Restore
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Backup List -->
                <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="bg-slate-50 dark:bg-slate-800 px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                        <h4 class="font-bold text-slate-700 dark:text-slate-300">Riwayat Backup di Server</h4>
                    </div>
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white dark:bg-slate-800 text-xs uppercase font-bold text-slate-500 border-b border-slate-100 dark:border-slate-700">
                            <tr>
                                <th class="px-6 py-3">Nama File</th>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Ukuran</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700 bg-white dark:bg-[#1a2e22]">
                            @forelse($backups as $backup)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-200">
                                    {{ $backup->filename }}
                                </td>
                                <td class="px-6 py-3 text-slate-500">
                                    {{ $backup->created_at->format('d M Y H:i') }}
                                    <span class="text-xs text-slate-400 block">{{ $backup->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-6 py-3 text-slate-500 font-mono text-xs">
                                    {{ $backup->size }}
                                </td>
                                <td class="px-6 py-3 text-right flex justify-end gap-2">
                                    <form action="{{ route('backup.restore-local', $backup->filename) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin restore dari file ini? Data saat ini akan hilang!')">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-amber-600 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded border border-amber-200 flex items-center gap-1" title="Restore">
                                            <span class="material-symbols-outlined text-[16px]">history</span> Restore
                                        </button>
                                    </form>
                                    
                                    <a href="{{ route('backup.download', $backup->filename) }}" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded border border-blue-200 flex items-center gap-1" title="Download">
                                        <span class="material-symbols-outlined text-[16px]">download</span> Download
                                    </a>

                                    <form action="{{ route('backup.destroy', $backup->filename) }}" method="POST"
                                          onsubmit="return confirm('Hapus file backup ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded border border-red-200 flex items-center gap-1" title="Hapus">
                                            <span class="material-symbols-outlined text-[16px]">delete</span> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-400 italic">
                                    Belum ada file backup tersimpan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- TAB 4: SYSTEM HEALTH DASHBOARD -->
        <div x-show="activeTab === 'maintenance'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <div class="bg-red-50 dark:bg-red-900/10 rounded-xl shadow-sm border border-red-200 dark:border-red-800 p-6 md:p-8">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 dark:bg-red-800/30 text-red-600 dark:text-red-400 mb-4">
                        <span class="material-symbols-outlined text-5xl">medical_services</span>
                    </div>
                    <h3 class="text-2xl font-bold text-red-700 dark:text-red-400 mb-2">Peta Masalah & Solusi (System Health)</h3>
                    <p class="text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                        Pusat perbaikan data mandiri. Gunakan fitur-fitur ini untuk memperbaiki error sistem tanpa perlu akses database manual.
                    </p>
                </div>

                <!-- AUTO UPDATE CARD (Hero Banner) -->
                <!-- SUPER MIGRATION (DATA SYNC) -->
                <div class="mb-8 p-6 bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl shadow-xl text-white flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden max-w-6xl mx-auto">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                            <span class="material-symbols-outlined text-9xl">move_up</span>
                    </div>
                    
                    <div class="relative z-10 flex-1">
                        <h3 class="text-2xl font-bold flex items-center gap-2 mb-2">
                                <span class="material-symbols-outlined">dataset_linked</span> Super Migration (Data Sync)
                        </h3>
                        <p class="text-emerald-100 max-w-xl text-sm">
                            Pindahkan data antar server (Local ↔ Online) tanpa duplikat. 
                            <br>Sistem akan cerdas menggabungkan data (Upsert) tanpa menimpa akun Admin.
                        </p>
                    </div>

                    <div class="relative z-10 flex gap-3">
                        <!-- EXPORT -->
                        <form action="{{ route('settings.migration.export') }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-white text-emerald-700 hover:bg-emerald-50 font-bold py-3 px-6 rounded-xl shadow-lg transition-transform hover:scale-105 flex items-center gap-2">
                                <span class="material-symbols-outlined">download</span> Download Data (JSON)
                            </button>
                        </form>
                        
                        <!-- IMPORT TRIGGER -->
                        <button onclick="document.getElementById('modalMigration').showModal()" class="bg-emerald-800 text-white hover:bg-emerald-900 border border-emerald-500 font-bold py-3 px-6 rounded-xl shadow-lg transition-transform hover:scale-105 flex items-center gap-2">
                            <span class="material-symbols-outlined">upload</span> Upload Data
                        </button>
                    </div>
                </div>

                <!-- MODAL MIGRATION -->
                <dialog id="modalMigration" class="modal rounded-2xl shadow-2xl p-0 backdrop:backdrop-blur-sm">
                    <div class="w-[500px] bg-white dark:bg-slate-800 text-slate-900 dark:text-white flex flex-col">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                            <h3 class="font-bold text-lg"><span class="material-symbols-outlined align-bottom">upload_file</span> Import Data Migrasi</h3>
                            <button onclick="this.closest('dialog').close()" class="w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        <form action="{{ route('settings.migration.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                            @csrf
                            <div class="p-4 bg-blue-50 text-blue-800 text-xs rounded-lg flex gap-3 items-start">
                                <span class="material-symbols-outlined text-lg shrink-0">info</span>
                                <div>
                                    <b>Cara Kerja:</b>
                                    <ul class="list-disc ml-4 space-y-1 mt-1">
                                        <li>Data yang sama (misal NISN sama) akan <b>Diupdate</b>.</li>
                                        <li>Data baru akan <b>Ditambahkan</b>.</li>
                                        <li>Data Akun Admin <b>TIDAK</b> akan ditimpa.</li>
                                        <li>Proses mungkin memakan waktu untuk data besar.</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold mb-2">Pilih File JSON Backup</label>
                                <input type="file" name="backup_file" accept=".json" required
                                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-lg cursor-pointer">
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg flex items-center gap-2">
                                    <span class="material-symbols-outlined">cloud_upload</span> Mulai Migrasi
                                </button>
                            </div>
                        </form>
                    </div>
                </dialog>

                <div class="mb-8 p-6 bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl shadow-xl text-white flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden max-w-6xl mx-auto">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                         <span class="material-symbols-outlined text-9xl">cloud_sync</span>
                    </div>
                    
                    <div class="relative z-10">
                        <h3 class="text-2xl font-bold flex items-center gap-2 mb-2">
                             <span class="material-symbols-outlined">rocket_launch</span> Update Aplikasi Otomatis
                        </h3>
                        <p class="text-violet-100 max-w-xl">
                            Klik tombol ini untuk menarik update terbaru dari sistem pusat (GitHub). 
                            Pastikan koneksi internet server stabil.
                        </p>
                    </div>

                            <form action="{{ route('settings.maintenance.update-app') }}" method="POST" class="relative z-10" 
                  data-confirm-delete="true"
                  data-title="Mulai Update Otomatis?"
                  data-message="Website mungkin tidak bisa diakses beberapa detik saat proses update berlangsung.">
                        @csrf
                        <button type="submit" class="bg-white text-violet-700 hover:bg-violet-50 font-bold py-3 px-6 rounded-xl shadow-lg transition-transform hover:scale-105 flex items-center gap-2">
                            <span class="material-symbols-outlined">download</span> Update Sekarang
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto text-left">
                    
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-6xl mx-auto text-left">
                    
                    <!-- CARD 1: MAGIC FIX (ALL IN ONE) - CLEAN & PROFESSIONAL -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl border-l-4 border-emerald-500 shadow-lg relative overflow-hidden text-slate-800 dark:text-white col-span-1 md:col-span-2 p-6 flex flex-col md:flex-row items-center justify-between gap-6">
                        
                        <!-- Icon & Text -->
                        <div class="flex items-start gap-4 flex-1">
                            <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-full text-emerald-600 dark:text-emerald-400">
                                <span class="material-symbols-outlined text-3xl">auto_fix_high</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-xl text-slate-800 dark:text-white mb-2">
                                    Perbaikan Sistem Otomatis
                                </h4>
                                <p class="text-slate-500 text-sm mb-3">
                                    Satu klik untuk membereskan masalah umum:
                                </p>
                                <ul class="text-xs text-slate-500 space-y-1 grid grid-cols-1 md:grid-cols-2 gap-x-4">
                                    <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Bersihkan Cache & Log</li>
                                    <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Hapus Data Sampah (Orphan)</li>
                                    <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Perbaiki Jenjang Kelas</li>
                                    <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Rapikan Format Nama</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="shrink-0">
                            <form action="{{ route('settings.maintenance.magic-fix') }}" method="POST"
                                  data-confirm-delete="true"
                                  data-title="Jalankan Perbaikan?"
                                  data-message="Sistem akan mendiagnosa dan memperbaiki masalah secara otomatis."
                                  data-confirm-text="Ya, Jalankan!"
                                  data-confirm-color="#10b981"
                                  data-icon="question">
                                @csrf
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2 group">
                                    <span class="material-symbols-outlined group-hover:rotate-12 transition-transform">auto_fix</span>
                                    <span>Jalankan Magic Fix</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- CARD 2: FORCE RECALCULATE (Important) -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-amber-200 shadow-sm flex flex-col justify-between relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-3 opacity-10">
                             <span class="material-symbols-outlined text-6xl text-amber-500">calculate</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2 mb-2">
                                <span class="material-symbols-outlined text-amber-500">update</span> Hitung Ulang Nilai
                            </h4>
                            <p class="text-xs text-slate-500 mb-4">
                                Gunakan ini jika Anda baru saja mengubah <b>Bobot Nilai</b> atau Rumus, tapi nilai di Rapor belum berubah.
                            </p>
                        </div>
                        <form action="{{ route('settings.maintenance.force-calcs') }}" method="POST"
                              data-confirm-delete="true"
                              data-title="Hitung Ulang Total?"
                              data-message="Mulai perhitungan ulang nilai Rapor massal."
                              data-confirm-text="Ya, Hitung Ulang!"
                              data-confirm-color="#f59e0b"
                              data-icon="question">
                            @csrf
                            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-4 rounded-lg shadow transition-colors flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">refresh</span> HITUNG ULANG
                            </button>
                        </form>
                    </div>

                    <!-- CARD 3: RESET PROMOTION (Critical/Danger) -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-red-200 shadow-sm flex flex-col justify-between relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-3 opacity-10">
                             <span class="material-symbols-outlined text-6xl text-red-500">restart_alt</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2 mb-2">
                                <span class="material-symbols-outlined text-red-500">warning</span> Reset Kenaikan Kelas
                            </h4>
                            <p class="text-xs text-slate-500 mb-4">
                                <b>BAHAYA:</b> Membatalkan semua proses kenaikan kelas. Siswa akan dikembalikan ke kelas asal. Gunakan jika terjadi kesalahan fatal saat naik kelas.
                            </p>
                        </div>
                        <form action="{{ route('settings.maintenance.reset-promotion') }}" method="POST"
                              data-confirm-delete="true"
                              data-title="RESET Kenaikan Kelas?"
                              data-message="BAHAYA: Data kenaikan kelas akan DIHAPUS TOTAL. Siswa kembali ke kelas lama."
                              data-confirm-text="Ya, Reset Total!"
                              data-confirm-color="#ef4444"
                              data-icon="warning">
                            @csrf
                            <button type="submit" class="w-full bg-red-100 hover:bg-red-200 text-red-700 font-bold py-3 px-4 rounded-lg shadow-sm border border-red-300 transition-colors flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">history</span> RESET KENAIKAN
                            </button>
                        </form>
                    </div>



                    <!-- CARD 12: FACTORY RESET (DANGER ZONE) -->
                    <div class="bg-red-50 dark:bg-red-900/10 p-6 rounded-xl border-2 border-red-500 flex flex-col items-center text-center gap-4 mt-8 col-span-full">
                        <h4 class="font-bold text-2xl text-red-600 dark:text-red-400 flex items-center gap-2">
                            <span class="material-symbols-outlined text-3xl">dangerous</span> ZONA BAHAYA: RESET TOTAL
                        </h4>
                        <p class="text-slate-600 dark:text-slate-300 max-w-2xl">
                            Tindakan ini akan <b>MENGHAPUS SEMUA DATA</b> (Siswa, Guru, Kelas, Nilai, Absensi). <br>
                            Yang tersisa HANYA akun <b>Admin & Staff TU</b> serta Data Master (Mapel & Tahun Ajaran). <br>
                            Gunakan ini jika Anda ingin memulai sistem dari nol (Fresh Start).
                        </p>
                        <form action="{{ route('settings.maintenance.reset-system') }}" method="POST" onsubmit="return confirmReset(event)">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-8 rounded-xl shadow-lg transition-transform hover:scale-105 flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">delete_forever</span> RESET SISTEM DARI AWAL
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
    @endif
</div>

<script>
function confirmReset(e) {
    e.preventDefault(); // Stop form
    const form = e.target;

    Swal.fire({
        title: '⚠️ ZONA BAHAYA!',
        text: "Anda akan MENGHAPUS SEMUA DATA (Siswa, Guru, Nilai, Kelas). Admin & TU tidak dihapus. Tindakan ini TIDAK BISA DIBATALKAN.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus Semuanya!',
        cancelButtonText: 'Batal',
        background: '#fff',
        color: '#545454'
    }).then((result) => {
        if (result.isConfirmed) {
            // Second Level: Challenge
            Swal.fire({
                title: 'KONFIRMASI TERAKHIR',
                text: 'Ketik "RESET" (Huruf Besar) untuk mengeksekusi penghapusan massal.',
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'off',
                    placeholder: 'Ketik RESET disini...'
                },
                showCancelButton: true,
                confirmButtonText: 'LEDAKKAN 💣',
                confirmButtonColor: '#d33',
                showLoaderOnConfirm: true,
                preConfirm: (text) => {
                    if (text !== 'RESET') {
                        Swal.showValidationMessage('Kode salah! Ketik "RESET" dengan huruf besar.')
                    }
                    return text === 'RESET';
                },
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses Kiamat...',
                        text: 'Sistem sedang dibersihkan. Jangan tutup halaman ini.',
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    }).then(() => {
                        form.submit(); // Submit the form programmatically
                    });
                }
            });
        }
    })
    return false;
}
</script>

<!-- Modal Year -->
<div id="yearModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('yearModal').classList.add('hidden')"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white" id="modal-title">
                    Kelola Tahun Ajaran
                </h3>
                
                <div class="mt-4 flex flex-col gap-6">
                    <!-- Form New -->
                    <form action="{{ route('settings.year.store') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="nama" placeholder="Contoh: 2025/2026" class="flex-1 rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600 focus:ring-primary focus:border-primary" required>
                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg font-bold hover:bg-green-600 transition-all">Tambah</button>
                    </form>

                    <!-- List Archived -->
                    <div class="flex flex-col gap-2 max-h-60 overflow-y-auto">
                        <label class="text-xs font-bold uppercase text-slate-500">Arsip Tahun Ajaran</label>
                        @foreach($archivedYears as $year)
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200 dark:border-slate-700">
                             <span class="font-medium text-slate-700 dark:text-slate-300">{{ $year->nama }}</span>
                             <div class="flex gap-1">
                                <form action="{{ route('settings.year.toggle', $year->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs bg-slate-200 hover:bg-primary hover:text-white px-3 py-1 rounded transition-colors">Aktifkan</button>
                                </form>
                                 <form action="{{ route('settings.year.destroy', $year->id) }}" method="POST" 
                                      data-confirm-delete="true" 
                                      data-title="Hapus Tahun Ajaran?"
                                      data-message="Semua data kelas, nilai, dan absensi di tahun ini akan hilang PERMANEN.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded transition-colors" title="Hapus Tahun">Hapus</button>
                                </form>
                             </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('yearModal').classList.add('hidden')">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Whitelist -->
<dialog id="modalWhitelist" class="modal rounded-xl shadow-2xl p-0 backdrop:bg-slate-900/50 w-full max-w-md">
    <div class="bg-white dark:bg-slate-800 p-6">
        <h3 class="font-bold text-lg mb-4 text-slate-800 dark:text-white">Beri Akses Khusus</h3>
        <form action="{{ route('settings.deadline.whitelist.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 mb-1">Pilih Guru</label>
                <div class="relative">
                    <select name="id_guru" required class="w-full rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm focus:ring-primary appearance-none">
                        <option value="" disabled selected>-- Cari Nama Guru --</option>
                        @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-700 dark:text-slate-300">
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 mb-1">Periode Akses</label>
                <select name="id_periode" class="w-full rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm focus:ring-primary">
                    @foreach($periods as $p)
                    <option value="{{ $p->id }}">{{ $p->nama_periode }} ({{ $p->lingkup_jenjang }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6 grid grid-cols-2 gap-4">
                <div>
                     <label class="block text-xs font-bold text-slate-500 mb-1">Durasi Akses</label>
                     <select name="duration" class="w-full rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm focus:ring-primary">
                         <option value="1">1 Hari (24 Jam)</option>
                         <option value="3">3 Hari</option>
                         <option value="7">1 Minggu</option>
                     </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Alasan</label>
                    <input type="text" name="alasan" required placeholder="Contoh: Sakit..." class="w-full rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm focus:ring-primary">
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalWhitelist').close()" class="px-4 py-2 rounded-lg text-slate-500 text-sm font-bold hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition-colors">Berikan Akses</button>
            </div>
        </form>
    </div>
</dialog>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('settingsPage', () => ({
            activeTab: new URLSearchParams(window.location.search).get('tab') || 'grading',
            isLocked: {{ $isLocked ? 'true' : 'false' }},
            jenjang: '{{ $jenjang }}', // From Controller
            loading: false,

            saveRules() {
                this.loading = true;
                // Submit the form closest to the button
                this.$el.closest('form').submit();
            },
            
            init() {
                // Optional: Auto-scroll to error if any
                if (document.querySelector('.text-red-600')) {
                    // document.querySelector('.text-red-600').scrollIntoView();
                }
            }
        }))
    })
</script>
@endsection
