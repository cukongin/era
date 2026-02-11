<?php $__env->startSection('title', 'Global Monitoring Nilai'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header & Filters -->
    <div class="bg-white dark:bg-[#1a2e22] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">travel_explore</span>
                    Global Monitoring
                </h1>
                <p class="text-xs text-slate-500 mt-1">Pantau progress penginputan nilai seluruh kelas.</p>
            </div>

            <!-- Unified Filter -->
            <form action="<?php echo e(route('tu.monitoring.global')); ?>" method="GET" class="w-full md:w-auto flex flex-col md:flex-row items-stretch md:items-center gap-3">
                <input type="hidden" name="year_id" value="<?php echo e($activeYear->id); ?>">

                <!-- Jenjang Selector -->
                <div class="relative group w-full md:w-auto">
                    <select name="jenjang" class="w-full appearance-none bg-none pl-9 pr-8 h-[40px] text-sm font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all md:min-w-[140px]" onchange="this.form.submit()">
                        <option value="" <?php echo e(empty(request('jenjang')) ? 'selected' : ''); ?>>Semua Jenjang</option>
                        <?php $__currentLoopData = ['MI', 'MTS']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($j); ?>" <?php echo e(request('jenjang') == $j ? 'selected' : ''); ?>><?php echo e($j); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[18px]">school</span>
                    </div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>

                <!-- Class Selector -->
                <div class="relative group w-full md:w-auto md:min-w-[200px]">
                    <select name="kelas_id" class="w-full appearance-none bg-none pl-9 pr-8 h-[40px] text-sm font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        <?php if(isset($allClasses)): ?>
                            <?php $__currentLoopData = $allClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kls): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($kls->id); ?>" <?php echo e(request('kelas_id') == $kls->id ? 'selected' : ''); ?>>
                                    <?php echo e($kls->nama_kelas); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[18px]">class</span>
                    </div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                         <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>

                <!-- Period Selector -->
                <div class="relative group w-full md:w-auto">
                    <select name="period_id" class="w-full appearance-none bg-none pl-9 pr-8 h-[40px] text-sm font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-lg hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all md:min-w-[160px]" onchange="this.form.submit()">
                        <option value="all" <?php echo e($selectedPeriodId == 'all' ? 'selected' : ''); ?>>Semua Periode</option>
                        <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>" <?php echo e(($currentPeriod && $currentPeriod->id == $p->id) ? 'selected' : ''); ?>>
                                <?php echo e($p->nama_periode); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                    </div>
                     <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                         <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Content -->
    <div class="">

        <?php if(count($monitoringData) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php $__currentLoopData = $monitoringData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all p-5 flex flex-col gap-4 relative overflow-hidden group">
                <!-- Progress Background -->
                <div class="absolute bottom-0 left-0 h-1 bg-slate-100 dark:bg-slate-700 w-full">
                    <div class="h-full bg-<?php echo e($data->color); ?>-500 transition-all duration-1000" style="width: <?php echo e($data->progress); ?>%"></div>
                </div>

                <div class="flex justify-between items-start">
                    <div>
                        <a href="<?php echo e(route('reports.class.analytics', $data->class->id)); ?>" class="text-lg font-bold text-slate-900 dark:text-white hover:text-primary transition-colors underline-offset-4 hover:underline">
                            <?php echo e($data->class->nama_kelas); ?>

                        </a>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-bold"><?php echo e($data->class->wali_kelas->name ?? 'No Wali'); ?></p>
                    </div>
                    <span class="text-xs font-bold px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                        <?php echo e($data->student_count); ?> Siswa
                    </span>
                </div>

                <div class="flex items-center gap-4 my-2">
                    <div class="relative size-16">
                        <svg class="size-full rotate-[-90deg]" viewBox="0 0 36 36">
                            <path class="text-slate-100 dark:text-slate-700" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" />
                            <path class="text-<?php echo e($data->color); ?>-500 transition-all duration-1000" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="<?php echo e($data->progress); ?>, 100" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center flex-col">
                            <span class="text-sm font-bold text-slate-900 dark:text-white"><?php echo e($data->progress); ?>%</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 flex-1">
                        <div class="text-xs text-slate-500">Status Pengisian</div>
                        <div class="text-sm font-bold text-<?php echo e($data->color); ?>-600"><?php echo e($data->status); ?></div>
                    </div>
                </div>

                <div class="mt-auto pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs text-slate-500">
                    <span><?php echo e($data->mapel_count); ?> Mapel</span>
                    <span><?php echo e($data->period_label); ?></span>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php else: ?>
        <div class="flex flex-col items-center justify-center h-[50vh] text-slate-400">
            <span class="material-symbols-outlined text-6xl mb-4 opacity-20">search_off</span>
            <p class="text-lg font-medium">Tidak ada data kelas ditemukan.</p>
            <p class="text-sm">Coba ubah filter di atas.</p>
        </div>
        <?php endif; ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\XAMPP\htdocs\erapor\resources\views/tu/monitoring_global.blade.php ENDPATH**/ ?>