@extends('layouts.app')

@section('title', 'Monitoring Guru')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-8">
    <!-- Page Heading & Actions -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl md:text-4xl font-black tracking-tight text-slate-900 dark:text-white">Monitoring Input Nilai</h1>
            @if($kelas)
            <p class="text-slate-500 dark:text-slate-400 text-base">Pantau progres input nilai Guru Mata Pelajaran untuk Kelas {{ $kelas->nama_kelas }}.</p>
            @else
            <p class="text-slate-500 dark:text-slate-400 text-base">Pilih filter untuk menampilkan data perwalian.</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <!-- Filter Form -->
            <form method="GET" class="flex flex-wrap gap-2 bg-white dark:bg-[#1a2e22] p-1.5 rounded-lg border border-slate-200 dark:border-slate-800">
                <!-- Jenjang Toggle Buttons -->
                <div class="flex bg-slate-100 dark:bg-slate-700 p-1 rounded-lg" x-data="{ jenjang: '{{ request('jenjang') }}' }">
                    <input type="hidden" name="jenjang" :value="jenjang">
                    
                    <button type="button" @click="jenjang = 'MI'; $nextTick(() => $el.closest('form').submit())" class="px-3 py-1 text-xs font-bold rounded-md transition-all" :class="jenjang === 'MI' ? 'bg-white dark:bg-[#1a2e22] text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                        MI
                    </button>
                    <button type="button" @click="jenjang = 'MTS'; $nextTick(() => $el.closest('form').submit())" class="px-3 py-1 text-xs font-bold rounded-md transition-all" :class="jenjang === 'MTS' ? 'bg-white dark:bg-[#1a2e22] text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                        MTS
                    </button>
                </div>
                @if($kelas)
                <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 my-auto"></div>
                <select name="kelas_id" class="text-sm border-none bg-transparent focus:ring-0 font-semibold text-slate-700 dark:text-white py-1" onchange="this.form.submit()">
                    @foreach($allClasses as $c)
                        <option value="{{ $c->id }}" {{ $kelas->id == $c->id ? 'selected' : '' }}>{{ $c->nama_kelas }}</option>
                    @endforeach
                </select>
                @endif
            </form>

            @if($kelas)
            <a href="{{ route('grade.import.index', $kelas->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-sm transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">upload_file</span>
                <span class="hidden sm:inline">Import Kolektif</span>
            </a>
            <button class="bg-primary hover:bg-green-600 text-white font-bold py-2.5 px-5 rounded-lg shadow-sm transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">notifications_active</span>
                <span class="hidden sm:inline">Ingatkan Semua</span>
            </button>
            @endif
        </div>
    </div>

    @if(!$kelas)
    <!-- EMPTY STATE -->
    <div class="flex flex-col items-center justify-center py-20 bg-white dark:bg-[#1a2e22] rounded-3xl border border-slate-200 dark:border-slate-800 border-dashed">
        <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-full mb-4">
            <span class="material-symbols-outlined text-4xl text-slate-400">school</span>
        </div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tidak Ada Data Kelas</h3>
        <p class="text-slate-500 dark:text-slate-400 text-center max-w-sm mt-1">
            Belum ada kelas perwalian yang ditemukan untuk filter jenjang 
            <span class="font-bold text-primary">{{ request('jenjang') ?? 'ini' }}</span>.
        </p>
    </div>
    @else
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Card 1: Total -->
        <div class="bg-white dark:bg-[#1a2e22] p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between h-32 relative overflow-hidden group">
            <div class="absolute right-[-10px] top-[-10px] opacity-5 rotate-12 group-hover:scale-110 transition-transform duration-500">
                <span class="material-symbols-outlined text-[100px] text-slate-800 dark:text-white">library_books</span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 font-medium">Total Mapel</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-4xl font-bold text-slate-900 dark:text-white">{{ count($monitoringData) }}</h3>
                <span class="text-sm text-slate-500 dark:text-slate-500">Mata Pelajaran</span>
            </div>
        </div>

        <!-- Card 2: Selesai -->
        <div class="bg-white dark:bg-[#1a2e22] p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between h-32 relative overflow-hidden group">
            <div class="absolute right-[-10px] top-[-10px] opacity-5 text-primary rotate-12 group-hover:scale-110 transition-transform duration-500">
                <span class="material-symbols-outlined text-[100px]">check_circle</span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 font-medium">Sudah Selesai</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-4xl font-bold text-primary">{{ $finishedCount }}</h3>
                <span class="text-sm text-primary/80">Guru Mapel</span>
            </div>
        </div>

        <!-- Card 3: Belum (Pending) -->
        <div class="bg-white dark:bg-[#1a2e22] p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between h-32 relative overflow-hidden group">
            <div class="absolute right-[-10px] top-[-10px] opacity-5 text-orange-500 rotate-12 group-hover:scale-110 transition-transform duration-500">
                <span class="material-symbols-outlined text-[100px]">pending</span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 font-medium">Belum Selesai</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-4xl font-bold text-orange-500">{{ $notStartedCount }}</h3>
                <span class="text-sm text-orange-500/80">Perlu diingatkan</span>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-[#1a2e22] p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm items-center">
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-slate-400">search</span>
            </div>
            <input class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 dark:border-slate-700 rounded-lg leading-5 bg-slate-50 dark:bg-[#10221a] text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm transition-colors" placeholder="Cari Guru atau Mata Pelajaran..." type="text"/>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 border border-slate-200 dark:border-slate-700 text-sm font-medium rounded-lg text-slate-700 dark:text-white bg-white dark:bg-[#1a2e22] hover:bg-slate-50 dark:hover:bg-[#20342a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                <span class="material-symbols-outlined text-[20px] mr-2 text-slate-500">filter_list</span>
                Filter Status
            </button>
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800" id="monitoringTable">
                <thead class="bg-slate-50 dark:bg-[#20342a]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider" scope="col">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider" scope="col">Mata Pelajaran</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider" scope="col">Guru Pengampu</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-1/5" scope="col">Status Progres</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider" scope="col">Analisa Nilai</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider" scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-[#1a2e22]">
                    @forelse($monitoringData as $index => $data)
                        @php
                            $isDone = $data->progress >= 100;
                            $inProgress = $data->progress > 0 && !$isDone;
                            
                            $badgeClass = $isDone 
                                ? 'bg-primary/10 text-primary' 
                                : ($inProgress ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200');
                            
                            $barClass = $isDone ? 'bg-primary' : ($inProgress ? 'bg-orange-400' : 'bg-red-500');
                            
                            // Data Attribute for filtering
                            $statusFilter = $isDone ? 'finished' : 'pending';
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-[#1a2c24] transition-colors search-item" data-status="{{ $statusFilter }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 row-number">{{ sprintf('%02d', $index + 1) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded {{ $isDone ? 'bg-primary/20 text-primary' : ($inProgress ? 'bg-orange-100 text-orange-600' : 'bg-red-100 text-red-600') }} flex items-center justify-center mr-3">
                                        <span class="material-symbols-outlined text-[18px]">
                                            {{ $isDone ? 'check_circle' : ($inProgress ? 'calculate' : 'pending') }}
                                        </span>
                                    </div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white search-text">{{ $data->nama_mapel }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                        {{ substr($data->nama_guru, 0, 1) }}
                                    </div>
                                    <div class="text-sm text-slate-700 dark:text-slate-300 search-text">{{ $data->nama_guru }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex justify-between mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                        {{ $data->status_label }}
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                                    <div class="{{ $barClass }} h-1.5 rounded-full" style="width: {{ $data->progress > 5 ? $data->progress : 5 }}%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($data->katrol_status === 'Perlu Katrol')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 animate-pulse">
                                        <span class="material-symbols-outlined text-[14px]">warning</span> Perlu Katrol (Min: {{ $data->min_score }})
                                    </span>
                                @elseif($data->katrol_status === 'Aman')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        <span class="material-symbols-outlined text-[14px]">check_small</span> Aman
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-mono">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($isDone)
                                    <a href="{{ route('walikelas.katrol.index', ['kelas_id' => $kelas->id, 'mapel_id' => $data->id]) }}" 
                                       class="text-slate-400 hover:text-primary transition-colors p-2 rounded-full hover:bg-slate-100 dark:hover:bg-[#20342a]" title="Lihat Nilai">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </a>
                                @else
                                    <button class="group inline-flex items-center gap-1.5 px-3 py-1.5 border border-primary text-primary hover:bg-primary hover:text-white rounded-lg transition-all text-xs font-bold shadow-sm">
                                        <span class="material-symbols-outlined text-[16px] group-hover:animate-swing">notifications</span>
                                        Kirim Pengingat
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl mb-2">assignment_late</span>
                                <p>Tidak ada data mata pelajaran.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-slate-700 dark:text-slate-400">
                        Menampilkan <span class="font-bold text-slate-900 dark:text-white" id="startShow">0</span> sampai <span class="font-bold text-slate-900 dark:text-white" id="endShow">0</span> dari <span class="font-bold text-slate-900 dark:text-white" id="totalShow">0</span> hasil
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination" id="paginationControls">
                        <!-- Controls generated by JS -->
                    </nav>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[placeholder="Cari Guru atau Mata Pelajaran..."]');
    const filterBtn = document.querySelector('button.border-slate-200'); 
    const rows = Array.from(document.querySelectorAll('.search-item')); // Convert to Array
    
    // UI Elements
    const elStart = document.getElementById('startShow');
    const elEnd = document.getElementById('endShow');
    const elTotal = document.getElementById('totalShow');
    const elControls = document.getElementById('paginationControls');

    // State
    const state = {
        filter: 'all',
        search: '',
        page: 1,
        limit: 5
    };

    function init() {
        // Event Listeners
        searchInput.addEventListener('input', (e) => {
            state.search = e.target.value.toLowerCase();
            state.page = 1; // Reset to page 1 on search
            render();
        });

        filterBtn.addEventListener('click', () => {
             // Cycle Filter
             if (state.filter === 'all') {
                state.filter = 'pending';
                filterBtn.innerHTML = '<span class="material-symbols-outlined text-[20px] mr-2 text-orange-500">pending</span> Belum Selesai';
                filterBtn.classList.replace('bg-white', 'bg-orange-50');
                filterBtn.classList.replace('border-slate-200', 'border-orange-200');
                filterBtn.classList.replace('text-slate-700', 'text-orange-700');
            } else if (state.filter === 'pending') {
                state.filter = 'finished';
                filterBtn.innerHTML = '<span class="material-symbols-outlined text-[20px] mr-2 text-primary">check_circle</span> Selesai';
                filterBtn.classList.replace('bg-orange-50', 'bg-green-50');
                filterBtn.classList.replace('border-orange-200', 'border-green-200');
                filterBtn.classList.replace('text-orange-700', 'text-green-700');
            } else {
                state.filter = 'all';
                filterBtn.innerHTML = '<span class="material-symbols-outlined text-[20px] mr-2 text-slate-500">filter_list</span> Filter Status';
                filterBtn.classList.replace('bg-green-50', 'bg-white');
                filterBtn.classList.replace('border-green-200', 'border-slate-200');
                filterBtn.classList.replace('text-green-700', 'text-slate-700');
            }
            state.page = 1; // Reset page
            render();
        });

        render();
    }

    function render() {
        // 1. Filter Data
        const filteredRows = rows.filter(row => {
            const mapel = row.querySelector('.search-text').innerText.toLowerCase();
            const guruElement = row.querySelectorAll('.search-text')[1];
            const guru = guruElement ? guruElement.innerText.toLowerCase() : '';
            const status = row.getAttribute('data-status');
            
            const matchesSearch = mapel.includes(state.search) || guru.includes(state.search);
            let matchesFilter = true;
            if (state.filter === 'pending') matchesFilter = status === 'pending';
            if (state.filter === 'finished') matchesFilter = status === 'finished';

            return matchesSearch && matchesFilter;
        });

        // 2. Paginate Data
        const total = filteredRows.length;
        const totalPages = Math.ceil(total / state.limit);
        const start = (state.page - 1) * state.limit;
        const end = start + state.limit;
        const pagedRows = filteredRows.slice(start, end);

        // 3. Update Table
        rows.forEach(r => r.style.display = 'none'); // Hide all first
        let indexCounter = start + 1;
        pagedRows.forEach(row => {
            row.style.display = '';
            // Update "No" column number
            row.querySelector('.row-number').innerText = String(indexCounter++).padStart(2, '0');
        });

        // 4. Update Stats
        elStart.innerText = total > 0 ? start + 1 : 0;
        elEnd.innerText = Math.min(end, total);
        elTotal.innerText = total;

        // 5. Render Pagination Controls
        renderControls(totalPages);
    }

    function renderControls(totalPages) {
        let html = '';
        
        // Previous
        const prevDisabled = state.page === 1;
        html += `<button onclick="changePage(${state.page - 1})" ${prevDisabled ? 'disabled' : ''} class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#1a2e22] text-sm font-medium ${prevDisabled ? 'text-slate-300 cursor-not-allowed' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-[#20342a]'}">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                 </button>`;

        // Numbered Pages (Simple implementation: Show all for small count, or basic ellipsis logic if needed. Assuming small count for classes)
        for (let i = 1; i <= totalPages; i++) {
            const isActive = i === state.page;
            if (isActive) {
                html += `<button aria-current="page" class="z-10 bg-primary/10 border-primary text-primary relative inline-flex items-center px-4 py-2 border text-sm font-bold">${i}</button>`;
            } else {
                html += `<button onclick="changePage(${i})" class="bg-white dark:bg-[#1a2e22] border-slate-300 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-[#20342a] relative inline-flex items-center px-4 py-2 border text-sm font-medium">${i}</button>`;
            }
        }

        // Next
        const nextDisabled = state.page >= totalPages || totalPages === 0;
        html += `<button onclick="changePage(${state.page + 1})" ${nextDisabled ? 'disabled' : ''} class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#1a2e22] text-sm font-medium ${nextDisabled ? 'text-slate-300 cursor-not-allowed' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-[#20342a]'}">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                 </button>`;

        elControls.innerHTML = html;
    }

    // Expose Function to Global Scope for OnClick
    window.changePage = function(newPage) {
        state.page = newPage;
        render();
    };

    init();
});
</script>
@endsection
