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
                <th rowspan="2" style="width: 30px;">NO</th>
                <th rowspan="2" style="width: 200px;">NAMA SISWA / NISN</th>
                @foreach($mapels as $mapel)
                    <th rowspan="2" class="vertical-header">{{ $mapel->nama_mapel }}</th>
                @endforeach
                <th colspan="2">NILAI AKHIR</th>
                <th rowspan="2" style="width: 60px;">KET</th>
            </tr>
            <tr class="bg-gray">
                <th style="width: 50px;">JUMLAH</th>
                <th style="width: 50px;">RATA-RATA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $s)
                @php 
                    $totalNa = 0; 
                    $mapelCount = 0; 
                    $hasFail = false;
                @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left">
                    <div style="font-weight: bold;">{{ strtoupper($s->siswa->nama_lengkap) }}</div>
                    <div style="font-size: 9px; color: #555;">{{ $s->siswa->nisn ?? '-' }}</div>
                </td>
                
                @foreach($mapels as $mapel)
                    @php
                        // Logic MATCHING Excel Export
                        // 1. Get Nilai Ijazah Row
                        $g = $grades[$s->id_siswa]->where('id_mapel', $mapel->id)->first();
                        
                        // 2. Fetch RR and UM
                        $rr = $g->rata_rata_rapor ?? 0;
                        $um = isset($g->nilai_ujian_madrasah) ? round($g->nilai_ujian_madrasah) : 0; // ROUNDED
                        
                        // 3. Calculate NA
                        $na = 0;
                        if ($rr > 0 || $um > 0) {
                            $calc = ($rr * ($bRapor/100)) + ($um * ($bUjian/100));
                            $na = round($calc, 2);
                        }
                        
                        // Accumulate
                        if ($na > 0) {
                            $totalNa += $na;
                            $mapelCount++;
                        }
                        
                        // Fail Check (Optional for display color)
                        $isLow = $na < $minLulus && $na > 0;
                    @endphp
                    <td class="text-center" style="{{ $isLow ? 'color: red; font-weight: bold;' : '' }}">
                        {{ $na > 0 ? number_format($na, 2) : '-' }}
                    </td>
                @endforeach
                
                @php 
                    $avgNa = $mapelCount > 0 ? $totalNa / $mapelCount : 0;
                    $lulus = $avgNa >= $minLulus;
                @endphp

                <td class="text-center font-bold">{{ $totalNa > 0 ? number_format($totalNa, 2) : '-' }}</td>
                <td class="text-center font-bold" style="background-color: #f0f0f0;">{{ $avgNa > 0 ? number_format($avgNa, 2) : '-' }}</td>
                <td class="text-center font-bold" style="{{ $lulus ? 'color: green;' : 'color: red;' }}">
                    {{ $lulus ? 'LULUS' : 'TIDAK' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 10px; font-size: 10px;">
        <strong>Keterangan:</strong><br>
        1. Nilai yang tercantum adalah <strong>Nilai Akhir (NA)</strong>.<br>
        2. Rumus: <strong>NA = (Rata-rata Rapor × {{ $bRapor }}%) + (Ujian Madrasah (Bulat) × {{ $bUjian }}%)</strong>.<br>
        3. Kriteria Kelulusan: Rata-rata Nilai Akhir minimal <strong>{{ number_format($minLulus, 2) }}</strong>.
    </div>

    <table style="width: 100%; margin-top: 30px; border: none;">
        <tr>
            <td style="border: none; width: 70%;"></td>
            <td style="border: none; text-align: center;">
                {{ $school->kota ?? 'Kota' }}, {{ date('d F Y') }}<br>
                Kepala Madrasah,<br><br><br><br><br>
                <strong><u>{{ $school->nama_kepala_sekolah ?? '......................' }}</u></strong><br>
                NIP. {{ $school->nip_kepala_sekolah ?? '-' }}
            </td>
        </tr>
    </table>

    <script>
        window.print();
    </script>
</body>
</html>
