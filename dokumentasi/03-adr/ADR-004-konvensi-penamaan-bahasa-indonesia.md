# ADR-004: Konvensi Penamaan, Struktur Folder, Tabel, & Anotasi dalam Bahasa Indonesia

- **Status**: Disetujui (*Accepted*)
- **Tanggal**: 2026-11-11
- **Pengambil Keputusan**: Tim Pengembang & Arsitek Sistem

## 1. Konteks dan Permasalahan
Dalam pengembangan aplikasi **Bank Mini**, pencampuran bahasa Inggris dan bahasa Indonesia pada nama folder, class, variabel, maupun tabel database (misalnya mencampur `users` dengan `nasabah`, atau `getRekeningById()` dengan `ambilSaldo()`) sering menimbulkan kebingungan dan inkonsistensi di antara pengembang maupun agen AI.

## 2. Keputusan Arsitektur
Menetapkan **Bahasa Indonesia** sebagai bahasa standar tunggal untuk seluruh penamaan domain kode, struktur direktori/file, skema database, dan anotasi dokumentasi kode dengan format penulisan mengikuti standar **PSR-12**:

1. **Folder & File**:
   - Folder infrastruktur & modul tampilan menggunakan `snake_case` Bahasa Indonesia (`konfigurasi/`, `basis_data/`, `penyimpanan/`, `tata_letak/`).
   - Folder lapisan class di dalam `aplikasi/` menggunakan `PascalCase` Bahasa Indonesia (`aplikasi/Inti/`, `aplikasi/Pengendali/`, `aplikasi/Layanan/`, `aplikasi/Model/`, `aplikasi/Pembantu/`, `aplikasi/Tampilan/`).
   - Pengecualian hanya untuk `public/index.php` dan `.htaccess` karena merupakan konvensi bawaan web server Apache.
2. **Class, Method, Variabel, & Konstanta**:
   - Class: `PascalCase` Bahasa Indonesia (`LayananTransaksi`, `ModelRekening`, `PengendaliNasabah`).
   - Method & Fungsi: `camelCase` Bahasa Indonesia dengan kata kerja aktif (`prosesSetorTunai()`, `kunciRekeningUntukPembaruan()`, `amankanHtml()`, `formatRupiah()`).
   - Variabel: `$camelCase` Bahasa Indonesia yang utuh dan deskriptif (`$nominalSetoran`, `$saldoSebelum`, `$saldoSesudah`, `$idRekening`).
   - Konstanta: `UPPER_SNAKE_CASE` Bahasa Indonesia (`MATA_UANG_RUPIAH`).
3. **Tabel & Kolom Database**:
   - Seluruh tabel dan kolom menggunakan `snake_case` Bahasa Indonesia (`pengguna`, `nasabah`, `produk_tabungan`, `rekening`, `mutasi_transaksi`, `log_audit`; kolom `hash_kata_sandi`, `dibuat_pada`, `diperbarui_pada`, `id_rekening`, `id_petugas`).
4. **Anotasi (PHPDoc & Komentar)**:
   - Seluruh blok PHPDoc (`@param`, `@return`, `@throws`) dan komentar alur logika bisnis wajib ditulis dalam Bahasa Indonesia yang jelas.

## 3. Konsekuensi
- **Positif**: Istilah bisnis perbankan (setor, tarik, saldo mengendap, mutasi, rekening, petugas) selaras 100% dari tampilan pengguna, kode PHP, hingga tabel database tanpa kebingungan translasi istilah.
- **Negatif / Mitigasi**: Pengembang dan AI Agent wajib merujuk ke [`02-panduan-dev/konvensi-penamaan-dan-anotasi.md`](../02-panduan-dev/konvensi-penamaan-dan-anotasi.md) agar tidak menggunakan istilah bahasa Inggris (`users`, `created_at`, `Controller`, `Service`) saat menambah fitur baru.

