<?php $__env->startSection('title', 'Import Nilai Global (Semua Kelas)'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Import Data Global</h1>
            <p class="text-slate-500">Import Nilai Mapel, Absensi, dan Sikap sekaligus untuk semua kelas.</p>
        </div>
    </div>

    <!-- MAIN IMPORT SECTION -->
    <div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Card MI -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-green-600 mb-2">Jenjang MI</h3>
                <p class="text-sm text-slate-500 mb-4">Import data (Nilai, Absensi, Sikap) untuk semua kelas 1-6 MI.</p>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pilih Tingkat (Opsional)</label>
                        <select id="gradeMI" class="w-full text-sm rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600" onchange="updateLinks()">
                            <option value="">Semua Tingkat (1-6)</option>
                            <option value="1">Kelas 1</option>
                            <option value="2">Kelas 2</option>
                            <option value="3">Kelas 3</option>
                            <option value="4">Kelas 4</option>
                            <option value="5">Kelas 5</option>
                            <option value="6">Kelas 6</option>
                        </select>
                    </div>

                    <a id="btnDlMI" href="<?php echo e(route('grade.import.global.template', 'MI')); ?>" class="block w-full text-center px-4 py-2 border-2 border-dashed border-green-300 rounded-lg hover:bg-green-50 transition-colors font-bold text-green-700">
                        <span class="material-symbols-outlined align-middle mr-2">download</span>
                        Download Template MI
                    </a>
                    
                    <form action="<?php echo e(route('grade.import.global.preview', 'MI')); ?>" method="POST" enctype="multipart/form-data" class="space-y-2">
                        <?php echo csrf_field(); ?>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Upload File CSV/Excel (MI)</label>
                        <input type="file" name="file" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-green-700 transition-colors mt-2">
                            Preview & Import MI
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card MTS -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-blue-600 mb-2">Jenjang MTS</h3>
                <p class="text-sm text-slate-500 mb-4">Import data (Nilai, Absensi, Sikap) untuk semua kelas 7-9 MTS.</p>
                
                <div class="space-y-4">
                     <div>
                         <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pilih Tingkat (Opsional)</label>
                         <select id="gradeMTS" class="w-full text-sm rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600" onchange="updateLinks()">
                             <option value="">Semua Tingkat (7-9)</option>
                             <option value="7">Kelas 7</option>
                             <option value="8">Kelas 8</option>
                             <option value="9">Kelas 9</option>
                         </select>
                    </div>

                    <a id="btnDlMTS" href="<?php echo e(route('grade.import.global.template', 'MTS')); ?>" class="block w-full text-center px-4 py-2 border-2 border-dashed border-blue-300 rounded-lg hover:bg-blue-50 transition-colors font-bold text-blue-700">
                        <span class="material-symbols-outlined align-middle mr-2">download</span>
                        Download Template MTS
                    </a>
                    
                    <form action="<?php echo e(route('grade.import.global.preview', 'MTS')); ?>" method="POST" enctype="multipart/form-data" class="space-y-2">
                        <?php echo csrf_field(); ?>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Upload File CSV/Excel (MTS)</label>
                        <input type="file" name="file" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700 transition-colors mt-2">
                            Preview & Import MTS
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateLinks() {
        // --- GRADES ---
        // MI
        const gradeMI = document.getElementById('gradeMI').value;
        const btnMI = document.getElementById('btnDlMI');
        let urlMI = "<?php echo e(route('grade.import.global.template', 'MI')); ?>";
        if(gradeMI) urlMI += '?grade=' + gradeMI;
        btnMI.href = urlMI;
        btnMI.innerHTML = '<span class="material-symbols-outlined align-middle mr-2">download</span> Download Template ' + (gradeMI ? 'Kelas ' + gradeMI : 'MI (Semua)');

        // MTS
        const gradeMTS = document.getElementById('gradeMTS').value;
        const btnMTS = document.getElementById('btnDlMTS');
        let urlMTS = "<?php echo e(route('grade.import.global.template', 'MTS')); ?>";
        if(gradeMTS) urlMTS += '?grade=' + gradeMTS;
        btnMTS.href = urlMTS;
        btnMTS.innerHTML = '<span class="material-symbols-outlined align-middle mr-2">download</span> Download Template ' + (gradeMTS ? 'Kelas ' + gradeMTS : 'MTS (Semua)');
        

    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u838039955/domains/rm.alhasany.or.id/public_html/resources/views/admin/grade_import/index.blade.php ENDPATH**/ ?>