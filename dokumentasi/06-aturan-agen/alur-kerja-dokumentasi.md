# Alur Kerja Sinkronisasi Dokumentasi & Manajemen Versi (SOP AI Agent)

Dokumentasi di `C:\xampp\htdocs\www\bankmini\dokumentasi` harus selalu sinkron 100% dengan kode di `C:\xampp\htdocs\www\bankmini`. Setiap AI Agent wajib mengikuti alur kerja ini:

## 1. Sebelum Memulai Tugas (Pre-Flight / Entry Gate)
1. Jalankan *Entry Gate* `C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\gerbang_handover_agen.php` atau baca `06-aturan-agen/status-handover-agen.md` dan `04-work-log/README.md`.
2. Jika tugas berkaitan dengan database atau alur transaksi, periksa `02-panduan-dev/skema-database.md`, `02-panduan-dev/dfd-dan-alur-sistem.md`, dan `03-adr/`.

## 2. Saat Menulis Kode & Mengambil Keputusan Arsitektur Baru
1. **Komentar Edukasi Siswa RPL (`@konsep_rpl`)**: Setiap class di `aplikasi/` wajib memiliki tag `@konsep_rpl` pada PHPDoc-nya, dan setiap blok logika penting wajib disertai komentar edukatif yang menjelaskan *mengapa* kode tersebut ditulis demikian.
2. **ADR Baru**: Apabila terdapat perubahan arsitektur, penambahan tabel baru, atau kebijakan teknis baru, buat dokumen ADR baru di `03-adr/` menggunakan `05-template-dokumentasi/template-adr.md` dan perbarui `03-adr/README.md`.

## 3. Setelah Menyelesaikan Tugas (Post-Flight Definition of Done / Exit Gate)
Sebuah tugas **belum dianggap selesai** sebelum agen melakukan seluruh langkah berikut:
1. **Buat / Perbarui File Tes QA (`pengujian/Tes*.php`)** untuk setiap komponen `Layanan` atau fitur yang dibangun sesuai [`aturan-pengujian-qa.md`](./aturan-pengujian-qa.md).
2. **Wajib Perbarui Panduan Pengguna (`01-panduan-pengguna/`) & Panduan Dev (`02-panduan-dev/`)** agar selalu mencerminkan fitur, alur, DFD/ERD, dan skema terbaru.
3. **Naikkan Versi (*Bump Version*) & Perbarui `CHANGELOG.md`**:
   - Jalankan `C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\naikkan_versi.php <major|minor|patch> "Ringkasan perubahan"` atau perbarui `VERSION` dan `CHANGELOG.md`.
4. **Catat Work Log & Status Handover**:
   - Buat/perbarui log di `04-work-log/` dan daftarkan di `04-work-log/README.md`.
   - Perbarui `06-aturan-agen/status-handover-agen.md`.
5. **Jalankan Skrip Penjaga Konsistensi & Suite QA**:
   ```powershell
   C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\periksa_kepatuhan_agen.php
   C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\pengujian\jalankan_semua_tes.php
   ```
   Pastikan output menunjukkan status **LULUS 100% (0 Pelanggaran)**.
