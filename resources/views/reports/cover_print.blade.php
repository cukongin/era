<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Cover Rapor</title>
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
                padding: 20mm !important;
                min-height: 297mm !important;
            }
        }
        
        .paper-a4 {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 20mm;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .font-serif { font-family: 'Times New Roman', Times, serif; }
    </style>
</head>
<body class="bg-gray-100 font-serif text-black antialiased">

<!-- No Print Nav -->
<div class="no-print sticky top-0 z-50 bg-white border-b border-gray-200 px-4 py-3 shadow-sm mb-8">
    <div class="max-w-[1200px] mx-auto flex items-center justify-between">
        <h1 class="font-bold">Cetak Cover</h1>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-primary text-white px-4 py-2 rounded font-bold hover:bg-primary-dark transition-colors">Print Cover</button>
            <button onclick="window.close()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">Tutup</button>
        </div>
    </div>
</div>

<div class="flex justify-center p-4">
    <div class="print-container paper-a4">
        
        <!-- 1. Header Title -->
        <div class="mt-10 mb-8">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Logo_Kementerian_Agama_Pengasuh.png/586px-Logo_Kementerian_Agama_Pengasuh.png" class="h-24 w-24 object-contain mx-auto mb-4" alt="Logo Garuda">
            <h1 class="text-2xl font-bold uppercase tracking-wide mb-2">Laporan Hasil Belajar</h1>
            <h2 class="text-xl font-bold uppercase tracking-wider">(RAPOR)</h2>
        </div>

        <!-- 2. Logo Sekolah Center -->
        <div class="flex-1 flex items-center justify-center my-8">
            @if($school->logo)
                <img src="{{ asset($school->logo) }}" class="h-48 w-48 object-contain" alt="Logo Sekolah">
            @else
                <div class="h-48 w-48 border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400">
                    No Logo
                </div>
            @endif
        </div>

        <!-- 3. Identitas Siswa -->
        <div class="w-full mb-12">
            <p class="mb-4 text-lg">Nama Peserta Didik:</p>
            <div class="border border-black p-4 inline-block min-w-[300px] rounded-lg mb-6">
                <h3 class="text-xl font-bold uppercase">{{ $student->nama_lengkap }}</h3>
            </div>
            
            <p class="mb-2 text-lg">Nomor Induk Siswa (NIS):</p>
            <div class="border-b-2 border-black inline-block min-w-[200px] pb-1">
                <p class="text-lg font-bold">{{ $student->nis_lokal ?? '-' }}</p>
            </div>
        </div>

        <!-- 4. Footer Sekolah -->
        <div class="mt-auto w-full">
            <h2 class="text-2xl font-bold uppercase mb-2">{{ $school->nama_sekolah }}</h2>
            <p class="text-lg font-bold uppercase text-gray-700 mb-1">Kecamatan {{ $school->kecamatan ?? '.......' }}</p>
            <p class="text-lg font-bold uppercase text-gray-700">Kabupaten {{ $school->kabupaten ?? '.......' }}</p>
            
            <div class="mt-8 border-t border-black pt-4 w-1/2 mx-auto">
                <p class="font-bold">Tahun Pelajaran {{ $activeYear->nama }}</p>
            </div>
        </div>

    </div>
</div>

</body>
</html>

