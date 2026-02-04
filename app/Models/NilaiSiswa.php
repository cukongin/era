<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiSiswa extends Model
{
    use HasFactory;

    protected $table = 'nilai_siswa';
    protected $guarded = ['id']; // Allow mass assignment for status

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
    
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel');
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatPerubahanNilai::class, 'id_nilai_siswa')->orderBy('created_at', 'desc');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'id_periode');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }
}
