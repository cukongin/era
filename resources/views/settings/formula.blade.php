@extends('layouts.app')

@section('title', 'Custom Formula Builder')

@section('content')
<div class="max-w-7xl mx-auto" x-data="formulaBuilder()">
    <!-- HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white">Custom Formula Builder</h1>
            <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400">Desain rumus penilaian sendiri dengan drag & drop variables.</p>
        </div>
        <a href="{{ route('settings.index') }}" class="w-full sm:w-auto text-center bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg transition">
            &larr; Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
        
        <!-- LEFT: NAVIGATION & LIST -->
        <div class="lg:col-span-1 space-y-6 order-2 lg:order-1">
            
            <!-- Context Tabs -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="p-4 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="font-bold text-slate-700 dark:text-slate-200">Kategori Rumus</h2>
                </div>
                <nav class="flex flex-col p-2 space-y-1">
                    <button @click="setContext('rapor_mi')" :class="context.includes('rapor') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:bg-slate-50'" class="text-left px-4 py-3 rounded-lg transition flex items-center justify-between">
                        <span>Rapor (MI/MTs)</span>
                        <span class="material-symbols-outlined text-sm">assignment</span>
                    </button>
                    <button @click="setContext('ijazah')" :class="context.includes('ijazah') ? 'bg-purple-50 text-purple-700 font-bold' : 'text-slate-600 hover:bg-slate-50'" class="text-left px-4 py-3 rounded-lg transition flex items-center justify-between">
                        <span>Ijazah / Kelulusan</span>
                        <span class="material-symbols-outlined text-sm">school</span>
                    </button>
                    <button @click="setContext('current_active')" :class="context === 'current_active' ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-600 hover:bg-slate-50'" class="text-left px-4 py-3 rounded-lg transition flex items-center justify-between">
                        <span>Current System Logic</span>
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full">Active</span>
                    </button>
                </nav>
            </div>

            <!-- Existing Formulas List (Filtered) -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden" x-show="context !== 'current_active'">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-700">Template Tersimpan</h3>
                </div>
                <div class="divide-y divide-slate-100 max-h-[60vh] overflow-y-auto">
                    @foreach($formulas as $f)
                    <div class="p-4 hover:bg-slate-50 transition cursor-pointer" 
                         x-show="(context.includes('rapor') && '{{ $f->context }}'.includes('rapor')) || (context.includes('ijazah') && '{{ $f->context }}'.includes('ijazah'))"
                         @click="loadFormula('{{ $f->formula }}', '{{ $f->name }}')">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-800 text-sm">{{ $f->name }}</span>
                            @if($f->is_active)
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] px-2 py-0.5 rounded-full font-bold">Default</span>
                            @endif
                        </div>
                        <div class="text-xs font-mono text-slate-500 bg-slate-100 px-2 py-1 rounded truncate">
                            {{ $f->formula }}
                        </div>
                        <div class="mt-2 flex items-center gap-2 justify-end">
                             <form action="{{ route('settings.formula.destroy', $f->id) }}" method="POST" onsubmit="return confirm('Hapus rumus ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 underline">Hapus</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- CURRENT ACTIVE INFO (Static) -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6" x-show="context === 'current_active'">
                <h3 class="font-bold text-slate-800 mb-4">Logika Sistem Saat Ini</h3>
                
                <div class="space-y-3">
                    
                    <!-- GROUP 1: RAPOR LOGIC -->
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden" x-data="{ open: true }">
                        <button @click="open = !open" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700/50 flex items-center justify-between hover:bg-slate-100 transition">
                            <span class="font-bold text-sm text-slate-700 dark:text-slate-300">LOGIKA RAPOR (MI & MTs)</span>
                            <span class="material-symbols-outlined text-slate-400 text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="open" class="p-4 space-y-4 bg-white dark:bg-slate-800">
                             <!-- 1. RAPOR MI -->
                            <div class="p-3 border border-indigo-100 rounded-lg shadow-sm bg-indigo-50/20">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700">RAPOR MI</span>
                                    <span class="text-xs text-slate-500">(Sistem Cawu)</span>
                                </div>
                                <div class="font-mono text-xs text-slate-600 bg-white p-2 rounded border border-indigo-50 break-all">
                                    ([Rata_PH] × {{ $bobotMI->bobot_harian }}%) + ([Nilai_PTS] × {{ $bobotMI->bobot_uts_cawu }}%) + ([Nilai_PAS] × {{ $bobotMI->bobot_uas }}%)
                                </div>
                            </div>
                            <!-- 2. RAPOR MTs -->
                            <div class="p-3 border border-purple-100 rounded-lg shadow-sm bg-purple-50/20">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700">RAPOR MTs</span>
                                    <span class="text-xs text-slate-500">(Sistem Semester)</span>
                                </div>
                                <div class="font-mono text-xs text-slate-600 bg-white p-2 rounded border border-purple-50 break-all">
                                    ([Rata_PH] × {{ $bobotMTS->bobot_harian }}%) + ([Nilai_PTS] × {{ $bobotMTS->bobot_uts_cawu }}%) + ([Nilai_PAS] × {{ $bobotMTS->bobot_uas }}%)
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GROUP 2: IJAZAH LOGIC -->
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden" x-data="{ open: false }">
                        <button @click="open = !open" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700/50 flex items-center justify-between hover:bg-slate-100 transition">
                            <span class="font-bold text-sm text-slate-700 dark:text-slate-300">LOGIKA IJAZAH (MI & MTs)</span>
                            <span class="material-symbols-outlined text-slate-400 text-sm transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="open" class="p-4 space-y-4 bg-white dark:bg-slate-800">
                             <!-- 3. IJAZAH MI -->
                            <div class="p-3 border border-emerald-100 rounded-lg shadow-sm bg-emerald-50/20">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">IJAZAH MI</span>
                                    <span class="text-xs text-slate-500">(Kelulusan)</span>
                                </div>
                                <div class="font-mono text-xs text-slate-600 bg-white p-2 rounded border border-emerald-50 break-all">
                                    ([Rata_Rapor_MI] × {{ $wIjazahRapor }}%) + ([Nilai_Ujian] × {{ $wIjazahUjian }}%)
                                </div>
                            </div>
                            <!-- 4. IJAZAH MTs -->
                            <div class="p-3 border border-emerald-100 rounded-lg shadow-sm bg-emerald-50/20">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">IJAZAH MTs</span>
                                    <span class="text-xs text-slate-500">(Kelulusan)</span>
                                </div>
                                <div class="font-mono text-xs text-slate-600 bg-white p-2 rounded border border-emerald-50 break-all">
                                    ([Rata_Rapor_MTS] × {{ $wIjazahRapor }}%) + ([Nilai_Ujian] × {{ $wIjazahUjian }}%)
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                
                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs text-center">
                     <strong>Status:</strong> Sistem mengikuti setting di atas. Gunakan tombol Reset di bawah jika ingin kembali ke Hardcode/Bawaan untuk kategori tertentu.
                </div>

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 border-t pt-4">
                    <!-- Reset Rapor MI -->
                    <form action="{{ route('settings.formula.restore') }}" method="POST">
                        @csrf <input type="hidden" name="context" value="rapor_mi">
                        <button type="submit" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold py-3 px-4 rounded border border-slate-300 transition flex items-center justify-center gap-2 active:bg-slate-300">
                            <span class="material-symbols-outlined text-sm text-slate-500">restart_alt</span>
                            Reset Rapor MI
                        </button>
                    </form>

                    <!-- Reset Rapor MTs -->
                     <form action="{{ route('settings.formula.restore') }}" method="POST">
                        @csrf <input type="hidden" name="context" value="rapor_mts">
                        <button type="submit" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold py-3 px-4 rounded border border-slate-300 transition flex items-center justify-center gap-2 active:bg-slate-300">
                            <span class="material-symbols-outlined text-sm text-slate-500">restart_alt</span>
                             Reset Rapor MTs
                        </button>
                    </form>

                    <!-- Reset Ijazah MI -->
                     <form action="{{ route('settings.formula.restore') }}" method="POST">
                        @csrf <input type="hidden" name="context" value="ijazah_mi">
                        <button type="submit" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold py-3 px-4 rounded border border-slate-300 transition flex items-center justify-center gap-2 active:bg-slate-300">
                            <span class="material-symbols-outlined text-sm text-slate-500">restart_alt</span>
                             Reset Ijazah MI
                        </button>
                    </form>

                    <!-- Reset Ijazah MTs -->
                     <form action="{{ route('settings.formula.restore') }}" method="POST">
                        @csrf <input type="hidden" name="context" value="ijazah_mts">
                        <button type="submit" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold py-3 px-4 rounded border border-slate-300 transition flex items-center justify-center gap-2 active:bg-slate-300">
                            <span class="material-symbols-outlined text-sm text-slate-500">restart_alt</span>
                             Reset Ijazah MTs
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <!-- RIGHT: EDITOR & SIMULATOR -->
        <div class="lg:col-span-2 space-y-6 order-1 lg:order-2" x-show="context !== 'current_active'">
            
            <!-- Editor Box -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
                <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-500">edit_note</span>
                    Editor: <span x-text="context.toUpperCase().replace('_', ' ')"></span>
                </h2>

                <!-- Variables Toolbar & Legend -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-bold text-slate-500 uppercase">Kamus Variabel (Tap)</p>
                        <span class="text-[10px] text-slate-400 sm:hidden">Geser &rarr;</span>
                    </div>
                    
                    <!-- Mobile: Horizontal Scroll, Desktop: Grid -->
                    <div class="flex overflow-x-auto pb-4 gap-2 sm:grid sm:grid-cols-2 sm:gap-3 sm:pb-0 snap-x">
                        <template x-for="v in variables" :key="v.code">
                            <button @click="insertVar(v.code)" class="shrink-0 w-64 sm:w-auto snap-center flex items-center gap-3 p-3 text-left border rounded-lg hover:bg-indigo-50 hover:border-indigo-300 transition group bg-slate-50 dark:bg-slate-700 dark:border-slate-600 shadow-sm active:scale-95 duration-100">
                                <div class="shrink-0 font-mono text-xs font-bold bg-indigo-100 text-indigo-700 px-2 py-1 rounded group-hover:bg-indigo-200" x-text="v.code"></div>
                                <div>
                                    <div class="text-sm font-bold text-slate-700 dark:text-slate-200" x-text="v.label"></div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight" x-text="v.desc"></div>
                                </div>
                            </button>
                        </template> 
                        <span x-show="variables.length === 0" class="text-gray-400 text-sm italic col-span-2 text-center w-full">Pilih kategori di atas dulu...</span>
                    </div>
                </div>

                <!-- Input Area -->
                <form action="{{ route('settings.formula.store') }}" method="POST" id="formulaForm">
                    @csrf
                    <input type="hidden" name="context" :value="context">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Nama Rumus</label>
                        <input type="text" x-model="name" name="name" class="w-full rounded-lg border-slate-300 dark:bg-slate-700 text-base sm:text-sm py-2 sm:py-2" placeholder="Contoh: Rumus Kustom 2024" required>
                    </div>

                    <div class="mb-4 relative">
                        <label class="block text-sm font-medium mb-1">Ekspresi Matematika</label>
                        <textarea x-model="formula" name="formula" rows="3" class="w-full rounded-lg border-slate-300 dark:bg-slate-700 font-mono text-base sm:text-lg p-4 focus:ring-2 focus:ring-indigo-500" placeholder="..."></textarea>
                        
                        <!-- Validation Badge -->
                        <div class="absolute bottom-4 right-4">
                            <span x-show="isValid" class="text-emerald-600 flex items-center gap-1 text-sm font-bold bg-emerald-50 px-2 py-1 rounded border border-emerald-200">
                                <span class="material-symbols-outlined text-sm">check_circle</span> Valid
                            </span>
                            <span x-show="!isValid && formula.length > 0" class="text-rose-600 flex items-center gap-1 text-sm font-bold bg-rose-50 px-2 py-1 rounded border border-rose-200">
                                <span class="material-symbols-outlined text-sm">error</span> Error
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-between items-center gap-4 sm:gap-0">
                        <button type="button" @click="checkSyntax()" class="w-full sm:w-auto text-sm text-indigo-600 hover:text-indigo-800 font-medium py-2">
                            <span class="material-symbols-outlined align-middle text-sm">science</span> Uji Simulasi
                        </button>
                        <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 sm:py-2 rounded-lg font-bold shadow-lg shadow-indigo-500/30 active:scale-95 transition">
                            Simpan Baru
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- SIMULATOR -->
            <div class="bg-slate-900 rounded-xl shadow-lg border border-slate-700 overflow-hidden" x-data="{ showSim: false }">
                 <button @click="showSim = !showSim" class="w-full p-4 sm:p-6 flex items-center justify-between text-left hover:bg-slate-800 transition">
                     <h2 class="font-bold text-lg text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-400">play_circle</span>
                        Simulasi Rumus
                    </h2>
                    <span class="material-symbols-outlined text-slate-400 transition-transform duration-300" :class="showSim ? 'rotate-180' : ''">expand_more</span>
                 </button>
                 
                <div x-show="showSim" x-collapse>
                    <div class="p-4 sm:p-6 border-t border-slate-800">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4">
                            <template x-for="v in variables" :key="v.code">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 mb-1" x-text="v.label"></label>
                                    <input type="number" x-model="inputs[v.code]" @input="simulate()" class="w-full bg-slate-700 border-slate-600 rounded text-white text-base px-2 py-2 sm:py-1 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                            </template>
                        </div>
                        <div class="flex flex-col sm:flex-row items-center justify-between border-t border-slate-700 pt-4 gap-2">
                            <span class="text-sm text-slate-400">Hasil Akhir:</span>
                            <span class="text-3xl font-mono font-bold text-emerald-400" x-text="result"></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    function formulaBuilder() {
        return {
            context: 'rapor_mi',
            variables: [],
            formula: '',
            name: '',
            isValid: true,
            inputs: {},
            result: '---',

            init() {
                this.setContext('rapor_mi');
            },

            setContext(ctx) {
                this.context = ctx;
                this.variables = this.getVars(ctx);
                // Reset inputs
                this.inputs = {};
                this.variables.forEach(v => this.inputs[v.code] = 0);
                this.result = '---';
            },

            getVars(ctx) {
                if (ctx.includes('rapor')) return [
                    { code: '[Rata_PH]', label: 'Nilai Harian', desc: 'Rata-rata dari seluruh Formatif/Harian' },
                    { code: '[Nilai_PTS]', label: 'Nilai UTS/Cawu', desc: 'Nilai Tengah Semester (MTs) atau Ujian Cawu (MI)' },
                    { code: '[Nilai_PAS]', label: 'Nilai PAS/PAT', desc: 'Nilai Akhir Semester/Tahun' },
                    { code: '[Kehadiran]', label: '% Kehadiran', desc: 'Persentase kehadiran siswa (0-100)' },
                    { code: '[Nilai_Sem_1]', label: 'Nilai Sem 1', desc: 'Nilai Akhir Rapor Semester 1 (Ganjil)' },
                    { code: '[Nilai_Sem_2]', label: 'Nilai Sem 2', desc: 'Nilai Akhir Rapor Semester 2 (Genap)' },
                    { code: '[Nilai_Cawu_1]', label: 'Nilai Cawu 1', desc: 'Khusus MI: Nilai Rapor Cawu 1' },
                    { code: '[Nilai_Cawu_2]', label: 'Nilai Cawu 2', desc: 'Khusus MI: Nilai Rapor Cawu 2' },
                    { code: '[Nilai_Cawu_3]', label: 'Nilai Cawu 3', desc: 'Khusus MI: Nilai Rapor Cawu 3' }
                ];
                if (ctx.includes('ijazah')) return [
                    { code: '[Rata_Rapor_MTS]', label: 'Rata Rapor MTs', desc: 'Rata-rata nilai rapor Kls 7, 8, 9' },
                    { code: '[Rata_Rapor_MI]', label: 'Rata Rapor MI', desc: 'Rata-rata nilai rapor Kls 4, 5, 6' },
                    { code: '[Nilai_Ujian]', label: 'Ujian Madrasah', desc: 'Nilai Murni Ujian Madrasah' }
                ];
                return [];
            },
            
            loadFormula(formula, name) {
                this.formula = formula;
                this.name = name; 
                this.checkSyntax();
            },

            insertVar(token) {
                this.formula += token + ' ';
            },

            simulate() {
                let expr = this.formula;
                this.variables.forEach(v => {
                    let val = this.inputs[v.code];
                    if (val === undefined || val === '') val = 0;
                    expr = expr.replaceAll(v.code, val);
                });

                // Support Indonesian decimal (Comma -> Dot)
                expr = expr.replaceAll(',', '.');

                try {
                    if (/[^0-9\.\+\-\*\/\(\)\s]/.test(expr)) {
                        this.result = 'Err (Char)';
                        this.isValid = false;
                        return;
                    }
                    this.result = eval(expr).toFixed(2);
                    this.isValid = true;
                } catch (e) {
                    this.result = 'Err (Eval)';
                    this.isValid = false;
                }
            },
            
            checkSyntax() {
                this.simulate();
                if(this.isValid) alert('Syntax Valid! Hasil simulasi: ' + this.result);
                else alert('Syntax Error! Periksa kembali rumus Anda.');
            }
        }
    }
</script>
@endsection
