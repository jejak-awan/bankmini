---
trigger: always_on
description: Aturan wajib projek Bank Mini (PHP Native Bahasa Indonesia, Edukasi RPL, SoT, SoC, ACID Rupiah IDR, Versioning, & QA)
---

# Aturan Wajib Agen — Projek Bank Mini (`bankmini` & `dokumentasi`)

Projek ini digunakan sebagai **bahan pembelajaran siswa Rekayasa Perangkat Lunak (RPL)**. Setiap kali bekerja pada projek ini (`C:\xampp\htdocs\www\bankmini` dan `C:\xampp\htdocs\www\bankmini\dokumentasi`), Anda **WAJIB** mematuhi aturan di `C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\`:

1. **Bahasa Indonesia Penuh (PSR-12) & Komentar Edukasi RPL (`@konsep_rpl`)**:
   - Struktur folder wajib: `konfigurasi/`, `aplikasi/Inti/`, `aplikasi/Pengendali/`, `aplikasi/Layanan/`, `aplikasi/Model/`, `aplikasi/Pembantu/`, `aplikasi/Tampilan/`, `basis_data/`, `pengujian/`, `penyimpanan/`, `dokumentasi/` (kecuali `public/index.php`).
   - Class: `PascalCase` Bahasa Indonesia (`LayananTransaksi`, `ModelRekening`, `PengendaliNasabah`).
   - Method & Variabel: `camelCase` Bahasa Indonesia (`prosesSetorTunai()`, `$nominalSetoran`, `$saldoSesudah`). Dilarang memakai `$snake_case` atau nama variabel bahasa Inggris.
   - Tabel & Kolom DB: `snake_case` Bahasa Indonesia (`pengguna`, `nasabah`, `produk_tabungan`, `rekening`, `mutasi_transaksi`, `log_audit`).
   - **Wajib Edukasi RPL**: Setiap class di `aplikasi/` wajib menyertakan `declare(strict_types=1);`, blok anotasi PHPDoc (`/** ... */`) Bahasa Indonesia dengan tag `@konsep_rpl`, serta komentar penjelasan blok kode per langkah untuk pembelajaran siswa RPL.
2. **Arsitektur SoT & SoC**:
   - `Tampilan` dilarang memanggil query DB/Model.
   - `Pengendali` dilarang menulis query PDO atau mengelola transaksi DB.
   - `Layanan` adalah satu-satunya batas transaksi ACID (`beginTransaction`, `commit`, `rollBack`).
3. **Integritas Finansial Rupiah (`IDR`) & Keamanan**:
   - Mata uang tunggal **Rupiah (`IDR`)** dengan tipe MySQL InnoDB `DECIMAL(15,2)` (dilarang `FLOAT`/`DOUBLE`).
   - Perubahan saldo wajib memakai `SELECT ... FOR UPDATE` dan mencatat ke `mutasi_transaksi` (*Append-Only Ledger*).
   - Wajib 100% PDO Prepared Statements (`EMULATE_PREPARES = false`), CSRF + pola PRG, dan sanitasi XSS `amankanHtml()`.
4. **Kewajiban File Tes QA, Dokumentasi, Versioning, & Gate Handover**:
   - **ENTRY GATE (Saat Memulai Sesi/Tugas Baru)**: Agen baru **WAJIB** menjalankan `C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\gerbang_handover_agen.php`.
   - Setiap `aplikasi/Layanan/Layanan<Nama>.php` wajib memiliki file tes QA `pengujian/Integrasi/TesLayanan<Nama>.php` atau `pengujian/Unit/TesLayanan<Nama>.php` dengan method `public function uji...()` dan tag `@skenario`.
   - **EXIT / HANDOVER GATE (Sebelum Menyelesaikan Tugas)**: Agen **WAJIB**:
     1. Memperbarui `01-panduan-pengguna/` dan `02-panduan-dev/` sesuai perubahan tugas.
     2. Memperbarui versi (`VERSION` & `CHANGELOG.md` via `naikkan_versi.php`), `04-work-log/`, dan `status-handover-agen.md`.
     3. Menjalankan `C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\periksa_kepatuhan_agen.php` dan `C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\pengujian\jalankan_semua_tes.php` hingga **0 Pelanggaran (LULUS 100%)**.
