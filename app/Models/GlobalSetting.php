<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'description'];

    /**
     * Get setting value by key, or default
     */
    public static function val($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        // Return default if setting not found OR value is null/empty
        return ($setting && !is_null($setting->value) && $setting->value !== '') ? $setting->value : $default;
    }
}
