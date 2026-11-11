# Status Handover & Konteks Aktif Agen (SSOT Lintas Sesi)

> **PERINGATAN UNTUK AGEN BARU**: Bacalah dokumen ini sebagai kebenaran kondisi projek saat ini (*Current State*). Jangan menebak atau mengulang fitur yang sudah ditandai selesai tanpa memeriksa implementasinya.

- **Terakhir Diperbarui**: 2026-11-12
- **Versi Projek Saat Ini**: `1.4.0`
- **Work Log Terakhir**: [`2026-11-12-modul-praktikum-rpl-dan-ci-cd-lokal-docker.md`](../04-work-log/2026-11-12-modul-praktikum-rpl-dan-ci-cd-lokal-docker.md)
- **Fase Projek Saat Ini**: **Fase 6 — Paket Pembelajaran & Praktikum Siswa RPL + Pipeline CI/CD Lokal Docker/Native (Selesai)**

---

## 1. Ringkasan Konteks Wajib (Jangan Diubah Tanpa ADR Baru)
- **Domain**: Aplikasi Web Bank Mini Sekolah (Pendidikan SMK Jurusan RPL).
- **Mata Uang & Tipe**: **Rupiah (`IDR`)** dengan tipe MySQL `DECIMAL(15,2)` pada storage engine **InnoDB** dan kalkulasi `bcmath` (`keStringNumerik()`) di PHP.
- **Konvensi Bahasa**: **100% Bahasa Indonesia** (file, folder, class, method, variabel `$camelCase`, tabel/kolom `snake_case`, komentar PHPDoc `@konsep_rpl`, header View, komentar langkah `Layanan`/`Pengendali`, serta JSDoc/komentar JS & E2E).
- **Arsitektur**: PHP Native 8.2+ terstruktur (Layered MVC-Lite) + Single Entry Point `public/index.php` + Bootstrap 5 Offline (`public/aset/vendor/`) + Vanilla JS (`public/aset/js/aplikasi.js`).
- **Standar Mutu Otomatis & CI/CD Lokal (`0 Error & 0 Warning`)**: Kepatuhan & Anotasi (`periksa_kepatuhan_agen.php`), PHPCS PSR-12 Zero-Warning (`phpcs.xml`), **PHPStan Level 8** (`phpstan.neon`), Unit/Integration strict `E_ALL` (`jalankan_semua_tes.php`), Playwright E2E (`playwright.config.js`), Git Commit/Push Gate (`.githooks/`, `gerbang_komit_dan_push.ps1`), serta Pipeline CI/CD Lokal Docker/Native (`jalankan-ci-lokal.ps1`, `Dockerfile`, `docker-compose.ci.yml`).

---

## 2. Komponen yang Sudah Selesai & Terverifikasi
- **Dokumen Desain, Arsitektur, DFD, ERD, Kamus Data, Glosarium, Versioning, Prasyarat Lingkungan, Modul Praktikum RPL, & CI/CD Lokal** (`dokumentasi/00`–`06`), termasuk [`alur-belajar-dan-modul-praktikum-rpl.md`](../02-panduan-dev/alur-belajar-dan-modul-praktikum-rpl.md), [`panduan-ci-cd-lokal-docker.md`](../02-panduan-dev/panduan-ci-cd-lokal-docker.md), dan [`ADR-008`](../03-adr/ADR-008-ci-cd-lokal-docker-dan-modul-praktikum-rpl.md).
- **Basis Data, Konfigurasi Fleksibel (`getenv`), & Skrip Onboarding**: `basis_data/skema.sql`, `basis_data/data_awal.sql`, `konfigurasi/basis_data.php`, `konfigurasi/aplikasi.php`, `.env.example`, `.editorconfig`, `.vscode/`, dan `siapkan-projek.ps1`.
- **Core, Model, Layanan, Pengendali, Pembantu, & Tampilan Bootstrap 5 Offline**: Seluruh lapisan kode telah 100% dilengkapi PHPDoc `@konsep_rpl`, komentar langkah `// [Langkah X - Konsep RPL]: ...`, serta header dokumentasi View.
- **Quality Gate, Handover Gate, & Local CI/CD Pipeline (`0 Error & 0 Warning`)**:
  - `periksa_kepatuhan_agen.php` (2415 titik pemeriksaan — 0 Pelanggaran).
  - `gerbang_handover_agen.php` (Entry Gate dengan pengecekan runtime PHP, ekstensi wajib, Composer, & Playwright).
  - `jalankan-ci-lokal.ps1` & `periksa-konsistensi.ps1` + PHPCS Zero-Warning PSR-12 + **PHPStan Level 8** + Playwright Smoke/E2E Suite (0 Console Error/Warning) + Git Commit/Push Gate (`.githooks/`, `gerbang_komit_dan_push.ps1`).

---

## 3. Tugas Aktif / Sedang Dikerjakan
- Tidak ada tugas tertunda pada Fase 6 (`v1.4.0`). Seluruh modul adaptasi siswa RPL dan pipeline CI/CD lokal telah selesai dan terverifikasi.

---

## 4. Langkah Selanjutnya (Prioritas Agen Berikutnya)
1. **Validasi Entry Gate**: Setiap sesi baru wajib memulai dengan menjalankan `C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\gerbang_handover_agen.php`.
2. **Implementasi Latihan Praktikum / Fitur Lanjutan (Opsional)**: Mengerjakan salah satu modul pada Bagian 4 [`alur-belajar-dan-modul-praktikum-rpl.md`](../02-panduan-dev/alur-belajar-dan-modul-praktikum-rpl.md) (misal halaman log audit `/laporan/audit` atau ekspor CSV mutasi) dengan tetap menjaga 100% kelulusan `jalankan-ci-lokal.ps1`.

---

## 5. Catatan Konteks Penting & Keputusan Terakhir
- **Bebas Kuota GitHub Runner**: Seluruh proses CI/CD dijalankan secara lokal melalui `jalankan-ci-lokal.ps1` (mendukung container Docker `docker-compose.ci.yml` maupun mode Native XAMPP) dan dijaga oleh `.githooks/pre-commit` serta `.githooks/pre-push`.
- **Aturan Nol Toleransi Quality Gate**: Sebelum melakukan `git commit` atau `git push`, wajib memastikan `periksa_kepatuhan_agen.php`, `phpcs` (0 warning), `phpstan` (Level 8, 0 error), `jalankan_semua_tes.php` (`E_ALL`), dan `npm run test:e2e` lulus 100%.
