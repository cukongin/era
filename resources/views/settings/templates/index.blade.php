@extends('layouts.app')

@section('title', 'Template')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Manajemen Template</h1>
            <p class="text-slate-500 text-sm">Atur tata letak Rapor dan Cover sesuai keinginan.</p>
        </div>
        <a href="{{ route('settings.templates.create') }}" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2">
            <span class="material-symbols-outlined">add</span> Buat Template Baru
        </a>
    </div>

    <!-- MAIN CONFIG TOGGLE -->
    <div class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-3">
             <div class="h-10 w-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-300">
                <span class="material-symbols-outlined">tune</span>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">Pengaturan Template Rapor</h3>
                <p class="text-xs text-slate-500">Pilih mode template yang ingin digunakan saat mencetak Rapor.</p>
            </div>
        </div>

        <form action="{{ route('settings.templates.config') }}" method="POST" class="flex items-center gap-4">
            @csrf
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold {{ \App\Models\GlobalSetting::val('rapor_use_custom_template') ? 'text-slate-400' : 'text-green-600' }}">Bawaan (Default)</span>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="rapor_use_custom_template" value="1" class="sr-only peer" onchange="this.form.submit()" {{ \App\Models\GlobalSetting::val('rapor_use_custom_template') ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>

                <span class="text-sm font-bold {{ \App\Models\GlobalSetting::val('rapor_use_custom_template') ? 'text-primary' : 'text-slate-400' }}">Custom (CKEditor)</span>
            </div>
        </form>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach(['rapor' => 'Template Rapor', 'cover' => 'Template Cover / Identitas', 'transcript' => 'Template Transkip Nilai'] as $type => $label)
        <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-slate-800 dark:text-white">{{ $label }}</h3>
                <span class="text-xs bg-slate-200 dark:bg-slate-700 px-2 py-1 rounded-full">{{ $templates->where('type', $type)->count() }} Layout</span>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($templates->where('type', $type) as $tpl)
                <div class="p-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center {{ $tpl->is_active ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-400' }}">
                            @php
                                $icon = 'description';
                                if($type == 'cover') $icon = 'book';
                                if($type == 'transcript') $icon = 'workspace_premium';
                            @endphp
                            <span class="material-symbols-outlined">{{ $icon }}</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white">{{ $tpl->name }}</h4>
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <span>Updated: {{ $tpl->updated_at->diffForHumans() }}</span>
                                @if($tpl->is_active)
                                <span class="bg-green-100 text-green-700 px-1.5 py-0.5 rounded font-bold">AKTIF</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(!$tpl->is_active)
                        <form action="{{ route('settings.templates.activate', $tpl->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-2 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Aktifkan">
                                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                            </button>
                        </form>
                        @endif

                        @if($tpl->is_locked)
                             <!-- Locked: View Only -->
                            <span class="p-2 text-slate-400" title="Template Permanen (Tidak bisa diedit)">
                                <span class="material-symbols-outlined text-[20px]">lock</span>
                            </span>
                        @else
                            <a href="{{ route('settings.templates.edit', $tpl->id) }}" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </a>

                            @if(!$tpl->is_active || $templates->where('type', $type)->count() > 1)
                            <form action="{{ route('settings.templates.destroy', $tpl->id) }}" method="POST"
                                  onsubmit="return confirm('Hapus template ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </form>
                            @endif
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-500 text-sm">
                    Belum ada template.
                </div>
                @endforelse
            </div>

            <div class="p-3 bg-slate-50 dark:bg-slate-800/30 text-center">
                <a href="{{ route('settings.templates.create') }}?type={{ $type }}" class="text-xs font-bold text-primary hover:underline">+ Buat {{ $type == 'cover' ? 'Cover' : ($type == 'transcript' ? 'Transkip' : 'Rapor') }} Baru</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

