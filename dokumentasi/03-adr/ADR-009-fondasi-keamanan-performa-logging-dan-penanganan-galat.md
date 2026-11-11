# ADR-009: Fondasi Keamanan Berlapis, Performa Kueri/Aset, Logging Terpusat, Penanganan Galat, & Pemantauan Kesehatan

| Atribut | Nilai |
| :--- | :--- |
| **Status** | **Disetujui (Accepted)** |
| **Tanggal** | 2026-11-12 |
| **Versi Terkait** | `v1.8.0` |

## Konteks Masalah
Pada versi sebelumnya (`v1.7.0`), aplikasi **Bank Mini** sudah memiliki arsitektur MVC-Lite + Service Layer, keamanan dasar PDO Prepared Statements, Token CSRF + PRG, dan Design System `v1.7.0`. Namun beberapa komponen fondasi operasional, observabilitas, dan pembelajaran produksi masih belum tersedia:
1. Rute tidak ditemukan (`404`) masih mencetak string mentah `<h1>404...</h1>` tanpa tata letak resmi, serta belum ada handler terpusat untuk `403`, `500`, dan `503` (mode pemeliharaan).
2. `mode_debug` di `konfigurasi/aplikasi.php` belum tersambung ke penampil galat global.
3. Belum ada pencatat log harian terpusat yang meredaksi data rahasia (`kata_sandi`, `token_csrf`) dengan *Correlation ID*.
4. Halaman login belum memiliki pembatas laju (*rate limiting*) anti *brute-force* dan sesi belum memiliki *idle timeout*.
5. Header keamanan HTTP (`Content-Security-Policy` ketat `script-src 'self'`, `X-Frame-Options`, `nosniff`), proteksi direktori `.htaccess`, kompresi Gzip/Cache, serta *cache-busting* aset CSS/JS belum diterapkan.
6. `ModelMutasi::ambilMutasiBerdasarkanTanggal()` masih memakai `DATE(m.waktu_transaksi) BETWEEN ...` (non-SARGable) yang melumpuhkan indeks B-Tree, dan belum ada endpoint `/kesehatan`.

## Keputusan Arsitektur
Kami menerapkan **Paket Fondasi Inti `v1.8.0`** yang terdiri atas:
1. **Logging Terpusat & Aman (`Aplikasi\Inti\PencatatLog`)**: Rotasi file harian di `penyimpanan/log/aplikasi-YYYY-MM-DD.log`, dilengkapi `idKorelasi` per permintaan HTTP dan redaksi otomatis kata kunci sensitif menjadi `[DIRAHASIAKAN]`.
2. **Penanganan Galat Global & Header Keamanan (`Aplikasi\Inti\PenanganGalat` + `PengecualianHttp`)**: Menangani HTTP `403`, `404`, `500`, `503` menggunakan template Design System `aplikasi/Tampilan/galat/halaman_galat.php`, menampilkan stack trace **hanya** saat `mode_debug = true`, serta mengirim header OWASP termasuk **CSP ketat `script-src 'self'`** (bebas `onclick=` inline).
3. **Pertahanan Berlapis Sesi & Login (`PembatasLaju`, `Sesi`, `MiddlewareAutentikasi`)**: Pembatas laju maksimal 5 kali gagal per 5 menit, *idle timeout* sesi 30 menit (`1800` detik), flag cookie `secure` otomatis saat HTTPS, dan `.htaccess` deny pada seluruh folder non-publik.
4. **Optimasi Performa & Observabilitas**: Indeks database baru (`idx_nasabah_nama`, `idx_rekening_status`, `idx_mutasi_waktu`), refactor kueri SARGable pada `ModelMutasi`, *cache-busting* `?v=VERSI`, Gzip/Expires di `public/.htaccess`, dan endpoint `GET /kesehatan` (terhubung juga dengan `HEALTHCHECK` Docker).

## Konsekuensi & Langkah Siswa RPL
- Seluruh tombol interaktif di HTML (`aplikasi/Tampilan/`) dilarang memakai atribut event inline (`onclick=...`), melainkan wajib menggunakan atribut `data-*` yang ditangani oleh `inti-ui.js` / `dasbor-interaktif.js` agar CSP ketat `script-src 'self'` tidak pernah memicu pelanggaran.
- Kueri rentang tanggal wajib menggunakan perbandingan batas waktu (`>= YYYY-MM-DD 00:00:00 AND <= YYYY-MM-DD 23:59:59`) agar MySQL InnoDB dapat memanfaatkan indeks B-Tree secara optimal.

