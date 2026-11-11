# Work Log: Fondasi Keamanan Berlapis, Performa Kueri/Aset, Logging Terpusat, Penanganan Galat, & Observabilitas (`v1.8.0`)

- **Tanggal**: 2026-11-12
- **Versi Terkait**: `1.8.0`
- **ADR Terkait**: [ADR-009](../03-adr/ADR-009-fondasi-keamanan-performa-logging-dan-penanganan-galat.md)
- **Status**: **Selesai & Lulus Seluruh Gerbang QA (100%)**

---

## Ringkasan Pekerjaan

1. **Pencatatan Log Terpusat & Aman (`aplikasi/Inti/PencatatLog.php`)**
   - Menambahkan logger rotasi harian (`penyimpanan/log/aplikasi-YYYY-MM-DD.log`) dengan tingkat `INFO`, `PERINGATAN`, `GALAT`, dan `KEAMANAN`.
   - Menghasilkan *Correlation ID* (`req-YYYYMMDD-...`) di setiap siklus permintaan HTTP untuk memudahkan penelusuran insiden pengguna di log server.
   - Menyensor kunci sensitif (`kata_sandi`, `hash_kata_sandi`, `token_csrf`, `token`, `rahasia`) secara rekursif menjadi `[DIRAHASIAKAN]`.

2. **Penanganan Galat Terpusat & Halaman Galat Kustom (`PenanganGalat.php`, `PengecualianHttp.php`, `aplikasi/Tampilan/galat/halaman_galat.php`)**
   - Menggantikan output `404` mentah di `Perute` dan error `403` mentah di `MiddlewareAutentikasi` menggunakan `PengecualianHttp` dan template Design System yang ramah pengguna.
   - Menghubungkan sakelar `mode_debug` dari `konfigurasi/aplikasi.php` (`true` menampilkan jejak debug edukatif untuk siswa RPL; `false` menyembunyikan detail SQL/path server demi keamanan).
   - Menambahkan dukungan **Mode Pemeliharaan (`503`)** melalui berkas penanda `penyimpanan/pemeliharaan.flag`.

3. **Keamanan Berlapis (`CSP`, Security Headers, Brute-Force Limiter, Idle Timeout, & `.htaccess`)**
   - Mengirimkan header OWASP (`X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, dan `Content-Security-Policy` ketat `script-src 'self'`).
   - Menghapus `onclick="window.print();"` di `laporan/mutasi.php` dan menggantinya dengan `data-cetak-halaman="ya"` yang ditangani oleh `inti-ui.js` agar CSP `script-src 'self'` lulus dengan 0 pelanggaran.
   - Menambahkan `PembatasLaju` di `LayananAutentikasi` (maks 5 kali gagal per 5 menit), *Idle Timeout* 30 menit + flag `secure` otomatis di `Sesi`, dan `.htaccess` pemblokir akses HTTP pada `aplikasi/`, `konfigurasi/`, `penyimpanan/`, `basis_data/`, `pengujian/`.

4. **Optimasi Performa Database/Aset & Health Check (`/kesehatan`)**
   - Menambahkan indeks B-Tree (`idx_nasabah_nama`, `idx_rekening_status`, `idx_mutasi_waktu`) di `basis_data/skema.sql` dan merefaktor `ModelMutasi::ambilMutasiBerdasarkanTanggal()` menjadi kueri **SARGable**.
   - Menambahkan *cache-busting* `?v=VERSI` di seluruh tata letak HTML, `mod_deflate` + `mod_expires` di `public/.htaccess`, serta endpoint JSON `GET /kesehatan` dan `HEALTHCHECK` di `Dockerfile`.

5. **Dokumentasi Pembelajaran RPL & Suite QA (`v1.8.0`)**
   - Memperbarui [`standar-kode-dan-keamanan.md`](../02-panduan-dev/standar-kode-dan-keamanan.md), [`arsitektur-sistem.md`](../02-panduan-dev/arsitektur-sistem.md), [`skema-database.md`](../02-panduan-dev/skema-database.md), [`glosarium-dan-daftar-istilah.md`](../02-panduan-dev/glosarium-dan-daftar-istilah.md), serta menambahkan `ADR-009`.
   - Menambahkan 6 skenario unit/keamanan di [`TesFondasiSistem.php`](../../pengujian/Unit/TesFondasiSistem.php) (`QA-FND-001` s.d. `QA-FND-006`) serta verifikasi Playwright `/kesehatan`, halaman `404`, dan header CSP di [`smoke.spec.js`](../../pengujian/e2e/smoke.spec.js).

