# Aturan Agen AI (Agent Rules & Guardrails)

Folder ini berisi instruksi, batasan, dan skrip pemeriksaan otomatis (**wajib dipatuhi**) oleh setiap AI Coding Agent yang bekerja pada projek **Bank Mini** (`C:\xampp\htdocs\www\bankmini`) dan dokumentasinya (`C:\xampp\htdocs\www\dokumentasi`).

## Daftar Aturan & Skrip Wajib
1. [**Aturan Koding PHP, Konvensi Bahasa Indonesia, & Integritas Finansial (`aturan-koding-php-bankmini.md`)**](./aturan-koding-php-bankmini.md)
   - Aturan mutlak penulisan kode PHP Native berbahasa Indonesia, SoT/SoC, transaksi database ACID Rupiah (`IDR`), dan keamanan.
2. [**Aturan Pembuatan File Tes QA & Verifikasi Konsistensi (`aturan-pengujian-qa.md`)**](./aturan-pengujian-qa.md)
   - Standar pembuatan file tes otomatis QA (`pengujian/Unit/`, `pengujian/Integrasi/`, `pengujian/Keamanan/`) dan kewajiban eksekusi skrip penjaga konsistensi.
3. [**Alur Kerja Sinkronisasi Dokumentasi (`alur-kerja-dokumentasi.md`)**](./alur-kerja-dokumentasi.md)
   - Prosedur wajib sebelum dan sesudah agen menyelesaikan tugas pemrograman.
4. [**Protokol Gate Agen Baru & Handover Lintas Sesi (`protokol-handover-dan-gate-agen.md`)**](./protokol-handover-dan-gate-agen.md)
   - Mekanisme *Entry Gate* (saat agen baru masuk) dan *Exit/Handover Gate* (sebelum agen menutup sesi) beserta manifest konteks aktif [`status-handover-agen.md`](./status-handover-agen.md) dan skrip [`skrip/gerbang_handover_agen.php`](./skrip/gerbang_handover_agen.php).
5. [**Skrip Otomatis Penjaga Konsistensi (`skrip/periksa_kepatuhan_agen.php`)**](./skrip/periksa_kepatuhan_agen.php)
   - Linter & validator otomatis untuk memeriksa kepatuhan aturan projek, SoT/SoC, konvensi Bahasa Indonesia, keamanan SQL/ACID, kelengkapan tes QA, dan sinkronisasi dokumentasi.

## Protokol Eksekusi Agen (Checklist Wajib)
1. **Baca Konteks & Konvensi Bahasa Indonesia**: Periksa [`02-panduan-dev/arsitektur-sistem.md`](../02-panduan-dev/arsitektur-sistem.md), [`02-panduan-dev/konvensi-penamaan-dan-anotasi.md`](../02-panduan-dev/konvensi-penamaan-dan-anotasi.md), [`02-panduan-dev/skema-database.md`](../02-panduan-dev/skema-database.md), dan [`03-adr/`](../03-adr/README.md).
2. **Patuhi Guardrails Finansial (Rupiah IDR & ACID)**: Pastikan setiap operasi yang menyentuh saldo atau mutasi menggunakan PDO Transaction, tipe `DECIMAL(15,2)` Rupiah, dan `SELECT ... FOR UPDATE`.
3. **Buat File Tes QA (`Tes*.php`)**: Buat atau perbarui pengujian di `C:\xampp\htdocs\www\bankmini\pengujian\` sesuai [`aturan-pengujian-qa.md`](./aturan-pengujian-qa.md).
4. **Update Dokumentasi & Work Log**: Catat perubahan ke dalam `04-work-log/` dan perbarui panduan terkait.
5. **Jalankan Skrip Penjaga Konsistensi**: Eksekusi `php C:\xampp\htdocs\www\dokumentasi\06-aturan-agen\skrip\periksa_kepatuhan_agen.php` dan pastikan menghasilkan **0 Pelanggaran**.
