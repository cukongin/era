<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\GlobalSetting::val('app_name', 'Madrasah Integrated System') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet"/> <!-- Arabic Support -->
    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet"/> <!-- Local Fonts (LPMQ) -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <style>
    .material-symbols-outlined {
      font-family: 'Material Symbols Outlined';
      font-weight: normal;
      font-style: normal;
      font-size: 24px;  /* Preferred icon size */
      display: inline-block;
      line-height: 1;
      text-transform: none;
      letter-spacing: normal;
      word-wrap: normal;
      white-space: nowrap;
      direction: ltr;
    }
    .material-symbols-outlined.filled {
        font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Compiled CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed lg:relative inset-y-0 left-0 w-72 bg-white dark:bg-surface-dark border-r border-slate-200 dark:border-slate-800 flex flex-col z-30 shadow-sm transition-transform duration-300 ease-in-out lg:transform-none">
            <div class="flex items-center justify-between px-6 py-6 border-b border-slate-100 dark:border-slate-800/50">
                <div class="flex items-center gap-3">
                    @if(\App\Models\GlobalSetting::val('app_logo'))
                         <img src="{{ asset('public/' . \App\Models\GlobalSetting::val('app_logo')) }}" class="h-10 w-auto object-contain">
                    @else
                        <div class="bg-primary/10 rounded-xl p-2">
                            <span class="material-symbols-outlined text-primary text-3xl">mosque</span>
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <h1 class="text-lg font-bold leading-tight tracking-tight">{{ \App\Models\GlobalSetting::val('app_name', 'Madrasah Admin') }}</h1>
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-medium">{{ \App\Models\GlobalSetting::val('app_tagline', 'Integrated System') }}</p>
                    </div>
                </div>
                <!-- Close Button Mobile -->
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- IMPERSONATION ALERT -->
            @if(session('impersonator_id'))
            <div class="px-4 pt-4">
                <form action="{{ route('impersonate.leave') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-red-500 text-white p-3 rounded-xl flex items-center justify-center gap-2 hover:bg-red-600 transition-colors shadow-lg shadow-red-500/20 animate-pulse">
                        <span class="material-symbols-outlined">no_accounts</span>
                        <div class="flex flex-col text-left">
                            <span class="text-[10px] font-bold uppercase opacity-80">Mode Penyamaran</span>
                            <span class="text-xs font-bold">KEMBALI KE ADMIN</span>
                        </div>
                    </button>
                </form>
            </div>
            @endif

            <nav class="flex flex-col gap-1 px-4 py-6 flex-1 overflow-y-auto" x-data="{ activeGroup: '{{ Request::is('master*') || Request::is('classes*') ? 'master' : (Request::is('settings*') ? 'settings' : (Request::is('walikelas*') || Request::is('reports*') ? 'walikelas' : (Request::is('teacher*') ? 'teacher' : ''))) }}' }">

                @if(isset($sidebarMenus))

                    {{-- HARDCODED MENU FOR ADMIN/TU GLOBAL MONITORING --}}
                    {{-- @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTu()))
                    <a class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('tu.monitoring.global') ? 'bg-primary/10 text-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }} transition-all mb-1" href="{{ route('tu.monitoring.global') }}">
                        <span class="material-symbols-outlined filled">monitoring</span>
                        <span class="font-semibold text-sm">Global Monitoring</span>
                    </a>
                    @endif --}}

                    @foreach($sidebarMenus as $menu)
                        {{-- INJECTED MENU: IJAZAH (Only for Final Year Wali Kelas) --}}
                        @if($loop->first && auth()->check() && auth()->user()->isWaliKelas())
                            @php
                                $__activeYearId = \App\Models\TahunAjaran::where('status', 'aktif')->value('id');
                                $__waliClass = \App\Models\Kelas::where('id_wali_kelas', auth()->id())
                                    ->where('id_tahun_ajaran', $__activeYearId)
                                    ->first();
                                $__isFinal = $__waliClass && in_array($__waliClass->tingkat_kelas, [6, 9, 12]);
                            @endphp

                            @if($__isFinal)
                            <a class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('ijazah.*') ? 'bg-amber-100/80 text-amber-800' : 'text-slate-600 dark:text-slate-400 hover:bg-amber-50 dark:hover:bg-amber-900/20' }} transition-all mb-2 border border-amber-300 dark:border-amber-600 shadow-sm" href="{{ route('ijazah.index') }}">
                                <span class="material-symbols-outlined filled text-amber-600">school</span>
                                <span class="font-bold text-sm text-amber-800 dark:text-amber-400">Nilai Ujian (Ijazah)</span>
                            </a>
                            @endif
                        @endif
                        @php
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
                        @endphp

                        @if($hasAccess)
                            @if(!$hasChildren)
                                <!-- Single Menu -->
                                <a class="flex items-center gap-3 px-4 py-3 rounded-xl {{ (request()->url() == url($menu->url) || ($menu->route && request()->routeIs($menu->route))) ? 'bg-primary/10 text-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }} transition-all" href="{{ $menu->getSafeUrl() }}">
                                    <span class="material-symbols-outlined filled">{{ $menu->icon }}</span>
                                    <span class="font-semibold text-sm">{{ $menu->title }}</span>
                                </a>
                            @else
                                <!-- Dropdown Menu -->
                                <div class="space-y-1">
                                    <button @click="activeGroup = (activeGroup === '{{ $menu->id }}' ? '' : '{{ $menu->id }}')"
                                        :class="{ 'text-primary': activeGroup === '{{ $menu->id }}' || '{{ $isActiveGroup }}' }"
                                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all font-semibold text-sm group">
                                        <div class="flex items-center gap-3">
                                            <span class="material-symbols-outlined group-hover:text-primary transition-colors">{{ $menu->icon }}</span>
                                            <span>{{ $menu->title }}</span>
                                        </div>
                                        <span class="material-symbols-outlined text-slate-400 text-sm transition-transform duration-200" :class="{ 'rotate-180': activeGroup === '{{ $menu->id }}' }">expand_more</span>
                                    </button>

                                    <div x-show="activeGroup === '{{ $menu->id }}' || (activeGroup === '' && '{{ $isActiveGroup }}')" x-collapse class="pl-4 space-y-1">
                                        @foreach($menu->children as $child)
                                            @php
                                                $childAllowed = $child->roles->pluck('role')->toArray();
                                                $childAccess = false;
                                                if ($user->isAdmin() && in_array('admin', $childAllowed)) $childAccess = true;
                                                if ($user->isTeacher() && in_array('teacher', $childAllowed)) $childAccess = true;
                                                if ($user->isWaliKelas() && in_array('walikelas', $childAllowed)) $childAccess = true;
                                                if ($user->isStaffTu() && in_array('staff_tu', $childAllowed)) $childAccess = true;
                                            @endphp

                                            @if($childAccess)
                                            <a class="flex items-center gap-3 px-4 py-2 rounded-lg {{ (request()->url() == url($child->url) || ($child->route && request()->routeIs($child->route))) ? 'text-primary bg-primary/5' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }} transition-all" href="{{ $child->getSafeUrl() }}">
                                                <span class="material-symbols-outlined text-[18px]">{{ $child->icon }}</span>
                                                <span class="text-sm">{{ $child->title }}</span>
                                            </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    @endforeach
                @endif



            </nav>

            <div class="p-4 border-t border-slate-100 dark:border-slate-800/50">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                    <div class="bg-gray-200 rounded-full h-10 w-10 flex items-center justify-center text-gray-500">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <div class="flex flex-col overflow-hidden flex-1">
                        <span class="text-sm font-bold truncate">{{ Auth::user()->name ?? 'Administrator' }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 truncate">
                            {{ Auth::user()->role == 'admin' ? 'Administrator' : (Auth::user()->role == 'teacher' ? 'Guru' : (Auth::user()->role == 'walikelas' ? 'Wali Kelas' : 'User')) }}
                        </span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
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
                            <span class="text-slate-800 dark:text-slate-200">@yield('title', 'Dashboard')</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 lg:p-10 pb-20">
                <!-- Notifications Area -->
                @auth
                    @php
                        $unreadNotifs = \App\Models\Notification::where('user_id', auth()->id())
                                        ->where('is_read', false)
                                        ->latest()
                                        ->get();
                    @endphp
                    @if($unreadNotifs->count() > 0)
                        <div class="mb-6 space-y-2">
                            @foreach($unreadNotifs as $notif)
                            <div class="rounded-lg p-4 flex justify-between items-start shadow-sm border-l-4 {{ $notif->type == 'warning' ? 'bg-orange-50 border-orange-400 dark:bg-orange-900/20 dark:border-orange-600' : ($notif->type == 'info' ? 'bg-teal-50 border-teal-400 dark:bg-teal-900/20 dark:border-teal-600' : 'bg-blue-50 border-blue-400') }}">
                                <div class="flex gap-3">
                                    <span class="material-symbols-outlined {{ $notif->type == 'warning' ? 'text-orange-600 dark:text-orange-500' : ($notif->type == 'info' ? 'text-teal-600 dark:text-teal-500' : 'text-blue-600') }}">
                                        {{ $notif->type == 'warning' ? 'warning' : 'info' }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold uppercase {{ $notif->type == 'warning' ? 'text-orange-800 dark:text-orange-200' : ($notif->type == 'info' ? 'text-teal-800 dark:text-teal-200' : 'text-blue-800') }}">
                                            {{ $notif->type == 'warning' ? 'PERINGATAN' : ($notif->type == 'info' ? 'INFORMASI' : 'PENGINGAT') }}
                                        </p>
                                        <p class="text-sm {{ $notif->type == 'warning' ? 'text-orange-700 dark:text-orange-300' : ($notif->type == 'info' ? 'text-teal-700 dark:text-teal-300' : 'text-blue-700') }}">
                                            {{ $notif->message }}
                                        </p>
                                    </div>
                                </div>
                                <form action="{{ route('dashboard.notification.read', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="{{ $notif->type == 'warning' ? 'text-orange-600 hover:text-orange-800' : ($notif->type == 'info' ? 'text-teal-600 hover:text-teal-800' : 'text-blue-600 hover:text-blue-800') }} text-xs font-bold underline">OKE</button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    @endif
                @endauth

                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')

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
            @if(session('success'))
                Toast.fire({
                    icon: 'success',
                    title: "{{ session('success') }}",
                    background: '#ecfdf5', // emerald-50
                    color: '#065f46', // emerald-900
                    iconColor: '#10b981' // emerald-500
                });
            @endif

            // 3. Session: Error (Modal - More prominent)
            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Waduh...',
                    html: `<div class="text-sm text-slate-600 dark:text-slate-300">{{ session('error') }}</div>`,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#ef4444', // red-500
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b'
                });
            @endif

            // 4. Validation Errors (Modal)
            @if($errors->any())
                let errorHtml = '<div class="text-left text-sm space-y-2 bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">';
                @foreach($errors->all() as $error)
                    errorHtml += '<div class="flex items-start gap-2 text-red-700 dark:text-red-300"><span class="material-symbols-outlined text-sm mt-0.5 transform scale-90">circle</span><span>{{ $error }}</span></div>';
                @endforeach
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
            @endif

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

