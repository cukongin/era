@extends('layouts.app')

@section('title', 'Smart Katrol Nilai')

@section('content')
<div class="flex flex-col gap-6" x-data="katrolSimulation()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Smart Katrol Nilai</h1>
            <p class="text-slate-500 dark:text-slate-400">Simulasi dan sesuaikan nilai siswa sebelum disimpan.</p>
        </div>

        <!-- Filter Context -->
        <form method="GET" class="flex flex-wrap gap-3 bg-white dark:bg-surface-dark p-2 rounded-lg border border-slate-200 dark:border-slate-800">
            <select name="kelas_id" class="text-sm border-none bg-transparent focus:ring-0 font-semibold text-slate-700 dark:text-white" onchange="this.form.submit()">
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $kelasId == $c->id ? 'selected' : '' }}>{{ $c->nama_kelas }}</option>
                @endforeach
            </select>
            <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 my-auto"></div>

            {{-- Period Selector --}}
            <select name="periode_id" class="text-sm border-none bg-transparent focus:ring-0 font-semibold text-slate-700 dark:text-white" onchange="this.form.submit()">
                @foreach($allPeriods as $p)
                    <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                        {{ $p->nama_periode }} {{ $p->status == 'aktif' ? '(Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
            <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 my-auto"></div>

            <select name="mapel_id" class="text-sm border-none bg-transparent focus:ring-0 font-semibold text-slate-700 dark:text-white" onchange="this.form.submit()">
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" {{ $mapelId == $s->id ? 'selected' : '' }}>{{ $s->nama_mapel }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- CONTROL PANEL (Left) -->
        <div class="lg:col-span-1 space-y-6">
            <form action="{{ route('walikelas.katrol.store') }}" method="POST" class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sticky top-6">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
                <input type="hidden" name="periode_id" value="{{ $selectedPeriodeId }}">

                <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-4 flex items-center justify-between">
                    <span class="flex items-center gap-2"><span class="material-symbols-outlined text-primary">tune</span> Konfigurasi</span>
                    <span class="text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 py-1 px-2 rounded-lg border border-slate-200 dark:border-slate-600 font-mono">
                        KKM: <b>{{ $currentKkm }}</b>
                    </span>
                </h3>

                <!-- Mode Selection -->
                <div class="space-y-3 mb-6">
                    <label class="text-sm font-medium text-slate-500">Pilih Metode Katrol</label>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="method_type_ui" value="kkm" class="peer sr-only" x-model="staging.mode">
                            <div class="text-center p-3 rounded-lg border border-slate-200 dark:border-slate-700 peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary transition-all hover:bg-slate-50">
                                <span class="material-symbols-outlined block mb-1">vertical_align_bottom</span>
                                <span class="text-xs font-bold">KKM</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="method_type_ui" value="points" class="peer sr-only" x-model="staging.mode">
                            <div class="text-center p-3 rounded-lg border border-slate-200 dark:border-slate-700 peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary transition-all hover:bg-slate-50">
                                <span class="material-symbols-outlined block mb-1">add</span>
                                <span class="text-xs font-bold">Poin</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="method_type_ui" value="linear_scale" class="peer sr-only" x-model="staging.mode">
                            <div class="text-center p-3 rounded-lg border border-slate-200 dark:border-slate-700 peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary transition-all hover:bg-slate-50">
                                <span class="material-symbols-outlined block mb-1">linear_scale</span>
                                <span class="text-xs font-bold">Interpolasi</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="method_type_ui" value="percentage" class="peer sr-only" x-model="staging.mode">
                            <div class="text-center p-3 rounded-lg border border-slate-200 dark:border-slate-700 peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary transition-all hover:bg-slate-50">
                                <span class="material-symbols-outlined block mb-1">percent</span>
                                <span class="text-xs font-bold">Persen</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Dynamic Controls -->
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 mb-6 border border-slate-100 dark:border-slate-700/50 min-h-[160px]">

                    <!-- KKM Mode -->
                    <div x-show="staging.mode === 'kkm'" x-transition>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Target Minimal (KKM)</label>
                         <p class="text-xs text-slate-500 mb-3">Tarik nilai < KKM menjadi sama dengan KKM.</p>
                        <div class="flex items-center gap-4">
                            <input type="range" class="w-full accent-primary" min="0" max="100" x-model.number="staging.kkmVal">
                            <span class="text-lg font-bold text-primary w-12 text-center" x-text="staging.kkmVal"></span>
                        </div>
                    </div>

                    <!-- Points Mode -->
                    <div x-show="staging.mode === 'points'" x-transition style="display: none;">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tambah Poin (+)</label>
                        <p class="text-xs text-slate-500 mb-3">Tambahkan poin ke semua siswa.</p>
                         <div class="flex items-center gap-4 mb-4">
                            <input type="range" class="w-full accent-primary" min="0" max="50" x-model.number="staging.boostPoints">
                            <span class="text-lg font-bold text-primary w-12 text-center" x-text="'+' + staging.boostPoints"></span>
                        </div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Batas Atas (Ceiling)</label>
                         <div class="flex items-center gap-4">
                            <input type="number" class="w-full border-slate-200 rounded-md text-sm" min="0" max="100" x-model.number="staging.maxCeiling">
                        </div>
                    </div>

                    <!-- Percent Mode -->
                    <div x-show="staging.mode === 'percentage'" x-transition style="display: none;">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Naikkan Persentase (%)</label>
                        <p class="text-xs text-slate-500 mb-3">Kalikan nilai dengan persentase.</p>
                         <div class="flex items-center gap-4">
                            <input type="range" class="w-full accent-primary" min="0" max="50" x-model.number="staging.boostPercent">
                            <span class="text-lg font-bold text-primary w-12 text-center" x-text="staging.boostPercent + '%'"></span>
                        </div>
                    </div>

                    <!-- Linear Scale Mode -->
                    <div x-show="staging.mode === 'linear_scale'" x-transition style="display: none;">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Range Target</label>
                        <p class="text-xs text-slate-500 mb-3">Petakan nilai terendah & tertinggi ke range baru.</p>

                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="text-xs text-slate-500">Min Target</label>
                                <input type="number" class="w-full border-slate-200 rounded-md text-sm font-bold text-center text-primary" min="0" max="100" x-model.number="staging.targetMin">
                            </div>
                            <div>
                                <label class="text-xs text-slate-500">Max Target</label>
                                <input type="number" class="w-full border-slate-200 rounded-md text-sm font-bold text-center text-primary" min="0" max="100" x-model.number="staging.targetMax">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                             <div>
                                <label class="text-xs text-slate-500">Data Min (Asli)</label>
                                <input type="number" class="w-full border-slate-200 rounded-md text-sm text-center bg-slate-100 text-slate-500 cursor-not-allowed" min="0" max="100" x-model.number="staging.dataMin" readonly>
                            </div>
                             <div>
                                <label class="text-xs text-slate-500">Data Max (Asli)</label>
                                <input type="number" class="w-full border-slate-200 rounded-md text-sm text-center bg-slate-100 text-slate-500 cursor-not-allowed" min="0" max="100" x-model.number="staging.dataMax" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actual Inputs for Form Submit (Bound to ACTIVE state) -->
                <!-- Actual Inputs for Form Submit (Bound to STAGING state to capture current inputs) -->
                <input type="hidden" name="method_type" :value="staging.mode">
                <input type="hidden" name="min_threshold" :value="staging.kkmVal">
                <input type="hidden" name="boost_points" :value="staging.boostPoints">
                <input type="hidden" name="max_ceiling" :value="staging.maxCeiling">
                <input type="hidden" name="boost_percent" :value="staging.boostPercent">
                <input type="hidden" name="target_min" :value="staging.targetMin">
                <input type="hidden" name="target_max" :value="staging.targetMax">
                <input type="hidden" name="data_min" :value="staging.dataMin">
                <input type="hidden" name="data_max" :value="staging.dataMax">

                <!-- Live Stats -->
                <div class="space-y-2 mb-6 text-sm">
                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                        <span>Rata-rata Awal:</span>
                        <span class="font-mono font-bold">{{ round($grades->avg('nilai_akhir_asli') ?? $grades->avg('nilai_akhir') ?? 0, 1) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-primary font-bold">
                        <span>Rata-rata Baru (Preview):</span>
                        <span class="font-mono" x-text="simAuth.avg">0</span>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="button" @click="applyPreview()" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-primary/20 transition-all flex justify-center items-center gap-2">
                        <span class="material-symbols-outlined">play_circle</span> Hitung / Preview
                    </button>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-primary rounded hover:bg-primary/90 transition-colors flex justify-center items-center gap-2">
                            <span class="material-symbols-outlined">save</span> Simpan Perubahan
                        </button>
                         <button type="submit" name="method_type" value="reset" class="w-auto bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3 px-3 rounded-xl transition-all" title="Reset ke Nilai Asli">
                             <span class="material-symbols-outlined">restart_alt</span>
                         </button>
                    </div>
                </div>
                <p class="text-xs text-slate-400 text-center mt-3">Klik Preview untuk melihat hasil sebelum menyimpan.</p>
            </form>
        </div>

        <!-- SIMULATION TABLE (Right) -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 dark:bg-[#20342a] text-slate-500 uppercase text-xs font-bold">
                            <tr>
                                <th class="p-4 w-10">No</th>
                                <th class="p-4">Nama Siswa</th>
                                <th class="p-4 text-center">Asli</th>
                                <th class="p-4 text-center">Baru</th>
                                <th class="p-4 text-center">Selisih</th>
                                <th class="p-4">Keterangan</th>
                                <th class="p-4 w-1/3">Visual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($grades as $index => $grade)
                            <tr class="hover:bg-slate-50 dark:hover:bg-[#1a2c24] transition-colors"
                                x-data="{
                                    original: {{ $grade->nilai_akhir_asli ?? $grade->nilai_akhir ?? 0 }},
                                    current: {{ $grade->nilai_akhir ?? 0 }},
                                    note: '{{ $grade->katrol_note ?? '' }}',
                                    get calculated() {
                                        return this.hasPreviewed ? this.calculateGrade(this.original) : this.current;
                                    },
                                    get diff() {
                                        return this.calculated - this.original;
                                    },
                                    get isModified() {
                                        return this.diff !== 0;
                                    }
                                }"
                            >
                                <td class="p-4 text-slate-500 text-sm">{{ $index+1 }}</td>
                                <td class="p-4 font-medium text-slate-900 dark:text-white">{{ $grade->siswa->nama_lengkap }}</td>

                                <!-- Nilai Asli: Yellow if < KKM -->
                                <td class="p-4 text-center font-mono"
                                    :class="original < {{ $currentKkm }} ? 'text-amber-500 font-bold' : 'text-slate-500'"
                                    x-text="original"></td>

                                <!-- Nilai Baru: Show Calculated (Preview) OR Saved (Current) -->
                                <td class="p-4 text-center font-mono font-bold text-lg">
                                    <template x-if="!isModified">
                                        <span class="text-slate-300">-</span>
                                    </template>
                                    <template x-if="isModified">
                                        <span :class="calculated < {{ $currentKkm }} ? 'text-amber-500' : (diff > 0 ? 'text-primary' : 'text-slate-700 dark:text-white')"
                                              x-text="calculated"></span>
                                    </template>
                                </td>

                                <!-- Selisih: Show if Modified -->
                                <td class="p-4 text-center">
                                    <template x-if="isModified">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary"
                                              x-text="diff > 0 ? '+' + diff : diff">
                                        </span>
                                    </template>
                                    <template x-if="!isModified">
                                        <span class="text-slate-300 text-xs">-</span>
                                    </template>
                                </td>

                                <!-- Keterangan: New Column -->
                                <td class="p-4 text-xs text-slate-500">
                                    <!-- Saved Note -->
                                    <template x-if="note && !hasPreviewed">
                                        <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded" x-text="note"></span>
                                    </template>
                                    <!-- Preview Note (Dynamic) -->
                                    <template x-if="hasPreviewed && isModified">
                                        <span class="bg-primary/10 text-primary px-2 py-1 rounded">Preview</span>
                                    </template>
                                    <template x-if="!isModified && !note">
                                        <span>-</span>
                                    </template>
                                </td>

                                <!-- Visual: Updated to use calculated (dynamic) -->
                                <td class="p-4 align-middle">
                                    <div class="h-2 w-full bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden relative">
                                        <!-- Original Marker -->
                                        <div class="absolute h-full"
                                             :class="original < {{ $currentKkm }} ? 'bg-amber-200 dark:bg-amber-900/50' : 'bg-slate-300 dark:bg-slate-500'"
                                             :style="`width: ${original}%`"></div>

                                        <!-- New Marker (Shows if modified) -->
                                        <div class="absolute h-full transition-all duration-300"
                                             :class="calculated < {{ $currentKkm }} ? 'bg-amber-500' : 'bg-primary/80'"
                                             :style="`width: ${calculated}%`"
                                             x-show="isModified"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ALPINE LOGIC -->
<script src="//unpkg.com/alpinejs" defer></script>
<script>
    function katrolSimulation() {
        return {
            // State Flags
            hasPreviewed: false,

            // Staging (Input State)
            staging: {
                mode: 'kkm',
                kkmVal: {{ $currentKkm }},
                boostPoints: 5,
                boostPercent: 10,
                maxCeiling: 100,
                targetMin: {{ $currentKkm }},
                targetMax: 95,
                dataMin: 0,
                dataMax: 100
            },

            // Active (Calculation State) - Initially synced
            active: {
                mode: 'kkm',
                kkmVal: {{ $currentKkm }},
                boostPoints: 5,
                boostPercent: 10,
                maxCeiling: 100,
                targetMin: {{ $currentKkm }},
                targetMax: 95,
                dataMin: 0,
                dataMax: 100
            },

            simAuth: { avg: 0, count: 0 },

            // Raw Grades for calculations
            rawGrades: @json($grades->map(fn($g) => (float) ($g->nilai_akhir_asli ?? $g->nilai_akhir ?? 0))),

            init() {
                // Determine initial stats
                if (this.rawGrades.length > 0) {
                    let min = Math.min(...this.rawGrades);
                    let max = Math.max(...this.rawGrades);

                    // Set both Staging and Active
                    this.staging.dataMin = min;
                    this.staging.dataMax = max;
                    this.active.dataMin = min;
                    this.active.dataMax = max;
                }

                // Initial calculation based on default
                this.updateStats();
            },

            // ACTION: PREVIEW
            applyPreview() {
                // Enable Preview Flag
                this.hasPreviewed = true;

                // Copy Staging to Active
                this.active = { ...this.staging };
                this.updateStats();

                // Optional: Show Toast or Feedback
            },

            // Compute Grade using ACTIVE config
            calculateGrade(original) {
                // If not previewed yet, show original
                if (!this.hasPreviewed) return parseFloat(original);

                let final = original;
                original = parseFloat(original);

                const cfg = this.active; // Use Active Config

                if (cfg.mode === 'kkm') {
                    if (original < cfg.kkmVal) final = cfg.kkmVal;

                } else if (cfg.mode === 'points') {
                    if (original < cfg.maxCeiling) {
                        final = Math.min(cfg.maxCeiling, original + cfg.boostPoints);
                    }

                } else if (cfg.mode === 'percentage') {
                    let factor = 1 + (cfg.boostPercent / 100);
                    final = Math.min(100, Math.round(original * factor));

                } else if (cfg.mode === 'linear_scale') {
                    if (cfg.dataMax === cfg.dataMin) {
                        final = cfg.targetMax;
                    } else {
                        let ratio = (original - cfg.dataMin) / (cfg.dataMax - cfg.dataMin);
                        let range = cfg.targetMax - cfg.targetMin;
                        final = cfg.targetMin + (ratio * range);
                        final = Math.min(100, Math.round(final));
                    }
                    // Prevent Downgrade: If calculated is lower than original, keep original
                    final = Math.max(final, original);
                }
                return isNaN(final) ? original : final;
            },

            // Recalculate Totals
            updateStats() {
                let total = 0;
                let count = 0;
                let changed = 0;

                this.rawGrades.forEach(g => {
                    let newG = this.calculateGrade(g);
                    total += newG;
                    count++;
                    if (newG !== g) changed++;
                });

                this.simAuth.avg = count > 0 ? (total / count).toFixed(1) : 0;
                this.simAuth.count = changed;
            }
        }
    }
</script>
@endsection

