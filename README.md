# Sistem Informasi Bank Mini Sekolah (PHP Native MVC-Lite & Bahan Belajar SMK RPL)

Aplikasi perbankan mikro (**Bank Mini Sekolah**) berbasis **PHP Native 8.2+** dengan arsitektur **Layered MVC-Lite + Service Layer**, transaksi database **ACID (MySQL InnoDB)** dalam mata uang tunggal **Rupiah (`IDR`)** `DECIMAL(15,2)`, antarmuka **Bootstrap 5 Offline**, serta konvensi kode dan anotasi edukasi **100% Bahasa Indonesia (`@konsep_rpl`)**.

---

## Mulai Cepat 1 Perintah (Onboarding Siswa & Guru RPL)

Setelah melakukan `git clone`, nyalakan **Apache** & **MySQL** di XAMPP, lalu jalankan skrip persiapan otomatis ini dari PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File .\dokumentasi\06-aturan-agen\skrip\siapkan-projek.ps1
```

Skrip di atas akan otomatis:
1. Mengaktifkan Git Hooks pemblokir *commit/push* (`.githooks/pre-commit` & `pre-push`).
2. Membuat database `bankmini_db`, tabel InnoDB (`skema.sql`), dan akun awal (`data_awal.sql`).
3. Memverifikasi kesiapan ekstensi PHP dan seluruh standar proyek.

### Akses Aplikasi & Akun Uji Bawaan
- **URL Lokal (XAMPP)**: `http://localhost/www/bankmini/public/masuk`
- **URL Lokal (Docker)**: `http://localhost:8080/masuk` *(jika dijalankan dengan `docker compose up -d`)*
- **Kredensial Login Bawaan** (Kata Sandi semua akun: **`password`**):
  - **`admin`** — Administrator Sistem (Akses penuh seluruh modul & jurnal koreksi)
  - **`teller1`** — Petugas Teller (Registrasi nasabah, buka rekening, setor, tarik, transfer)
  - **`supervisor1`** — Kepala / Supervisor Bank Mini (Otorisasi jurnal koreksi/reversal & laporan)

---

## Peta Belajar Siswa SMK RPL (Mulai dari Sini!)

Baru pertama kali mempelajari kode projek ini? Ikuti panduan bertahap berikut:
1. [**Alur Belajar 5 Modul, Bedah Kode Hulu-ke-Hilir, & Latihan Praktikum (`alur-belajar-dan-modul-praktikum-rpl.md`)**](./dokumentasi/02-panduan-dev/alur-belajar-dan-modul-praktikum-rpl.md)
2. [**Arsitektur Sistem & Prasyarat Lingkungan (`arsitektur-sistem.md`)**](./dokumentasi/02-panduan-dev/arsitektur-sistem.md)
3. [**DFD (Level 0–2), Sequence Diagram, & Flowchart (`dfd-dan-alur-sistem.md`)**](./dokumentasi/02-panduan-dev/dfd-dan-alur-sistem.md)
4. [**ERD, Kamus Data, & Normalisasi 1NF–3NF (`skema-database.md`)**](./dokumentasi/02-panduan-dev/skema-database.md)
5. [**Glosarium & Daftar Istilah Fundamental RPL (`glosarium-dan-daftar-istilah.md`)**](./dokumentasi/02-panduan-dev/glosarium-dan-daftar-istilah.md)
6. [**Panduan Operasional Pengguna (`01-panduan-pengguna/`)**](./dokumentasi/01-panduan-pengguna/README.md)

---

## Struktur Direktori Utama

| Folder / File | Deskripsi & Tanggung Jawab (*Separation of Concerns*) |
| :--- | :--- |
| [`public/`](./public/) | Satu-satunya pintu masuk HTTP (`public/index.php`) dan aset offline (`aset/vendor/`, `aset/css/`, `aset/js/`). |
| [`konfigurasi/`](./konfigurasi/) | *Single Source of Truth (SoT)* konfigurasi aplikasi (`aplikasi.php`) dan koneksi database (`basis_data.php`). |
| [`aplikasi/Inti/`](./aplikasi/Inti/) | Pondasi sistem: `BasisData` (Singleton PDO), `Sesi`, `TokenCsrf`, `MiddlewareAutentikasi`, `Perute`, `PengendaliDasar`. |
| [`aplikasi/Pengendali/`](./aplikasi/Pengendali/) | Lapisan HTTP Controller dengan pola *Post-Redirect-Get (PRG)* (bebas query SQL). |
| [`aplikasi/Layanan/`](./aplikasi/Layanan/) | Lapisan Logika Bisnis & Batas Transaksi ACID (`beginTransaction`, `SELECT ... FOR UPDATE`, `BCMath`, `commit`, `rollBack`). |
| [`aplikasi/Model/`](./aplikasi/Model/) | Lapisan Akses Data menggunakan 100% PDO Prepared Statements (`EMULATE_PREPARES = false`). |
| [`aplikasi/Tampilan/`](./aplikasi/Tampilan/) | Template antarmuka HTML Bootstrap 5 dengan sanitasi XSS `amankanHtml()` dan `formatRupiah()`. |
| [`basis_data/`](./basis_data/) | Skema DDL InnoDB (`skema.sql`), seeder (`data_awal.sql`), dan pemasang CLI (`pasang_basis_data.php`). |
| [`pengujian/`](./pengujian/) | Suite pengujian otomatis (`Unit/`, `Integrasi/`, `Keamanan/`, `e2e/` Playwright, & `jalankan_semua_tes.php`). |
| [`dokumentasi/`](./dokumentasi/README.md) | Dokumentasi lengkap: Panduan Pengguna, Panduan Dev, ADR, Work Log, Template, dan Skrip Penjaga Konsistensi. |
| [`Dockerfile`](./Dockerfile) & [`docker-compose.yml`](./docker-compose.yml) | Kontainerisasi lingkungan pengembangan & **Pipeline CI/CD Lokal (`docker-compose.ci.yml`)** bebas kuota GitHub Runner. |

---

## Pipeline CI/CD Lokal & Quality Gate (`0 Error & 0 Warning`)

Projek ini menerapkan kebijakan **Zero Error & Zero Warning** serta dilengkapi **Local CI/CD Pipeline** (mendukung mode **Docker** maupun **Native XAMPP**) tanpa memerlukan kuota menit GitHub Actions:

```powershell
# Jalankan Pipeline CI/CD Lokal lengkap (Otomatis deteksi Docker / Native + Playwright E2E)
powershell -ExecutionPolicy Bypass -File .\dokumentasi\06-aturan-agen\skrip\jalankan-ci-lokal.ps1

# Atau jalankan Quality Gate PHP + E2E secara langsung
powershell -ExecutionPolicy Bypass -File .\dokumentasi\06-aturan-agen\skrip\periksa-konsistensi.ps1 -DenganE2E
```

Seluruh `git commit` dan `git push` diproteksi oleh [`.githooks/pre-commit`](./.githooks/pre-commit), [`.githooks/pre-push`](./.githooks/pre-push), dan [`gerbang_komit_dan_push.ps1`](./dokumentasi/06-aturan-agen/skrip/gerbang_komit_dan_push.ps1) yang memblokir rilis apabila masih terdapat 1 saja *bug*, *error*, atau *warning* pada:
1. Pemeriksa Kepatuhan Arsitektur & Anotasi RPL (`composer patuh`)
2. Suite Pengujian Unit, Integrasi ACID, & Keamanan (`composer test` — strict `E_ALL`)
3. Standar Gaya Kode PSR-12 (`composer lint` — PHPCS Zero Warning)
4. Analisis Statis Tipe Data (`composer stan` — **PHPStan Level 8** Zero Error)
5. Smoke & End-to-End Browser Test (`npm run test:e2e` — Playwright Chromium Zero Console Error/Warning)
