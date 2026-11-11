# Status Handover & Konteks Aktif Agen (SSOT Lintas Sesi)

- **Pembaruan Terakhir**: 2026-11-11
- **Work Log Terakhir**: [`2026-11-11-scaffold-aplikasi-dan-gate-handover.md`](../04-work-log/2026-11-11-scaffold-aplikasi-dan-gate-handover.md)
- **Fase Projek Saat Ini**: Fase 1 & 2 Selesai — Dokumentasi, Gate Handover, Scaffold Penuh Aplikasi `bankmini`, Suite QA, & Instalasi Database MySQL InnoDB (`bankmini_db`)
- **Status Kepatuhan & QA**: LULUS 100% (1.636 Titik Pemeriksaan, 0 Pelanggaran, 14 Skenario QA Lulus)

---

## 1. Ringkasan Konteks Wajib (Jangan Diubah Tanpa ADR Baru)
- **Bahasa & Konvensi**: 100% Bahasa Indonesia (PSR-12: Class `PascalCase`, method/variabel `camelCase`, tabel/kolom `snake_case`, wajib PHPDoc Bahasa Indonesia).
- **Arsitektur SoT & SoC**: `public/index.php` -> `Pengendali` -> `Layanan` (Batas Transaksi ACID) -> `Model` (PDO Prepared Statements) -> `Tampilan` (`amankanHtml()`, `formatRupiah()`).
- **Standar Keuangan**: Mata uang tunggal **Rupiah (`IDR`)** dengan tipe MySQL InnoDB `DECIMAL(15,2)`, penguncian baris `SELECT ... FOR UPDATE`, dan `mutasi_transaksi` bersifat *Append-Only* (koreksi hanya melalui `LayananTransaksi::prosesJurnalKoreksi()`).

---

## 2. Komponen yang Sudah Selesai & Terverifikasi
- [x] **Pondasi Dokumentasi & 5 ADR** (`C:\xampp\htdocs\www\dokumentasi`)
- [x] **Skrip Pemeriksa Kepatuhan & Gate Handover** (`06-aturan-agen/skrip/periksa_kepatuhan_agen.php`, `gerbang_handover_agen.php`, `periksa-konsistensi.ps1`)
- [x] **Skema, Seeder, & Pemasang Database MySQL InnoDB Rupiah IDR** (`basis_data/skema.sql`, `basis_data/data_awal.sql`, `basis_data/pasang_basis_data.php` — sudah terpasang aktif di `bankmini_db`)
- [x] **Inti Framework PHP Native (`aplikasi/Inti/`)**: `BasisData`, `Sesi`, `TokenCsrf`, `MiddlewareAutentikasi`, `Perute`, `PengendaliDasar`
- [x] **Model (`aplikasi/Model/`)**: `ModelPengguna`, `ModelNasabah`, `ModelProduk`, `ModelRekening` (dengan `FOR UPDATE`), `ModelMutasi` (termasuk filter tanggal), `ModelLogAudit`
- [x] **Layanan Bisnis ACID (`aplikasi/Layanan/`)**: `LayananAutentikasi`, `LayananRekening`, `LayananTransaksi` (Setor, Tarik, Transfer anti-deadlock, Jurnal Koreksi)
- [x] **Pengendali & Tampilan (`aplikasi/Pengendali/`, `aplikasi/Tampilan/`)**: Masuk/Keluar, Dasbor, Nasabah, Produk, Rekening, Setor, Tarik, Transfer, Jurnal Koreksi (`transaksi/koreksi.php`), Laporan Mutasi dengan Filter Tanggal & Cetak (`laporan/mutasi.php`)
- [x] **Suite Pengujian Otomatis QA (`pengujian/`)**: `TesFungsiUmum`, `TesTokenCsrf`, `TesLayananAutentikasi`, `TesLayananRekening`, `TesLayananTransaksi`, `TesKeamananAplikasi`

---

## 3. Tugas Aktif / Sedang Dikerjakan
- [ ] Tidak ada tugas yang menggantung (aplikasi dan database `bankmini_db` sudah siap diakses di browser).

---

## 4. Langkah Selanjutnya (Prioritas Agen Berikutnya)
1. **Portal Khusus Nasabah (`PengendaliPortalNasabah`)**: Menambahkan halaman khusus login nasabah untuk melihat saldo dan riwayat mutasi rekening pribadinya.
2. **Manajemen Pengguna & Produk oleh Admin**: Menambahkan fitur tambah/ubah status pengguna dan produk tabungan melalui antarmuka Admin.
3. **Ekspor Laporan Harian (CSV / Cetak Slip Bukti Transaksi Individual)**: Menambahkan halaman cetak slip bukti per transaksi (`kode_referensi`).

---

## 5. Catatan Konteks Penting & Keputusan Terakhir
- Database `bankmini_db` sudah aktif di MySQL XAMPP lokal dengan akun default:
  - `admin` / `password` (Peran: `admin`)
  - `teller1` / `password` (Peran: `teller`)
  - `supervisor1` / `password` (Peran: `supervisor`)
- Setiap agen baru **wajib** menjalankan `gerbang_handover_agen.php` di awal sesi (Entry Gate) dan memperbarui file `status-handover-agen.md` ini serta menjalankan `periksa_kepatuhan_agen.php` di akhir sesi (Exit Gate).
