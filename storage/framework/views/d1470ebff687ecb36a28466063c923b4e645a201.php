<?php $__env->startSection('title', 'Monitoring Nilai'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col gap-4">
        <?php if(auth()->user()->isAdmin() || auth()->user()->isTu()): ?>

        <div class="mb-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Filter (Admin Mode)</h3>
            <form action="<?php echo e(route('walikelas.monitoring')); ?>" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center gap-3">

                <!-- Jenjang Selector -->
                <div class="relative group w-full md:w-auto">
                    <select name="jenjang" class="w-full appearance-none bg-none pl-9 pr-8 h-[46px] text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all md:min-w-[140px]" onchange="this.form.submit()">
                        <?php $__currentLoopData = ['MI', 'MTS']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($j); ?>" <?php echo e((request('jenjang') == $j || (empty(request('jenjang')) && $loop->first)) ? 'selected' : ''); ?>>
                                <?php echo e($j); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">school</span>
                    </div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                </div>

                <!-- Class Selector -->
                <div class="relative group w-full md:w-auto md:min-w-[200px]">
                    <select name="kelas_id" class="w-full appearance-none bg-none pl-10 pr-8 h-[46px] text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all" onchange="this.form.submit()">
                        <?php
                            $yId = $activeYear->id ?? $kelas->id_tahun_ajaran;
                            $q = \App\Models\Kelas::where('id_tahun_ajaran', $yId)->orderBy('nama_kelas');
                            if(request('jenjang')) {
                                $q->whereHas('jenjang', function($query) {
                                    $query->where('kode', request('jenjang'));
                                });
                            }
                            $allClassesInYear = $q->get();
                        ?>

                        <?php if($allClassesInYear->count() == 0): ?>
                            <option value="">Tidak ada kelas</option>
                        <?php endif; ?>

                        <?php $__currentLoopData = $allClassesInYear; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kls): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($kls->id); ?>" <?php echo e($kelas->id == $kls->id ? 'selected' : ''); ?>>
                                <?php echo e($kls->nama_kelas); ?>

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

                <!-- Period Selector -->
                <div class="relative group w-full md:w-auto">
                <?php if(isset($allPeriods) && $allPeriods->count() > 0): ?>
                    <select name="periode_id" class="w-full appearance-none bg-none pl-9 pr-8 h-[46px] text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer shadow-sm transition-all md:min-w-[160px]" onchange="this.form.submit()">
                        <?php $__currentLoopData = $allPeriods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($prd->id); ?>" <?php echo e($periode->id == $prd->id ? 'selected' : ''); ?>>
                                <?php echo e($prd->nama_periode); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 group-hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                    </div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="periode_id" value="<?php echo e($periode->id); ?>">
                <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                    <a href="<?php echo e(route('walikelas.dashboard')); ?>" class="hover:text-primary">Dashboard Wali Kelas</a>
                    <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                    <span>Monitoring Nilai</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                    Monitoring Nilai Kelas <?php echo e($kelas->nama_kelas); ?> - <?php echo e($kelas->jenjang->kode); ?>

                </h1>
                <div class="flex flex-col gap-1 text-sm text-slate-500">
                    <p>
                        Wali Kelas: <strong class="text-slate-800 dark:text-slate-300"><?php echo e($kelas->wali_kelas->name ?? 'Belum ditentukan'); ?></strong>
                    </p>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                            <span>Aman (> 86)</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span>Perlu Katrol (< 86)</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="flex items-center gap-3 w-full md:w-auto">
                <?php if(isset($allLocked) && $allLocked): ?>
                    <div class="flex-1 md:flex-none flex items-center justify-center gap-1.5 px-3 py-2 bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-lg text-sm font-bold border border-green-200 dark:border-green-800 cursor-default">
                        <span class="material-symbols-outlined text-[18px]">lock</span>
                        <span>Terkunci</span>
                    </div>
                <?php else: ?>
                    <form action="<?php echo e(route('walikelas.monitoring.finalize')); ?>" method="POST"
                          data-confirm-delete="true"
                          data-title="Kunci Nilai (Final)?"
                          data-message="Nilai akan dikunci dan siap dicetak. Pastikan semua nilai sudah benar."
                          data-confirm-text="Ya, Kunci Nilai!"
                          data-confirm-color="#059669"
                          data-icon="question"
                          class="w-full md:w-auto">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="kelas_id" value="<?php echo e($kelas->id); ?>">
                        <input type="hidden" name="periode_id" value="<?php echo e($periode->id); ?>">
                        <button type="submit" class="w-full md:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-bold hover:bg-slate-700 transition-all shadow-sm hover:shadow-md">
                            <span class="material-symbols-outlined text-[18px]">lock_open</span>
                            Kunci Nilai
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>

    <!-- MOBILE CARD VIEW -->
    <div class="grid grid-cols-1 gap-4 md:hidden">
        <?php $__empty_1 = true; $__currentLoopData = $monitoringData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $isSafe = $data->status === 'aman';
                // Mobile Card Style
                $cardBorder = $isSafe ? 'border-slate-200 dark:border-slate-700' : 'border-amber-300 dark:border-amber-700/50 bg-amber-50/50 dark:bg-amber-900/10';
            ?>
            <div class="bg-white dark:bg-[#1a2e22] rounded-xl border <?php echo e($cardBorder); ?> shadow-sm p-4 flex flex-col gap-4">
                <!-- Header: Mapel & Status -->
                <div class="flex justify-between items-start">
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-800 dark:text-white text-lg font-arabic"><?php echo e($data->nama_mapel); ?></span>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="h-5 w-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px] text-slate-500 font-bold">
                                <?php echo e(substr($data->nama_guru, 0, 1)); ?>

                            </div>
                            <span class="text-xs text-slate-500"><?php echo e($data->nama_guru); ?></span>
                        </div>
                    </div>
                    <?php if(!$isSafe): ?>
                    <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wide">
                        Perlu Katrol
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex flex-col p-2 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-700/50 text-center">
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Rata-Rata</span>
                        <span class="text-lg font-bold text-slate-700 dark:text-slate-300"><?php echo e($data->avg_score); ?></span>
                    </div>
                    <div class="flex flex-col p-2 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-700/50 text-center">
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Terendah</span>
                        <span class="text-lg font-bold <?php echo e(!$isSafe ? 'text-red-500' : 'text-slate-700'); ?>"><?php echo e($data->min_score); ?></span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-2 gap-2 mt-auto">
                    <a href="<?php echo e(route('teacher.input-nilai', ['kelas' => $kelas->id, 'mapel' => $data->id, 'periode_id' => $periode->id])); ?>"
                       class="flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-bold rounded-lg bg-white border border-slate-300 text-slate-700 shadow-sm active:bg-slate-50">
                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                        Input
                    </a>
                    <a href="<?php echo e(route('walikelas.katrol.index', ['kelas_id' => $kelas->id, 'mapel_id' => $data->id])); ?>"
                       class="flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-bold rounded-lg text-white shadow-sm transition-all
                       <?php echo e($isSafe ? 'bg-slate-500' : 'bg-primary'); ?>">
                        <span class="material-symbols-outlined text-[18px]"><?php echo e($isSafe ? 'visibility' : 'upgrade'); ?></span>
                        <?php echo e($isSafe ? 'Lihat' : 'Katrol'); ?>

                    </a>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="p-8 text-center text-slate-400">
                Belum ada data mata pelajaran.
            </div>
        <?php endif; ?>
    </div>

    <!-- DESKTOP TABLE VIEW -->
    <div class="hidden md:block bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-xs font-semibold text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Mata Pelajaran</th>
                        <th class="px-6 py-4">Guru Pengampu</th>
                        <th class="px-6 py-4 text-center">Rata-Rata</th>
                        <th class="px-6 py-4 text-center">Terendah</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php $__empty_1 = true; $__currentLoopData = $monitoringData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isSafe = $data->status === 'aman';
                            $rowClass = $isSafe ? 'hover:bg-slate-50 dark:hover:bg-slate-800/50' : 'bg-amber-50 hover:bg-amber-100/50 dark:bg-amber-900/10 dark:hover:bg-amber-900/20';
                        ?>
                        <tr class="<?php echo e($rowClass); ?> transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                <?php echo e($data->nama_mapel); ?>

                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-slate-200 flex items-center justify-center text-xs text-slate-500 font-bold">
                                        <?php echo e(substr($data->nama_guru, 0, 1)); ?>

                                    </div>
                                    <span><?php echo e($data->nama_guru); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold text-slate-700 dark:text-slate-300"><?php echo e($data->avg_score); ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold <?php echo e(!$isSafe ? 'text-red-500' : 'text-slate-700'); ?>"><?php echo e($data->min_score); ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if($isSafe): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        Aman
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 animate-pulse">
                                        Perlu Katrol (<?php echo e($data->below_count); ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo e(route('teacher.input-nilai', ['kelas' => $kelas->id, 'mapel' => $data->id, 'periode_id' => $periode->id])); ?>"
                                       class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg shadow-sm text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all"
                                       title="Input Nilai sebagai Wali Kelas">
                                        <span class="material-symbols-outlined text-[16px]">edit_note</span>
                                        <span>Input</span>
                                    </a>

                                    <a href="<?php echo e(route('walikelas.katrol.index', ['kelas_id' => $kelas->id, 'mapel_id' => $data->id])); ?>"
                                       class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all
                                       <?php echo e($isSafe ? 'bg-slate-500 hover:bg-slate-600 focus:ring-slate-500 opacity-70 hover:opacity-100' : 'bg-primary hover:bg-green-600 focus:ring-primary shadow-lg shadow-primary/30'); ?>">
                                        <span class="material-symbols-outlined text-[16px]"><?php echo e($isSafe ? 'visibility' : 'upgrade'); ?></span>
                                        <span><?php echo e($isSafe ? 'Lihat' : 'Katrol'); ?></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                Belum ada data mata pelajaran.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\XAMPP\htdocs\erapor\resources\views/wali-kelas/monitoring.blade.php ENDPATH**/ ?>