---
trigger: always_on
description: Aturan wajib projek Bank Mini (PHP Native Bahasa Indonesia, SoT, SoC, ACID Rupiah IDR, & QA)
---

# Aturan Wajib Agen — Projek Bank Mini (`bankmini` & `dokumentasi`)

Setiap kali bekerja pada projek ini (`C:\xampp\htdocs\www\bankmini` dan `C:\xampp\htdocs\www\bankmini\dokumentasi`), Anda **WAJIB** mematuhi aturan di `C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\`:

1. **Bahasa Indonesia Penuh (PSR-12)**:
   - Struktur folder wajib: `konfigurasi/`, `aplikasi/Inti/`, `aplikasi/Pengendali/`, `aplikasi/Layanan/`, `aplikasi/Model/`, `aplikasi/Pembantu/`, `aplikasi/Tampilan/`, `basis_data/`, `pengujian/`, `penyimpanan/`, `dokumentasi/` (kecuali `public/index.php`).
   - Class: `PascalCase` Bahasa Indonesia (`LayananTransaksi`, `ModelRekening`, `PengendaliNasabah`).
   - Method & Variabel: `camelCase` Bahasa Indonesia (`prosesSetorTunai()`, `$nominalSetoran`, `$saldoSesudah`). Dilarang memakai `$snake_case` atau nama variabel bahasa Inggris.
   - Tabel & Kolom DB: `snake_case` Bahasa Indonesia (`pengguna`, `nasabah`, `produk_tabungan`, `rekening`, `mutasi_transaksi`, `log_audit`).
   - Wajib menyertakan `declare(strict_types=1);` dan anotasi PHPDoc (`/** ... */`) Bahasa Indonesia pada setiap class dan method.
2. **Arsitektur SoT & SoC**:
   - `Tampilan` dilarang memanggil query DB/Model.
   - `Pengendali` dilarang menulis query PDO atau mengelola transaksi DB.
   - `Layanan` adalah satu-satunya batas transaksi ACID (`beginTransaction`, `commit`, `rollBack`).
3. **Integritas Finansial Rupiah (`IDR`) & Keamanan**:
   - Mata uang tunggal **Rupiah (`IDR`)** dengan tipe MySQL InnoDB `DECIMAL(15,2)` (dilarang `FLOAT`/`DOUBLE`).
   - Perubahan saldo wajib memakai `SELECT ... FOR UPDATE` dan mencatat ke `mutasi_transaksi` (*Append-Only Ledger*).
   - Wajib 100% PDO Prepared Statements (`EMULATE_PREPARES = false`), CSRF + pola PRG, dan sanitasi XSS `amankanHtml()`.
4. **Kewajiban File Tes QA, Gate Handover Agen Baru, & Verifikasi Skrip Konsistensi**:
   - **ENTRY GATE (Saat Memulai Sesi/Tugas Baru)**: Agen baru **WAJIB** membaca `C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\status-handover-agen.md` atau menjalankan `C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\gerbang_handover_agen.php` agar memahami progres terakhir tanpa kehilangan konteks.
   - Setiap `aplikasi/Layanan/Layanan<Nama>.php` wajib memiliki file tes QA `pengujian/Integrasi/TesLayanan<Nama>.php` atau `pengujian/Unit/TesLayanan<Nama>.php` dengan method `public function uji...()` dan tag `@skenario`.
   - **EXIT / HANDOVER GATE (Sebelum Menyelesaikan Tugas)**: Agen **WAJIB** memperbarui `status-handover-agen.md` + `04-work-log/`, lalu menjalankan:
     `C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\periksa_kepatuhan_agen.php`
     dan `C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\pengujian\jalankan_semua_tes.php`
     serta memastikan hasilnya **0 Pelanggaran (LULUS 100%)**.
