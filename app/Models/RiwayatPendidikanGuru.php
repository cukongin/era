<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPendidikanGuru extends Model
{
    use HasFactory;

    protected $table = 'riwayat_pendidikan_guru';
    protected $guarded = ['id'];

    public function guru()
    {
        return $this->belongsTo(DataGuru::class, 'data_guru_id');
    }
}
