# Work Log: Penambahan Dependensi & Layanan Dokumen PDF, Excel, Word, & Cetak (`v1.6.0`)

- **Tanggal**: 2026-11-12
- **Versi Rilis**: `1.6.0` (Minor Release)
- **Status**: Selesai & Terverifikasi

---

## 1. Ringkasan Perubahan
Menambahkan rangkaian dependensi Composer produksi untuk pemrosesan dokumen **PDF** (`dompdf/dompdf`), **Excel/CSV** (`phpoffice/phpspreadsheet`, `shuchkin/simplexlsxgen`, `shuchkin/simplexlsx`), dan **Word** (`phpoffice/phpword`), mengintegrasikan `vendor/autoload.php` pada Front Controller dan Test Runner, serta membangun layanan terpusat [`LayananDokumenKantor.php`](../../aplikasi/Layanan/LayananDokumenKantor.php) dengan *fallback* otomatis (`ext-zlib` / `PCLZip`) agar fitur *Export*, *Import*, dan *Print* siap dibangun tanpa terkendala konfigurasi `php.ini` di laptop siswa.

## 2. Daftar Pekerjaan & File yang Terlibat
1. **Penambahan Dependensi Composer & Konfigurasi Kontainer (`composer.json`, `composer.lock`, `Dockerfile`, `public/index.php`, `pengujian/jalankan_semua_tes.php`)**:
   - Memasang paket produksi `dompdf/dompdf` (`^3.1`), `phpoffice/phpspreadsheet` (`^5.11`), `phpoffice/phpword` (`^1.4`), `shuchkin/simplexlsxgen` (`^1.5`), dan `shuchkin/simplexlsx` (`^1.1`).
   - Menambahkan konfigurasi `platform` pada [`composer.json`](../../composer.json) serta ekstensi `gd` dan `zip` pada [`Dockerfile`](../../Dockerfile).
   - Memuat `vendor/autoload.php` secara otomatis di [`public/index.php`](../../public/index.php) dan [`pengujian/jalankan_semua_tes.php`](../../pengujian/jalankan_semua_tes.php).
2. **Pembangunan Layanan Adapter Dokumen (`aplikasi/Layanan/LayananDokumenKantor.php`)**:
   - Menyediakan method siap pakai: `periksaKesiapanPustaka()`, `buatPdfDariHtml()`, `buatExcelDariTabel()`, `bacaDataDariExcel()`, `buatCsvDariTabel()`, `bacaDataDariCsv()`, `buatDokumenWord()`, dan `siapkanHtmlCetak()`.
   - Menjaga presisi untai numerik (`0...` untuk NIS/NIK dan desimal Rupiah `.00`) saat ekspor/impor Excel serta mengaktifkan fallback otomatis `PengaturanWord::PCLZIP` apabila ekstensi `ZipArchive` belum diaktifkan di `php.ini`.
3. **Pengujian Otomatis & Penguatan Gate (`pengujian/Unit/TesLayananDokumenKantor.php`, `gerbang_handover_agen.php`, `periksa_kepatuhan_agen.php`)**:
   - Menambahkan skenario `QA-UNIT-006` (verifikasi biner `%PDF-`), `QA-UNIT-007` (round-trip ekspor & impor `.xlsx` dan `.csv` dengan pelestarian presisi Rupiah), serta `QA-UNIT-008` (pembuatan dokumen `.docx` OpenXML & template HTML cetak).
   - Memperbarui [`gerbang_handover_agen.php`](../06-aturan-agen/skrip/gerbang_handover_agen.php), [`periksa_kepatuhan_agen.php`](../06-aturan-agen/skrip/periksa_kepatuhan_agen.php), dan [`arsitektur-sistem.md`](../02-panduan-dev/arsitektur-sistem.md).

## 3. Verifikasi & Penutupan
- Versi dinaikkan dari `1.5.0` -> `1.6.0` melalui `naikkan_versi.php`.
- Diverifikasi lulus 100% pada `periksa_kepatuhan_agen.php` (`0` pelanggaran), `jalankan_semua_tes.php` (`20/20` skenario lulus, `0` warning), `phpcs` (`37/37` file lulus PSR-12 `0` warning), `phpstan` Level 8 (`37/37` file, `0` error), dan `playwright test` (`3/3` skenario E2E lulus).

