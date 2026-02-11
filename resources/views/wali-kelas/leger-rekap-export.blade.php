<!DOCTYPE html>
<html>
<head>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 0.5pt solid #000; padding: 4px; } /* Thinnest possible in HTML-to-Excel */
        .text-center { text-align: center; }
        .bg-header { background-color: #f0f0f0; font-weight: bold; }
        .text-red { color: red; }
    </style>
</head>
<body>
    <h2>LEGER REKAP TAHUNAN KELAS {{ $kelas->nama_kelas }}</h2>
    <p>Tahun Ajaran: {{ $kelas->tahun_ajaran->nama_tahun }} | Total Siswa: {{ $students->count() }}</p>
    
    <table>
        <thead>
            <tr class="bg-header">
                <th rowspan="2" style="border:0.5pt solid #000;">No</th>
                <th rowspan="2" style="border:0.5pt solid #000;">NIS</th>
                <th rowspan="2" style="border:0.5pt solid #000;">Nama Siswa</th>
                <th rowspan="2" style="border:0.5pt solid #000;">L/P</th>
                
                @foreach($mapels as $mapel)
                <th colspan="{{ $periods->count() + 1 }}" class="text-center" style="border:0.5pt solid #000;">
                    {{ $mapel->nama_mapel }}<br>
                    <span style="font-size:10px; font-weight:normal;">KKM: {{ $kkm[$mapel->id] ?? 70 }}</span>
                </th>
                @endforeach

                <th rowspan="2" style="border:0.5pt solid #000;">Nilai Total</th>
                <th rowspan="2" style="border:0.5pt solid #000;">Rata-rata Total</th>
                <th rowspan="2" style="border:0.5pt solid #000;">Ranking</th>
            </tr>
            <tr class="bg-header">
                @foreach($mapels as $mapel)
                    @foreach($periods as $periode)
                    <th style="border:0.5pt solid #000;">{{ $periode->nama_periode }}</th>
                    @endforeach
                    <th style="background-color: #e0e0e0; border:0.5pt solid #000;">Rata2</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $ak)
            @php
                $myStats = $studentStats[$ak->id_siswa] ?? ['avg'=>0, 'total'=>0, 'rank'=>'-'];
                $sGrades = $grades[$ak->id_siswa] ?? collect([]);
            @endphp
            <tr>
                <td class="text-center" style="border:0.5pt solid #000;">{{ $index + 1 }}</td>
                <td class="text-center" style="mso-number-format:'\@'; border:0.5pt solid #000;">{{ $ak->siswa->nis_lokal }}</td>
                <td style="border:0.5pt solid #000;">{{ $ak->siswa->nama_lengkap }}</td>
                <td class="text-center" style="border:0.5pt solid #000;">{{ $ak->siswa->jenis_kelamin }}</td>

                @foreach($mapels as $mapel)
                    @php
                        $mapelTotal = 0;
                        $mapelCount = 0;
                    @endphp
                    @foreach($periods as $periode)
                        @php
                            $g = $sGrades->first(function($item) use ($periode, $mapel) {
                                return $item->id_periode == $periode->id && $item->id_mapel == $mapel->id;
                            });
                            $val = $g ? $g->nilai_akhir : 0;
                            if($g) {
                                $mapelTotal += $val;
                                $mapelCount++;
                            }
                            $style = 'text-center; border:0.5pt solid #000;'; // Removed fixed decimal
                            if ($g && $val < ($kkm[$mapel->id] ?? 70)) $style .= ' color: red;';
                        @endphp
                        <td class="text-center" style="{{ $style }}">
                            {{ $g ? round($val) : '-' }}
                        </td>
                    @endforeach
                    
                    @php
                        $avgMapel = $mapelCount > 0 ? $mapelTotal / $mapelCount : 0;
                    @endphp
                    <td class="text-center" style="background-color: #fff8e1; font-weight:bold; border:0.5pt solid #000; mso-number-format:'0\.00';">
                        {{ $mapelCount > 0 ? number_format($avgMapel) : '-' }}
                    </td>
                @endforeach
                
                <td class="text-center font-bold" style="background-color: #e3f2fd; border: 0.5pt solid #000; mso-number-format:'0\.00';">{{ number_format($myStats['total']) }}</td>
                <td class="text-center font-bold" style="background-color: #bbdefb; border: 0.5pt solid #000; mso-number-format:'0\.00';">{{ number_format($myStats['avg'], 2) }}</td>
                <td class="text-center font-bold" style="background-color: #fff9c4; border: 0.5pt solid #000; color: #000;">{{ $myStats['rank'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

