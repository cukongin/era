<?php $__env->startSection('title', 'Cetak Rapor'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex flex-col h-[calc(100vh-80px)]">
    <!-- Header & Filters Stack -->
    <div class="mb-6 space-y-4">
        <!-- Header Title -->
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Cetak Rapor</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Pilih siswa untuk mencetak Rapor Capaian Kompetensi.</p>
        </div>
        
        <!-- Filters Toolbar (Left Aligned) -->
        <div class="flex flex-wrap items-center gap-3">
            
            <!-- Year Selector (Admin/TU) -->
            <?php if(isset($years) && count($years) > 0): ?>
            <form action="<?php echo e(route('reports.index')); ?>" method="GET">
                <div class="relative group">
                    <select name="year_id" class="appearance-none pl-10 pr-8 py-2.5 text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer min-w-[180px] shadow-sm transition-all" onchange="this.form.submit()">
                        <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($y->id); ?>" <?php echo e(isset($selectedYear) && $selectedYear->id == $y->id ? 'selected' : ''); ?>>
                                <?php echo e($y->nama); ?> <?php echo e($y->status == 'aktif' ? '(Aktif)' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                    </div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>
            </form>
            <?php endif; ?>

            <!-- Class Selector -->
            <form action="<?php echo e(route('reports.index')); ?>" method="GET" class="flex gap-2">
                <?php if(isset($selectedYear)): ?>
                <input type="hidden" name="year_id" value="<?php echo e($selectedYear->id); ?>">
                <?php endif; ?>
                
                <?php if(count($classes) > 1): ?>
                <div class="relative group">
                    <select name="class_id" class="appearance-none pl-10 pr-8 py-2.5 text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer min-w-[200px] shadow-sm transition-all" onchange="this.form.submit()">
                        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>" <?php echo e(isset($selectedClass) && $selectedClass->id == $c->id ? 'selected' : ''); ?>>
                                <?php echo e($c->nama_kelas); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">class</span>
                    </div>
                     <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>
                <?php elseif(count($classes) == 1): ?>
                     <div class="flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-700 bg-slate-100/50 border-2 border-slate-200 rounded-xl dark:bg-[#1a2332] dark:text-white dark:border-slate-700">
                        <span class="material-symbols-outlined text-slate-400">class</span>
                        <?php echo e($classes->first()->nama_kelas); ?>

                     </div>
                <?php else: ?>
                    <div class="px-4 py-2 text-sm text-red-500 font-medium bg-red-50 border border-red-200 rounded-xl flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">error</span>
                        Tidak Ada Kelas
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Student List -->
    <div class="bg-white dark:bg-[#1a2332] rounded-xl border border-slate-200 dark:border-[#2a3441] shadow-sm flex-1 overflow-hidden flex flex-col">
        <?php if($selectedClass): ?>
            <div class="p-4 border-b border-slate-100 dark:border-[#2a3441] flex justify-between items-center bg-slate-50 dark:bg-[#1e2837]">
                <span class="font-semibold text-slate-700 dark:text-slate-300">Daftar Siswa (<?php echo e($students->count()); ?>)</span>
                
                <?php if($students->count() > 0): ?>
                <a href="<?php echo e(route('reports.print.all', $selectedClass->id)); ?>" target="_blank" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                    <span class="material-symbols-outlined text-[20px]">download</span>
                    Download Semua Rapor
                </a>
                <?php endif; ?>
            </div>
            
            <div class="overflow-auto flex-1">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-[#1e2837] dark:text-slate-400 border-b border-slate-100 dark:border-[#2a3441] sticky top-0">
                        <tr>
                            <th class="px-6 py-3 font-semibold">NIS / NISN</th>
                            <th class="px-6 py-3 font-semibold">Nama Lengkap</th>
                            <th class="px-6 py-3 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#2a3441]">
                        <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-[#253041] transition-colors group">
                            <td class="px-6 py-3 font-medium text-slate-500"><?php echo e($member->siswa->nis_lokal); ?> / <?php echo e($member->siswa->nisn); ?></td>
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white"><?php echo e($member->siswa->nama_lengkap); ?></td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex gap-1 justify-center">
                                    <a href="<?php echo e(route('reports.print.cover', ['student' => $member->siswa->id, 'year_id' => $selectedYear->id ?? null])); ?>" target="_blank" class="px-2 py-1 text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 rounded hover:bg-slate-200" title="Cetak Cover">
                                        Cover
                                    </a>
                                    <a href="<?php echo e(route('reports.print.biodata', ['student' => $member->siswa->id, 'year_id' => $selectedYear->id ?? null])); ?>" target="_blank" class="px-2 py-1 text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 rounded hover:bg-slate-200" title="Cetak Biodata">
                                        Biodata
                                    </a>

                                    <a href="<?php echo e(route('reports.print', ['student' => $member->siswa->id, 'year_id' => $selectedYear->id ?? null])); ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold text-white bg-emerald-600 rounded hover:bg-emerald-700 shadow-sm border border-emerald-700">
                                        <span class="material-symbols-outlined text-[16px]">print</span>
                                        Rapor
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-slate-500">
                                Tidak ada siswa di kelas ini.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center flex-1 p-8 text-center cursor-pointer">
                <div class="bg-slate-100 dark:bg-slate-800 p-4 rounded-full mb-4">
                    <span class="material-symbols-outlined text-4xl text-slate-400">print_disabled</span>
                </div>
                <h3 class="text-lg font-medium text-slate-900 dark:text-white">Belum Ada Kelas Dipilih</h3>
                <p class="text-slate-500 dark:text-slate-400 mt-1 max-w-sm">Siapa takut? Silakan pilih kelas di atas untuk mulai mencetak rapor.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u838039955/domains/rm.alhasany.or.id/public_html/resources/views/reports/index.blade.php ENDPATH**/ ?>