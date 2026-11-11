# Panduan Operasional Teller / Petugas

## 1. Akses Sistem Teller
- **URL Masuk**: `http://localhost/www/bankmini/public/masuk`
- **Akun Default Teller**: `teller1` / Kata sandi: `password`

## 2. Registrasi Nasabah & Pembukaan Rekening Baru
1. **Tambah Nasabah (`/nasabah`)**:
   - Buka menu **Data Nasabah**.
   - Masukkan Nomor Identitas (`NIS`/`NIP`/`NIK`), Nama Lengkap, Jenis Nasabah (`siswa`/`guru`/`umum`), Kelas/Unit, Nomor Telepon, dan Alamat.
   - Klik **Simpan Nasabah**.
2. **Buka Rekening Tabungan (`/rekening`)**:
   - Buka menu **Data Rekening**.
   - Pilih **Nasabah**, pilih **Produk Tabungan**, dan masukkan **Nominal Setoran Awal (Rupiah IDR)** (wajib minimal sebesar batas setoran awal produk).
   - Klik **Buka Rekening & Simpan Setoran Awal**. Sistem menerbitkan nomor rekening unik (`BM-YYYYMM-...`) dan mencatat mutasi `SETOR` pertama secara otomatis.

## 3. Transaksi Setor Tunai (`/transaksi/setor`)
1. Buka menu **Setor Tunai**.
2. Pilih rekening nasabah tujuan setoran.
3. Masukkan **Nominal Setoran (Rupiah IDR)** dan keterangan.
4. Klik **Proses Setor Tunai**. Saldo rekening langsung bertambah (`KREDIT`) di dalam transaksi database ACID.

## 4. Transaksi Tarik Tunai (`/transaksi/tarik`)
1. Buka menu **Tarik Tunai**.
2. Pilih rekening penarikan dan periksa saldo saat ini.
3. Pastikan **Saldo Efektif** (`Saldo Saat Ini - Saldo Mengendap Minimum`) mencukupi.
4. Masukkan **Nominal Penarikan (Rupiah IDR)** dan klik **Proses Tarik Tunai**.

## 5. Transaksi Transfer Antar Rekening (`/transaksi/transfer`)
1. Buka menu **Transfer Rekening**.
2. Pilih **Rekening Pengirim (DEBIT)** dan **Rekening Penerima (KREDIT)**.
3. Masukkan **Nominal Transfer (Rupiah IDR)** dan keterangan, lalu klik **Proses Transfer**.

## 6. Prosedur Jika Terjadi Kesalahan Input (Reversal)
> [!IMPORTANT]
> Transaksi yang sudah tersimpan **tidak dapat dihapus atau diedit nominalnya**. Jika terjadi salah ketik nominal oleh Teller, catat **Kode Referensi Transaksi** lalu minta **Supervisor / Admin** memproses pembalikan melalui menu **Jurnal Koreksi (`/transaksi/koreksi`)**.
