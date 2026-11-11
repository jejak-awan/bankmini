# Status Handover & Konteks Aktif Agen (SSOT Lintas Sesi)

> **PERINGATAN UNTUK AGEN BARU**: Bacalah dokumen ini sebagai kebenaran kondisi projek saat ini (*Current State*). Jangan menebak atau mengulang fitur yang sudah ditandai selesai tanpa memeriksa implementasinya.

- **Terakhir Diperbarui**: 2026-11-12
- **Versi Projek Saat Ini**: `1.3.1`
- **Work Log Terakhir**: [`2026-11-12-prasyarat-lingkungan-dan-penguatan-gate-anotasi.md`](../04-work-log/2026-11-12-prasyarat-lingkungan-dan-penguatan-gate-anotasi.md)
- **Fase Projek Saat Ini**: **Fase 5 — Penguatan Prasyarat Lingkungan, Gate Anotasi Edukasi RPL, & Quality Gate Terpadu (Selesai)**

---

## 1. Ringkasan Konteks Wajib (Jangan Diubah Tanpa ADR Baru)
- **Domain**: Aplikasi Web Bank Mini Sekolah (Pendidikan SMK Jurusan RPL).
- **Mata Uang & Tipe**: **Rupiah (`IDR`)** dengan tipe MySQL `DECIMAL(15,2)` pada storage engine **InnoDB** dan kalkulasi `bcmath` di PHP.
- **Konvensi Bahasa**: **100% Bahasa Indonesia** (file, folder, class, method, variabel `$camelCase`, tabel/kolom `snake_case`, komentar PHPDoc `@konsep_rpl`, header View, komentar langkah `Layanan`/`Pengendali`, serta JSDoc/komentar JS & E2E).
- **Arsitektur**: PHP Native 8.2+ terstruktur (Layered MVC-Lite) + Single Entry Point `public/index.php` + Bootstrap 5 Offline (`public/aset/vendor/`) + Vanilla JS (`public/aset/js/bankmini.js`).
- **Standar Mutu Otomatis (Zero Error & Zero Warning)**: Kepatuhan & Anotasi (`periksa_kepatuhan_agen.php`), PHPCS PSR-12 (`phpcs.xml`), **PHPStan Level 8** (`phpstan.neon`), Unit/Integration strict `E_ALL` (`jalankan_semua_tes.php`), Playwright E2E (`playwright.config.js`), serta Git Commit/Push Gate (`.githooks/`, `gerbang_komit_dan_push.ps1`).

---

## 2. Komponen yang Sudah Selesai & Terverifikasi
- **Dokumen Desain, Arsitektur, DFD, ERD, Kamus Data, Glosarium, Versioning, & Prasyarat Lingkungan** (`dokumentasi/00`–`06`), termasuk Bagian 4 **Prasyarat Lingkungan & Perkakas Pengembangan** pada [`arsitektur-sistem.md`](../02-panduan-dev/arsitektur-sistem.md) serta aturan lengkap anotasi View/JS/Inline pada [`konvensi-penamaan-dan-anotasi.md`](../02-panduan-dev/konvensi-penamaan-dan-anotasi.md).
- **Basis Data & Skema SQL**: `basis_data/skema_bank_mini.sql` & `basis_data/data_awal_bank_mini.sql` (`bankmini_db` aktif dengan `PRIMARY KEY`, `FOREIGN KEY`, dan saldo `DECIMAL(15,2)`).
- **Core, Model, Layanan, Pengendali, & Pembantu**: `aplikasi/Inti/`, `aplikasi/Model/`, `aplikasi/Layanan/`, `aplikasi/Pengendali/`, dan `aplikasi/Pembantu/` telah 100% dilengkapi PHPDoc `@konsep_rpl` dan komentar langkah `// [Langkah X - Konsep RPL]: ...`.
- **Antarmuka Modern Bootstrap 5 Offline & Header Dokumentasi**: `aplikasi/Tampilan/` (sidebar navy kontras tinggi, modal konfirmasi transaksi, tabel/kartu responsif, format Rupiah live via `bankmini.js`) beserta header `<?php /** ... */ ?>` di seluruh file View.
- **Quality Gate & Handover Gate Terpadu**:
  - `periksa_kepatuhan_agen.php` (2367 titik pemeriksaan, termasuk prasyarat berkas konfigurasi QA, aset Bootstrap luring, komentar JS/E2E, header View, `@konsep_rpl`, dan komentar langkah Layanan/Pengendali).
  - `gerbang_handover_agen.php` (Entry Gate dengan pengecekan PHP `>= 8.2`, ekstensi wajib `pdo_mysql`, `bcmath`, `mbstring`, `openssl`, `tokenizer`, Composer `vendor/`, serta Playwright `node_modules/`).
  - `periksa-konsistensi.ps1` + PHPCS Zero-Warning PSR-12 + **PHPStan Level 8** + Playwright Smoke/E2E Suite (0 Console Error/Warning) + Git Commit/Push Gate (`.githooks/`, `gerbang_komit_dan_push.ps1`).

---

## 3. Tugas Aktif / Sedang Dikerjakan
- Tidak ada tugas tertunda pada Fase 5 (`v1.3.1`). Seluruh prasyarat lingkungan, gate anotasi kode, quality gate, dan dokumentasi aturan agen telah disinkronkan.

---

## 4. Langkah Selanjutnya (Prioritas Agen Berikutnya)
1. **Validasi Entry Gate**: Setiap sesi baru wajib memulai dengan menjalankan `C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\gerbang_handover_agen.php`.
2. **Pengembangan Fitur Tambahan Berikutnya (Opsional)**: Ekspor laporan mutasi ke CSV/PDF atau manajemen pengguna petugas multi-peran oleh admin (pastikan mematuhi 100% aturan QA, anotasi RPL, PHPCS, PHPStan, dan Playwright E2E).

---

## 5. Catatan Konteks Penting & Keputusan Terakhir
- **Aset Offline & Portabilitas Lab SMK**: Seluruh library UI (`bootstrap.min.css`, `bootstrap.bundle.min.js`, `bootstrap-icons`) disimpan lokal di `public/aset/vendor/` tanpa bergantung CDN eksternal.
- **Aturan Nol Toleransi Quality Gate**: Sebelum mengakhiri setiap tugas, wajib memastikan `periksa_kepatuhan_agen.php`, `phpcs`, `phpstan`, `jalankan_semua_tes.php`, dan `npm run test:e2e` lulus 100%.
- **Jalur Kredensial Uji Lokal & E2E**: Akun seed tersedia di `basis_data/data_awal_bank_mini.sql` (`admin`, `teller`, `supervisor`), dan skrip E2E Playwright menguji pada server PHP lokal `127.0.0.1:8199`.
