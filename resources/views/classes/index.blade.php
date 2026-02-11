@extends('layouts.app')

@section('title', 'Manajemen Kelas')

@section('content')
<div class="flex flex-col gap-6">
    <!-- Breadcrumbs & Heading -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ url('/') }}" class="hover:text-primary transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">home</span> Dashboard
            </a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-slate-900 dark:text-white font-medium">Manajemen Kelas</span>
        </div>

        <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-6">
            <div class="flex flex-col gap-2 max-w-2xl">
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Manajemen Kelas</h1>
                <p class="text-slate-500 dark:text-slate-400">
                    Kelola kelas, tentukan wali kelas, dan pantau alokasi siswa untuk tingkat MI (Cawu) dan MTs (Semester).
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button onclick="openCreateModal()" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold shadow-md hover:bg-primary/90 transition-all transform active:scale-95">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    <span class="truncate">Tambah Kelas</span>
                </button>
                <button onclick="openPromoteModal()" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-amber-500 text-white text-sm font-bold shadow-md hover:bg-amber-600 transition-all transform active:scale-95">
                    <span class="material-symbols-outlined text-[20px]">upgrade</span>
                    <span class="truncate">Naikkan Kelas</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats / Filter -->
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="border-b border-slate-200 dark:border-slate-800 px-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex gap-8 overflow-x-auto">
                <a href="{{ route('classes.index', ['search' => request('search')]) }}" class="flex items-center gap-2 border-b-[3px] {{ !request('id_jenjang') ? 'border-primary' : 'border-transparent' }} py-4 px-1 min-w-max group">
                    <p class="{{ !request('id_jenjang') ? 'text-primary' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900' }} text-sm font-bold leading-normal tracking-[0.015em] transition-colors">Semua Lembaga</p>
                    <span class="bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full transition-colors">{{ $stats['total_classes'] ?? 0 }}</span>
                </a>
                @foreach($levels as $lvl)
                <a href="{{ route('classes.index', ['id_jenjang' => $lvl->id, 'search' => request('search')]) }}" class="flex items-center gap-2 border-b-[3px] {{ request('id_jenjang') == $lvl->id ? 'border-primary' : 'border-transparent' }} py-4 px-1 min-w-max group">
                    <p class="{{ request('id_jenjang') == $lvl->id ? 'text-primary' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900' }} text-sm font-bold leading-normal tracking-[0.015em] transition-colors">{{ $lvl->nama ?? $lvl->kode }}</p>
                    <span class="bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full transition-colors">
                        {{ $stats['jenjang_' . $lvl->id] ?? 0 }}
                    </span>
                </a>
                @endforeach
            </div>

            <!-- Moved Reset Button Here -->
            <div class="pb-2 md:pb-0">
                <form action="{{ route('classes.reset') }}" method="POST"
                      data-confirm-delete="true"
                      data-title="RESET KELAS TOTAL?"
                      data-message="BAHAYA: Aksi ini akan MENGHAPUS SEMUA KELAS di tahun ajaran aktif ini. Data tidak dapat dikembalikan."
                      data-confirm-text="Ya, Reset Total!"
                      data-confirm-color="#ef4444"
                      data-icon="warning">
                    @csrf
                    <button type="submit" class="flex items-center justify-center gap-2 rounded-lg py-2 px-3 bg-red-50 text-red-600 text-xs font-bold border border-red-200 hover:bg-red-100 transition-all">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        <span class="truncate">Reset Data</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Filters Toolbar -->
        <div class="p-4 md:p-6 grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-6 lg:col-span-5">
                <form action="{{ route('classes.index') }}" method="GET" class="relative flex w-full h-11">
                    @if(request('id_jenjang'))
                    <input type="hidden" name="id_jenjang" value="{{ request('id_jenjang') }}">
                    @endif
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-[22px]">search</span>
                    </div>
                    <input name="search" value="{{ request('search') }}" class="w-full pl-10 pr-4 rounded-lg bg-slate-50 dark:bg-black/20 border-transparent focus:border-primary focus:bg-white dark:focus:bg-surface-dark focus:ring-0 text-slate-900 dark:text-white placeholder-slate-400 text-sm transition-all" placeholder="Cari Nama Kelas atau Wali Kelas..." type="text" onchange="this.form.submit()"/>
                </form>
            </div>
        </div>
    </div>

    <!-- Grid List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pb-10">
        @forelse($classes as $class)
        <div class="group bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md hover:border-primary/50 dark:hover:border-primary/50 transition-all duration-300 flex flex-col justify-between h-[220px] relative">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-1 relative z-0">
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold {{ $class->jenjang->kode == 'MI' ? 'bg-primary/10 text-primary' : 'bg-primary/10 text-primary' }} w-fit">
                        {{ $class->jenjang->nama_jenjang }}
                    </span>
                    <a href="{{ route('classes.show', $class->id) }}" class="text-slate-900 dark:text-white text-xl font-bold hover:text-primary transition-colors before:absolute before:inset-0">
                        {{ $class->nama_kelas }}
                    </a>
                </div>
                <div class="relative z-10" x-data="{ open: false }">
                    <button @click.prevent="open = !open" @click.away="open = false" class="text-slate-400 hover:text-slate-900 dark:hover:text-white p-1 rounded-full hover:bg-slate-100 dark:hover:bg-white/10 transition-colors">
                        <span class="material-symbols-outlined">more_vert</span>
                    </button>
                    <!-- Dropdown -->
                    <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white dark:bg-surface-dark rounded-md shadow-lg py-1 border border-slate-200 dark:border-slate-800 z-50">
                        <a href="#"
                           data-id="{{ $class->id }}"
                           data-nama="{{ $class->nama_kelas }}"
                           data-jenjang="{{ $class->id_jenjang }}"
                           data-tingkat="{{ $class->tingkat_kelas }}"
                           data-wali="{{ $class->id_wali_kelas }}"
                           onclick="event.preventDefault(); openEditModalFromEl(this)"
                           class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5">
                            Edit Data
                        </a>
                        <form action="{{ route('classes.destroy', $class->id) }}" method="POST"
                              data-confirm-delete="true"
                              data-title="Hapus Kelas?"
                              data-message="Data kelas dan anggota di dalamnya akan terhapus.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                Hapus Kelas
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-4 pointer-events-none">
                @if($class->wali_kelas)
                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                    {{ substr($class->wali_kelas->name, 0, 1) }}
                </div>
                <div class="flex flex-col">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Wali Kelas</span>
                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-200">{{ $class->wali_kelas->name }}</span>
                </div>
                @else
                <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 border border-slate-200">
                    <span class="material-symbols-outlined text-[20px]">person_off</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs text-slate-500 font-medium uppercase tracking-wider">Wali Kelas</span>
                    <span class="text-sm font-semibold text-red-500 italic">Belum Ditentukan</span>
                </div>
                @endif
            </div>

            <div class="mt-auto pt-4 border-t border-slate-50 dark:border-slate-800 flex items-center justify-between pointer-events-none">
                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                    <span class="material-symbols-outlined text-[18px]">group</span>
                    <span class="text-sm font-medium">{{ $class->anggota_kelas_count }} Siswa</span>
                    <span class="text-xs text-slate-300 mx-1">â€¢</span>
                    <span class="text-sm font-medium">{{ $class->pengajar_mapel_count }} Mapel</span>
                </div>
                <span class="flex h-2 w-2 rounded-full bg-slate-200 dark:bg-slate-700 relative"></span>
            </div>
        </div>
        @empty
        <div class="col-span-full p-8 text-center text-slate-400 border border-dashed border-slate-200 rounded-xl">
            Belum ada kelas yang dibuat.
        </div>
        @endforelse

        <!-- Create New Card -->
        <button onclick="openCreateModal()" class="group bg-transparent border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-5 hover:border-primary hover:bg-slate-50 dark:hover:bg-white/5 transition-all duration-300 flex flex-col items-center justify-center gap-4 h-[220px]">
            <div class="h-12 w-12 rounded-full bg-slate-100 dark:bg-white/5 flex items-center justify-center group-hover:bg-white dark:group-hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-slate-400 group-hover:text-primary text-[28px]">add</span>
            </div>
            <span class="text-slate-500 dark:text-slate-400 group-hover:text-primary font-bold text-sm">Buat Kelas Lain</span>
        </button>
    </div>
</div>
@endsection

<!-- Edit Class Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-surface-dark text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-800">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white dark:bg-surface-dark px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white mb-4">Edit Data Kelas</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Nama Kelas</label>
                                <input type="text" id="edit_nama_kelas" name="nama_kelas" required class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                            </div>
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Jenjang (Level)</label>
                                <select id="edit_id_jenjang" name="id_jenjang" required class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                                    @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}">{{ $lvl->nama_jenjang }} ({{ $lvl->kode }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Tingkat (Grade)</label>
                                <input type="number" id="edit_tingkat_kelas" name="tingkat_kelas" required min="1" max="9" class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                            </div>
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Wali Kelas</label>
                                <select id="edit_id_wali_kelas" name="id_wali_kelas" class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                                    <option value="">-- Pilih Wali Kelas --</option>
                                    @foreach($teachers as $t)
                                    @php
                                        $isTaken = in_array($t->id, $takenTeachers ?? []);
                                    @endphp
                                    <option value="{{ $t->id }}" data-taken="{{ $isTaken ? 'true' : 'false' }}">
                                        {{ $t->name }} {{ $isTaken ? '(Sudah Ada Kelas)' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-black/20 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 sm:ml-3 sm:w-auto">Simpan Perubahan</button>
                        <button type="button" onclick="closeEditModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Create Class Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-surface-dark text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-800">
                <form action="{{ route('classes.store') }}" method="POST">
                    @csrf
                    <!-- Hidden Year ID for now, assume backend handles it or we select it -->
                    <input type="hidden" name="id_tahun_ajaran" value="{{ $academicYears->first()->id ?? 1 }}">

                    <div class="bg-white dark:bg-surface-dark px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white mb-4">Tambah Kelas Baru</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Nama Kelas</label>
                                <input type="text" name="nama_kelas" required placeholder="Contoh: 1-A" class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                            </div>
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Jenjang (Level)</label>
                                <select name="id_jenjang" required class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                                    @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}">{{ $lvl->nama_jenjang }} ({{ $lvl->kode }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Tingkat (Grade)</label>
                                <input type="number" name="tingkat_kelas" required min="1" max="9" class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                            </div>
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Wali Kelas</label>
                                <select name="id_wali_kelas" class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                                    <option value="">-- Pilih Wali Kelas --</option>
                                    @foreach($teachers as $t)
                                    @php
                                        $isTaken = in_array($t->id, $takenTeachers ?? []);
                                    @endphp
                                    <option value="{{ $t->id }}" class="{{ $isTaken ? 'text-slate-400 bg-slate-50' : '' }}" {{ $isTaken ? 'disabled' : '' }}>
                                        {{ $t->name }} {{ $isTaken ? '(Sudah Ada Kelas)' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-black/20 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 sm:ml-3 sm:w-auto">Simpan</button>
                        <button type="button" onclick="closeCreateModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Promote Modal -->
<div id="promoteModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-surface-dark text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 dark:border-slate-800">
                    <form action="{{ route('classes.bulk-promote') }}" method="POST"
                          data-confirm-delete="true"
                          data-title="Proses Kenaikan?"
                          data-message="Siswa akan dipindahkan secara massal ke tingkat selanjutnya."
                          data-confirm-text="Ya, Proses!"
                          data-confirm-color="#f59e0b"
                          data-icon="question">
                    @csrf
                    <div class="bg-white dark:bg-surface-dark px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="h-10 w-10 flex-shrink-0 rounded-full bg-amber-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-amber-600">upgrade</span>
                            </div>
                            <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">Proses Kenaikan Kelas</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="text-sm text-slate-500 bg-amber-50 border border-amber-100 p-3 rounded-lg">
                                <ul class="list-disc pl-4 space-y-1">
                                    <li>Siswa <b>NAIK KELAS</b> akan dipindahkan ke tingkat selanjutnya (Misal: 1A -> 2A).</li>
                                    <li>Kelas di Tahun Aktif akan otomatis dibuat jika belum ada.</li>
                                    <li>Kelas Akhir (6/9) yang Naik akan jadi <b>LULUS</b>.</li>
                                </ul>
                            </div>

                            <div class="flex items-start gap-2 bg-red-50 p-3 rounded-lg border border-red-100">
                                <div class="flex h-6 items-center">
                                    <input id="reset_first" name="reset_first" type="checkbox" value="1" checked class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-600">
                                </div>
                                <div class="text-sm leading-6">
                                    <label for="reset_first" class="font-medium text-slate-900 dark:text-white">Hapus Data Tahun Ini Dulu (Wajib)</label>
                                    <p class="text-slate-500 text-xs">Menghapus semua kelas di tahun aktif sebelum memindahkan siswa, agar tidak numbuk/dobel.</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Pilih Jenjang (Opsional)</label>
                                <select name="id_jenjang" class="block w-full rounded-md border-0 py-1.5 text-slate-900 bg-white shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-amber-500 sm:text-sm sm:leading-6">
                                    <option value="">-- Semua Jenjang --</option>
                                    @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}">{{ $lvl->nama_jenjang ?? $lvl->nama ?? 'Jenjang ' . $lvl->kode }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-slate-500 mt-1">Kosongkan untuk memproses semua kelas.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-black/20 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-amber-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 sm:ml-3 sm:w-auto">Proses Sekarang</button>
                        <button type="button" onclick="closePromoteModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }
    function openPromoteModal() {
        document.getElementById('promoteModal').classList.remove('hidden');
    }
    function closePromoteModal() {
        document.getElementById('promoteModal').classList.add('hidden');
    }

    function openEditModal(id, nama, jenjang, tingkat, wali) {
        // Populate inputs
        document.getElementById('edit_nama_kelas').value = nama;
        document.getElementById('edit_id_jenjang').value = jenjang;
        document.getElementById('edit_tingkat_kelas').value = tingkat;
        document.getElementById('edit_id_wali_kelas').value = wali || "";

        // Set Action URL
        let url = "{{ route('classes.update', ':id') }}";
        url = url.replace(':id', id);
        document.getElementById('editForm').action = url;

        // Update Disabled State
        updateTakenTeachersState(wali);

        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function openEditModalFromEl(el) {
        let id = el.getAttribute('data-id');
        let nama = el.getAttribute('data-nama');
        let jenjang = el.getAttribute('data-jenjang');
        let tingkat = el.getAttribute('data-tingkat');
        let wali = el.getAttribute('data-wali');

        openEditModal(id, nama, jenjang, tingkat, wali);
    }

    // Function to handle "Taken" teachers logic for Edit Modal
    function updateTakenTeachersState(currentWaliId) {
        let select = document.getElementById('edit_id_wali_kelas');
        let options = select.options;

        for (let i = 0; i < options.length; i++) {
            let opt = options[i];
            let isTaken = opt.getAttribute('data-taken') === 'true';
            let isCurrent = opt.value == currentWaliId;

            // Disable if TAKEN AND NOT CURRENT
            // Enable if NOT TAKEN OR IS CURRENT
            if (isTaken && !isCurrent) {
                opt.disabled = true;
                opt.classList.add('text-slate-400', 'bg-slate-50');
            } else {
                opt.disabled = false;
                opt.classList.remove('text-slate-400', 'bg-slate-50');
            }
        }
    }
</script>
@endpush

