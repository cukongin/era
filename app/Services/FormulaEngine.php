<?php

namespace App\Services;

class FormulaEngine
{
    /**
     * Calculate the result of a formula given a dictionary of variables.
     *
     * @param string $formula e.g. "([Rata_PH] * 0.6) + ([Nilai_PAS] * 0.4)"
     * @param array $variables e.g. ['[Rata_PH]' => 80, '[Nilai_PAS]' => 90]
     * @return float
     */
    public static function calculate($formula, $variables = [])
    {
        if (empty($formula)) return 0;

        // 1. Replace Variables
        // Sort keys by length desc to avoid partial replacement issues
        uksort($variables, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        $parsed = $formula;
        foreach ($variables as $key => $value) {
            // Ensure value is numeric, default to 0
            $val = is_numeric($value) ? $value : 0;
            $parsed = str_replace($key, $val, $parsed);
        }

        // 2. Handle Missing Variables (remaining [...])
        // If there are still square brackets, it means some variables weren't provided. 
        // We replace them with 0 to prevent syntax errors.
        $parsed = preg_replace('/\[.*?\]/', '0', $parsed);

        // 3. Sanitize
        // Only allow numbers, dot, operators, parentheses, and spaces
        $sanitized = preg_replace('/[^0-9\.\+\-\*\/\(\)\s]/', '', $parsed);
        
        // Safety check: if empty after sanitization
        if (trim($sanitized) === '') return 0;

        // 4. Evaluate
        try {
            $result = 0;
            @eval('$result = ' . $sanitized . ';');
            return (float) $result;
        } catch (\Throwable $e) {
            \Log::error("FormulaEngine Error: " . $e->getMessage() . " | Formula: $formula");
            return 0;
        }
    }
}
