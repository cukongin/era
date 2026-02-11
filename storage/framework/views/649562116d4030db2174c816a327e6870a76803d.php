<?php $__env->startSection('title', 'Manajemen Data Siswa'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-[1200px] mx-auto flex flex-col gap-8">

    <!-- Heading -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div class="flex flex-col gap-2 max-w-2xl">
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white leading-tight tracking-tight">Manajemen Data Siswa</h1>
            <p class="text-slate-500 dark:text-slate-400 text-base font-normal leading-normal">
                Sistem Rapor Terpadu MI (Cawu) dan MTs (Semester).
            </p>
        </div>
        <div class="flex gap-3">
            <button type="submit" form="bulkDeleteForm" id="bulkDeleteBtn" class="hidden items-center justify-center gap-2 h-10 px-4 rounded-lg bg-red-600 text-white text-sm font-bold shadow-md hover:bg-red-700 transition-colors animate-pulse" onclick="return confirm('Yakin ingin menghapus data terpilih?')">
                <span class="material-symbols-outlined text-[20px]">delete</span>
                <span class="truncate">Hapus Terpilih</span>
            </button>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-200 text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[20px]">upload_file</span>
                <span class="truncate">Impor Excel</span>
            </button>
            <button onclick="document.getElementById('addStudentModal').classList.remove('hidden')" class="flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-primary text-slate-900 text-sm font-bold shadow-md hover:bg-[#15bd4d] transition-colors">
                <span class="material-symbols-outlined text-[20px]">add</span>
                <span class="truncate">Tambah Siswa</span>
            </button>
        </div>
    </div>

    <div class="flex flex-col gap-4">
        <!-- Tabs Navigation -->
        <div class="flex flex-col gap-4">
            <div class="flex border-b border-slate-200 dark:border-slate-800 w-full overflow-x-auto">
                <a href="<?php echo e(route('master.students.index', ['tab' => 'active', 'level_id' => request('level_id')])); ?>"
                   class="px-5 py-3 text-sm font-bold min-w-max flex items-center gap-2 <?php echo e(request('tab', 'active') == 'active' ? 'text-green-600 border-b-2 border-green-600 bg-green-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'); ?> transition-all">
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                    Siswa Aktif
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full ml-1"><?php echo e($stats['all_active']); ?></span>
                </a>
                <a href="<?php echo e(route('master.students.index', ['tab' => 'new', 'level_id' => request('level_id')])); ?>"
                   class="px-5 py-3 text-sm font-bold min-w-max flex items-center gap-2 <?php echo e(request('tab') == 'new' ? 'text-amber-600 border-b-2 border-amber-600 bg-amber-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'); ?> transition-all">
                    <span class="material-symbols-outlined text-[18px]">fiber_new</span>
                    Siswa Baru
                    <span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full ml-1"><?php echo e($stats['new'] ?? 0); ?></span>
                </a>
                <a href="<?php echo e(route('master.students.index', ['tab' => 'inactive', 'level_id' => request('level_id')])); ?>"
                   class="px-5 py-3 text-sm font-bold min-w-max flex items-center gap-2 <?php echo e(request('tab') == 'inactive' ? 'text-red-600 border-b-2 border-red-600 bg-red-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'); ?> transition-all">
                    <span class="material-symbols-outlined text-[18px]">do_not_disturb_on</span>
                    Lulus / Keluar
                    <span class="bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full ml-1"><?php echo e($stats['inactive']); ?></span>
                </a>
            </div>

            <!-- Filters & Search (Simplified) -->
            <form action="<?php echo e(route('master.students.index')); ?>" method="GET" class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <input type="hidden" name="tab" value="<?php echo e(request('tab', 'active')); ?>">

                <!-- Jenjang Filter -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">
                    <a href="<?php echo e(route('master.students.index', ['tab' => request('tab', 'active'), 'level_id' => 'all'])); ?>" class="px-3 py-1.5 rounded-full text-xs font-bold border <?php echo e(!request('level_id') || request('level_id') == 'all' ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-500 border-slate-200 hover:border-slate-300'); ?>">Semua</a>
                    <?php $__currentLoopData = $levels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lvl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('master.students.index', ['tab' => request('tab', 'active'), 'level_id' => $lvl->id])); ?>" class="px-3 py-1.5 rounded-full text-xs font-bold border <?php echo e(request('level_id') == $lvl->id ? 'bg-primary text-white border-primary' : 'bg-white text-slate-500 border-slate-200 hover:border-slate-300'); ?>">
                        <?php echo e($lvl->nama); ?>

                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="relative w-full md:w-64">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[18px]">search</span>
                    <input name="search" value="<?php echo e(request('search')); ?>" class="w-full pl-9 pr-4 h-9 rounded-lg border border-slate-200 text-sm focus:ring-1 focus:ring-primary focus:border-primary" placeholder="Cari Siswa..." type="text"/>
                </div>
            </form>
        </div>

    <!-- Table -->
    <form id="bulkDeleteForm" action="<?php echo e(route('master.students.bulk_destroy')); ?>" method="POST" class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
        <?php echo csrf_field(); ?>
        <?php echo method_field('DELETE'); ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#f0f4f2] dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider w-10">
                            <input class="rounded border-slate-300 text-primary focus:ring-primary cursor-pointer" type="checkbox" onchange="toggleAll(this)"/>
                        </th>
                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">NIS</th>
                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Lembaga</th>
                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kelas Saat Ini</th>
                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="group hover:bg-primary/5 dark:hover:bg-primary/10 transition-colors">
                        <td class="py-4 px-4">
                            <input name="ids[]" value="<?php echo e($student->id); ?>" class="row-checkbox rounded border-slate-300 text-primary focus:ring-primary cursor-pointer" type="checkbox" onchange="toggleRow()"/>
                        </td>
                        <td class="py-4 px-4 text-sm font-medium text-slate-500"><?php echo e($student->nis_lokal ?? '-'); ?></td>
                        <td class="py-4 px-4">
                            <div class="flex flex-col">
                                <a href="<?php echo e(route('master.students.show', $student->id)); ?>" class="text-sm font-semibold text-slate-900 dark:text-white hover:text-primary transition-colors">
                                    <?php echo e($student->nama_lengkap); ?>

                                </a>
                                <span class="text-xs text-slate-500"><?php echo e($student->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan'); ?></span>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e(optional($student->jenjang)->kode == 'MI' ? 'bg-emerald-100 text-emerald-800' : 'bg-primary/10 text-primary'); ?>">
                                <?php echo e(optional($student->jenjang)->kode); ?>

                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm text-slate-900 dark:text-white">
                            <?php echo e($student->kelas_saat_ini->kelas->nama_kelas ?? '-'); ?>

                        </td>
                        <td class="py-4 px-4">
                            <?php if($student->status_siswa == 'aktif'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                            <?php elseif($student->status_siswa == 'lulus'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Lulus</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><?php echo e(ucfirst($student->status_siswa)); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?php echo e(route('master.students.show', $student->id)); ?>" class="text-slate-400 hover:text-primary p-1 rounded hover:bg-slate-100 transition-colors" title="Lihat Detail">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </a>
                                <?php if(in_array(request('tab', 'active'), ['active', 'new'])): ?>
                                <button type="button" onclick="openStatusModal(<?php echo e($student->id); ?>, '<?php echo e($student->nama_lengkap); ?>')" class="text-amber-500 hover:text-amber-600 p-1 rounded hover:bg-amber-50 transition-colors" title="Ubah Status (Pindah/Keluar)">
                                    <span class="material-symbols-outlined text-[20px]">input</span>
                                </button>
                                <?php endif; ?>

                                <?php if(request('tab') == 'inactive' && $student->status_siswa != 'lulus'): ?>
                                <button type="button" onclick="restoreStudent(<?php echo e($student->id); ?>)" class="text-green-500 hover:text-green-600 p-1 rounded hover:bg-green-50 transition-colors" title="Kembali Bersekolah (Restore)">
                                    <span class="material-symbols-outlined text-[20px]">restore_from_trash</span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500">
                             <div class="flex flex-col items-center gap-2">
                                <span class="material-symbols-outlined text-4xl text-slate-300">person_off</span>
                                <span>Tidak ada data siswa yang ditemukan.</span>
                             </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between p-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-[#1a2e22] gap-4">
            <div class="text-sm text-slate-500">
                Menampilkan <span class="font-medium text-slate-900 dark:text-white"><?php echo e($students->firstItem() ?? 0); ?></span> sampai <span class="font-medium text-slate-900 dark:text-white"><?php echo e($students->lastItem() ?? 0); ?></span> dari <span class="font-medium text-slate-900 dark:text-white"><?php echo e($students->total()); ?></span> siswa
            </div>
            <div class="flex items-center gap-2">
                <?php echo e($students->links('pagination::simple-tailwind')); ?>

            </div>
        </div>
    </div>
    </form>
</div>

<script>
function toggleAll(source) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
    toggleDeleteBtn();
}

function toggleRow() {
    toggleDeleteBtn();
}

function toggleDeleteBtn() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const deleteBtn = document.getElementById('bulkDeleteBtn');
    if (checkboxes.length > 0) {
        deleteBtn.classList.remove('hidden');
        deleteBtn.classList.add('flex');
    } else {
        deleteBtn.classList.add('hidden');
        deleteBtn.classList.remove('flex');
    }
}

function openStatusModal(id, nama) {
    document.getElementById('statusStudentName').innerText = nama;

    let url = "<?php echo e(route('master.students.updateStatus', ':id')); ?>";
    url = url.replace(':id', id);
    document.getElementById('statusForm').action = url;

    document.getElementById('statusModal').classList.remove('hidden');
}
function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

function restoreStudent(id) {
    if(confirm('Kembalikan siswa ini menjadi AKTIF dan coba masukkan ke kelas terakhirnya?')) {
        let url = "<?php echo e(route('master.students.restore', ':id')); ?>";
        url = url.replace(':id', id);
        let form = document.getElementById('restoreForm');
        form.action = url;
        form.submit();
    }
}
</script>

<!-- Status Change Modal -->
<div id="statusModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-[#1a2e22] text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 dark:border-slate-800">
                <form id="statusForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <!-- Route defined in JS -->

                    <div class="bg-white dark:bg-[#1a2e22] px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-10 w-10 flex-shrink-0 rounded-full bg-amber-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-amber-600">input</span>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">Ubah Status Siswa</h3>
                                <p class="text-xs text-slate-500" id="statusStudentName">Nama Siswa</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mb-1">Pilih Status Baru</label>
                                <select name="status" required class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-amber-500 sm:text-sm sm:leading-6">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="active">Aktif (Batalkan Keluar)</option>
                                    <option value="mutasi">Mutasi (Pindah Sekolah)</option>
                                    <option value="keluar">Keluar (Drop Out / Berhenti)</option>
                                    <option value="lulus">Lulus (Alumni)</option>
                                    <option value="meninggal">Meninggal Dunia</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mb-1">Catatan (Opsional)</label>
                                <textarea name="catatan" rows="2" class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 sm:text-sm sm:leading-6" placeholder="Alasan pindah/berhenti..."></textarea>
                            </div>

                            <div class="text-xs text-amber-600 bg-amber-50 p-2 rounded border border-amber-100">
                                <b>Perhatian:</b> Siswa yang berstatus Mutasi/Keluar/Lulus tidak akan muncul di daftar Absensi, Penilaian, atau Kenaikan Kelas.
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-black/20 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" onclick="return confirm('Yakin ingin mengubah status siswa ini?')" class="inline-flex w-full justify-center rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 sm:ml-3 sm:w-auto">Simpan Status</button>
                        <button type="button" onclick="closeStatusModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Siswa (Buku Induk Style) -->
<div id="addStudentModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white dark:bg-[#1a2e22] text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-slate-200 dark:border-slate-800">
                <form action="<?php echo e(route('master.students.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="bg-white dark:bg-[#1a2e22] px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                        <h3 class="text-lg font-bold leading-6 text-slate-900 dark:text-white">Tambah Siswa Baru (Buku Induk)</h3>
                    </div>

                    <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                        <!-- Grid Layout -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Section: Identitas -->
                            <div class="space-y-4">
                                <h4 class="font-bold text-primary text-sm uppercase tracking-wider border-b border-slate-100 pb-2">Identitas Diri</h4>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap</label>
                                    <input type="text" name="nama_lengkap" required class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">NIS Lokal</label>
                                        <input type="text" name="nis_lokal" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">NISN</label>
                                        <input type="text" name="nisn" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">NIK</label>
                                        <input type="text" name="nik" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2 bg-white">
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Lahir</label>
                                        <input type="date" name="tanggal_lahir" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap</label>
                                    <textarea name="alamat_lengkap" rows="3" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2"></textarea>
                                </div>
                            </div>

                            <!-- Section: Data Tambahan -->
                            <div class="space-y-4">
                                <h4 class="font-bold text-primary text-sm uppercase tracking-wider border-b border-slate-100 pb-2">Orang Tua & Akademik</h4>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Ayah</label>
                                    <input type="text" name="nama_ayah" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Ibu</label>
                                    <input type="text" name="nama_ibu" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">No. Telepon Ortu</label>
                                    <input type="text" name="no_telp_ortu" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2">
                                </div>

                                <hr class="border-slate-200">

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jenjang</label>
                                        <select name="id_jenjang" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2 bg-white">
                                            <?php $__currentLoopData = $levels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lvl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($lvl->id); ?>"><?php echo e($lvl->nama); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tahun Masuk</label>
                                        <input type="number" name="tahun_masuk" value="<?php echo e(date('Y')); ?>" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary px-3 py-2">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-black/20 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-primary px-4 py-2 text-sm font-bold text-slate-900 shadow-sm hover:bg-green-600 sm:ml-3 sm:w-auto">Simpan Data</button>
                        <button type="button" onclick="document.getElementById('addStudentModal').classList.add('hidden')" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-slate-800 px-4 py-2 text-sm font-bold text-slate-700 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white dark:bg-[#1a2e22] text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-800">
                <div class="bg-white dark:bg-[#1a2e22] px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-symbols-outlined text-green-600">upload_file</span>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white" id="modal-title">Impor Data Siswa</h3>
                            <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                <p class="mb-4">Upload file CSV/Excel untuk menambahkan siswa secara massal. Gunakan template yang disediakan agar format sesuai.</p>

                                <a href="<?php echo e(route('master.students.template')); ?>" class="inline-flex items-center gap-2 text-primary hover:text-green-700 font-medium mb-4 p-2 bg-green-50 rounded-lg w-full border border-green-100 justify-center">
                                    <span class="material-symbols-outlined text-[18px]">download</span>
                                    Download Template CSV
                                </a>

                                <form action="<?php echo e(route('master.students.import')); ?>" method="POST" enctype="multipart/form-data" id="importForm">
                                    <?php echo csrf_field(); ?>
                                    <div class="mt-2">
                                        <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white mb-2">Pilih File CSV</label>
                                        <input type="file" name="file" accept=".csv, .txt" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-slate-900 hover:file:bg-green-500"/>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-black/20 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="submit" form="importForm" class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">Upload & Proses</button>
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Restore Form -->
<form id="restoreForm" method="POST" class="hidden">
    <?php echo csrf_field(); ?>
</form>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\XAMPP\htdocs\erapor\resources\views/master/students/index.blade.php ENDPATH**/ ?>