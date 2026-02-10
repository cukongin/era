@extends('layouts.app')

@section('title', $pageContext['title'])

@section('content')
<div class="space-y-6" x-data="promotionPage()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                @if($pageContext['type'] == 'graduation')
                    <span class="material-symbols-outlined text-indigo-600">school</span>
                @endif
                {{ $pageContext['title'] }}
            </h1>
            <p class="text-sm text-slate-500">
                Sistem otomatis menghitung rekomendasi {{ strtolower($pageContext['title']) }} berdasarkan aturan penilaian.
                <br>Kelas: <span class="font-bold text-indigo-600">{{ $kelas->nama_kelas }}</span>
                @if(isset($isLocked) && $isLocked)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        <span class="material-symbols-outlined text-[14px] mr-1">lock</span> Mode Baca
                    </span>
                @endif
            </p>
        </div>
        
        <div class="flex gap-2">
            @php
                $allDecisionsLocked = collect($studentStats)->every(fn($s) => $s->is_locked);
                $isUserAdmin = auth()->user()->isAdmin() || auth()->user()->isTu();
            @endphp

            @if(isset($isLocked) && $isLocked)
                <div class="bg-amber-100 text-amber-800 px-4 py-2 rounded-lg font-bold flex items-center gap-2 border border-amber-200 cursor-not-allowed opacity-75 select-none" title="Periode ini terkunci">
                    <span class="material-symbols-outlined">lock</span> Terkunci (Periode)
                </div>
            @elseif($allDecisionsLocked && !$isUserAdmin)
                <div class="bg-slate-100 text-slate-500 px-4 py-2 rounded-lg font-bold flex items-center gap-2 border border-slate-200 cursor-not-allowed select-none" title="Keputusan sudah final">
                    <span class="material-symbols-outlined">verified_user</span> Keputusan Final
                </div>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Santri -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Santri</p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $summary['total'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined">groups</span>
            </div>
        </div>

        <!-- Siap Naik/Lulus -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-slate-500">Siap {{ $pageContext['success_label'] }}</p>
                <h3 class="text-3xl font-bold text-emerald-600 mt-1">{{ $summary['promote'] }}</h3>
                <p class="text-xs text-emerald-500 mt-1">Memenuhi syarat</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
        </div>

        <!-- Perlu Peninjauan/Tidak Naik -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-slate-500">Perlu Peninjauan / {{ $isFinalYear ? 'Tidak Lulus' : 'Tinggal' }}</p>
                <h3 class="text-3xl font-bold text-amber-500 mt-1">{{ $summary['review'] + $summary['retain'] }}</h3>
                <p class="text-xs text-amber-500 mt-1">Tidak memenuhi syarat</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined">warning</span>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 relative">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h2 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">table_chart</span>
                Daftar Rekomendasi {{ $pageContext['title'] }}
            </h2>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                <input type="text" x-model="search" placeholder="Cari nama santri..." class="pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-64">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 uppercase text-xs font-bold">
                    <tr>
                        @if(isset($isFinalPeriod) && $isFinalPeriod)
                        <th class="p-4 w-4">
                            <input type="checkbox" @change="toggleAll($event)" class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary">
                        </th>
                        @endif
                        <th class="px-6 py-4">Nama Santri</th>
                        <th class="px-6 py-4 text-center">Rata-Rata<br>Tahun</th>
                        <th class="px-6 py-4 text-center">Mapel<br>< KKM</th>
                        <th class="px-6 py-4 text-center">Nilai<br>Sikap</th>
                        <th class="px-6 py-4 text-center">Kehadiran<br>(%)</th>
                        <th class="px-6 py-4 text-center">Rekomendasi<br>Sistem</th>
                        <th class="px-6 py-4 text-left w-64">Catatan<br>Sistem</th>
                        @if(isset($isFinalPeriod) && $isFinalPeriod)
                        <th class="px-6 py-4 text-right w-48">Status Akhir</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($studentStats as $index => $stat)
                    <tr data-name="{{ strtolower($stat->student->nama_lengkap) }}" 
                        x-show="matchesSearch($el.dataset.name)" 
                        class="hover:bg-slate-50 transition-colors group">
                        
                        @if(isset($isFinalPeriod) && $isFinalPeriod)
                        <td class="w-4 p-4 text-center">
                            <input type="checkbox" value="{{ $stat->student->id }}" x-model="selectedIds" class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary">
                        </td>
                        @endif

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $stat->student->nama_lengkap }}</div>
                                    <div class="text-xs text-slate-500">NIS: {{ $stat->student->nis_lokal ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-slate-700">{{ $stat->avg_yearly }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($stat->under_kkm > 0)
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">{{ $stat->under_kkm }} Mapel</span>
                            @else
                                <span class="text-slate-400">0</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center font-bold {{ $stat->attitude == 'A' ? 'text-emerald-600' : ($stat->attitude == 'C' ? 'text-red-600' : 'text-slate-700') }}">
                            {{ $stat->attitude }}
                        </td>
                        <td class="px-6 py-4 text-center {{ $stat->attendance_pct < 85 ? 'text-red-600 font-bold' : 'text-slate-700' }}">
                            {{ $stat->attendance_pct }}%
                        </td>
                        <td class="px-6 py-4 text-center">
                             @if($stat->system_status == 'promote' || $stat->system_status == 'graduate')
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200 block w-full text-center">
                                    {{ $stat->recommendation }}
                                </span>
                            @else
                                <span class="{{ $stat->system_status == 'review' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-red-100 text-red-700 border-red-200' }} px-3 py-1 rounded-full text-xs font-bold border flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-[10px]">{{ $stat->system_status == 'review' ? 'warning' : 'close' }}</span>
                                    {{ $stat->recommendation }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 leading-snug break-words">
                            @if(!empty($stat->fail_reasons))
                                <div class="text-red-600 mb-1">
                                    @foreach($stat->fail_reasons as $reason)
                                        <div>• {{ $reason }}</div>
                                    @endforeach
                                </div>
                            @endif
                            @if($stat->ijazah_note) <div class="font-bold text-emerald-600">{{ $stat->ijazah_note }}</div> @endif
                            @if($stat->manual_note) <div class="italic">"{{ $stat->manual_note }}"</div> @endif
                            @if(empty($stat->fail_reasons) && !$stat->ijazah_note && !$stat->manual_note) - @endif
                        </td>

                        @if(isset($isFinalPeriod) && $isFinalPeriod)
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <!-- SERVER SIDE RENDERED BADGE -->
                                @php
                                    $status = $stat->final_status ?: 'pending';
                                    $badgeClass = 'bg-slate-50 text-slate-600 border-slate-200';
                                    $badgeLabel = 'BELUM DITENTUKAN';
                                    $badgeIcon = 'help_outline';

                                    if(in_array($status, ['promoted', 'promote', 'graduated', 'graduate'])) {
                                        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                        $badgeLabel = in_array($status, ['graduated', 'graduate']) ? 'LULUS' : 'NAIK KELAS';
                                        $badgeIcon = 'check_circle';
                                    } elseif(in_array($status, ['retained', 'retain', 'not_graduated', 'not_graduate'])) {
                                        $badgeClass = 'bg-red-50 text-red-700 border-red-200';
                                        $badgeLabel = in_array($status, ['not_graduated', 'not_graduate']) ? 'TIDAK LULUS' : 'TINGGAL KELAS';
                                        $badgeIcon = 'cancel';
                                    } elseif($status == 'conditional') {
                                        $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                                        $badgeLabel = 'NAIK BERSYARAT';
                                        $badgeIcon = 'warning';
                                    }
                                    
                                    // Prepare Data for Modal
                                    $studentJson = json_encode([
                                        'id' => $stat->student->id,
                                        'name' => $stat->student->nama_lengkap,
                                        'current_status' => $status,
                                        'class_id' => $kelas->id
                                    ]);
                                @endphp

                                <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase border shadow-sm flex items-center gap-2 {{ $badgeClass }}">
                                        <span class="material-symbols-outlined text-[14px]">{{ $badgeIcon }}</span>
                                        <span>{{ $badgeLabel }}</span>
                                </span>
                                
                                @if((!isset($isLocked) || !$isLocked) && (!$stat->is_locked || auth()->user()->isAdmin()))
                                <button @click="openModal({{ $studentJson }})" 
                                        class="text-slate-400 hover:text-blue-600 transition-colors p-1 rounded hover:bg-slate-100" 
                                        title="Ubah Keputusan">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- FLOATING BULK TOOLBAR -->
    <div x-show="selectedIds.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-20 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-40"
         style="display: none;">
        <div class="bg-slate-900/90 backdrop-blur text-white rounded-full shadow-2xl px-6 py-3 flex items-center gap-6 border border-slate-700/50 ring-1 ring-white/10">
             <div class="flex items-center gap-3 border-r border-slate-700 pr-6">
                <span class="bg-primary text-white text-xs font-bold px-2 py-0.5 rounded-full" x-text="selectedIds.length"></span>
                <span class="font-bold text-sm">Terpilih</span>
            </div>
            <div class="flex items-center gap-2">
                @if($isFinalYear)
                    <button @click="bulkUpdate('graduated')" class="px-4 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-xs font-bold transition-colors shadow-lg shadow-emerald-900/20">LULUS</button>
                    <button @click="bulkUpdate('not_graduated')" class="px-4 py-1.5 rounded-full bg-red-600 hover:bg-red-500 text-xs font-bold transition-colors shadow-lg shadow-red-900/20">TIDAK LULUS</button>
                @else
                    <button @click="bulkUpdate('promoted')" class="px-4 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-xs font-bold transition-colors shadow-lg shadow-emerald-900/20">NAIK KELAS</button>
                    <button @click="bulkUpdate('retained')" class="px-4 py-1.5 rounded-full bg-red-600 hover:bg-red-500 text-xs font-bold transition-colors shadow-lg shadow-red-900/20">TINGGAL KELAS</button>
                @endif
            </div>
            <button @click="selectedIds = []" class="ml-2 text-slate-400 hover:text-white transition-colors"><span class="material-symbols-outlined">close</span></button>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div x-show="showModal" 
         style="display: none;"
         class="fixed inset-0 z-[99] overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop -->
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.away="closeModal()"
                 class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-symbols-outlined text-indigo-600">edit</span>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Ubah Keputusan</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 mb-4">
                                    Tentukan status akhir untuk santri: <br>
                                    <span class="font-bold text-slate-900 text-lg" x-text="editData.name"></span>
                                </p>
                                
                                <label class="block text-sm font-bold text-slate-700 mb-2">Status Akhir</label>
                                <select x-model="editData.new_status" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                                    @if($isFinalYear)
                                        <option value="graduated">LULUS</option>
                                        <option value="not_graduated">TIDAK LULUS</option>
                                        <option value="pending">Ditangguhkan / Belum Ada Keputusan</option>
                                    @else
                                        <option value="promoted">Naik Kelas</option>
                                        <option value="retained">Tinggal Kelas</option>
                                        <option value="pending">Ditangguhkan / Belum Ada Keputusan</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" 
                            @click="saveDecision()" 
                            :disabled="saving"
                            class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto disabled:opacity-50 flex items-center gap-2">
                        <span x-show="saving" class="material-symbols-outlined animate-spin text-xs">sync</span>
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                    </button>
                    <button type="button" @click="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function promotionPage() {
        return {
            selectedIds: [],
            search: '',
            showModal: false,
            saving: false,
            editData: {
                id: null,
                name: '',
                new_status: 'pending',
                class_id: null
            },
            
            matchesSearch(name) {
                if(!this.search) return true;
                return name.includes(this.search.toLowerCase());
            },

            toggleAll(e) {
                if(e.target.checked) {
                    this.selectedIds = [
                        @foreach($studentStats as $stat)
                            {{ $stat->student->id }},
                        @endforeach
                    ];
                } else {
                    this.selectedIds = [];
                }
            },

            openModal(studentData) {
                this.editData = {
                    id: studentData.id,
                    name: studentData.name,
                    new_status: studentData.current_status,
                    class_id: studentData.class_id
                };
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
            },

            async saveDecision() {
                this.saving = true;
                try {
                    const res = await fetch("{{ route('promotion.update') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ 
                            student_id: this.editData.id, 
                            class_id: this.editData.class_id,
                            status: this.editData.new_status 
                        })
                    });
                    
                    if (res.ok) {
                        window.location.reload(); // Reload to reflect changes safely
                    } else {
                        const data = await res.json();
                        alert(data.message || 'Gagal menyimpan');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan jaringan');
                } finally {
                    this.saving = false;
                }
            },

            async bulkUpdate(status) {
                 if (!confirm(`Yakin ubah status ${this.selectedIds.length} santri jadi ${status}?`)) return;

                 try {
                     const res = await fetch("{{ route('promotion.bulk_update') }}", {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         },
                         body: JSON.stringify({ 
                            student_ids: this.selectedIds, 
                            class_id: {{ $kelas->id }},
                            status: status 
                        })
                     });
                     
                     if (res.ok) {
                         window.location.reload();
                     } else {
                         alert('Gagal melakukan update massal');
                     }
                 } catch(e) {
                     alert('Error network');
                 }
            }
        }
    }
</script>
@endsection
