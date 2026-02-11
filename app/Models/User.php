<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Added role
        'access_code', // Login Code from Google Sheet
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function data_guru()
    {
        return $this->hasOne(DataGuru::class, 'id_user');
    }
    public function kelas_wali()
    {
        return $this->hasOne(Kelas::class, 'id_wali_kelas');
    }

    public function wali_kelas_aktif()
    {
        // Static lookup to avoid query inside relation definition if possible? 
        // No, we can subquery or join. But simplest is using `whereHas`.
        // However, for eager loading `with('wali_kelas_aktif')`, we need a direct relation.
        // We can use a subquery for the active year ID, or fetch it.
        // Since `AppServiceProvider` runs early, we could potentially share it, but let's do a raw query or simple fetch.
        
        // Note: This relies on TahunAjaran having only ONE 'aktif' row.
        return $this->hasOne(Kelas::class, 'id_wali_kelas')
            ->whereHas('tahun_ajaran', function($q) {
                $q->where('status', 'aktif');
            });
    }

    public function data_siswa()
    {
        return $this->hasOne(Siswa::class, 'id_user');
    }

    public function mapel_ajar()
    {
        return $this->hasMany(PengajarMapel::class, 'id_guru');
    }

    /**
     * Check user role
     */
    public function hasRole($role)
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTu()
    {
        return $this->role === 'staff_tu';
    }

    public function isStaffTu()
    {
        return $this->isTu();
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if user is assigned as Wali Kelas
     */
    public function isWaliKelas()
    {
        // Must be in Active Year
        // Ideally we cache the active year ID to avoid N+1, but for checking permission this is safer.
        $activeYearId = \App\Models\TahunAjaran::where('status', 'aktif')->value('id');
        
        if (!$activeYearId) return false;

        return $this->kelas_wali()->where('id_tahun_ajaran', $activeYearId)->exists();
    }


    public function isStudent()
    {
        return $this->role === 'student';
    }
}
