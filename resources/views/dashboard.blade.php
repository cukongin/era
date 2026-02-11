@extends('layouts.app')

@section('title', 'Dashboard Administrator')

@section('content')
<div class="flex-1 flex flex-col h-full overflow-hidden relative">
    <!-- Top Header Desktop (User profile, etc) -->
    <header class="hidden lg:flex items-center justify-between px-8 py-5 bg-background-light dark:bg-[#1a2e22]">
        <div>
            <nav class="text-sm font-medium text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <span class="text-primary font-bold">Portal Administrator</span>
                        <span class="mx-2">/</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-slate-700 dark:text-slate-200">Ringkasan Sistem</span>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="flex items-center gap-4">
            <button class="relative p-2 rounded-full hover:bg-white dark:hover:bg-white/10 transition-colors text-text-secondary dark:text-slate-400">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute top-2 right-2 size-2 bg-red-500 rounded-full border border-white dark:border-background-dark"></span>
            </button>
            <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-700 mx-2"></div>
            <div class="flex items-center gap-3 pl-2">
                <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 border-2 border-white dark:border-slate-700 shadow-sm flex items-center justify-center bg-primary text-white uppercase font-bold text-lg">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-900 dark:text-white leading-none">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 mt-1">Administrator</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto px-4 lg:px-8 pb-10 scroll-smooth">
        <div class="max-w-7xl mx-auto flex flex-col gap-8 pt-6">

            <!-- Welcome Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="flex flex-col gap-2">
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Selamat Datang, {{ auth()->user()->name }} 👋</h2>
                    <p class="text-slate-500 dark:text-slate-400">
                        Pantau aktivitas akademik Madrasah MI & MTs secara real-time.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold border border-green-200 shadow-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">check_circle</span> Sistem Online
                    </span>
                    <span class="text-slate-400 text-sm font-medium">{{ date('d F Y') }}</span>
                </div>
            </div>

            <!-- Stats Cards Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Santri -->
                <div class="bg-white dark:bg-[#1a2e22] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden group hover:border-primary/30 transition-colors">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <span class="material-symbols-outlined text-6xl text-primary">groups</span>
                    </div>
                    <div class="flex flex-col gap-1 relative z-10">
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">Total Santri</p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['total_siswa'] }}</h3>
                        <div class="flex gap-3 mt-2 text-xs font-medium">
                            <span class="text-primary bg-primary/5 px-2 py-0.5 rounded-md border border-primary/10">MI: {{ $stats['siswa_mi'] }}</span>
                            <span class="text-primary bg-primary/5 px-2 py-0.5 rounded-md border border-primary/10">MTs: {{ $stats['siswa_mts'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Guru Aktif -->
                <div class="bg-white dark:bg-[#1a2e22] p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden group hover:border-primary/30 transition-colors">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <span class="material-symbols-outlined text-6xl text-primary">school</span>
                    </div>
                    <div class="flex flex-col gap-1 relative z-10">
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">Guru Aktif</p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['total_guru'] }}</h3>
                        <div class="flex gap-3 mt-2 text-xs font-medium">
                            <span class="text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">{{ $stats['total_kelas'] }} Kelas Wali</span>
                        </div>
                    </div>
                </div>

                <!-- Tahun Ajaran -->
                <div class="bg-gradient-to-br from-primary to-green-700 text-white p-6 rounded-2xl shadow-lg shadow-green-900/20 relative overflow-hidden col-span-1 md:col-span-2">
                    <div class="absolute -right-6 -bottom-6 opacity-20 transform rotate-12">
                        <span class="material-symbols-outlined text-9xl">calendar_month</span>
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div class="flex flex-col gap-1">
                            <p class="text-green-100 text-xs font-bold uppercase tracking-wider">Tahun Ajaran Aktif</p>
                            <h3 class="text-3xl font-bold">{{ $activeYear ? $activeYear->nama : 'Belum Ada' }}</h3>
                            <p class="mt-1 text-green-50 text-sm flex items-center gap-1">
                                <span class="size-2 bg-green-400 rounded-full animate-pulse"></span> Semester Berjalan
                            </p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-3 rounded-xl border border-white/20">
                            <span class="material-symbols-outlined text-2xl">event_available</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Status Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Status MI (Diniyah) -->
                <div class="flex flex-col gap-5">
                     <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-3">
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <span class="material-symbols-outlined text-primary">child_care</span>
                        </div>
                        Akademik MI (Diniyah)
                     </h3>
                     <div class="bg-white dark:bg-[#1a2e22] rounded-2xl p-8 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col gap-8">
                        @php
                            $activeCawu = (int) ($miStats['active_cawu'] ?? 1);
                            $cawuLabels = ['Cawu 1', 'Cawu 2', 'Cawu 3'];
                        @endphp

                        <!-- Timeline (Step Wizard) -->
                        <div class="relative">
                            <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 dark:bg-slate-700 -z-10 -translate-y-1/2 rounded-full"></div>

                            <!-- Active Line -->
                            <div class="absolute top-1/2 left-0 h-1 bg-primary -z-10 -translate-y-1/2 rounded-full transition-all duration-1000" style="width: {{ ($activeCawu - 1) * 50 }}%"></div>

                            <div class="flex justify-between w-full">
                                @foreach($cawuLabels as $index => $label)
                                    @php $step = $index + 1; @endphp
                                    <div class="flex flex-col items-center gap-3 bg-white dark:bg-[#1a2e22] px-2 relative z-10">
                                        @if($step < $activeCawu)
                                            <div class="size-10 rounded-full bg-primary text-white flex items-center justify-center shadow-lg shadow-primary/20 transition-transform hover:scale-110">
                                                <span class="material-symbols-outlined text-base">check</span>
                                            </div>
                                        @elseif($step == $activeCawu)
                                            <div class="size-10 rounded-full bg-white border-2 border-primary text-primary flex items-center justify-center shadow-lg ring-4 ring-primary/10 animate-pulse-slow">
                                                <span class="text-sm font-bold">{{ $step }}</span>
                                            </div>
                                        @else
                                            <div class="size-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center border border-slate-200">
                                                <span class="text-sm font-bold">{{ $step }}</span>
                                            </div>
                                        @endif
                                        <span class="text-xs font-bold uppercase tracking-wider {{ $step == $activeCawu ? 'text-primary' : 'text-slate-400' }}">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 gap-6">
                            <div class="p-5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 flex flex-col items-center text-center">
                                <span class="text-xs text-slate-500 font-bold uppercase tracking-wide mb-1">Kelas Dinilai</span>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">
                                    {{ $miStats['finalized_classes'] }} <span class="text-slate-400 text-sm font-normal">/ {{ $miStats['total_classes'] }}</span>
                                </p>
                            </div>
                            <div class="p-5 rounded-xl border flex flex-col items-center text-center {{ is_null($miStats['days_left']) ? 'bg-slate-50 border-slate-100' : ($miStats['days_left'] < 7 ? 'bg-red-50 border-red-100 text-red-700' : 'bg-primary/5 border-primary/10 text-primary') }}">
                                <span class="text-xs font-bold uppercase tracking-wide mb-1 {{ is_null($miStats['days_left']) ? 'text-slate-500' : ($miStats['days_left'] < 7 ? 'text-red-600' : 'text-primary') }}">Deadline Penilaian</span>
                                @if(is_null($miStats['days_left']))
                                     <p class="text-sm font-bold text-slate-400 mt-1">Belum Diset</p>
                                 @else
                                     <p class="text-2xl font-bold">{{ $miStats['days_left'] }} <span class="text-xs font-normal opacity-80">Hari Lagi</span></p>
                                 @endif
                            </div>
                        </div>
                     </div>
                </div>

                <!-- Status MTs -->
                <div class="flex flex-col gap-5">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-3">
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <span class="material-symbols-outlined text-primary">school</span>
                        </div>
                        Akademik MTs
                     </h3>
                     <div class="bg-white dark:bg-[#1a2e22] rounded-2xl p-8 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col justify-between h-full gap-6">
                        <div class="flex flex-col gap-4">
                             <!-- Semesters -->
                            <div class="relative overflow-hidden rounded-xl border {{ $mtsStats['active_semester'] == 1 ? 'border-primary shadow-lg shadow-primary/5 bg-primary/5' : 'border-slate-200 opacity-60' }} p-5 flex items-center justify-between transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="size-12 rounded-full {{ $mtsStats['active_semester'] == 1 ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center font-bold text-lg">1</div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white text-lg">Semester Ganjil</p>
                                        <p class="text-sm text-slate-500">{{ $mtsStats['active_semester'] == 1 ? 'Status: Sedang Berjalan' : ($mtsStats['active_semester'] > 1 ? 'Status: Selesai' : 'Status: Belum Mulai') }}</p>
                                    </div>
                                </div>
                                @if($mtsStats['active_semester'] == 1)
                                <div class="px-3 py-1 bg-white text-primary rounded-lg text-xs font-bold border border-primary/20 shadow-sm">AKTIF</div>
                                @endif
                            </div>

                            <div class="relative overflow-hidden rounded-xl border {{ $mtsStats['active_semester'] == 2 ? 'border-primary shadow-lg shadow-primary/5 bg-primary/5' : 'border-slate-200 opacity-60' }} p-5 flex items-center justify-between transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="size-12 rounded-full {{ $mtsStats['active_semester'] == 2 ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center font-bold text-lg">2</div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white text-lg">Semester Genap</p>
                                        <p class="text-sm text-slate-500">{{ $mtsStats['active_semester'] == 2 ? 'Status: Sedang Berjalan' : 'Status: Belum Mulai' }}</p>
                                    </div>
                                </div>
                                 @if($mtsStats['active_semester'] == 2)
                                <div class="px-3 py-1 bg-white text-primary rounded-lg text-xs font-bold border border-primary/20 shadow-sm">AKTIF</div>
                                @endif
                            </div>
                        </div>

                         <div class="mt-auto p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl text-center border-t border-slate-100 dark:border-slate-800">
                            <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mb-1">Estimasi Rapor</p>
                            <p class="text-xl font-bold text-slate-900 dark:text-white">
                                 @if($mtsStats['deadline'])
                                    {{ \Carbon\Carbon::parse($mtsStats['deadline'])->translatedFormat('d F Y') }}
                                 @else
                                    Belum ditentukan
                                 @endif
                            </p>
                         </div>
                     </div>
                </div>

            </div>

             <!-- Monitor Wali Kelas -->
             <div class="flex flex-col gap-4 pb-8" x-data="{
                showReminderModal: false,
                showBulkModal: false,
                selectedWaliId: '',
                selectedKelasName: '',
                reminderMessage: '',
                messageType: 'warning',

                // Bulk Data
                bulkClasses: {{ json_encode($ongoingClasses) }},
                bulkFilter: 'all', // all, MI, MTs
                bulkSelected: [],

                get filteredClasses() {
                    if (this.bulkFilter === 'all') return this.bulkClasses;
                    return this.bulkClasses.filter(c => c.jenjang === this.bulkFilter);
                },

                toggleAll() {
                    if (this.bulkSelected.length === this.filteredClasses.length) {
                        this.bulkSelected = [];
                    } else {
                        this.bulkSelected = this.filteredClasses.map(c => c.id);
                    }
                }
             }">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-3">
                        <div class="p-2 bg-primary/10 rounded-lg">
                            <span class="material-symbols-outlined text-primary">supervisor_account</span>
                        </div>
                        Monitoring Wali Kelas
                    </h3>
                    <button @click="showBulkModal = true" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-colors" title="Ingatkan Semua">
                        <span class="material-symbols-outlined text-[18px]">notifications_active</span> Ingatkan Semua
                    </button>
                </div>

                 <div class="bg-white dark:bg-[#1a2e22] rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col max-h-[500px]">
                    <div class="overflow-y-auto custom-scrollbar">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 uppercase text-xs font-bold text-slate-500">
                                <tr>
                                    <th class="px-6 py-4">Kelas</th>
                                    <th class="px-6 py-4">Wali Kelas</th>
                                    <th class="px-6 py-4 text-center">Deadline</th>
                                    <th class="px-6 py-4 text-center">Progres Nilai</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($ongoingClasses as $kelas)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                        {{ $kelas['nama_kelas'] }}
                                        <span class="ml-2 px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] font-normal">{{ $kelas['jenjang'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                        {{ $kelas['wali_kelas'] }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                         @if($kelas['deadline_diff'] <= 7 && $kelas['deadline_diff'] >= 0)
                                            <span class="text-orange-600 font-bold text-xs">{{ $kelas['deadline_diff'] }} Hari Lagi</span>
                                         @elseif($kelas['deadline_diff'] < 0)
                                            <span class="text-red-600 font-bold text-xs">Telat</span>
                                         @else
                                            <span class="text-green-600 font-bold text-xs">Aman</span>
                                         @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                         @if($kelas['status_nilai'] == 'Selesai')
                                            <span class="px-2.5 py-1 rounded-full bg-green-50 text-green-700 text-xs font-bold border border-green-100">
                                                Selesai
                                            </span>
                                         @elseif($kelas['status_nilai'] == 'Proses')
                                            <span class="px-2.5 py-1 rounded-full bg-yellow-50 text-yellow-700 text-xs font-bold border border-yellow-100">
                                                Proses ({{ $kelas['graded_mapel'] }}/{{ $kelas['total_mapel'] }})
                                            </span>
                                         @else
                                            <span class="px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-xs font-bold border border-red-100">
                                                Belum Mulai
                                            </span>
                                         @endif
                                    </td>
                                    <td class="px-6 py-4 text-center flex items-center justify-center gap-2">
                                        <a href="{{ route('classes.show', $kelas['id']) }}" class="size-8 rounded-lg bg-slate-100 hover:bg-primary hover:text-white flex items-center justify-center transition-colors" title="Lihat Kelas">
                                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        </a>

                                        @if($kelas['wali_id'])
                                        <button @click="showReminderModal = true; selectedWaliId = '{{ $kelas['wali_id'] }}'; selectedKelasName = '{{ $kelas['nama_kelas'] }}'"
                                            class="size-8 rounded-lg bg-slate-100 hover:bg-orange-500 hover:text-white flex items-center justify-center transition-colors" title="Ingatkan Wali Kelas">
                                            <span class="material-symbols-outlined text-[16px]">notifications_active</span>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="material-symbols-outlined text-4xl text-slate-300">inbox</span>
                                            <span>Belum ada kelas aktif saat ini.</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

             <!-- Monitor Wali Kelas -->
             <div class="flex flex-col gap-4 pb-8" x-data="{ showReminderModal: false, selectedWaliId: '', selectedKelasName: '', reminderMessage: '', messageType: 'warning' }">
                <!-- ... (Table Content Omitted for Brevity - Matches Prior State) ... -->

                <!-- Reminder Modal -->
                <div x-show="showReminderModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">

                    <div class="bg-white dark:bg-[#1a2e22] rounded-2xl shadow-xl w-full max-w-md p-6 m-4"
                         @click.away="showReminderModal = false"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-90"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-90">

                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Kirim Pesan</h3>
                        <p class="text-sm text-slate-500 mb-4">Kirim pesan untuk Wali Kelas <span x-text="selectedKelasName" class="font-bold text-primary"></span>.</p>

                        <form action="{{ route('dashboard.remind-wali') }}" method="POST">
                            @csrf
                            <input type="hidden" name="wali_id" :value="selectedWaliId">
                            <input type="hidden" name="kelas_name" :value="selectedKelasName">
                            <input type="hidden" name="type" :value="messageType">

                            <!-- Type Selection -->
                            <div class="flex gap-4 mb-4">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="type_option" value="warning" x-model="messageType" class="peer sr-only">
                                    <div class="p-3 rounded-xl border-2 border-slate-100 peer-checked:border-orange-500 peer-checked:bg-orange-50 text-center transition-all">
                                        <span class="material-symbols-outlined text-orange-500 block mb-1">warning</span>
                                        <span class="text-xs font-bold text-slate-600 peer-checked:text-orange-700">Peringatan</span>
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="type_option" value="info" x-model="messageType" class="peer sr-only">
                                    <div class="p-3 rounded-xl border-2 border-slate-100 peer-checked:border-teal-500 peer-checked:bg-teal-50 text-center transition-all">
                                        <span class="material-symbols-outlined text-teal-500 block mb-1">info</span>
                                        <span class="text-xs font-bold text-slate-600 peer-checked:text-teal-700">Info / Lainnya</span>
                                    </div>
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-500 mb-1">Isi Pesan</label>
                                <textarea name="message" x-model="reminderMessage" rows="3" class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary focus:border-primary" placeholder="Tulis pesan Anda di sini..."></textarea>
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showReminderModal = false" class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold text-sm transition-colors">Batal</button>
                                <button type="submit"
                                    :class="messageType == 'warning' ? 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/20' : 'bg-teal-600 hover:bg-teal-700 shadow-teal-500/20'"
                                    class="px-4 py-2 rounded-xl text-white font-bold text-sm shadow-lg transition-all flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">send</span> Kirim
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bulk Reminder Modal -->
                <div x-show="showBulkModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">

                    <div class="bg-white dark:bg-[#1a2e22] rounded-2xl shadow-xl w-full max-w-2xl p-6 m-4 max-h-[90vh] flex flex-col"
                         @click.away="showBulkModal = false">

                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Ingatkan Semua Wali Kelas</h3>
                        <p class="text-sm text-slate-500 mb-6">Kirim pesan masal ke wali kelas yang dipilih.</p>

                        <form action="{{ route('dashboard.remind-bulk') }}" method="POST" class="flex-1 flex flex-col overflow-hidden">
                            @csrf
                            <input type="hidden" name="type" :value="messageType">

                            <!-- Type Selection -->
                            <div class="flex gap-4 mb-6">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="type_option_bulk" value="warning" x-model="messageType" class="peer sr-only">
                                    <div class="p-3 rounded-xl border-2 border-slate-100 peer-checked:border-orange-500 peer-checked:bg-orange-50 text-center transition-all h-full flex flex-col justify-center items-center">
                                        <span class="material-symbols-outlined text-orange-500 block mb-1">warning</span>
                                        <span class="text-xs font-bold text-slate-600 peer-checked:text-orange-700">Peringatan</span>
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="type_option_bulk" value="info" x-model="messageType" class="peer sr-only">
                                    <div class="p-3 rounded-xl border-2 border-slate-100 peer-checked:border-teal-500 peer-checked:bg-teal-50 text-center transition-all h-full flex flex-col justify-center items-center">
                                        <span class="material-symbols-outlined text-teal-500 block mb-1">info</span>
                                        <span class="text-xs font-bold text-slate-600 peer-checked:text-teal-700">Info / Lainnya</span>
                                    </div>
                                </label>
                            </div>

                            <div class="flex gap-4 flex-1 min-h-0">
                                <!-- Filter & List -->
                                <div class="w-1/2 flex flex-col gap-2 border-r border-slate-100 pr-4">
                                    <div class="flex gap-2">
                                        <button type="button" @click="bulkFilter = 'all'" :class="bulkFilter == 'all' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600'" class="px-3 py-1 rounded-lg text-xs font-bold transition-colors">Semua</button>
                                        <button type="button" @click="bulkFilter = 'MI'" :class="bulkFilter == 'MI' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600'" class="px-3 py-1 rounded-lg text-xs font-bold transition-colors">MI</button>
                                        <button type="button" @click="bulkFilter = 'MTs'" :class="bulkFilter == 'MTs' ? 'bg-slate-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-3 py-1 rounded-lg text-xs font-bold transition-colors">MTs</button>
                                    </div>

                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs font-bold text-slate-500">Pilih Kelas:</span>
                                        <button type="button" @click="toggleAll()" class="text-xs font-bold text-primary hover:underline">Pilih Semua</button>
                                    </div>

                                    <div class="flex-1 overflow-y-auto border border-slate-100 rounded-xl p-2 bg-slate-50">
                                        <template x-for="kelas in filteredClasses" :key="kelas.id">
                                            <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-white transition-colors cursor-pointer">
                                                <input type="checkbox" name="class_ids[]" :value="kelas.id" x-model="bulkSelected" class="rounded border-slate-300 text-primary">
                                                <span class="text-xs font-bold" x-text="kelas.nama_kelas"></span>
                                                <span class="text-[10px] text-slate-400" x-text="kelas.wali_kelas"></span>
                                            </label>
                                        </template>
                                        <div x-show="filteredClasses.length === 0" class="text-center py-4 text-xs text-slate-400">Tidak ada kelas.</div>
                                    </div>
                                </div>

                                <!-- Message Input -->
                                <div class="w-1/2 flex flex-col">
                                    <label class="block text-xs font-bold text-slate-500 mb-2">Isi Pesan Broadcast</label>
                                    <textarea name="message" x-model="reminderMessage" class="flex-1 w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm focus:ring-primary focus:border-primary p-4" placeholder="Tulis pesan untuk semua wali kelas terpilih..."></textarea>
                                </div>
                            </div>

                            <div class="flex justify-between items-center mt-6 pt-4 border-t border-slate-100">
                                <span class="text-xs font-bold text-slate-500">Terpilih: <span x-text="bulkSelected.length" class="text-slate-900"></span> Kelas</span>
                                <div class="flex gap-2">
                                    <button type="button" @click="showBulkModal = false" class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold text-sm transition-colors">Batal</button>
                                    <button type="submit"
                                        :class="messageType == 'warning' ? 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/20' : 'bg-teal-600 hover:bg-teal-700 shadow-teal-500/20'"
                                        class="px-6 py-2 rounded-xl text-white font-bold text-sm shadow-lg transition-all flex items-center gap-2"
                                        :disabled="bulkSelected.length === 0">
                                        <span class="material-symbols-outlined text-[18px]">send</span> Kirim Broadcast
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
             </div>

        </div>
    </div>
</div>
@endsection

