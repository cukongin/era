@extends('layouts.app')

@section('title', 'Catatan Wali Kelas - ' . $kelas->nama_kelas)

@section('content')
<div class="flex flex-col gap-6">
    <!-- Check for Admin Filter -->
    @if(auth()->user()->isAdmin() || auth()->user()->isTu())
    <div class="mb-2">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Filter (Admin Mode)</h3>
        <form action="{{ url()->current() }}" method="GET" class="grid grid-cols-12 gap-3 items-center">
            <!-- Jenjang Toggle -->
            <div class="col-span-5 md:col-span-auto flex p-1 bg-slate-100 dark:bg-[#1a2332] rounded-xl border-2 border-slate-200 dark:border-slate-700 h-[46px]">
                @foreach(['MI', 'MTS'] as $j)
                <button type="submit" name="jenjang" value="{{ $j }}"
                    class="flex-1 px-3 text-sm font-bold rounded-lg transition-all flex items-center justify-center {{ (request('jenjang') == $j || (empty(request('jenjang')) && $loop->first)) ? 'bg-white dark:bg-slate-700 text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                    {{ $j }}
                </button>
                @endforeach
            </div>
            <!-- Class Selector -->
            <div class="col-span-7 md:col-span-auto relative group">
                <select name="kelas_id" class="w-full appearance-none pl-10 pr-8 h-[46px] text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer min-w-[200px] shadow-sm transition-all" onchange="this.form.submit()">
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
            </div>
        </form>
    </div>
    @endif
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('walikelas.dashboard') }}" class="hover:text-primary">Dashboard Wali Kelas</a>
                <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                <span>Catatan</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Catatan Wali Kelas</h1>
            <p class="text-sm text-slate-500">
                Berikan catatan motivasi untuk rapor siswa.
            </p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="openGeneratorModal()" class="bg-primary text-white px-4 py-2.5 rounded-xl font-bold hover:bg-primary/90 transition-all flex items-center gap-2 shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined">auto_fix_high</span> Generate Otomatis
            </button>
            <button type="submit" form="catatanForm" class="bg-primary text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/30 hover:bg-green-600 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">save</span> Simpan Catatan
            </button>
        </div>
    </div>

    <!-- Form Table -->
    <div class="grid grid-cols-1 gap-6">
        <form id="catatanForm" action="{{ route('walikelas.catatan.store') }}" method="POST">
            @csrf
            <!-- Important: Pass Class ID for Admin context -->
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($students as $ak)
                @php
                    $note = $catatanRows[$ak->id_siswa] ?? null; // Use correct variable from controller
                    $avg = $averages[$ak->id_siswa] ?? 0;
                @endphp
                <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 hover:border-primary transition-colors student-card" data-id="{{ $ak->id_siswa }}" data-avg="{{ $avg }}">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                             <h3 class="font-bold text-slate-900 dark:text-white">{{ $ak->siswa->nama_lengkap }}</h3>
                             <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-slate-500">{{ $ak->siswa->nis_lokal }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-mono">Avg: {{ $avg }}</span>
                             </div>
                        </div>
                        <span class="h-8 w-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-xs">
                            {{ $loop->iteration }}
                        </span>
                    </div>

                    <div class="space-y-3">
                         <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Catatan / Motivasi</label>
                            <textarea name="catatan[{{ $ak->id_siswa }}]" rows="3" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary placeholder:text-slate-400 note-input" placeholder="Contoh: Tingkatkan prestasimu...">{{ $note->catatan_akademik ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </form>
    </div>
</div>

<!-- Generator Modal -->
<div id="generatorModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeGeneratorModal()"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900 mb-4 flex items-center gap-2" id="modal-title">
                        Konfigurasi Pesan Otomatis (Magic Notes <span class="material-symbols-outlined text-purple-500">auto_fix_high</span>)
                    </h3>
                    <p class="text-sm text-gray-500 mb-6">Sesuaikan template pesan yang akan di-generate berdasarkan rata-rata nilai siswa.</p>

                    <div class="space-y-4">
                        <!-- Grade A -->
                        <div>
                            <label class="block text-sm font-medium text-green-700 mb-1">Grade A (91 - 100) - Sangat Baik</label>
                            <textarea id="tpl_a" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" rows="2">Prestasi yang luar biasa! Pertahankan semangat belajarmu dan jadilah inspirasi bagi teman-temanmu.</textarea>
                        </div>
                        <!-- Grade B -->
                        <div>
                            <label class="block text-sm font-medium text-primary mb-1">Grade B (81 - 90) - Baik</label>
                            <textarea id="tpl_b" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" rows="2">Hasil belajarmu sudah baik. Teruslah tekun dan tingkatkan lagi pencapaianmu di semester depan.</textarea>
                        </div>
                        <!-- Grade C -->
                        <div>
                            <label class="block text-sm font-medium text-amber-700 mb-1">Grade C (70 - 80) - Cukup</label>
                            <textarea id="tpl_c" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" rows="2">Kamu memiliki potensi besar. Tingkatkan lagi fokus dan kedisiplinan dalam belajar agar hasilnya lebih maksimal.</textarea>
                        </div>
                        <!-- Grade D -->
                        <div>
                            <label class="block text-sm font-medium text-red-700 mb-1">Grade D (< 70) - Perlu Bimbingan</label>
                            <textarea id="tpl_d" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" rows="2">Jangan menyerah! Belajarlah lebih giat lagi, perbanyak latihan, dan jangan ragu bertanya kepada guru.</textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" onclick="applyGenerator()" class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary/90 sm:ml-3 sm:w-auto">
                        <span class="material-symbols-outlined text-[16px] mr-1">auto_fix_normal</span> Terapkan Catatan
                    </button>
                    <button type="button" onclick="closeGeneratorModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openGeneratorModal() {
        document.getElementById('generatorModal').classList.remove('hidden');
    }

    function closeGeneratorModal() {
        document.getElementById('generatorModal').classList.add('hidden');
    }

    function applyGenerator() {
        if(!confirm('Aksi ini akan menimpa Catatan siswa secara otomatis. Lanjutkan?')) return;

        const tplA = document.getElementById('tpl_a').value;
        const tplB = document.getElementById('tpl_b').value;
        const tplC = document.getElementById('tpl_c').value;
        const tplD = document.getElementById('tpl_d').value;

        document.querySelectorAll('.student-card').forEach(card => {
            const avg = parseFloat(card.dataset.avg) || 0;
            const noteInput = card.querySelector('.note-input');

            let message = '';

            if (avg >= 91) { message = tplA; }
            else if (avg >= 81) { message = tplB; }
            else if (avg >= 70) { message = tplC; }
            else {
                message = tplD;
            }

            // Set Note
            noteInput.value = message;
        });

        closeGeneratorModal();
    }
</script>
@endsection

