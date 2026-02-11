@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-white mb-6">Profil Saya</h1>

    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-3" required>
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-3" required>
                </div>

                <!-- Password (Optional) -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Password Baru (Opsional)</label>
                    <input type="password" name="password" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-3" placeholder="Biarkan kosong jika tidak ubah">
                </div>

                <!-- Confirm Password -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-3">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="submit" class="bg-primary text-white px-6 py-3 rounded-xl font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

