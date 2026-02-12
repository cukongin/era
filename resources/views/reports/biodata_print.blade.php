<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Biodata Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { 
                background-color: white !important; 
                -webkit-print-color-adjust: exact;
            }
            .no-print { display: none !important; }
            .print-container { 
                box-shadow: none !important; 
                margin: 0 !important; 
                width: 100% !important; 
                max-width: 210mm !important; 
                padding: 15mm 20mm !important;
                min-height: 297mm !important;
            }
        }
        
        .paper-a4 {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 15mm 20mm;
            position: relative;
        }
        
        .bio-table td {
            vertical-align: top;
            padding: 6px 4px;
        }
        .label-col { width: 40%; }
        .sep-col { width: 2%; text-align: center; }
        .val-col { width: 58%; font-weight: bold; }
    </style>
</head>
<body class="bg-gray-100 font-sans text-black antialiased">

<div class="no-print sticky top-0 z-50 bg-white border-b border-gray-200 px-4 py-3 shadow-sm mb-8">
    <div class="max-w-[1200px] mx-auto flex items-center justify-between">
        <h1 class="font-bold">Cetak Biodata</h1>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-primary text-white px-4 py-2 rounded font-bold hover:bg-primary-dark transition-colors">Print Biodata</button>
            <button onclick="window.close()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Tutup</button>
        </div>
    </div>
</div>

<div class="flex justify-center p-4">
    <div class="print-container paper-a4">
        
        <div class="text-center mb-10">
            <h1 class="text-xl font-bold uppercase underline">Keterangan Tentang Diri Santri</h1>
        </div>

        <table class="w-full text-base bio-table leading-relaxed">
            <tr>
                <td class="label-col">1. Nama Lengkap</td>
                <td class="sep-col">:</td>
                <td class="val-col uppercase">{{ $student->nama_lengkap }}</td>
            </tr>
            <tr>
                <td>2. Nomor Induk Siswa (NIS)</td>
                <td>:</td>
                <td class="val-col">{{ $student->nis_lokal ?? '-' }}</td>
            </tr>
            <tr>
                <td>3. Nomor Induk Siswa Nasional (NISN)</td>
                <td>:</td>
                <td class="val-col">{{ $student->nisn ?? '-' }}</td>
            </tr>
            <tr>
                <td>4. Tempat, Tanggal Lahir</td>
                <td>:</td>
                <td class="val-col">{{ $student->tempat_lahir ?? '.......' }}, {{ $student->tanggal_lahir ? \Carbon\Carbon::parse($student->tanggal_lahir)->locale('id')->isoFormat('D MMMM Y') : '-' }}</td>
            </tr>
            <tr>
                <td>5. Jenis Kelamin</td>
                <td>:</td>
                <td class="val-col">{{ $student->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td>6. Agama</td>
                <td>:</td>
                <td class="val-col">Islam</td>
            </tr>
            <tr>
                <td>7. Anak ke</td>
                <td>:</td>
                <td class="val-col">{{ $student->anak_ke ?? '...' }}</td>
            </tr>
            <tr>
                <td>8. Alamat Peserta Didik</td>
                <td>:</td>
                <td class="val-col">{{ $student->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <td>9. Nama Orang Tua</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="pl-6">a. Ayah</td>
                <td>:</td>
                <td class="val-col">{{ $student->nama_ayah ?? '-' }}</td>
            </tr>
            <tr>
                <td class="pl-6">b. Ibu</td>
                <td>:</td>
                <td class="val-col">{{ $student->nama_ibu ?? '-' }}</td>
            </tr>
            <tr>
                <td>10. Pekerjaan Orang Tua</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="pl-6">a. Ayah</td>
                <td>:</td>
                <td class="val-col">{{ $student->pekerjaan_ayah ?? '-' }}</td>
            </tr>
            <tr>
                <td class="pl-6">b. Ibu</td>
                <td>:</td>
                <td class="val-col">{{ $student->pekerjaan_ibu ?? '-' }}</td>
            </tr>
            <tr>
                <td>11. Alamat Orang Tua</td>
                <td>:</td>
                <td class="val-col">{{ $student->alamat_ortu ?? $student->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <td>12. Nama Wali Peserta Didik</td>
                <td>:</td>
                <td class="val-col">{{ $student->nama_wali ?? '-' }}</td>
            </tr>
            <tr>
                <td>13. Pekerjaan Wali</td>
                <td>:</td>
                <td class="val-col">{{ $student->pekerjaan_wali ?? '-' }}</td>
            </tr>
            <tr>
                <td>14. Alamat Wali</td>
                <td>:</td>
                <td class="val-col">{{ $student->alamat_wali ?? '-' }}</td>
            </tr>
        </table>

        <!-- Signature & Photo -->
        <div class="flex justify-end mt-16 px-4">
            <div class="text-left relative w-[300px]">
                <div class="flex gap-4 mb-4">
                    <!-- Photo Box -->
                    <div class="w-[3cm] h-[4cm] border border-black flex items-center justify-center text-xs text-center bg-gray-50 flex-shrink-0">
                        Pas Foto<br>3 x 4
                    </div>
                    
                    <!-- Date & Signature -->
                    <div class="flex-1">
                        <p class="mb-1">{{ $school->kabupaten }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</p>
                        <p class="mb-20">Kepala Madrasah,</p>
                        <p class="font-bold border-b border-black inline-block min-w-[150px] uppercase">{{ $school->kepala_madrasah }}</p>
                        <p class="mt-1">NIP. {{ $school->nip_kepala ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>

