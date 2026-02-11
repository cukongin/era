<table class="w-full text-xs text-left border-collapse rapor-table whitespace-nowrap">
     <thead>
        <tr class="bg-gray-100 text-center font-bold">
            <th>Keterangan</th>
            @foreach($periodSlots as $slot)
                <th>{{ isset($periodLabel) && $periodLabel == 'Catur Wulan' ? 'Cawu' : ($periodLabel ?? 'Semester') }} {{ $slot }}</th>
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

