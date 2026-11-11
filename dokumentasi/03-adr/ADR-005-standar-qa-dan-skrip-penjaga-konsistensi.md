# ADR-005: Standar Pengujian QA & Skrip Otomatis Penjaga Konsistensi Aturan Agen

- **Status**: Disetujui (*Accepted*)
- **Tanggal**: 2026-11-11
- **Pengambil Keputusan**: Tim Pengembang & Arsitek Sistem

## 1. Konteks dan Permasalahan
Meskipun dokumentasi aturan projek (SoT, SoC, transaksi ACID Rupiah `DECIMAL(15,2)`, keamanan PDO/CSRF/XSS, dan penamaan Bahasa Indonesia) sudah tertulis, pengembangan yang melibatkan banyak sesi atau beberapa AI Agent berisiko mengalami penyimpangan (*drift*) apabila hanya mengandalkan ingatan atau pembacaan manual tanpa verifikasi otomatis. Selain itu, setiap fitur baru memerlukan jaminan kualitas (QA) melalui file pengujian terstandar.

## 2. Keputusan Arsitektur
1. **Kewajiban Pembuatan File Tes QA (`pengujian/`)**:
   - Setiap penambahan atau perubahan logika bisnis di `aplikasi/Layanan/Layanan<Modul>.php` wajib disertai file tes otomatis di `pengujian/Integrasi/TesLayanan<Modul>.php` atau `pengujian/Unit/TesLayanan<Modul>.php`.
   - Nama file tes wajib diawali `Tes` (`PascalCase`), method pengujian diawali `uji` (`camelCase`), dan dilengkapi anotasi `@skenario` dalam Bahasa Indonesia.
2. **Skrip Otomatis Penjaga Konsistensi (`periksa_kepatuhan_agen.php`)**:
   - Menyediakan skrip CLI mandiri di `C:\xampp\htdocs\www\dokumentasi\06-aturan-agen\skrip\periksa_kepatuhan_agen.php` (beserta wrapper PowerShell `periksa-konsistensi.ps1`) yang wajib dijalankan oleh setiap AI Agent sebelum menyatakan tugas selesai.
   - Skrip ini memvalidasi secara statis dan dinamis:
     1. Kelengkapan struktur dokumentasi & sinkronisasi indeks ADR/Work Log.
     2. Larangan nama folder/file/tabel berbahasa Inggris di `bankmini`.
     3. Kepatuhan `declare(strict_types=1);`, anotasi PHPDoc Bahasa Indonesia, dan format variabel `$camelCase`.
     4. Kepatuhan batas SoC (tidak ada query SQL di `Tampilan/` atau `Pengendali/`) dan larangan tipe `FLOAT`/`DOUBLE` pada `skema.sql`.
     5. Ketersediaan file tes QA (`Tes*.php`) untuk setiap class `Layanan*.php` serta keabsahan method `uji*()`.

## 3. Konsekuensi
- **Positif**: Agen AI memiliki gerbang kualitas otomatis (*automated quality gate*) yang langsung memberi tahu baris dan file mana yang melanggar aturan projek sebelum diserahkan ke pengguna.
- **Negatif**: Agen harus memperbaiki seluruh temuan skrip hingga status keluar (*exit code*) bernilai `0` (*Lulus Semua*).

