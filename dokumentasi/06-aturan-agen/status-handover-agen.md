# Status Handover & Konteks Aktif Agen (SSOT Lintas Sesi)

> **PERINGATAN UNTUK AGEN BARU**: Bacalah dokumen ini sebagai kebenaran kondisi projek saat ini (*Current State*). Jangan menebak atau mengulang fitur yang sudah ditandai selesai tanpa memeriksa implementasinya.

- **Terakhir Diperbarui**: 2026-11-12
- **Versi Projek Saat Ini**: `1.6.0`
- **Work Log Terakhir**: [`2026-11-12-dependensi-dan-layanan-dokumen-pdf-excel-word.md`](../04-work-log/2026-11-12-dependensi-dan-layanan-dokumen-pdf-excel-word.md)
- **Fase Projek Saat Ini**: **Fase 8 — Dependensi & Layanan Adapter Ekspor/Impor/Cetak Dokumen PDF, Excel, & Word (Selesai)**

---

## 1. Ringkasan Konteks Wajib (Jangan Diubah Tanpa ADR Baru)
- **Domain**: Aplikasi Web Bank Mini Sekolah (Pendidikan SMK Jurusan RPL).
- **Mata Uang & Tipe**: **Rupiah (`IDR`)** dengan tipe MySQL `DECIMAL(15,2)` pada storage engine **InnoDB** dan kalkulasi `bcmath` (`keStringNumerik()`) di PHP.
- **Konvensi Bahasa**: **100% Bahasa Indonesia** (file, folder, class, method, variabel `$camelCase`, tabel/kolom `snake_case`, komentar PHPDoc `@konsep_rpl`, header View, komentar langkah `Layanan`/`Pengendali`, serta JSDoc/komentar JS & E2E).
- **Arsitektur**: PHP Native 8.2+ terstruktur (Layered MVC-Lite) + Single Entry Point `public/index.php` + Bootstrap 5 Offline (`public/aset/vendor/`) + Vanilla JS (`public/aset/js/aplikasi.js`).
- **Standar Mutu Otomatis & CI/CD Lokal (`0 Error & 0 Warning`)**: Kepatuhan & Anotasi (`periksa_kepatuhan_agen.php`), PHPCS PSR-12 Zero-Warning (`phpcs.xml`), **PHPStan Level 8** (`phpstan.neon`), Unit/Integration strict `E_ALL` (`jalankan_semua_tes.php`), Playwright E2E (`playwright.config.js`), Git Commit/Push Gate (`.githooks/`, `gerbang_komit_dan_push.ps1`), serta Pipeline CI/CD Lokal Docker/Native (`jalankan-ci-lokal.ps1`, `Dockerfile`, `docker-compose.ci.yml`).

---

## 2. Komponen yang Sudah Selesai & Terverifikasi
- **Pustaka & Layanan Ekspor/Impor/Cetak Dokumen (`LayananDokumenKantor.php`)**:
  - Dependensi Composer terpasang penuh: `dompdf/dompdf` (`^3.1`), `phpoffice/phpspreadsheet` (`^5.11`), `shuchkin/simplexlsxgen` (`^1.5`), `shuchkin/simplexlsx` (`^1.1`), dan `phpoffice/phpword` (`^1.4`).
  - `LayananDokumenKantor.php` menyediakan fondasi pembuatan PDF (`buatPdfDariHtml`), ekspor & impor Excel `.xlsx`/`.csv` (`buatExcelDariTabel`, `bacaDataDariExcel`, `buatCsvDariTabel`, `bacaDataDariCsv`), pembuatan dokumen Word `.docx` (`buatDokumenWord` dengan fallback `PCLZip`), serta template HTML siap cetak (`siapkanHtmlCetak`), diuji oleh `TesLayananDokumenKantor.php` (`QA-UNIT-006` s.d. `QA-UNIT-008`).
- **Modul Pusat Dokumentasi & Materi RPL di Browser (`/dokumentasi`)**:
  - `LayananDokumentasi.php` (pengindeks otomatis folder `dokumentasi/`, konverter Markdown ke HTML Bootstrap 5, daftar isi otomatis *On This Page*, pencarian kata kunci penuh, serta proteksi keamanan *Anti-Path Traversal* `realpath()`).
  - `PengendaliDokumentasi.php`, tampilan 3-kolom `aplikasi/Tampilan/dokumentasi/indeks.php`, menu **Pusat Dokumentasi** di sidebar, serta pengujian integrasi `TesLayananDokumentasi.php` + smoke test Playwright `/dokumentasi`.
- **Dokumen Desain, Arsitektur, DFD, ERD, Kamus Data, Glosarium, Versioning, Prasyarat Lingkungan, Modul Praktikum RPL, & CI/CD Lokal** (`dokumentasi/00`–`06`).
- **Basis Data, Konfigurasi Fleksibel (`getenv`), & Skrip Onboarding**: `basis_data/skema.sql`, `basis_data/data_awal.sql`, `konfigurasi/basis_data.php`, `konfigurasi/aplikasi.php`, `.env.example`, `.editorconfig`, `.vscode/`, dan `siapkan-projek.ps1`.
- **Quality Gate, Handover Gate, & Local CI/CD Pipeline (`0 Error & 0 Warning`)**:
  - `periksa_kepatuhan_agen.php` (3011 titik pemeriksaan — 0 Pelanggaran).
  - `gerbang_handover_agen.php` (Entry Gate dengan pengecekan runtime PHP, ekstensi wajib, Composer, & Playwright).
  - `jalankan-ci-lokal.ps1` & `periksa-konsistensi.ps1` + PHPCS Zero-Warning PSR-12 (35/35 file) + **PHPStan Level 8** (35/35 file) + Unit/Integration QA (17/17 skenario) + Playwright Smoke/E2E Suite (0 Console Error/Warning).

---

## 3. Tugas Aktif / Sedang Dikerjakan
- Tidak ada tugas tertunda pada Fase 8 (`v1.6.0`). Seluruh dependensi dan layanan fondasi ekspor, impor, serta cetak dokumen (PDF, Excel, Word, CSV, & Print HTML) telah siap digunakan oleh fitur aplikasi.

---

## 4. Langkah Selanjutnya (Prioritas Agen Berikutnya)
1. **Validasi Entry Gate**: Setiap sesi baru wajib memulai dengan menjalankan `C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\gerbang_handover_agen.php`.
2. **Implementasi Latihan Praktikum / Fitur Lanjutan (Opsional)**: Mengerjakan salah satu tantangan pada Bagian 4 [`alur-belajar-dan-modul-praktikum-rpl.md`](../02-panduan-dev/alur-belajar-dan-modul-praktikum-rpl.md) dengan tetap menjaga 100% kelulusan `jalankan-ci-lokal.ps1`.

---

## 5. Catatan Konteks Penting & Keputusan Terakhir
- **Single Source of Truth Dokumentasi Web**: Modul `/dokumentasi` membaca langsung berkas `.md` di folder `dokumentasi/` tanpa menduplikasi file HTML statis.
- **Aturan Nol Toleransi Quality Gate**: Sebelum melakukan `git commit` atau `git push`, wajib memastikan `periksa_kepatuhan_agen.php`, `phpcs` (0 warning), `phpstan` (Level 8, 0 error), `jalankan_semua_tes.php` (`E_ALL`), dan `npm run test:e2e` lulus 100%.
