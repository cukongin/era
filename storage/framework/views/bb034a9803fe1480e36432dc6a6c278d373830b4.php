<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Rapor Madrasah</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Compiled CSS -->
    <link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">
</head>
<body class="bg-gray-50 text-slate-800 antialiased h-screen flex items-center justify-center relative overflow-hidden">

    <!-- Background Decor -->
    <div class="absolute top-0 left-0 w-full h-1/2 bg-login-primary/10 -skew-y-3 -z-10 origin-top-left"></div>

    <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="p-8 pb-6">
            <div class="flex flex-col items-center gap-2 mb-8">
                <div class="bg-login-primary/10 p-3 rounded-xl mb-2">
                    <span class="material-symbols-outlined text-login-primary text-4xl">mosque</span>
                </div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight text-center">E-Rapor Madrasah</h1>
                <p class="text-sm text-slate-500 text-center">Silakan login untuk masuk sistem</p>
            </div>

            <?php if($errors->any()): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 text-sm p-3 rounded-lg flex items-start gap-2">
                <span class="material-symbols-outlined text-sm mt-0.5">error</span>
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <?php endif; ?>

            <form action="<?php echo e(route('login.post')); ?>" method="POST" class="flex flex-col gap-4">
                <?php echo csrf_field(); ?>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">Email Address</label>
                    <input type="email" name="email" class="w-full rounded-lg border-slate-300 focus:border-login-primary focus:ring-login-primary text-sm placeholder-slate-400" placeholder="admin@madrasah.com" value="<?php echo e(old('email')); ?>" required>
                </div>

                <!-- Honeypot for Bots (Hidden) -->
                <div class="hidden">
                    <label>Don't fill this out if you're human: <input type="text" name="website" value="<?php echo e(old('website')); ?>" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">Password</label>
                    <input type="password" name="password" class="w-full rounded-lg border-slate-300 focus:border-login-primary focus:ring-login-primary text-sm placeholder-slate-400" placeholder="••••••••" required>
                </div>

                <div class="flex items-center justify-between mt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="rounded border-slate-300 text-login-primary focus:ring-login-primary">
                        <span class="text-xs text-slate-600">Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="mt-4 bg-login-primary hover:bg-login-primary-dark text-white font-bold py-2.5 rounded-lg shadow-lg shadow-login-primary/30 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-sm">login</span>
                    Log In
                </button>
            </form>
        </div>
        <div class="bg-gray-50 border-t border-gray-100 p-4 text-center">
            <p class="text-xs text-slate-400">Integrated System MI & MTs Diniyah</p>
        </div>
    </div>

</body>
</html>
<?php /**PATH D:\XAMPP\htdocs\erapor\resources\views/auth/login.blade.php ENDPATH**/ ?>