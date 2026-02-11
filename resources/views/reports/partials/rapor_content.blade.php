    @php
        $periodSlots = $periodSlots ?? [1, 2, 3]; 
        $periodLabel = $periodLabel ?? 'Cawu';
        // Ensure local variables are present (when passed from print_all, they are in array item)
        // Passed variables: student, class, school, activeYear, allPeriods, activePeriod, stats, etc.
    @endphp

    <!-- A4 Paper Container -->
    <div class="print-container paper-a4 flex flex-col relative text-black" style="page-break-after: always; page-break-inside: avoid;">
        
        <!-- Header Section (Logo + Title) -->
        <header class="flex flex-col items-center justify-center mb-4 mt-0">
            <div class="flex items-center justify-center mb-2">
                @if($school->logo)
                    <img src="{{ asset($school->logo) }}" class="h-16 w-16 object-contain" alt="Logo">
                @else
                   <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Logo_Kementerian_Agama_Pengasuh.png/586px-Logo_Kementerian_Agama_Pengasuh.png" class="h-16 w-16 object-contain" alt="Logo Kemenag">
                @endif
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
                    <span class="uppercase font-medium">{{ $student->nama_lengkap }}</span>
                </div>
                <div class="flex mb-1">
                    <span class="w-32 font-semibold">Nomor Induk</span>
                    <span class="mr-2">:</span>
                    <span>{{ $student->nis_lokal }}</span>
                </div>
                <div class="flex mb-1">
                    <span class="w-32 font-semibold">Nama MDT. {{ $class->jenjang->kode == 'MTS' ? 'Wustha' : 'Ula' }}</span>
                    <span class="mr-2">:</span>
                    <span>{{ $school->nama_sekolah }}</span>
                </div>
            </div>
            
            <!-- Right Column (Aligned to Right Edge) -->
            <div class="w-auto">
                <div class="flex mb-1">
                    <span class="w-32 font-semibold">Kelas</span>
                    <span class="mr-2">:</span>
                    <span>{{ $class->nama_kelas }}</span>
                </div>
                <div class="flex mb-1">
                    <span class="w-32 font-semibold">Tahun Pelajaran</span>
                    <span class="mr-2">:</span>
                    <span>{{ $activeYear->nama }}</span>
                </div>
                <div class="flex mb-1">
                    <span class="w-32 font-semibold">Alamat</span>
                    <span class="mr-2">:</span>
                    <span>{{ $school->alamat }}</span>
                </div>
            </div>
        </div>

        @php $sectionIndex = 0; @endphp
        <!-- Academic Table -->
        <div class="mb-2">
            <h4 class="font-bold text-xs mb-1">{{ chr(65 + $sectionIndex++) }}. Pengetahuan dan Keterampilan</h4>
            <table class="w-full text-xs text-left border-collapse rapor-table">
                <thead>
                    <tr class="bg-gray-100 text-center font-bold">
                        <th class="w-8" rowspan="2">No</th>
                        <th class="{{ $class->jenjang->kode == 'MTS' ? 'w-auto' : 'w-64' }}" rowspan="2">Mata Pelajaran</th>
                        <th class="w-12" rowspan="2">KKM</th>
                        <th class="whitespace-nowrap"  colspan="{{ count($periodSlots) }}">Nilai {{ $periodLabel ?? 'Catur Wulan' }}</th>
                        <th class="w-20" rowspan="2">Rata-Rata<br>Akhir Tahun</th> <!-- Widened -->
                        <th class="w-14" rowspan="2">Predikat</th> <!-- Widened -->
                    </tr>
                    <tr class="bg-gray-100 text-center font-bold">
                        @foreach($periodSlots as $slot)
                           <th class="w-8">{{ $slot }}</th> 
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @php 
                        // Convert to Base Collection to avoid Eloquent Key checks on Groups
                        $mapelGroups = $mapelGroups->toBase();
                        
                        // Identify Mulok Group (Case Insensitive check for 'Muatan Lokal' or 'Mulok')
                        $mulokKey = $mapelGroups->keys()->first(fn($k) => stripos($k, 'Muatan Lokal') !== false || stripos($k, 'Mulok') !== false);
                        $mulokGroup = $mulokKey ? $mapelGroups[$mulokKey] : collect([]);
                        $otherGroups = $mapelGroups->except($mulokKey ? [$mulokKey] : []);
                    @endphp

                    {{-- 1. Header Wajib Fixed --}}
                    <tr class="bg-gray-50/50">
                        <td class="font-bold italic px-2 py-1" colspan="{{ 3 + count($periodSlots) + 2 }}">1. Mata Pelajaran Wajib</td>
                    </tr>

                    {{-- Render Wajib Items (Flattened) --}}
                    @foreach($otherGroups as $kategori => $mapels)
                        @foreach($mapels as $pm)
                            @php
                                $totalScore = 0;
                                $countScore = 0;
                                $kkm = $kkmMapels[$pm->id_mapel] ?? $globalKkm; 
                            @endphp
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>
                                    <span class="font-arabic">{{ $pm->mapel->nama_mapel }}</span>
                                    @if(!empty($pm->mapel->nama_kitab))
                                          -  <span class="font-arabic">{{ $pm->mapel->nama_kitab }}</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $kkm }}</td>
                                @foreach($periodSlots as $i => $slot)
                                    @php
                                        $pObj = $allPeriods->skip($i)->first(); 
                                        $pId = $pObj ? $pObj->id : null;
                                        $grade = $pId ? ($cumulativeGrades[$pm->id_mapel][$pId] ?? null) : null;
                                        $val = $grade ? $grade->nilai_akhir : '-';
                                        
                                        if(is_numeric($val)) { 
                                            // Ensure numeric values are floated for calc
                                            $totalScore += (float)$val; 
                                            $countScore++; 
                                        }
                                    @endphp
                                    <td class="text-center {{ is_numeric($val) && $val < $kkm ? 'text-red-600 font-bold' : '' }}">
                                        {{ is_numeric($val) ? round($val) : $val }}
                                    </td>
                                @endforeach
                                
                                @php
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
                                @endphp

                                <td class="text-center font-bold">{{ $finalDisplay }}</td>
                                <td class="text-center">{{ $predikat }}</td>
                            </tr>
                        @endforeach
                    @endforeach

                    {{-- 2. Render Muatan Lokal (Fixed Header "2. Muatan Lokal") --}}
                    <tr class="bg-gray-50/50">
                        <td class="font-bold italic px-2 py-1" colspan="{{ 3 + count($periodSlots) + 2 }}">2. Muatan Lokal</td>
                    </tr>
                    
                    @if($mulokGroup->count() > 0)
                        @foreach($mulokGroup as $pm)
                             @php
                                $totalScore = 0;
                                $countScore = 0;
                                $kkm = $kkmMapels[$pm->id_mapel] ?? $globalKkm; 
                            @endphp
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>
                                    {{ $pm->mapel->nama_mapel }}
                                    @if(!empty($pm->mapel->nama_kitab))
                                          -  <span class="font-arabic">{{ $pm->mapel->nama_kitab }}</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $kkm }}</td>
                                @foreach($periodSlots as $i => $slot)
                                    @php
                                        $pObj = $allPeriods->skip($i)->first(); 
                                        $pId = $pObj ? $pObj->id : null;
                                        $grade = $pId ? ($cumulativeGrades[$pm->id_mapel][$pId] ?? null) : null;
                                        $val = $grade ? $grade->nilai_akhir : '-';
                                        
                                        if(is_numeric($val)) { 
                                            $totalScore += (float)$val; 
                                            $countScore++; 
                                        }
                                    @endphp
                                    <td class="text-center {{ is_numeric($val) && $val < $kkm ? 'text-red-600 font-bold' : '' }}">
                                        {{ is_numeric($val) ? round($val) : $val }}
                                    </td>
                                @endforeach
                                
                                @php
                                    $finalAvg = $countScore > 0 ? number_format($totalScore / $countScore, 2) : 0;
                                    $predikat = '-';
                                    if ($countScore > 0) {
                                        if ($finalAvg >= 90) $predikat = 'A';
                                        elseif ($finalAvg >= 80) $predikat = 'B';
                                        elseif ($finalAvg >= 70) $predikat = 'C';
                                        else $predikat = 'D';
                                    }
                                    $finalDisplay = $countScore > 0 ? $finalAvg : '-';
                                @endphp

                                <td class="text-center font-bold">{{ $finalDisplay }}</td>
                                <td class="text-center">{{ $predikat }}</td>
                            </tr>
                        @endforeach
                    @else
                        {{-- 3 Empty Rows if No Mulok --}}
                        @for($k=0; $k<3; $k++)
                        <tr class="h-6">
                            <td class="text-center">{{ $no++ }}</td>
                            <td>-</td>
                            <td class="text-center">-</td>
                            @foreach($periodSlots as $slot)
                                <td class="text-center">-</td>
                            @endforeach
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                        @endfor
                    @endif
                </tbody>
                <tfoot>
                    <!-- Total Score -->
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="3" class="text-center">Nilai Total</td>
                        @foreach($periodSlots as $i => $slot)
                            @php
                                $pObj = $allPeriods->skip($i)->first(); 
                                $pId = $pObj ? $pObj->id : null;
                                $val = $pId && isset($stats[$pId]) ? $stats[$pId]['total'] : '-';
                            @endphp
                            <td class="text-center">{{ is_numeric($val) ? round($val) : $val }}</td>
                        @endforeach
                        <td class="text-center">{{ $annualStats['total'] ?? '-' }}</td>
                        <td class="bg-gray-200"></td> 
                    </tr>
                    
                    <!-- Average Score -->
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="3" class="text-center">Rata-rata Total</td>
                        @foreach($periodSlots as $i => $slot)
                            @php
                                $pObj = $allPeriods->skip($i)->first(); 
                                $pId = $pObj ? $pObj->id : null;
                                $val = $pId && isset($stats[$pId]) ? $stats[$pId]['average'] : '-';
                            @endphp
                            <td class="text-center">{{ is_numeric($val) ? number_format($val, 2) : $val }}</td>
                        @endforeach
                         <td class="text-center">{{ $annualStats['average'] ?? '-' }}</td>
                         <td class="bg-gray-200"></td>
                    </tr>
                    
                    <!-- Ranking -->
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="3" class="text-center">Ranking</td>
                         @foreach($periodSlots as $i => $slot)
                            @php
                                $pObj = $allPeriods->skip($i)->first(); 
                                $pId = $pObj ? $pObj->id : null;
                                $rank = $pId && isset($stats[$pId]) ? $stats[$pId]['rank'] : '-';
                                $count = $pId && isset($stats[$pId]) ? $stats[$pId]['count'] : '-';
                                $display = $rank !== '-' ? "$rank / $count" : '-';
                            @endphp
                            <td class="text-center">{{ $display }}</td>
                        @endforeach
                         <td class="text-center">{{ $annualStats['rank'] ? ($annualStats['rank'] . ' / ' . $annualStats['count']) : '-' }}</td>
                         <td class="bg-gray-200"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if(\App\Models\GlobalSetting::val('rapor_show_ekskul', 1))
        <div class="flex gap-4 mb-2">
            <!-- Extracurricular -->
            <div class="flex-1">
                <h4 class="font-bold text-xs mb-1">{{ chr(65 + $sectionIndex++) }}. Ekstrakurikuler</h4>
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
                        @php 
                            $flatEkskuls = collect();
                            foreach($ekskuls as $pId => $grp) {
                                foreach($grp as $e) {
                                    $ekey = $e->nama_ekskul; 
                                    if (!$flatEkskuls->has($ekey)) {
                                        $flatEkskuls->put($ekey, $e);
                                    }
                                }
                            }
                        @endphp
                        
                        @forelse($flatEkskuls as $e)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $e->nama_ekskul }}</td>
                            <td class="text-center">{{ $e->nilai }}</td>
                            <td>{{ $e->keterangan }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="text-center">-</td>
                            <td>-</td>
                            <td class="text-center">-</td>
                            <td>-</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(\App\Models\GlobalSetting::val('rapor_show_prestasi', 0))
        <div class="flex gap-4 mb-2">
            <!-- Prestasi -->
            <div class="flex-1">
                <h4 class="font-bold text-xs mb-1">{{ chr(65 + $sectionIndex++) }}. Prestasi</h4>
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
        @endif

        <div class="flex gap-4 mb-2">
            <!-- Personality (New C) -->
            <div class="flex-1">
                <h4 class="font-bold text-xs mb-1">{{ chr(65 + $sectionIndex++) }}. Kepribadian</h4>
                <table class="w-full text-xs text-left border-collapse rapor-table">
                    <thead>
                        <tr class="bg-gray-100 text-center font-bold">
                            <th>Aspek</th>
                            @foreach($periodSlots as $slot)
                                <th>{{ $periodLabel == 'Catur Wulan' ? 'Cawu' : $periodLabel }} {{ $slot }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['Kelakuan', 'Kerajinan', 'Kebersihan'] as $aspek)
                        <tr>
                            <td>{{ $aspek }}</td>
                            @foreach($periodSlots as $i => $slot)
                                @php
                                    $pObj = $allPeriods->skip($i)->first(); 
                                    $pId = $pObj ? $pObj->id : null;
                                    $safeKey = strtolower($aspek);
                                    // Fetch from Attendance Record
                                    $val = $pId && isset($attendance[$pId]) ? $attendance[$pId]->$safeKey : '-';
                                @endphp
                                <td class="text-center">{{ $val }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Attendance (Renamed to D) -->
            <div class="flex-1">
                <h4 class="font-bold text-xs mb-1">{{ chr(65 + $sectionIndex++) }}. Ketidakhadiran</h4>
                <table class="w-full text-xs text-left border-collapse rapor-table whitespace-nowrap">
                     <thead>
                        <tr class="bg-gray-100 text-center font-bold">
                            <th>Keterangan</th>
                            @foreach($periodSlots as $slot)
                                <th>{{ $periodLabel == 'Catur Wulan' ? 'Cawu' : $periodLabel }} {{ $slot }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                         <tr>
                            <td>Sakit</td>
                            @foreach($periodSlots as $i => $slot)
                                @php
                                    $pObj = $allPeriods->skip($i)->first(); 
                                    $pId = $pObj ? $pObj->id : null;
                                    $val = $pId && isset($attendance[$pId]) ? $attendance[$pId]->sakit : '-';
                                    $disp = ($val !== '-' && $val > 0) ? $val . ' Hari' : '-';
                                @endphp
                                <td class="text-center">{{ $disp }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Izin</td>
                             @foreach($periodSlots as $i => $slot)
                                @php
                                    $pObj = $allPeriods->skip($i)->first(); 
                                    $pId = $pObj ? $pObj->id : null;
                                    $val = $pId && isset($attendance[$pId]) ? $attendance[$pId]->izin : '-';
                                    $disp = ($val !== '-' && $val > 0) ? $val . ' Hari' : '-';
                                @endphp
                                <td class="text-center">{{ $disp }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>Tanpa Keterangan</td>
                             @foreach($periodSlots as $i => $slot)
                                @php
                                    $pObj = $allPeriods->skip($i)->first(); 
                                    $pId = $pObj ? $pObj->id : null;
                                    $val = $pId && isset($attendance[$pId]) ? $attendance[$pId]->tanpa_keterangan : '-';
                                    $disp = ($val !== '-' && $val > 0) ? $val . ' Hari' : '-';
                                @endphp
                                <td class="text-center">{{ $disp }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notes & Promotion Status (Renamed to E) -->
        <div class="mb-4">
            <h4 class="font-bold text-xs mb-1">{{ chr(65 + $sectionIndex++) }}. Catatan Wali Kelas (Akhir Tahun)</h4>
            @php
                $latestRem = $remarks->last(); 
                $note = $latestRem ? $latestRem->catatan_akademik : '-'; 
            @endphp
            <div class="border border-black p-2 text-xs min-h-[40px] mb-2 italic">
                "{{ $note }}"
            </div>
            
            @php
                // Logic: Is this the Final Period? (Cawu 3 or Semester Genap)
                // We check if the active period is the last one in the list for this year/jenjang.
                $lastPeriod = $allPeriods->last();
                $isFinalPeriod = $activePeriod && $lastPeriod && $activePeriod->id == $lastPeriod->id;
                
                // Show if data exists OR if it's the final period (show placeholder)
                $showPromotion = $statusNaik !== null || $isFinalPeriod;
            @endphp
            
            @if($showPromotion)
            <div class="border border-black p-2 flex flex-col items-center justify-center gap-1 bg-gray-50">
                <p class="text-xs">Berdasarkan hasil penilaian, Peserta Didik dinyatakan:</p>
                <p class="font-bold text-sm uppercase">{{ $decisionText ?? '......................' }}</p>
            </div>
            @endif
        </div>
        <!-- Signatures (Date Right) -->
        <div class="text-right text-xs mt-4 mb-1">
            @php
                $place = !empty($titimangsaTempat) ? $titimangsaTempat : ($school->kabupaten ?? $school->kota ?? 'Tempat');
                $date1Raw = !empty($titimangsa) ? $titimangsa : \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y');
                
                $jenjangKey = strtolower($class->jenjang->kode ?? 'mi'); 
                $date2Raw = \App\Models\GlobalSetting::val('titimangsa_2_' . $jenjangKey);
            @endphp
            
            @if(!empty($date2Raw))
                @php
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
                @endphp

                {{-- Strict Table Layout --}}
                <div class="inline-block text-left">
                    <table style="border-collapse: collapse; white-space: nowrap;">
                        {{-- Row 1: Hijri --}}
                        <tr class="leading-tight">
                            <td class="pr-2 text-right">{{ $place }},</td>
                            <td class="px-1 text-center">{{ $d1['day'] }}</td>
                            <td class="px-1 text-left pl-2">{{ $d1['month'] }}</td>
                            <td class="px-1 text-center">{{ $d1['year'] }}</td>
                            <td class="pl-1 text-left">{{ $d1['suffix'] }}</td>
                        </tr>
                        {{-- Row 2: Masehi --}}
                        <tr class="leading-tight">
                            <td></td> {{-- Empty Place Column --}}
                            <td class="px-1 text-center">{{ $d2['day'] }}</td>
                            <td class="px-1 text-left pl-2">{{ $d2['month'] }}</td>
                            <td class="px-1 text-center">{{ $d2['year'] }}</td>
                            <td class="pl-1 text-left">{{ $d2['suffix'] }}</td>
                        </tr>
                    </table>
                </div>
            @else
                {{-- Standard Single Line --}}
                <p>{{ $place }}, {{ $date1Raw }}</p>
            @endif
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
                @php
                    $kepalaName = $school->kepala_madrasah;
                    // Override for MTS if available
                    if (($class->jenjang->kode ?? '') == 'MTS' && !empty($school->kepala_madrasah_mts)) {
                        $kepalaName = $school->kepala_madrasah_mts;
                    }
                @endphp
                <p class="font-bold inline-block min-w-[120px] uppercase">{{ $kepalaName }}</p> 
            </div>

            <!-- Teacher (Right) -->
            <div class="text-center" style="min-width: 120px;">
                <p class="mb-24">Wali Kelas</p>
                <p class="font-bold inline-block min-w-[120px] uppercase">{{ $class->wali_kelas->name }}</p> 
            </div>
        </div>

    </div>

