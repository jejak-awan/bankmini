# Panduan Operasional Administrator & Supervisor

## 1. Gambaran Umum & Akun Bawaan
Peran **Administrator (`admin`)** dan **Supervisor (`supervisor`)** bertanggung jawab atas konfigurasi sistem, pemantauan produk simpanan, pengawasan buku besar mutasi, serta persetujuan **Jurnal Koreksi / Reversal**.

- **URL Masuk**: `http://localhost/www/bankmini/public/masuk`
- **Akun Default Admin**: `admin` / Kata sandi: `password`
- **Akun Default Supervisor**: `supervisor1` / Kata sandi: `password`

## 2. Alur Kerja Utama
### 2.1 Pemeriksaan Master Produk Tabungan (`/produk`)
1. Buka menu **Produk Tabungan** pada bilah sisi kiri.
2. Periksa daftar produk aktif (`TAB-SISWA`, `TAB-GURU`, `TAB-UMUM`) beserta parameter:
   - Setoran Awal Minimum (Rupiah `IDR`)
   - Saldo Mengendap Minimum (Rupiah `IDR`)
   - Biaya Administrasi Bulanan (Rupiah `IDR`)

### 2.2 Eksekusi Jurnal Koreksi / Reversal (`/transaksi/koreksi`)
Sesuai aturan **Immutable Ledger (*Append-Only*)**, transaksi yang salah diinput oleh Teller tidak boleh dihapus dari database, melainkan dibalik menggunakan **Jurnal Koreksi**:
1. Masuk menggunakan akun berperan `admin` atau `supervisor` (menu **Jurnal Koreksi** pada bilah sisi kiri hanya muncul untuk kedua peran ini).
2. Buka menu **Jurnal Koreksi** (`/transaksi/koreksi`).
3. Gunakan kotak pencarian di atas dropdown untuk mencari **Kode Referensi Transaksi**, nomor rekening, atau nama nasabah yang akan dikoreksi.
4. Isi **Alasan Koreksi** secara jelas (wajib untuk pencatatan `log_audit`).
5. Klik **Proses Jurnal Koreksi** dan konfirmasikan pada **Modal Konfirmasi**. Sistem akan otomatis membuat mutasi pembalik (`KOREKSI`) dan mengembalikan saldo rekening secara atomik.

### 2.3 Filter Rentang Tanggal, Pencarian Cepat, & Cetak Laporan Mutasi (`/laporan/mutasi`)
1. Buka menu **Laporan Mutasi** (`/laporan/mutasi`).
2. Pilih **Tanggal Mulai** dan **Tanggal Akhir**, lalu klik **Tampilkan Mutasi**.
3. Gunakan kotak pencarian cepat di pojok kanan tabel untuk menyaring kode referensi, nama nasabah, atau keterangan secara langsung.
4. Klik tombol **Cetak Laporan** untuk mencetak atau menyimpan buku besar mutasi ke PDF; bilah navigasi, form filter, dan tombol akan otomatis disembunyikan dan digantikan kop cetak resmi.

### 2.4 Pusat Dokumentasi Terindeks & Materi RPL (`/dokumentasi`)
1. Klik menu **Pusat Dokumentasi** pada bagian *Materi & Dokumentasi RPL* di bilah sisi kiri.
2. Pilih dokumen dari pohon indeks kategori di sebelah kiri (`00` s.d. `06`), atau ketik kata kunci (misal: `ACID`, `ERD`, `Jurnal Koreksi`, `Docker`) pada kolom **Cari Materi**.
3. Gunakan panel **Daftar Isi** di sebelah kanan untuk melompat antar-subjudul, atau klik **Cetak Modul** untuk mencetak halaman dokumentasi ke PDF.
