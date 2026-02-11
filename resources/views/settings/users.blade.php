@extends('layouts.app')

@section('title', 'Manajemen User & Akses')

@section('content')
<div class="flex flex-col gap-6" x-data="{
    activeTab: 'users',
    selectedUsers: [],
    get allSelected() {
        return this.selectedUsers.length === {{ $users->count() }} && this.selectedUsers.length > 0;
    },
    toggleAll() {
        if (this.allSelected) {
            this.selectedUsers = [];
        } else {
            this.selectedUsers = [{{ $users->pluck('id')->map(fn($id) => "'$id'")->implode(',') }}];
        }
    }
}">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Manajemen User & Akses</h1>
            <p class="text-slate-500 text-sm">Kelola akun, sinkronisasi data guru, dan hak akses aplikasi.</p>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-slate-100 dark:bg-slate-800 p-1 rounded-lg flex gap-1">
            <button @click="activeTab = 'users'"
                :class="activeTab === 'users' ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                class="px-4 py-2 text-sm font-bold rounded-md transition-all">
                Akun Pengguna
            </button>
            <button @click="activeTab = 'sync'"
                :class="activeTab === 'sync' ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                class="px-4 py-2 text-sm font-bold rounded-md transition-all flex items-center gap-2">
                Sinkronisasi Guru
                @if($teachersWithoutAccount->count() > 0)
                <span class="bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ $teachersWithoutAccount->count() }}</span>
                @endif
            </button>
            <button @click="activeTab = 'permissions'"
                :class="activeTab === 'permissions' ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                class="px-4 py-2 text-sm font-bold rounded-md transition-all">
                Kontrol Akses
            </button>
        </div>
    </div>

    <!-- TAB 1: USERS LIST -->
    <div x-show="activeTab === 'users'" class="space-y-6">
        <!-- Filter Info -->
        <div class="flex gap-2">
            <a href="{{ route('settings.users.index') }}" class="px-3 py-1 text-xs font-bold rounded-full {{ !request('role') ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }}">Semua</a>
            <a href="{{ route('settings.users.index', ['role' => 'teacher']) }}" class="px-3 py-1 text-xs font-bold rounded-full {{ request('role') == 'teacher' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }}">Guru</a>
            <a href="{{ route('settings.users.index', ['role' => 'admin']) }}" class="px-3 py-1 text-xs font-bold rounded-full {{ request('role') == 'admin' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }}">Admin</a>
            <a href="{{ route('settings.users.index', ['role' => 'staff_tu']) }}" class="px-3 py-1 text-xs font-bold rounded-full {{ request('role') == 'staff_tu' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }}">Staff TU</a>
            <a href="{{ route('settings.users.index', ['role' => 'student']) }}" class="px-3 py-1 text-xs font-bold rounded-full {{ request('role') == 'student' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }}">Siswa</a>
        </div>

        <!-- Actions Bar -->
        @if(request('role'))
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                 <span class="material-symbols-outlined text-yellow-600">warning</span>
                 <div>
                     <h3 class="font-bold text-yellow-800 text-sm">Zone Bahaya: Mass Action</h3>
                     <p class="text-xs text-yellow-700">Aksi ini akan mereset password seluruh user dengan role <strong>{{ ucfirst(request('role')) }}</strong>.</p>
                 </div>
            </div>
            <form action="{{ route('settings.users.export') }}" method="POST"
                  data-confirm-delete="true"
                  data-title="RESET & EXPORT PASSWORD?"
                  data-message="PERINGATAN KERAS: Semua password user role {{ request('role') }} akan di-RESET dan diganti baru. File CSV berisi password baru akan didownload."
                  data-confirm-text="Ya, Reset & Export!"
                  data-confirm-color="#0f172a"
                  data-icon="warning">
                @csrf
                <input type="hidden" name="role" value="{{ request('role') }}">
                <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-sm shadow hover:bg-slate-800 flex items-center gap-2 transition-all">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Reset & Export Data {{ ucfirst(request('role')) }}
                </button>
            </form>
        </div>
        @endif

        <!-- Search -->
        <div class="bg-white dark:bg-[#1a2e22] p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <form action="{{ route('settings.users.index') }}" method="GET" class="relative">
                <input type="hidden" name="role" value="{{ request('role') }}">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama atau Email..." class="w-full pl-10 pr-4 py-2 rounded-lg border-slate-200 dark:bg-slate-800 dark:border-slate-700 text-sm focus:ring-primary focus:border-primary">
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <div class="px-4 py-3 bg-red-50 border-b border-red-200 flex items-center justify-between" x-show="selectedUsers.length > 0" x-cloak>
                   <div class="flex items-center gap-2 text-red-700">
                       <span class="font-bold x-text" x-text="selectedUsers.length + ' User terpilih'"></span>
                   </div>
                   <form action="{{ route('settings.users.bulk_destroy') }}" method="POST"
                         data-confirm-delete="true"
                         data-title="Hapus User Terpilih?"
                         data-message="Yakin ingin menghapus user terpilih? Data tidak dapat dikembalikan.">
                        @csrf
                        @method('DELETE')
                        <!-- Hidden inputs for each selected ID -->
                        <template x-for="id in selectedUsers">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit" class="bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-red-700 transition-colors">
                            Hapus Terpilih
                        </button>
                   </form>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="p-4 w-10">
                                <input type="checkbox" @click="toggleAll" :checked="allSelected" class="rounded border-slate-300 text-primary focus:ring-primary">
                            </th>
                            <th class="p-4 pl-2 text-xs font-bold text-slate-500 uppercase">User</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase">Role</th>
                            <th class="p-4 text-xs font-bold text-slate-500 uppercase">Email (Username)</th>
                            <th class="p-4 pr-6 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($users as $user)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors" :class="{'bg-primary/50': selectedUsers.includes('{{ $user->id }}')}">
                            <td class="p-4">
                                <input type="checkbox" value="{{ $user->id }}" x-model="selectedUsers" class="rounded border-slate-300 text-primary focus:ring-primary">
                            </td>
                            <td class="p-4 pl-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $user->name }}</span>
                                        @if($user->wali_kelas_aktif)
                                        <span class="text-[10px] text-orange-600 bg-orange-100 px-2 py-0.5 rounded-full w-fit max-w-[150px] truncate" title="Wali Kelas {{ $user->wali_kelas_aktif->nama_kelas }}">
                                            Wali Kelas {{ $user->wali_kelas_aktif->nama_kelas }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <form action="{{ route('settings.users.role', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" onchange="this.form.submit()" class="text-[10px] font-bold uppercase rounded-md border-transparent focus:ring-0 cursor-pointer py-1 pl-2 pr-8 appearance-none {{ $user->role == 'admin' ? 'bg-red-100 text-red-700' : ($user->role == 'teacher' ? 'bg-primary/10 text-primary' : ($user->role == 'staff_tu' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700')) }}">
                                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>ADMIN</option>
                                        <option value="teacher" {{ $user->role == 'teacher' ? 'selected' : '' }}>GURU</option>
                                        <option value="staff_tu" {{ $user->role == 'staff_tu' ? 'selected' : '' }}>STAFF TU</option>
                                        <option value="student" {{ $user->role == 'student' ? 'selected' : '' }}>SISWA</option>
                                    </select>
                                </form>
                            </td>
                            <td class="p-4">
                                <span class="font-mono text-sm text-slate-600 dark:text-slate-400">{{ $user->email }}</span>
                            </td>
                            <td class="p-4 pr-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('settings.users.impersonate', $user->id) }}" method="POST" target="_blank">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors" title="Login Ajaib (Masuk sebagai user ini)">
                                            <span class="material-symbols-outlined text-[16px]">bolt</span>
                                            Ajaib
                                        </button>
                                    </form>
                                    <form action="{{ route('settings.users.generate', $user->id) }}" method="POST"
                                          data-confirm-delete="true"
                                          data-title="Generate Ulang Akun?"
                                          data-message="Password lama akan hilang dan diganti baru.">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">vpn_key</span> Generate
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">Tidak ada user ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $users->appends(['role' => request('role'), 'search' => request('search')])->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 2: SYNC TEACHERS -->
    <div x-show="activeTab === 'sync'" class="space-y-6">
        <div class="bg-primary/5 border border-primary/20 p-6 rounded-xl">
            <h3 class="font-bold text-primary mb-2">Sinkronisasi Akun Guru</h3>
            <p class="text-sm text-primary/70 mb-0">Daftar guru dibawah ini belum memiliki akun login. Klik "Buat Akun" untuk membuatkan akun guru secara otomatis sesuai NIP/Data Guru.</p>
        </div>

        <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
             <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="p-4 pl-6 text-xs font-bold text-slate-500 uppercase">Nama Guru</th>
                        <th class="p-4 text-xs font-bold text-slate-500 uppercase">NIP / NUPTK</th>
                        <th class="p-4 pr-6 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($teachersWithoutAccount as $teacher)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="p-4 pl-6 text-sm font-bold text-slate-900 dark:text-white">{{ $teacher->nama }}</td>
                        <td class="p-4 text-sm text-slate-600 dark:text-slate-400">{{ $teacher->nip ?? $teacher->nuptk ?? '-' }}</td>
                        <td class="p-4 pr-6 text-right">
                             <form action="{{ route('settings.users.sync-teacher') }}" method="POST">
                                @csrf
                                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                                <button type="submit" class="px-4 py-2 bg-primary text-white hover:bg-primary-dark rounded-lg text-xs font-bold whitespace-nowrap shadow-sm hover:shadow-md transition-all">
                                    + Buat Akun
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-12 text-center">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-2xl">check</span>
                                </div>
                                <span class="font-bold text-slate-900 dark:text-white">Semua Aman!</span>
                                <span class="text-sm text-slate-500">Semua guru sudah memiliki akun login.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: PERMISSIONS -->
    <div x-show="activeTab === 'permissions'" class="space-y-6">
        <form action="{{ route('settings.users.permissions') }}" method="POST">
            @csrf

            <div class="bg-white dark:bg-[#1a2e22] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-8">

                <!-- Guru Permissions -->
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">school</span>
                        Hak Akses Guru
                    </h3>
                    <div class="space-y-3 pl-8">
                        @php
                            $guruKeys = [
                                'access_guru_input_nilai' => 'Bisa Akses Menu Input Nilai & Jadwal',
                            ];
                        @endphp
                        @foreach($guruKeys as $key => $label)
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" id="{{ $key }}" value="1" {{ old($key, \App\Models\GlobalSetting::val($key, 1)) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary cursor-pointer">
                            <label for="{{ $key }}" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <hr class="border-slate-100 dark:border-slate-800">

                <!-- Wali Kelas Permissions -->
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-orange-500">supervisor_account</span>
                        Hak Akses Wali Kelas
                    </h3>
                    <div class="space-y-3 pl-8">
                        @php
                            $waliKeys = [
                                'access_wali_input_catatan' => 'Bisa Akses Menu Catatan Siswa',
                                'access_wali_input_absensi' => 'Bisa Akses Menu Absensi',
                                'access_wali_input_ekskul' => 'Bisa Akses Menu Ekstrakurikuler',
                                'access_wali_kenaikan_kelas' => 'Bisa Akses Menu Kenaikan Kelas',
                                'access_wali_cetak_rapor' => 'Bisa Akses Menu Cetak Rapor',
                                'access_wali_monitoring_nilai' => 'Bisa Akses Menu Monitoring Penilaian', // Added
                            ];
                        @endphp
                        @foreach($waliKeys as $key => $label)
                         <div class="flex items-center gap-3">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" id="{{ $key }}" value="1" {{ old($key, \App\Models\GlobalSetting::val($key, 1)) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary cursor-pointer">
                            <label for="{{ $key }}" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg">
                        Simpan Pengaturan Akses
                    </button>
                </div>

            </div>
        </form>
    </div>

</div>

<!-- Credential Modal -->
@if(session('generated_credential'))
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-data="{ open: true }" x-show="open" x-cloak>
    <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-2xl w-full max-w-md p-6 border border-slate-200 dark:border-slate-800 animate-in fade-in zoom-in duration-200">
        <div class="text-center mb-6">
             <div class="bg-green-100 text-green-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                <span class="material-symbols-outlined text-3xl">check_circle</span>
            </div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Akun Digenerate!</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kredensial baru untuk <strong>{{ session('generated_credential')['role'] }}</strong>.</p>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-xl border border-slate-100 dark:border-slate-800 space-y-4 mb-6">
            <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                <span class="text-xs font-bold text-slate-500 uppercase">Nama</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ session('generated_credential')['name'] }}</span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-200 pb-2 gap-4">
                <span class="text-xs font-bold text-slate-500 uppercase">Email</span>
                <span class="font-mono text-base font-bold text-primary truncate">{{ session('generated_credential')['email'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500 uppercase">Password</span>
                <span class="font-mono text-xl font-bold text-slate-900 dark:text-white bg-white dark:bg-slate-900 px-3 py-1 rounded border border-slate-200 shadow-sm">
                    {{ session('generated_credential')['password'] }}
                </span>
            </div>
        </div>

        <button @click="open = false" class="w-full py-3 bg-slate-900 dark:bg-slate-700 text-white font-bold rounded-xl hover:bg-slate-800 transform hover:scale-[1.02] transition-all shadow-lg">
            Selesai & Tutup
        </button>
    </div>
</div>

@endif

@endsection
