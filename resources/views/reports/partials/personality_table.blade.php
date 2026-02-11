<table class="w-full text-xs text-left border-collapse rapor-table">
    <thead>
        <tr class="bg-gray-100 text-center font-bold">
            <th>Aspek</th>
            @foreach($periodSlots as $slot)
                <th>{{ isset($periodLabel) && $periodLabel == 'Catur Wulan' ? 'Cawu' : ($periodLabel ?? 'Semester') }} {{ $slot }}</th>
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

