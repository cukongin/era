@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 text-center">
        <div class="mb-6">
            <svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Tidak Ada Tahun Ajaran Aktif</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Sistem tidak dapat menemukan Tahun Ajaran yang berstatus <strong>Aktif</strong>. 
            Mohon hubungi Administrator untuk mengaktifkan Tahun Ajaran.
        </p>
        
        @if(auth()->user()->isAdmin())
        <a href="{{ route('settings.index') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
            Ke Pengaturan
        </a>
        @else
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-red-500 hover:underline">Keluar</button>
        </form>
        @endif
    </div>
</div>
@endsection
