@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
<div class="min-h-[60vh] flex flex-col items-center justify-center text-center p-6">
    <div class="w-24 h-24 bg-red-100 text-red-500 rounded-full flex items-center justify-center mb-6">
        <span class="material-symbols-outlined text-5xl">lock</span>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Akses Terbatas</h1>
    <p class="text-slate-500 max-w-md mx-auto mb-8">
        Maaf, akun Anda tidak terdaftar sebagai <strong>Wali Kelas</strong> untuk Tahun Ajaran aktif saat ini. 
        Silakan hubungi Administrator jika ini adalah kesalahan.
    </p>
    <a href="{{ route('dashboard') }}" class="bg-primary text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-green-600 transition-all">
        Kembali ke Dashboard
    </a>
</div>
@endsection

