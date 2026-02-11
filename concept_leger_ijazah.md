# Konsep Pengembangan Leger & Ijazah

## 1. Leger Rekap Tahunan (Semua Semester/Cawu)
Fitur ini bertujuan untuk melihat akumulasi nilai siswa dalam **satu Tahun Ajaran**.

### Konsep Data
- **Sumber Data**: Mengambil nilai dari tabel `nilai_siswa` untuk semua periode (Cawu 1, 2, 3) dalam `id_tahun_ajaran` yang sama.
- **Tampilan Tabel**:
  - Baris: Nama Siswa
  - Kolom Utama: Mata Pelajaran
  - Sub-Kolom: Nilai C1 | Nilai C2 | Nilai C3 | **Rata-rata Tahunan**
- **Logika Kenaikan Kelas**:
  - Rata-rata Tahunan ini yang biasanya menjadi dasar "Naik Kelas" atau tidak.
  - Ditambah pertimbangan Nilai Akhlak/Kepribadian (akumulasi dari 3 cawu).

### Implementasi Teknis
- Tidak perlu mengubah struktur database.
- Hanya membuat *View* baru (`leger-rekap.blade.php`) dan *Controller Logic* yang melakukan *looping* semua periode.

---

## 2. Leger Nilai Ijazah (Akumulasi Tingkat Akhir)
Fitur ini lebih kompleks karena menggabungkan data dari **beberapa tahun ajaran** (misal Kelas 4, 5, 6 untuk MI, atau Kelas 7, 8, 9 untuk MTs).

### Tantangan Utama: Data Historis
Untuk membuat Leger Ijazah otomatis, sistem membutuhkan data nilai dari tahun-tahun sebelumnya.
- **Masalah**: Jika aplikasi baru dipakai tahun ini (misal di Kelas 6), maka data nilai Kelas 4 dan 5 **belum ada** di database.
- **Solusi**:
  1.  **Fitur Backdate Input**: Admin/Wali Kelas menginput nilai rapor lama (repot tapi lengkap).
  2.  **Fitur Input Nilai Ijazah Langsung**: Langsung input "Nilai Rata-rata Rapor" (tanpa detail per mapel/semester) + "Nilai Ujian Madrasah" (UM).

### Rumus Kelulusan (Contoh Umum Kemenag)
Nilai Ijazah biasanya dihitung dengan rumus bobot, contoh:
$$ \text{Nilai Kelulusan} = (Rata2 Rapor \times 60\%) + (Nilai Ujian Madrasah \times 40\%) $$

*Catatan: Rata2 Rapor biasanya diambil dari 6 semester terakhir.*

### Desain Implementasi
1.  **Menu "Asesmen Sumatif Akhir" (Ujian Madrasah)**:
    - Input nilai ujian khusus siswa tingkat akhir.
2.  **Menu "Leger Ijazah"**:
    - **Opsi A (Otomatis)**: Menarik data rapor semester 1-5 (jika ada datanya) + Nilai Ujian.
    - **Opsi B (Manual)**: Form input untuk nilai rata-rata rapor (jika data lama tidak ada).
3.  **Cetak SKL & Ijazah**:
    - Output berupa PDF sesuai blangko resmi.

---

## Pertanyaan Diskusi (Untuk Boss)
1.  **Apakah data tahun lalu (Kelas 1-5) akan diimpor juga ke sistem ini?** Atau kita mulai dari nol dan untuk Ijazah tahun ini pakai "Input Manual Rata-rata" saja?
2.  **Sistem Periode**: Apakah Bapak mau Leger Biasa bisa "Pindah Periode" (lihat data lama) tanpa harus mengubah "Tahun Aktif" di menu Admin? (Supaya Wali Kelas bisa cek nilai Cawu 1 saat sedang di Cawu 3).
