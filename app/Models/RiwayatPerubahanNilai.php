<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPerubahanNilai extends Model
{
    protected $table = 'riwayat_perubahan_nilai';
    public $timestamps = false; // Only created_at
    protected $guarded = ['id'];
    
    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
