# Panduan Operasional Teller / Petugas

## 1. Akses Sistem Teller
- **URL Masuk**: `http://localhost/www/bankmini/public/masuk`
- **Akun Default Teller**: `teller1` / Kata sandi: `password`

## 2. Registrasi Nasabah & Pembukaan Rekening Baru
1. **Tambah Nasabah (`/nasabah`)**:
   - Buka menu **Data Nasabah** (pada layar ponsel/tablet, tekan ikon menu di pojok kanan atas bilah navigasi).
   - Masukkan Nomor Identitas (`NIS`/`NIP`/`NIK`), Nama Lengkap, Jenis Nasabah (`siswa`/`guru`/`umum`), Kelas/Unit, Nomor Telepon, dan Alamat.
   - Klik **Simpan Nasabah**. Gunakan kotak pencarian di atas tabel untuk memfilter daftar nasabah secara instan.
2. **Buka Rekening Tabungan (`/rekening`)**:
   - Buka menu **Data Rekening**.
   - Gunakan kotak **Cari Nasabah** di atas dropdown untuk memfilter nama/NIS, pilih **Produk Tabungan**, lalu masukkan **Nominal Setoran Awal (Rupiah IDR)**.
   - Periksa teks **Terbaca: Rp ...** di bawah kolom nominal agar tidak terjadi salah jumlah angka nol.
   - Klik **Buka Rekening & Simpan Setoran Awal**, periksa ringkasan pada **Modal Konfirmasi**, lalu klik **Ya, Proses Sekarang**. Sistem menerbitkan nomor rekening unik (`BM-YYYYMM-...`) dan mencatat mutasi `SETOR` pertama secara otomatis.

## 3. Transaksi Setor Tunai (`/transaksi/setor`)
1. Buka menu **Setor Tunai** (atau tombol cepat dari halaman Dasbor).
2. Gunakan kotak pencarian di atas pilihan rekening untuk menemukan nomor rekening atau nama nasabah dengan cepat; panel informasi akan menampilkan **Saldo Saat Ini** dan **Saldo Efektif**.
3. Masukkan **Nominal Setoran (Rupiah IDR)** dan pastikan teks **Terbaca: Rp ...** sudah sesuai, lalu isi keterangan.
4. Klik **Proses Setor Tunai** dan setujui **Modal Konfirmasi**. Saldo rekening langsung bertambah (`KREDIT`) di dalam transaksi database ACID.

## 4. Transaksi Tarik Tunai (`/transaksi/tarik`)
1. Buka menu **Tarik Tunai**.
2. Pilih rekening penarikan (bisa disaring dengan kotak pencarian); sistem menampilkan panel **Saldo Total**, **Mengendap Wajib**, dan **Maksimal Dapat Ditarik (Saldo Efektif)**.
3. Pastikan nominal penarikan tidak melebihi **Maksimal Dapat Ditarik**.
4. Masukkan **Nominal Penarikan (Rupiah IDR)**, periksa pratinjau Rupiah, klik **Proses Tarik Tunai**, lalu konfirmasi pada modal dialog.

## 5. Transaksi Transfer Antar Rekening (`/transaksi/transfer`)
1. Buka menu **Transfer Rekening**.
2. Pilih **Rekening Pengirim (DEBIT)** (lihat batas maksimal transfer pada panel info saldo) dan **Rekening Penerima (KREDIT)**.
3. Masukkan **Nominal Transfer (Rupiah IDR)** dan keterangan, periksa pratinjau Rupiah, klik **Proses Transfer**, lalu setujui **Modal Konfirmasi**.

## 6. Prosedur Jika Terjadi Kesalahan Input (Reversal)
> [!IMPORTANT]
> Transaksi yang sudah tersimpan **tidak dapat dihapus atau diedit nominalnya**. Jika terjadi salah ketik nominal oleh Teller, catat **Kode Referensi Transaksi** lalu minta **Supervisor / Admin** memproses pembalikan melalui menu **Jurnal Koreksi (`/transaksi/koreksi`)**.

## 7. Membuka Panduan & Glosarium Langsung di Browser (`/dokumentasi`)
Petugas maupun siswa praktikum dapat membaca seluruh panduan operasional, kamus data, DFD/ERD, dan modul praktikum tanpa keluar dari aplikasi dengan membuka menu **Pusat Dokumentasi** (`/dokumentasi`) pada bilah sisi kiri.
