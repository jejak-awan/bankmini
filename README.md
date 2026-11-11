# Sistem Informasi Bank Mini (PHP Native)

Aplikasi perbankan mikro (**Bank Mini**) berbasis **PHP Native** dengan arsitektur **Layered MVC-Lite + Service Layer**, transaksi database **ACID (MySQL InnoDB)** dalam mata uang tunggal **Rupiah (`IDR`)** `DECIMAL(15,2)`, serta konvensi kode dan anotasi **100% Bahasa Indonesia**.

---

## Struktur Direktori Utama

| Folder / File | Deskripsi |
| :--- | :--- |
| [`public/`](./public/) | Satu-satunya pintu masuk HTTP (`public/index.php`) dan aset statis (`aset/css/`, `aset/js/`). |
| [`konfigurasi/`](./konfigurasi/) | *Single Source of Truth (SoT)* konfigurasi aplikasi (`aplikasi.php`) dan koneksi database (`basis_data.php`). |
| [`aplikasi/Inti/`](./aplikasi/Inti/) | Pondasi sistem: `BasisData` (Singleton PDO), `Sesi`, `TokenCsrf`, `MiddlewareAutentikasi`, `Perute`, `PengendaliDasar`. |
| [`aplikasi/Pengendali/`](./aplikasi/Pengendali/) | Lapisan HTTP Controller dengan pola *Post-Redirect-Get (PRG)*. |
| [`aplikasi/Layanan/`](./aplikasi/Layanan/) | Lapisan Logika Bisnis & Batas Transaksi ACID (`beginTransaction`, `SELECT ... FOR UPDATE`, `commit`, `rollBack`). |
| [`aplikasi/Model/`](./aplikasi/Model/) | Lapisan Akses Data menggunakan 100% PDO Prepared Statements (`EMULATE_PREPARES = false`). |
| [`aplikasi/Tampilan/`](./aplikasi/Tampilan/) | Template antarmuka HTML bebas query SQL dengan sanitasi XSS `amankanHtml()` dan `formatRupiah()`. |
| [`basis_data/`](./basis_data/) | Skema DDL InnoDB (`skema.sql`), seeder (`data_awal.sql`), dan pemasang CLI (`pasang_basis_data.php`). |
| [`pengujian/`](./pengujian/) | Suite pengujian otomatis Quality Assurance (`Unit/`, `Integrasi/`, `Keamanan/`, `jalankan_semua_tes.php`). |
| [`dokumentasi/`](./dokumentasi/README.md) | Dokumentasi lengkap: Panduan Pengguna, Panduan Dev, ADR, Work Log, Template, dan Aturan Agen + Skrip Konsistensi. |

---

## Instalasi & Menjalankan Secara Lokal (XAMPP)

1. **Pasang Skema & Data Awal Database (`bankmini_db`)**:
   ```powershell
   C:\xampp\php\php.exe basis_data\pasang_basis_data.php
   ```
2. **Jalankan Pemeriksa Konsistensi & Suite Pengujian QA**:
   ```powershell
   C:\xampp\php\php.exe dokumentasi\06-aturan-agen\skrip\periksa_kepatuhan_agen.php
   C:\xampp\php\php.exe pengujian\jalankan_semua_tes.php
   ```
3. **Akses di Browser**:
   - Buka `http://localhost/www/bankmini/public/masuk`
   - Akun bawaan (kata sandi: `password`):
     - `admin` (Peran: `admin`)
     - `teller1` (Peran: `teller`)
     - `supervisor1` (Peran: `supervisor`)

