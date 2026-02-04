@extends('layouts.app')

@section('title', 'Master Data Mapel')

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Master Mata Pelajaran</h1>
            <p class="text-slate-500 text-sm">Kelola daftar mata pelajaran untuk MI dan MTs.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('master.mapel.plotting') }}" class="flex items-center gap-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-outlined text-[20px]">dataset_linked</span> Plotting Massal
            </a>
            <button onclick="openImportModal()" class="flex items-center gap-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-outlined text-[20px]">upload_file</span> Import
            </button>
            <button onclick="openDeleteAllModal()" class="flex items-center gap-2 bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-red-100 transition-colors">
                <span class="material-symbols-outlined text-[20px]">delete_forever</span> Hapus Semua
            </button>
            <button onclick="openCreateModal()" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-green-600 transition-colors">
                <span class="material-symbols-outlined text-[20px]">add</span> Tambah Mapel
            </button>
        </div>
    </div>

    <!-- Delete All Modal -->
    <div id="deleteAllModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closeDeleteAllModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 dark:bg-slate-800">
                <form action="{{ route('master.mapel.destroy-all') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div>
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full dark:bg-red-900">
                            <span class="material-symbols-outlined text-red-600 dark:text-red-400">warning</span>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">Hapus SEMUA Data Mapel?</h3>
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <p class="mb-4">Tindakan ini akan menghapus <strong>SELURUH</strong> data Mata Pelajaran, serta data terkait seperti Nilai Siswa, KKM, dan Pengajar Mapel.</p>
                                <p class="font-bold text-red-600">Data yang dihapus TIDAK DAPAT dikembalikan!</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm">
                            Ya, Hapus Semua
                        </button>
                        <button type="button" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-slate-700 dark:text-gray-300 dark:border-slate-600 dark:hover:bg-slate-600" onclick="closeDeleteAllModal()">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closeImportModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 dark:bg-slate-800">
                <form action="{{ route('master.mapel.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full dark:bg-blue-900">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">upload_file</span>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">Import Data Mapel</h3>
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <p class="mb-4">Upload file CSV dengan format yang sesuai. Gunakan template jika belum memiliki format.</p>
                                <a href="{{ route('master.mapel.template') }}" class="inline-flex items-center px-4 py-2 gap-2 text-sm font-medium text-blue-700 bg-blue-100 border border-transparent rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <span class="material-symbols-outlined text-[18px]">download</span> Download Template
                                </a>
                                <div class="mt-4">
                                    <input type="file" name="file" accept=".csv, .txt" class="block w-full text-sm text-slate-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-full file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-blue-50 file:text-blue-700
                                      hover:file:bg-blue-100 dark:file:bg-slate-700 dark:file:text-slate-200
                                    "/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                            Import
                        </button>
                        <button type="button" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-slate-700 dark:text-gray-300 dark:border-slate-600 dark:hover:bg-slate-600" onclick="closeImportModal()">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 uppercase text-xs font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Kode</th>
                        <th class="px-6 py-4">Nama Mapel</th>
                        <th class="px-6 py-4">Nama Mapel Arab / Kitab</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Jenjang Target</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($mapels as $mapel)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4 font-mono text-slate-500">{{ $mapel->kode_mapel ?? '-' }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white font-arabic">{{ $mapel->nama_mapel }}</td>
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300 font-arabic text-md">{{ $mapel->nama_kitab ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-bold 
                                {{ $mapel->kategori == 'AGAMA' ? 'bg-green-50 text-green-700' : 
                                   ($mapel->kategori == 'MULOK' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                {{ $mapel->kategori }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-bold border 
                                {{ $mapel->target_jenjang == 'MI' ? 'bg-teal-50 text-teal-700 border-teal-100' : 
                                   ($mapel->target_jenjang == 'MTS' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-slate-50 text-slate-600 border-slate-200') }}">
                                {{ $mapel->target_jenjang }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick='openEditModal(@json($mapel))' class="p-1 rounded text-blue-600 hover:bg-blue-50 transition-colors"><span class="material-symbols-outlined text-[20px]">edit</span></button>
                                <form action="{{ route('master.mapel.destroy', $mapel->id) }}" method="POST" onsubmit="return confirm('Hapus Mapel ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded text-red-600 hover:bg-red-50 transition-colors"><span class="material-symbols-outlined text-[20px]">delete</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400 italic">Belum ada mata pelajaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div id="mapelModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-[#1a2e22] text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-800">
                <form id="mapelForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    
                    <div class="bg-white dark:bg-[#1a2e22] px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white mb-4" id="modalTitle">Tambah Mapel</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Nama Mata Pelajaran</label>
                                <input type="text" name="nama_mapel" id="nama_mapel" required class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                            </div>
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Nama Mapel Arab / Kitab (Opsional)</label>
                                <input type="text" name="nama_kitab" id="nama_kitab" placeholder="Contoh: اللغة العربية / سفينة النجاة" class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700 font-arabic">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Kode Mapel</label>
                                    <input type="text" name="kode_mapel" id="kode_mapel" placeholder="Contoh: PAI-01" class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Kategori</label>
                                    <input type="text" name="kategori" id="kategori" list="kategori_list" required placeholder="Pilih atau Ketik Baru..." class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                                    <datalist id="kategori_list">
                                        <option value="UMUM">
                                        <option value="AGAMA">
                                        <option value="MULOK">
                                        <option value="KELOMPOK A">
                                        <option value="KELOMPOK B">
                                        <option value="PEMINATAN">
                                    </datalist>
                                </div>
                            </div>
                             <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">Target Jenjang</label>
                                <select name="target_jenjang" id="target_jenjang" required class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                                    <option value="SEMUA">SEMUA (MI & MTs)</option>
                                    <option value="MI">Khusus MI</option>
                                    <option value="MTS">Khusus MTs</option>
                                </select>
                                <p class="mt-1 text-xs text-slate-500">Pilih 'SEMUA' jika mapel diajarkan di kedua jenjang.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-black/20 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 sm:ml-3 sm:w-auto">Simpan</button>
                        <button type="button" onclick="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateModal() {
        document.getElementById('mapelModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Tambah Mapel';
        document.getElementById('mapelForm').action = "{{ route('master.mapel.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('nama_mapel').value = '';
        document.getElementById('nama_kitab').value = '';
        document.getElementById('kode_mapel').value = '';
        document.getElementById('kategori').value = 'UMUM';
        document.getElementById('target_jenjang').value = 'SEMUA';
    }

    function closeCreateModal() {
        document.getElementById('mapelModal').classList.add('hidden');
    }

    function openEditModal(mapel) {
        document.getElementById('mapelModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Edit Mapel';
        document.getElementById('mapelForm').action = "{{ url('master/mapel') }}/" + mapel.id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('nama_mapel').value = mapel.nama_mapel;
        document.getElementById('nama_kitab').value = mapel.nama_kitab || '';
        document.getElementById('kode_mapel').value = mapel.kode_mapel;
        document.getElementById('kategori').value = mapel.kategori;
        document.getElementById('target_jenjang').value = mapel.target_jenjang;
    }

    function closeEditModal() {
        document.getElementById('mapelModal').classList.add('hidden');
    }

    function closeModal() {
        document.getElementById('mapelModal').classList.add('hidden');
    }

    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
    }

    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }

    function openDeleteAllModal() {
        document.getElementById('deleteAllModal').classList.remove('hidden');
    }

    function closeDeleteAllModal() {
        document.getElementById('deleteAllModal').classList.add('hidden');
    }
</script>
@endpush
