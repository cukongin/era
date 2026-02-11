@extends('layouts.app')

@section('title', $template->exists ? 'Edit Template' : 'Buat Template Baru')

@section('content')
<div class="flex flex-col h-auto">

    <form id="template-form" action="{{ $template->exists ? route('settings.templates.update', $template->id) : route('settings.templates.store') }}" method="POST" class="h-full flex flex-col gap-4">
        @csrf
        @if($template->exists)
            @method('PUT')
        @endif

        <!-- Global Error Alert -->
        @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mx-6 mt-4" role="alert">
            <p class="font-bold">Gagal Menyimpan</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Header -->
        <div class="flex justify-between items-center shrink-0">
            <!-- ... (Header content unchanged) ... -->
            <div class="flex items-center gap-3">
                <a href="{{ route('settings.templates.index') }}" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $template->exists ? 'Edit Template' : 'Template Baru' }}</h1>
                    @if($template->exists && $template->is_active)
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded font-bold">SEDANG AKTIF</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- PRESET DROPDOWN -->
                <div class="relative group">
                    <button type="button" class="bg-primary/10 hover:bg-primary/20 text-primary px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 border border-primary/20">
                        <span class="material-symbols-outlined">interests</span> Load Preset
                    </button>
                    <div class="absolute right-0 top-full mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-xl hidden group-hover:block z-50 overflow-hidden">
                        <div class="p-2">
                            <h6 class="text-xs font-bold text-slate-400 px-2 py-1 uppercase">Rapor / Cover</h6>
                            <button type="button" onclick="loadPreset('kemenag_mi')" class="w-full text-left px-2 py-1.5 text-sm hover:bg-slate-50 rounded flex items-center gap-2">
                                <span class="material-symbols-outlined text-green-600 text-[18px]">school</span> Rapor Kemenag (MI)
                            </button>
                            <button type="button" onclick="loadPreset('diknas_smp')" class="w-full text-left px-2 py-1.5 text-sm hover:bg-slate-50 rounded flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[18px]">menu_book</span> Rapor Diknas (SMP)
                            </button>
                            <button type="button" onclick="loadPreset('simple')" class="w-full text-left px-2 py-1.5 text-sm hover:bg-slate-50 rounded flex items-center gap-2">
                                <span class="material-symbols-outlined text-slate-600 text-[18px]">article</span> Rapor Simple
                            </button>

                            <h6 class="text-xs font-bold text-slate-400 px-2 py-1 uppercase mt-2 border-t border-slate-100">Transkip Nilai</h6>
                            <button type="button" onclick="loadPreset('transcript_simple')" class="w-full text-left px-2 py-1.5 text-sm hover:bg-slate-50 rounded flex items-center gap-2">
                                <span class="material-symbols-outlined text-amber-600 text-[18px]">workspace_premium</span> Transkip Nilai Simple
                            </button>
                        </div>
                    </div>
                </div>

                @if($template->exists && !$template->is_active)
                    <button type="submit" form="activate-form" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined">check_circle</span> Aktifkan
                    </button>
                @endif

                <button type="button" onclick="previewTemplate()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 border border-slate-300">
                    <span class="material-symbols-outlined">visibility</span> Preview
                </button>

                <button type="button" onclick="saveTemplate()" class="bg-primary hover:bg-primary/90 text-white px-6 py-2 rounded-lg text-sm font-bold flex items-center gap-2 shadow-lg shadow-blue-500/30">
                    <span class="material-symbols-outlined">save</span> Simpan
                </button>
            </div>
        </div>

        <script>
            function saveTemplate() {
                const form = document.querySelector('#template-form');
                for (var i in CKEDITOR.instances) CKEDITOR.instances[i].updateElement();
                form.submit();
            }

            function previewTemplate() {
                const form = document.querySelector('#template-form');
                const oldAction = form.action;
                const oldTarget = form.target;
                for (var i in CKEDITOR.instances) CKEDITOR.instances[i].updateElement();

                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) methodInput.disabled = true;

                form.action = "{{ route('settings.templates.preview') }}";
                form.target = "_blank";
                form.submit();

                if (methodInput) methodInput.disabled = false;
                form.action = oldAction;
                form.target = oldTarget || "";
            }

            function loadPreset(presetName) {
                if (!confirm('Konten editor akan ditimpa dengan template preset. Lanjutkan?')) return;

                // Get current type
                const typeSelect = document.querySelector('select[name="type"]');
                const type = typeSelect ? typeSelect.value : 'rapor';

                fetch(`{{ route('settings.templates.preset') }}?preset=${presetName}&type=${type}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.content) {
                            CKEDITOR.instances.editor.setData(data.content);
                            // Auto-switch type if transcript
                            if (presetName.includes('transcript')) {
                                typeSelect.value = 'transcript';
                            }
                        }
                    })
                    .catch(err => alert('Gagal memuat preset: ' + err));
            }
        </script>


        <div class="flex-1 flex gap-6 min-h-0">
            <!-- Main Editor Area -->
            <div class="flex-1 flex flex-col bg-white rounded-xl shadow-sm border border-slate-200 relative">
                <!-- Textarea for CKEditor 4 -->
                <textarea id="editor" name="content">{!! old('content', $template->content) !!}</textarea>
            </div>

            <!-- Sidebar Settings -->
            <div class="w-72 flex flex-col gap-4 shrink-0 overflow-y-auto pb-10">

                <!-- Basic Info -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm space-y-4">

                    <h3 class="font-bold text-sm text-slate-700">Informasi Template</h3>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Template</label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-primary focus:border-primary" placeholder="Contoh: Rapor Kemenag 2024">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Jenis</label>
                        <select name="type" class="w-full text-sm rounded-lg border-slate-300 focus:ring-primary">
                            <option value="rapor" {{ old('type', $template->type) == 'rapor' ? 'selected' : '' }}>Halaman Rapor (Nilai)</option>
                            <option value="cover" {{ old('type', $template->type) == 'cover' ? 'selected' : '' }}>Cover / Identitas</option>
                            <option value="transcript" {{ old('type', $template->type) == 'transcript' ? 'selected' : '' }}>Transkip Nilai (Ijazah)</option>
                        </select>
                    </div>
                    <div>
                         <label class="block text-xs font-medium text-slate-500 mb-1">Orientasi</label>
                        <select name="orientation" class="w-full text-sm rounded-lg border-slate-300 focus:ring-primary">
                            <option value="portrait" {{ old('orientation', $template->orientation) == 'portrait' ? 'selected' : '' }}>Portrait (Tegak)</option>
                            <option value="landscape" {{ old('orientation', $template->orientation) == 'landscape' ? 'selected' : '' }}>Landscape (Mendatar)</option>
                        </select>
                    </div>

                    <!-- Margins (New) -->
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-2">Margin Kertas (mm)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] text-slate-400">Atas</label>
                                <input type="number" name="margins[top]" value="{{ old('margins.top', $template->margins['top'] ?? 10) }}" class="w-full text-sm rounded-lg border-slate-300 py-1 px-2">
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400">Kanan</label>
                                <input type="number" name="margins[right]" value="{{ old('margins.right', $template->margins['right'] ?? 10) }}" class="w-full text-sm rounded-lg border-slate-300 py-1 px-2">
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400">Bawah</label>
                                <input type="number" name="margins[bottom]" value="{{ old('margins.bottom', $template->margins['bottom'] ?? 10) }}" class="w-full text-sm rounded-lg border-slate-300 py-1 px-2">
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400">Kiri</label>
                                <input type="number" name="margins[left]" value="{{ old('margins.left', $template->margins['left'] ?? 10) }}" class="w-full text-sm rounded-lg border-slate-300 py-1 px-2">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variables Cheat Sheet -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex-1">
                    <h3 class="font-bold text-sm text-slate-700 mb-2">Kode Data (Variable)</h3>
                    <p class="text-xs text-slate-500 mb-3">Klik untuk menyalin kode.</p>

                    <div class="space-y-4">

                        <!-- Identitas Siswa -->
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block border-b pb-1 mb-2">1. Identitas Siswa</span>
                            <div class="grid grid-cols-1 gap-1">
                                <button type="button" onclick="insertVar('[[NAMA_SISWA]]')" class="text-left text-xs bg-slate-50 hover:bg-slate-100 p-1.5 rounded border border-slate-200 font-mono text-slate-700 transition">[[NAMA_SISWA]] <span class="text-[10px] text-slate-400 float-right">Nama Lengkap</span></button>
                                <button type="button" onclick="insertVar('[[NIS]]')" class="text-left text-xs bg-slate-50 hover:bg-slate-100 p-1.5 rounded border border-slate-200 font-mono text-slate-700 transition">[[NIS]] <span class="text-[10px] text-slate-400 float-right">Nomor Induk</span></button>
                                <button type="button" onclick="insertVar('[[NISN]]')" class="text-left text-xs bg-slate-50 hover:bg-slate-100 p-1.5 rounded border border-slate-200 font-mono text-slate-700 transition">[[NISN]] <span class="text-[10px] text-slate-400 float-right">NISN</span></button>
                                <button type="button" onclick="insertVar('[[ALAMAT_SISWA]]')" class="text-left text-xs bg-slate-50 hover:bg-slate-100 p-1.5 rounded border border-slate-200 font-mono text-slate-700 transition">[[ALAMAT_SISWA]] <span class="text-[10px] text-slate-400 float-right">Alamat</span></button>
                            </div>
                        </div>

                        <!-- Data Kelas & Sekolah -->
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block border-b pb-1 mb-2">2. Kelas & Sekolah</span>
                            <div class="grid grid-cols-1 gap-1">
                                <button type="button" onclick="insertVar('[[KELAS]]')" class="text-left text-xs bg-slate-50 hover:bg-slate-100 p-1.5 rounded border border-slate-200 font-mono text-slate-700 transition">[[KELAS]]</button>
                                <button type="button" onclick="insertVar('[[SEMESTER]]')" class="text-left text-xs bg-slate-50 hover:bg-slate-100 p-1.5 rounded border border-slate-200 font-mono text-slate-700 transition">[[SEMESTER]]</button>
                                <button type="button" onclick="insertVar('[[TAHUN_AJARAN]]')" class="text-left text-xs bg-slate-50 hover:bg-slate-100 p-1.5 rounded border border-slate-200 font-mono text-slate-700 transition">[[TAHUN_AJARAN]]</button>
                                <button type="button" onclick="insertVar('[[NAMA_SEKOLAH]]')" class="text-left text-xs bg-slate-50 hover:bg-slate-100 p-1.5 rounded border border-slate-200 font-mono text-slate-700 transition">[[NAMA_SEKOLAH]]</button>
                                <button type="button" onclick="insertVar('[[KEPALA_SEKOLAH]]')" class="text-left text-xs bg-slate-50 hover:bg-slate-100 p-1.5 rounded border border-slate-200 font-mono text-slate-700 transition">[[KEPALA_SEKOLAH]]</button>
                                <button type="button" onclick="insertVar('[[WALI_KELAS]]')" class="text-left text-xs bg-slate-50 hover:bg-slate-100 p-1.5 rounded border border-slate-200 font-mono text-slate-700 transition">[[WALI_KELAS]]</button>
                                <button type="button" onclick="insertVar('[[TANGGAL_RAPOR]]')" class="text-left text-xs bg-slate-50 hover:bg-slate-100 p-1.5 rounded border border-slate-200 font-mono text-slate-700 transition">[[TANGGAL_RAPOR]]</button>
                            </div>
                        </div>

                        <!-- Statistik & Kenaikan -->
                        <div>
                            <span class="text-[10px] font-bold text-green-600 uppercase tracking-wider block border-b pb-1 mb-2">3. Statistik & Status</span>
                            <div class="grid grid-cols-1 gap-1">
                                <button type="button" onclick="insertVar('[[JUMLAH_NILAI]]')" class="text-left text-xs bg-green-50 hover:bg-green-100 p-1.5 rounded border border-green-200 font-mono text-green-700 transition">[[JUMLAH_NILAI]]</button>
                                <button type="button" onclick="insertVar('[[RATA_RATA]]')" class="text-left text-xs bg-green-50 hover:bg-green-100 p-1.5 rounded border border-green-200 font-mono text-green-700 transition">[[RATA_RATA]]</button>
                                <button type="button" onclick="insertVar('[[PERINGKAT]]')" class="text-left text-xs bg-green-50 hover:bg-green-100 p-1.5 rounded border border-green-200 font-mono text-green-700 transition">[[PERINGKAT]] <span class="text-[10px] text-green-500 float-right">Ranking</span></button>
                                <button type="button" onclick="insertVar('[[TOTAL_SISWA]]')" class="text-left text-xs bg-green-50 hover:bg-green-100 p-1.5 rounded border border-green-200 font-mono text-green-700 transition">[[TOTAL_SISWA]]</button>
                                <button type="button" onclick="insertVar('[[STATUS_KENAIKAN]]')" class="text-left text-xs bg-yellow-50 hover:bg-yellow-100 p-2 rounded border border-yellow-200 font-mono text-yellow-700 font-bold transition">[[STATUS_KENAIKAN]]</button>
                            </div>
                        </div>

                        <!-- Tabel Akademik -->
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block border-b pb-1 mb-2">3. Tabel Akademik</span>
                            <div class="grid grid-cols-1 gap-1">
                                <button type="button" onclick="insertVar('[[TABEL_NILAI]]')" class="text-left text-xs bg-primary/10 hover:bg-primary/20 p-2 rounded border border-primary/20 font-mono text-primary font-bold transition">[[TABEL_NILAI]] <span class="text-[10px] text-primary/70 block">Daftar Nilai Otomatis</span></button>
                                <button type="button" onclick="insertVar('[[TABEL_PRESTASI]]')" class="text-left text-xs bg-primary/10 hover:bg-primary/20 p-2 rounded border border-primary/20 font-mono text-primary font-bold transition">[[TABEL_PRESTASI]] <span class="text-[10px] text-primary/70 block">Tabel Prestasi</span></button>
                            </div>
                        </div>

                        <!-- Tabel Transkip / Custom Loop -->
                        <div>
                            <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider block border-b pb-1 mb-2">3b. Tabel Manual / Transkip</span>
                            <p class="text-[10px] text-slate-400 mb-2 leading-tight">Gunakan ini untuk Transkip atau tabel custom.</p>
                            <div class="grid grid-cols-1 gap-1">
                                <button type="button" onclick="insertVar('[[LOOP_NILAI_START]]')" class="text-left text-xs bg-amber-50 hover:bg-amber-100 p-1.5 rounded border border-amber-200 font-mono text-amber-700 font-bold transition">[[LOOP_NILAI_START]] <span class="text-[10px] text-amber-500 float-right">Awal Loop</span></button>
                                <div class="pl-2 border-l-2 border-amber-100 grid grid-cols-1 gap-1">
                                    <button type="button" onclick="insertVar('[[NO]]')" class="text-left text-xs p-1 hover:bg-slate-50 rounded text-slate-600 font-mono">[[NO]]</button>
                                    <button type="button" onclick="insertVar('[[MAPEL]]')" class="text-left text-xs p-1 hover:bg-slate-50 rounded text-slate-600 font-mono">[[MAPEL]]</button>
                                    <button type="button" onclick="insertVar('[[KKM]]')" class="text-left text-xs p-1 hover:bg-slate-50 rounded text-slate-600 font-mono">[[KKM]]</button>
                                    <button type="button" onclick="insertVar('[[NILAI]]')" class="text-left text-xs p-1 hover:bg-slate-50 rounded text-slate-600 font-mono">[[NILAI]] <span class="text-[9px] text-slate-400">(Nilai Akhir)</span></button>
                                    <button type="button" onclick="insertVar('[[PREDIKAT]]')" class="text-left text-xs p-1 hover:bg-slate-50 rounded text-slate-600 font-mono">[[PREDIKAT]]</button>
                                    <button type="button" onclick="insertVar('[[NILAI_RAPOR]]')" class="text-left text-xs p-1 hover:bg-amber-50 rounded text-amber-600 font-mono">[[NILAI_RAPOR]] <span class="text-[9px] text-amber-400">(Rata Rapor)</span></button>
                                    <button type="button" onclick="insertVar('[[NILAI_UJIAN]]')" class="text-left text-xs p-1 hover:bg-amber-50 rounded text-amber-600 font-mono">[[NILAI_UJIAN]] <span class="text-[9px] text-amber-400">(Nilai UM)</span></button>
                                </div>
                                <button type="button" onclick="insertVar('[[LOOP_NILAI_END]]')" class="text-left text-xs bg-amber-50 hover:bg-amber-100 p-1.5 rounded border border-amber-200 font-mono text-amber-700 font-bold transition">[[LOOP_NILAI_END]] <span class="text-[10px] text-amber-500 float-right">Akhir Loop</span></button>
                            </div>
                        </div>

                        <!-- Non-Akademik -->
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block border-b pb-1 mb-2">4. Non-Akademik</span>
                            <div class="grid grid-cols-1 gap-1">
                                <button type="button" onclick="insertVar('[[TABEL_EKSKUL]]')" class="text-left text-xs bg-primary/10 hover:bg-primary/20 p-2 rounded border border-primary/20 font-mono text-primary font-bold transition">[[TABEL_EKSKUL]]</button>
                                <button type="button" onclick="insertVar('[[TABEL_KEPRIBADIAN]]')" class="text-left text-xs bg-primary/10 hover:bg-primary/20 p-2 rounded border border-primary/20 font-mono text-primary font-bold transition">[[TABEL_KEPRIBADIAN]]</button>
                                <button type="button" onclick="insertVar('[[TABEL_KETIDAKHADIRAN]]')" class="text-left text-xs bg-primary/10 hover:bg-primary/20 p-2 rounded border border-primary/20 font-mono text-primary font-bold transition">[[TABEL_KETIDAKHADIRAN]]</button>
                                <button type="button" onclick="insertVar('[[CATATAN_WALI]]')" class="text-left text-xs bg-yellow-50 hover:bg-yellow-100 p-2 rounded border border-yellow-200 font-mono text-yellow-700 font-bold transition">[[CATATAN_WALI]]</button>
                                <button type="button" onclick="insertVar('[[STATUS_KENAIKAN]]')" class="text-left text-xs bg-yellow-50 hover:bg-yellow-100 p-2 rounded border border-yellow-200 font-mono text-yellow-700 font-bold transition">[[STATUS_KENAIKAN]]</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@if($template->exists && !$template->is_active)
    <!-- Hidden Form for Activation -->
    <form id="activate-form" action="{{ route('settings.templates.activate', $template->id) }}" method="POST" class="hidden">
        @csrf
    </form>
@endif

<!-- CKEditor 4 Full-All (Better Plugin Support) -->
<script src="https://cdn.ckeditor.com/4.22.1/full-all/ckeditor.js"></script>

<style>
    /* Center the editor wrapper */
    .cke_chrome {
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important;
        border: 1px solid #d1d5db !important;
        border-radius: 8px !important;
        overflow: hidden;
    }
    .cke_inner {
        background: #f8fafc !important; /* Light gray background */
    }
    /* Hide Secure Version Warning */
    .cke_notification_warning {
        display: none !important;
    }
</style>

<script>
    window.onload = function() {
        if (typeof CKEDITOR === 'undefined') {
            alert('Gagal memuat CKEditor. Mohon periksa koneksi internet Anda atau refresh halaman.');
            return;
        }

        CKEDITOR.replace('editor', {
            height: 1000, // Fixed Pixel Height (Verified "Long")
            resize_enabled: false,
            versionCheck: false, // Disable security warning
            // A4 Content Style
            contentsCss: [
                'https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&display=swap',
                'https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&display=swap',
                '{{ asset("css/fonts.css") }}', // Load Local Fonts (LPMQ)
                'https://cdn.tailwindcss.com'
            ],
            // Custom Font Names
            font_names: 'Arial/Arial, Helvetica, sans-serif;' +
                'Times New Roman/Times New Roman, Times, serif;' +
                'LPMQ Isep Misbah/LPMQ Isep Misbah, Amiri, serif;' + // Kemenag Font
                'Amiri/Amiri, serif;' +
                'Lexend/Lexend, sans-serif;' +
                'Verdana',
            fontSize_defaultLabel: '12px',
            font_defaultLabel: 'Arial', // Default font

            on: {
                instanceReady: function(evt) {
                    var editorBody = this.document.getBody();
                    editorBody.setStyle('background-color', '#fff');
                    editorBody.setStyle('width', '210mm');
                    editorBody.setStyle('min-height', '297mm');
                    editorBody.setStyle('margin', '20px auto');
                    editorBody.setStyle('padding', '20mm');
                    editorBody.setStyle('box-shadow', '0 0 10px rgba(0,0,0,0.2)');
                    editorBody.setStyle('border', '1px solid #ccc');

                    // Inject CSS into the editor head for body styling context
                    var head = this.document.getHead();
                    head.appendHtml('<style>body{background-color: #f1f5f9 !important;} p{margin-bottom:1em;}</style>');
                }
            },

            // Toolbar - Full Features
            toolbar: [
                { name: 'document', items: [ 'Source', '-', 'Preview', 'Print' ] },
                { name: 'clipboard', items: [ 'Undo', 'Redo' ] },
                { name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll' ] },
                '/',
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
                { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
                { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak' ] },
                '/',
                { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] }
            ],

            // Fonts
            font_names: 'Amiri/Amiri, serif;' + CKEDITOR.config.font_names,
            extraAllowedContent: 'style;*[id,rel](*)',
            language: 'id'
        });
    };

    function insertVar(code) {
        CKEDITOR.instances.editor.insertHtml(code);
    }
</script>
@endsection

