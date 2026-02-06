<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GradingFormula;
use Illuminate\Support\Facades\DB;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use App\Models\GlobalSetting;
use App\Models\TahunAjaran;
use App\Models\BobotPenilaian;

class FormulaController extends Controller
{
    public function index()
    {
        $formulas = GradingFormula::orderBy('context')->orderBy('name')->get();
        
        // Settings for "Current Logic" Hardcode Display 
        // 1. Ijazah Weights
        $wIjazahRapor = GlobalSetting::val('ijazah_bobot_rapor', 60);
        $wIjazahUjian = GlobalSetting::val('ijazah_bobot_ujian', 40);

        // 2. Rapor Weights (Fetch from Active Year)
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        // Default values if no active year or no bobot
        $bobotMI = (object)['bobot_harian' => 50, 'bobot_uts_cawu' => 50, 'bobot_uas' => 0];
        $bobotMTS = (object)['bobot_harian' => 30, 'bobot_uts_cawu' => 30, 'bobot_uas' => 40];

        if ($activeYear) {
            $bobotMI = BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)->where('jenjang', 'MI')->first() ?? $bobotMI;
            $bobotMTS = BobotPenilaian::where('id_tahun_ajaran', $activeYear->id)->where('jenjang', 'MTS')->first() ?? $bobotMTS;
        }
        
        return view('settings.formula', compact('formulas', 'wIjazahRapor', 'wIjazahUjian', 'bobotMI', 'bobotMTS'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'context' => 'required|string',
            'formula' => 'required|string',
        ]);

        // Validate Syntax
        if (!$this->validateFormula($request->formula, $this->getVariables($request->context))) {
            return back()->with('error', 'Formula tidak valid! Periksa syntax matematis.');
        }

        GradingFormula::create($request->all());

        return back()->with('success', 'Rumus berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $formula = GradingFormula::findOrFail($id);
        
        if ($request->has('is_active')) {
            // Deactivate others in same context first
            GradingFormula::where('context', $formula->context)->update(['is_active' => false]);
            $formula->update(['is_active' => true]);
            
            return back()->with('success', 'Rumus berhasil diaktifkan.');
        }
        
        $request->validate([
            'name' => 'required|string',
            'formula' => 'required|string',
        ]);

        // Validate Syntax
        if (!$this->validateFormula($request->formula, $this->getVariables($formula->context))) {
             return back()->with('error', 'Formula tidak valid! Periksa syntax matematis.');
        }

        $formula->update($request->only(['name', 'formula', 'description']));

        return back()->with('success', 'Rumus berhasil diupdate.');
    }

    public function destroy($id)
    {
        GradingFormula::findOrFail($id)->delete();
        return back()->with('success', 'Rumus dihapus.');
    }

    public function simulate(Request $request) 
    {
        // AJAX Simuator
        $formula = $request->formula;
        $context = $request->context;
        $inputs = $request->inputs; // array of values

        try {
            $language = new ExpressionLanguage();
            // Process inputs to numbers
            $values = [];
            foreach ($inputs as $k => $v) $values[$k] = floatval($v);
            
            $result = $language->evaluate($formula, $values);
            return response()->json(['success' => true, 'result' => round($result, 2)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function restoreDefault(Request $request) 
    {
        $context = $request->input('context');
        if ($context) {
             GradingFormula::where('context', $context)->update(['is_active' => false]);
             return back()->with('success', "Logika Custom untuk $context dinonaktifkan. Sistem kembali ke mode Bawaan (Hardcode).");
        }
        return back();
    }

    private function validateFormula($formula, $variables)
    {
        try {
            $language = new ExpressionLanguage();
            // Create dummy values for all variables
            $values = array_fill_keys($variables, 10); 
            $language->evaluate($formula, $values);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getVariables($context)
    {
        if (str_contains($context, 'rapor')) {
            // Include Annual Variables
            return ['Rata_PH', 'Nilai_PAS', 'Nilai_PTS', 'Kehadiran', 'Nilai_Cawu_1', 'Nilai_Cawu_2', 'Nilai_Cawu_3', 'Nilai_Sem_1', 'Nilai_Sem_2'];
        }
        if (str_contains($context, 'ijazah')) {
            return ['Rata_Rapor_MI', 'Rata_Rapor_MTS', 'Nilai_Ujian'];
        }
        return [];
    }
}
