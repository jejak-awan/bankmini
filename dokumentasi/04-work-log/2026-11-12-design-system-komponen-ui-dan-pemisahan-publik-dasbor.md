# Work Log: Design System Berlapis, Komponen UI Bersama, & Pemisahan UI Publik vs Dasbor (`v1.7.0`)

- **Tanggal**: 2026-11-12
- **Versi Rilis**: `1.7.0` (Minor Release)
- **Status**: Selesai & Terverifikasi

---

## 1. Ringkasan Perubahan
Menerapkan strategi arsitektur frontend modern di PHP Native melalui pemecahan **Design Tokens & Tipografi**, **Slicing Komponen UI Bersama (`aplikasi/Tampilan/komponen/` via `komponenUi()`)**, serta **Pemisahan Tata Letak dan Aset Publik vs Dasbor (`'publik'`, `'dasbor'`, `'cetak'`)** lengkap dengan rute Portal Informasi Publik **`GET /portal`**, sehingga halaman publik tidak lagi membawa beban CSS sidebar, modal transaksi, maupun JavaScript operasional dasbor admin/teller.

## 2. Daftar Pekerjaan & File yang Terlibat
1. **Design System Berlapis & Code Splitting Aset (`public/aset/css/` & `public/aset/js/`)**:
   - Memecah `gaya.css` dan `aplikasi.js` monolitik menjadi:
     - Shared: [`token-desain.css`](../../public/aset/css/token-desain.css), [`komponen-ui.css`](../../public/aset/css/komponen-ui.css), [`inti-ui.js`](../../public/aset/js/inti-ui.js).
     - Public Scope: [`publik.css`](../../public/aset/css/publik.css).
     - Dashboard Scope: [`dasbor.css`](../../public/aset/css/dasbor.css), [`dasbor-interaktif.js`](../../public/aset/js/dasbor-interaktif.js).
2. **Slicing Komponen UI Terstandarisasi (`aplikasi/Pembantu/fungsi_umum.php`, `aplikasi/Tampilan/komponen/`)**:
   - Menambahkan helper `komponenUi()` dengan *closure isolation*, `ob_start()`, serta validasi anti-traversal nama komponen.
   - Membuat 6 komponen standar: `pesan_kilat.php`, `kartu_kpi.php`, `lencana_status.php`, `kepala_kartu.php`, `pencarian_tabel.php`, dan `kop_surat.php`, serta mengaplikasikannya ke `dasbor/indeks.php`, `nasabah/indeks.php`, `rekening/indeks.php`, `laporan/mutasi.php`, `tata_letak/bilah_sisi.php`, dan `autentikasi/masuk.php`.
3. **Pemisahan Multi-Shell Layout & Portal Publik (`PengendaliDasar.php`, `PengendaliPublik.php`, `tata_letak/`)**:
   - Memperbarui [`PengendaliDasar::tampilkanTampilan()`](../../aplikasi/Inti/PengendaliDasar.php) untuk mendukung shell `'dasbor'`, `'publik'`, dan `'cetak'` menggunakan `kepala_dasbor.php`/`kaki_dasbor.php`, `kepala_publik.php`/`kaki_publik.php`, serta `kepala_cetak.php`/`kaki_cetak.php`.
   - Menambahkan [`PengendaliPublik.php`](../../aplikasi/Pengendali/PengendaliPublik.php), [`publik/portal.php`](../../aplikasi/Tampilan/publik/portal.php), dan rute `GET /portal` di [`public/index.php`](../../public/index.php).
4. **Pengujian QA Unit, E2E Playwright, & Dokumentasi**:
   - Menambahkan `QA-UNT-005` (`ujiKomponenUiMerenderDanMenolakPathTraversal()`) pada [`TesFungsiUmum.php`](../../pengujian/Unit/TesFungsiUmum.php).
   - Memperluas [`smoke.spec.js`](../../pengujian/e2e/smoke.spec.js) untuk memverifikasi secara langsung bahwa `/portal` dan `/masuk` memuat aset publik (`200 OK`) dan **0 byte** `dasbor.css` / `dasbor-interaktif.js`.
   - Memperbarui [`standar-frontend-ui-ux.md`](../02-panduan-dev/standar-frontend-ui-ux.md), [`arsitektur-sistem.md`](../02-panduan-dev/arsitektur-sistem.md), dan [`periksa_kepatuhan_agen.php`](../06-aturan-agen/skrip/periksa_kepatuhan_agen.php).

## 3. Verifikasi & Penutupan
- Versi dinaikkan dari `1.6.0` -> `1.7.0`.
- Diverifikasi lulus 100% pada `periksa_kepatuhan_agen.php` (`0` pelanggaran), `jalankan_semua_tes.php` (`21/21` skenario lulus), `phpcs` Zero Warning, `phpstan` Level 8 (`0` error), dan Playwright E2E (`3/3` skenario lulus).

