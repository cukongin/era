@extends('layouts.app')

@section('title', 'Pengaturan Penilaian')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pengaturan Bobot & KKM</h1>
        <p class="text-slate-500 text-sm">Tahun Ajaran Aktif: {{ $activeYear->nama }}</p>
    </div>

    <!-- Alert Config Info -->
    <div class="bg-primary/5 dark:bg-primary/20 border border-primary/10 dark:border-primary/50 rounded-xl p-4 flex items-start gap-3">
        <span class="material-symbols-outlined text-primary dark:text-primary">info</span>
        <div>
            <h4 class="font-bold text-primary dark:text-primary text-sm">Penting</h4>
            <p class="text-sm text-primary/80 dark:text-primary/80 mt-1">
                Perubahan bobot penilaian akan mempengaruhi kalkulasi nilai akhir rapor secara otomatis.
                Pastikan konfigurasi bobot sudah benar sebelum guru memulai input nilai.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8">

        <!-- 1. Pengaturan Periode (Akses Input) -->
        <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary text-sm">1</span>
                        Periode Input Nilai
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">Buka kunci periode agar Guru bisa memulai input nilai rapor.</p>
                </div>
                <!-- Legend -->
                <div class="flex gap-4 text-xs">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-primary"></span> Aktif (Bisa Input)</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-300"></span> Tutup (Read-only)</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                 @foreach($periods as $periode)
                 <div class="flex items-center justify-between p-4 rounded-xl border transition-all {{ $periode->status == 'aktif' ? 'border-primary bg-primary/5 shadow-sm' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/30' }}">
                     <div>
                         <span class="text-xs font-bold uppercase tracking-wider {{ $periode->lingkup_jenjang == 'MI' ? 'text-secondary' : 'text-primary' }} mb-1 block">{{ $periode->lingkup_jenjang }}</span>
                         <p class="font-bold text-slate-900 dark:text-white text-base">{{ $periode->nama_periode }}</p>
                     </div>
                     <form action="{{ route('settings.grading.period', $periode->id) }}" method="POST">
                         @csrf
                         <button type="submit" class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 {{ $periode->status == 'aktif' ? 'bg-primary' : 'bg-slate-300 dark:bg-slate-600' }}" role="switch" aria-checked="{{ $periode->status == 'aktif' }}">
                            <span aria-hidden="true" class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $periode->status == 'aktif' ? 'translate-x-5' : 'translate-x-0' }}"></span>
                         </button>
                     </form>
                 </div>
                 @endforeach
            </div>
        </div>

        <!-- 2. Pengaturan Bobot Nilai -->
        <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                     <h3 class="font-bold text-lg text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary text-sm">2</span>
                        Rumus Bobot Penilaian
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">Konfigurasi persentase nilai Harian vs Ujian untuk kalkulasi otomatis Nilai Akhir.</p>
                </div>
                
                <!-- Tabs -->
                <nav class="flex space-x-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-lg">
                    <button onclick="switchTab('mi')" id="tab-mi" class="bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm px-4 py-2 rounded-md text-sm font-bold transition-all">
                        Jenjang MI (Cawu)
                    </button>
                    <button onclick="switchTab('mts')" id="tab-mts" class="text-slate-500 hover:text-slate-900 px-4 py-2 rounded-md text-sm font-medium transition-all">
                        Jenjang MTs (Semester)
                    </button>
                </nav>
            </div>

            <!-- MI Form -->
            <div id="content-mi">
                <form action="{{ route('settings.grading.weights') }}" method="POST"
                      x-data="{
                          harianActive: {{ $bobotMI->bobot_harian > 0 ? 'true' : 'false' }},
                          utsActive: {{ $bobotMI->bobot_uts_cawu > 0 ? 'true' : 'false' }},
                          toggleHarian() {
                              this.harianActive = !this.harianActive;
                              if(this.harianActive && document.getElementById('mi_harian_input').value == 0) document.getElementById('mi_harian_input').value = 50;
                              if(!this.harianActive) document.getElementById('mi_harian_input').value = 0;
                          },
                          toggleUts() {
                              this.utsActive = !this.utsActive;
                              if(this.utsActive && document.getElementById('mi_uts_input').value == 0) document.getElementById('mi_uts_input').value = 50;
                              if(!this.utsActive) document.getElementById('mi_uts_input').value = 0;
                          }
                      }">
                    @csrf
                    <input type="hidden" name="jenjang" value="MI">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-2xl mx-auto">
                        <!-- Harian Component -->
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary transition-colors cursor-pointer" @click="toggleHarian()">
                            <div class="flex items-center justify-between mb-3">
                                <label class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2 cursor-pointer">
                                    <div class="w-5 h-5 rounded border flex items-center justify-center transition-colors"
                                         :class="harianActive ? 'bg-primary border-primary text-white' : 'bg-white border-slate-300'">
                                        <span class="material-symbols-outlined text-sm" x-show="harianActive">check</span>
                                    </div>
                                    Nilai Harian
                                </label>
                                <span class="text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded" x-show="!harianActive">OFF</span>
                                <span class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded font-bold" x-show="harianActive">ON</span>
                            </div>
                            <div x-show="harianActive" x-transition @click.stop>
                                <div class="flex items-center gap-2">
                                    <input type="number" id="mi_harian_input" name="bobot_harian" value="{{ $bobotMI->bobot_harian }}" min="0" max="100" class="text-center font-bold w-20 rounded-lg border-slate-300 focus:ring-primary focus:border-primary p-2 text-primary">
                                    <span class="font-bold text-slate-400">%</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-2">Masukkan persentase bobot Harian.</p>
                            </div>
                            <div x-show="!harianActive">
                                <p class="text-xs text-slate-400 italic">Komponen ini tidak dihitung dalam Rapor.</p>
                                <input type="hidden" name="bobot_harian" value="0">
                            </div>
                        </div>

                        <!-- Ujian Component -->
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary transition-colors cursor-pointer" @click="toggleUts()">
                            <div class="flex items-center justify-between mb-3">
                                <label class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2 cursor-pointer">
                                     <div class="w-5 h-5 rounded border flex items-center justify-center transition-colors"
                                         :class="utsActive ? 'bg-primary border-primary text-white' : 'bg-white border-slate-300'">
                                        <span class="material-symbols-outlined text-sm" x-show="utsActive">check</span>
                                    </div>
                                    Nilai Ujian (Cawu)
                                </label>
                                <span class="text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded" x-show="!utsActive">OFF</span>
                                <span class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded font-bold" x-show="utsActive">ON</span>
                            </div>
                             <div x-show="utsActive" x-transition @click.stop>
                                <div class="flex items-center gap-2">
                                    <input type="number" id="mi_uts_input" name="bobot_uts_cawu" value="{{ $bobotMI->bobot_uts_cawu }}" min="0" max="100" class="text-center font-bold w-20 rounded-lg border-slate-300 focus:ring-primary focus:border-primary p-2 text-primary">
                                    <span class="font-bold text-slate-400">%</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-2">Masukkan persentase bobot Ujian.</p>
                            </div>
                            <div x-show="!utsActive">
                                <p class="text-xs text-slate-400 italic">Komponen ini tidak dihitung dalam Rapor.</p>
                                <input type="hidden" name="bobot_uts_cawu" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-center">
                        <button type="submit" class="bg-primary text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-primary/30 hover:bg-green-600 transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined">save</span> Simpan Konfigurasi MI
                        </button>
                    </div>
                </form>
            </div>

            <!-- MTs Form -->
            <div id="content-mts" class="hidden">
                 <form action="{{ route('settings.grading.weights') }}" method="POST"
                      x-data="{
                          harianActive: {{ $bobotMTS->bobot_harian > 0 ? 'true' : 'false' }},
                          utsActive: {{ $bobotMTS->bobot_uts_cawu > 0 ? 'true' : 'false' }},
                          uasActive: {{ $bobotMTS->bobot_uas > 0 ? 'true' : 'false' }}
                      }">
                    @csrf
                    <input type="hidden" name="jenjang" value="MTS">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">

                        <!-- Harian -->
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary transition-colors" >
                            <div class="flex items-center justify-between mb-3 cursor-pointer" @click="harianActive = !harianActive">
                                <label class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2 cursor-pointer">
                                    <div class="w-5 h-5 rounded border flex items-center justify-center transition-colors"
                                         :class="harianActive ? 'bg-primary border-primary text-white' : 'bg-white border-slate-300'">
                                        <span class="material-symbols-outlined text-sm" x-show="harianActive">check</span>
                                    </div>
                                    Harian (PH)
                                </label>
                                <span class="text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded" x-show="!harianActive">OFF</span>
                                <span class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded font-bold" x-show="harianActive">ON</span>
                            </div>
                            <div x-show="harianActive" x-transition>
                                 <div class="flex items-center gap-2">
                                    <input type="number" name="bobot_harian" value="{{ $bobotMTS->bobot_harian > 0 ? $bobotMTS->bobot_harian : 30 }}" min="0" max="100" class="text-center font-bold w-20 rounded-lg border-slate-300 focus:ring-primary focus:border-primary p-2 text-primary">
                                    <span class="font-bold text-slate-400">%</span>
                                </div>
                            </div>
                             <div x-show="!harianActive">
                                <input type="hidden" name="bobot_harian" value="0" :disabled="harianActive">
                                <p class="text-xs text-slate-400 italic">Tidak digunakan.</p>
                            </div>
                        </div>

                        <!-- UTS/PTS -->
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary transition-colors">
                            <div class="flex items-center justify-between mb-3 cursor-pointer" @click="utsActive = !utsActive">
                                <label class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2 cursor-pointer">
                                     <div class="w-5 h-5 rounded border flex items-center justify-center transition-colors"
                                         :class="utsActive ? 'bg-primary border-primary text-white' : 'bg-white border-slate-300'">
                                        <span class="material-symbols-outlined text-sm" x-show="utsActive">check</span>
                                    </div>
                                    PTS ( Tengah Semester)
                                </label>
                                <span class="text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded" x-show="!utsActive">OFF</span>
                                <span class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded font-bold" x-show="utsActive">ON</span>
                            </div>
                             <div x-show="utsActive" x-transition>
                                 <div class="flex items-center gap-2">
                                    <input type="number" name="bobot_uts_cawu" value="{{ $bobotMTS->bobot_uts_cawu > 0 ? $bobotMTS->bobot_uts_cawu : 30 }}" min="0" max="100" class="text-center font-bold w-20 rounded-lg border-slate-300 focus:ring-primary focus:border-primary p-2 text-primary">
                                    <span class="font-bold text-slate-400">%</span>
                                </div>
                            </div>
                             <div x-show="!utsActive">
                                <input type="hidden" name="bobot_uts_cawu" value="0" :disabled="utsActive">
                                <p class="text-xs text-slate-400 italic">Tidak digunakan.</p>
                            </div>
                        </div>

                         <!-- UAS/PAS -->
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary transition-colors">
                            <div class="flex items-center justify-between mb-3 cursor-pointer" @click="uasActive = !uasActive">
                                <label class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2 cursor-pointer">
                                     <div class="w-5 h-5 rounded border flex items-center justify-center transition-colors"
                                         :class="uasActive ? 'bg-primary border-primary text-white' : 'bg-white border-slate-300'">
                                        <span class="material-symbols-outlined text-sm" x-show="uasActive">check</span>
                                    </div>
                                    PAS (Akhir Semester)
                                </label>
                                <span class="text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded" x-show="!uasActive">OFF</span>
                                <span class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded font-bold" x-show="uasActive">ON</span>
                            </div>
                             <div x-show="uasActive" x-transition>
                                 <div class="flex items-center gap-2">
                                    <input type="number" name="bobot_uas" value="{{ $bobotMTS->bobot_uas > 0 ? $bobotMTS->bobot_uas : 40 }}" min="0" max="100" class="text-center font-bold w-20 rounded-lg border-slate-300 focus:ring-primary focus:border-primary p-2 text-primary">
                                    <span class="font-bold text-slate-400">%</span>
                                </div>
                            </div>
                             <div x-show="!uasActive">
                                <input type="hidden" name="bobot_uas" value="0" :disabled="uasActive">
                                <p class="text-xs text-slate-400 italic">Tidak digunakan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-center">
                        <button type="submit" class="bg-primary text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-primary/30 hover:bg-green-600 transition-all flex items-center gap-2">
                             <span class="material-symbols-outlined">save</span> Simpan Konfigurasi MTs
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. Batas KKM -->
        <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                     <h3 class="font-bold text-lg text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary text-sm">3</span>
                        Target KKM Mata Pelajaran
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">Siswa dikatakan <strong>TUNTAS</strong> jika Nilai Akhir >= KKM.</p>
                </div>
                <button form="kkmForm" type="submit" class="bg-slate-800 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-700 shadow-lg shadow-slate-800/20 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">save_as</span> Simpan Perubahan KKM
                </button>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <form id="kkmForm" action="{{ route('settings.grading.kkm') }}" method="POST">
                    @csrf
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white dark:bg-slate-800 uppercase text-xs font-bold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-6 py-4">Mata Pelajaran</th>
                                <th class="px-6 py-4 w-40 text-center bg-secondary/10 dark:bg-secondary/20 text-secondary dark:text-secondary">KKM MI</th>
                                <th class="px-6 py-4 w-40 text-center bg-primary/10 dark:bg-primary/5 text-primary dark:text-primary">KKM MTs</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($mapels as $mapel)
                            <tr class="hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $mapel->nama_mapel }}
                                    <span class="text-xs font-normal text-slate-500 block">{{ $mapel->kode_mapel }}</span>
                                </td>
                                <!-- KKM MI Input -->
                                <td class="px-6 py-3 text-center bg-secondary/10 dark:bg-secondary/20">
                                    @if($mapel->target_jenjang == 'MI' || $mapel->target_jenjang == 'SEMUA')
                                        <div class="relative">
                                            <input type="number" name="kkm[{{ $mapel->id }}][MI]" value="{{ $kkms[$mapel->id.'-MI']->nilai_kkm ?? 70 }}" class="w-24 text-center font-bold text-secondary rounded-lg border-slate-300 focus:border-secondary focus:ring-secondary dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                                        </div>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <!-- KKM MTs Input -->
                                <td class="px-6 py-3 text-center bg-primary/5 dark:bg-primary/5">
                                     @if($mapel->target_jenjang == 'MTS' || $mapel->target_jenjang == 'SEMUA')
                                        <div class="relative">
                                            <input type="number" name="kkm[{{ $mapel->id }}][MTS]" value="{{ $kkms[$mapel->id.'-MTS']->nilai_kkm ?? 75 }}" class="w-24 text-center font-bold text-primary rounded-lg border-slate-300 focus:border-primary focus:ring-primary dark:bg-slate-800 dark:border-slate-600 dark:text-white">
                                        </div>
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
</div>

<script>
    function switchTab(tab) {
        if (tab === 'mi') {
            document.getElementById('content-mi').classList.remove('hidden');
            document.getElementById('content-mts').classList.add('hidden');

            // Activate MI Tab
            document.getElementById('tab-mi').classList.remove('text-slate-500', 'hover:text-slate-900');
            document.getElementById('tab-mi').classList.add('bg-white', 'dark:bg-slate-700', 'text-slate-900', 'dark:text-white', 'shadow-sm', 'font-bold');

            // Deactivate MTs Tab
            document.getElementById('tab-mts').classList.add('text-slate-500', 'hover:text-slate-900');
            document.getElementById('tab-mts').classList.remove('bg-white', 'dark:bg-slate-700', 'text-slate-900', 'dark:text-white', 'shadow-sm', 'font-bold');
        } else {
            document.getElementById('content-mi').classList.add('hidden');
            document.getElementById('content-mts').classList.remove('hidden');

            // Activate MTs Tab
            document.getElementById('tab-mts').classList.remove('text-slate-500', 'hover:text-slate-900');
            document.getElementById('tab-mts').classList.add('bg-white', 'dark:bg-slate-700', 'text-slate-900', 'dark:text-white', 'shadow-sm', 'font-bold');

            // Deactivate MI Tab
            document.getElementById('tab-mi').classList.add('text-slate-500', 'hover:text-slate-900');
            document.getElementById('tab-mi').classList.remove('bg-white', 'dark:bg-slate-700', 'text-slate-900', 'dark:text-white', 'shadow-sm', 'font-bold');
        }
    }
</script>
@endsection

