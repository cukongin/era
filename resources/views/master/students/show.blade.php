@extends('layouts.app')

@section('title', 'Detail Data Siswa')

@section('content')
<div class="max-w-6xl mx-auto flex flex-col gap-8 font-sans" x-data="{ isEditing: false }">
    
    <!-- Top Configuration / Back -->
    <div class="flex items-center justify-between no-print">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('master.students.index') }}" class="hover:text-primary transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Data Siswa
            </a>
            <span>/</span>
            <span class="text-slate-900 font-medium">Detail Buku Induk</span>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-lg font-bold flex items-center gap-2 hover:bg-slate-50 transition-colors">
                <span class="material-symbols-outlined text-[20px]">print</span> Cetak Buku Induk (PDF)
            </button>
        </div>
    </div>

    <form action="{{ route('master.students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Header Card: Identity -->
        <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-800 p-8 shadow-sm flex flex-col md:flex-row gap-8 relative overflow-hidden">
             <!-- Decorative Top Bar -->
             <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-green-400"></div>

             <!-- Photo -->
             <div class="flex-shrink-0 relative group">
                <div class="w-40 h-48 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 shadow-inner flex items-center justify-center relative">
                    @if($student->foto)
                        <img src="{{ asset('public/' . $student->foto) }}" class="w-full h-full object-cover">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($student->nama_lengkap) }}&background=random&size=200" class="w-full h-full object-cover opacity-80">
                    @endif
                    
                    <!-- Edit Photo Overlay -->
                    <div x-show="isEditing" class="absolute inset-0 bg-black/50 flex flex-col items-center justify-center text-white cursor-pointer hover:bg-black/60 transition-colors">
                        <span class="material-symbols-outlined text-3xl mb-1">upload</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Ubah Foto</span>
                        <input type="file" name="foto" class="absolute inset-0 opacity-0 cursor-pointer text-[0px]" accept="image/*">
                    </div>
                </div>
                
                <!-- Active Badge -->
                <div class="absolute -bottom-3 -right-3">
                     <span class="px-3 py-1 rounded-full text-xs font-bold border-2 border-white shadow-sm {{ $student->status_siswa == 'aktif' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                        {{ strtoupper($student->status_siswa) }}
                    </span>
                </div>
             </div>

             <!-- Main Info -->
             <div class="flex-1 flex flex-col justify-center">
                 <div class="flex justify-between items-start">
                     <div>
                         <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2 tracking-tight">
                            <span x-show="!isEditing">{{ $student->nama_lengkap }}</span>
                            <input x-show="isEditing" type="text" name="nama_lengkap" value="{{ $student->nama_lengkap }}" class="text-2xl font-bold border-b-2 border-primary bg-transparent focus:outline-none w-full">
                         </h1>
                         <div class="flex items-center gap-4 text-sm text-slate-500 mb-6">
                             <div class="flex items-center gap-1">
                                 <span class="material-symbols-outlined text-[18px]">badge</span>
                                 <span>NIS: {{ $student->nis_lokal ?? '-' }}</span>
                             </div>
                             <div class="flex items-center gap-1">
                                 <span class="material-symbols-outlined text-[18px]">fingerprint</span>
                                 <span>NISN: 
                                     <span x-show="!isEditing">{{ $student->nisn ?? '-' }}</span>
                                     <input x-show="isEditing" type="text" name="nisn" value="{{ $student->nisn }}" class="w-32 border-b border-slate-300 py-0 px-1 ml-1 text-slate-900 focus:outline-none focus:border-primary">
                                 </span>
                             </div>
                         </div>
                     </div>
                     
                     <!-- Edit Button -->
                     <div class="no-print">
                        <template x-if="!isEditing">
                            <button type="button" @click="isEditing = true" class="text-primary hover:text-green-700 font-bold flex items-center gap-1 text-sm bg-primary/10 px-3 py-1.5 rounded-full transition-colors">
                                <span class="material-symbols-outlined text-[16px]">edit</span> Edit Data
                            </button>
                        </template>
                        <template x-if="isEditing">
                            <div class="flex gap-2">
                                <button type="button" @click="isEditing = false" class="text-slate-500 hover:text-slate-700 font-bold flex items-center gap-1 text-sm bg-slate-100 px-3 py-1.5 rounded-full transition-colors">
                                    Batal
                                </button>
                                <button type="submit" class="text-white hover:bg-green-700 font-bold flex items-center gap-1 text-sm bg-primary px-4 py-1.5 rounded-full transition-colors shadow-lg shadow-primary/30">
                                    <span class="material-symbols-outlined text-[16px]">save</span> Simpan
                                </button>
                            </div>
                        </template>
                     </div>
                 </div>

                 <!-- Grid Stats -->
                 <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-800">
                     <div>
                         <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Kelas Saat Ini</span>
                         <span class="text-lg font-bold text-slate-800 dark:text-white">{{ $student->kelas_saat_ini->kelas->nama_kelas ?? '-' }}</span>
                     </div>
                     <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Tahun Masuk</span>
                        <span class="text-lg font-bold text-slate-800 dark:text-white">
                            <span x-show="!isEditing">{{ $student->tahun_masuk ?? date('Y') }}</span>
                            <input x-show="isEditing" type="number" name="tahun_masuk" value="{{ $student->tahun_masuk }}" class="w-20 bg-white border border-slate-200 rounded px-1 py-0.5 text-sm">
                        </span>
                    </div>
                     <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Wali Kelas</span>
                        <span class="text-lg font-bold text-slate-800 dark:text-white text-sm truncate">{{ $student->kelas_saat_ini->kelas->wali_kelas->name ?? '-' }}</span>
                    </div>
                     <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Jenjang</span>
                        <span class="text-lg font-bold text-slate-800 dark:text-white">{{ optional($student->jenjang)->kode ?? '-' }}</span>
                    </div>
                 </div>
             </div>
        </div>

        <!-- Section: Biodata Pribadi -->
        <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-800 p-8 shadow-sm">
             <div class="flex items-center gap-3 mb-6 border-b border-slate-100 dark:border-slate-800 pb-4">
                 <span class="material-symbols-outlined text-green-500 bg-green-100 p-2 rounded-lg">person_book</span>
                 <h2 class="text-lg font-bold text-slate-900 dark:text-white">Biodata Pribadi</h2>
             </div>

             <div class="grid grid-cols-1 md:grid-cols-3 gap-y-8 gap-x-12">
                 <!-- Row 1 -->
                 <div>
                     <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Tempat, Tanggal Lahir</label>
                     <div x-show="!isEditing" class="text-slate-800 dark:text-white font-medium text-base">
                        {{ $student->tempat_lahir }}, {{ $student->tanggal_lahir ? \Carbon\Carbon::parse($student->tanggal_lahir)->translatedFormat('d F Y') : '' }}
                     </div>
                     <div x-show="isEditing" class="flex gap-2">
                        <input type="text" name="tempat_lahir" value="{{ $student->tempat_lahir }}" placeholder="Tempat" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                        <input type="date" name="tanggal_lahir" value="{{ $student->tanggal_lahir }}" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                     </div>
                 </div>
                 <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Jenis Kelamin</label>
                    <div x-show="!isEditing" class="text-slate-800 dark:text-white font-medium text-base">
                       {{ $student->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </div>
                    <select x-show="isEditing" name="jenis_kelamin" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                        <option value="L" {{ $student->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ $student->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                 <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">NIK</label>
                    <div x-show="!isEditing" class="text-slate-800 dark:text-white font-medium text-base">
                       {{ $student->nik ?? '-' }}
                    </div>
                    <input x-show="isEditing" type="text" name="nik" value="{{ $student->nik }}" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                </div>

                <!-- Row 2 -->
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Alamat Lengkap</label>
                    <div x-show="!isEditing" class="text-slate-800 dark:text-white font-medium text-base leading-relaxed">
                       {{ $student->alamat_lengkap ?? '-' }}
                    </div>
                    <textarea x-show="isEditing" name="alamat_lengkap" rows="2" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">{{ $student->alamat_lengkap }}</textarea>
                </div>
                <div>
                     <!-- Placeholder for Agama/WargaNegara if data exists later -->
                     <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Golongan Darah</label>
                     <div class="text-slate-800 dark:text-white font-medium text-base">-</div>
                </div>
             </div>
        </div>

        <!-- Section: Data Orang Tua -->
        <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-800 p-8 shadow-sm">
             <div class="flex items-center gap-3 mb-6 border-b border-slate-100 dark:border-slate-800 pb-4">
                 <span class="material-symbols-outlined text-indigo-500 bg-indigo-100 p-2 rounded-lg">family_restroom</span>
                 <h2 class="text-lg font-bold text-slate-900 dark:text-white">Data Orang Tua / Wali</h2>
             </div>

             <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Father -->
                <div class="bg-slate-50 dark:bg-slate-800/30 p-6 rounded-lg border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2 mb-4 text-indigo-600 font-bold text-sm uppercase">
                        <span class="material-symbols-outlined text-[18px]">man</span> Data Ayah
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-slate-400 block mb-1">Nama Lengkap</label>
                            <div x-show="!isEditing" class="font-bold text-slate-800">{{ $student->nama_ayah ?? '-' }}</div>
                            <input x-show="isEditing" type="text" name="nama_ayah" value="{{ $student->nama_ayah }}" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                        </div>
                        <div>
                             <label class="text-xs text-slate-400 block mb-1">Pekerjaan</label>
                             <div x-show="!isEditing" class="font-bold text-slate-800">{{ $student->pekerjaan_ayah ?? '-' }}</div>
                             <input x-show="isEditing" type="text" name="pekerjaan_ayah" value="{{ $student->pekerjaan_ayah }}" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Mother -->
                <div class="bg-slate-50 dark:bg-slate-800/30 p-6 rounded-lg border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2 mb-4 text-pink-600 font-bold text-sm uppercase">
                        <span class="material-symbols-outlined text-[18px]">woman</span> Data Ibu
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs text-slate-400 block mb-1">Nama Lengkap</label>
                            <div x-show="!isEditing" class="font-bold text-slate-800">{{ $student->nama_ibu ?? '-' }}</div>
                             <input x-show="isEditing" type="text" name="nama_ibu" value="{{ $student->nama_ibu }}" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                        </div>
                         <div>
                             <label class="text-xs text-slate-400 block mb-1">Pekerjaan</label>
                             <div x-show="!isEditing" class="font-bold text-slate-800">{{ $student->pekerjaan_ibu ?? '-' }}</div>
                             <input x-show="isEditing" type="text" name="pekerjaan_ibu" value="{{ $student->pekerjaan_ibu }}" class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Kontak / No. Telepon</label>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-green-500">call</span>
                         <div x-show="!isEditing" class="text-lg font-bold text-slate-800">{{ $student->no_telp_ortu ?? '-' }}</div>
                         <input x-show="isEditing" type="text" name="no_telp_ortu" value="{{ $student->no_telp_ortu }}" class="border border-slate-300 rounded px-2 py-1 text-sm w-64">
                    </div>
                </div>
             </div>
        </div>
    </form>

    <!-- Section: Rekap Nilai Tahunan -->
    <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-800 p-8 shadow-sm break-inside-avoid">
            <div class="flex items-center gap-3 mb-6 border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="material-symbols-outlined text-green-500 bg-green-100 p-2 rounded-lg">table_chart</span>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Rekap Nilai Tahunan</h2>
                <div class="ml-auto flex gap-2">
                    <span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded">Lulus KKM</span>
                    <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded">Di Bawah KKM</span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <div x-data="{}">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-800 dark:text-slate-400">
                            <tr>
                                <th scope="col" class="px-6 py-3 rounded-l-lg">No</th>
                                <th scope="col" class="px-6 py-3">Tahun Ajaran</th>
                                <th scope="col" class="px-6 py-3">Tingkat Kelas</th>
                                <th scope="col" class="px-6 py-3 text-center">Rerata Nilai</th>
                                <th scope="col" class="px-6 py-3 rounded-r-lg text-right">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($student->riwayat_kelas as $index => $riwayat)
                            <!-- Row Clickable to Report Card (New Tab) -->
                            <tr class="bg-white dark:bg-[#1a2e22] hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors cursor-pointer group border-b border-slate-100 dark:border-slate-800">
                                
                                <td onclick="window.open('{{ route('reports.print', ['student' => $student->id, 'year_id' => $riwayat->kelas->id_tahun_ajaran]) }}', '_blank')" class="px-6 py-4 font-medium text-slate-900 dark:text-white group-hover:text-primary transition-colors">{{ $loop->iteration }}</td>
                                <td onclick="window.open('{{ route('reports.print', ['student' => $student->id, 'year_id' => $riwayat->kelas->id_tahun_ajaran]) }}', '_blank')" class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ $riwayat->kelas->tahun_ajaran->nama ?? '-' }}</span>
                                        <span class="text-xs text-slate-400">Semester Ganjil &amp; Genap</span>
                                    </div>
                                </td>
                                <td onclick="window.open('{{ route('reports.print', ['student' => $student->id, 'year_id' => $riwayat->kelas->id_tahun_ajaran]) }}', '_blank')" class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                    {{ $riwayat->kelas->nama_kelas }}
                                    <span class="block text-xs text-slate-400 font-normal mt-0.5">Wali: {{ $riwayat->kelas->wali_kelas->name ?? '-' }}</span>
                                </td>
                                <td onclick="window.open('{{ route('reports.print', ['student' => $student->id, 'year_id' => $riwayat->kelas->id_tahun_ajaran]) }}', '_blank')" class="px-6 py-4 text-center">
                                    @if(isset($gradeHistory[$riwayat->id_kelas]['periods']) && count($gradeHistory[$riwayat->id_kelas]['periods']) > 0)
                                        <div class="flex flex-col gap-1 w-full max-w-[140px] mx-auto">
                                            @foreach($gradeHistory[$riwayat->id_kelas]['periods'] as $period => $val)
                                                <div class="flex justify-between items-center text-xs border-b border-dashed border-slate-200 dark:border-slate-700 pb-1 last:border-0 last:pb-0">
                                                    <span class="text-slate-500">{{ $period }}</span>
                                                    <span class="font-bold {{ $val < 70 ? 'text-red-500' : 'text-slate-700 dark:text-slate-300' }}">{{ $val }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Belum ada nilai</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right relative">
                                    <div class="flex items-center justify-end gap-2">
                                        <div onclick="window.open('{{ route('reports.print', ['student' => $student->id, 'year_id' => $riwayat->kelas->id_tahun_ajaran]) }}', '_blank')" class="flex-1 text-right">
                                            @if($riwayat->status == 'aktif')
                                                <span class="text-xs font-bold text-blue-600 bg-blue-100 px-3 py-1 rounded-full border border-blue-200">AKTIF</span>
                                            @elseif($riwayat->status == 'lulus' || $riwayat->status == 'naik_kelas')
                                                <span class="text-xs font-bold text-green-600 bg-green-100 px-3 py-1 rounded-full border border-green-200">NAIK KELAS</span>
                                            @elseif($riwayat->status == 'tinggal_kelas')
                                                <span class="text-xs font-bold text-red-600 bg-red-100 px-3 py-1 rounded-full border border-red-200">TINGGAL KELAS</span>
                                            @else
                                                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">{{ strtoupper(str_replace('_', ' ', $riwayat->status)) }}</span>
                                            @endif
                                            
                                            <div class="mt-2 text-[10px] text-primary font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-end gap-1">
                                                Lihat Rapor <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Edit Status Button REMOVED as per User Request -->
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500 italic">Belum ada data riwayat pendidikan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                     <!-- Edit Status Modal REMOVED -->


                </div>
            </div>

            <!-- Graduation Status (Static Placeholder based on Logic) -->
            <div class="mt-8 bg-slate-50 dark:bg-slate-800/50 rounded-xl p-6 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">verified</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white">Status Kelulusan</h3>
                        <p class="text-sm text-slate-500">Data kelulusan santri akan muncul setelah seluruh syarat akademik terpenuhi.</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">STATUS SAAT INI</span>
                    @if($student->status_siswa == 'lulus')
                         <span class="text-xl font-bold text-green-600">LULUS</span>
                    @else
                         <span class="text-xl font-bold text-orange-500">BELUM LULUS</span>
                    @endif
                </div>
            </div>
    </div>
    
    <div class="text-center text-slate-400 text-sm mt-8 pb-8 no-print">
         &copy; {{ date('Y') }} Madrasah Digital System. Dokumen ini adalah salinan digital dari Buku Induk Siswa.
    </div>

</div>

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white; }
        .shadow-sm, .shadow-md, .shadow-lg { box-shadow: none !important; }
        .border { border-color: #ddd !important; }
    }
</style>
@endsection
