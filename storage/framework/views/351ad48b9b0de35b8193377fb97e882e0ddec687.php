<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(\App\Models\GlobalSetting::val('app_name', 'Madrasah Integrated System')); ?></title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet"/> <!-- Arabic Support -->
    <link href="<?php echo e(asset('css/fonts.css')); ?>" rel="stylesheet"/> <!-- Local Fonts (LPMQ) -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <!-- Tailwind CSS (CDN for Prototyping) -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
            colors: {
              "primary": "#003e29", // Kaitoke Green (Deep Green)
              "secondary": "#467061", // Como (Greenish Gray)
              "accent": "#fee46d", // Kournikova (Yellow/Gold)
              "background-light": "#f6f8f6",
              "background-dark": "#002a1c", // Darker Green for bg
            },
            fontFamily: {
              "display": ["Inter", "Amiri", "sans-serif"],
              "sans": ["Inter", "Amiri", "sans-serif"],
              "serif": ["Amiri", "Times New Roman", "serif"] 
            },
            },
          },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
        
        .fouc-cloak { opacity: 0; visibility: hidden; transition: opacity 0.3s ease-in-out; }
        [x-cloak] { display: none !important; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.remove('fouc-cloak');
        });
    </script>
</head>
<body class="fouc-cloak bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-100 antialiased overflow-hidden">
    <div class="flex h-screen w-full" x-data="{ sidebarOpen: false }">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-slate-900/50 z-20 lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed lg:relative inset-y-0 left-0 w-72 bg-white dark:bg-[#1a2e22] border-r border-slate-200 dark:border-slate-800 flex flex-col z-30 shadow-sm transition-transform duration-300 ease-in-out lg:transform-none">
            <div class="flex items-center justify-between px-6 py-6 border-b border-slate-100 dark:border-slate-800/50">
                <div class="flex items-center gap-3">
                    <?php if(\App\Models\GlobalSetting::val('app_logo')): ?>
                         <img src="<?php echo e(asset('public/' . \App\Models\GlobalSetting::val('app_logo'))); ?>" class="h-10 w-auto object-contain">
                    <?php else: ?>
                        <div class="bg-primary/10 rounded-xl p-2">
                            <span class="material-symbols-outlined text-primary text-3xl">mosque</span>
                        </div>
                    <?php endif; ?>
                    <div class="flex flex-col">
                        <h1 class="text-lg font-bold leading-tight tracking-tight"><?php echo e(\App\Models\GlobalSetting::val('app_name', 'Madrasah Admin')); ?></h1>
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-medium"><?php echo e(\App\Models\GlobalSetting::val('app_tagline', 'Integrated System')); ?></p>
                    </div>
                </div>
                <!-- Close Button Mobile -->
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- IMPERSONATION ALERT -->
            <?php if(session('impersonator_id')): ?>
            <div class="px-4 pt-4">
                <form action="<?php echo e(route('impersonate.leave')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="w-full bg-red-500 text-white p-3 rounded-xl flex items-center justify-center gap-2 hover:bg-red-600 transition-colors shadow-lg shadow-red-500/20 animate-pulse">
                        <span class="material-symbols-outlined">no_accounts</span>
                        <div class="flex flex-col text-left">
                            <span class="text-[10px] font-bold uppercase opacity-80">Mode Penyamaran</span>
                            <span class="text-xs font-bold">KEMBALI KE ADMIN</span>
                        </div>
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
            <nav class="flex flex-col gap-1 px-4 py-6 flex-1 overflow-y-auto" x-data="{ activeGroup: '<?php echo e(Request::is('master*') || Request::is('classes*') ? 'master' : (Request::is('settings*') ? 'settings' : (Request::is('walikelas*') || Request::is('reports*') ? 'walikelas' : (Request::is('teacher*') ? 'teacher' : '')))); ?>' }">
                
                <?php if(isset($sidebarMenus)): ?>
                    
                    
                    

                    <?php $__currentLoopData = $sidebarMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        
                        <?php if($loop->first && auth()->check() && auth()->user()->isWaliKelas()): ?>
                            <?php
                                $__activeYearId = \App\Models\TahunAjaran::where('status', 'aktif')->value('id');
                                $__waliClass = \App\Models\Kelas::where('id_wali_kelas', auth()->id())
                                    ->where('id_tahun_ajaran', $__activeYearId)
                                    ->first();
                                $__isFinal = $__waliClass && in_array($__waliClass->tingkat_kelas, [6, 9, 12]);
                            ?>

                            <?php if($__isFinal): ?>
                            <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo e(request()->routeIs('ijazah.*') ? 'bg-amber-100/80 text-amber-800' : 'text-slate-600 dark:text-slate-400 hover:bg-amber-50 dark:hover:bg-amber-900/20'); ?> transition-all mb-2 border border-amber-300 dark:border-amber-600 shadow-sm" href="<?php echo e(route('ijazah.index')); ?>">
                                <span class="material-symbols-outlined filled text-amber-600">school</span>
                                <span class="font-bold text-sm text-amber-800 dark:text-amber-400">Nilai Ujian (Ijazah)</span>
                            </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php
                            // Check Role Permission
                            $allowedRoles = $menu->roles->pluck('role')->toArray();
                            $user = Auth::user();
                            $hasAccess = false;
                            
                            if ($user->isAdmin() && in_array('admin', $allowedRoles)) $hasAccess = true;
                            if ($user->isTeacher() && in_array('teacher', $allowedRoles)) $hasAccess = true;
                            if ($user->isWaliKelas() && in_array('walikelas', $allowedRoles)) $hasAccess = true;
                            if ($user->isStudent() && in_array('student', $allowedRoles)) $hasAccess = true;
                            if ($user->isStaffTu() && in_array('staff_tu', $allowedRoles)) $hasAccess = true;
                            
                            // Special Condition for "Input Nilai" (Optional)
                            // if ($menu->title == 'Input Nilai' && ...) 

                            $hasChildren = $menu->children->isNotEmpty();
                            $isActiveGroup = false;
                            
                            if ($hasChildren) {
                                foreach($menu->children as $child) {
                                    if (request()->url() == url($child->url) || ($child->route && request()->routeIs($child->route))) {
                                        $isActiveGroup = true;
                                        break;
                                    }
                                }
                            }
                        ?>

                        <?php if($hasAccess): ?>
                            <?php if(!$hasChildren): ?>
                                <!-- Single Menu -->
                                <a class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo e((request()->url() == url($menu->url) || ($menu->route && request()->routeIs($menu->route))) ? 'bg-primary/10 text-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'); ?> transition-all" href="<?php echo e($menu->getSafeUrl()); ?>">
                                    <span class="material-symbols-outlined filled"><?php echo e($menu->icon); ?></span>
                                    <span class="font-semibold text-sm"><?php echo e($menu->title); ?></span>
                                </a>
                            <?php else: ?>
                                <!-- Dropdown Menu -->
                                <div class="space-y-1">
                                    <button @click="activeGroup = (activeGroup === '<?php echo e($menu->id); ?>' ? '' : '<?php echo e($menu->id); ?>')" 
                                        :class="{ 'text-primary': activeGroup === '<?php echo e($menu->id); ?>' || '<?php echo e($isActiveGroup); ?>' }"
                                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all font-semibold text-sm group">
                                        <div class="flex items-center gap-3">
                                            <span class="material-symbols-outlined group-hover:text-primary transition-colors"><?php echo e($menu->icon); ?></span>
                                            <span><?php echo e($menu->title); ?></span>
                                        </div>
                                        <span class="material-symbols-outlined text-slate-400 text-sm transition-transform duration-200" :class="{ 'rotate-180': activeGroup === '<?php echo e($menu->id); ?>' }">expand_more</span>
                                    </button>
                                    
                                    <div x-show="activeGroup === '<?php echo e($menu->id); ?>' || (activeGroup === '' && '<?php echo e($isActiveGroup); ?>')" x-collapse class="pl-4 space-y-1">
                                        <?php $__currentLoopData = $menu->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $childAllowed = $child->roles->pluck('role')->toArray();
                                                $childAccess = false;
                                                if ($user->isAdmin() && in_array('admin', $childAllowed)) $childAccess = true;
                                                if ($user->isTeacher() && in_array('teacher', $childAllowed)) $childAccess = true;
                                                if ($user->isWaliKelas() && in_array('walikelas', $childAllowed)) $childAccess = true;
                                                if ($user->isStaffTu() && in_array('staff_tu', $childAllowed)) $childAccess = true;
                                            ?>

                                            <?php if($childAccess): ?>
                                            <a class="flex items-center gap-3 px-4 py-2 rounded-lg <?php echo e((request()->url() == url($child->url) || ($child->route && request()->routeIs($child->route))) ? 'text-primary bg-primary/5' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'); ?> transition-all" href="<?php echo e($child->getSafeUrl()); ?>">
                                                <span class="material-symbols-outlined text-[18px]"><?php echo e($child->icon); ?></span>
                                                <span class="text-sm"><?php echo e($child->title); ?></span>
                                            </a>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>



            </nav>

            <div class="p-4 border-t border-slate-100 dark:border-slate-800/50">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                    <div class="bg-gray-200 rounded-full h-10 w-10 flex items-center justify-center text-gray-500">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <div class="flex flex-col overflow-hidden flex-1">
                        <span class="text-sm font-bold truncate"><?php echo e(Auth::user()->name ?? 'Administrator'); ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 truncate">
                            <?php echo e(Auth::user()->role == 'admin' ? 'Administrator' : (Auth::user()->role == 'teacher' ? 'Guru' : (Auth::user()->role == 'walikelas' ? 'Wali Kelas' : 'User'))); ?>

                        </span>
                    </div>
                    
                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors" title="Logout">
                            <span class="material-symbols-outlined text-[20px]">logout</span>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-full overflow-hidden relative">
            <header class="h-18 min-h-[72px] bg-white dark:bg-[#002a1c] border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 lg:px-10 z-10 sticky top-0">
                <div class="flex items-center gap-4">
                     <!-- Mobile Toggle -->
                     <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary">
                        <span class="material-symbols-outlined">menu</span>
                     </button>
                    
                     <div class="flex flex-col">
                        <div class="flex items-center gap-2 text-slate-400 text-xs font-medium">
                            <span>Home</span>
                            <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                            <span class="text-slate-800 dark:text-slate-200"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 lg:p-10 pb-20">
                <!-- Notifications Area -->
                <?php if(auth()->guard()->check()): ?>
                    <?php
                        $unreadNotifs = \App\Models\Notification::where('user_id', auth()->id())
                                        ->where('is_read', false)
                                        ->latest()
                                        ->get();
                    ?>
                    <?php if($unreadNotifs->count() > 0): ?>
                        <div class="mb-6 space-y-2">
                            <?php $__currentLoopData = $unreadNotifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-lg p-4 flex justify-between items-start shadow-sm border-l-4 <?php echo e($notif->type == 'warning' ? 'bg-orange-50 border-orange-400 dark:bg-orange-900/20 dark:border-orange-600' : ($notif->type == 'info' ? 'bg-teal-50 border-teal-400 dark:bg-teal-900/20 dark:border-teal-600' : 'bg-blue-50 border-blue-400')); ?>">
                                <div class="flex gap-3">
                                    <span class="material-symbols-outlined <?php echo e($notif->type == 'warning' ? 'text-orange-600 dark:text-orange-500' : ($notif->type == 'info' ? 'text-teal-600 dark:text-teal-500' : 'text-blue-600')); ?>">
                                        <?php echo e($notif->type == 'warning' ? 'warning' : 'info'); ?>

                                    </span>
                                    <div>
                                        <p class="text-sm font-bold uppercase <?php echo e($notif->type == 'warning' ? 'text-orange-800 dark:text-orange-200' : ($notif->type == 'info' ? 'text-teal-800 dark:text-teal-200' : 'text-blue-800')); ?>">
                                            <?php echo e($notif->type == 'warning' ? 'PERINGATAN' : ($notif->type == 'info' ? 'INFORMASI' : 'PENGINGAT')); ?>

                                        </p>
                                        <p class="text-sm <?php echo e($notif->type == 'warning' ? 'text-orange-700 dark:text-orange-300' : ($notif->type == 'info' ? 'text-teal-700 dark:text-teal-300' : 'text-blue-700')); ?>">
                                            <?php echo e($notif->message); ?>

                                        </p>
                                    </div>
                                </div>
                                <form action="<?php echo e(route('dashboard.notification.read', $notif->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="<?php echo e($notif->type == 'warning' ? 'text-orange-600 hover:text-orange-800' : ($notif->type == 'info' ? 'text-teal-600 hover:text-teal-800' : 'text-blue-600 hover:text-blue-800')); ?> text-xs font-bold underline">OKE</button>
                                </form>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>
    </div>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    
    <!-- Global SweetAlert Handler -->
    <!-- Global SweetAlert Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Toast Mixin Configuration
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                padding: '1em',
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // 2. Session: Success (Toast)
            <?php if(session('success')): ?>
                Toast.fire({
                    icon: 'success',
                    title: "<?php echo e(session('success')); ?>",
                    background: '#ecfdf5', // emerald-50
                    color: '#065f46', // emerald-900
                    iconColor: '#10b981' // emerald-500
                });
            <?php endif; ?>

            // 3. Session: Error (Modal - More prominent)
            <?php if(session('error')): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Waduh...',
                    html: `<div class="text-sm text-slate-600 dark:text-slate-300"><?php echo e(session('error')); ?></div>`,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#ef4444', // red-500
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b'
                });
            <?php endif; ?>

            // 4. Validation Errors (Modal)
            <?php if($errors->any()): ?>
                let errorHtml = '<div class="text-left text-sm space-y-2 bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">';
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    errorHtml += '<div class="flex items-start gap-2 text-red-700 dark:text-red-300"><span class="material-symbols-outlined text-sm mt-0.5 transform scale-90">circle</span><span><?php echo e($error); ?></span></div>';
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                errorHtml += '</div>';

                Swal.fire({
                    icon: 'warning',
                    title: 'Periksa Kembali Input',
                    html: errorHtml,
                    confirmButtonText: 'Saya Perbaiki',
                    confirmButtonColor: '#f59e0b', // amber-500
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b'
                });
            <?php endif; ?>

            // 5. Global Confirmation Handler
            // Usage: <form ... data-confirm-delete="true" data-title="..." data-message="..." data-confirm-text="Ya, Simpan!" data-confirm-color="#10b981">
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.getAttribute('data-confirm-delete') === 'true') {
                    e.preventDefault();
                    
                    const message = form.getAttribute('data-message') || 'Data yang dihapus tidak dapat dikembalikan!';
                    const title = form.getAttribute('data-title') || 'Yakin Hapus?';
                    const confirmText = form.getAttribute('data-confirm-text') || 'Ya, Hapus!';
                    const cancelText = form.getAttribute('data-cancel-text') || 'Batal';
                    const confirmColor = form.getAttribute('data-confirm-color') || '#ef4444'; // Default Red
                    const iconType = form.getAttribute('data-icon') || 'warning';

                    Swal.fire({
                        title: title,
                        text: message,
                        icon: iconType,
                        showCancelButton: true,
                        confirmButtonColor: confirmColor,
                        cancelButtonColor: '#64748b', // slate-500
                        confirmButtonText: confirmText,
                        cancelButtonText: cancelText,
                        background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
<?php /**PATH /home/u838039955/domains/rm.alhasany.or.id/public_html/resources/views/layouts/app.blade.php ENDPATH**/ ?>