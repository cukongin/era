<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';
    protected $guarded = ['id'];

    public function wali_kelas()
    {
        return $this->belongsTo(User::class, 'id_wali_kelas');
    }

    public function jenjang()
    {
        return $this->belongsTo(Jenjang::class, 'id_jenjang');
    }
    
    public function tahun_ajaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran');
    }

    public function anggota_kelas()
    {
        return $this->hasMany(AnggotaKelas::class, 'id_kelas');
    }
    
    public function pengajar_mapel()
    {
        return $this->hasMany(PengajarMapel::class, 'id_kelas');
    }

    public function mapel_diajar()
    {
        return $this->pengajar_mapel();
    }
}
