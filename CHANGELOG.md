# Catatan Perubahan (Changelog) — Sistem Informasi Bank Mini

Seluruh perubahan penting pada projek **Bank Mini (PHP Native)** didokumentasikan di dalam file ini.

Format dokumen ini mengacu pada standar [Keep a Changelog](https://keepachangelog.com/id-ID/1.1.0/), dan penomoran versi mengikuti [Semantic Versioning (SemVer 2.0.0)](https://semver.org/lang/id/) (`MAJOR.MINOR.PATCH`) yang dikombinasikan dengan tanggal rilis (`YYYY-MM-DD`).

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

