<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingFormula extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Helper to get active formula for a context
    public static function active($context)
    {
        return self::where('context', $context)->where('is_active', true)->first();
    }
}
