@extends('layouts.app')

@section('title', 'Plotting Paket Mapel')

@section('content')
<div class="flex flex-col gap-6" x-data="{
    selectedJenjang: '',
    selectedTingkat: '',
    selectedMapels: [],
    targetClasses: [],
    loading: false,

    // Magic Copy State
    sourceJenjang: '',
    sourceTingkat: '',
    copying: false,

    async fetchExisting() {
        if (!this.selectedJenjang || !this.selectedTingkat) return;

        this.loading = true;
        try {
            let url = `{{ route('master.mapel.get-plotting-data') }}?id_jenjang=${this.selectedJenjang}&tingkat_kelas=${this.selectedTingkat}`;
            let res = await fetch(url);
            let data = await res.json();

            // NEW STRUCTURE: { activeMapelIds: [...], classes: [...] }
            this.selectedMapels = data.activeMapelIds.map(String);
            this.targetClasses = data.classes;
            if (this.targetClasses.length === 0) {
                 // Optional: Show warning or empty state
            }
        } catch(e) {
            console.error(e);
        } finally {
            this.loading = false;
        }
    },

    async copyData() {
        if(!this.sourceJenjang || !this.sourceTingkat) {
            alert('Pilih Jenjang & Tingkat sumber dulu!');
            return;
        }
        if(!confirm('Data mapel yang dipilih sekarang akan DIGANTI dengan data dari sumber. Lanjutkan?')) return;

        this.copying = true;
        try {
            let url = `{{ route('master.mapel.get-plotting-data') }}?id_jenjang=${this.sourceJenjang}&tingkat_kelas=${this.sourceTingkat}`;
            let res = await fetch(url);
            let data = await res.json();

            // Replace current selection
            if(data.activeMapelIds && data.activeMapelIds.length > 0) {
                this.selectedMapels = data.activeMapelIds.map(String);
                alert(`Berhasil menyalin ${data.activeMapelIds.length} mapel!`);
            } else {
                alert('Sumber data kosong (belum ada mapel di-plot).');
            }
        } catch(e) {
            alert('Gagal menyalin data.');
            console.error(e);
        } finally {
            this.copying = false;
        }
    },

    toggleAll(category) {
        let checkboxes = document.querySelectorAll(`input[data-category='${category}']`);
        // Check if all are currently checked
        let allChecked = Array.from(checkboxes).every(cb => this.selectedMapels.includes(cb.value));

        checkboxes.forEach(cb => {
            let val = cb.value;
            if (allChecked) {
                // Remove
                this.selectedMapels = this.selectedMapels.filter(id => id !== val);
            } else {
                // Add if not exists
                if (!this.selectedMapels.includes(val)) {
                    this.selectedMapels.push(val);
                }
            }
        });
    }
}">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('master.mapel.index') }}" class="hover:text-primary transition-colors">Data Mapel</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="text-slate-900 dark:text-white font-medium">Plotting Massal</span>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 relative">

        <!-- Loading Overlay -->
        <div x-show="loading" class="absolute inset-0 z-50 bg-white/50 dark:bg-black/50 flex items-center justify-center rounded-xl backdrop-blur-sm">
            <span class="material-symbols-outlined animate-spin text-primary text-4xl">autorenew</span>
        </div>

        <div class="mb-6 border-b border-slate-100 dark:border-slate-800 pb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Setting Paket Mapel</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                    Pilih Jenjang & Tingkat. Mapel yang dipilih akan diterapkan ke <b>SEMUA KELAS</b> di tingkat tersebut.
                </p>
            </div>
            <button type="button" onclick="openCloneModal()" class="flex items-center gap-2 px-4 py-2 bg-primary/5 text-primary border border-primary/10 rounded-lg hover:bg-primary/10 font-bold text-sm transition-colors">
                <span class="material-symbols-outlined text-[20px]">content_copy</span>
                Salin ke Kelas Lain
            </button>
        </div>

        @if(session('error'))
        <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 border border-red-100 flex items-center gap-2">
            <span class="material-symbols-outlined">error</span>
            {{ session('error') }}
        </div>
        @endif

        @if(session('success'))
        <div class="bg-green-50 text-green-600 p-4 rounded-lg mb-6 border border-green-100 flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('master.mapel.save-plotting') }}" method="POST">
            @csrf

            <!-- Step 1: Target -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-bold text-slate-900 dark:text-white mb-2">Jenjang Sekolah</label>
                    <select name="id_jenjang" x-model="selectedJenjang" @change="fetchExisting()" required class="w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="">-- Pilih Jenjang --</option>
                        @foreach($jenjangs as $j)
                        <option value="{{ $j->id }}">{{ $j->nama }} ({{ $j->kode }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-900 dark:text-white mb-2">Tingkat Kelas</label>
                    <select name="tingkat_kelas" x-model="selectedTingkat" @change="fetchExisting()" required class="w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="">-- Pilih Tingkat --</option>
                        <template x-if="selectedJenjang == 1"> <!-- MI -->
                            <optgroup label="Tingkat MI">
                                <option value="1">Kelas 1</option>
                                <option value="2">Kelas 2</option>
                                <option value="3">Kelas 3</option>
                                <option value="4">Kelas 4</option>
                                <option value="5">Kelas 5</option>
                                <option value="6">Kelas 6</option>
                            </optgroup>
                        </template>
                        <template x-if="selectedJenjang == 2"> <!-- MTS -->
                            <optgroup label="Tingkat MTS">
                                <option value="7">Kelas 7</option>
                                <option value="8">Kelas 8</option>
                                <option value="9">Kelas 9</option>
                            </optgroup>
                        </template>
                        <template x-if="selectedJenjang == 3"> <!-- MA -->
                           <optgroup label="Tingkat MA">
                               <option value="10">Kelas 10</option>
                               <option value="11">Kelas 11</option>
                               <option value="12">Kelas 12</option>
                           </optgroup>
                       </template>
                    </select>
                </div>
            </div>

            <!-- Magic Copy Button Moved to Header -->

            <!-- Clone Modal Moved to Bottom -->

            <!-- Step 2: Subject Selection -->
            <div class="space-y-6" x-show="selectedJenjang && selectedTingkat" x-transition>
                @foreach($mapels as $category => $list)
                <div class="border rounded-lg border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="bg-slate-50 dark:bg-slate-800/50 px-4 py-2 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                        <h3 class="font-bold text-slate-700 dark:text-slate-300">{{ $category }}</h3>
                        <button type="button" @click="toggleAll('{{ $category }}')" class="text-xs text-primary hover:underline font-medium cursor-pointer">
                            Pilih Semua
                        </button>
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 bg-white dark:bg-surface-dark">
                        @foreach($list as $mapel)
                        <label class="flex items-center gap-3 p-2 rounded-lg border border-slate-100 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800 cursor-pointer transition-colors">
                            <input type="checkbox" name="mapel_ids[]" value="{{ $mapel->id }}" x-model="selectedMapels" data-category="{{ $category }}" class="rounded border-slate-300 text-primary focus:ring-primary">
                            <div>
                                <div class="font-medium text-sm text-slate-800 dark:text-white">
                                    {{ $mapel->nama_mapel }}
                                    @if($mapel->nama_kitab)
                                        <div class="font-arabic text-primary text-xs mt-0.5">{{ $mapel->nama_kitab }}</div>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 mt-1">{{ $mapel->kode_mapel }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Empty State Helper -->
            <div x-show="!selectedJenjang || !selectedTingkat" class="text-center py-10 border-2 border-dashed border-slate-200 rounded-xl">
                 <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">touch_app</span>
                 <p class="text-slate-400 text-sm">Silakan pilih Jenjang & Tingkat Kelas terlebih dahulu.</p>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800" x-show="selectedJenjang && selectedTingkat">
                <a href="{{ route('master.mapel.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">Batal</a>
                <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-primary rounded-lg hover:bg-green-600 shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Simpan Perubahan
                </button>
            </div>
        </form>

        <!-- Clone Modal -->
            <div id="cloneModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closeCloneModal()"></div>
                    <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6 dark:bg-slate-800">
                        <form action="{{ route('master.mapel.copy-plotting') }}" method="POST" onsubmit="return confirm('Yakin ingin menyalin plotting mapel? Data di kelas target akan DIHAPUS dan Diganti dengan sumber.')">
                            @csrf
                            <div class="mb-4 text-center">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Salin Plotting Massal</h3>
                                <p class="text-sm text-slate-500">Salin pengaturan mapel dari satu kelas ke banyak kelas sekaligus.</p>
                            </div>

                            <!-- Source -->
                            <div class="bg-primary/5 dark:bg-primary/20 p-4 rounded-lg mb-4 border border-primary/10 dark:border-primary/50">
                                <h4 class="font-bold text-primary dark:text-primary text-xs uppercase mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px]">input</span> Sumber Data (Copy Dari)
                                </h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">Jenjang</label>
                                        <select name="source_jenjang" x-model="sourceJenjang" class="w-full text-sm rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                            <option value="">-- Pilih --</option>
                                            @foreach($jenjangs as $j)
                                            <option value="{{ $j->id }}">{{ $j->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">Tingkat</label>
                                        <select name="source_tingkat" x-model="sourceTingkat" class="w-full text-sm rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                            <option value="">-- Pilih --</option>
                                            <template x-if="sourceJenjang == 1">
                                                <optgroup label="Tingkat MI">
                                                    <option value="1">Kelas 1</option>
                                                    <option value="2">Kelas 2</option>
                                                    <option value="3">Kelas 3</option>
                                                    <option value="4">Kelas 4</option>
                                                    <option value="5">Kelas 5</option>
                                                    <option value="6">Kelas 6</option>
                                                </optgroup>
                                            </template>
                                            <template x-if="sourceJenjang == 2">
                                                <optgroup label="Tingkat MTS">
                                                    <option value="7">Kelas 7</option>
                                                    <option value="8">Kelas 8</option>
                                                    <option value="9">Kelas 9</option>
                                                </optgroup>
                                            </template>
                                            <template x-if="sourceJenjang == 3">
                                               <optgroup label="Tingkat MA">
                                                   <option value="10">Kelas 10</option>
                                                   <option value="11">Kelas 11</option>
                                                   <option value="12">Kelas 12</option>
                                               </optgroup>
                                           </template>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Target -->
                            <div class="bg-primary/5 dark:bg-primary/20 p-4 rounded-lg mb-6 border border-primary/10 dark:border-primary/50">
                                <h4 class="font-bold text-primary dark:text-primary text-xs uppercase mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px]">output</span> Target (Terapkan Ke)
                                </h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <!-- MI Targets -->
                                    <div class="space-y-1">
                                        <p class="text-xs font-bold text-slate-400 uppercase">MI</p>
                                        @for($i=1; $i<=6; $i++)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="targets[]" value="1-{{ $i }}" class="rounded text-primary focus:ring-primary">
                                            <span class="text-sm">Kelas {{ $i }}</span>
                                        </label>
                                        @endfor
                                    </div>
                                    <!-- MTS Targets -->
                                    <div class="space-y-1">
                                        <p class="text-xs font-bold text-slate-400 uppercase">MTS</p>
                                        @for($i=7; $i<=9; $i++)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="targets[]" value="2-{{ $i }}" class="rounded text-primary focus:ring-primary">
                                            <span class="text-sm">Kelas {{ $i }}</span>
                                        </label>
                                        @endfor
                                    </div>
                                    <!-- MA Targets -->
                                    <div class="space-y-1">
                                        <p class="text-xs font-bold text-slate-400 uppercase">MA</p>
                                        @for($i=10; $i<=12; $i++)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="targets[]" value="3-{{ $i }}" class="rounded text-primary focus:ring-primary">
                                            <span class="text-sm">Kelas {{ $i }}</span>
                                        </label>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeCloneModal()" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">Batal</button>
                                <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-primary rounded-lg hover:bg-primary/90 shadow-sm">
                                    Terapkan Plotting
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection

<script>
    function openCloneModal() {
        document.getElementById('cloneModal').classList.remove('hidden');
    }

    function closeCloneModal() {
        document.getElementById('cloneModal').classList.add('hidden');
    }
</script>

