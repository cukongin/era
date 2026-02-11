<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkip Nilai - {{ $student->nama_lengkap }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt; /* Adjusted for better fit */
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 0;
            font-size: 14pt;
        }
        .header h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: normal;
        }
        .student-info {
            width: 100%;
            margin-bottom: 15px;
        }
        .student-info td {
            vertical-align: top;
            padding: 2px 0;
        }
        .student-info td:first-child {
            width: 25%;
        }
        .student-info td:nth-child(2) {
            width: 2%;
        }
        .table-grades {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-grades th, .table-grades td {
            border: 1px solid black;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .table-grades th {
            text-align: center;
            font-weight: bold;
            background-color: #f0f0f0; /* Optional: light gray header */
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .footer-notes {
            font-size: 9pt;
            margin-top: 10px;
        }
        .signature-section {
            margin-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-box {
            float: right;
            width: 40%;
            text-align: center;
        }
        .clear { clear: both; }
        
        /* Utility for Group Headers */
        .group-header {
            background-color: #fafafa;
            font-weight: bold;
            padding-left: 10px !important;
        }
        .sub-mapel {
            padding-left: 20px !important;
        }
    </style>
</head>
<body>

    <div class="header">
        <h3>DAFTAR NILAI UJIAN</h3>
        <h3>{{ strtoupper($school->nama_sekolah ?? 'SEKOLAH MENENGAH ATAS') }}</h3>
        <h3 style="font-weight: normal; text-transform: none; font-size: 11pt; margin-top: 5px;">Program Ilmu Pengetahuan Sosial (CONTOH/TODO)</h3> 
        <!-- TODO: Program/Jurusan logic if needed, currently hardcoded or fetched from class -->
    </div>

    <table class="student-info">
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td class="font-bold">{{ strtoupper($student->nama_lengkap) }}</td>
        </tr>
        <tr>
            <td>Tempat dan Tanggal Lahir</td>
            <td>:</td>
            <td>{{ $student->tempat_lahir }}, {{ $student->tanggal_lahir ? \Carbon\Carbon::parse($student->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td>Nomor Induk Siswa</td>
            <td>:</td>
            <td>{{ $student->nis_lokal }}</td>
        </tr>
        <tr>
            <td>Nomor Induk Siswa Nasional</td>
            <td>:</td>
            <td>{{ $student->nisn }}</td>
        </tr>
    </table>

    <table class="table-grades">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 45%;">Mata Pelajaran</th>
                <th style="width: 15%;">Nilai Rata-rata<br>Rapor<sup>1)</sup></th>
                <th style="width: 15%;">Nilai Ujian<br>Sekolah</th>
                <th style="width: 20%;">Nilai<br>Sekolah<sup>2)</sup></th>
            </tr>
        </thead>
        <tbody>
            <!-- Group A: Wajib -->
            <tr>
                <td class="font-bold text-center">A.</td>
                <td colspan="4" class="font-bold group-header">Mata Pelajaran Wajib</td>
            </tr>
            @foreach($mapelGroups['A'] as $index => $grade)
            <tr>
                <td class="text-center">{{ $index + 1 }}.</td>
                <td>{{ $grade['nama'] }}</td>
                <td class="text-center">{{ $grade['rata_rapor'] }}</td>
                <td class="text-center">{{ $grade['nilai_ujian'] }}</td>
                <td class="text-center font-bold">{{ $grade['nilai_sekolah'] }}</td>
            </tr>
            @endforeach

            <!-- Group B: Muatan Lokal / Lainnya -->
            @if(!empty($mapelGroups['B']))
            <tr>
                <td class="font-bold text-center">B.</td>
                <td colspan="4" class="font-bold group-header">Muatan Lokal</td>
            </tr>
            @foreach($mapelGroups['B'] as $index => $grade)
            <tr>
                <td class="text-center">{{ $index + 1 }}.</td>
                <td class="sub-mapel">{{ $grade['nama'] }}</td> <!-- Indented -->
                <td class="text-center">{{ $grade['rata_rapor'] }}</td>
                <td class="text-center">{{ $grade['nilai_ujian'] }}</td>
                <td class="text-center font-bold">{{ $grade['nilai_sekolah'] }}</td>
            </tr>
            @endforeach
            @endif

            <!-- Rata-rata Row -->
            <tr>
                <td colspan="4" class="text-right font-bold" style="padding-right: 15px;">Rata-rata</td>
                <td class="text-center font-bold">{{ $avgNetwork }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer-notes">
        <p>Keterangan:</p>
        <ol style="margin-top: 0; padding-left: 20px;">
            <li>Nilai Rata-rata Rapor: Rata-rata nilai semester 1-5 (MI/MTs) atau Semester 1-5 (SMA Satuan Pendidikan)</li>
            <li>Nilai Sekolah: Gabungan Rata-rata Rapor dan Nilai Ujian Sekolah (Sesuai Konfigurasi)</li>
        </ol>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <p>{{ $titimangsa }}</p>
            <p>Kepala Sekolah,</p>
            <br><br><br><br>
            <p class="font-bold" style="text-decoration: underline;">{{ strtoupper($school->kepala_sekolah) }}</p>
            <p>NIP. {{ $school->nip_kepala_sekolah ?? '-' }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>

