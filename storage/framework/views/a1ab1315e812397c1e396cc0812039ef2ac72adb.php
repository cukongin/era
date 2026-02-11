<?php $__env->startSection('content'); ?>
<div class="p-10 flex flex-col items-center justify-center text-center h-[60vh]">
    <span class="material-symbols-outlined text-6xl text-slate-300 mb-4">school</span>
    <h2 class="text-xl font-bold text-slate-700 dark:text-white">Tidak Ada Kelas Jenjang Akhir</h2>
    <p class="text-slate-500 max-w-md my-2">
        Anda tidak memiliki akses ke Kelas Tingkat Akhir (Kelas 6 MI / Kelas 9 MTS) di Tahun Ajaran Aktif (<?php echo e($year->nama_tahun); ?>).
    </p>
    <a href="<?php echo e(route('dashboard')); ?>" class="text-indigo-600 hover:underline">Kembali ke Dashboard</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\XAMPP\htdocs\erapor\resources\views/ijazah/no-class.blade.php ENDPATH**/ ?>