@extends('layouts.app')

@section('title', $pageContext['title'])

@section('content')
<div class="space-y-6">
    <!-- Check for Admin Filter -->
    @if(auth()->user()->isAdmin() || auth()->user()->isTu())
    <div class="mb-2">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Filter (Admin Mode)</h3>
        <form action="{{ url()->current() }}" method="GET" class="grid grid-cols-12 gap-3 items-center">
            <!-- Jenjang Toggle -->
            <div class="col-span-5 md:col-span-auto flex p-1 bg-slate-100 dark:bg-[#1a2332] rounded-xl border-2 border-slate-200 dark:border-slate-700 h-[46px]">
                @foreach(['MI', 'MTS'] as $j)
                <button type="submit" name="jenjang" value="{{ $j }}" 
                    class="flex-1 px-3 text-sm font-bold rounded-lg transition-all flex items-center justify-center {{ (request('jenjang') == $j || (empty(request('jenjang')) && $loop->first)) ? 'bg-white dark:bg-slate-700 text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                    {{ $j }}
                </button>
                @endforeach
            </div>
            <!-- Class Selector -->
            <div class="col-span-7 md:col-span-auto relative group">
                <select name="kelas_id" class="w-full appearance-none pl-10 pr-8 h-[46px] text-sm font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl hover:border-primary/50 focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-[#1a2332] dark:text-slate-200 dark:border-slate-700 cursor-pointer min-w-[200px] shadow-sm transition-all" onchange="this.form.submit()">
                    @if(isset($allClasses) && $allClasses->count() > 0)
                        @foreach($allClasses as $kls)
                            <option value="{{ $kls->id }}" {{ isset($kelas) && $kelas->id == $kls->id ? 'selected' : '' }}>
                                {{ $kls->nama_kelas }}
                            </option>
                        @endforeach
                    @else
                        <option value="">Tidak ada kelas</option>
                    @endif
                </select>
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 group-hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]">class</span>
                </div>
            </div>
        </form>
    </div>
    @endif
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                @if($pageContext['type'] == 'graduation')
                    <span class="material-symbols-outlined text-indigo-600">school</span>
                @endif
                {{ $pageContext['title'] }}
            </h1>
            <p class="text-sm text-slate-500">
                Sistem otomatis menghitung rekomendasi {{ strtolower($pageContext['title']) }} berdasarkan aturan penilaian.
                <br>Kelas: <span class="font-bold text-indigo-600">{{ $kelas->nama_kelas }}</span>
                @if(isset($isLocked) && $isLocked)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        <span class="material-symbols-outlined text-[14px] mr-1">lock</span> Mode Baca
                    </span>
                @endif
            </p>
            
            @if(isset($warningMessage) && $warningMessage)
            <div class="mt-4 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-amber-500">warning</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700 font-bold">
                            {{ $warningMessage }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($debugInfo))
            <div class="mt-4 bg-slate-800 text-green-400 p-4 rounded-lg font-mono text-xs overflow-auto border border-slate-700 hidden">
                <p class="font-bold text-white border-b border-slate-600 pb-2 mb-2">🕵️ DEBUG MODE ACTIVATED</p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($debugInfo as $k => $v)
                        <div class="text-slate-400">{{ $k }}:</div>
                        <div class="font-bold">{{ $v }}</div>
                    @endforeach
                </div>
            </div>
            @endif
            </p>
        </div>
        <div class="flex gap-2">
            @php
                $allDecisionsLocked = collect($studentStats)->every(fn($s) => $s->is_locked);
                $isUserAdmin = auth()->user()->isAdmin() || auth()->user()->isTu();
            @endphp

            @if(isset($isLocked) && $isLocked)
                <div class="bg-amber-100 text-amber-800 px-4 py-2 rounded-lg font-bold flex items-center gap-2 border border-amber-200 cursor-not-allowed opacity-75 select-none" title="Periode ini terkunci">
                    <span class="material-symbols-outlined">lock</span> Terkunci (Periode)
                </div>
            @elseif($allDecisionsLocked && !$isUserAdmin)
                <div class="bg-slate-100 text-slate-500 px-4 py-2 rounded-lg font-bold flex items-center gap-2 border border-slate-200 cursor-not-allowed select-none" title="Keputusan sudah final">
                    <span class="material-symbols-outlined">verified_user</span> Keputusan Final
                </div>
            @else
                <button onclick="document.getElementById('form-kenaikan').submit()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-indigo-700 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">save</span> Simpan Keputusan
                </button>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Santri -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Santri</p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $summary['total'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined">groups</span>
            </div>
        </div>

        <!-- Siap Naik/Lulus -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-slate-500">Siap {{ $pageContext['success_label'] }}</p>
                <h3 class="text-3xl font-bold text-emerald-600 mt-1">{{ $summary['promote'] }}</h3>
                <p class="text-xs text-emerald-500 mt-1">Memenuhi syarat</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
        </div>

        <!-- Perlu Peninjauan/Tidak Naik -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-slate-500">Perlu Peninjauan / {{ $isFinalYear ? 'Tidak Lulus' : 'Tinggal' }}</p>
                <h3 class="text-3xl font-bold text-amber-500 mt-1">{{ $summary['review'] + $summary['retain'] }}</h3>
                <p class="text-xs text-amber-500 mt-1">Tidak memenuhi syarat</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined">warning</span>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h2 class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">table_chart</span>
                Daftar Rekomendasi {{ $pageContext['title'] }}
            </h2>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                <input type="text" placeholder="Cari nama santri..." class="pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-64">
            </div>
        </div>
        
        <form action="{{ route('walikelas.kenaikan.store') }}" method="POST" id="form-kenaikan">
            @csrf
            <!-- Important: Pass Class ID for Admin context -->
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700 text-slate-500 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-6 py-4">Nama Santri</th>
                            <th class="px-6 py-4 text-center">Rata-Rata<br>Tahun</th>
                            <th class="px-6 py-4 text-center">Mapel<br>< KKM</th>
                            <th class="px-6 py-4 text-center">Nilai<br>Sikap</th>
                            <th class="px-6 py-4 text-center">Kehadiran<br>(%)</th>
                            <th class="px-6 py-4 text-center">Rekomendasi<br>Sistem</th>
                            <th class="px-6 py-4 text-left w-64">Catatan<br>Sistem</th>
                            <th class="px-6 py-4 text-center w-48">Status Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($studentStats as $index => $stat)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $stat->student->nama_lengkap }}</div>
                                        <div class="text-xs text-slate-500">NIS: {{ $stat->student->nis_lokal ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-700">
                                <span onclick="showGradeDetails(this)" 
                                      data-grades="{{ json_encode($stat->grades_detail) }}"
                                      data-student="{{ $stat->student->nama_lengkap }}"
                                      data-avg="{{ $stat->avg_yearly }}"
                                      class="cursor-pointer border-b border-dashed border-slate-400 hover:text-indigo-600 transition-colors"
                                      title="Klik untuk lihat detail nilai">
                                    {{ $stat->avg_yearly }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($stat->under_kkm > 0)
                                    <span onclick="showFailDetails(this)"
                                          data-grades="{{ json_encode($stat->grades_detail) }}"
                                          data-student="{{ $stat->student->nama_lengkap }}"
                                          class="cursor-pointer bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold hover:bg-red-200 transition-colors"
                                          title="Klik untuk lihat mapel yang belum tuntas">
                                        {{ $stat->under_kkm }} Mapel
                                    </span>
                                @else
                                    <span class="text-slate-400">0</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span onclick="showAttitudeDetails('{{ $stat->attitude_detail->kelakuan }}', '{{ $stat->attitude_detail->kerajinan }}', '{{ $stat->attitude_detail->kebersihan }}')"
                                      class="cursor-pointer border-b border-dashed border-slate-400 hover:text-indigo-600 transition-colors font-bold {{ $stat->attitude == 'A' ? 'text-emerald-600' : ($stat->attitude == 'C' ? 'text-red-600' : 'text-slate-700') }}"
                                      title="Klik untuk detail sikap">
                                    {{ $stat->attitude }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span onclick="showAttendanceDetails({{ $stat->effective_days }}, {{ $stat->total_absent }}, {{ $stat->attendance_pct }})" 
                                      class="cursor-pointer border-b border-dashed border-slate-400 hover:text-indigo-600 transition-colors {{ $stat->attendance_pct < $stat->effective_days ? '' : '' }} {{ $stat->attendance_pct < 85 ? 'text-red-600 font-bold' : 'text-slate-700' }}"
                                      title="Klik untuk lihat detail perhitungan">
                                    {{ $stat->attendance_pct }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($stat->system_status == 'promote' || $stat->system_status == 'graduate')
                                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200">
                                        <span class="material-symbols-outlined text-[10px] mr-1">check</span> 
                                        {{ $isFinalYear ? 'LULUS' : 'Naik Kelas' }}
                                    </span>
                                @else
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="{{ $stat->system_status == 'review' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-red-100 text-red-700 border-red-200' }} px-3 py-1 rounded-full text-xs font-bold border flex items-center">
                                            <span class="material-symbols-outlined text-[10px] mr-1">{{ $stat->system_status == 'review' ? 'warning' : 'close' }}</span>
                                            {{ $isFinalYear ? 'TIDAK LULUS' : 'Tinggal Kelas' }}
                                        </span>
                                        @if(!empty($stat->fail_reasons))
                                            <div class="text-[10px] text-red-600 text-center w-full px-1 leading-tight mt-1">
                                                @foreach($stat->fail_reasons as $reason)
                                                    <div>{{ $reason }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 leading-snug break-words">
                                @if(isset($stat->ijazah_note) && $stat->ijazah_note)
                                    <div class="font-bold mb-1 {{ $stat->ijazah_class ?? (str_contains($stat->ijazah_note, 'TIDAK') ? 'text-red-600' : 'text-emerald-600') }}">
                                        {{ $stat->ijazah_note }}
                                    </div>
                                @endif
                                @if(isset($stat->manual_note) && $stat->manual_note)
                                    <div class="italic">"{{ $stat->manual_note }}"</div>
                                @endif
                                @if(empty($stat->ijazah_note) && empty($stat->manual_note))
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="relative">
                                    <select name="decisions[{{ $stat->student->id }}]" 
                                        class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 {{ $stat->final_status == 'pending' ? 'border-amber-400 bg-amber-50' : '' }} disabled:opacity-75 disabled:bg-slate-100 disabled:cursor-not-allowed"
                                        {{ (isset($isLocked) && $isLocked) || ($stat->is_locked && !auth()->user()->isAdmin() && !auth()->user()->isTu()) ? 'disabled' : '' }}>
                                        @if($isFinalYear)
                                            <option value="graduated" {{ $stat->final_status == 'graduated' ? 'selected' : '' }}>Lulus</option>
                                            <option value="not_graduated" {{ $stat->final_status == 'not_graduated' ? 'selected' : '' }}>Tidak Lulus</option>
                                            <option value="pending" {{ $stat->final_status == 'pending' ? 'selected' : '' }}>Ditangguhkan</option>
                                        @else
                                            <option value="promoted" {{ $stat->final_status == 'promoted' ? 'selected' : '' }}>Naik Kelas</option>
                                            <option value="retained" {{ $stat->final_status == 'retained' ? 'selected' : '' }}>Tinggal Kelas</option>
                                            <option value="pending" {{ $stat->final_status == 'pending' ? 'selected' : '' }}>Ditangguhkan</option>
                                        @endif
                                    </select>
                                    @if($stat->is_locked)
                                        <div class="absolute right-8 top-2.5" title="Keputusan Permanen (Terkunci)">
                                            <span class="material-symbols-outlined text-xs text-slate-500">lock</span>
                                        </div>
                                    @endif
                                </div>
                                <input type="text" name="notes[{{ $stat->student->id }}]" 
                                       placeholder="Alasan (jika Tidak Lulus)..." 
                                       value="{{ $stat->final_status == 'not_graduated' || $stat->final_status == 'retained' ? ($stat->student->promotion_decision->notes ?? '') : '' }}"
                                       class="mt-2 w-full text-xs border-slate-300 rounded-lg focus:ring-red-500 focus:border-red-500 placeholder-slate-400 disabled:bg-slate-100 disabled:text-slate-400"
                                       {{ isset($isLocked) && $isLocked ? 'readonly' : '' }}>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden flex flex-col gap-4 p-4">
                 @foreach($studentStats as $index => $stat)
                 <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex flex-col gap-4">
                    <!-- Header Info -->
                    <div class="flex justify-between items-start border-b border-slate-200 dark:border-slate-700 pb-3">
                         <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white">{{ $stat->student->nama_lengkap }}</h4>
                                <div class="text-xs text-slate-500">{{ $stat->student->nis_lokal ?? '-' }}</div>
                            </div>
                        </div>
                        
                        <!-- System Recommendation Badge (Compact) -->
                        <div class="flex items-center gap-2">
                             <span class="text-xs font-bold text-right {{ $stat->system_status == 'promote' || $stat->system_status == 'graduate' ? 'text-emerald-600' : ($stat->system_status == 'review' ? 'text-amber-600' : 'text-red-600') }}">
                                {{ $stat->recommendation }}
                            </span>

                            @if($stat->system_status == 'promote' || $stat->system_status == 'graduate')
                                <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[18px]">check</span>
                                </div>
                            @else
                                <div class="w-8 h-8 {{ $stat->system_status == 'review' ? 'bg-amber-100 text-amber-600' : 'bg-red-100 text-red-600' }} rounded-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[18px]">{{ $stat->system_status == 'review' ? 'warning' : 'close' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-4 gap-2 text-center">
                        <div class="bg-white p-2 rounded-lg border border-slate-200">
                             <div class="text-[10px] text-slate-400 uppercase font-bold">Rata2</div>
                             <div class="font-bold text-slate-800 text-sm cursor-pointer hover:text-indigo-600" 
                                  onclick="showGradeDetails({
                                      getAttribute: (name) => {
                                          const data = {
                                              'data-grades': '{{ json_encode($stat->grades_detail) }}',
                                              'data-student': '{{ $stat->student->nama_lengkap }}',
                                              'data-avg': '{{ $stat->avg_yearly }}'
                                          };
                                          return data[name];
                                      }
                                  })">
                                 {{ $stat->avg_yearly }}
                             </div>
                        </div>
                         <div class="bg-white p-2 rounded-lg border border-slate-200">
                             <div class="text-[10px] text-slate-400 uppercase font-bold">< KKM</div>
                             @if($stat->under_kkm > 0)
                                <div class="font-bold text-red-600 text-sm cursor-pointer hover:underline"
                                     onclick="showFailDetails({
                                          getAttribute: (name) => { 
                                            const data = { 'data-grades': '{{ json_encode($stat->grades_detail) }}', 'data-student': '{{ $stat->student->nama_lengkap }}' };
                                            return data[name];
                                          }
                                     })">
                                    {{ $stat->under_kkm }}
                                </div>
                             @else
                                <div class="font-bold text-emerald-600 text-sm">0</div>
                             @endif
                        </div>
                         <div class="bg-white p-2 rounded-lg border border-slate-200">
                             <div class="text-[10px] text-slate-400 uppercase font-bold">Sikap</div>
                             <div class="font-bold {{ $stat->attitude == 'A' ? 'text-emerald-600' : ($stat->attitude == 'C' ? 'text-red-600' : 'text-slate-700') }} text-sm cursor-pointer hover:underline"
                                   onclick="showAttitudeDetails('{{ $stat->attitude_detail->kelakuan }}', '{{ $stat->attitude_detail->kerajinan }}', '{{ $stat->attitude_detail->kebersihan }}')">
                                 {{ $stat->attitude }}
                             </div>
                        </div>
                         <div class="bg-white p-2 rounded-lg border border-slate-200">
                             <div class="text-[10px] text-slate-400 uppercase font-bold">Hadir</div>
                             <div class="font-bold {{ $stat->attendance_pct < 85 ? 'text-red-600' : 'text-slate-700' }} text-sm cursor-pointer hover:underline"
                                  onclick="showAttendanceDetails({{ $stat->effective_days }}, {{ $stat->total_absent }}, {{ $stat->attendance_pct }})">
                                 {{ $stat->attendance_pct }}%
                             </div>
                        </div>
                    </div>
                    
                    @if(!empty($stat->fail_reasons))
                         <div class="bg-red-50 p-2 rounded-lg text-[11px] text-red-700 border border-red-100">
                            <strong>Alasan:</strong>
                            <ul class="list-disc list-inside">
                                @foreach($stat->fail_reasons as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Action Footer -->
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                         <select name="decisions[{{ $stat->student->id }}]" 
                            class="w-full text-sm border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 mb-2 font-bold {{ $stat->final_status == 'not_graduated' || $stat->final_status == 'retained' ? 'bg-red-50 text-red-700' : 'bg-white' }}"
                            {{ (isset($isLocked) && $isLocked) || ($stat->is_locked && !auth()->user()->isAdmin() && !auth()->user()->isTu()) ? 'disabled' : '' }}>
                            @if($isFinalYear)
                                <option value="graduated" {{ $stat->final_status == 'graduated' ? 'selected' : '' }}>Lulus</option>
                                <option value="not_graduated" {{ $stat->final_status == 'not_graduated' ? 'selected' : '' }}>Tidak Lulus</option>
                                <option value="pending" {{ $stat->final_status == 'pending' ? 'selected' : '' }}>Ditangguhkan</option>
                            @else
                                <option value="promoted" {{ $stat->final_status == 'promoted' ? 'selected' : '' }}>Naik Kelas</option>
                                <option value="retained" {{ $stat->final_status == 'retained' ? 'selected' : '' }}>Tinggal Kelas</option>
                                <option value="pending" {{ $stat->final_status == 'pending' ? 'selected' : '' }}>Ditangguhkan</option>
                            @endif
                        </select>
                        <input type="text" name="notes[{{ $stat->student->id }}]" 
                               placeholder="Alasan (opsional)..." 
                               value="{{ $stat->student->promotion_decision->notes ?? '' }}"
                               class="w-full text-xs border-slate-300 rounded-lg focus:ring-slate-500 focus:border-slate-500 bg-slate-50"
                               {{ isset($isLocked) && $isLocked ? 'readonly' : '' }}>
                    </div>
                 </div>
                 @endforeach
            </div>
            
            <!-- Summary Verification -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 rounded-b-xl">
                 <div class="flex items-center gap-4 text-sm text-slate-600 dark:text-slate-400">
                    <span class="material-symbols-outlined">info</span>
                    <p>Pastikan semua keputusan telah sesuai sebelum menyimpan. Data ini akan digunakan untuk mencetak Rapor.</p>
                 </div>
            </div>
        </form>
    </div>
</div>

<script>
    function showAttendanceDetails(totalDays, absent, pct) {
        const present = totalDays - absent;
        const htmlContent = `
            <div class="text-left space-y-4">
                <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                    <div class="flex justify-between items-center text-sm mb-2 text-slate-600">
                        <span>Total Hari Efektif</span>
                        <span class="font-bold text-slate-800">${totalDays} Hari</span>
                    </div>
                    <div class="flex justify-between items-center text-sm mb-2 text-red-600">
                        <span>Total Alpa (Absen)</span>
                        <span class="font-bold">${absent} Hari</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-t border-indigo-200 pt-2 text-emerald-600">
                        <span>Total Hadir (Logis)</span>
                        <span class="font-bold">${present} Hari</span>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-xs uppercase text-slate-500 mb-1">Rumus Perhitungan</h4>
                    <div class="bg-slate-100 p-3 rounded-lg text-center font-mono text-sm text-slate-700">
                        (${present} / ${totalDays}) x 100% = <span class="text-indigo-600 font-bold">${pct}%</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2 italic">* Sakit & Izin dianggap hadir (tidak mengurangi persentase).</p>
                </div>
            </div>
        `;
        
        Swal.fire({
            title: '📊 Detail Kalkulasi Kehadiran',
            html: htmlContent,
            icon: 'info',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#4f46e5'
        });
    }

    function showAttitudeDetails(kelakuan, kerajinan, kebersihan) {
        const htmlContent = `
            <div class="text-left space-y-4">
               <div class="grid grid-cols-1 gap-2">
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 flex justify-between items-center">
                        <span class="text-sm text-slate-600">1. Kelakuan</span>
                        <span class="font-bold text-slate-800">${kelakuan}</span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 flex justify-between items-center">
                        <span class="text-sm text-slate-600">2. Kerajinan</span>
                        <span class="font-bold text-slate-800">${kerajinan}</span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 flex justify-between items-center">
                        <span class="text-sm text-slate-600">3. Kebersihan</span>
                        <span class="font-bold text-slate-800">${kebersihan}</span>
                    </div>
               </div>

               <div>
                    <h4 class="font-bold text-xs uppercase text-slate-500 mb-2">Rumus Penilaian Akhir</h4>
                    <div class="bg-blue-50 p-3 rounded-lg text-sm text-slate-700 border border-blue-100">
                        Saat ini sistem menggunakan <b>'Kelakuan'</b> sebagai penentu utama:
                        <ul class="mt-2 space-y-1 list-disc list-inside text-xs">
                            <li>Baik  ➜  <b class="text-emerald-600">A (Sangat Baik)</b></li>
                            <li>Cukup ➜  <b class="text-amber-600">B (Baik)</b></li>
                            <li>Kurang ➜ <b class="text-red-600">C (Cukup)</b></li>
                        </ul>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2 italic">* Kerajinan & Kebersihan dicatat sebagai info tambahan.</p>
               </div>
            </div>
        `;

        Swal.fire({
            title: '🧠 Detail Nilai Sikap',
            html: htmlContent,
            icon: 'info',
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#4f46e5'
        });
    }
    function showGradeDetails(element) {
        const grades = JSON.parse(element.getAttribute('data-grades'));
        const studentName = element.getAttribute('data-student');
        const avg = element.getAttribute('data-avg');
        
        // Build Table Rows
        let rows = '';
        grades.forEach(g => {
            const statusClass = g.is_under ? 'text-red-600 font-bold' : 'text-slate-700';
            const kkmClass = g.is_under ? 'bg-red-50 text-red-700' : 'text-slate-500';
            rows += `
                <tr class="border-b border-slate-100 last:border-0">
                    <td class="py-2 text-left text-xs font-medium text-slate-600">${g.mapel}</td>
                    <td class="py-2 text-center text-xs ${kkmClass} rounded">${g.kkm}</td>
                    <td class="py-2 text-right text-xs ${statusClass}">${g.nilai}</td>
                </tr>
            `;
        });

        const htmlContent = `
            <div class="text-left">
               <div class="bg-indigo-50 p-3 rounded-lg mb-4 flex justify-between items-center text-sm text-indigo-900 border border-indigo-100">
                   <span>Rata-Rata Akhir</span>
                   <span class="font-bold text-lg">${avg}</span>
               </div>
               
               <div class="overflow-y-auto max-h-64 rounded-lg border border-slate-200">
                   <table class="w-full">
                       <thead class="bg-slate-50 text-xs text-slate-500 uppercase font-bold sticky top-0">
                           <tr>
                               <th class="px-3 py-2 text-left">Mata Pelajaran</th>
                               <th class="px-2 py-2 text-center">KKM</th>
                               <th class="px-3 py-2 text-right">Nilai</th>
                           </tr>
                       </thead>
                       <tbody class="divide-y divide-slate-100">
                           ${rows}
                       </tbody>
                   </table>
               </div>
               <p class="text-[10px] text-slate-400 mt-2 italic">* Nilai merah menandakan di bawah KKM.</p>
            </div>
        `;

        Swal.fire({
            title: `📊 Transkrip Nilai: ${studentName}`,
            html: htmlContent,
            width: '500px',
            showCloseButton: true,
            confirmButtonText: 'Tutup',
        });
    }
    function showFailDetails(element) {
        const grades = JSON.parse(element.getAttribute('data-grades'));
        const studentName = element.getAttribute('data-student');
        
        // Filter only failed grades
        const failedGrades = grades.filter(g => g.is_under);
        
        let rows = '';
        failedGrades.forEach(g => {
            rows += `
                <tr class="border-b border-red-100 last:border-0 bg-red-50/50">
                    <td class="py-2 text-left text-xs font-medium text-red-800">${g.mapel}</td>
                    <td class="py-2 text-center text-xs text-red-600 rounded">${g.kkm}</td>
                    <td class="py-2 text-right text-xs font-bold text-red-600">${g.nilai}</td>
                </tr>
            `;
        });

        const htmlContent = `
            <div class="text-left">
               <div class="bg-red-50 p-3 rounded-lg mb-4 flex gap-3 items-center border border-red-100">
                   <span class="material-symbols-outlined text-red-500">warning</span>
                   <div class="text-red-900 text-sm">
                        <span class="font-bold block">Perlu Perbaikan</span>
                        <span class="text-xs">Daftar mata pelajaran di bawah KKM</span>
                   </div>
               </div>
               
               <div class="overflow-y-auto max-h-64 rounded-lg border border-red-100">
                   <table class="w-full">
                       <thead class="bg-red-50 text-xs text-red-500 uppercase font-bold sticky top-0">
                           <tr>
                               <th class="px-3 py-2 text-left">Mata Pelajaran</th>
                               <th class="px-2 py-2 text-center">KKM</th>
                               <th class="px-3 py-2 text-right">Nilai</th>
                           </tr>
                       </thead>
                       <tbody class="divide-y divide-red-100">
                           ${rows}
                       </tbody>
                   </table>
               </div>
               <p class="text-[10px] text-red-400 mt-2 italic">* Disarankan untuk remidial sebelum penentuan akhir.</p>
            </div>
        `;

        Swal.fire({
            title: `Mapel Belum Tuntas: ${studentName}`,
            html: htmlContent,
            width: '450px',
            icon: 'warning',
            showCloseButton: true,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#dc2626'
        });
    }
</script>
@endsection
