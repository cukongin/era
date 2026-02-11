    <?php
        $periodSlots = $periodSlots ?? [1, 2, 3]; 
        $periodLabel = $periodLabel ?? 'Cawu';
        // Ensure local variables are present (when passed from print_all, they are in array item)
        // Passed variables: student, class, school, activeYear, allPeriods, activePeriod, stats, etc.
    ?>

    <!-- A4 Paper Container -->
    <div class="print-container paper-a4 flex flex-col relative text-black" style="page-break-after: always; page-break-inside: avoid;">
        
        <!-- Header Section (Logo + Title) -->
        <header class="flex flex-col items-center justify-center mb-4 mt-0">
            <div class="flex items-center justify-center mb-2">
                <?php if($school->logo): ?>
                    <img src="<?php echo e(asset($school->logo)); ?>" class="h-16 w-16 object-contain" alt="Logo">
                <?php else: ?>
                   <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Logo_Kementerian_Agama_Pengasuh.png/586px-Logo_Kementerian_Agama_Pengasuh.png" class="h-16 w-16 object-contain" alt="Logo Kemenag">
                <?php endif; ?>
            </div>
            <div class="text-center mt-2">
                 <h3 class="text-base font-bold uppercase">Laporan Hasil Belajar (Rapor)</h3>
            </div>
        </header>

        <!-- Identity Section (Expanded) -->
        <div class="flex justify-between w-full mb-4 text-xs">
            <!-- Left Column -->
            <div class="flex-1 pr-4">
                <div class="flex mb-1">
                    <span class="w-32 font-semibold">Nama</span>
                    <span class="mr-2">:</span>
                    <span class="uppercase font-medium"><?php echo e($student->nama_lengkap); ?></span>
                </div>
                <div class="flex mb-1">
                    <span class="w-32 font-semibold">Nomor Induk</span>
                    <span class="mr-2">:</span>
                    <span><?php echo e($student->nis_lokal); ?></span>
                </div>
                <div class="flex mb-1">
                    <span class="w-32 font-semibold">Nama MDT. <?php echo e($class->jenjang->kode == 'MTS' ? 'Wustha' : 'Ula'); ?></span>
                    <span class="mr-2">:</span>
                    <span><?php echo e($school->nama_sekolah); ?></span>
                </div>
            </div>
            
            <!-- Right Column (Aligned to Right Edge) -->
            <div class="w-auto">
                <div class="flex mb-1">
                    <span class="w-32 font-semibold">Kelas</span>
                    <span class="mr-2">:</span>
                    <span><?php echo e($class->nama_kelas); ?></span>
                </div>
                <div class="flex mb-1">
                    <span class="w-32 font-semibold">Tahun Pelajaran</span>
                    <span class="mr-2">:</span>
                    <span><?php echo e($activeYear->nama); ?></span>
                </div>
                <div class="flex mb-1">
                    <span class="w-32 font-semibold">Alamat</span>
                    <span class="mr-2">:</span>
                    <span><?php echo e($school->alamat); ?></span>
                </div>
            </div>
        </div>

        <?php $sectionIndex = 0; ?>
        <!-- Academic Table -->
        <div class="mb-2">
            <h4 class="font-bold text-xs mb-1"><?php echo e(chr(65 + $sectionIndex++)); ?>. Pengetahuan dan Keterampilan</h4>
            <table class="w-full text-xs text-left border-collapse rapor-table">
                <thead>
                    <tr class="bg-gray-100 text-center font-bold">
                        <th class="w-8" rowspan="2">No</th>
                        <th class="<?php echo e($class->jenjang->kode == 'MTS' ? 'w-auto' : 'w-64'); ?>" rowspan="2">Mata Pelajaran</th>
                        <th class="w-12" rowspan="2">KKM</th>
                        <th class="whitespace-nowrap"  colspan="<?php echo e(count($periodSlots)); ?>">Nilai <?php echo e($periodLabel ?? 'Catur Wulan'); ?></th>
                        <th class="w-20" rowspan="2">Rata-Rata<br>Akhir Tahun</th> <!-- Widened -->
                        <th class="w-14" rowspan="2">Predikat</th> <!-- Widened -->
                    </tr>
                    <tr class="bg-gray-100 text-center font-bold">
                        <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                           <th class="w-8"><?php echo e($slot); ?></th> 
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php 
                        // Convert to Base Collection to avoid Eloquent Key checks on Groups
                        $mapelGroups = $mapelGroups->toBase();
                        
                        // Identify Mulok Group (Case Insensitive check for 'Muatan Lokal' or 'Mulok')
                        $mulokKey = $mapelGroups->keys()->first(fn($k) => stripos($k, 'Muatan Lokal') !== false || stripos($k, 'Mulok') !== false);
                        $mulokGroup = $mulokKey ? $mapelGroups[$mulokKey] : collect([]);
                        $otherGroups = $mapelGroups->except($mulokKey ? [$mulokKey] : []);
                    ?>

                    
                    <tr class="bg-gray-50/50">
                        <td class="font-bold italic px-2 py-1" colspan="<?php echo e(3 + count($periodSlots) + 2); ?>">1. Mata Pelajaran Wajib</td>
                    </tr>

                    
                    <?php $__currentLoopData = $otherGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kategori => $mapels): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $mapels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $totalScore = 0;
                                $countScore = 0;
                                $kkm = $kkmMapels[$pm->id_mapel] ?? $globalKkm; 
                            ?>
                            <tr>
                                <td class="text-center"><?php echo e($no++); ?></td>
                                <td>
                                    <span class="font-arabic"><?php echo e($pm->mapel->nama_mapel); ?></span>
                                    <?php if(!empty($pm->mapel->nama_kitab)): ?>
                                          -  <span class="font-arabic"><?php echo e($pm->mapel->nama_kitab); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo e($kkm); ?></td>
                                <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $pObj = $allPeriods->skip($i)->first(); 
                                        $pId = $pObj ? $pObj->id : null;
                                        $grade = $pId ? ($cumulativeGrades[$pm->id_mapel][$pId] ?? null) : null;
                                        $val = $grade ? $grade->nilai_akhir : '-';
                                        
                                        if(is_numeric($val)) { 
                                            // Ensure numeric values are floated for calc
                                            $totalScore += (float)$val; 
                                            $countScore++; 
                                        }
                                    ?>
                                    <td class="text-center <?php echo e(is_numeric($val) && $val < $kkm ? 'text-red-600 font-bold' : ''); ?>">
                                        <?php echo e(is_numeric($val) ? round($val) : $val); ?>

                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php
                                    $finalAvg = $countScore > 0 ? number_format($totalScore / $countScore, 2) : 0;
                                    // Simple Predicate Logic
                                    $predikat = '-';
                                    if ($countScore > 0) {
                                        if ($finalAvg >= 90) $predikat = 'A';
                                        elseif ($finalAvg >= 80) $predikat = 'B';
                                        elseif ($finalAvg >= 70) $predikat = 'C';
                                        else $predikat = 'D';
                                    }
                                    $finalDisplay = $countScore > 0 ? $finalAvg : '-';
                                ?>

                                <td class="text-center font-bold"><?php echo e($finalDisplay); ?></td>
                                <td class="text-center"><?php echo e($predikat); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <tr class="bg-gray-50/50">
                        <td class="font-bold italic px-2 py-1" colspan="<?php echo e(3 + count($periodSlots) + 2); ?>">2. Muatan Lokal</td>
                    </tr>
                    
                    <?php if($mulokGroup->count() > 0): ?>
                        <?php $__currentLoopData = $mulokGroup; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                             <?php
                                $totalScore = 0;
                                $countScore = 0;
                                $kkm = $kkmMapels[$pm->id_mapel] ?? $globalKkm; 
                            ?>
                            <tr>
                                <td class="text-center"><?php echo e($no++); ?></td>
                                <td>
                                    <?php echo e($pm->mapel->nama_mapel); ?>

                                    <?php if(!empty($pm->mapel->nama_kitab)): ?>
                                          -  <span class="font-arabic"><?php echo e($pm->mapel->nama_kitab); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo e($kkm); ?></td>
                                <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $pObj = $allPeriods->skip($i)->first(); 
                                        $pId = $pObj ? $pObj->id : null;
                                        $grade = $pId ? ($cumulativeGrades[$pm->id_mapel][$pId] ?? null) : null;
                                        $val = $grade ? $grade->nilai_akhir : '-';
                                        
                                        if(is_numeric($val)) { 
                                            $totalScore += (float)$val; 
                                            $countScore++; 
                                        }
                                    ?>
                                    <td class="text-center <?php echo e(is_numeric($val) && $val < $kkm ? 'text-red-600 font-bold' : ''); ?>">
                                        <?php echo e(is_numeric($val) ? round($val) : $val); ?>

                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php
                                    $finalAvg = $countScore > 0 ? number_format($totalScore / $countScore, 2) : 0;
                                    $predikat = '-';
                                    if ($countScore > 0) {
                                        if ($finalAvg >= 90) $predikat = 'A';
                                        elseif ($finalAvg >= 80) $predikat = 'B';
                                        elseif ($finalAvg >= 70) $predikat = 'C';
                                        else $predikat = 'D';
                                    }
                                    $finalDisplay = $countScore > 0 ? $finalAvg : '-';
                                ?>

                                <td class="text-center font-bold"><?php echo e($finalDisplay); ?></td>
                                <td class="text-center"><?php echo e($predikat); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        
                        <?php for($k=0; $k<3; $k++): ?>
                        <tr class="h-6">
                            <td class="text-center"><?php echo e($no++); ?></td>
                            <td>-</td>
                            <td class="text-center">-</td>
                            <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td class="text-center">-</td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                        <?php endfor; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <!-- Total Score -->
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="3" class="text-center">Nilai Total</td>
                        <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $pObj = $allPeriods->skip($i)->first(); 
                                $pId = $pObj ? $pObj->id : null;
                                $val = $pId && isset($stats[$pId]) ? $stats[$pId]['total'] : '-';
                            ?>
                            <td class="text-center"><?php echo e(is_numeric($val) ? round($val) : $val); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td class="text-center"><?php echo e($annualStats['total'] ?? '-'); ?></td>
                        <td class="bg-gray-200"></td> 
                    </tr>
                    
                    <!-- Average Score -->
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="3" class="text-center">Rata-rata Total</td>
                        <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $pObj = $allPeriods->skip($i)->first(); 
                                $pId = $pObj ? $pObj->id : null;
                                $val = $pId && isset($stats[$pId]) ? $stats[$pId]['average'] : '-';
                            ?>
                            <td class="text-center"><?php echo e(is_numeric($val) ? number_format($val, 2) : $val); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                         <td class="text-center"><?php echo e($annualStats['average'] ?? '-'); ?></td>
                         <td class="bg-gray-200"></td>
                    </tr>
                    
                    <!-- Ranking -->
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="3" class="text-center">Ranking</td>
                         <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $pObj = $allPeriods->skip($i)->first(); 
                                $pId = $pObj ? $pObj->id : null;
                                $rank = $pId && isset($stats[$pId]) ? $stats[$pId]['rank'] : '-';
                                $count = $pId && isset($stats[$pId]) ? $stats[$pId]['count'] : '-';
                                $display = $rank !== '-' ? "$rank / $count" : '-';
                            ?>
                            <td class="text-center"><?php echo e($display); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                         <td class="text-center"><?php echo e($annualStats['rank'] ? ($annualStats['rank'] . ' / ' . $annualStats['count']) : '-'); ?></td>
                         <td class="bg-gray-200"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if(\App\Models\GlobalSetting::val('rapor_show_ekskul', 1)): ?>
        <div class="flex gap-4 mb-2">
            <!-- Extracurricular -->
            <div class="flex-1">
                <h4 class="font-bold text-xs mb-1"><?php echo e(chr(65 + $sectionIndex++)); ?>. Ekstrakurikuler</h4>
                <table class="w-full text-xs text-left border-collapse rapor-table">
                    <thead class="bg-gray-100 text-center font-bold">
                        <tr>
                            <th class="w-8">No</th>
                            <th>Kegiatan Ekstrakurikuler</th>
                            <th class="w-10">Nilai</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $flatEkskuls = collect();
                            foreach($ekskuls as $pId => $grp) {
                                foreach($grp as $e) {
                                    $ekey = $e->nama_ekskul; 
                                    if (!$flatEkskuls->has($ekey)) {
                                        $flatEkskuls->put($ekey, $e);
                                    }
                                }
                            }
                        ?>
                        
                        <?php $__empty_1 = true; $__currentLoopData = $flatEkskuls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center"><?php echo e($loop->iteration); ?></td>
                            <td><?php echo e($e->nama_ekskul); ?></td>
                            <td class="text-center"><?php echo e($e->nilai); ?></td>
                            <td><?php echo e($e->keterangan); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="text-center">-</td>
                            <td>-</td>
                            <td class="text-center">-</td>
                            <td>-</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if(\App\Models\GlobalSetting::val('rapor_show_prestasi', 0)): ?>
        <div class="flex gap-4 mb-2">
            <!-- Prestasi -->
            <div class="flex-1">
                <h4 class="font-bold text-xs mb-1"><?php echo e(chr(65 + $sectionIndex++)); ?>. Prestasi</h4>
                <table class="w-full text-xs text-left border-collapse rapor-table">
                    <thead class="bg-gray-100 text-center font-bold">
                        <tr>
                            <th class="w-8" rowspan="2">No</th>
                            <th rowspan="2">Jenis Prestasi</th>
                            <th rowspan="2">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex gap-4 mb-2">
            <!-- Personality (New C) -->
            <div class="flex-1">
                <h4 class="font-bold text-xs mb-1"><?php echo e(chr(65 + $sectionIndex++)); ?>. Kepribadian</h4>
                <table class="w-full text-xs text-left border-collapse rapor-table">
                    <thead>
                        <tr class="bg-gray-100 text-center font-bold">
                            <th>Aspek</th>
                            <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th><?php echo e($periodLabel == 'Catur Wulan' ? 'Cawu' : $periodLabel); ?> <?php echo e($slot); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = ['Kelakuan', 'Kerajinan', 'Kebersihan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aspek): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($aspek); ?></td>
                            <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $pObj = $allPeriods->skip($i)->first(); 
                                    $pId = $pObj ? $pObj->id : null;
                                    $safeKey = strtolower($aspek);
                                    // Fetch from Attendance Record
                                    $val = $pId && isset($attendance[$pId]) ? $attendance[$pId]->$safeKey : '-';
                                ?>
                                <td class="text-center"><?php echo e($val); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Attendance (Renamed to D) -->
            <div class="flex-1">
                <h4 class="font-bold text-xs mb-1"><?php echo e(chr(65 + $sectionIndex++)); ?>. Ketidakhadiran</h4>
                <table class="w-full text-xs text-left border-collapse rapor-table whitespace-nowrap">
                     <thead>
                        <tr class="bg-gray-100 text-center font-bold">
                            <th>Keterangan</th>
                            <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th><?php echo e($periodLabel == 'Catur Wulan' ? 'Cawu' : $periodLabel); ?> <?php echo e($slot); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>
                    <tbody>
                         <tr>
                            <td>Sakit</td>
                            <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $pObj = $allPeriods->skip($i)->first(); 
                                    $pId = $pObj ? $pObj->id : null;
                                    $val = $pId && isset($attendance[$pId]) ? $attendance[$pId]->sakit : '-';
                                    $disp = ($val !== '-' && $val > 0) ? $val . ' Hari' : '-';
                                ?>
                                <td class="text-center"><?php echo e($disp); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                        <tr>
                            <td>Izin</td>
                             <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $pObj = $allPeriods->skip($i)->first(); 
                                    $pId = $pObj ? $pObj->id : null;
                                    $val = $pId && isset($attendance[$pId]) ? $attendance[$pId]->izin : '-';
                                    $disp = ($val !== '-' && $val > 0) ? $val . ' Hari' : '-';
                                ?>
                                <td class="text-center"><?php echo e($disp); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                        <tr>
                            <td>Tanpa Keterangan</td>
                             <?php $__currentLoopData = $periodSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $pObj = $allPeriods->skip($i)->first(); 
                                    $pId = $pObj ? $pObj->id : null;
                                    $val = $pId && isset($attendance[$pId]) ? $attendance[$pId]->tanpa_keterangan : '-';
                                    $disp = ($val !== '-' && $val > 0) ? $val . ' Hari' : '-';
                                ?>
                                <td class="text-center"><?php echo e($disp); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notes & Promotion Status (Renamed to E) -->
        <div class="mb-4">
            <h4 class="font-bold text-xs mb-1"><?php echo e(chr(65 + $sectionIndex++)); ?>. Catatan Wali Kelas (Akhir Tahun)</h4>
            <?php
                $latestRem = $remarks->last(); 
                $note = $latestRem ? $latestRem->catatan_akademik : '-'; 
            ?>
            <div class="border border-black p-2 text-xs min-h-[40px] mb-2 italic">
                "<?php echo e($note); ?>"
            </div>
            
            <?php
                // Logic: Is this the Final Period? (Cawu 3 or Semester Genap)
                // We check if the active period is the last one in the list for this year/jenjang.
                $lastPeriod = $allPeriods->last();
                $isFinalPeriod = $activePeriod && $lastPeriod && $activePeriod->id == $lastPeriod->id;
                
                // Show if data exists OR if it's the final period (show placeholder)
                $showPromotion = $statusNaik !== null || $isFinalPeriod;
            ?>
            
            <?php if($showPromotion): ?>
            <div class="border border-black p-2 flex flex-col items-center justify-center gap-1 bg-gray-50">
                <p class="text-xs">Berdasarkan hasil penilaian, Peserta Didik dinyatakan:</p>
                <p class="font-bold text-sm uppercase"><?php echo e($decisionText ?? '......................'); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <!-- Signatures (Date Right) -->
        <div class="text-right text-xs mt-4 mb-1">
            <?php
                $place = !empty($titimangsaTempat) ? $titimangsaTempat : ($school->kabupaten ?? $school->kota ?? 'Tempat');
                $date1Raw = !empty($titimangsa) ? $titimangsa : \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y');
                
                $jenjangKey = strtolower($class->jenjang->kode ?? 'mi'); 
                $date2Raw = \App\Models\GlobalSetting::val('titimangsa_2_' . $jenjangKey);
            ?>
            
            <?php if(!empty($date2Raw)): ?>
                <?php
                    // Helper logic to parse dates (Inline to avoid redeclaration error)
                    $parseDate = function($dateStr) {
                        $parts = explode(' ', trim($dateStr));
                        if (count($parts) < 3) return ['day' => $dateStr, 'month' => '', 'year' => '', 'suffix' => ''];
                        
                        $day = array_shift($parts);
                        $last = end($parts);
                        $suffix = '';
                        // Suffix is usually H. or M. or similar short string ending in dot
                        if (str_ends_with($last, '.') || strlen($last) <= 2) {
                            $suffix = array_pop($parts);
                        }
                        $year = array_pop($parts);
                        $month = implode(' ', $parts);
                        return compact('day', 'month', 'year', 'suffix');
                    };

                    $d1 = $parseDate($date1Raw);
                    $d2 = $parseDate($date2Raw);
                ?>

                
                <div class="inline-block text-left">
                    <table style="border-collapse: collapse; white-space: nowrap;">
                        
                        <tr class="leading-tight">
                            <td class="pr-2 text-right"><?php echo e($place); ?>,</td>
                            <td class="px-1 text-center"><?php echo e($d1['day']); ?></td>
                            <td class="px-1 text-left pl-2"><?php echo e($d1['month']); ?></td>
                            <td class="px-1 text-center"><?php echo e($d1['year']); ?></td>
                            <td class="pl-1 text-left"><?php echo e($d1['suffix']); ?></td>
                        </tr>
                        
                        <tr class="leading-tight">
                            <td></td> 
                            <td class="px-1 text-center"><?php echo e($d2['day']); ?></td>
                            <td class="px-1 text-left pl-2"><?php echo e($d2['month']); ?></td>
                            <td class="px-1 text-center"><?php echo e($d2['year']); ?></td>
                            <td class="pl-1 text-left"><?php echo e($d2['suffix']); ?></td>
                        </tr>
                    </table>
                </div>
            <?php else: ?>
                
                <p><?php echo e($place); ?>, <?php echo e($date1Raw); ?></p>
            <?php endif; ?>
        </div>

        <div class="flex justify-between items-end text-xs pb-4 w-full cursor-default">
            <!-- Parents (Left) -->
            <div class="text-center" style="min-width: 120px;">
                <p class="mb-24">Orang Tua / Wali</p> 
                <p class="font-bold inline-block min-w-[120px]">..........................</p> 
            </div>
            
            <!-- Principal (Center) -->
            <div class="text-center" style="min-width: 120px;">
                <p class="mb-24">Mengetahui,<br>Kepala Madrasah</p>
                <?php
                    $kepalaName = $school->kepala_madrasah;
                    // Override for MTS if available
                    if (($class->jenjang->kode ?? '') == 'MTS' && !empty($school->kepala_madrasah_mts)) {
                        $kepalaName = $school->kepala_madrasah_mts;
                    }
                ?>
                <p class="font-bold inline-block min-w-[120px] uppercase"><?php echo e($kepalaName); ?></p> 
            </div>

            <!-- Teacher (Right) -->
            <div class="text-center" style="min-width: 120px;">
                <p class="mb-24">Wali Kelas</p>
                <p class="font-bold inline-block min-w-[120px] uppercase"><?php echo e($class->wali_kelas->name); ?></p> 
            </div>
        </div>

    </div>
<?php /**PATH /home/u838039955/domains/rm.alhasany.or.id/public_html/resources/views/reports/partials/rapor_content.blade.php ENDPATH**/ ?>