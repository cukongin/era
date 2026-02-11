<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Rapor {{ $student->nama_lengkap }} - {{ $class->nama_kelas }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet"/> <!-- Arabic Support -->
    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet"/> <!-- LPMQ Font -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <!-- Compiled CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            body {
                background-color: white !important;
                -webkit-print-color-adjust: exact;
            }
            .no-print { display: none !important; }
            /* Reset padding on the wrapper div during print */
            .print-wrapper {
                padding: 0 !important;
                display: block !important; /* Avoid flex issues */
            }

            .print-container {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 210mm !important;
                padding: 0.5cm 1.5cm !important; /* Top/Bottom 0.5cm, Left/Right 1.5cm (User Request) */
                min-height: auto !important;
            }

            /* Aggressively remove backgrounds */
            .bg-gray-100, .bg-gray-50, .bg-gray-50\/50, .bg-background-light {
                background-color: transparent !important;
            }
            tr, td, th {
                background-color: transparent !important;
            }

            /* Re-add borders for clarity since backgrounds are gone */
            /* User requested consistent thin borders */
            thead th {
                border: 0.5px solid #000 !important;
            }
        }

        /* A4 simulation for screen */
        .paper-a4 {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 0.5cm 1.5cm; /* Match print padding */
            position: relative;
            overflow: hidden;
        }

        /* Compact table styles */
        .rapor-table th, .rapor-table td {
            border: 0.5px solid #000; /* Tipisin border */
            padding: 1px 3px; /* Lebih rapat */
            font-size: 11px; /* Slightly smaller to fit info */
            line-height: 1.1;
        }

        /* General Text Adjustments */
        .text-xs { font-size: 12px !important; line-height: 1 !important; }
        .text-sm { font-size: 13px !important; line-height: 1 !important; }


        .text-base { font-size: 14px !important; line-height: 1.1 !important; }
        .text-lg { font-size: 16px !important; line-height: 1.2 !important; }
        .text-xl { font-size: 18px !important; }
        .text-2xl { font-size: 20px !important; }

        .h-24 { height: 2rem !important; }
        .mb-16 { margin-bottom: 2rem !important; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-[#111518] antialiased">
    {{-- Variable periodSlots passed from Controller --}}
     @php
        // EXPANDED ROBUST JENJANG DETECTION
        // includes Roman Numerals and Trusting Explicit DB 'MTS'
        $name = strtoupper($class->nama_kelas ?? '');
        $isMts = ($class->tingkat_kelas > 6) ||
                 (strpos($name, 'MTS') !== false) ||
                 (strpos($name, 'VII') !== false) ||
                 (strpos($name, 'VIII') !== false) ||
                 (strpos($name, 'IX') !== false) ||
                 (optional($class->jenjang)->kode === 'MTS');

        $jenjang = $isMts ? 'MTS' : 'MI';

        if ($jenjang === 'MTS') {
            $periodSlots = [1, 2];
            $periodLabel = 'Semester';
        } else {
            $periodSlots = [1, 2, 3];
            $periodLabel = 'Catur Wulan';
        }
    @endphp

<!-- Top Navigation (No Print) -->
<div class="no-print sticky top-0 z-50 bg-white border-b border-[#e5e7eb] px-4 py-3 shadow-sm">
    <div class="max-w-[1200px] mx-auto flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined">description</span>
            </div>
            <div>
                <h1 class="text-lg font-bold leading-tight">Sistem Rapor Terpadu</h1>
                <p class="text-xs text-gray-500">Pratinjau Cetak &bull; Format Arial Compact</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button class="flex h-10 cursor-pointer items-center justify-center gap-2 rounded-lg bg-primary px-6 text-sm font-bold text-white transition hover:bg-primary/90" onclick="window.print()">
                <span class="material-symbols-outlined text-[20px]">print</span>
                <span>Cetak PDF</span>
            </button>
            <button class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="window.close()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
    </div>
</div>

<!-- Main Scrollable Area -->
<div class="print-wrapper flex w-full justify-center p-6 sm:p-10">
    @include('reports.partials.rapor_content')
</div>

</body>
</html>

