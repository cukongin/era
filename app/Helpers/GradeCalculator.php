<?php

namespace App\Helpers;

class GradeCalculator
{
    /**
     * Calculate Final Score based on weights and fallback logic.
     * 
     * Logic requested by User:
     * "klo semisal nilai harian di kosongin atau nol disitung saman dengan nilai cawu"
     * -> If Daily (Harian) is 0/Null, use the Exam (Cawu/UAS/UTS) score as the Daily score.
     */
    public static function calculate($harian, $uts, $uas, $weights, $rounding = true)
    {
        $harianVal = $harian ?? 0;
        $utsVal = $uts ?? 0;
        $uasVal = $uas ?? 0;

        $bobotHarian = $weights['harian'] ?? 0; // e.g. 60
        $bobotUts = $weights['uts'] ?? 0;       // e.g. 20
        $bobotUas = $weights['uas'] ?? 0;       // e.g. 20
        
        $totalBobot = $bobotHarian + $bobotUts + $bobotUas;
        if ($totalBobot <= 0) return 0;

        // --- FALLBACK LOGIC ---
        // If Harian is 0 or NULL, use the Average of Exams (UTS + UAS) as Harian
        if ($bobotHarian > 0 && ($harianVal == 0)) {
            $examCount = 0;
            $examSum = 0;
            
            if ($bobotUts > 0) { $examSum += $utsVal; $examCount++; }
            if ($bobotUas > 0) { $examSum += $uasVal; $examCount++; }
            
            if ($examCount > 0) {
                $harianVal = $examSum / $examCount; // Set Daily = Exam Avg
            }
        }
        
        // Calculate Weighted Score
        // Normalize weights to percentage just in case they are integers (e.g. 60, 20, 20)
        $bH = $bobotHarian / $totalBobot;
        $bT = $bobotUts / $totalBobot;
        $bA = $bobotUas / $totalBobot;

        $finalScore = ($harianVal * $bH) + ($utsVal * $bT) + ($uasVal * $bA);

        return $rounding ? round($finalScore) : round($finalScore, 2);
    }
}
