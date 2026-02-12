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
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-50 text-slate-800 antialiased h-screen flex items-center justify-center relative overflow-hidden">

    <!-- Background Decor -->
    <div class="absolute top-0 left-0 w-full h-1/2 bg-primary/10 -skew-y-3 -z-10 origin-top-left"></div>

    <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="p-8 pb-6">
            <div class="flex flex-col items-center gap-2 mb-8">
                <div class="bg-primary/10 p-3 rounded-xl mb-2">
                    <span class="material-symbols-outlined text-primary text-4xl">admin_panel_settings</span>
                </div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight text-center">Login Admin</h1>
                <p class="text-sm text-slate-500 text-center">Masuk dengan Email & Password</p>
            </div>

            @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 text-sm p-3 rounded-lg flex items-start gap-2">
                <span class="material-symbols-outlined text-sm mt-0.5">error</span>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('login.admin.post') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">Email Address</label>
                    <input type="email" name="email" class="w-full rounded-lg border-slate-300 focus:border-primary focus:ring-primary text-sm placeholder-slate-400" placeholder="admin@madrasah.com" value="{{ old('email') }}" required>
                </div>

                <!-- Honeypot for Bots (Hidden) -->
                <div class="hidden">
                    <label>Don't fill this out if you're human: <input type="text" name="website" value="{{ old('website') }}" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">Password</label>
                    <input type="password" name="password" class="w-full rounded-lg border-slate-300 focus:border-primary focus:ring-primary text-sm placeholder-slate-400" placeholder="********" required>
                </div>

                <div class="flex items-center justify-between mt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary">
                        <span class="text-xs text-slate-600">Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="mt-4 bg-primary hover:bg-primary-dark text-white font-bold py-2.5 rounded-lg shadow-lg shadow-primary/30 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-sm">login</span>
                    Log In Admin
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-xs text-slate-500 hover:text-surface-dark transition-colors flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-sm">key</span>
                    Masuk dengan Kode
                </a>
            </div>
        </div>
        <div class="bg-gray-50 border-t border-gray-100 p-4 text-center">
            <p class="text-xs text-slate-400">Integrated System MI & MTs Diniyah</p>
        </div>
    </div>

</body>
</html>

