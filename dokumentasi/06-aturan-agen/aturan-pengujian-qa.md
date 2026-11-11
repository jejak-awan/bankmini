# Aturan Pembuatan File Tes QA & Verifikasi Konsistensi (Wajib untuk AI Agent)

Setiap AI Agent yang membuat atau memodifikasi fitur di `C:\xampp\htdocs\www\bankmini` **wajib** menyertakan pengujian otomatis untuk Quality Assurance (QA) dan menjalankan skrip penjaga konsistensi sebelum menyelesaikan tugas.

---

## 1. Aturan Wajib Pembuatan File Tes QA (`pengujian/`)

1. **Pasangan 1:1 untuk Lapisan `Layanan/`**:
   - Setiap kali agen membuat class `C:\xampp\htdocs\www\bankmini\aplikasi\Layanan\Layanan<Nama>.php`, agen **wajib** membuat file tes QA di `C:\xampp\htdocs\www\bankmini\pengujian\Integrasi\TesLayanan<Nama>.php` (atau di `pengujian/Unit/TesLayanan<Nama>.php`).
2. **Standar Penamaan & Struktur File Tes (Bahasa Indonesia)**:
   - Nama file dan class tes wajib diawali `Tes` dalam `PascalCase` (contoh: `TesLayananTransaksi.php`).
   - File tes wajib mengaktifkan `declare(strict_types=1);` di baris awal.
   - Setiap method pengujian wajib `public function uji<DeskripsiSkenario>(): void` dalam `camelCase`.
   - Setiap method `uji...()` wajib memiliki blok anotasi PHPDoc berbahasa Indonesia yang memuat tag `@skenario QA-<MODUL>-<NOMOR>` (contoh: `@skenario QA-TRX-001`).
3. **Minimal 4 Skenario Uji untuk Fitur Transaksi Keuangan (Rupiah IDR)**:
   - **Uji Positif**: Memastikan saldo bertambah/berkurang akurat dalam `DECIMAL(15,2)` Rupiah dan tercatat di `mutasi_transaksi`.
   - **Uji Batas Saldo / Negatif**: Memastikan penarikan yang melanggar `saldo_mengendap_minimum` atau nominal `<= 0` ditolak (`DomainException` / `InvalidArgumentException`).
   - **Uji Rollback ACID**: Memastikan saat transaksi gagal, saldo rekening tetap utuh seperti sebelum transaksi.
   - **Uji Keamanan**: Memastikan input berbahaya (XSS/SQLi/CSRF) ditangani sesuai standar.

---

## 2. Kewajiban Menjalankan Skrip Penjaga Konsistensi & Quality Gate

Sebelum melaporkan pekerjaan selesai kepada pengguna, AI Agent **wajib** menjalankan rangkaian *Quality Gate* berikut dari terminal:

```powershell
# 1. Verifikasi Kepatuhan Arsitektur, Anotasi RPL, Prasyarat Aset, & Standar QA
C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\periksa_kepatuhan_agen.php

# 2. Menjalankan Quality Gate PHP terpadu (Kepatuhan + Unit/Integration + PHPCS Zero-Warning + PHPStan Level 8)
powershell -ExecutionPolicy Bypass -File C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\periksa-konsistensi.ps1

# 3. Verifikasi End-to-End & Smoke Test Browser Nyata (Playwright Chromium)
npm run test:e2e
```

### Kriteria Lulus (Quality Gate — Kebijakan Zero Error & Zero Warning)
- **Kebijakan Nol Toleransi (`0 Error & 0 Warning`)**: Tidak boleh ada satu pun *error*, *warning*, PHP `E_NOTICE`/`E_DEPRECATED`, maupun *console error/warning* browser.
- **Kepatuhan & Anotasi (`periksa_kepatuhan_agen.php`)**: Harus **0 Pelanggaran (Exit Code 0)**, mencakup tag `@konsep_rpl`, header dokumentasi di `aplikasi/Tampilan/`, komentar langkah di `Layanan/` & `Pengendali/`, JSDoc/komentar di JS & E2E, serta keberadaan *Git Commit/Push Gate*.
- **Standar Gaya Kode (`composer lint` / `phpcs`)**: Harus **0 Error & 0 Warning PSR-12** (`ignore_warnings_on_exit = 0`) pada `aplikasi/`, `pengujian/`, dan `public/index.php`.
- **Analisis Statis PHPStan Bertahap (`composer stan` / `phpstan`)**: Telah dinaikkan secara bertahap dari Level 5 -> Level 6 -> Level 7 -> **Level 8 (Aktif)** (memastikan ketepatan *nullable* serta `numeric-string` untuk BCMath Rupiah via `keStringNumerik()`), dan wajib lulus **0 Error**. Peningkatan lanjutan ke Level 9–10/max dilakukan bertahap tanpa mengorbankan keterbacaan materi RPL.
- **Unit/Integration (`jalankan_semua_tes.php`)**: Seluruh skenario uji wajib lulus **100%** dengan `error_reporting(E_ALL)` + *error handler* yang menggagalkan setiap *PHP warning/notice/deprecated*.
- **Playwright E2E (`npm run test:e2e`)**: Seluruh skenario UI & alur keuangan wajib lulus **100%** dengan **0 Console Error, 0 Console Warning, & 0 Page Error**.

### Gerbang Pemblokir Commit & Push (`Commit/Push QA Blocker Gate`)
- **DILARANG keras** menjalankan `git commit` maupun `git push` selama masih ada 1 saja pengecekan QA yang gagal, memiliki *error*, *warning*, atau *bug*.
- Projek melindungi repositori via `.githooks/pre-commit` (menjalankan `periksa-konsistensi.ps1`) dan `.githooks/pre-push` (menjalankan `periksa-konsistensi.ps1 -DenganE2E`). Aktifkan hook secara lokal dengan:
```powershell
git config core.hooksPath .githooks
```
- Untuk melakukan rilis aman otomatis (hanya *commit* & *push* jika seluruh QA bersih 100%), gunakan:
```powershell
powershell -ExecutionPolicy Bypass -File C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\gerbang_komit_dan_push.ps1 -PesanCommit "pesan commit" -JalankanPush
```

