@extends('layouts.app')

@section('title', 'Manajemen Halaman')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Manajemen Halaman</h1>
            <p class="text-slate-500 text-sm">Buat halaman informasi statis (Visi Misi, Tata Tertib, Panduan).</p>
        </div>
        <a href="{{ route('settings.pages.create') }}" class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">add_circle</span>
            Buat Halaman
        </a>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-4 font-bold">Judul Halaman</th>
                        <th class="px-6 py-4 font-bold">Link (Slug)</th>
                        <th class="px-6 py-4 font-bold">Status</th>
                        <th class="px-6 py-4 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($pages as $page)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-700 dark:text-slate-200">{{ $page->title }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="text-primary hover:underline flex items-center gap-1">
                                {{ $page->slug }}
                                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $page->status == 'published' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($page->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('settings.pages.edit', $page->id) }}" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg text-slate-500 transition-colors">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                <form action="{{ route('settings.pages.destroy', $page->id) }}" method="POST"
                                      data-confirm-delete="true"
                                      data-title="Hapus Halaman?"
                                      data-message="Halaman ini akan dihapus permanen.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg text-slate-400 hover:text-red-500 transition-colors">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                            Belum ada halaman yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

