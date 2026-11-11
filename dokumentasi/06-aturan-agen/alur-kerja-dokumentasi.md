# Alur Kerja Sinkronisasi Dokumentasi (SOP AI Agent)

Dokumentasi di `C:\xampp\htdocs\www\dokumentasi` harus selalu sinkron 100% dengan kode di `C:\xampp\htdocs\www\bankmini`. Setiap AI Agent wajib mengikuti alur kerja ini:

## 1. Sebelum Memulai Tugas (Pre-Flight)
1. Baca `04-work-log/README.md` dan log terakhir untuk memahami status terkini projek.
2. Jika tugas berkaitan dengan database atau alur transaksi, periksa `02-panduan-dev/skema-database.md` dan `03-adr/`.

## 2. Saat Mengambil Keputusan Arsitektur Baru
- Apabila terdapat perubahan struktur folder, penambahan tabel baru, atau perubahan mekanisme keamanan/keuangan, buat dokumen ADR baru di `03-adr/` menggunakan `05-template-dokumentasi/template-adr.md` dan perbarui `03-adr/README.md`.

## 3. Setelah Menyelesaikan Tugas (Post-Flight Definition of Done)
Sebuah tugas **belum dianggap selesai** sebelum agen melakukan langkah berikut:
1. **Buat / Perbarui File Tes QA (`pengujian/Tes*.php`)** untuk setiap komponen `Layanan` atau fitur yang dibangun sesuai [`aturan-pengujian-qa.md`](./aturan-pengujian-qa.md).
2. **Perbarui Panduan Dev** (`02-panduan-dev/`) jika ada penambahan tabel, perubahan skema, atau komponen arsitektur baru.
3. **Perbarui Panduan Pengguna** (`01-panduan-pengguna/`) jika fitur baru menambah atau mengubah alur kerja Admin, Teller, atau Nasabah.
4. **Catat Work Log** di `04-work-log/` menggunakan format `YYYY-MM-DD-ringkasan-pekerjaan.md` (mengacu pada `05-template-dokumentasi/template-work-log.md`) dan perbarui tabel indeks di `04-work-log/README.md`.
5. **Jalankan Skrip Penjaga Konsistensi**:
   ```powershell
   php C:\xampp\htdocs\www\dokumentasi\06-aturan-agen\skrip\periksa_kepatuhan_agen.php
   ```
   Pastikan output menunjukkan status **LULUS (0 Pelanggaran)**.

