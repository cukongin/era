@extends('layouts.app')

@section('content')
<div class="p-10 flex flex-col items-center justify-center text-center h-[60vh]">
    <span class="material-symbols-outlined text-6xl text-amber-300 mb-4">warning</span>
    <h2 class="text-xl font-bold text-slate-700 dark:text-white">Bukan Kelas Akhir</h2>
    <p class="text-slate-500 max-w-md my-2">
        Kelas <strong>{{ $kelas->nama_kelas }}</strong> adalah Kelas {{ $kelas->tingkat_kelas }}.<br>
        Menu Ijazah hanya tersedia untuk Kelas 6 (MI) atau Kelas 9 (MTS).
    </p>
    <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:underline">Kembali ke Dashboard</a>
</div>
@endsection

