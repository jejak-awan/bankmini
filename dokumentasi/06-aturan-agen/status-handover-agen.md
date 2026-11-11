# Status Handover & Konteks Aktif Agen (SSOT Lintas Sesi)

- **Pembaruan Terakhir**: 2026-11-11
- **Versi Aplikasi Saat Ini**: `1.1.1` (SoT di `VERSION` & `CHANGELOG.md`)
- **Work Log Terakhir**: [`2026-11-11-glosarium-dan-daftar-istilah-rpl.md`](../04-work-log/2026-11-11-glosarium-dan-daftar-istilah-rpl.md)
- **Fase Projek Saat Ini**: Fase 1 & 2 Selesai (v1.1.1) — Aplikasi `bankmini` + Bahan Belajar RPL Lengkap (`@konsep_rpl`, DFD, ERD, Kamus Data, Glosarium Istilah) + Manajemen Versi & Suite QA
- **Status Kepatuhan & QA**: LULUS 100% (0 Pelanggaran, 14 Skenario QA Lulus)

---

## 1. Ringkasan Konteks Wajib (Jangan Diubah Tanpa ADR Baru)
- **Bahasa, Konvensi, & Edukasi RPL**: 100% Bahasa Indonesia (PSR-12: Class `PascalCase`, method/variabel `camelCase`, tabel/kolom `snake_case`, wajib PHPDoc Bahasa Indonesia + tag `@konsep_rpl` di setiap class `aplikasi/` untuk pembelajaran siswa RPL).
- **Arsitektur SoT & SoC**: `public/index.php` -> `Pengendali` -> `Layanan` (Batas Transaksi ACID) -> `Model` (PDO Prepared Statements) -> `Tampilan` (`amankanHtml()`, `formatRupiah()`).
- **Standar Keuangan**: Mata uang tunggal **Rupiah (`IDR`)** dengan tipe MySQL InnoDB `DECIMAL(15,2)`, penguncian baris `SELECT ... FOR UPDATE`, dan `mutasi_transaksi` bersifat *Append-Only* (koreksi hanya melalui `LayananTransaksi::prosesJurnalKoreksi()`).
- **Tata Kelola Versi & Dokumentasi**: Setiap selesai mengerjakan tugas, agen **wajib** memperbarui `01-panduan-pengguna/`, `02-panduan-dev/`, menaikkan versi (`naikkan_versi.php` / `VERSION` & `CHANGELOG.md`), serta mencatat di `04-work-log/`.

---

## 2. Komponen yang Sudah Selesai & Terverifikasi
- [x] **Pondasi Dokumentasi, 6 ADR, & Bahan Belajar RPL** (`dfd-dan-alur-sistem.md`, `skema-database.md`, `manajemen-versi-lisensi-dan-changelog.md`, `glosarium-dan-daftar-istilah.md`)
- [x] **Manajemen Versi & Lisensi** (`VERSION` = `1.1.1`, `CHANGELOG.md`, `LICENSE` MIT, `naikkan_versi.php`)
- [x] **Skrip Pemeriksa Kepatuhan & Gate Handover** (`06-aturan-agen/skrip/periksa_kepatuhan_agen.php`, `gerbang_handover_agen.php`, `periksa-konsistensi.ps1`)
- [x] **Skema, Seeder, & Pemasang Database MySQL InnoDB Rupiah IDR** (`basis_data/skema.sql`, `basis_data/data_awal.sql`, `basis_data/pasang_basis_data.php` — sudah terpasang aktif di `bankmini_db`)
- [x] **Seluruh Kode Inti, Model, Layanan, Pengendali, & Tampilan** lengkap dengan komentar edukasi `@konsep_rpl` untuk siswa RPL
- [x] **Suite Pengujian Otomatis QA (`pengujian/`)**: 14 skenario lulus 100%

---

## 3. Tugas Aktif / Sedang Dikerjakan
- [ ] Tidak ada tugas yang menggantung (seluruh *deliverable* v1.1.1 selesai 100%).

---

## 4. Langkah Selanjutnya (Prioritas Agen Berikutnya)
1. **Portal Khusus Nasabah (`PengendaliPortalNasabah`)**: Menambahkan halaman login nasabah untuk melihat saldo dan riwayat mutasi rekening pribadinya.
2. **Manajemen Pengguna & Produk oleh Admin**: Menambahkan fitur tambah/ubah status pengguna dan produk tabungan melalui antarmuka Admin.
3. **Cetak Slip Bukti Transaksi Individual**: Menambahkan halaman cetak slip bukti per transaksi (`kode_referensi`).

---

## 5. Catatan Konteks Penting & Keputusan Terakhir
- Setiap class baru di `aplikasi/` **wajib** memiliki tag `@konsep_rpl` pada PHPDoc-nya (diperiksa otomatis oleh `periksa_kepatuhan_agen.php`).
- Setiap agen baru **wajib** menjalankan `gerbang_handover_agen.php` di awal sesi (Entry Gate) dan memperbarui `status-handover-agen.md`, `01-panduan-pengguna/`, `02-panduan-dev/`, `VERSION`/`CHANGELOG.md`, serta menjalankan `periksa_kepatuhan_agen.php` di akhir sesi (Exit Gate).
