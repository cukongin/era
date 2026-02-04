<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatAbsensi extends Model
{
    protected $table = 'riwayat_absensi';
    protected $guarded = ['id'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'id_periode');
    }
}
