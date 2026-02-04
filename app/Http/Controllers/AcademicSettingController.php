<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TahunAjaran;

class AcademicSettingController extends Controller
{
    public function index()
    {
        // 1. Ambil Semua Tahun Ajaran
        $years = TahunAjaran::orderBy('id', 'desc')->get();

        $activeYear = $years->firstWhere('status', 'aktif');
        $archivedYears = $years->where('status', 'non-aktif');

        // Fitur Term & Bobot sementara dinonaktifkan karena belum ada tabel penggantinya di skema Indo
        // $activeTerms = ...
        // $gradeWeights = ...

        return view('settings.academic', [
            'activeYear' => $activeYear,
            'archivedYears' => $archivedYears,
            // 'activeTerms' => [],
            // 'gradeWeights' => []
        ]);
    }

    public function toggleTerm(Request $request)
    {
        // Placeholder until Terms are implemented
        return response()->json(['success' => false, 'message' => 'Fitur sedang dalam perbaikan.']);
    }

    public function updateWeights(Request $request)
    {
        // Placeholder until Weights are implemented
        return back()->with('error', 'Fitur sedang dalam perbaikan.');
    }
}
