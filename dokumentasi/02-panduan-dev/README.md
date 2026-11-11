# Panduan Developer (Developer Guide)

Selamat datang di dokumentasi teknis pengembangan **Bank Mini (PHP Native)**. Dokumen di dalam folder ini menjadi acuan wajib bagi seluruh pengembang dan agen AI sebelum menulis kode di `C:\xampp\htdocs\www\bankmini`.

## Daftar Dokumen Teknis
1. [**Arsitektur Sistem, SoT, & SoC (`arsitektur-sistem.md`)**](./arsitektur-sistem.md)
   - Perencanaan *Single Source of Truth (SoT)* dan *Separation of Concerns (SoC)*
   - Struktur direktori aplikasi `bankmini` berbahasa Indonesia (`konfigurasi/`, `aplikasi/Inti/`, `aplikasi/Pengendali/`, `aplikasi/Layanan/`, `aplikasi/Model/`, `aplikasi/Tampilan/`)
   - Siklus hidup request (*Request Lifecycle*)
2. [**Konvensi Penamaan & Standar Anotasi Bahasa Indonesia (`konvensi-penamaan-dan-anotasi.md`)**](./konvensi-penamaan-dan-anotasi.md)
   - Format penulisan folder, file, class (`PascalCase`), method & variabel (`camelCase`), serta tabel/kolom database (`snake_case`) dalam Bahasa Indonesia
   - Standar anotasi PHPDoc & komentar kode Bahasa Indonesia
3. [**Standar Kode & Keamanan Finansial (`standar-kode-dan-keamanan.md`)**](./standar-kode-dan-keamanan.md)
   - Standar penulisan PHP Native modern (`strict_types`, PSR-12)
   - Penanganan mata uang tunggal **Rupiah (`IDR`)** & transaksi database ACID (`SELECT ... FOR UPDATE`)
   - Keamanan aplikasi (100% PDO Prepared Statements, CSRF + PRG, Sanitasi XSS `amankanHtml()`, Hardening Sesi)
4. [**Skema Database & Desain Ledger (`skema-database.md`)**](./skema-database.md)
   - Entity Relationship Diagram (ERD) berbahasa Indonesia
   - Spesifikasi tabel utama (`pengguna`, `nasabah`, `produk_tabungan`, `rekening`, `mutasi_transaksi`, `log_audit`)
   - Aturan konsistensi saldo Rupiah & indeks performa
5. [**Standar Pengujian & Quality Assurance (`standar-pengujian-qa.md`)**](./standar-pengujian-qa.md)
   - Struktur folder `pengujian/` (`Unit/`, `Integrasi/`, `Keamanan/`)
   - Konvensi penamaan file tes (`Tes*.php`) dan method uji (`uji...()`) dalam Bahasa Indonesia
   - Cakupan wajib 4 kategori skenario QA untuk transaksi Rupiah (`IDR`)

---

## Persyaratan Lingkungan Pengembangan (Local Environment)
- **Web Server**: Apache 2.4+ (XAMPP) dengan `mod_rewrite` aktif
- **PHP**: PHP 8.1+ (ekstensi wajib: `pdo_mysql`, `mbstring`, `openssl`, `intl`, `bcmath`)
- **Database**: MySQL 8.0+ / MariaDB 10.4+ dengan storage engine **InnoDB** (wajib untuk mendukung transaksi ACID dan *row-level locking*)
- **Lokasi Projek**: `C:\xampp\htdocs\www\bankmini`
