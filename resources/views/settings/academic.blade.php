@extends('layouts.app')

@section('title', 'Academic Configuration')

@section('content')
<div class="flex flex-col gap-6">
    <!-- Breadcrumbs & Heading -->
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ url('/') }}" class="hover:text-primary transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">home</span> Dashboard
            </a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-slate-900 dark:text-white font-medium">Configuration</span>
        </div>

        <div class="flex flex-wrap justify-between gap-6">
            <div class="flex flex-col gap-2">
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Academic Period & Logic</h1>
                <p class="text-slate-500 dark:text-slate-400 max-w-2xl">
                    Konfigurasi periode penilaian, tahun ajaran, dan logika pembobotan nilai untuk jenjang MI (Cawu) dan MTs (Semester).
                </p>
            </div>
            <button class="flex items-center gap-2 bg-primary hover:bg-green-600 text-white px-4 py-2 rounded-lg font-bold shadow-lg shadow-primary/30 transition-all h-fit">
                <span class="material-symbols-outlined">add</span>
                Tahun Ajaran Baru
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Sukses!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error!</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Active Academic Year Card -->
    @if($activeYear)
    <div class="flex flex-col rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-surface-dark shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 px-6 py-4 bg-slate-50/50 dark:bg-white/5">
            <div class="flex items-center gap-3">
                <div class="bg-primary/10 text-primary p-2 rounded-lg">
                    <span class="material-symbols-outlined">calendar_month</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tahun Ajaran {{ $activeYear->nama }}</h3>
                    <p class="text-xs text-slate-500 font-medium">Status: Aktif</p>
                </div>
                <span class="ml-2 px-2.5 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-bold border border-green-200">
                    Active
                </span>
            </div>
        </div>

        <!-- Body: Simple Message -->
        <div class="p-8 text-center text-slate-500">
            <p>Fitur pengaturan semester dan bobot nilai sedang dalam proses penyesuaian sistem baru.</p>
            <p class="text-sm mt-2">Silakan gunakan menu <strong>Master Data</strong> untuk mengelola Siswa dan Guru.</p>
        </div>
    </div>
    @endif

    <!-- Archived Years -->
    <div class="flex flex-col gap-3">
        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Arsip Tahun Ajaran</h3>
        
        @forelse($archivedYears as $year)
        <div class="flex cursor-pointer items-center justify-between gap-6 px-6 py-4 bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 transition-colors opacity-70 hover:opacity-100">
            <div class="flex items-center gap-3">
                <div class="bg-slate-100 dark:bg-slate-700 text-slate-500 p-2 rounded-lg">
                    <span class="material-symbols-outlined">history</span>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-slate-700 dark:text-slate-200">{{ $year->nama }}</h3>
                    <p class="text-xs text-slate-500">Status: Non-Aktif</p>
                </div>
                <span class="ml-2 px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                    Archived
                </span>
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-slate-400 border border-dashed border-slate-200 rounded-xl">
            Belum ada arsip tahun ajaran.
        </div>
        @endforelse
    </div>
</div>
@endsection


@push('scripts')
<script>
    // Placeholder script block if needed in future
</script>
@endpush

