<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;
    
    protected $table = 'siswa';
    protected $guarded = ['id'];

    public function jenjang()
    {
        return $this->belongsTo(Jenjang::class, 'id_jenjang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function kelas_saat_ini()
    {
        // Get Active Year ID (Cached if possible, but safe here)
        $activeYearId = \App\Models\TahunAjaran::where('status', 'aktif')->value('id');

        return $this->hasOne(AnggotaKelas::class, 'id_siswa')
            ->where('status', 'aktif')
            ->whereHas('kelas', function($q) use ($activeYearId) {
                $q->where('id_tahun_ajaran', $activeYearId);
            })
            ->latest();
    }

    public function anggota_kelas()
    {
        return $this->hasMany(AnggotaKelas::class, 'id_siswa');
    }

    public function riwayat_kelas()
    {
        return $this->hasMany(AnggotaKelas::class, 'id_siswa')->orderBy('id', 'desc');
    }

    public function riwayat_absensi()
    {
        return $this->hasMany(RiwayatAbsensi::class, 'id_siswa');
    }

    public function catatan_wali_kelas()
    {
        return $this->hasMany(CatatanWaliKelas::class, 'id_siswa');
    }

    public function nilai_ekskul()
    {
        return $this->hasMany(NilaiEkstrakurikuler::class, 'id_siswa');
    }
    public function nilai_siswa()
    {
        return $this->hasMany(NilaiSiswa::class, 'id_siswa');
    }
}
