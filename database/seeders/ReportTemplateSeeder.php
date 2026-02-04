<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;

class ReportTemplateSeeder extends Seeder
{
    public function run()
    {
        $defaultContent = '
<!-- Header Section (Logo + Title) -->
<table style="width: 100%; border: none; margin-bottom: 20px;">
    <tr>
        <td style="width: 100%; text-align: center;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Logo_Kementerian_Agama_Pengasuh.png/586px-Logo_Kementerian_Agama_Pengasuh.png" style="height: 60px; width: auto;" alt="Logo Kemenag">
            <h3 style="font-size: 16px; font-weight: bold; text-transform: uppercase; margin-top: 10px;">Laporan Hasil Belajar (Rapor)</h3>
        </td>
    </tr>
</table>

<!-- Identity Section -->
<table style="width: 100%; border-collapse: collapse; border: none; font-size: 12px; margin-bottom: 20px;">
    <tbody>
        <tr>
            <td style="width: 15%; font-weight: 600;">Nama</td>
            <td style="width: 2%;">:</td>
            <td style="width: 33%; text-transform: uppercase; font-weight: 500;">[[NAMA_SISWA]]</td>
            <td style="width: 15%; font-weight: 600;">Kelas</td>
            <td style="width: 2%;">:</td>
            <td style="width: 33%;">[[KELAS]]</td>
        </tr>
        <tr>
            <td style="font-weight: 600;">Nomor Induk</td>
            <td>:</td>
            <td>[[NIS]]</td>
            <td style="font-weight: 600;">Tahun Pelajaran</td>
            <td>:</td>
            <td>[[TAHUN_AJARAN]]</td>
        </tr>
        <tr>
            <td style="font-weight: 600;">Nama MDT.</td>
            <td>:</td>
            <td>[[NAMA_SEKOLAH]]</td>
            <td style="font-weight: 600;">Alamat</td>
            <td>:</td>
            <td>Jl. KH. A. Jazuli, Sumber</td>
        </tr>
    </tbody>
</table>

<!-- A. Academic Table -->
<div style="margin-bottom: 10px;">
    <h4 style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">A. Pengetahuan dan Keterampilan</h4>
    [[TABEL_NILAI]]
</div>

<!-- B. Ekstrakurikuler -->
<div style="margin-bottom: 10px;">
    <h4 style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">B. Ekstrakurikuler</h4>
    [[TABEL_EKSKUL]]
</div>

<!-- C. Prestasi -->
<div style="margin-bottom: 10px;">
    <h4 style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">C. Prestasi</h4>
    [[TABEL_PRESTASI]]
</div>

<!-- D & E Container (Kepribadian & Ketidakhadiran) -->
<table style="width: 100%; border-collapse: collapse; border: none; font-size: 12px; margin-bottom: 10px;">
    <tr>
        <td style="width: 49%; vertical-align: top; padding-right: 5px;">
            <h4 style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">D. Kepribadian</h4>
            [[TABEL_KEPRIBADIAN]]
        </td>
        <td style="width: 2%;">&nbsp;</td>
        <td style="width: 49%; vertical-align: top; padding-left: 5px;">
            <h4 style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">E. Ketidakhadiran</h4>
            [[TABEL_KETIDAKHADIRAN]]
        </td>
    </tr>
</table>

<!-- F. Catatan Wali Kelas -->
<div style="margin-bottom: 20px;">
    <h4 style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">F. Catatan Wali Kelas (Akhir Tahun)</h4>
    <div style="border: 1px solid #000; padding: 10px; min-height: 40px; font-size: 12px; font-style: italic;">
        "[[CATATAN_WALI]]"
    </div>
    
    <!-- Status Kenaikan -->
    <div style="border: 1px solid #000; border-top: none; padding: 10px; background-color: #f9fafb; text-align: center;">
        <p style="font-size: 12px; margin-bottom: 5px;">Berdasarkan hasil penilaian, Peserta Didik dinyatakan:</p>
        <p style="font-weight: bold; font-size: 14px; text-transform: uppercase;">[[STATUS_KENAIKAN]]</p>
    </div>
</div>

<!-- Signatures -->
<div style="text-align: right; font-size: 12px; margin-bottom: 5px;">
    <p>Bangkalan, [[TANGGAL_RAPOR]]</p>
</div>

<table style="width: 100%; border: none; font-size: 12px;">
    <tbody>
        <tr>
            <td style="width: 30%; text-align: center; vertical-align: top;">
                <p style="margin-bottom: 80px;">Orang Tua / Wali</p>
                <p style="font-weight: bold;">..........................</p>
            </td>
            <td style="width: 40%; text-align: center; vertical-align: top;">
                <p style="margin-bottom: 0;">Mengetahui,</p>
                <p style="margin-bottom: 65px;">Kepala Madrasah</p>
                <p style="font-weight: bold; text-transform: uppercase;">[[KEPALA_SEKOLAH]]</p>
            </td>
            <td style="width: 30%; text-align: center; vertical-align: top;">
                <p style="margin-bottom: 80px;">Wali Kelas</p>
                <p style="font-weight: bold; text-transform: uppercase;">[[WALI_KELAS]]</p>
            </td>
        </tr>
    </tbody>
</table>
';

        ReportTemplate::create([
            'name' => 'Rapor Standar (Bawaan Sistem)',
            'type' => 'rapor',
            'content' => $defaultContent,
            'margins' => ['top' => 10, 'right' => 10, 'bottom' => 10, 'left' => 10],
            'is_active' => true,
            'is_locked' => true, // PERMANENT / NON-EDITABLE
        ]);
        
        // Also ensure Cover template exists if needed (Optional)
    }
}
