<!DOCTYPE html>
<html>
<head>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 0.5pt solid #000; padding: 4px; } /* Thinnest possible */
        .text-center { text-align: center; }
        .bg-header { background-color: #f0f0f0; font-weight: bold; }
        .text-red { color: red; }
    </style>
</head>
<body>
    <h2>LEGER NILAI KELAS {{ $kelas->nama_kelas }}</h2>
    <p>Periode: {{ $periode->nama_periode }} | Total Siswa: {{ $students->count() }}</p>
    
    <table>
        <thead>
            <tr class="bg-header">
                <th rowspan="2" style="background-color:#eee; border:0.5pt solid #000;">No</th>
                <th rowspan="2" style="background-color:#eee; border:0.5pt solid #000;">NIS</th>
                <th rowspan="2" style="background-color:#eee; border:0.5pt solid #000;">Nama Siswa</th>
                <th rowspan="2" style="background-color:#eee; border:0.5pt solid #000;">L/P</th>
                @foreach($mapels as $mapel)
                <th class="text-center" style="background-color:#eee; border:0.5pt solid #000;">{{ $mapel->nama_mapel }}</th>
                @endforeach
                <th rowspan="2" style="background-color:#eee; border:0.5pt solid #000;">Total Nilai</th>
                <th rowspan="2" style="background-color:#eee; border:0.5pt solid #000;">Rata-rata</th>
                <th rowspan="2" style="background-color:#eee; border:0.5pt solid #000;">Ranking</th>
            </tr>
            <tr class="bg-header">
                @foreach($mapels as $mapel)
                <th class="text-center" style="font-size:10px; background-color:#f8f9fa; border:0.5pt solid #000;">KKM: {{ $kkm[$mapel->id] ?? 70 }}</th> 
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $ak)
            @php
                $stats = $studentStats[$ak->id_siswa] ?? ['total'=>0, 'avg'=>0, 'rank'=>'-'];
                $sGrades = $grades[$ak->id_siswa] ?? collect([]);
            @endphp
            <tr>
                <td class="text-center" style="border:0.5pt solid #000;">{{ $index + 1 }}</td>
                <td class="text-center" style="mso-number-format:'\@'; border:0.5pt solid #000;">{{ $ak->siswa->nis_lokal }}</td>
                <td style="border:0.5pt solid #000;">{{ $ak->siswa->nama_lengkap }}</td>
                <td class="text-center" style="border:0.5pt solid #000;">{{ $ak->siswa->jenis_kelamin }}</td>
                
                @foreach($mapels as $mapel)
                    @php
                        $grade = $sGrades->where('id_mapel', $mapel->id)->first();
                        
                        $score = 0;
                        if ($grade) {
                            $score = (!empty($showOriginal)) ? ($grade->nilai_akhir_asli ?? $grade->nilai_akhir) : $grade->nilai_akhir;
                        }

                        $kkmVal = $kkm[$mapel->id] ?? 70;
                        $style = 'border:0.5pt solid #000;'; // Removed fixed decimal format
                        if ($grade && $score < $kkmVal) $style .= ' color: red;';
                        
                        // Highlight if Katrol (only in Original Mode)
                        if (!empty($showOriginal) && $grade) {
                            $final = $grade->nilai_akhir;
                            $original = $grade->nilai_akhir_asli ?? $final;
                            if ($final != $original) {
                                $style .= ' background-color: #fff9c4;'; // Light Yellow
                            }
                        }
                    @endphp
                    <td class="text-center" style="{{ $style }}">
                        {{ $grade ? round($score) : '-' }}
                    </td>
                @endforeach
                
                <td class="text-center" style="border:0.5pt solid #000; background-color: #e3f2fd; font-weight: bold; mso-number-format:'0\.00';">{{ number_format($stats['total']) }}</td>
                <td class="text-center" style="border:0.5pt solid #000; background-color: #bbdefb; font-weight: bold; mso-number-format:'0\.00';">{{ number_format($stats['avg'], 2) }}</td>
                <td class="text-center font-bold" style="border:0.5pt solid #000; background-color: #fff9c4; color: black;">{{ $stats['rank'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

