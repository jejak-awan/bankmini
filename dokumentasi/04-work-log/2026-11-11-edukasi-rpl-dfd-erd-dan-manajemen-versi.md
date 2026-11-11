# Work Log: 2026-11-11 — Komentar Edukasi RPL, DFD/ERD/Kamus Data, & Manajemen Versi (v1.1.0)

- **Tanggal**: 2026-11-11
- **Pelaksana**: Tim Pengembang & AI Agent
- **Modul Terkait**: Seluruh Kode Aplikasi, Bahan Belajar RPL (DFD/ERD), & Tata Kelola Rilis (`VERSION`, `CHANGELOG.md`, `LICENSE`)
- **Status**: Selesai

## 1. Tujuan Pekerjaan
1. Memperbarui seluruh file PHP di `C:\xampp\htdocs\www\bankmini` dengan komentar blok kode edukatif (`@konsep_rpl`) dan penjelasan langkah logika untuk pembelajaran siswa **Rekayasa Perangkat Lunak (RPL)**, serta menambahkan validasi otomatisnya di `periksa_kepatuhan_agen.php`.
2. Menambahkan dokumentasi bahan belajar database yang komprehensif: DFD Level 0 (Diagram Konteks), DFD Level 1, DFD Level 2 Transaksi, Sequence Diagram, Flowchart Algoritma ACID (`dfd-dan-alur-sistem.md`), serta memperluas `skema-database.md` dengan Arsitektur InnoDB, Kamus Data Entitas, dan Normalisasi Database (1NF-3NF).
3. Menyinkronkan `01-panduan-pengguna/` dan `02-panduan-dev/`, serta mempertegas kewajiban pembaruannya pada SOP agen.
4. Menambahkan sistem manajemen versi (`VERSION`, `CHANGELOG.md`, `LICENSE` MIT, skrip *bump version* `naikkan_versi.php`, `manajemen-versi-lisensi-dan-changelog.md`, dan `ADR-006`).

## 2. File yang Dibuat / Diubah
- **Tata Kelola Rilis & Lisensi (`C:\xampp\htdocs\www\bankmini\`)**:
  - `VERSION` (`1.1.0`)
  - `CHANGELOG.md`
  - `LICENSE` (MIT License + Ringkasan Edukasi Siswa RPL)
  - `dokumentasi/06-aturan-agen/skrip/naikkan_versi.php`
  - `dokumentasi/02-panduan-dev/manajemen-versi-lisensi-dan-changelog.md`
  - `dokumentasi/03-adr/ADR-006-manajemen-versi-changelog-dan-lisensi.md`
- **Bahan Belajar DFD, ERD, & Kamus Data**:
  - `dokumentasi/02-panduan-dev/dfd-dan-alur-sistem.md`
  - `dokumentasi/02-panduan-dev/skema-database.md`
- **Komentar Edukasi Siswa RPL (`@konsep_rpl`)**:
  - Seluruh file di `konfigurasi/`, `public/index.php`, `aplikasi/Pembantu/`, `aplikasi/Inti/`, `aplikasi/Model/`, `aplikasi/Layanan/`, dan `aplikasi/Pengendali/`.
- **Aturan Agen & Pemeriksa Otomatis**:
  - `dokumentasi/06-aturan-agen/skrip/periksa_kepatuhan_agen.php`
  - `dokumentasi/06-aturan-agen/aturan-koding-php-bankmini.md`
  - `dokumentasi/06-aturan-agen/alur-kerja-dokumentasi.md`
  - `.agents/rules/aturan-projek-bankmini.md`

## 3. Pengujian & Verifikasi
- [x] Skrip `periksa_kepatuhan_agen.php` memverifikasi `@konsep_rpl`, `VERSION`, `CHANGELOG.md`, `LICENSE`, dan seluruh dokumen baru dengan **0 Pelanggaran**.
- [x] Suite QA `jalankan_semua_tes.php` lulus 14/14 skenario.
- [x] Skrip `gerbang_handover_agen.php` lulus verifikasi.

