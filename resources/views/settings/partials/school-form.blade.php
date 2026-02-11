<form action="{{ route('settings.school.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="jenjang" value="{{ $jenjang }}">
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left: Logo & Basic Info -->
        <div class="md:col-span-1 flex flex-col gap-6">
            <!-- Logo Card -->
            <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 flex flex-col items-center">
                <div class="w-32 h-32 rounded-lg bg-slate-50 dark:bg-slate-800 border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center mb-4 overflow-hidden relative group">
                    @if($school->logo)
                        <img src="{{ asset($school->logo) }}" class="w-full h-full object-contain p-2">
                    @else
                        <span class="material-symbols-outlined text-4xl text-slate-300">image</span>
                    @endif
                    
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <span class="text-white text-xs font-bold">Ganti Logo</span>
                    </div>
                    <input type="file" name="logo" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                </div>
                <p class="text-xs text-slate-400 text-center">Logo {{ $jenjang }}<br>Format: PNG/JPG (Max 2MB).</p>
            </div>

            <!-- Codes -->
            <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">NSM</label>
                    <input type="text" name="nsm" value="{{ $school->nsm }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">NPSN</label>
                    <input type="text" name="npsn" value="{{ $school->npsn }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                </div>
            </div>
        </div>

        <!-- Right: Details -->
        <div class="md:col-span-2 bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2 px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                <span class="material-symbols-outlined text-primary">domain</span>
                <h3 class="font-bold text-slate-900 dark:text-white">Detail Instansi ({{ $jenjang }})</h3>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Madrasah</label>
                    <input type="text" name="nama_sekolah" value="{{ $school->nama_sekolah }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 font-bold focus:ring-primary focus:border-primary">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kepala Madrasah</label>
                        <input type="text" name="kepala_madrasah" value="{{ $school->kepala_madrasah }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">NIP Kepala</label>
                        <input type="text" name="nip_kepala" value="{{ $school->nip_kepala }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">{{ $school->alamat }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                        <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Desa/Kelurahan</label>
                        <input type="text" name="desa" value="{{ $school->desa }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kecamatan</label>
                        <input type="text" name="kecamatan" value="{{ $school->kecamatan }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kabupaten/Kota</label>
                        <input type="text" name="kabupaten" value="{{ $school->kabupaten }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Provinsi</label>
                        <input type="text" name="provinsi" value="{{ $school->provinsi }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">No. Telp</label>
                        <input type="text" name="no_telp" value="{{ $school->no_telp }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Website</label>
                        <input type="text" name="website" value="{{ $school->website }}" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                    </div>
                </div>


            </div>
            
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-bold shadow hover:bg-green-600 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">save</span> Simpan Identitas {{ $jenjang }}
                </button>
            </div>
        </div>
    </div>
</form>

