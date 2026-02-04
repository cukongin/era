<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($action, $target = null, $details = null)
    {
        return self::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user() ? auth()->user()->name : 'Guest',
            'action' => $action,
            'target' => $target,
            'details' => is_array($details) ? json_encode($details) : $details,
            'ip_address' => request()->ip()
        ]);
    }
}
