<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Transkip Nilai - {{ $kelas->nama_kelas }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm 1.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.1;
            -webkit-print-color-adjust: exact;
        }
        .page-break {
            page-break-after: always;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            text-transform: Propercase;
        }
        .header h2 { margin: 0; font-size: 14pt; }
        .header h3 { margin: 0; font-size: 12pt; font-weight: normal; }
        
        .student-info { width: 100%; margin-bottom: 15px; }
        .student-info td { vertical-align: top; padding: 2px 0; }
        .student-info td:first-child { width: 25%; }
        .student-info td:nth-child(2) { width: 2%; }
        
        .table-grades { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-grades th, .table-grades td { border: 1px solid black; padding: 4px 6px; vertical-align: middle; }
        .table-grades th { text-align: center; font-weight: bold; background-color: #f0f0f0; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .footer-notes { font-size: 11pt; margin-top: 10px; }
        .signature-section { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        .signature-box { float: right; width: 40%; text-align: center; }
        .clear { clear: both; }
        
        .group-header { background-color: #fafafa; font-weight: bold; padding-left: 10px !important; }
        .sub-mapel { padding-left: 20px !important; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

@foreach($dataStudents as $index => $ds)
    @php
        $student = $ds['student'];
        // Ensure mapelGroups structure exists even if empty
        $groups = $ds['mapelGroups'] ?? ['A' => [], 'B' => []];
        $avg = $ds['avgNetwork'];

        // Weights
        $bRapor = \App\Models\GlobalSetting::val('ijazah_bobot_rapor', 60);
        $bUjian = \App\Models\GlobalSetting::val('ijazah_bobot_ujian', 40);

        // Determine Jenjang for this student/class
        // Determine Jenjang for this student/class (Robust)
        $isMts = $kelas->tingkat_kelas > 6 || stripos($kelas->nama_kelas, 'mts') !== false;
        $jenjang = $isMts ? 'MTS' : 'MI';

        // Dynamic Headmaster Logic (Re-applied)
        $defaultHm = $school->kepala_madrasah ? trim($school->kepala_madrasah) : '';
        $defaultNip = $school->nip_kepala ? trim($school->nip_kepala) : '';
        
        $hmName = $defaultHm ?: '......................';
        $hmNip = $defaultNip ?: '-';

        if ($jenjang === 'MI') {
            $settingHm = \App\Models\GlobalSetting::val('hm_name_mi');
            $settingNip = \App\Models\GlobalSetting::val('hm_nip_mi');
            if (!empty($settingHm)) $hmName = $settingHm;
            if (!empty($settingNip)) $hmNip = $settingNip;
        } elseif ($jenjang === 'MTS') {
             $settingHm = \App\Models\GlobalSetting::val('hm_name_mts');
             $settingNip = \App\Models\GlobalSetting::val('hm_nip_mts');
             if (!empty($settingHm)) $hmName = $settingHm;
             if (!empty($settingNip)) $hmNip = $settingNip;
        }
    @endphp

    <div class="{{ $index < count($dataStudents) - 1 ? 'page-break' : '' }}" style="page-break-after: always; position: relative; width: 100%;">
    <div class="header">
        @php
            // Robust Jenjang Check
            $isMts = $kelas->tingkat_kelas > 6 || stripos($kelas->nama_kelas, 'mts') !== false;
            $jenjang = $isMts ? 'MTS' : 'MI';
            $kopFile = ($jenjang === 'MTS') ? 'KOP TRANSKIP Mts.svg' : 'KOP TRANSKIP MI.svg';
        @endphp
        <img src="{{ asset('assets/img/' . $kopFile) }}" alt="Kop Surat" style="width: 100%; max-width: 100%; height: auto; margin-bottom: 5px;">
        
        <div style="text-align: center; margin-top: 0px;">
            <h3 style="margin: 0; font-weight: bold; font-size: 12pt; text-decoration: none; letter-spacing: 5px;">TRANSKRIP NILAI</h3>
            <p style="margin: 2px 0; font-size: 11pt;">Nomor: ...........................................</p>
        </div>
    </div>

        <table class="student-info">
            <tr>
                <td>Nama</td><td>:</td><td class="font-bold">{{ strtoupper($student->nama_lengkap) }}</td>
            </tr>
            <tr>
                <td>Tempat, Tanggal Lahir</td><td>:</td>
                <td>{{ $student->tempat_lahir }}, {{ $student->tanggal_lahir ? \Carbon\Carbon::parse($student->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Nomor Induk</td><td>:</td>
                <td>{{ $student->nis_lokal ?? '-' }}</td>
            </tr>
        </table>

        <table class="table-grades">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 45%;">Mata Pelajaran</th>
                    <th style="width: 15%;">Rata-rata<br>Rapor</th>
                    <th style="width: 15%;">Nilai Ujian<br>Madrasah</th>
                    <th style="width: 20%;">Nilai<br>Madrasah</th>
                </tr>
            </thead>
            <tbody>
                <!-- Group A -->
                <tr><td class="font-bold text-center">A.</td><td colspan="4" class="font-bold group-header">Mata Pelajaran Wajib</td></tr>
                @foreach($groups['A'] as $i => $g)
                <tr>
                    <td class="text-center">{{ $i + 1 }}.</td>
                    <td>{{ $g['nama'] }}</td>
                    <td class="text-center">{{ $g['rata_rapor'] }}</td>
                    <td class="text-center">{{ $g['nilai_ujian'] }}</td>
                    <td class="text-center font-bold">{{ $g['nilai_sekolah'] }}</td>
                </tr>
                @endforeach

                <!-- Group B -->
                @if(!empty($groups['B']))
                <tr><td class="font-bold text-center">B.</td><td colspan="4" class="font-bold group-header">Muatan Lokal</td></tr>
                @foreach($groups['B'] as $i => $g)
                <tr>
                    <td class="text-center">{{ $i + 1 }}.</td>
                    <td class="sub-mapel">{{ $g['nama'] }}</td>
                    <td class="text-center">{{ $g['rata_rapor'] }}</td>
                    <td class="text-center">{{ $g['nilai_ujian'] }}</td>
                    <td class="text-center font-bold">{{ $g['nilai_sekolah'] }}</td>
                </tr>
                @endforeach
                @endif

                <tr>
                    <td colspan="4" class="text-right font-bold" style="padding-right: 15px;">Rata-rata</td>
                    <td class="text-center font-bold">{{ $avg }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer-notes">
            <p>Keterangan:</p>
            <ol style="margin-top: 0; padding-left: 10px;">
                @if($jenjang === 'MI')
                <li>Nilai Rata-rata Rapor: Diambil dari rata-rata nilai rapor kelas 4, 5, dan 6 (Semester 1-2).</li>
                @else
                <li>Nilai Rata-rata Rapor: Diambil dari rata-rata nilai rapor kelas 1, 2, dan 3 (Semester 1-6).</li>
                @endif
                <li>Nilai Madrasah: Gabungan Rata-rata Rapor ({{ $bRapor }}%) dan Nilai Ujian Madrasah ({{ $bUjian }}%).</li>
            </ol>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                @php
                    $jenjangKey = strtolower($kelas->jenjang->kode ?? 'mi'); 
                    
                    // Priority: Variable > Setting > School > Default
                    $place = $titimangsaPlace ?? \App\Models\GlobalSetting::val('titimangsa_transkrip_tempat_' . $jenjangKey) 
                             ?? ($school->kabupaten ?? $school->kota ?? 'Tempat');
                    
                    // Date 1 (Main/Hijri)
                    $date1Raw = !empty($titimangsa) ? $titimangsa : \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y');
                    
                    // Date 2 (Secondary/Masehi)
                    // Checks Transkrip Specific 2nd Date, falls back to Rapor 2nd Date
                    $date2Raw = \App\Models\GlobalSetting::val('titimangsa_transkrip_2_' . $jenjangKey)
                                ?? \App\Models\GlobalSetting::val('titimangsa_2_' . $jenjangKey);
                @endphp

                {{-- Wrapper: Inline Block to shrink-wrap content, Left Aligned Text, Floating in the Right Box --}}
                <div style="display: inline-block;">
                    @php
                        // Helper logic to parse dates
                        $parseDate = function($dateStr) {
                            $parts = explode(' ', trim($dateStr));
                            if (count($parts) < 3) return ['day' => $dateStr, 'month' => '', 'year' => '', 'suffix' => ''];
                            
                            $day = array_shift($parts);
                            $last = end($parts);
                            $suffix = '';
                            if (str_ends_with($last, '.') || strlen($last) <= 2) {
                                $suffix = array_pop($parts);
                            }
                            $year = array_pop($parts);
                            $month = implode(' ', $parts);
                            return compact('day', 'month', 'year', 'suffix');
                        };

                        $d1Raw = !empty($date1Raw) ? $date1Raw : '';
                        $d2Raw = !empty($date2Raw) ? $date2Raw : '';
                        
                        $d1 = $parseDate($d1Raw);
                        $d2 = $d2Raw ? $parseDate($d2Raw) : null;
                    @endphp

                    {{-- Unified Table for Alignment --}}
                    <table style="border-collapse: collapse; white-space: nowrap;">
                        {{-- Row 1: Hijri / Main Date --}}
                        <tr class="leading-tight">
                            <td class="pr-2 text-right" style="border: none; vertical-align: top;">{{ $place }},</td>
                            <td class="px-1 text-center" style="border: none;">{{ $d1['day'] }}</td>
                            <td class="px-1 text-left pl-2" style="border: none;">{{ $d1['month'] }}</td>
                            <td class="px-1 text-center" style="border: none;">{{ $d1['year'] }}</td>
                            <td class="pl-1 text-left" style="border: none;">{{ $d1['suffix'] }}</td>
                        </tr>
                        
                        {{-- Row 2: Masehi (Optional) --}}
                        @if($d2)
                        <tr class="leading-tight">
                            <td style="border: none;"></td> {{-- Empty Place Column --}}
                            <td class="px-1 text-center" style="border: none;">{{ $d2['day'] }}</td>
                            <td class="px-1 text-left pl-2" style="border: none;">{{ $d2['month'] }}</td>
                            <td class="px-1 text-center" style="border: none;">{{ $d2['year'] }}</td>
                            <td class="pl-1 text-left" style="border: none;">{{ $d2['suffix'] }}</td>
                        </tr>
                        @endif

                        {{-- Spacer --}}
                        <tr><td colspan="5" style="height: 10px;"></td></tr>

                        {{-- Title --}}
                        <tr class="leading-tight">
                            <td style="border: none;"></td>
                            <td colspan="4" class="text-center" style="border: none;">Mengetahui,</td>
                        </tr>
                        <tr class="leading-tight">
                            <td style="border: none;"></td>
                            <td colspan="4" class="text-center" style="border: none;">Kepala Madrasah</td>
                        </tr>

                        {{-- Signature Space --}}
                        <tr><td colspan="5" style="height: 60px;"></td></tr>

                        {{-- Name --}}
                        <tr>
                            <td style="border: none;"></td>
                            <td colspan="4" class="text-center font-bold" style="border: none;">{{ strtoupper($hmName) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
@endforeach

<script>
    window.onload = function() {
        window.print();
    }
</script>
</body>
</html>
