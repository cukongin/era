@extends('layouts.app')

@section('title', 'Detail Guru - ' . $teacher->name)

@section('content')
<div class="flex flex-col gap-6" x-data="{ activeTab: 'profil' }">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('master.teachers.index') }}" class="hover:text-primary transition-colors">Data Guru</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="text-slate-900 dark:text-white font-medium">Detail Profile</span>
    </div>

    <!-- Header Card -->
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex flex-col md:flex-row items-center gap-6">
        <div class="size-20 rounded-full bg-primary/10 flex items-center justify-center text-primary text-3xl font-bold overflow-hidden border-2 border-slate-100 dark:border-slate-700">
            @if($teacher->data_guru->foto)
                <img src="{{ asset('public/' . $teacher->data_guru->foto) }}" class="w-full h-full object-cover">
            @else
                {{ substr($teacher->name, 0, 1) }}
            @endif
        </div>
        <div class="flex-1 text-center md:text-left">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $teacher->name }}</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mb-2">{{ $teacher->email }}</p>
            <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                @if($teacher->kelas_wali)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="material-symbols-outlined text-[16px]">star</span>
                    Wali Kelas {{ $teacher->kelas_wali->nama_kelas }}
                </span>
                @endif
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    NIP: {{ $teacher->data_guru->nip ?? '-' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-slate-200 dark:border-slate-800">
        <nav class="-mb-px flex space-x-6">
            <button @click="activeTab = 'profil'" :class="{ 'border-primary text-primary': activeTab === 'profil', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': activeTab !== 'profil' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">person</span> Profil
            </button>
            <button @click="activeTab = 'beban'" :class="{ 'border-primary text-primary': activeTab === 'beban', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': activeTab !== 'beban' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">school</span> Beban Mengajar (Aktif)
            </button>
            <button @click="activeTab = 'riwayat'" :class="{ 'border-primary text-primary': activeTab === 'riwayat', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': activeTab !== 'riwayat' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">history</span> Riwayat
            </button>
            <button @click="activeTab = 'keamanan'" :class="{ 'border-primary text-primary': activeTab === 'keamanan', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': activeTab !== 'keamanan' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">lock</span> Keamanan
            </button>
        </nav>
    </div>

    <!-- Tab Contents -->
    
    <!-- 1. Profil -->
    <div x-data="{ isEditing: false }" x-show="activeTab === 'profil'" class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-slate-900 dark:text-white">Informasi Pribadi</h3>
            <button x-show="!isEditing" @click="isEditing = true" class="text-sm text-primary hover:underline font-medium">Edit Profil</button>
        </div>

        <!-- View Mode -->
        <dl x-show="!isEditing" class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6 text-sm">
            <div>
                <dt class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Nama Lengkap</dt>
                <dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $teacher->name }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Email</dt>
                <dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $teacher->email }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 text-xs uppercase tracking-wider font-semibold">NUPTK</dt>
                <dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $teacher->data_guru->nuptk ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 text-xs uppercase tracking-wider font-semibold">NIP</dt>
                <dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $teacher->data_guru->nip ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Jenis Kelamin</dt>
                <dd class="font-medium text-slate-900 dark:text-white mt-1">
                    {{ $teacher->data_guru->jenis_kelamin == 'L' ? 'Laki-laki' : ($teacher->data_guru->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}
                </dd>
            </div>
            <div>
                <dt class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Tempat, Tanggal Lahir</dt>
                <dd class="font-medium text-slate-900 dark:text-white mt-1">
                    {{ $teacher->data_guru->tempat_lahir ? $teacher->data_guru->tempat_lahir . ', ' : '' }}
                    {{ $teacher->data_guru->tanggal_lahir ? \Carbon\Carbon::parse($teacher->data_guru->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-slate-500 text-xs uppercase tracking-wider font-semibold">No HP</dt>
                <dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $teacher->data_guru->no_hp ?? '-' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Alamat</dt>
                <dd class="font-medium text-slate-900 dark:text-white mt-1">{{ $teacher->data_guru->alamat ?? '-' }}</dd>
            </div>
        </dl>

        <!-- Edit Mode -->
        <form x-show="isEditing" action="{{ route('master.teachers.update', $teacher->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6"
              x-data="{ 
                  education: {{ $teacher->data_guru && $teacher->data_guru->riwayat_pendidikan ? $teacher->data_guru->riwayat_pendidikan->toJson() : '[]' }} 
              }">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Foto Profil</label>
                    <input type="file" name="foto" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ $teacher->name }}" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ $teacher->email }}" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">NIK</label>
                    <input type="text" name="nik" value="{{ $teacher->data_guru->nik ?? '' }}" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">NPWP</label>
                    <input type="text" name="npwp" value="{{ $teacher->data_guru->npwp ?? '' }}" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="">- Pilih -</option>
                        <option value="L" {{ ($teacher->data_guru->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ ($teacher->data_guru->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">No HP</label>
                    <input type="text" name="no_hp" value="{{ $teacher->data_guru->no_hp ?? '' }}" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ $teacher->data_guru->tempat_lahir ?? '' }}" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                <div>
                     <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ $teacher->data_guru->tanggal_lahir ? \Carbon\Carbon::parse($teacher->data_guru->tanggal_lahir)->format('Y-m-d') : '' }}" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                
                <!-- Expanded Education Fields -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Pendidikan Terakhir (Text)</label>
                    <input type="text" name="pendidikan_terakhir" value="{{ $teacher->data_guru->pendidikan_terakhir ?? '' }}" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Mapel Ajar (Text Referensi)</label>
                    <input type="text" name="mapel_ajar_text" value="{{ $teacher->data_guru->mapel_ajar_text ?? '' }}" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Riwayat Pesantren (Text)</label>
                    <textarea name="riwayat_pesantren" rows="2" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">{{ $teacher->data_guru->riwayat_pesantren ?? '' }}</textarea>
                </div>

                 <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:ring-primary focus:border-primary">{{ $teacher->data_guru->alamat ?? '' }}</textarea>
                </div>
            </div>

            <!-- Dynamic Education History -->
            <div class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Riwayat Pendidikan Terstruktur</label>
                    <button type="button" @click="education.push({id: Date.now(), jenjang: '', nama_instansi: '', tahun_masuk: '', tahun_lulus: ''})" class="text-xs flex items-center gap-1 text-primary font-bold hover:underline">
                        <span class="material-symbols-outlined text-[16px]">add_circle</span> Tambah
                    </button>
                </div>
                
                <template x-for="(item, index) in education" :key="index">
                    <div class="grid grid-cols-12 gap-2 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 items-end">
                        <div class="col-span-3">
                            <label class="text-[10px] text-slate-400 block mb-1">Jenjang</label>
                            <select :name="'pendidikan['+index+'][jenjang]'" x-model="item.jenjang" class="w-full rounded-lg border-slate-200 text-xs py-1.5 dark:bg-slate-800 dark:border-slate-700">
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
                            <input type="text" :name="'pendidikan['+index+'][nama_instansi]'" x-model="item.nama_instansi" placeholder="Nama Sekolah..." class="w-full rounded-lg border-slate-200 text-xs py-1.5 dark:bg-slate-800 dark:border-slate-700">
                        </div>
                        <div class="col-span-2">
                            <label class="text-[10px] text-slate-400 block mb-1">Masuk</label>
                            <input type="text" :name="'pendidikan['+index+'][tahun_masuk]'" x-model="item.tahun_masuk" placeholder="Thn" class="w-full rounded-lg border-slate-200 text-xs py-1.5 dark:bg-slate-800 dark:border-slate-700">
                        </div>
                        <div class="col-span-2">
                            <label class="text-[10px] text-slate-400 block mb-1">Lulus</label>
                            <input type="text" :name="'pendidikan['+index+'][tahun_lulus]'" x-model="item.tahun_lulus" placeholder="Thn" class="w-full rounded-lg border-slate-200 text-xs py-1.5 dark:bg-slate-800 dark:border-slate-700">
                        </div>
                        <div class="col-span-1 text-center">
                                <button type="button" @click="education.splice(index, 1)" class="text-red-500 hover:text-red-700">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="education.length === 0" class="text-center py-4 border-2 border-dashed border-slate-200 rounded-lg text-slate-400 text-xs">
                    Belum ada data pendidikan.
                </div>
            </div>

            <div class="md:col-span-2 flex justify-end gap-2 mt-2 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" @click="isEditing = false" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-primary rounded-lg hover:bg-green-600 shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <!-- 2. Beban Mengajar -->
    <div x-show="activeTab === 'beban'" class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col min-h-[500px]">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-bold text-lg text-slate-900 dark:text-white">Beban Mengajar Aktif</h3>
            <p class="text-sm text-slate-500">Tahun Ajaran: <span class="font-bold text-slate-700">{{ $activeYear->nama }}</span></p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase text-slate-500 font-medium border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Mata Pelajaran</th>
                        <th class="px-6 py-4">Kelas</th>
                        <th class="px-6 py-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($teacher->mapel_ajar->where('kelas.id_tahun_ajaran', $activeYear->id) as $assignment)
                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded bg-slate-100 flex items-center justify-center text-slate-600">
                                    <span class="material-symbols-outlined text-[18px]">menu_book</span>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $assignment->mapel->nama_mapel }}</p>
                                    <p class="text-xs text-slate-500">{{ $assignment->mapel->kode }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $assignment->kelas?->nama_kelas ?? '-' }}</span>
                            <span class="text-xs text-slate-500 block">{{ $assignment->kelas?->jenjang?->kode ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                <span class="size-1.5 rounded-full bg-green-500"></span> Aktif
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-slate-400 italic">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-50">event_busy</span>
                                <span>Tidak ada beban mengajar di tahun aktif ini.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Riwayat -->
    <div x-show="activeTab === 'riwayat'" class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col min-h-[500px]">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-bold text-lg text-slate-900 dark:text-white">Riwayat Mengajar</h3>
            <p class="text-sm text-slate-500">Arsip pengajaran tahun-tahun sebelumnya.</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase text-slate-500 font-medium border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Tahun Ajaran</th>
                        <th class="px-6 py-4">Mata Pelajaran</th>
                        <th class="px-6 py-4">Kelas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($teacher->mapel_ajar->where('kelas.id_tahun_ajaran', '!=', $activeYear->id) as $assignment)
                    <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                             <span class="px-2 py-1 rounded text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                {{ $assignment->kelas?->tahun_ajaran?->nama ?? '-' }}
                             </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-900 dark:text-white">{{ $assignment->mapel->nama_mapel }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-slate-600">{{ $assignment->kelas?->nama_kelas ?? '-' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-slate-400 italic">
                             Belum ada riwayat mengajar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. Keamanan -->
    <div x-show="activeTab === 'keamanan'" class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 max-w-2xl">
        <h3 class="font-bold text-slate-900 dark:text-white mb-6">Ganti Password</h3>
        <form action="{{ route('master.teachers.password', $teacher->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Password Baru</label>
                <div class="mt-2 relative">
                    <input type="password" name="password" required class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700">
                </div>
                <p class="mt-1 text-xs text-slate-500">Minimal 6 karakter.</p>
            </div>
            <div>
                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Konfirmasi Password</label>
                <div class="mt-2 text-sm text-slate-900 dark:text-white">
                    <input type="password" name="password_confirmation" required class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-900 dark:text-white dark:ring-slate-700">
                </div>
            </div>
            
            <div class="pt-4 flex justify-end">
                <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                    Update Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

