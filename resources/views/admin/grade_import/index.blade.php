@extends('layouts.app')

@section('title', 'Import Nilai Global (Semua Kelas)')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary dark:from-primary dark:to-secondary flex items-center gap-2">
                <span class="material-symbols-outlined text-primary dark:text-primary">cloud_upload</span>
                Import Data Global
            </h1>
            <p class="text-slate-500">Import Nilai Mapel, Absensi, dan Sikap sekaligus untuk semua kelas.</p>
        </div>
    </div>

    <!-- MAIN IMPORT SECTION -->
    <div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Card MI -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-secondary mb-2">Jenjang MI</h3>
                <p class="text-sm text-slate-500 mb-4">Import data (Nilai, Absensi, Sikap) untuk semua kelas 1-6 MI.</p>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pilih Tingkat (Opsional)</label>
                        <select id="gradeMI" class="w-full text-sm rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600" onchange="updateLinks()">
                            <option value="">Semua Tingkat (1-6)</option>
                            <option value="1">Kelas 1</option>
                            <option value="2">Kelas 2</option>
                            <option value="3">Kelas 3</option>
                            <option value="4">Kelas 4</option>
                            <option value="5">Kelas 5</option>
                            <option value="6">Kelas 6</option>
                        </select>
                    </div>

                    <a id="btnDlMI" href="{{ route('grade.import.global.template', 'MI') }}" class="block w-full text-center px-4 py-2 border-2 border-dashed border-secondary/30 rounded-lg hover:bg-secondary/10 transition-colors font-bold text-secondary">
                        <span class="material-symbols-outlined align-middle mr-2">download</span>
                        Download Template MI
                    </a>
                    
                    <form action="{{ route('grade.import.global.preview', 'MI') }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                        @csrf
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Upload File CSV/Excel (MI)</label>
                        <input type="file" name="file" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-secondary/10 file:text-secondary hover:file:bg-secondary/20">
                        <button type="submit" class="w-full bg-secondary text-white px-4 py-2 rounded-lg font-bold hover:bg-secondary-dark transition-colors mt-2">
                            Preview & Import MI
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card MTS -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-primary mb-2">Jenjang MTS</h3>
                <p class="text-sm text-slate-500 mb-4">Import data (Nilai, Absensi, Sikap) untuk semua kelas 7-9 MTS.</p>
                
                <div class="space-y-4">
                     <div>
                         <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pilih Tingkat (Opsional)</label>
                         <select id="gradeMTS" class="w-full text-sm rounded-lg border-slate-300 dark:bg-slate-700 dark:border-slate-600" onchange="updateLinks()">
                             <option value="">Semua Tingkat (7-9)</option>
                             <option value="7">Kelas 7</option>
                             <option value="8">Kelas 8</option>
                             <option value="9">Kelas 9</option>
                         </select>
                    </div>

                    <a id="btnDlMTS" href="{{ route('grade.import.global.template', 'MTS') }}" class="block w-full text-center px-4 py-2 border-2 border-dashed border-primary/30 rounded-lg hover:bg-primary/10 transition-colors font-bold text-primary">
                        <span class="material-symbols-outlined align-middle mr-2">download</span>
                        Download Template MTS
                    </a>
                    
                    <form action="{{ route('grade.import.global.preview', 'MTS') }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                        @csrf
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Upload File CSV/Excel (MTS)</label>
                        <input type="file" name="file" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        <button type="submit" class="w-full bg-primary text-white px-4 py-2 rounded-lg font-bold hover:bg-primary-dark transition-colors mt-2">
                            Preview & Import MTS
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateLinks() {
        // --- GRADES ---
        // MI
        const gradeMI = document.getElementById('gradeMI').value;
        const btnMI = document.getElementById('btnDlMI');
        let urlMI = "{{ route('grade.import.global.template', 'MI') }}";
        if(gradeMI) urlMI += '?grade=' + gradeMI;
        btnMI.href = urlMI;
        btnMI.innerHTML = '<span class="material-symbols-outlined align-middle mr-2">download</span> Download Template ' + (gradeMI ? 'Kelas ' + gradeMI : 'MI (Semua)');

        // MTS
        const gradeMTS = document.getElementById('gradeMTS').value;
        const btnMTS = document.getElementById('btnDlMTS');
        let urlMTS = "{{ route('grade.import.global.template', 'MTS') }}";
        if(gradeMTS) urlMTS += '?grade=' + gradeMTS;
        btnMTS.href = urlMTS;
        btnMTS.innerHTML = '<span class="material-symbols-outlined align-middle mr-2">download</span> Download Template ' + (gradeMTS ? 'Kelas ' + gradeMTS : 'MTS (Semua)');
        

    }
</script>
@endsection

