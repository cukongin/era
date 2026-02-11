<table class="w-full text-xs text-left border-collapse rapor-table">
    <thead>
        <tr class="bg-gray-100 text-center font-bold">
            <th class="w-8" rowspan="2">No</th>
            <th rowspan="2">Mata Pelajaran / Kitab</th>
            <th class="w-14" rowspan="2">KKM</th>
            <th class="whitespace-nowrap" colspan="{{ count($periodSlots) }}">Nilai {{ $periodLabel ?? 'Catur Wulan' }}</th>
            <th class="w-17" rowspan="2">Rata-Rata</th>
            <th class="w-14" rowspan="2">Predikat</th>
        </tr>
        <tr class="bg-gray-100 text-center font-bold">
            @foreach($periodSlots as $slot)
               <th class="w-14">{{ $slot }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($mapelGroups as $kategori => $mapels)
            <tr class="bg-gray-50/50">
                <td class="font-bold italic px-2 py-1" colspan="{{ 3 + count($periodSlots) + 2 }}">{{ $kategori }}</td>
            </tr>
            @foreach($mapels as $pm)
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
                             - <span class="font-arabic">{{ $pm->mapel->nama_kitab }}</span>
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
                                $totalScore += $val; 
                                $countScore++; 
                            }
                        @endphp
                        <td class="text-center {{ is_numeric($val) && $val < $kkm ? 'text-red-600 font-bold' : '' }}">
                            {{ is_numeric($val) ? round($val) : $val }}
                        </td>
                    @endforeach
                    
                    @php
                        $finalAvg = $countScore > 0 ? round($totalScore / $countScore) : 0;
                        $predikat = '-';
                        $predikat = 'D'; // Default fallthrough
                        if ($countScore > 0) {
                            foreach ($predicateRules as $rule) {
                                // Assuming rules are ordered DESC by min_score
                                if ($finalAvg >= $rule->min_score) {
                                    $predikat = $rule->grade;
                                    break;
                                }
                            }
                        }
                        $finalDisplay = $countScore > 0 ? $finalAvg : '-';
                    @endphp

                    <td class="text-center font-bold">{{ $finalDisplay }}</td>
                    <td class="text-center">{{ $predikat }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
    <tfoot>
        <!-- Total -->
        <tr class="bg-gray-100 font-bold">
            <td colspan="3" class="text-center">Jumlah</td>
            @foreach($periodSlots as $i => $slot)
                @php
                    $pObj = $allPeriods->skip($i)->first(); 
                    $pId = $pObj ? $pObj->id : null;
                    $val = $pId && isset($stats[$pId]) ? $stats[$pId]['total'] : '-';
                @endphp
                <td class="text-center">{{ is_numeric($val) ? round($val) : $val }}</td>
            @endforeach
            <td colspan="2" class="bg-gray-200"></td>
        </tr>
        
        <!-- Average -->
        <tr class="bg-gray-100 font-bold">
            <td colspan="3" class="text-center">Nilai Rata-rata</td>
            @foreach($periodSlots as $i => $slot)
                @php
                    $pObj = $allPeriods->skip($i)->first(); 
                    $pId = $pObj ? $pObj->id : null;
                    $val = $pId && isset($stats[$pId]) ? $stats[$pId]['average'] : '-';
                @endphp
                <td class="text-center">{{ $val }}</td>
            @endforeach
             <td colspan="2" class="bg-gray-200"></td>
        </tr>
        
        <!-- Rank -->
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
             <td colspan="2" class="bg-gray-200"></td>
        </tr>
    </tfoot>
</table>

