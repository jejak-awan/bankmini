# Panduan Operasional Administrator

## 1. Gambaran Umum
Peran **Administrator** bertanggung jawab atas konfigurasi sistem, pengelolaan akun petugas/teller, manajemen master produk simpanan, serta pengawasan audit log.

## 2. Alur Kerja Utama
### 2.1 Manajemen Pengguna (Petugas & Teller)
1. Masuk ke menu **Master Data > Pengguna**.
2. Klik **Tambah Pengguna**, lengkapi NIP/ID, Nama Lengkap, Username, Role (`admin` / `teller` / `supervisor`), dan Password awal.
3. Untuk menonaktifkan petugas yang sudah tidak aktif, ubah status menjadi **Nonaktif** (jangan menghapus data user yang sudah pernah melakukan transaksi demi menjaga integritas riwayat transaksi).

### 2.2 Manajemen Produk Tabungan
1. Masuk ke menu **Master Data > Produk Simpanan**.
2. Tentukan kode produk (misal: `TAB-SISWA`, `TAB-GURU`, `TAB-UMUM`), setoran awal minimum, saldo minimum mengendap, dan biaya administrasi.

### 2.3 Rekonsiliasi & Tutup Buku Harian (End of Day)
1. Masuk ke menu **Laporan > Rekap Kas Harian**.
2. Pastikan total mutasi Kredit (Setoran) dikurangi Debit (Penarikan) sesuai dengan fisik uang kas pada masing-masing Teller.
3. Lakukan verifikasi jurnal harian dan unduh laporan dalam format PDF/Excel.

