@extends('layouts.app')

@section('title', 'Buat Halaman Baru')

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('settings.pages.index') }}" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Buat Halaman Baru</h1>
    </div>

    <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <form action="{{ route('settings.pages.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Content (Left) -->
                <div class="md:col-span-2 space-y-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Judul Halaman</label>
                        <input type="text" name="title" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 p-3 shadow-sm focus:ring-primary focus:border-primary" placeholder="Contoh: Visi & Misi Sekolah" required>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Konten</label>
                        <textarea name="content" id="editor" class="w-full h-96 rounded-xl border-slate-300 dark:border-slate-700"></textarea>
                    </div>
                </div>

                <!-- Sidebar (Right) -->
                <div class="space-y-6">
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl space-y-4 border border-slate-200 dark:border-slate-700">
                        <h3 class="font-bold text-slate-700 dark:text-slate-300">Publikasi</h3>
                        
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-500">Status</label>
                            <select name="status" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-sm">
                                <option value="published">Published (Tayang)</option>
                                <option value="draft">Draft (Konsep)</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                            Simpan Halaman
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        CKEDITOR.replace('editor', {
            height: 500,
            width: '100%',
            disableNativeSpellChecker: false,
            removePlugins: 'exportpdf',
            toolbar: [
                { name: 'document', items: [ 'Source', '-', 'Preview', 'Print' ] },
                { name: 'clipboard', items: [ 'Undo', 'Redo' ] },
                { name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat' ] },
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                { name: 'links', items: [ 'Link', 'Unlink' ] },
                { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule' ] },
                { name: 'tools', items: [ 'Maximize' ] }
            ]
        });
    });
</script>
@endsection

