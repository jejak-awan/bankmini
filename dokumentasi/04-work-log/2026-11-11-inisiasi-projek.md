# Work Log: 2026-11-11 — Inisiasi & Standarisasi Dokumentasi Bank Mini

- **Tanggal**: 2026-11-11
- **Pelaksana**: Tim Pengembang & AI Agent
- **Status**: Selesai

## 1. Tujuan Pekerjaan
Menyusun pondasi dokumentasi rekayasa perangkat lunak, perencanaan **Single Source of Truth (SoT)** & **Separation of Concerns (SoC)**, standar transaksi keuangan **Rupiah (`IDR`)**, serta konvensi penamaan & anotasi **Bahasa Indonesia** untuk projek **Bank Mini (PHP Native)** di `C:\xampp\htdocs\www\dokumentasi`.

## 2. Daftar Perubahan (Deliverables)
- Membuat indeks utama dokumentasi (`README.md`).
- Membuat folder `01-panduan-pengguna/` berisi panduan operasional Admin, Teller, dan Nasabah.
- Membuat folder `02-panduan-dev/` berisi:
  - `arsitektur-sistem.md`: Matriks SoT, SoC, dan struktur folder berbahasa Indonesia (`konfigurasi/`, `aplikasi/Inti/`, `aplikasi/Pengendali/`, `aplikasi/Layanan/`, `aplikasi/Model/`, `aplikasi/Pembantu/`, `aplikasi/Tampilan/`, `basis_data/`, `penyimpanan/`).
  - `konvensi-penamaan-dan-anotasi.md`: Standar penulisan kode PSR-12 berbahasa Indonesia (Class `PascalCase`, method/variabel `camelCase`, tabel/kolom `snake_case`) beserta contoh lengkap anotasi PHPDoc Bahasa Indonesia.
  - `standar-kode-dan-keamanan.md`: Standar transaksi ACID (`SELECT ... FOR UPDATE`), mata uang tunggal **Rupiah (`IDR`)** `DECIMAL(15,2)`, PDO Prepared Statements (`EMULATE_PREPARES = false`), CSRF + PRG, dan XSS `amankanHtml()`.
  - `skema-database.md`: Rancangan ERD berbahasa Indonesia (`pengguna`, `nasabah`, `produk_tabungan`, `rekening`, `mutasi_transaksi`, `log_audit`).
  - `standar-pengujian-qa.md`: Standar struktur folder `pengujian/`, penamaan `Tes*.php`, method `uji...()`, dan cakupan wajib 4 kategori QA untuk transaksi Rupiah (`IDR`).
- Membuat folder `03-adr/` beserta 5 keputusan arsitektur (`ADR-001` s.d. `ADR-005`).
- Membuat folder `04-work-log/` untuk pencatatan progres harian.
- Membuat folder `05-template-dokumentasi/` berisi template ADR, Work Log, Spesifikasi Fitur, Panduan, dan Skenario Pengujian QA (`template-skenario-qa.md`).
- Membuat folder `06-aturan-agen/` beserta aturan pengujian QA (`aturan-pengujian-qa.md`), skrip otomatis penjaga konsistensi (`skrip/periksa_kepatuhan_agen.php` dan `skrip/periksa-konsistensi.ps1`), serta rule workspace otomatis `.agents/rules/aturan-projek-bankmini.md`.

## 3. Langkah Selanjutnya (Next Steps)
1. Menyiapkan skema database (`basis_data/skema.sql` dan `basis_data/data_awal.sql`) di `C:\xampp\htdocs\www\bankmini`.
2. Membangun *core framework* PHP Native berbahasa Indonesia (`BasisData`, `Perute`, `Sesi`, `TokenCsrf`, `MiddlewareAutentikasi`, `PengendaliDasar`).
3. Mengimplementasikan modul Autentikasi & Manajemen Master Data.
