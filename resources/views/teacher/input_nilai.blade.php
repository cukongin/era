@extends('layouts.app')

@section('title', 'Input Nilai - ' . $assignment->mapel->nama_mapel . ($assignment->mapel->nama_kitab ? ' (' . $assignment->mapel->nama_kitab . ')' : ''))

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header info -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                <span>{{ $assignment->kelas->nama_kelas }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                Input Nilai: <span class="font-arabic">{{ $assignment->mapel->nama_mapel }}</span>
                @if($assignment->mapel->nama_kitab)
                    <span class="text-lg font-normal text-slate-500 font-arabic">({{ $assignment->mapel->nama_kitab }})</span>
                @endif
            </h1>
            <p class="text-sm text-slate-500">
                Periode: <strong class="text-slate-800 dark:text-slate-300">{{ $periode->nama_periode }}</strong> •
                KKM: <strong class="text-red-500">{{ $nilaiKkm }}</strong>
            </p>
        </div>
        <div class="flex gap-2">
            <!-- Back Button -->
            <a href="{{ str_contains(url()->previous(), 'monitoring') ? route('walikelas.monitoring', ['kelas_id' => $assignment->id_kelas, 'periode_id' => $periode->id]) : route('teacher.dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all focus:ring-4 focus:ring-slate-200">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                Kembali
            </a>
            @php
                // Check if any grade is already final
                $isFinal = $grades->contains('status', 'final');
            @endphp

            @if($isFinal)

                <div class="flex items-center gap-2">
                    <div class="bg-green-100 text-green-700 px-4 py-2 rounded-lg font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined">lock</span> Nilai Terkunci (Final)
                    </div>

                    {{-- Show Unlock Button for Admin, Wali Kelas, OR Assigned Teacher (Controller will check deadline) --}}
                    @if(Auth::user()->role === 'admin' || $assignment->kelas->id_wali_kelas === Auth::id() || $assignment->id_guru === Auth::id())
                    <form action="{{ route('teacher.unlock-nilai') }}" method="POST" onsubmit="return confirm('Buka kunci nilai? Status akan kembali menjadi DRAFT.')">
                        @csrf
                        <input type="hidden" name="id_kelas" value="{{ $assignment->id_kelas }}">
                        <input type="hidden" name="id_mapel" value="{{ $assignment->id_mapel }}">
                        <input type="hidden" name="id_periode" value="{{ $periode->id }}">

                        <button type="submit" class="bg-amber-100 text-amber-700 hover:bg-amber-200 border border-amber-200 px-4 py-2 rounded-lg font-bold flex items-center gap-2 transition-colors" title="Buka Kunci (Revisi)">
                            <span class="material-symbols-outlined">lock_open</span> Buka Kunci
                        </button>
                    </form>
                    @endif
                </div>
            @else
                <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-white text-slate-700 border border-slate-300 px-4 py-2.5 rounded-xl font-bold hover:bg-slate-50 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">upload_file</span> Import Excel
                </button>
                <button type="submit" name="action" value="draft" form="nilaiForm" class="bg-white text-slate-700 border border-slate-300 px-4 py-2.5 rounded-xl font-bold hover:bg-slate-50 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">save_as</span> Simpan Draft
                </button>
                <button type="submit" name="action" value="finalize" form="nilaiForm" onclick="return confirm('Apakah Anda yakin ingin memfinalisasi nilai? Data akan dikunci dan tidak bisa diubah.')" class="bg-primary text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/30 hover:bg-green-600 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">check_circle</span> Finalisasi Nilai
                </button>
            @endif
        </div>
    </div>

    <!-- Alert Info Bobot -->
    <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg p-3 text-sm flex gap-4 text-slate-600 dark:text-slate-300">
        <span class="font-bold">Bobot Penilaian:</span>
        <span>Harian: {{ $bobot->bobot_harian }}%</span>
        <span>{{ $periode->lingkup_jenjang == 'MI' ? 'Ujian Cawu' : 'PTS' }}: {{ $bobot->bobot_uts_cawu }}%</span>
        @if($periode->lingkup_jenjang == 'MTS')
        <span>PAS/PAT: {{ $bobot->bobot_uas }}%</span>
        @endif
    </div>

    <!-- Grading Table -->
    <div class="bg-white dark:bg-[#1a2e22] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <form id="nilaiForm" action="{{ route('teacher.store-nilai') }}" method="POST">
            @csrf

            <input type="hidden" name="id_kelas" value="{{ $assignment->id_kelas }}">
            <input type="hidden" name="id_mapel" value="{{ $assignment->id_mapel }}">
            <input type="hidden" name="id_periode" value="{{ $periode->id }}">
            <input type="hidden" name="bobot_harian" value="{{ $bobot->bobot_harian }}">
            <input type="hidden" name="bobot_uts" value="{{ $bobot->bobot_uts_cawu }}">
            <input type="hidden" name="bobot_uas" value="{{ $bobot->bobot_uas }}">

        <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 uppercase text-xs font-bold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3 w-10">No</th>
                            <th class="px-4 py-3 min-w-[200px]">Nama Santri</th>

                            @if($blueprint['harian'])
                            <th class="px-4 py-3 text-center w-24">Harian</th>
                            @endif

                            @if($blueprint['uts'])
                            <th class="px-4 py-3 text-center w-24">{{ $blueprint['label_uts'] }}</th>
                            @endif

                            @if($blueprint['uas'])
                            <th class="px-4 py-3 text-center w-24">{{ $blueprint['label_uas'] }}</th>
                            @endif

                            <th class="px-4 py-3 text-center w-20">Akhir</th>
                            <th class="px-4 py-3 text-center w-20">Predikat</th>
                            <th class="px-4 py-3 min-w-[200px]">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($students as $index => $ak)
                        @php
                            $nilai = $grades[$ak->id_siswa] ?? null;
                            $disabled = isset($isFinal) && $isFinal ? 'disabled' : ''; // Variable defined in header block
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-4 py-3 text-center text-slate-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $ak->siswa->nama_lengkap }}</td>

                            <!-- Input Harian -->
                            @if($blueprint['harian'])
                            <td class="px-2 py-2">
                                @php
                                    // FORCE INTEGER DISPLAY
                                    $hVal = ($bobot->bobot_harian == 0 && ($nilai->nilai_harian ?? null) === null) ? 0 : ($nilai->nilai_harian ?? '');
                                    // If numeric, round it to remove decimals for display
                                    if(is_numeric($hVal)) $hVal = (int) round($hVal);
                                @endphp
                                <input {{ $disabled }} type="number" step="1" name="grades[{{ $ak->id_siswa }}][harian]" value="{{ $hVal }}" min="0" max="100" class="w-full text-center rounded border-slate-300 dark:bg-slate-800 dark:border-slate-700 py-1 focus:ring-primary nilai-input {{ $disabled ? 'bg-slate-100 text-slate-500' : '' }}" data-weight="{{ $bobot->bobot_harian }}">
                            </td>
                            @else
                                <input type="hidden" name="grades[{{ $ak->id_siswa }}][harian]" value="0" class="nilai-input" data-weight="0">
                            @endif

                            <!-- Input UTS/Cawu -->
                            @if($blueprint['uts'])
                            <td class="px-2 py-2">
                                @php
                                    $tVal = ($bobot->bobot_uts_cawu == 0 && ($nilai->nilai_uts_cawu ?? null) === null) ? 0 : ($nilai->nilai_uts_cawu ?? '');
                                    if(is_numeric($tVal)) $tVal = (int) round($tVal);
                                @endphp
                                <input {{ $disabled }} type="number" step="1" name="grades[{{ $ak->id_siswa }}][uts]" value="{{ $tVal }}" min="0" max="100" class="w-full text-center rounded border-slate-300 dark:bg-slate-800 dark:border-slate-700 py-1 focus:ring-primary nilai-input {{ $disabled ? 'bg-slate-100 text-slate-500' : '' }}" data-weight="{{ $bobot->bobot_uts_cawu }}">
                            </td>
                            @else
                                <input type="hidden" name="grades[{{ $ak->id_siswa }}][uts]" value="0" class="nilai-input" data-weight="0">
                            @endif

                            <!-- Input UAS (Only MTs) -->
                            @if($blueprint['uas'])
                            <td class="px-2 py-2">
                                @php
                                    $aVal = ($bobot->bobot_uas == 0 && ($nilai->nilai_uas ?? null) === null) ? 0 : ($nilai->nilai_uas ?? '');
                                    if(is_numeric($aVal)) $aVal = (int) round($aVal);
                                @endphp
                                <input {{ $disabled }} type="number" step="1" name="grades[{{ $ak->id_siswa }}][uas]" value="{{ $aVal }}" min="0" max="100" class="w-full text-center rounded border-slate-300 dark:bg-slate-800 dark:border-slate-700 py-1 focus:ring-primary nilai-input {{ $disabled ? 'bg-slate-100 text-slate-500' : '' }}" data-weight="{{ $bobot->bobot_uas }}">
                            </td>
                            @else
                                <input type="hidden" name="grades[{{ $ak->id_siswa }}][uas]" value="0" class="nilai-input" data-weight="0">
                            @endif

                            <!-- Calc Result -->
                            <td class="px-4 py-3 text-center font-bold text-slate-900 dark:text-white relative group">
                                <span class="nilai-akhir text-lg block">{{ $nilai->nilai_akhir ?? 0 }}</span>
                            </td>
                            <td class="px-4 py-3 text-center font-bold predikat">
                                {{ $nilai->predikat ?? '-' }}
                            </td>

                            <td class="px-2 py-2">
                                <input type="text" name="grades[{{ $ak->id_siswa }}][catatan]" value="{{ $nilai->catatan ?? '' }}" placeholder="Catatan..." class="w-full text-sm rounded border-slate-300 dark:bg-slate-800 dark:border-slate-700 py-1 focus:ring-primary">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
    // Live Calculation Script
    const predicateRules = @json($predicateRules);

    // Initial Calculation on Load
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('tbody tr').forEach(row => {
            // Trigger calculation for the first input of each row to init values
            const firstInput = row.querySelector('.nilai-input');
            if (firstInput) {
                // Create a fake event object
                const evt = { target: firstInput };
                calculateRow(evt);
            }
        });
    });

    document.querySelectorAll('.nilai-input').forEach(input => {
        input.addEventListener('input', calculateRow);
    });

    function calculateRow(e) {
        const row = e.target.closest('tr');
        const inputs = row.querySelectorAll('.nilai-input');
        let totalScore = 0;

        inputs.forEach(inp => {
            const val = parseFloat(inp.value) || 0;
            const weight = parseFloat(inp.getAttribute('data-weight')) || 0;
            totalScore += val * (weight / 100);
        });

        // Rounding Logic based on Settings
        const roundingEnable = {{ isset($gradingSettings) && $gradingSettings->rounding_enable ? 'true' : 'false' }};
        let finalScore = totalScore;

        if (roundingEnable) {
            finalScore = Math.round(totalScore);
        } else {
            finalScore = Math.round(totalScore * 100) / 100;
        }

        // --- KKM Coloring Only ---
        const kkm = {{ $nilaiKkm }};

        // Update Akhir
        const akhirCell = row.querySelector('.nilai-akhir');
        akhirCell.innerText = finalScore;

        // Update Color
        const predikatCell = row.querySelector('.predikat');

        if (finalScore < kkm) {
            akhirCell.classList.add('text-red-500');
            akhirCell.classList.remove('text-slate-900', 'dark:text-white');
        } else {
            akhirCell.classList.remove('text-red-500');
        }

        // Dynamic Rule Check
        let predikat = 'D';
        for (const rule of predicateRules) {
            if (finalScore >= rule.min_score) {
                predikat = rule.grade;
                break; // Because sorted by min_score desc
            }
        }

        predikatCell.innerText = predikat;
    }
</script>
@endsection

<!-- Import Nilai Modal -->
<div id="importModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-[#1a2e22] text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-800">
                <form action="{{ route('teacher.input-nilai.import', ['kelas' => $assignment->id_kelas, 'mapel' => $assignment->id_mapel]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white dark:bg-[#1a2e22] px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="h-10 w-10 flex-shrink-0 rounded-full bg-green-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-green-600">upload_file</span>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">Import Nilai Excel</h3>
                                <p class="text-xs text-slate-500">Upload format .csv atau .txt</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="p-3 bg-primary/5 border border-primary/10 rounded-lg text-sm text-primary">
                                <b>Langkah 1:</b> Download Template terlebih dahulu agar format sesuai dengan data siswa di kelas ini.
                                <div class="mt-2">
                                    <a href="{{ route('teacher.input-nilai.template', ['kelas' => $assignment->id_kelas, 'mapel' => $assignment->id_mapel]) }}" class="inline-flex items-center gap-1 text-white bg-primary hover:bg-primary/90 px-3 py-1.5 rounded-md text-xs font-bold transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">download</span> Download Template
                                    </a>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium leading-6 text-slate-900 dark:text-white">File CSV/Excel</label>
                                <input type="file" name="file" accept=".csv, .txt, .xls, .html" required class="block w-full text-sm text-slate-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-primary/10 file:text-primary
                                  hover:file:bg-primary/20
                                "/>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-black/20 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 sm:ml-3 sm:w-auto">Upload & Validasi</button>
                        <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

