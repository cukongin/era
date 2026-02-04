<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'margins' => 'array',
        'is_active' => 'boolean',
    ];

    // Helper to get active template
    public static function getActive($type = 'rapor')
    {
        return self::where('type', $type)->where('is_active', true)->first();
    }
}
