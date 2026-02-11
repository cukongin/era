<?php $__env->startSection('title', $pageContext['title']); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6" x-data="promotionPage()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <?php if($pageContext['type'] == 'graduation'): ?>
                    <span class="material-symbols-outlined text-primary">school</span>
                <?php endif; ?>
                <?php echo e($pageContext['title']); ?>

            </h1>
            <p class="text-sm text-slate-500">
                Sistem otomatis menghitung rekomendasi <?php echo e(strtolower($pageContext['title'])); ?> berdasarkan aturan penilaian.
                <br>Kelas: <span class="font-bold text-primary"><?php echo e($kelas->nama_kelas); ?></span>
                <?php if(isset($isLocked) && $isLocked): ?>
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        <span class="material-symbols-outlined text-[14px] mr-1">lock</span> Mode Baca
                    </span>
                <?php endif; ?>
            </p>
        </div>

        <div class="flex gap-2 items-center">
            <!-- Filter Lengkap -->
            <!-- Filter Lengkap -->
            <?php if(auth()->user()->isAdmin() || auth()->user()->isTu()): ?>
            <div x-data="{ open: false }" class="w-full md:w-auto">
                <!-- Mobile Toggle Button -->
                <button @click="open = !open" type="button" class="md:hidden w-full flex justify-between items-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-2.5 rounded-lg shadow-sm">
                    <div class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200">
                        <span class="material-symbols-outlined text-primary">tune</span>
                        <span>Filter: <?php echo e($activeYear->nama); ?></span>
                    </div>
                    <span class="material-symbols-outlined text-slate-500 transition-transform duration-200" :class="{'rotate-180': open}">expand_more</span>
                </button>

                <!-- Form -->
                <form action="<?php echo e(route('walikelas.kenaikan.index')); ?>" method="GET"
                      class="flex-col md:flex-row gap-2 items-center flex-wrap w-full md:w-auto mt-2 md:mt-0"
                      :class="open ? 'flex' : 'hidden md:flex'">

                    <!-- Tahun Ajaran -->
                    <select name="year_id" onchange="this.form.submit()" class="bg-white dark:bg-[#1a2332] border border-slate-200 dark:border-[#2a3441] text-slate-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary block p-2.5 shadow-sm font-bold w-full md:w-40">
                        <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($y->id); ?>" <?php echo e($activeYear->id == $y->id ? 'selected' : ''); ?>>
                                <?php echo e($y->nama); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <!-- Jenjang -->
                    <select name="jenjang" onchange="this.form.submit()" class="bg-white dark:bg-[#1a2332] border border-slate-200 dark:border-[#2a3441] text-slate-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary block p-2.5 shadow-sm font-bold w-full md:w-24">
                        <option value="">Semua</option>
                        <?php $__currentLoopData = $jenjangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($j->kode); ?>" <?php echo e(request('jenjang') == $j->kode || ($kelas && $kelas->jenjang->kode == $j->kode) ? 'selected' : ''); ?>>
                                <?php echo e($j->kode); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <!-- Kelas -->
                    <select name="kelas_id" onchange="this.form.submit()" class="bg-white dark:bg-[#1a2332] border border-slate-200 dark:border-[#2a3441] text-slate-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary block p-2.5 shadow-sm font-bold w-full md:w-32">
                        <?php $__currentLoopData = $allClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>" <?php echo e($kelas->id == $c->id ? 'selected' : ''); ?>>
                                <?php echo e($c->nama_kelas); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <!-- Periode -->
                    <select name="period_id" onchange="this.form.submit()" class="bg-white dark:bg-[#1a2332] border border-slate-200 dark:border-[#2a3441] text-slate-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary block p-2.5 shadow-sm font-bold w-full md:w-40">
                        <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>" <?php echo e(isset($activePeriod) && $activePeriod->id == $p->id ? 'selected' : ''); ?>>
                                <?php echo e($p->nama_periode); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                </form>
            </div>
            <?php endif; ?>

            <?php
                $allDecisionsLocked = collect($studentStats)->every(fn($s) => $s->is_locked);
                $isUserAdmin = auth()->user()->isAdmin() || auth()->user()->isTu();
            ?>

            <?php if(isset($isLocked) && $isLocked): ?>
                <div class="bg-amber-100 text-amber-800 px-4 py-2 rounded-lg font-bold flex items-center gap-2 border border-amber-200 cursor-not-allowed opacity-75 select-none" title="Periode ini terkunci">
                    <span class="material-symbols-outlined">lock</span> Terkunci (Periode)
                </div>
            <?php elseif($allDecisionsLocked && !$isUserAdmin): ?>
                <div class="bg-slate-100 text-slate-500 px-4 py-2 rounded-lg font-bold flex items-center gap-2 border border-slate-200 cursor-not-allowed select-none" title="Keputusan sudah final">
                    <span class="material-symbols-outlined">verified_user</span> Keputusan Final
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Santri -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Santri</p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1"><?php echo e($summary['total']); ?></h3>
            </div>
            <div class="w-12 h-12 bg-primary/10 text-primary rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined">groups</span>
            </div>
        </div>

        <!-- Siap Naik/Lulus -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-slate-500">Siap <?php echo e($pageContext['success_label']); ?></p>
                <h3 class="text-3xl font-bold text-emerald-600 mt-1"><?php echo e($summary['promote']); ?></h3>
                <p class="text-xs text-emerald-500 mt-1">Memenuhi syarat</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
        </div>

        <!-- Perlu Peninjauan/Tidak Naik -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-slate-500">Perlu Peninjauan / <?php echo e($isFinalYear ? 'Tidak Lulus' : 'Tinggal'); ?></p>
                <h3 class="text-3xl font-bold text-amber-500 mt-1"><?php echo e($summary['review'] + $summary['retain']); ?></h3>
                <p class="text-xs text-amber-500 mt-1">Tidak memenuhi syarat</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined">warning</span>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 relative">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">table_chart</span>
                Daftar Rekomendasi <?php echo e($pageContext['title']); ?>

            </h2>
            <div class="relative w-full md:w-auto">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                <input type="text" x-model="search" placeholder="Cari nama santri..." class="pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full md:w-64">
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden space-y-4 p-4 bg-slate-50 dark:bg-slate-900/50">
            <!-- Select All (Mobile) -->
            <?php if(isset($isFinalPeriod) && $isFinalPeriod): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl p-3 shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <label class="flex items-center gap-3 cursor-pointer w-full">
                     <input type="checkbox" @change="toggleAll($event)" class="w-5 h-5 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary">
                     <span class="font-bold text-slate-700 dark:text-slate-200 text-sm">Pilih Semua (<?php echo e(count($studentStats)); ?>)</span>
                </label>
            </div>
            <?php endif; ?>

            <?php $__currentLoopData = $studentStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    // Re-use logic for badge
                    $status = $stat->final_status ?: 'pending';
                    $badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
                    $badgeLabel = 'BELUM DITENTUKAN';

                    if(in_array($status, ['promoted', 'promote', 'graduated', 'graduate'])) {
                        $badgeClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                        $badgeLabel = $pageContext['success_label'] ?? 'NAIK KELAS';
                    } elseif(in_array($status, ['retained', 'retain', 'not_graduated', 'not_graduate'])) {
                        $badgeClass = 'bg-red-100 text-red-700 border-red-200';
                        $badgeLabel = $pageContext['fail_label'] ?? 'TINGGAL KELAS';
                    } elseif($status == 'conditional') {
                         $badgeClass = 'bg-amber-100 text-amber-700 border-amber-200';
                         $badgeLabel = 'NAIK BERSYARAT';
                    }

                    $studentJson = json_encode([
                        'id' => $stat->student->id,
                        'name' => $stat->student->nama_lengkap,
                        'current_status' => $status,
                        'class_id' => $kelas->id
                    ]);
                ?>

            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700"
                 data-name="<?php echo e(strtolower($stat->student->nama_lengkap)); ?>"
                 x-show="matchesSearch($el.dataset.name)">

                <!-- Card Header -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm shrink-0">
                            <?php echo e($index + 1); ?>

                        </div>
                        <div class="overflow-hidden">
                             <h3 class="font-bold text-slate-900 dark:text-white truncate"><?php echo e($stat->student->nama_lengkap); ?></h3>
                             <p class="text-xs text-slate-500">NIS: <?php echo e($stat->student->nis_lokal ?? '-'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-3 gap-2 text-center text-xs mb-3 bg-slate-50 dark:bg-slate-700/50 p-2 rounded-lg border border-slate-100 dark:border-slate-600">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase">Rata-rata</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200 text-sm"><?php echo e($stat->avg_yearly); ?></span>
                    </div>
                    <div>
                         <span class="text-slate-400 block text-[10px] uppercase">Sikap</span>
                         <span class="font-bold text-sm <?php echo e($stat->attitude == 'A' ? 'text-emerald-600' : ($stat->attitude == 'C' ? 'text-red-600' : 'text-slate-700')); ?>"><?php echo e($stat->attitude); ?></span>
                    </div>
                     <div>
                         <span class="text-slate-400 block text-[10px] uppercase">Kehadiran</span>
                         <span class="font-bold text-sm <?php echo e($stat->attendance_pct < 85 ? 'text-red-600' : 'text-slate-700'); ?>"><?php echo e($stat->attendance_pct); ?>%</span>
                    </div>
                </div>

                <!-- Failure/Notes -->
                <?php if(($stat->under_kkm > 0) || !empty($stat->fail_reasons) || $stat->ijazah_note): ?>
                <div class="mb-3 space-y-1">
                     <?php if($stat->under_kkm > 0): ?>
                        <div class="text-xs bg-red-50 text-red-700 px-2 py-1 rounded border border-red-100 inline-block font-bold">
                            <?php echo e($stat->under_kkm); ?> Mapel < KKM
                        </div>
                     <?php endif; ?>

                     <?php if(!empty($stat->fail_reasons)): ?>
                        <div class="text-xs text-red-600 bg-red-50 p-2 rounded border border-red-100">
                            <?php $__currentLoopData = $stat->fail_reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>• <?php echo e($reason); ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                     <?php endif; ?>

                     <?php if($stat->ijazah_note): ?>
                        <div class="text-xs font-bold text-emerald-600 bg-emerald-50 p-2 rounded border border-emerald-100">
                            <?php echo e($stat->ijazah_note); ?>

                        </div>
                     <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Footer Action -->
                <?php if(isset($isFinalPeriod) && $isFinalPeriod): ?>
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700 gap-2">
                    <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase border flex-1 text-center <?php echo e($badgeClass); ?>">
                        <?php echo e($badgeLabel); ?>

                    </span>

                    <?php if((!isset($isLocked) || !$isLocked) && (!$stat->is_locked || auth()->user()->isAdmin())): ?>
                        <button @click="openModal(<?php echo e($studentJson); ?>)" class="p-1.5 rounded-lg bg-primary/5 text-primary hover:bg-primary/10 border border-primary/20 transition-colors">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                    <?php endif; ?>

                    <!-- Checkbox for Bulk -->
                     <input type="checkbox" value="<?php echo e($stat->student->id); ?>" x-model="selectedIds" class="w-6 h-6 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary ml-1">
                </div>
                <?php endif; ?>

            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="overflow-x-auto hidden md:block">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 uppercase text-xs font-bold">
                    <tr>
                        <?php if(isset($isFinalPeriod) && $isFinalPeriod): ?>
                        <th class="p-4 w-4">
                            <input type="checkbox" @change="toggleAll($event)" class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary">
                        </th>
                        <?php endif; ?>
                        <th class="px-6 py-4">Nama Santri</th>
                        <th class="px-6 py-4 text-center">Rata-Rata<br>Tahun</th>
                        <th class="px-6 py-4 text-center">Mapel<br>< KKM</th>
                        <th class="px-6 py-4 text-center">Nilai<br>Sikap</th>
                        <th class="px-6 py-4 text-center">Kehadiran<br>(%)</th>
                        <th class="px-6 py-4 text-center">Rekomendasi<br>Sistem</th>
                        <th class="px-6 py-4 text-left w-64">Catatan<br>Sistem</th>
                        <?php if(isset($isFinalPeriod) && $isFinalPeriod): ?>
                        <th class="px-6 py-4 text-right w-48">Status Akhir</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php $__currentLoopData = $studentStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr data-name="<?php echo e(strtolower($stat->student->nama_lengkap)); ?>"
                        x-show="matchesSearch($el.dataset.name)"
                        class="hover:bg-slate-50 transition-colors group">

                        <?php if(isset($isFinalPeriod) && $isFinalPeriod): ?>
                        <td class="w-4 p-4 text-center">
                            <input type="checkbox" value="<?php echo e($stat->student->id); ?>" x-model="selectedIds" class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary">
                        </td>
                        <?php endif; ?>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                    <?php echo e($index + 1); ?>

                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white"><?php echo e($stat->student->nama_lengkap); ?></div>
                                    <div class="text-xs text-slate-500">NIS: <?php echo e($stat->student->nis_lokal ?? '-'); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-slate-700"><?php echo e($stat->avg_yearly); ?></td>
                        <td class="px-6 py-4 text-center">
                            <?php if($stat->under_kkm > 0): ?>
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold"><?php echo e($stat->under_kkm); ?> Mapel</span>
                            <?php else: ?>
                                <span class="text-slate-400">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center font-bold <?php echo e($stat->attitude == 'A' ? 'text-emerald-600' : ($stat->attitude == 'C' ? 'text-red-600' : 'text-slate-700')); ?>">
                            <?php echo e($stat->attitude); ?>

                        </td>
                        <td class="px-6 py-4 text-center <?php echo e($stat->attendance_pct < 85 ? 'text-red-600 font-bold' : 'text-slate-700'); ?>">
                            <?php echo e($stat->attendance_pct); ?>%
                        </td>
                        <td class="px-6 py-4 text-center">
                             <?php if($stat->system_status == 'promote' || $stat->system_status == 'graduate'): ?>
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200 block w-full text-center">
                                    <?php echo e($stat->recommendation); ?>

                                </span>
                            <?php else: ?>
                                <span class="<?php echo e($stat->system_status == 'review' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-red-100 text-red-700 border-red-200'); ?> px-3 py-1 rounded-full text-xs font-bold border flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-[10px]"><?php echo e($stat->system_status == 'review' ? 'warning' : 'close'); ?></span>
                                    <?php echo e($stat->recommendation); ?>

                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 leading-snug break-words">
                            <?php if(!empty($stat->fail_reasons)): ?>
                                <div class="text-red-600 mb-1">
                                    <?php $__currentLoopData = $stat->fail_reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div>• <?php echo e($reason); ?></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                            <?php if($stat->ijazah_note): ?> <div class="font-bold text-emerald-600"><?php echo e($stat->ijazah_note); ?></div> <?php endif; ?>
                            <?php if($stat->manual_note): ?> <div class="italic">"<?php echo e($stat->manual_note); ?>"</div> <?php endif; ?>
                            <?php if(empty($stat->fail_reasons) && !$stat->ijazah_note && !$stat->manual_note): ?> - <?php endif; ?>
                        </td>

                        <?php if(isset($isFinalPeriod) && $isFinalPeriod): ?>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <!-- SERVER SIDE RENDERED BADGE -->
                                <?php
                                    $status = $stat->final_status ?: 'pending';
                                    $badgeClass = 'bg-slate-50 text-slate-600 border-slate-200';
                                    $badgeLabel = 'BELUM DITENTUKAN';
                                    $badgeIcon = 'help_outline';

                                    if(in_array($status, ['promoted', 'promote', 'graduated', 'graduate'])) {
                                        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                        // Use dynamic label from controller
                                        $badgeLabel = $pageContext['success_label'] ?? 'NAIK KELAS';
                                        $badgeIcon = 'check_circle';
                                    } elseif(in_array($status, ['retained', 'retain', 'not_graduated', 'not_graduate'])) {
                                        $badgeClass = 'bg-red-50 text-red-700 border-red-200';
                                        // Use dynamic label from controller
                                        $badgeLabel = $pageContext['fail_label'] ?? 'TINGGAL KELAS';
                                        $badgeIcon = 'cancel';
                                    } elseif($status == 'conditional') {
                                        $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                                        $badgeLabel = 'NAIK BERSYARAT';
                                        $badgeIcon = 'warning';
                                    }

                                    // Prepare Data for Modal
                                    $studentJson = json_encode([
                                        'id' => $stat->student->id,
                                        'name' => $stat->student->nama_lengkap,
                                        'current_status' => $status,
                                        'class_id' => $kelas->id
                                    ]);
                                ?>

                                <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase border shadow-sm flex items-center gap-2 <?php echo e($badgeClass); ?>">
                                        <span class="material-symbols-outlined text-[14px]"><?php echo e($badgeIcon); ?></span>
                                        <span><?php echo e($badgeLabel); ?></span>
                                </span>

                                <?php if((!isset($isLocked) || !$isLocked) && (!$stat->is_locked || auth()->user()->isAdmin())): ?>
                                <button @click="openModal(<?php echo e($studentJson); ?>)"
                                        class="text-slate-400 hover:text-primary transition-colors p-1 rounded hover:bg-slate-100"
                                        title="Ubah Keputusan">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FLOATING BULK TOOLBAR -->
    <div x-show="selectedIds.length > 0"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-20 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-40"
         style="display: none;">
        <div class="bg-slate-900/90 backdrop-blur text-white rounded-full shadow-2xl px-6 py-3 flex items-center gap-6 border border-slate-700/50 ring-1 ring-white/10">
             <div class="flex items-center gap-3 border-r border-slate-700 pr-6">
                <span class="bg-primary text-white text-xs font-bold px-2 py-0.5 rounded-full" x-text="selectedIds.length"></span>
                <span class="font-bold text-sm">Terpilih</span>
            </div>
            <div class="flex items-center gap-2">
                <?php if($isFinalYear): ?>
                    <button @click="bulkUpdate('graduated')" class="px-4 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-xs font-bold transition-colors shadow-lg shadow-emerald-900/20">LULUS</button>
                    <button @click="bulkUpdate('not_graduated')" class="px-4 py-1.5 rounded-full bg-red-600 hover:bg-red-500 text-xs font-bold transition-colors shadow-lg shadow-red-900/20">TIDAK LULUS</button>
                <?php else: ?>
                    <button @click="bulkUpdate('promoted')" class="px-4 py-1.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-xs font-bold transition-colors shadow-lg shadow-emerald-900/20">NAIK KELAS</button>
                    <button @click="bulkUpdate('retained')" class="px-4 py-1.5 rounded-full bg-red-600 hover:bg-red-500 text-xs font-bold transition-colors shadow-lg shadow-red-900/20">TINGGAL KELAS</button>
                <?php endif; ?>
            </div>
            <button @click="selectedIds = []" class="ml-2 text-slate-400 hover:text-white transition-colors"><span class="material-symbols-outlined">close</span></button>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div x-show="showModal"
         style="display: none;"
         class="fixed inset-0 z-[99] overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">

        <!-- Backdrop -->
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.away="closeModal()"
                 class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-symbols-outlined text-primary">edit</span>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Ubah Keputusan</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 mb-4">
                                    Tentukan status akhir untuk santri: <br>
                                    <span class="font-bold text-slate-900 text-lg" x-text="editData.name"></span>
                                </p>

                                <label class="block text-sm font-bold text-slate-700 mb-2">Status Akhir</label>
                                <select x-model="editData.new_status" class="w-full rounded-md border-slate-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-2 border">
                                    <?php if($isFinalYear): ?>
                                        <option value="graduated">LULUS</option>
                                        <option value="not_graduated">TIDAK LULUS</option>
                                        <option value="pending">Ditangguhkan / Belum Ada Keputusan</option>
                                    <?php else: ?>
                                        <option value="promoted">Naik Kelas</option>
                                        <option value="retained">Tinggal Kelas</option>
                                        <option value="pending">Ditangguhkan / Belum Ada Keputusan</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button"
                            @click="saveDecision()"
                            :disabled="saving"
                            class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary/90 sm:ml-3 sm:w-auto disabled:opacity-50 flex items-center gap-2">
                        <span x-show="saving" class="material-symbols-outlined animate-spin text-xs">sync</span>
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                    </button>
                    <button type="button" @click="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function promotionPage() {
        return {
            selectedIds: [],
            search: '',
            showModal: false,
            saving: false,
            editData: {
                id: null,
                name: '',
                new_status: 'pending',
                class_id: null
            },

            matchesSearch(name) {
                if(!this.search) return true;
                return name.includes(this.search.toLowerCase());
            },

            toggleAll(e) {
                if(e.target.checked) {
                    this.selectedIds = [
                        <?php $__currentLoopData = $studentStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo e($stat->student->id); ?>,
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    ];
                } else {
                    this.selectedIds = [];
                }
            },

            openModal(studentData) {
                this.editData = {
                    id: studentData.id,
                    name: studentData.name,
                    new_status: studentData.current_status,
                    class_id: studentData.class_id
                };
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
            },

            async saveDecision() {
                this.saving = true;
                try {
                    const res = await fetch("<?php echo e(route('promotion.update')); ?>", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({
                            student_id: this.editData.id,
                            class_id: this.editData.class_id,
                            status: this.editData.new_status
                        })
                    });

                    if (res.ok) {
                        this.showModal = false;
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Status kenaikan kelas berhasil diperbarui.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        const data = await res.json();
                        Swal.fire('Gagal', data.message || 'Gagal menyimpan', 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
                } finally {
                    this.saving = false;
                }
            },

            async bulkUpdate(status) {
                 const result = await Swal.fire({
                    title: 'Konfirmasi Massal',
                    text: `Yakin ubah status ${this.selectedIds.length} santri terpilih?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                 });

                 if (!result.isConfirmed) return;

                 try {
                     const res = await fetch("<?php echo e(route('promotion.bulk_update')); ?>", {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                         },
                         body: JSON.stringify({
                            student_ids: this.selectedIds,
                            class_id: <?php echo e($kelas->id); ?>,
                            status: status
                        })
                     });

                     if (res.ok) {
                         const data = await res.json();
                         Swal.fire({
                            icon: 'success',
                            title: 'Selesai!',
                            text: data.message || 'Update massal berhasil.',
                            timer: 2000,
                            showConfirmButton: false
                         }).then(() => {
                             window.location.reload();
                         });
                     } else {
                         const data = await res.json();
                         Swal.fire('Gagal', data.message || 'Gagal melakukan update massal', 'error');
                     }
                 } catch(e) {
                     Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
                 }
            }
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\XAMPP\htdocs\erapor\resources\views/wali-kelas/kenaikan-kelas.blade.php ENDPATH**/ ?>