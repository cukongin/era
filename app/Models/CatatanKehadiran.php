<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanKehadiran extends Model
{
    protected $table = 'catatan_kehadiran';
    protected $guarded = ['id'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
}
