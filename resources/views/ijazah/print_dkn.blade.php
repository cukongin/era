<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak DKN - {{ $kelas->nama_kelas }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 4px; text-align: center; }
        .text-left { text-align: left !important; }
        .bg-gray { background-color: #f0f0f0; }
        @media print {
            @page { size: landscape; margin: 10mm; }
        }
    </style>
</head>
<body>

    <div class="header">
        DAFTAR KUMPULAN NILAI (DKN) IJAZAH<br>
        TAHUN PELAJARAN {{ $kelas->tahun_ajaran->nama_tahun }}<br>
        {{ strtoupper($school->nama_sekolah ?? 'SEKOLAH') }}
    </div>

    @php
        $bRapor = \App\Models\GlobalSetting::val('ijazah_bobot_rapor', 60);
        $bUjian = \App\Models\GlobalSetting::val('ijazah_bobot_ujian', 40);
        $minLulus = \App\Models\GlobalSetting::val('ijazah_min_lulus', 60);
    @endphp

    <div style="margin-bottom: 10px;">
        Kelas: {{ $kelas->nama_kelas }}
    </div>

    <table class="table">
        <thead>
            <tr class="bg-gray">
                <th rowspan="3" style="width: 30px;">NO</th>
                <th rowspan="3" style="width: 80px;">NISN</th>
                <th rowspan="3" style="width: 200px;">NAMA SISWA</th>
                <th colspan="{{ $mapels->count() }}">MATA PELAJARAN</th>
                <th rowspan="3">RATA<br>RATA</th>
                <th rowspan="3">KET</th>
            </tr>
            <tr class="bg-gray">
                @foreach($mapels as $mapel)
                    <th>{{ $mapel->nama_mapel }}</th>
                @endforeach
            </tr>
            <tr class="bg-gray">
                {{-- No Sub-columns specific here to save space, assuming Final Grade shown --}}
                @foreach($mapels as $mapel)
                    <th>Nilai</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $s)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $s->siswa->nisn }}</td>
                <td class="text-left">{{ $s->siswa->nama_lengkap }}</td>
                
                @php $totalRow = 0; $countRow = 0; @endphp

                @foreach($mapels as $mapel)
                    @php
                        $g = $grades[$s->id_siswa]->where('id_mapel', $mapel->id)->first();
                        $val = $g->nilai_ijazah ?? 0;
                        if ($val > 0) {
                            $totalRow += $val;
                            $countRow++;
                        }
                    @endphp
                    <td>{{ $val > 0 ? number_format($val, 2) : '-' }}</td>
                @endforeach
                
                @php $avgRow = $countRow > 0 ? $totalRow / $countRow : 0; @endphp

                <td><strong>{{ number_format($avgRow, 2) }}</strong></td>
                <td>{{ $avgRow >= $minLulus ? 'LULUS' : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 10px; font-size: 10px;">
        <strong>Keterangan:</strong><br>
        1. Nilai yang tercantum adalah <strong>Nilai Akhir (NA)</strong> Ijazah.<br>
        2. Rumus Nilai Akhir: <strong>NA = (Rata-rata Rapor × {{ $bRapor }}%) + (Nilai Ujian Madrasah × {{ $bUjian }}%)</strong>.<br>
        3. Kriteria Kelulusan: Rata-rata Nilai Akhir minimal <strong>{{ number_format($minLulus, 2) }}</strong>.
    </div>

    <div style="margin-top: 20px; text-align: right; margin-right: 50px;">
        {{ $school->kota ?? 'Kota' }}, {{ date('d F Y') }}<br>
        Kepala Madrasah,<br><br><br><br>
        <strong>{{ $school->nama_kepala_sekolah ?? '......................' }}</strong><br>
        NIP. {{ $school->nip_kepala_sekolah ?? '-' }}
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
