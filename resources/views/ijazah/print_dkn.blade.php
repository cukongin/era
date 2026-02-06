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
    <table class="table">
        <thead>
            <tr class="bg-gray">
                <th rowspan="2" style="width: 30px;">NO</th>
                <th rowspan="2" style="width: 150px;">NAMA SISWA</th>
                <th rowspan="2" style="width: 200px;">MATA PELAJARAN</th>
                <th colspan="3">KOMPONEN NILAI</th>
                <th rowspan="2" style="width: 50px;">PREDIKAT</th>
                <th rowspan="2" style="width: 80px;">KETERANGAN</th>
            </tr>
            <tr class="bg-gray">
                <th style="width: 80px;">RATA-RATA RAPOR<br>({{ $bRapor }}%)</th>
                <th style="width: 80px;">UJIAN MADRASAH<br>({{ $bUjian }}%)</th>
                <th style="width: 80px;">NILAI AKHIR<br>(IJAZAH)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $s)
                @php 
                    // Determine Grade Counts for Rowspan
                    // Assuming we list ALL mapels for each student per row? 
                    // OR One Student per Row (Horizontal Mapels)?
                    // User Example: "No | Mapel | Rata | Ujian | Akhir | Pred | Ket" -> This looks like ONE ROW PER MAPEL.
                    // BUT DKN usually means "Daftar Kumpulan Nilai" (Student List).
                    // If we follow User Example strictly: "1 | Fiqih...", "2 | Bahasa Arab...". This is per-student transcript style?
                    // NO, User said "Struktur Tabel Bantu" usually implies a per-student sheet.
                    // BUT "Tabel Ijazah (Lengkap Kelulusan)" usually implies DKN (List of Students).
                    
                    // Let's stick to the STANDARD DKN FORMAT (Students as Rows, Mapels as Columns) OR (Students as Blocks).
                    // However, printing ALL columns (Rata, Ujian, NA, Pred, Ket) for EACH mapel horizontally will explode the width.
                    // The User's Example "A B C D E F G H" (No, Mapel, Rapor 4,5,6, Rata, Ujian, NA) is clearly a Transcript format (Vertical Mapels).
                    
                    // IF this is "Cetak DKN" (Class List), we usually only show FINAL GRADE to save space.
                    // BUT User asks "Coba jelaskan...". User provided a TRANSCRIPT view (One student, many mapels).
                    
                    // CHALLENGE: "Cetak DKN" usually means 1 Row per Student.
                    // If I change `print_dkn` to Vertical (One block per student), it becomes "SKL / Transkrip Sementara".
                    
                    // WAIT: User context is "Tabel Ijazah".
                    // Let's look at the View `print_dkn.blade.php`. It currently iterates `$students`.
                    
                    // COMPROMISE: I will make the DKN table simpler but with SUB-COLUMNS for each mapel? No space.
                    // Maybe the user wants a "Ledger Ijazah"?
                    
                    // Let's assume user wants the "Standard DKN" (List of students) but wants to see the FINAL SCORE mostly.
                    // BUT user passed "Predikat" and "Keterangan".
                    // If I add Predikat/Keterangan per Mapel per Student, it's huge.
                    // Maybe Predikat/Keterangan applied to the AVERAGE TOTAL? ("RATA-RATA TOTAL: 80.61 -> LULUS").
                    
                    // Let's look at User's Example 2:
                    // "RATA-RATA TOTAL: 80.61 | B | LULUS"
                    // So Predikat/Status applies to the STUDENT'S OVERALL PERFORMANCE?
                    // OR Per Mapel?
                    // Table shows: "Matematika | 67.2 | D | TIDAK". So PER MAPEL.
                    
                    // If per mapel, a Matrix DKN (Students x Mapels) is hard to fit Predicate.
                    // I will stick to the previous layout (Students x Mapels) BUT add a summary column at the end?
                    // OR is this view meant to be "Leger Ijazah"?
                    
                    // Let's re-read: "Cetak DKN". 
                    // I will implement a "Compact DKN":
                    // Rows: Students.
                    // Cols: Mapels (Just Nilai Akhir).
                    // Summary Cols: Total, Rata-Rata, Predikat (of Avg), Status (Lulus/Tidak).
                    
                    // BUT user might want the "Calculation Proof" per mapel?
                    // That's usually "Leger".
                    
                    // Let's optimize `print_dkn` to display:
                    // 1. One Row Per Student.
                    // 2. Col for each Mapel (Showing NA).
                    // 3. Final Cols: Rata-Rata NA, Predikat (Overall), Keputusan (Lulus/Tidak).
                    
                    // Adding Predicate Logic to the existing DKN summary.
                @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left">{{ $s->siswa->nama_lengkap }}<br><span style="font-size: 9px; color: #666;">NISN: {{ $s->siswa->nisn }}</span></td>
                
                @php 
                    $totalSum = 0; 
                    $mapelCount = 0; 
                    $hasFail = false;
                @endphp

                @foreach($mapels as $mapel)
                    @php
                        $g = $grades[$s->id_siswa]->where('id_mapel', $mapel->id)->first();
                        $val = $g->nilai_ijazah ?? 0;
                        if ($val > 0) {
                            $totalSum += $val;
                            $mapelCount++;
                            if ($val < $minLulus) $hasFail = true;
                        }
                    @endphp
                    <td class="text-center" style="{{ $val < $minLulus && $val > 0 ? 'color: red; font-weight: bold;' : '' }}">
                        {{ $val > 0 ? number_format($val, 2) : '-' }}
                    </td>
                @endforeach
                
                @php 
                    $avg = $mapelCount > 0 ? $totalSum / $mapelCount : 0;
                    // Predikat Global (rata-rata)
                    $predikat = 'D';
                    if ($avg >= 90) $predikat = 'A';
                    elseif ($avg >= 80) $predikat = 'B';
                    elseif ($avg >= 70) $predikat = 'C';
                    
                    // Status
                    // Lulus if Average >= MinLulus AND (Optional: No failed subjects? Usually Ijazah is pure Average, but User showed "TIDAK" per mapel. Usually "Lulus Satuan Pendidikan" depends on Overall Avg).
                    // Let's use Average >= MinLulus.
                    $lulus = $avg >= $minLulus;
                @endphp

                <td class="text-center font-bold">{{ number_format($avg, 2) }}</td>
                <td class="text-center font-bold">{{ $lulus ? 'LULUS' : 'TIDAK' }}</td>
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
