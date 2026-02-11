@extends('layouts.app')

@section('title', 'Manajemen Data Guru')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div class="flex flex-col gap-2 max-w-2xl">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Manajemen Data Guru</h1>
            <p class="text-slate-500 dark:text-slate-400">Kelola anggota fakultas, penugasan, dan beban kerja untuk tingkat MI dan MTs.</p>
        </div>
        <div class="flex gap-3" x-data="{ openImport: false, openCreate: false }">
            <button @click="openCreate = true" class="flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-primary text-white text-sm font-bold shadow-md hover:bg-primary-dark transition-all">
                <span class="material-symbols-outlined text-[20px]">add</span>
                <span>Tambah Guru</span>
            </button>
            <button @click="openImport = true" class="flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-semibold shadow-sm hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-[20px]">upload_file</span>
                <span>Import Excel</span>
            </button>
            <form action="{{ route('master.teachers.destroy-all') }}" method="POST"
                  data-confirm-delete="true"
                  data-title="Hapus SEMUA Guru?"
                  data-message="AWAS: Semua data guru dan akun loginnya akan DIHAPUS PERMANEN. Tindakan ini tidak bisa dibatalkan.">
                @csrf
                @method('DELETE')
                <button type="submit" class="flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-red-100 text-red-700 border border-red-200 text-sm font-bold shadow-sm hover:bg-red-200 transition-all">
                    <span class="material-symbols-outlined text-[20px]">delete_forever</span>
                    <span>Hapus Semua</span>
                </button>
            </form>

            <!-- Error Alert -->
            @if(session('import_errors'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                <strong class="font-bold">Import Selesai dengan Catatan:</strong>
                <ul class="list-disc list-inside text-sm mt-1">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- IMPORT MODAL -->
            <div x-show="openImport" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-cloak>
                 <div @click.outside="openImport = false" class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-2xl w-full max-w-md p-6 border border-slate-200 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Import Data Guru</h3>
                    <form action="{{ route('master.teachers.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="space-y-2">
                             <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">File CSV/Excel</label>
                             <input type="file" name="file" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                             <div class="bg-primary/5 p-3 rounded-lg text-xs text-primary space-y-1">
                                <p class="font-bold">Format Kolom (Lengkap 11 Kolom):</p>
                                <p>1.NIK*, 2.Nama, 3.Gender(L/P), 4.Tempat Lhr, 5.Tgl Lhr, 6.Alamat, 7.NPWP*, 8.Pend.Terakhir, 9.Riwayat Pesantren, 10.Mapel Ajar, 11.Email</p>
                                <p class="italic text-[10px] mt-1">* Gunakan tanda kutip (') di Excel untuk NIK/NPWP agar Text.</p>
                             </div>
                        </div>
                        <div class="pt-2 flex gap-2">
                             <a href="{{ route('master.teachers.template') }}" class="flex-1 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-lg text-center text-sm hover:bg-slate-200">Download Template</a>
                             <button type="submit" class="flex-1 py-2.5 bg-primary text-white font-bold rounded-lg text-sm hover:bg-primary-dark">Upload & Proses</button>
                        </div>
                    </form>
                 </div>
            </div>

            <!-- CREATE MODAL -->
            <div x-show="openCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-cloak>
                 <div @click.outside="openCreate = false" class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-2xl w-full max-w-2xl border border-slate-200 dark:border-slate-800 flex flex-col max-h-[90vh]">

                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Tambah Guru Baru</h3>
                            <p class="text-xs text-slate-500">Lengkapi data pendaftaran guru dibawah ini.</p>
                        </div>
                        <button @click="openCreate = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <!-- Modal Body (Scrollable) -->
                    <div class="p-6 overflow-y-auto custom-scrollbar">
                        <form action="{{ route('master.teachers.store') }}" method="POST" id="createTeacherForm" class="space-y-6" enctype="multipart/form-data">
                            @csrf

                            <!-- Section: Akun Login -->
                            <div class="bg-slate-50 dark:bg-slate-900/50 p-5 rounded-xl border border-slate-200 dark:border-slate-800">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="p-1.5 bg-slate-200 text-slate-600 rounded-lg">
                                        <span class="material-symbols-outlined text-[18px]">lock</span>
                                    </div>
                                    <h4 class="font-bold text-slate-900 dark:text-white text-sm">Akun Login</h4>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Nama Lengkap & Gelar</label>
                                        <input type="text" name="name" required placeholder="Contoh: Ahmad Dahlan, S.Pd.I" class="w-full rounded-xl border-slate-200 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary text-sm placeholder:text-slate-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Email (Username)</label>
                                        <input type="email" name="email" required placeholder="email@sekolah.sch.id" class="w-full rounded-xl border-slate-200 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary text-sm placeholder:text-slate-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Password Default</label>
                                        <input type="text" value="123456" disabled class="w-full rounded-xl border-slate-200 bg-slate-100 text-slate-500 text-sm cursor-not-allowed">
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Data Pribadi -->
                            <div>
                                <div class="flex items-center gap-2 mb-4 mt-2">
                                    <div class="p-1.5 bg-slate-100 text-slate-600 rounded-lg">
                                        <span class="material-symbols-outlined text-[18px]">person</span>
                                    </div>
                                    <h4 class="font-bold text-slate-900 dark:text-white text-sm">Identitas & Riwayat</h4>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Photo Upload -->
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Foto Profil (Opsional)</label>
                                        <input type="file" name="foto" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all border border-slate-200 dark:border-slate-700 rounded-xl">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">NIK</label>
                                        <input type="text" name="nik" placeholder="Ketik NIK..." class="w-full rounded-xl border-slate-200 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">NPWP (Opsional)</label>
                                        <input type="text" name="npwp" class="w-full rounded-xl border-slate-200 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" required class="w-full rounded-xl border-slate-200 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary text-sm">
                                            <option value="">Pilih...</option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-span-1 md:col-span-2 space-y-3" x-data="{ education: [] }">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-xs font-bold text-slate-500 uppercase">Riwayat Pendidikan</label>
                                            <button type="button" @click="education.push({id: Date.now(), jenjang: '', nama: '', masuk: '', lulus: ''})" class="text-xs flex items-center gap-1 text-primary font-bold hover:underline">
                                                <span class="material-symbols-outlined text-[14px]">add</span> Tambah
                                            </button>
                                        </div>

                                        <!-- List Pendidikan -->
                                        <template x-for="(item, index) in education" :key="item.id">
                                            <div class="grid grid-cols-12 gap-2 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 items-end">
                                                <div class="col-span-3">
                                                    <label class="text-[10px] text-slate-400 block mb-1">Jenjang</label>
                                                    <select :name="'pendidikan['+index+'][jenjang]'" class="w-full rounded-lg border-slate-200 text-xs py-1.5 dark:bg-slate-800 dark:border-slate-700">
                                                        <option value="SD">SD/MI</option>
                                                        <option value="SMP">SMP/Mts</option>
                                                        <option value="SMA">SMA/MA/SMK</option>
                                                        <option value="S1">S1</option>
                                                        <option value="S2">S2</option>
                                                        <option value="S3">S3</option>
                                                        <option value="Pesantren">Pesantren</option>
                                                        <option value="Lainnya">Lainnya</option>
                                                    </select>
                                                </div>
                                                <div class="col-span-4">
                                                    <label class="text-[10px] text-slate-400 block mb-1">Nama Instansi</label>
                                                    <input type="text" :name="'pendidikan['+index+'][nama_instansi]'" placeholder="Nama Sekolah/Ponpes" class="w-full rounded-lg border-slate-200 text-xs py-1.5 dark:bg-slate-800 dark:border-slate-700">
                                                </div>
                                                <div class="col-span-2">
                                                    <label class="text-[10px] text-slate-400 block mb-1">Masuk</label>
                                                    <input type="number" :name="'pendidikan['+index+'][tahun_masuk]'" placeholder="YYYY" class="w-full rounded-lg border-slate-200 text-xs py-1.5 dark:bg-slate-800 dark:border-slate-700">
                                                </div>
                                                <div class="col-span-2">
                                                    <label class="text-[10px] text-slate-400 block mb-1">Lulus</label>
                                                    <input type="number" :name="'pendidikan['+index+'][tahun_lulus]'" placeholder="YYYY" class="w-full rounded-lg border-slate-200 text-xs py-1.5 dark:bg-slate-800 dark:border-slate-700">
                                                </div>
                                                <div class="col-span-1 text-center">
                                                     <button type="button" @click="education = education.filter(i => i.id !== item.id)" class="text-red-500 hover:text-red-700">
                                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>

                                        <div x-show="education.length === 0" class="text-center py-4 border-2 border-dashed border-slate-200 rounded-lg text-slate-400 text-xs">
                                            Belum ada data pendidikan. Klik "Tambah" di atas.
                                        </div>
                                    </div>

                                     <div class="col-span-1 md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Mapel yang diajarkan</label>
                                        <input type="text" name="mapel_ajar_text" placeholder="Contoh: Matematika, Fiqih" class="w-full rounded-xl border-slate-200 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary text-sm">
                                        <p class="text-[10px] text-slate-400 mt-1 ml-1">Hanya catatan referensi (Text).</p>
                                    </div>

                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Alamat Lengkap</label>
                                        <textarea name="alamat" rows="2" class="w-full rounded-xl border-slate-200 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary text-sm"></textarea>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3 rounded-b-xl">
                         <button type="button" @click="openCreate = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl text-sm hover:bg-slate-50 transition-all shadow-sm">
                             Batal
                         </button>
                         <button type="submit" form="createTeacherForm" class="px-5 py-2.5 bg-primary text-white font-bold rounded-xl text-sm hover:bg-primary-dark transition-all shadow-md shadow-primary/20 flex items-center gap-2">
                             <span class="material-symbols-outlined text-[18px]">check_circle</span>
                             Simpan Data Guru
                         </button>
                    </div>

                 </div>
            </div>

        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama Guru</th>
                            <th scope="col" class="px-6 py-3">NIK / Identitas</th>
                            <th scope="col" class="px-6 py-3">Mapel Ajar (Ket)</th>
                            <th scope="col" class="px-6 py-3">No. HP</th>
                            <th scope="col" class="px-6 py-3">Status Akun</th>
                            <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $teacher)
                        <tr class="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden">
                                        @if($teacher->data_guru && $teacher->data_guru->foto)
                                            <img src="{{ asset($teacher->data_guru->foto) }}" class="h-full w-full object-cover">
                                        @else
                                            {{ substr($teacher->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('master.teachers.show', $teacher->id) }}" class="font-bold hover:text-primary hover:underline transition-colors">{{ $teacher->name }}</a>
                                        <div class="text-xs text-slate-500">{{ $teacher->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($teacher->data_guru)
                                    <div class="space-y-1">
                                        @if($teacher->data_guru->nik)
                                            <div class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 text-[10px] inline-block font-mono">
                                                {{ $teacher->data_guru->nik }}
                                            </div>
                                        @else
                                            <div class="text-xs italic text-slate-400">- Belum ada NIK -</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Data Profil Belum Lengkap</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($teacher->data_guru && $teacher->data_guru->mapel_ajar_text)
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $teacher->data_guru->mapel_ajar_text }}</span>
                                    @if($teacher->data_guru->pendidikan_terakhir)
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $teacher->data_guru->pendidikan_terakhir }}</div>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $teacher->data_guru->no_hp ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100 text-xs">
                                    Aktif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Detail/Edit Button -->
                                    <a href="{{ route('master.teachers.show', $teacher->id) }}" class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors" title="Lihat Profil / Edit">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <!-- Delete Button -->
                                    <form action="{{ route('master.teachers.destroy', $teacher->id) }}" method="POST"
                                          data-confirm-delete="true"
                                          data-title="Hapus Guru Ini?"
                                          data-message="Data profil dan akun login guru ini akan dihapus.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors" title="Hapus">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">school</span>
                                    <p>Belum ada data guru.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $teachers->links() }}
        </div>
    </div>
</div>

<!-- Credential Modal -->
@if(session('generated_credential'))
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-data="{ open: true }" x-show="open">
    <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-2xl w-full max-w-md p-6 border border-slate-200 dark:border-slate-800">
        <div class="text-center mb-6">
            <div class="bg-green-100 text-green-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl">check_circle</span>
            </div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Akun Berhasil Digenerate!</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Silakan catat atau bagikan kredensial berikut kepada guru yang bersangkutan.</p>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-lg border border-slate-100 dark:border-slate-800 space-y-3 mb-6">
            <div>
                <label class="text-xs font-bold text-slate-400 uppercase">Nama Guru</label>
                <div class="font-medium text-slate-900 dark:text-white">{{ session('generated_credential')['name'] }}</div>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-400 uppercase">Email Login</label>
                <div class="font-mono text-lg font-bold text-primary">{{ session('generated_credential')['email'] }}</div>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-400 uppercase">Password Baru</label>
                <div class="font-mono text-lg font-bold text-slate-900 dark:text-white bg-white dark:bg-slate-800 px-3 py-1 rounded border border-slate-200 dark:border-slate-700">
                    {{ session('generated_credential')['password'] }}
                </div>
            </div>
        </div>

        <button @click="open = false" class="w-full py-2.5 bg-slate-900 dark:bg-slate-700 text-white font-bold rounded-lg hover:bg-slate-800 transition-all">
            Tutup
        </button>
    </div>
</div>
@endif
@endsection
