# Work Log: Modul Pusat Dokumentasi & Materi RPL Terindeks di Browser (`v1.5.0`)

- **Tanggal**: 2026-11-12
- **Versi Terkait**: `1.5.0`
- **Status**: Selesai & Terverifikasi 100%

---

## Ringkasan Pekerjaan
Membangun modul **Pusat Dokumentasi & Materi RPL (`/dokumentasi`)** yang terintegrasi langsung di dalam aplikasi web Bank Mini agar seluruh dokumentasi di folder `dokumentasi/` (`00` s.d. `06`) dapat dibuka, diindeks, dicari kata kuncinya, dan dicetak ke PDF langsung dari browser secara luring (*offline*) tanpa keluar dari aplikasi.

## Perubahan yang Dilakukan
1. **Lapisan Layanan & Keamanan Anti-Path Traversal (`aplikasi/Layanan/LayananDokumentasi.php`)**:
   - Mengindeks otomatis seluruh berkas `.md` di `dokumentasi/` berdasarkan kategori (`00-ikhtisar`, `01-panduan-pengguna`, `02-panduan-dev`, `03-adr`, `04-work-log`, `05-template-dokumentasi`, `06-aturan-agen`).
   - Menyediakan konverter Markdown ke HTML Bootstrap 5 (heading ber-anchor, daftar isi otomatis *On This Page*, tabel bergaris, blok kode & diagram, *GitHub Alert* `[!NOTE]`/`[!TIP]`/`[!IMPORTANT]`/`[!WARNING]`/`[!CAUTION]`, serta resolusi otomatis tautan antar-berkas `.md`).
   - Menyediakan mesin pencarian kata kunci lintas dokumen (`cariDokumen()`) beserta cuplikan konteks.
   - Menerapkan validasi keamanan berlapis (`realpath()` boundary check & regex whitelist) untuk menolak serangan *Directory/Path Traversal* (`../`).
2. **Pengendali, Tampilan 3-Kolom, & Navigasi Sidebar**:
   - Menambahkan [`PengendaliDokumentasi.php`](../../aplikasi/Pengendali/PengendaliDokumentasi.php), tampilan 3-kolom [`aplikasi/Tampilan/dokumentasi/indeks.php`](../../aplikasi/Tampilan/dokumentasi/indeks.php), pendaftaran rute `GET /dokumentasi` di [`public/index.php`](../../public/index.php), dan menu **Pusat Dokumentasi** di [`bilah_sisi.php`](../../aplikasi/Tampilan/tata_letak/bilah_sisi.php).
3. **Pengujian Otomatis QA (1:1 Layanan & E2E Playwright)**:
   - Menambahkan [`TesLayananDokumentasi.php`](../../pengujian/Integrasi/TesLayananDokumentasi.php) (`QA-INT-009` & `QA-INT-010`) dan mendaftarkannya di [`jalankan_semua_tes.php`](../../pengujian/jalankan_semua_tes.php).
   - Menambahkan pengujian rute `/dokumentasi` dan pencarian kata kunci `?cari=ACID` pada [`smoke.spec.js`](../../pengujian/e2e/smoke.spec.js).
4. **Pembaruan Panduan Pengguna & Panduan Developer**:
   - Memperbarui [`panduan-admin.md`](../01-panduan-pengguna/panduan-admin.md), [`panduan-teller.md`](../01-panduan-pengguna/panduan-teller.md), dan [`arsitektur-sistem.md`](../02-panduan-dev/arsitektur-sistem.md).

## Verifikasi Akhir
- `periksa_kepatuhan_agen.php`: 3011 titik pemeriksaan — 0 Pelanggaran (Lulus 100%).
- `phpcs` (Zero Warning): 35 / 35 file (100%).
- `phpstan` (**Level 8** Zero Error): 35 / 35 file — `[OK] No errors`.
- `jalankan_semua_tes.php` (`E_ALL` strict): 17 / 17 skenario Lulus.
- `playwright test` (Smoke & E2E termasuk `/dokumentasi`): 3 / 3 passed (`0 Console Error & 0 Warning`).
