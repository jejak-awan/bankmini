# Panduan Standar Frontend & UI/UX Bank Mini (SMK RPL)

Dokumen ini menjelaskan arsitektur antarmuka (**Frontend**) dan pola interaksi (**UI/UX**) yang digunakan di **Bank Mini**, dibangun agar **100% berjalan offline** di XAMPP tanpa perlu `npm` atau *build tools* modern yang rumit.

---

## 1. Struktur Design System & Aset Frontend (`public/aset/`)

Agar halaman **Publik** (`/portal` & `/masuk`) tetap ringan dan tidak ikut mengunduh gaya maupun skrip **Dasbor Admin/Teller**, CSS dan JavaScript dipisah menjadi **3 lapisan terstandar**:

```text
public/aset/
├── css/
│   ├── token-desain.css          <- [SHARED] Design Tokens (:root warna, radius, bayangan), tipografi, .angka-keuangan
│   ├── komponen-ui.css           <- [SHARED] .kartu-bankmini, .tombol-bankmini, fokus form, & @media print
│   ├── publik.css                <- [PUBLIK] Gaya halaman /portal, navbar publik, & .halaman-masuk-bankmini
│   └── dasbor.css                <- [DASBOR] Sidebar navy, topbar petugas, aksen KPI, .tabel-bankmini
├── js/
│   ├── inti-ui.js                <- [SHARED] Toggle kata sandi (data-toggle-sandi) & cegah kirim ganda dasar
│   └── dasbor-interaktif.js      <- [DASBOR] Pratinjau Rupiah, filter select/tabel, info saldo, modal konfirmasi
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

## 2. Tata Letak Multi-Shell & Komponen UI Bersama (`aplikasi/Tampilan/`)

### 2.1 Pemisahan Kerangka Tata Letak (`aplikasi/Tampilan/tata_letak/`)
[`PengendaliDasar::tampilkanTampilan()`](../../aplikasi/Inti/PengendaliDasar.php) mendukung pemilihan kerangka secara eksplisit (`'dasbor'`, `'publik'`, `'cetak'`, atau `'kosong'`):

| Tata Letak | Berkas | Aset yang Dimuat | Peruntukan |
| :--- | :--- | :--- | :--- |
| **`dasbor`** | [`kepala_dasbor.php`](../../aplikasi/Tampilan/tata_letak/kepala_dasbor.php), [`bilah_sisi.php`](../../aplikasi/Tampilan/tata_letak/bilah_sisi.php), [`kaki_dasbor.php`](../../aplikasi/Tampilan/tata_letak/kaki_dasbor.php) | `token-desain.css` + `komponen-ui.css` + `dasbor.css`, `inti-ui.js` + `dasbor-interaktif.js`, Modal `#modalKonfirmasiTransaksi` | Seluruh modul operasional Admin, Supervisor, Teller, & Dokumentasi. |
| **`publik`** | [`kepala_publik.php`](../../aplikasi/Tampilan/tata_letak/kepala_publik.php), [`kaki_publik.php`](../../aplikasi/Tampilan/tata_letak/kaki_publik.php) | `token-desain.css` + `komponen-ui.css` + `publik.css`, `inti-ui.js` (0 byte aset dasbor!) | Halaman `/portal` & `/masuk`. |
| **`cetak`** | [`kepala_cetak.php`](../../aplikasi/Tampilan/tata_letak/kepala_cetak.php), [`kaki_cetak.php`](../../aplikasi/Tampilan/tata_letak/kaki_cetak.php) | `token-desain.css` + `komponen-ui.css` (tanpa navbar/JS) | Tampilan siap cetak/PDF mandiri. |

### 2.2 Slicing Komponen UI Terstandar (`aplikasi/Tampilan/komponen/`)
Seluruh elemen UI yang dipakai ulang dirender melalui fungsi pembantu terisolasi [`komponenUi()`](../../aplikasi/Pembantu/fungsi_umum.php) agar konsistensi Design System terjaga di seluruh halaman:

| Komponen | Fungsi Utama | Contoh Pemakaian |
| :--- | :--- | :--- |
| `pesan_kilat` | Alert notifikasi sukses/peringatan/kesalahan seragam | `komponenUi('pesan_kilat', ['pesanKilat' => $pesanKilat])` |
| `kartu_kpi` | Kartu statistik KPI dengan aksen `biru`, `toska`, atau `hijau` | `komponenUi('kartu_kpi', ['judul' => ..., 'nilai' => ..., 'ikon' => ..., 'aksen' => 'biru'])` |
| `lencana_status` | Badge semantik `KREDIT/AKTIF`, `DEBIT/TUTUP`, `KOREKSI/BEKU` | `komponenUi('lencana_status', ['status' => 'KREDIT'])` |
| `kepala_kartu` | Header kartu standar dengan ikon & judul sejajar | `komponenUi('kepala_kartu', ['judul' => ..., 'ikon' => ...])` |
| `pencarian_tabel` | Bilah filter pencarian instan terhubung dengan `data-cari-tabel` | `komponenUi('pencarian_tabel', ['idTabel' => 'tabelNasabah', 'placeholder' => ...])` |
| `kop_surat` | Kop resmi Bank Mini Sekolah khusus saat dicetak (`.hanya-cetak`) | `komponenUi('kop_surat', ['judulDokumen' => ..., 'subjudulDokumen' => ...])` |

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

