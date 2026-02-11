<?php $__env->startSection('title', 'Input Nilai Ekskul - ' . $kelas->nama_kelas); ?>

<?php $__env->startSection('content'); ?>
<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="<?php echo e(route('walikelas.dashboard')); ?>" class="hover:text-primary">Dashboard Wali Kelas</a>
                <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                <span>Ekskul</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Input Nilai Ekstrakurikuler</h1>
            <p class="text-sm text-slate-500">
                Pilih kegiatan dan berikan nilai untuk siswa. Maksimal 2 kegiatan per siswa.
            </p>
        </div>
        <button type="submit" form="ekskulForm" class="bg-primary text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/30 hover:bg-green-600 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">save</span> Simpan Perubahan
        </button>
    </div>

    <!-- Form Table -->
    <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <form id="ekskulForm" action="<?php echo e(route('walikelas.ekskul.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 uppercase text-xs font-bold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4 w-10">No</th>
                            <th class="px-6 py-4 min-w-[200px]">Nama Siswa</th>
                            <th class="px-6 py-4 text-center">Kegiatan 1</th>
                            <th class="px-6 py-4 text-center">Kegiatan 2</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $ak): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $nilai = $ekskulRows[$ak->id_siswa] ?? collect([]);
                            $ekskul1 = $nilai->get(0);
                            $ekskul2 = $nilai->get(1);
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-6 py-4 text-slate-500 text-center align-top pt-6"><?php echo e($index + 1); ?></td>
                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white align-top pt-6">
                                <?php echo e($ak->siswa->nama_lengkap); ?>

                                <div class="text-xs text-slate-400 font-normal mt-0.5"><?php echo e($ak->siswa->nis_lokal); ?></div>
                            </td>
                            
                            <!-- Kegiatan 1 -->
                            <td class="px-4 py-4 bg-slate-50/30 align-top">
                                <div class="flex flex-col gap-2">
                                    <input type="text" list="ekskulList" name="ekskul[<?php echo e($ak->id_siswa); ?>][0][nama_ekskul]" value="<?php echo e(optional($ekskul1)->nama_ekskul); ?>" placeholder="Nama Kegiatan (ex: Pramuka)" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                    
                                    <div class="grid grid-cols-2 gap-2">
                                        <select name="ekskul[<?php echo e($ak->id_siswa); ?>][0][nilai]" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                            <option value="">Nilai</option>
                                            <option value="A" <?php echo e(optional($ekskul1)->nilai == 'A' ? 'selected' : ''); ?>>A (Sangat Baik)</option>
                                            <option value="B" <?php echo e(optional($ekskul1)->nilai == 'B' ? 'selected' : ''); ?>>B (Baik)</option>
                                            <option value="C" <?php echo e(optional($ekskul1)->nilai == 'C' ? 'selected' : ''); ?>>C (Cukup)</option>
                                        </select>
                                        <input type="text" name="ekskul[<?php echo e($ak->id_siswa); ?>][0][keterangan]" value="<?php echo e(optional($ekskul1)->keterangan); ?>" placeholder="Keterangan..." class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                    </div>
                                </div>
                            </td>

                            <!-- Kegiatan 2 -->
                            <td class="px-4 py-4 bg-slate-50/30 align-top">
                                <div class="flex flex-col gap-2">
                                    <input type="text" list="ekskulList" name="ekskul[<?php echo e($ak->id_siswa); ?>][1][nama_ekskul]" value="<?php echo e(optional($ekskul2)->nama_ekskul); ?>" placeholder="Nama Kegiatan (ex: Futsal)" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                    
                                    <div class="grid grid-cols-2 gap-2">
                                        <select name="ekskul[<?php echo e($ak->id_siswa); ?>][1][nilai]" class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                            <option value="">Nilai</option>
                                            <option value="A" <?php echo e(optional($ekskul2)->nilai == 'A' ? 'selected' : ''); ?>>A (Sangat Baik)</option>
                                            <option value="B" <?php echo e(optional($ekskul2)->nilai == 'B' ? 'selected' : ''); ?>>B (Baik)</option>
                                            <option value="C" <?php echo e(optional($ekskul2)->nilai == 'C' ? 'selected' : ''); ?>>C (Cukup)</option>
                                        </select>
                                        <input type="text" name="ekskul[<?php echo e($ak->id_siswa); ?>][1][keterangan]" value="<?php echo e(optional($ekskul2)->keterangan); ?>" placeholder="Keterangan..." class="w-full rounded-lg border-slate-300 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
</div>

<datalist id="ekskulList">
    <?php $__currentLoopData = $ekskulOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($opt); ?>">
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</datalist>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\XAMPP\htdocs\erapor\resources\views/wali-kelas/ekskul.blade.php ENDPATH**/ ?>