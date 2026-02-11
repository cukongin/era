<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak DKN - {{ $kelas->nama_kelas }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; font-weight: bold; text-transform: uppercase; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        .text-left { text-align: left !important; padding-left: 5px !important; }
        .bg-gray { background-color: #f0f0f0; }
        .vertical-header {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            height: 120px;
            padding: 5px;
            font-size: 9px;
            max-width: 30px;
        }
        .rank-col { font-weight: bold; background-color: #f9f9f9; }
        @media print {
            @page { size: landscape; margin: 10mm; }
            body { -webkit-print-color-adjust: exact; }
            .bg-gray { background-color: #f0f0f0 !important; }
        }
    </style>
</head>
<body>

    <div class="header">
        DAFTAR KUMPULAN NILAI (DKN) IJAZAH<br>
        TAHUN PELAJARAN {{ $kelas->tahun_ajaran->nama_tahun }}<br>
        {{ $school->nama_sekolah ?? 'SEKOLAH' }}
    </div>

    @php
        $bRapor = \App\Models\GlobalSetting::val('ijazah_bobot_rapor', 60);
        $bUjian = \App\Models\GlobalSetting::val('ijazah_bobot_ujian', 40);
        $minLulus = \App\Models\GlobalSetting::val('ijazah_min_lulus', 60);
    @endphp

    <div style="margin-bottom: 10px; font-weight: bold;">
        KELAS: {{ $kelas->nama_kelas }} <span style="float: right;">Wali Kelas: {{ $kelas->wali_kelas->name ?? '-' }}</span>
    </div>

    <table class="table">
        <thead>
            <tr class="bg-gray">
                <th rowspan="2" style="width: 25px;">NO</th>
                <th rowspan="2" style="width: 180px;">NAMA SISWA / NISN</th>
                <th rowspan="2" style="width: 40px;">KET</th>
                @foreach($mapels as $mapel)
                    <th rowspan="2" class="vertical-header">{{ $mapel->nama_mapel }}</th>
                @endforeach
                <th colspan="2">RATA-RATA</th>
                <th rowspan="2" style="width: 60px;">STATUS</th>
            </tr>
            <tr class="bg-gray">
                <th style="width: 40px;">JML</th>
                <th style="width: 40px;">AVG</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $s)
                @php 
                    // 1. CALCULATE ALL DATA FIRST
                    $dataRR = []; $sumRR = 0; $countRR = 0;
                    $dataUM = []; $sumUM = 0; $countUM = 0;
                    $dataNA = []; $sumNA = 0; $countNA = 0;
                    
                    foreach($mapels as $mapel) {
                        $g = $grades[$s->id_siswa]->where('id_mapel', $mapel->id)->first();
                        
                        // RR
                        $rr = $g->rata_rata_rapor ?? 0;
                        $dataRR[$mapel->id] = $rr;
                        if($rr > 0) { $sumRR += $rr; $countRR++; }
                        
                        // UM (Rounded)
                        $um = isset($g->nilai_ujian_madrasah) ? round($g->nilai_ujian_madrasah) : 0;
                        $dataUM[$mapel->id] = $um;
                        if($um > 0) { $sumUM += $um; $countUM++; }
                        
                        // NA
                        $na = 0;
                        if ($rr > 0 || $um > 0) {
                            $calc = ($rr * ($bRapor/100)) + ($um * ($bUjian/100));
                            $na = round($calc, 2);
                        }
                        $dataNA[$mapel->id] = $na;
                        if($na > 0) { $sumNA += $na; $countNA++; }
                    }
                    
                    // Averages
                    $avgRR = $countRR > 0 ? $sumRR / $countRR : 0;
                    $avgUM = $countUM > 0 ? $sumUM / $countUM : 0;
                    $avgNA = $countNA > 0 ? $sumNA / $countNA : 0;
                    $lulus = $avgNA >= $minLulus;
                @endphp
                
                <!-- ROW 1: Rata Rapor -->
                <tr>
                    <td rowspan="3" class="text-center">{{ $index + 1 }}</td>
                    <td rowspan="3" class="text-left">
                        <div style="font-weight: bold;">{{ strtoupper($s->siswa->nama_lengkap) }}</div>
                        <div style="font-size: 9px; color: #555;">{{ $s->siswa->nisn ?? '-' }}</div>
                    </td>
                    <td class="text-center" style="font-size: 9px; background-color: #fdfdfd;">Rata-rata Rapor</td>
                    @foreach($mapels as $mapel)
                        <td class="text-center text-gray-500" style="font-size: 9px;">
                            {{ $dataRR[$mapel->id] > 0 ? $dataRR[$mapel->id] : '-' }}
                        </td>
                    @endforeach
                    <td class="text-center" style="font-size: 9px;">{{ $avgRR > 0 ? number_format($avgRR, 2) : '-' }}</td>
                    <td class="text-center" style="font-size: 9px;">-</td>
                    <td rowspan="3" class="text-center font-bold" style="{{ $lulus ? 'color: green;' : 'color: red;' }}">
                        {{ $lulus ? 'LULUS' : 'TIDAK' }}
                    </td>
                </tr>

                <!-- ROW 2: Ujian Madrasah -->
                <tr>
                    <td class="text-center" style="font-size: 9px; background-color: #fdfdfd;">Ujian Madrasah</td>
                    @foreach($mapels as $mapel)
                        <td class="text-center text-gray-500" style="font-size: 9px;">
                            {{ $dataUM[$mapel->id] > 0 ? $dataUM[$mapel->id] : '-' }}
                        </td>
                    @endforeach
                    <td class="text-center" style="font-size: 9px;">{{ $avgUM > 0 ? round($avgUM) : '-' }}</td>
                    <td class="text-center" style="font-size: 9px;">-</td>
                </tr>

                <!-- ROW 3: Nilai Akhir (Highlight) -->
                <tr style="background-color: #f0f8ff;">
                    <td class="text-center font-bold" style="font-size: 9px;">Nilai Akhir</td>
                    @foreach($mapels as $mapel)
                        @php $val = $dataNA[$mapel->id]; @endphp
                        <td class="text-center font-bold" style="{{ $val < $minLulus && $val > 0 ? 'color: red;' : '' }}">
                            {{ $val > 0 ? number_format($val, 2) : '-' }}
                        </td>
                    @endforeach
                    <td class="text-center font-bold">{{ $sumNA > 0 ? $sumNA : '-' }}</td>
                    <td class="text-center font-bold" style="background-color: #333; color: white;">
                        {{ $avgNA > 0 ? number_format($avgNA, 2) : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 10px; font-size: 10px;">
        <strong>Keterangan:</strong><br>
        1. Nilai yang tercantum adalah <strong>Nilai Akhir (NA)</strong>.<br>
        2. Rumus: <strong>NA = (Rata-rata Rapor Ã— {{ $bRapor }}%) + (Ujian Madrasah (Bulat) Ã— {{ $bUjian }}%)</strong>.<br>
        3. Kriteria Kelulusan: Rata-rata Nilai Akhir minimal <strong>{{ number_format($minLulus, 2) }}</strong>.
    </div>

    @php
        // Jenjang Logic (Robust)
        $jenjang = ($kelas->jenjang->kode ?? '') == 'MTS' || $kelas->tingkat_kelas > 6 ? 'MTS' : 'MI';
        $key = strtolower($jenjang);
        
        // Fetch Headmaster from Global Settings (Priority)
        $hmName = \App\Models\GlobalSetting::val("hm_name_$key");
        $hmNip = \App\Models\GlobalSetting::val("hm_nip_$key");
        
        // Fallback to IdentitasSekolah if GlobalSetting is empty
        if(empty($hmName)) $hmName = $school->nama_kepala_sekolah ?? '......................';
        if(empty($hmNip)) $hmNip = $school->nip_kepala_sekolah ?? '-';
        
        // Date (Indonesian Format)
        $date = \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y');
        $place = $school->kabupaten ?? $school->kota ?? 'Tempat';
    @endphp

    <table style="width: 100%; margin-top: 30px; border: none; page-break-inside: avoid;">
        <tr>
            <td style="border: none; width: 65%;"></td>
            <td style="border: none; text-align: center;">
                {{ $place }}, {{ $date }}<br>
                Kepala Madrasah,<br><br><br><br><br>
                <strong>{{ strtoupper($hmName) }}</strong>
            </td>
        </tr>
    </table>

    <script>
        window.print();
    </script>
</body>
</html>

