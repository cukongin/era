# Konsep Halaman Tata Usaha (TU) & DKN Ijazah

## A. Halaman Khusus TU (Tata Usaha)
**Fungsi Utama**: Monitoring global dan Administrasi Kelulusan.

### 1. Hak Akses (Role)
*Opsi:*
*   **A. Gunakan Role Admin**: Fitur ini ditempel di dashboard Admin yang sekarang.
*   **B. Role Baru 'staff_tu'**: Login khusus, beda dengan kepala sekolah/admin teknis.

### 2. Fitur: Rekap Nilai Tahunan (Semua Kelas)
*   **Tampilan**: Dashboard ringkas berisi daftar semua kelas.
*   **Aksi**: Klik kelas -> Muncul Leger Rekap (seperti yang ada di Wali Kelas, tapi *Read Only*).
*   **Export**: Bisa "Download Semua Leger" dalam satu kali klik (Batch Zip/Excel).

---

## B. DKN Ijazah (Daftar Kumpulan Nilai)
**Target**: Hanya untuk Kelas Akhir (Kelas 6 MI & Kelas 9 MTs).

### 1. Komponen Nilai DKN
Format standar DKN biasanya terdiri dari:
1.  **Rata-rata Rapor (RR)**:
    *   MI: Rata-rata semeseter 7, 8, 9, 10, 11 (Kelas 4 SmT 1 s.d. Kelas 6 Smt 1).
    *   MTs: Rata-rata semester 1, 2, 3, 4, 5 (Kelas 7 Smt 1 s.d. Kelas 9 Smt 1).
2.  **Nilai Ujian Madrasah (UM)**: Nilai tes kelulusan.
3.  **Nilai Akhir Jenjang (NA)**: Formula (misal: `60% RR + 40% UM`).

### 2. Masalah Data (Transisi Sistem Baru)
Karena sistem baru dipakai, **Data Nilai Kelas 4 & 5 belum ada** di database.
*   **Solusi Jangka Pendek (Tahun Ini)**:
    *   Dibuatkan menu **"Input Nilai Ijazah Manual"**.
    *   TU menginput langsung **Nilai Rata-Rapor** sekolah sebelumnya.
    *   Nilai Ujian (UM) diinput manual juga.
*   **Solusi Jangka Panjang**:
    *   Tahun depan, Rata-rata Rapor ditarik otomatis dari history sistem.

### 3. Output / Luaran
*   **Cetak SKL**: PDF Surat Keterangan Lulus (Format Resmi Kop Sekolah).
*   **Cetak DKN**: Tabel landscape untuk arsip/dikirim ke Kemenag/Diknas.
*   **Label Amplop / Ijazah**: Membantu penulisan ijazah.

---

## Poin Diskusi
1.  **Login**: Apakah perlu bikin akun login khusus `tu`? Atau pakai admin saja cukup?
2.  **Data Ijazah**: Setuju kah tahun ini kita pakai metode **Input Manual** dulu untuk DKN? (Karena history kelas 4-5 kosong).
