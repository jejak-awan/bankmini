# Work Log: Tooling Lint PSR-12, PHPStan, Playwright E2E, & Redesign UI (`v1.3.0`)

- **Tanggal**: 2026-11-12
- **Versi Rilis**: `1.3.0` (Minor Release)
- **Status**: Selesai & Terverifikasi

---

## 1. Ringkasan Perubahan
Memperbaiki cacat visual Bootstrap `.offcanvas-lg` yang menimpa warna latar sidebar menjadi transparan pada layar desktop, meredesain keseluruhan antarmuka agar kontras dan modern, serta menambahkan rangkaian dependensi **Linting PSR-12 (PHPCS)**, **Analisis Statis (PHPStan Level 5)**, dan **Smoke + E2E Browser Test (Playwright)**.

## 2. Daftar Pekerjaan & File yang Terlibat
1. **Perbaikan Fatal & Redesign UI/UX (`public/aset/css/gaya.css`, `public/aset/js/aplikasi.js`, `aplikasi/Tampilan/`, `aplikasi/Pembantu/fungsi_umum.php`)**:
   - Memperbaiki struktur `.offcanvas-lg` -> `.offcanvas-body` di [`bilah_sisi.php`](../../aplikasi/Tampilan/tata_letak/bilah_sisi.php) serta menegaskan background navy gelap pada `.bilah-sisi-bankmini.offcanvas-lg` di [`gaya.css`](../../public/aset/css/gaya.css) agar menu tidak lagi tembus pandang.
   - Menambahkan topbar `.bilah-atas-bankmini`, grup menu sidebar, kartu profil petugas, aksen garis atas KPI di [`dasbor/indeks.php`](../../aplikasi/Tampilan/dasbor/indeks.php), tabel `.tabel-bankmini`, fitur toggle tampil/sembunyi kata sandi (`data-toggle-sandi`) di [`masuk.php`](../../aplikasi/Tampilan/autentikasi/masuk.php) & [`aplikasi.js`](../../public/aset/js/aplikasi.js), dan `min-width: 0` anti overflow.
   - Meningkatkan `urlDasar()` di [`fungsi_umum.php`](../../aplikasi/Pembantu/fungsi_umum.php) agar otomatis mendeteksi origin server aktif saat diakses lewat Apache maupun `php -S`.
2. **Penambahan Tooling Quality Gate (`composer.json`, `phpcs.xml`, `phpstan.neon`, `package.json`, `playwright.config.js`, `pengujian/e2e/`)**:
   - Memasang **Node.js v22.14.0** dan **Composer 2.11.2** lokal di `%LOCALAPPDATA%\Programs\` serta dependensi proyek `phpstan/phpstan`, `squizlabs/php_codesniffer`, dan `@playwright/test`.
   - Merapikan seluruh file PHP mengikuti standar PSR-12 (`phpcbf` + perbaikan header [`fungsi_umum.php`](../../aplikasi/Pembantu/fungsi_umum.php) & [`jalankan_semua_tes.php`](../../pengujian/jalankan_semua_tes.php)).
   - Menambahkan [`smoke.spec.js`](../../pengujian/e2e/smoke.spec.js) dan [`alur_keuangan.spec.js`](../../pengujian/e2e/alur_keuangan.spec.js) untuk memverifikasi aset CSS/JS `200 OK`, kontras sidebar, bebas *overflow*, serta alur end-to-end Nasabah -> Buka Rekening -> Setor/Tarik via Modal Konfirmasi.
   - Memperbarui [`standar-pengujian-qa.md`](../02-panduan-dev/standar-pengujian-qa.md) dan [`standar-frontend-ui-ux.md`](../02-panduan-dev/standar-frontend-ui-ux.md).

## 3. Verifikasi & Penutupan
- Versi dinaikkan dari `1.2.0` -> `1.3.0` melalui `naikkan_versi.php`.
- Diverifikasi lulus 100% pada `phpcs` (32/32 file), `phpstan` Level 5 (32/32 file, 0 errors), `jalankan_semua_tes.php` (14/14 tes), `periksa_kepatuhan_agen.php` (0 pelanggaran), `gerbang_handover_agen.php`, serta `playwright test` (3/3 tes browser lulus + inspeksi visual screenshot).

