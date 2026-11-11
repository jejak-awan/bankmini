# Panduan Developer & Materi Pembelajaran RPL (Developer Guide)

Selamat datang di dokumentasi teknis pengembangan **Bank Mini (PHP Native)**. Dokumen di dalam folder ini disusun sebagai acuan wajib bagi pengembang dan agen AI sekaligus **materi pembelajaran Rekayasa Perangkat Lunak (RPL)**.

## Daftar Dokumen Teknis & Bahan Belajar RPL
1. [**Arsitektur Sistem, SoT, & SoC (`arsitektur-sistem.md`)**](./arsitektur-sistem.md)
   - Perencanaan *Single Source of Truth (SoT)* dan *Separation of Concerns (SoC)*
   - Struktur direktori aplikasi `bankmini` berbahasa Indonesia (`konfigurasi/`, `aplikasi/Inti/`, `aplikasi/Pengendali/`, `aplikasi/Layanan/`, `aplikasi/Model/`, `aplikasi/Tampilan/`)
2. [**DFD (Level 0, 1, 2), Sequence Diagram, & Alur Sistem (`dfd-dan-alur-sistem.md`)**](./dfd-dan-alur-sistem.md)
   - Perbedaan DFD, ERD, dan Flowchart untuk siswa RPL
   - Diagram Konteks (DFD Level 0), DFD Level 1, dan DFD Level 2 Transaksi Keuangan
   - Sequence Diagram siklus transaksi ACID & Flowchart algoritma validasi saldo efektif
3. [**Arsitektur Database, ERD, Kamus Data, & Normalisasi (`skema-database.md`)**](./skema-database.md)
   - Arsitektur mesin penyimpanan MySQL InnoDB & studi kasus prinsip ACID
   - Entity Relationship Diagram (ERD) & Kamus Data lengkap (`pengguna`, `nasabah`, `produk_tabungan`, `rekening`, `mutasi_transaksi`, `log_audit`)
   - Pembahasan Normalisasi Database (`1NF`, `2NF`, `3NF`) & *Cached Balance* (`rekening.saldo`)
4. [**Konvensi Penamaan, Anotasi, & Komentar Edukasi RPL (`konvensi-penamaan-dan-anotasi.md`)**](./konvensi-penamaan-dan-anotasi.md)
   - Format penulisan folder, file, class (`PascalCase`), method & variabel (`camelCase`), serta tabel/kolom database (`snake_case`) dalam Bahasa Indonesia
   - Standar anotasi PHPDoc & kewajiban komentar edukasi `@konsep_rpl` untuk siswa RPL
5. [**Standar Kode & Keamanan Finansial (`standar-kode-dan-keamanan.md`)**](./standar-kode-dan-keamanan.md)
   - Standar penulisan PHP Native modern (`strict_types`, PSR-12)
   - Penanganan mata uang tunggal **Rupiah (`IDR`)** & transaksi database ACID (`SELECT ... FOR UPDATE`)
   - Keamanan aplikasi (100% PDO Prepared Statements, CSRF + PRG, Sanitasi XSS `amankanHtml()`, Hardening Sesi)
6. [**Standar Pengujian & Quality Assurance (`standar-pengujian-qa.md`)**](./standar-pengujian-qa.md)
   - Struktur folder `pengujian/` (`Unit/`, `Integrasi/`, `Keamanan/`)
   - Konvensi penamaan file tes (`Tes*.php`), method uji (`uji...()`), dan tag `@skenario`
7. [**Manajemen Versi (SemVer/CalVer), Lisensi, & Changelog (`manajemen-versi-lisensi-dan-changelog.md`)**](./manajemen-versi-lisensi-dan-changelog.md)
   - Perbandingan SemVer vs CalVer untuk siswa RPL
   - Aturan *Single Source of Truth* file `VERSION`, `CHANGELOG.md`, `LICENSE` (MIT), dan cara menggunakan skrip *bump version* (`naikkan_versi.php`)
8. [**Glosarium & Daftar Istilah Teknis, Kamus Data, dan Fundamental RPL (`glosarium-dan-daftar-istilah.md`)**](./glosarium-dan-daftar-istilah.md)
   - Penjelasan kamus data lengkap tabel & kolom Bank Mini serta definisi konsep fundamental RPL (`MVC`, `ACID`, `Row Lock`, `Immutable Ledger`, `CSRF`, `PRG`, `XSS`, `SemVer`, dll.)
9. [**Standar Frontend & UI/UX (`standar-frontend-ui-ux.md`)**](./standar-frontend-ui-ux.md)
   - Arsitektur **Bootstrap 5 Offline**, Design System Berlapis (`token-desain.css`, `komponen-ui.css`), komponen `komponenUi()`, tata letak multi-shell (`publik`, `dasbor`, `cetak`), & kepatuhan CSP ketat
10. [**Rundown Belajar 8 Pertemuan, Bedah Kode Hulu-ke-Hilir, & Modul Praktikum Siswa RPL (`alur-belajar-dan-modul-praktikum-rpl.md`)**](./alur-belajar-dan-modul-praktikum-rpl.md)
    - Kurikulum rundown belajar terstruktur 8 pertemuan SMK RPL (`v1.8.1+`) lengkap dengan file bacaan wajib dan *checkpoint* pemahaman
    - Bedah alur kode *End-to-End* transaksi Setor Tunai Rp 50.000 baris demi baris & resep 6 langkah menambah fitur baru
11. [**Katalog 12 Tugas Pengembangan Modul & Fitur RPL (`katalog-tugas-pengembangan-fitur-rpl.md`)**](./katalog-tugas-pengembangan-fitur-rpl.md)
    - Backlog 12 tugas praktikum & proyek akhir UKK (`TUGAS-01` s.d. `TUGAS-12`) mencakup filter nasabah, cetak struk, siklus rekening ACID, ekspor/impor PDF/Excel/Word, audit log, portal mandiri siswa *anti-IDOR*, & pengujian E2E lengkap dengan rubrik penilaian
12. [**Panduan CI/CD Lokal Berbasis Docker & Native (`panduan-ci-cd-lokal-docker.md`)**](./panduan-ci-cd-lokal-docker.md)
    - Arsitektur *Dual-Engine Local CI/CD* (`docker-compose.ci.yml` & Native XAMPP) tanpa memakai kuota menit GitHub Runner
    - Cara menjalankan aplikasi di Docker (`docker compose up -d`) dan mengeksekusi `jalankan-ci-lokal.ps1`

---

## Persyaratan Lingkungan Pengembangan (Local Environment)
- **Web Server**: Apache 2.4+ (XAMPP) dengan `mod_rewrite` aktif
- **PHP**: PHP 8.1+ (ekstensi wajib: `pdo_mysql`, `mbstring`, `openssl`, `intl`, `bcmath`)
- **Database**: MySQL 8.0+ / MariaDB 10.4+ dengan storage engine **InnoDB**
- **Lokasi Projek**: `C:\xampp\htdocs\www\bankmini`
