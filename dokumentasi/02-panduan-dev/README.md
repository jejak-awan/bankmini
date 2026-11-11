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
   - Penjelasan lengkap istilah fundamental RPL & arsitektur (`SoT`, `SoC`, `MVC-Lite`, `PSR-4`, `PSR-12`, `Singleton`)
   - Penjelasan istilah basis data & kamus data (`InnoDB`, `PK`, `FK`, `Self-Referencing FK`, `DECIMAL(15,2)`, `1NF-3NF`, `DFD`, `ERD`)
   - Penjelasan istilah transaksi keuangan (`ACID`, `Row Locking`, `Race Condition`, `Deadlock`, `Immutable Ledger`, `KREDIT/DEBIT`, `Saldo Efektif`, `Reversal`), keamanan web (`SQLi`, `XSS`, `CSRF`, `PRG`, `Session Fixation`, `RBAC`), serta QA, Versioning, dan Git

---

## Persyaratan Lingkungan Pengembangan (Local Environment)
- **Web Server**: Apache 2.4+ (XAMPP) dengan `mod_rewrite` aktif
- **PHP**: PHP 8.1+ (ekstensi wajib: `pdo_mysql`, `mbstring`, `openssl`, `intl`, `bcmath`)
- **Database**: MySQL 8.0+ / MariaDB 10.4+ dengan storage engine **InnoDB**
- **Lokasi Projek**: `C:\xampp\htdocs\www\bankmini`
