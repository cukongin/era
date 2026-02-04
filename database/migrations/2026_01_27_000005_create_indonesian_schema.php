<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndonesianSchema extends Migration
{
    public function up()
    {
        // 1. Referensi: Tahun Ajaran
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 20); // 2024/2025
            $table->enum('status', ['aktif', 'non-aktif'])->default('non-aktif');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });

        // 2. Referensi: Jenjang (MI/MTs)
        Schema::create('jenjang', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique(); // MI, MTS
            $table->string('nama', 50); // Madrasah Ibtidaiyah
            $table->timestamps();
        });

        // 3. Referensi: Mapel
        Schema::create('mapel', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mapel');
            $table->string('kode_mapel', 20)->nullable();
            $table->enum('kategori', ['UMUM', 'AGAMA', 'MULOK'])->default('UMUM');
            $table->timestamps();
        });

        // 4. Data Guru (Detail Profile)
        Schema::create('data_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->string('nip', 30)->nullable();
            $table->string('nuptk', 30)->nullable();
            $table->string('jenis_kelamin', 1)->nullable(); // L/P
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        // 5. Data Siswa (Buku Induk)
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            
            // Identitas
            $table->string('nis_lokal', 20)->unique()->nullable();
            $table->string('nisn', 20)->unique()->nullable();
            $table->string('nik', 20)->nullable();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama')->default('Islam');
            
            // Alamat
            $table->text('alamat_lengkap')->nullable();
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kota')->nullable();
            
            // Ortu
            $table->string('nama_ayah')->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->string('no_telp_ortu')->nullable();
            
            // Akademik
            $table->foreignId('id_jenjang')->nullable()->constrained('jenjang');
            $table->year('tahun_masuk')->nullable();
            $table->string('sekolah_asal')->nullable();
            
            // Status: aktif, lulus, mutasi, keluar, non-aktif
            $table->enum('status_siswa', ['aktif', 'lulus', 'mutasi', 'keluar', 'non-aktif'])->default('aktif');
            
            $table->timestamps();
        });

        // 6. Kelas (Rombel)
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajaran');
            $table->foreignId('id_jenjang')->constrained('jenjang');
            $table->string('nama_kelas', 20); // 1-A, 7-B
            $table->integer('tingkat_kelas'); // 1, 2, ... 9
            $table->foreignId('id_wali_kelas')->nullable()->constrained('users'); // Link ke User Guru
            $table->timestamps();
        });

        // 7. Anggota Kelas (Pivot Siswa-Kelas)
        Schema::create('anggota_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade');
            $table->enum('status', ['aktif', 'pindah', 'naik_kelas'])->default('aktif');
            $table->timestamps();
        });

        // 8. Pengajar Mapel (Jadwal/Assignment)
        Schema::create('pengajar_mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('id_mapel')->constrained('mapel')->onDelete('cascade');
            $table->foreignId('id_guru')->nullable()->constrained('users'); // Guru Pengampu
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengajar_mapel');
        Schema::dropIfExists('anggota_kelas');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('data_guru');
        Schema::dropIfExists('mapel');
        Schema::dropIfExists('jenjang');
        Schema::dropIfExists('tahun_ajaran');
    }
}
