# Catatan Perubahan (Changelog) — Sistem Informasi Bank Mini

Seluruh perubahan penting pada projek **Bank Mini (PHP Native)** didokumentasikan di dalam file ini.

Format dokumen ini mengacu pada standar [Keep a Changelog](https://keepachangelog.com/id-ID/1.1.0/), dan penomoran versi mengikuti [Semantic Versioning (SemVer 2.0.0)](https://semver.org/lang/id/) (`MAJOR.MINOR.PATCH`) yang dikombinasikan dengan tanggal rilis (`YYYY-MM-DD`).

---

## [1.8.0] - 2026-11-11

### Ditambahkan (Added)
- **Logging Terpusat & Aman (`PencatatLog.php`)**: Rotasi berkas log harian di `penyimpanan/log/aplikasi-YYYY-MM-DD.log`, pembangkitan *Correlation ID* per permintaan HTTP, serta redaksi otomatis kata kunci sensitif (`kata_sandi`, `token_csrf`) menjadi `[DIRAHASIAKAN]`.
- **Penanganan Galat Global & Halaman Galat Kustom (`PenanganGalat.php`, `PengecualianHttp.php`, `galat/halaman_galat.php`)**: Menangani HTTP `403`, `404`, `500`, dan `503` (Mode Pemeliharaan via `penyimpanan/pemeliharaan.flag`) dengan tata letak Design System serta sakelar `mode_debug` edukatif.
- **Keamanan Berlapis & Pertahanan Sesi**: Header keamanan HTTP + **Content-Security-Policy (`script-src 'self'`)**, pembatas laju anti brute-force login (`PembatasLaju.php`), *idle timeout* sesi 30 menit + flag cookie `secure` di [`Sesi.php`](./aplikasi/Inti/Sesi.php), serta proteksi `.htaccess` untuk direktori non-publik (`aplikasi/`, `konfigurasi/`, `penyimpanan/`, `basis_data/`, `pengujian/`).
- **Optimasi Performa & Endpoint Kesehatan (`/kesehatan`)**: Penambahan indeks B-Tree pada `skema.sql` (`idx_nasabah_nama`, `idx_rekening_status`, `idx_mutasi_waktu`), refactor kueri SARGable di [`ModelMutasi.php`](./aplikasi/Model/ModelMutasi.php), *cache-busting* `?v=VERSI`, kompresi Gzip/Expires di [`public/.htaccess`](./public/.htaccess), rute JSON `GET /kesehatan`, serta `HEALTHCHECK` pada [`Dockerfile`](./Dockerfile).
- **Dokumentasi Pembelajaran RPL & Pengujian QA (`QA-FND-001` s.d. `QA-FND-006`)**: Pembaruan panduan keamanan, arsitektur, optimasi SARGable database, glosarium istilah teknis, `ADR-009`, unit test [`TesFondasiSistem.php`](./pengujian/Unit/TesFondasiSistem.php), serta Playwright test untuk `/kesehatan`, `404`, dan header CSP.

---

## [1.7.0] - 2026-11-11

### Ditambahkan (Added)
- **Arsitektur Design System Berlapis (`public/aset/css/` & `public/aset/js/`)**: Memecah CSS & JS menjadi token terpusat ([`token-desain.css`](./public/aset/css/token-desain.css)), komponen bersama ([`komponen-ui.css`](./public/aset/css/komponen-ui.css)), skrip dasar ringan ([`inti-ui.js`](./public/aset/js/inti-ui.js)), serta bundel terpisah **Publik** ([`publik.css`](./public/aset/css/publik.css)) dan **Dasbor** ([`dasbor.css`](./public/aset/css/dasbor.css), [`dasbor-interaktif.js`](./public/aset/js/dasbor-interaktif.js)).
- **Slicing Komponen UI Bersama (`aplikasi/Tampilan/komponen/` & `komponenUi()`)**: Menambahkan helper terisolasi `komponenUi()` di [`fungsi_umum.php`](./aplikasi/Pembantu/fungsi_umum.php) beserta 6 komponen standar (`kartu_kpi`, `lencana_status`, `pesan_kilat`, `kepala_kartu`, `pencarian_tabel`, `kop_surat`) yang dipakai seragam di `dasbor`, `nasabah`, `rekening`, `laporan`, `masuk`, dan `portal`.
- **Pemisahan Kerangka Tata Letak Multi-Shell & Portal Publik (`/portal`)**: Memperluas [`PengendaliDasar::tampilkanTampilan()`](./aplikasi/Inti/PengendaliDasar.php) mendukung `'dasbor'`, `'publik'`, dan `'cetak'` serta menambahkan [`PengendaliPublik::portal()`](./aplikasi/Pengendali/PengendaliPublik.php) untuk rute `/portal` tanpa membawa beban UI dasbor admin.
- **Pengujian Unit & E2E Pemisahan Aset**: Menambahkan `QA-UNT-005` di [`TesFungsiUmum.php`](./pengujian/Unit/TesFungsiUmum.php) dan verifikasi Playwright di [`smoke.spec.js`](./pengujian/e2e/smoke.spec.js) yang memastikan halaman `/portal` & `/masuk` bebas dari aset CSS/JS dasbor admin.

---

## [1.6.0] - 2026-11-11

### Ditambahkan (Added)
- **Dependensi Composer Dokumen Kantor (PDF, Excel, Word)**: Menambahkan pustaka produksi `dompdf/dompdf` (`^3.1`), `phpoffice/phpspreadsheet` (`^5.11`), `shuchkin/simplexlsxgen` (`^1.5`), `shuchkin/simplexlsx` (`^1.1`), dan `phpoffice/phpword` (`^1.4`) pada [`composer.json`](./composer.json) beserta pemuatan otomatis `vendor/autoload.php` di [`public/index.php`](./public/index.php).
- **Layanan Terpusat Ekspor/Impor/Cetak (`LayananDokumenKantor.php`)**: Menyediakan adapter siap pakai untuk pembangkitan PDF (`buatPdfDariHtml`), ekspor/impor Excel `.xlsx` & `.csv` (`buatExcelDariTabel`, `bacaDataDariExcel`, `buatCsvDariTabel`, `bacaDataDariCsv`), pembuatan dokumen Word `.docx` (`buatDokumenWord` dengan fallback otomatis `PCLZip`), serta pembungkus HTML Kop Surat siap cetak (`siapkanHtmlCetak`).
- **Pengujian Unit Dokumen Kantor (`TesLayananDokumenKantor.php`)**: Menambahkan 3 skenario pengujian otomatis (`QA-UNIT-006` s.d. `QA-UNIT-008`) yang memvalidasi biner PDF, round-trip ekspor-impor Excel/CSV, dan pembuatan dokumen Word `.docx`.

---

## [1.5.0] - 2026-11-11

### Diperbarui (Changed)
- Tambahkan Modul Pusat Dokumentasi & Materi RPL Terindeks di Browser (/dokumentasi) Lengkap dengan Pencarian Kata Kunci, Daftar Isi Otomatis, Proteksi Anti-Path Traversal, & Pengujian QA

---

## [1.4.0] - 2026-11-11

### Diperbarui (Changed)
- Tambahkan Paket Pembelajaran & Praktikum Siswa RPL, Skrip Onboarding Satu Klik, Konfigurasi Env Fleksibel, serta Pipeline CI/CD Lokal Berbasis Docker/Native Tanpa Kuota GitHub Runner

---

## [1.3.1] - 2026-11-11

### Diperbarui (Changed)
- Lengkapi Dokumen Arsitektur Prasyarat Lingkungan serta Gate Anotasi Kode, Linting, Static Analysis, dan Handover

---

## [1.3.0] - 2026-11-11

### Diperbarui (Changed)
- Perbaikan UI Sidebar Kontras Tinggi, Redesign Antarmuka Modern Bank Mini, serta Penambahan Tooling Lint PHPCS, PHPStan Level 5, dan Playwright E2E/Smoke Test

---

## [1.2.0] - 2026-11-11

### Diperbarui (Changed)
- Upgrade Frontend dan UI/UX Bootstrap 5 Offline, Pratinjau Rupiah Live, Modal Konfirmasi Transaksi, dan Tata Letak Siap Cetak

---

## [1.1.1] - 2026-11-11

### Diperbarui (Changed)
- Penambahan Glosarium & Daftar Istilah Teknis, Kamus Data, dan Fundamental RPL (glosarium-dan-daftar-istilah.md)

---

## [1.1.0] - 2026-11-11

### Ditambahkan (Added)
- **Komentar Edukasi RPL (`@konsep_rpl`)**: Penjelasan blok kode mendalam di seluruh file PHP (`Inti`, `Model`, `Layanan`, `Pengendali`, `Pembantu`, `Pengujian`) sebagai bahan pembelajaran siswa Rekayasa Perangkat Lunak (RPL).
- **Dokumentasi Bahan Belajar Database & Alur Sistem**:
  - Dokumen `dfd-dan-alur-sistem.md` (Diagram Konteks / DFD Level 0, DFD Level 1, DFD Level 2 Transaksi, Sequence Diagram, dan Flowchart Algoritma ACID).
  - Perluasan `skema-database.md` dengan Arsitektur InnoDB, Kamus Data Entitas & Atribut, Relasi Kardinalitas, serta Normalisasi Database (1NF s.d. 3NF).
- **Manajemen Versi, Lisensi, & Skrip Bump Version**:
  - File `VERSION` sebagai *Single Source of Truth (SoT)* versi aplikasi.
  - File `LICENSE` (MIT License dengan ringkasan edukasi siswa RPL) dan `CHANGELOG.md`.
  - Skrip CLI `dokumentasi/06-aturan-agen/skrip/naikkan_versi.php` untuk melakukan *bump version* otomatis (`major`, `minor`, `patch`).
  - Dokumen `manajemen-versi-lisensi-dan-changelog.md` dan `ADR-006`.
- **Fitur Jurnal Koreksi & Filter Tanggal Mutasi**:
  - Antarmuka Jurnal Koreksi / *Reversal* (`GET/POST /transaksi/koreksi`) khusus Supervisor & Admin.
  - Filter rentang tanggal (`tanggal_mulai` s.d. `tanggal_akhir`) dan tombol cetak pada laporan buku besar mutasi.

### Diperbarui (Changed)
- `konfigurasi/aplikasi.php` kini membaca nomor versi secara dinamis dari file `VERSION` (SoT).
- `periksa_kepatuhan_agen.php` diperluas untuk memvalidasi sinkronisasi `VERSION`, `CHANGELOG.md`, `LICENSE`, dan keberadaan anotasi edukasi `@konsep_rpl` pada seluruh class aplikasi.
- Pembaruan menyeluruh pada `01-panduan-pengguna/` dan `02-panduan-dev/`.

---

## [1.0.0] - 2026-11-11

### Ditambahkan (Added)
- Inisiasi pondasi dokumentasi projek (`01-panduan-pengguna` s.d. `06-aturan-agen`) dan `ADR-001` s.d. `ADR-005`.
- Scaffold penuh aplikasi PHP Native berbahasa Indonesia dengan arsitektur **Layered MVC-Lite + Service Layer** (`Pengendali -> Layanan -> Model -> Tampilan`).
- Skema database MySQL InnoDB (`skema.sql`), seeder (`data_awal.sql`), dan installer CLI (`pasang_basis_data.php`) dengan mata uang tunggal **Rupiah (`IDR`)** `DECIMAL(15,2)`.
- Suite pengujian otomatis Quality Assurance (`pengujian/`) dengan 14 skenario Unit, Integrasi ACID, dan Keamanan.
- Skrip penjaga konsistensi (`periksa_kepatuhan_agen.php`) dan *Gate Handover* agen baru (`gerbang_handover_agen.php`).

