<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>{{ $title ?? 'Cetak Rapor' }}</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <style>
        @page {
            size: A4 {{ $orientation ?? 'portrait' }};
            margin: 0; 
        }
        @media print {
            body { 
                background-color: white !important; 
                -webkit-print-color-adjust: exact;
            }
            .no-print { display: none !important; }
            .print-wrapper {
                padding: 0 !important;
                display: block !important;
            }
            .print-container { 
                box-shadow: none !important; 
                margin: 0 !important; 
                width: 100% !important; 
                padding: {{ $margins['top'] ?? 5 }}mm {{ $margins['right'] ?? 15 }}mm {{ $margins['bottom'] ?? 5 }}mm {{ $margins['left'] ?? 15 }}mm !important;
            }
        }
        
        .paper-a4 {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: {{ $margins['top'] ?? 5 }}mm {{ $margins['right'] ?? 15 }}mm {{ $margins['bottom'] ?? 5 }}mm {{ $margins['left'] ?? 15 }}mm;
            position: relative; 
            overflow: hidden;
        }

        /* Compact Table Styles (Matching Default) */
        .rapor-table th, .rapor-table td {
            border: 0.5px solid #000; /* Tipisin border setara main print */
            padding: 1px 3px; /* Rapat */
            font-size: 11px; 
            line-height: 1.1;
        }
        /* Allow user content styles to work */
        .user-content {
            font-size: 12px;
        }
        /* Override Tailwind Preflight: Restore inline behavior for alignment */
        .user-content img {
            display: inline-block;
        }
        .user-content table {
            width: 100%;
        }
    </style>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet"/>
    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet"/> <!-- LPMQ Font -->
</head>
<body class="bg-gray-100 font-sans antialiased text-black">
    <style>
        body {
            background: white;
            color: black;
        }
    </style>

<div class="no-print sticky top-0 z-50 bg-white border-b border-[#e5e7eb] px-4 py-3 shadow-sm">
    <div class="max-w-[1200px] mx-auto flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="text-lg font-bold leading-tight">Cetak Rapor</h1>
                <p class="text-xs text-gray-500">Mode Template Kustom</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button class="flex h-10 cursor-pointer items-center justify-center gap-2 rounded-lg bg-primary px-6 text-sm font-bold text-white transition hover:bg-primary-dark" onclick="window.print()">
                <span class="material-symbols-outlined text-[20px]">print</span>
                <span>Cetak PDF</span>
            </button>
            <button class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="window.close()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
    </div>
</div>

<div class="print-wrapper flex w-full justify-center p-6 sm:p-10">
    <div class="print-container paper-a4 user-content">
        {!! $content !!}
    </div>
</div>

</body>
</html>

