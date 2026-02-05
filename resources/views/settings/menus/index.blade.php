@extends('layouts.app')

@section('title', 'Manajemen Menu')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Manajemen Menu</h1>
            <p class="text-slate-500 text-sm">Atur struktur dan hak akses menu aplikasi.</p>
        </div>
        <button @click="$dispatch('open-menu-modal')" class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">add</span>
            Tambah Menu
        </button>
    </div>

    <!-- Menu List -->
    <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden" x-data="{ expanded: [] }">
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach($menus as $menu)
            <li class="group">
                <div class="flex items-center justify-between p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="material-symbols-outlined text-slate-400 cursor-move">drag_indicator</span>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined">{{ $menu->icon }}</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-700 dark:text-slate-200">{{ $menu->title }}</h3>
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded text-[10px] font-mono">{{ $menu->route ?? $menu->url }}</span>
                                    <span>•</span>
                                    <span>Order: {{ $menu->order }}</span>
                                    <span>•</span>
                                    <div class="flex gap-1">
                                        @foreach($menu->roles as $role)
                                        <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ $role->role }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="$dispatch('edit-menu', { id: {{ $menu->id }}, title: '{{ $menu->title }}', icon: '{{ $menu->icon }}', route: '{{ $menu->route }}', url: '{{ $menu->url }}', order: {{ $menu->order }}, roles: {{ $menu->roles->pluck('role') }} })" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg text-slate-500 transition-colors">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </button>
                        <form action="{{ route('settings.menus.destroy', $menu->id) }}" method="POST"
                              data-confirm-delete="true"
                              data-title="Hapus Menu?"
                              data-message="Menu ini akan hilang dari sidebar.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg text-slate-400 hover:text-red-500 transition-colors">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </form>
                        @if($menu->children->isNotEmpty())
                        <button @click="expanded.includes({{ $menu->id }}) ? expanded = expanded.filter(i => i !== {{ $menu->id }}) : expanded.push({{ $menu->id }})" class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg text-slate-500 transition-colors">
                            <span class="material-symbols-outlined text-lg transform transition-transform" :class="expanded.includes({{ $menu->id }}) ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Submenus -->
                @if($menu->children->isNotEmpty())
                <ul x-show="expanded.includes({{ $menu->id }})" x-collapse class="pl-12 pr-4 pb-2 space-y-1 bg-slate-50/50 dark:bg-slate-800/20 border-t border-slate-100 dark:border-slate-800">
                    @foreach($menu->children as $child)
                    <li class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group/child">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-slate-300 text-sm">subdirectory_arrow_right</span>
                            <div class="flex flex-col">
                                <span class="font-medium text-sm text-slate-700 dark:text-slate-300">{{ $child->title }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $child->route ?? $child->url }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                             <div class="flex gap-1 text-[10px]">
                                @foreach($child->roles as $role)
                                <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ $role->role }}</span>
                                @endforeach
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover/child:opacity-100 transition-opacity">
                                <button @click="$dispatch('edit-menu', { id: {{ $child->id }}, parent_id: {{ $menu->id }}, title: '{{ $child->title }}', icon: '{{ $child->icon }}', route: '{{ $child->route }}', url: '{{ $child->url }}', order: {{ $child->order }}, roles: {{ $child->roles->pluck('role') }} })" class="p-1 hover:bg-slate-200 dark:hover:bg-slate-700 rounded text-slate-500">
                                    <span class="material-symbols-outlined text-base">edit</span>
                                </button>
                                <form action="{{ route('settings.menus.destroy', $child->id) }}" method="POST"
                                      data-confirm-delete="true"
                                      data-title="Hapus Submenu?"
                                      data-message="Submenu ini akan hilang.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 hover:bg-red-100 dark:hover:bg-red-900/30 rounded text-slate-400 hover:text-red-500">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
</div>

<!-- Modal Form -->
<div x-data="{ open: false, isEdit: false, form: { id: null, title: '', icon: '', route: '', url: '', order: 1, roles: [], parent_id: null } }"
    @open-menu-modal.window="open = true; isEdit = false; form = { id: null, title: '', icon: '', route: '', url: '', order: 1, roles: [], parent_id: null }"
    @edit-menu.window="open = true; isEdit = true; form = $event.detail"
    class="relative z-50" x-show="open" x-cloak>
    
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="open = false" x-transition.opacity></div>
    
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#1a2e22] w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden" x-transition.scale>
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white" x-text="isEdit ? 'Edit Menu' : 'Tambah Menu'"></h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
            </div>
            
            <form :action="isEdit ? '{{ url('settings/menus') }}/' + form.id : '{{ route('settings.menus.store') }}'" method="POST" class="p-6 space-y-6">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Row 1: Title & Internal Link -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500">Judul Menu</label>
                        <input type="text" name="title" x-model="form.title" class="w-full rounded-lg border-slate-200 text-sm" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500">Link ke Halaman Internal</label>
                        <select @change="form.url = '{{ url('page') }}/' + $event.target.value" class="w-full rounded-lg border-slate-200 text-sm text-slate-600">
                            <option value="">-- Pilih Halaman --</option>
                            @foreach($pages as $page)
                                <option value="{{ $page->slug }}">{{ $page->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 2: Route & URL -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1 relative">
                        <label class="text-xs font-bold text-slate-500">Route Name (Pilih dari Daftar)</label>
                        <select name="route" x-model="form.route" class="w-full rounded-lg border-slate-200 text-sm text-slate-700">
                            <option value="">-- Pilih Rute --</option>
                            @foreach($routes as $group => $items)
                                <optgroup label="{{ $group }}">
                                    @foreach($items as $item)
                                        <option value="{{ $item['name'] }}">
                                            {{ $item['name'] }} ({{ $item['label'] }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Pilih rute yang sesuai fungsinya.</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500">URL / Path</label>
                        <input type="text" name="url" x-model="form.url" class="w-full rounded-lg border-slate-200 text-sm" placeholder="/settings">
                    </div>
                </div>
                
                <!-- Row 3: Icon, Parent, Order -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500">Icon (Material)</label>
                        <div class="flex gap-2">
                             <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                 <span class="material-symbols-outlined text-slate-600" x-text="form.icon || 'help'"></span>
                             </div>
                             <input type="text" name="icon" x-model="form.icon" class="w-full rounded-lg border-slate-200 text-sm" placeholder="home">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500">Parent Menu</label>
                        <select name="parent_id" x-model="form.parent_id" class="w-full rounded-lg border-slate-200 text-sm">
                            <option value="">-- Root Menu --</option>
                            @foreach($menus as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500">Urutan (Order)</label>
                        <input type="number" name="order" x-model="form.order" class="w-full rounded-lg border-slate-200 text-sm">
                    </div>
                </div>

                <!-- Row 4: Roles -->
                <div class="space-y-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                    <label class="text-xs font-bold text-slate-500">Hak Akses Role</label>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center gap-2 cursor-pointer hover:text-primary transition-colors">
                            <input type="checkbox" name="roles[]" value="admin" x-model="form.roles" class="rounded border-slate-300 text-primary focus:ring-primary w-5 h-5">
                            <span class="text-sm font-semibold">Admin</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer hover:text-primary transition-colors">
                            <input type="checkbox" name="roles[]" value="teacher" x-model="form.roles" class="rounded border-slate-300 text-primary focus:ring-primary w-5 h-5">
                            <span class="text-sm font-semibold">Guru</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer hover:text-primary transition-colors">
                            <input type="checkbox" name="roles[]" value="walikelas" x-model="form.roles" class="rounded border-slate-300 text-primary focus:ring-primary w-5 h-5">
                            <span class="text-sm font-semibold">Wali Kelas</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer hover:text-primary transition-colors">
                            <input type="checkbox" name="roles[]" value="student" x-model="form.roles" class="rounded border-slate-300 text-primary focus:ring-primary w-5 h-5">
                            <span class="text-sm font-semibold">Siswa</span>
                        </label>
                         <label class="flex items-center gap-2 cursor-pointer hover:text-primary transition-colors">
                            <input type="checkbox" name="roles[]" value="staff_tu" x-model="form.roles" class="rounded border-slate-300 text-purple-600 focus:ring-purple-600 w-5 h-5">
                            <span class="text-sm font-semibold text-purple-700">Staff TU</span>
                        </label>
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-3 border-t border-slate-50 dark:border-slate-800 mt-4">
                    <button type="button" @click="open = false" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-sm transition-all">Batal</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-white hover:bg-primary/90 font-bold text-sm shadow-lg shadow-primary/20 transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
