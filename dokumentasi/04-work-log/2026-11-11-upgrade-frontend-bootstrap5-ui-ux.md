# Work Log: Upgrade Frontend & UI/UX Bootstrap 5 Offline + Vanilla JS (`v1.2.0`)

- **Tanggal**: 2026-11-11
- **Versi Rilis**: `1.2.0` (Minor Release)
- **Status**: Selesai & Terverifikasi

---

## 1. Ringkasan Perubahan
Meningkatkan arsitektur antarmuka (**Frontend**) dan pengalaman pengguna (**UI/UX**) aplikasi **Bank Mini** (jenjang SMK RPL) agar tetap berjalan **100% offline** tanpa Node.js atau CDN, sambil menerapkan fitur pencegahan kesalahan input (*human error prevention*) standar aplikasi perbankan.

## 2. Daftar Pekerjaan & File yang Terlibat
1. **Instalasi Aset Offline (`public/aset/vendor/`)**:
   - Menambahkan **Bootstrap 5.3.3** (`bootstrap.min.css`, `bootstrap.bundle.min.js`) dan **Bootstrap Icons 1.11.3** (`bootstrap-icons.min.css`, `.woff`, `.woff2`) secara lokal.
2. **Tema & Interaksi Perbankan (`public/aset/css/gaya.css`, `public/aset/js/aplikasi.js`, `aplikasi/Inti/PengendaliDasar.php`)**:
   - Memperbarui [`PengendaliDasar.php`](../../aplikasi/Inti/PengendaliDasar.php) (`tampilkanTampilan()`) untuk menyuntikkan `$versiAplikasi`, `$namaAplikasi`, `$namaPenggunaAktif`, `$peranPenggunaAktif`, dan `$tampilanAktif`.
   - Memperbarui [`gaya.css`](../../public/aset/css/gaya.css) dengan tema biru perbankan, `.angka-keuangan` (`tabular-nums`), komponen kartu/bilah sisi, serta aturan `@media print` (`.tidak-cetak`, `.hanya-cetak`).
   - Memperbarui [`aplikasi.js`](../../public/aset/js/aplikasi.js) untuk pratinjau Rupiah *real-time* (`data-pratinjau-rupiah`), pencarian dropdown (`data-cari-select`), info Saldo & Saldo Efektif (`data-target-info-saldo`), filter tabel (`data-cari-tabel`), pencegahan *double submit*, dan modal konfirmasi transaksi XSS-safe (`#modalKonfirmasiTransaksi`).
3. **Pembaruan Seluruh Tata Letak & Halaman (`aplikasi/Tampilan/`)**:
   - Tata letak responsif (`offcanvas-lg`) pada `tata_letak/kepala.php`, `bilah_sisi.php`, `kaki.php`, serta kartu login `autentikasi/masuk.php`.
   - Dasbor KPI + aksi cepat & tabel mutasi (`dasbor/indeks.php`), 2 kolom nasabah & filter tabel (`nasabah/indeks.php`), tabel produk tabular (`produk/indeks.php`), pembukaan rekening + konfirmasi (`rekening/indeks.php`).
   - Formulir Setor Tunai, Tarik Tunai, Transfer, dan Jurnal Koreksi (`transaksi/*.php`) lengkap dengan pencarian rekening, saldo efektif *real-time*, pratinjau Rupiah, dan modal konfirmasi.
   - Laporan Mutasi siap cetak (`laporan/mutasi.php`) dengan kop cetak dan filter instan.
4. **Dokumentasi, ADR, & Panduan**:
   - Menambahkan [`ADR-007-arsitektur-frontend-dan-standar-ui-ux.md`](../03-adr/ADR-007-arsitektur-frontend-dan-standar-ui-ux.md) & mendaftarkannya di `03-adr/README.md`.
   - Menambahkan [`standar-frontend-ui-ux.md`](../02-panduan-dev/standar-frontend-ui-ux.md) & memperbarui `02-panduan-dev/README.md`.
   - Memperbarui [`panduan-teller.md`](../01-panduan-pengguna/panduan-teller.md) & [`panduan-admin.md`](../01-panduan-pengguna/panduan-admin.md).
   - Mendaftarkan kedua dokumen baru ke `$fileDokumentasiWajib` pada `periksa_kepatuhan_agen.php`.

## 3. Verifikasi & Penutupan
- Versi dinaikkan dari `1.1.1` -> `1.2.0` via `naikkan_versi.php`.
- Diverifikasi dengan `periksa_kepatuhan_agen.php`, `jalankan_semua_tes.php`, dan `gerbang_handover_agen.php`.

