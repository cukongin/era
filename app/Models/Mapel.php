<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'mapel';
    protected $guarded = ['id'];
    protected $fillable = ['nama_mapel', 'nama_kitab', 'kode_mapel', 'kategori', 'target_jenjang'];

    public function nilai_siswa()
    {
        return $this->hasMany(NilaiSiswa::class, 'id_mapel');
    }
}
