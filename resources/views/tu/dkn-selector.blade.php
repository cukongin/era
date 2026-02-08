@extends('layouts.app')

@section('title', 'Pilih Kelas - DKN Ijazah')

@section('content')
<div class="flex-1 flex flex-col h-full overflow-hidden">
    <!-- Header -->
    <div class="px-8 py-6 bg-white dark:bg-[#1a2e22] border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Kelola Nilai Ijazah (DKN)</h1>
            <p class="text-slate-500 text-sm mt-1">Pilih kelas tingkat akhir untuk mengelola nilai ujian dan ijazah.</p>
        </div>
        <a href="{{ route('tu.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-sm transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
        </a>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-8 scroll-smooth">
        <div class="max-w-6xl mx-auto">
            
            <div class="flex items-center gap-3 mb-6">
                 <div class="bg-purple-100 text-purple-700 p-2 rounded-lg">
                    <span class="material-symbols-outlined">school</span>
                 </div>
                 <h2 class="text-lg font-bold text-slate-800 dark:text-white">Daftar Kelas Akhir (MI 6 / MTs 9 / MA 12)</h2>
            </div>

            @if($finalClasses->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 bg-white dark:bg-[#1a2e22] rounded-3xl border border-dashed border-slate-300 dark:border-slate-700">
                    <div class="bg-orange-50 p-6 rounded-full mb-4">
                        <span class="material-symbols-outlined text-4xl text-orange-400">warning</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Tidak Ada Kelas Akhir Ditemukan</h3>
                    <p class="text-slate-500 text-center max-w-md mt-2">
                        System tidak menemukan kelas akhir (6, 9, 12, atau 3) pada Tahun Ajaran aktif ini.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($finalClasses as $kelas)
                        <div class="bg-white dark:bg-[#1a2e22] rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                            <!-- Background Decoration -->
                            <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity transform group-hover:scale-110 duration-500">
                                <span class="material-symbols-outlined text-9xl">school</span>
                            </div>

                            <div class="relative z-10 flex flex-col h-full">
                                <span class="inline-block px-3 py-1 rounded-full bg-purple-50 text-purple-700 text-[10px] font-bold uppercase tracking-wider w-fit mb-3">
                                    {{ $kelas->jenjang->nama_jenjang ?? $kelas->tingkat_kelas }}
                                </span>
                                
                                <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-1 group-hover:text-purple-600 transition-colors">
                                    {{ $kelas->nama_kelas }}
                                </h3>
                                
                                <p class="text-sm text-slate-500 mb-6 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">person</span>
                                    {{ $kelas->wali_kelas ? $kelas->wali_kelas->name : 'Tanpa Wali Kelas' }}
                                </p>
                                
                                <div class="mt-auto flex flex-col gap-3">
                                    <a href="{{ route('tu.dkn.show', $kelas->id) }}" class="w-full py-3 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:bg-primary/90 hover:shadow-primary/30 transition-all flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-[18px]">edit_note</span> Input Nilai Ijazah
                                    </a>
                                    
                                    <a href="{{ route('tu.dkn.archive', $kelas->id) }}" class="w-full py-3 bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-100 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-[18px]">inventory_2</span> Lihat Arsip Lengkap
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
