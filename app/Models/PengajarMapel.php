<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajarMapel extends Model
{
    use HasFactory;

    protected $table = 'pengajar_mapel';
    protected $guarded = ['id'];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel');
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'id_guru');
    }
    
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }
}
