# ADR-008: Pipeline CI/CD Lokal Berbasis Docker/Native & Modul Praktikum Adaptasi Siswa RPL

- **Status**: Disetujui (Accepted)
- **Tanggal**: 2026-11-12
- **Pengambil Keputusan**: Tim Arsitek & Pengembang Bank Mini

---

## 1. Konteks & Permasalahan
1. **Keterbatasan Kuota Runner Cloud (GitHub Free)**: Penggunaan GitHub Actions di akun gratis memiliki batas kuota menit bulanan dan waktu tunggu antrean yang dapat menghambat alur kerja praktikum siswa maupun pengujian intensif E2E.
2. **Kebutuhan Adaptasi Cepat Siswa SMK RPL**: Siswa yang baru mempelajari arsitektur *Layered MVC-Lite + Service Layer* membutuhkan urutan belajar terstruktur (*Learning Path*), bedah kode nyata dari hulu ke hilir (*walkthrough*), skrip persiapan satu klik (`siapkan-projek.ps1`), serta konfigurasi koneksi database yang fleksibel baik untuk XAMPP maupun Docker.

## 2. Keputusan Arsitektur
1. **Menerapkan Dual-Engine Local CI/CD Pipeline (`jalankan-ci-lokal.ps1`)**:
   - Menyediakan [`Dockerfile`](../../Dockerfile), [`docker-compose.yml`](../../docker-compose.yml), dan [`docker-compose.ci.yml`](../../docker-compose.ci.yml) untuk menjalankan seluruh tahapan CI/CD (migrasi DB InnoDB, `periksa_kepatuhan_agen.php`, `PHPCS` 0 Warning, `PHPStan Level 8` 0 Error, `jalankan_semua_tes.php` strict `E_ALL`, serta `Playwright E2E`) secara lokal tanpa menggunakan GitHub Runner.
   - Menyediakan mode *Native Fallback* agar komputer siswa dengan RAM terbatas tetap dapat menjalankan pipeline CI/CD yang sama menggunakan XAMPP lokal.
2. **Mengintegrasikan Gerbang Pemblokir Commit & Push (`.githooks/`)**:
   - Memasang `.githooks/pre-commit` dan `.githooks/pre-push` serta skrip [`gerbang_komit_dan_push.ps1`](../06-aturan-agen/skrip/gerbang_komit_dan_push.ps1) sehingga kode yang masih memiliki 1 saja *bug*, *error*, atau *warning* ditolak otomatis sebelum masuk ke repositori.
3. **Dukungan Variabel Lingkungan (`getenv`) Tanpa Merusak Kemudahan XAMPP**:
   - Memperbarui [`konfigurasi/basis_data.php`](../../konfigurasi/basis_data.php) dan [`konfigurasi/aplikasi.php`](../../konfigurasi/aplikasi.php) agar membaca variabel lingkungan `BANKMINI_*` (untuk Docker/CI) dengan *fallback* otomatis ke pengaturan standar XAMPP (`127.0.0.1:3306`).
4. **Menyediakan Modul Praktikum & Alur Belajar Siswa RPL**:
   - Menerbitkan [`alur-belajar-dan-modul-praktikum-rpl.md`](../02-panduan-dev/alur-belajar-dan-modul-praktikum-rpl.md), [`panduan-ci-cd-lokal-docker.md`](../02-panduan-dev/panduan-ci-cd-lokal-docker.md), skrip [`siapkan-projek.ps1`](../06-aturan-agen/skrip/siapkan-projek.ps1), serta konfigurasi `.vscode/` dan `.editorconfig`.

## 3. Konsekuensi
- **Positif**:
  - 0 biaya dan 0 pemakaian kuota menit GitHub Actions.
  - Siswa dapat memilih belajar menggunakan XAMPP tradisional maupun container Docker modern.
  - Kualitas repositori selalu terjaga 100% (`0 Error & 0 Warning`) berkat kombinasi Local CI/CD + Git Hooks + PHPStan Level 8.
- **Negatif / Mitigasi**:
  - Membutuhkan aktivasi `git config core.hooksPath .githooks` satu kali saat *clone*, yang telah diotomatisasi melalui skrip `siapkan-projek.ps1`.
