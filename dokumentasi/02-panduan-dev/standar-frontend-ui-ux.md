# Panduan Standar Frontend & UI/UX Bank Mini (SMK RPL)

Dokumen ini menjelaskan arsitektur antarmuka (**Frontend**) dan pola interaksi (**UI/UX**) yang digunakan di **Bank Mini**, dibangun agar **100% berjalan offline** di XAMPP tanpa perlu `npm` atau *build tools* modern yang rumit.

---

## 1. Struktur Aset Frontend (`public/aset/`)

```text
public/aset/
├── css/
│   └── gaya.css                  <- Lapisan tema perbankan, .angka-keuangan, & @media print
├── js/
│   └── aplikasi.js               <- Vanilla JS (pratinjau Rupiah, filter tabel/select, modal konfirmasi)
└── vendor/                       <- Aset pihak ketiga OFFLINE (tanpa CDN internet)
    ├── bootstrap/
    │   ├── css/bootstrap.min.css
    │   └── js/bootstrap.bundle.min.js
    └── bootstrap-icons/
        └── font/
            ├── bootstrap-icons.min.css
            └── fonts/bootstrap-icons.woff / .woff2
```

---

## 2. Tata Letak Utama (`aplikasi/Tampilan/tata_letak/`)

| File | Fungsi | Fitur Utama |
| :--- | :--- | :--- |
| [`kepala.php`](../../aplikasi/Tampilan/tata_letak/kepala.php) | Bagian `<head>` & Navbar Mobile | Memuat `bootstrap.min.css`, `bootstrap-icons.min.css`, `gaya.css`, dan tombol *hamburger* offcanvas di layar kecil (`d-lg-none`). |
| [`bilah_sisi.php`](../../aplikasi/Tampilan/tata_letak/bilah_sisi.php) | Navigasi Samping Responsif & Topbar | Menggunakan struktur resmi `.offcanvas-lg` -> `.offcanvas-body` dengan latar navy gelap kontras tinggi (`!important` menimpa default transparent Bootstrap), topbar putih bersih `.bilah-atas-bankmini`, serta kontrol menu berdasarkan `$peranPenggunaAktif`. |
| [`kaki.php`](../../aplikasi/Tampilan/tata_letak/kaki.php) | Footer, Modal Global, & Skrip | Menyediakan modal konfirmasi global `#modalKonfirmasiTransaksi`, menampilkan `$versiAplikasi`, serta memuat `bootstrap.bundle.min.js` dan `aplikasi.js`. |

---

## 3. Atribut Interaktif UI/UX (`public/aset/js/aplikasi.js`)

Pengembang cukup menambahkan atribut HTML `data-*` pada elemen tampilan tanpa menulis skrip JS berulang:

### 3.1 Pratinjau Rupiah Langsung (`data-pratinjau-rupiah`)
Mencegah kesalahan ketik jumlah angka nol oleh teller (*human error prevention*).
```html
<input type="number" name="nominal_setoran" data-pratinjau-rupiah="pratinjauSetor" required>
<div id="pratinjauSetor" class="form-text fw-semibold text-primary">Terbaca: Rp 0</div>
```

### 3.2 Pencarian Cepat pada Dropdown (`data-cari-select`)
Memudahkan teller mencari nasabah/rekening dari daftar panjang berdasarkan NIS, nomor rekening, atau nama.
```html
<input type="text" placeholder="Ketik nomor rekening / nama..." data-cari-select="id_rekening">
<select id="id_rekening" name="id_rekening" required>...</select>
```

### 3.3 Panel Saldo Saat Ini & Saldo Efektif Real-Time (`data-target-info-saldo`)
Menampilkan **Saldo Total**, **Saldo Mengendap Minimum**, dan **Saldo Efektif (`Saldo - Mengendap`)** saat rekening dipilih:
```html
<select id="id_rekening" data-target-info-saldo="panelInfoSaldoTarik">
    <option value="1" data-saldo="500000" data-saldo-mengendap="10000">...</option>
</select>
<div id="panelInfoSaldoTarik" class="alert alert-light border d-none">
    <span data-nilai-saldo>Rp 0</span>
    <span data-nilai-mengendap>Rp 0</span>
    <span data-nilai-efektif>Rp 0</span>
</div>
```

### 3.4 Pencarian Cepat Tabel (`data-cari-tabel`)
Menyaring baris `<tbody> tr` pada tabel nasabah, rekening, maupun laporan mutasi secara instan di sisi klien:
```html
<input type="text" placeholder="Cari..." data-cari-tabel="tabelNasabah">
<table id="tabelNasabah">...</table>
```

### 3.5 Modal Konfirmasi Transaksi & Anti Double-Submit
- `data-cegah-ganda="ya"`: Otomatis menonaktifkan tombol kirim (*spinner*) setelah disetujui agar tidak terjadi kirim ganda.
- `data-konfirmasi-transaksi="ya"` + `data-judul-konfirmasi="..."`: Menampilkan `#modalKonfirmasiTransaksi` sebelum form dikirim. Konten modal disusun menggunakan DOM API (`createElement` + `textContent`) sehingga aman dari injeksi **XSS**.

---

## 4. Konvensi Tampilan Keuangan & Cetak (`public/aset/css/gaya.css`)

1. **Angka Keuangan Tabular (`.angka-keuangan text-end`)**: Wajib digunakan pada kolom nominal Rupiah, saldo, dan nomor rekening agar digit lurus sejajar dari atas ke bawah.
2. **Semantik Warna Lencana (*Badge*)**:
   - **KREDIT / AKTIF**: `text-bg-success` (hijau)
   - **DEBIT / TUTUP**: `text-bg-danger` (merah)
   - **KOREKSI / BEKU**: `text-bg-warning` (kuning/oranye)
3. **Kontrol Cetak (`@media print`)**:
   - Gunakan kelas `.tidak-cetak` untuk filter, tombol, dan navigasi yang tidak boleh muncul di kertas.
   - Gunakan kelas `.hanya-cetak` untuk kop surat resmi yang hanya tampil saat pengguna menekan **Cetak Laporan**.

---

## 5. Aturan Wajib Kepatuhan Kode View (Dicheck oleh Skrip Penjaga)

1. Semua variabel lokal di dalam file `aplikasi/Tampilan/*.php` wajib memakai format **`$camelCase` Bahasa Indonesia** (dilarang `$snake_case`).
2. Setiap output variabel wajib dibungkus fungsi `amankanHtml(...)` untuk mencegah celah **XSS**.
3. Dilarang menulis query SQL, memanggil Model/Service, membaca `$_SESSION`, atau `require konfigurasi/` langsung di dalam View.

