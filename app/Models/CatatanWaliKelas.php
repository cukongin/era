<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanWaliKelas extends Model
{
    protected $table = 'catatan_wali_kelas';
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
