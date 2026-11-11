# Katalog 12 Tugas Pengembangan Modul & Fitur Bank Mini (Praktikum & Proyek UKK RPL)

Dokumen ini berisi **daftar tugas implementasi nyata (*Product Backlog & Spesifikasi Teknis*)** yang dirancang khusus agar siswa SMK Rekayasa Perangkat Lunak (RPL) dapat melengkapi fitur-fitur lanjutan **Bank Mini (`v1.8.1+`)** di atas fondasi arsitektur yang sudah tersedia.

> [!IMPORTANT]
> Sebelum mengerjakan salah satu tugas di bawah ini, pastikan kamu telah membaca [**Rundown Belajar 8 Pertemuan & Resep 6 Langkah**](./alur-belajar-dan-modul-praktikum-rpl.md) serta memeriksa apakah kodemu patuh terhadap aturan SoT, SoC, Penamaan Bahasa Indonesia, `BCMath`, dan CSP ketat (`script-src 'self'`).

---

## 1. Peta Status Modul Saat Ini vs Target Pengembangan Siswa

| Domain / Modul | Sudah Tersedia (`v1.8.1`) | Target Pengembangan Siswa (Tugas Praktikum) |
| :--- | :--- | :--- |
| **Publik & Observabilitas** | Portal Publik (`GET /`, `/portal`), Masuk/Keluar, Health Check (`GET /kesehatan`), Error Pages (`403`/`404`/`500`/`503`) | Pengujian & pemantauan log harian di dasbor admin (`TUGAS-10`) |
| **Nasabah** | Daftar Nasabah (`GET /nasabah`) & Tambah Nasabah (`POST /nasabah/tambah`) | Filter kelas/jenis nasabah (`TUGAS-01`), Ubah & nonaktifkan nasabah (`TUGAS-04`), Impor massal Excel `.xlsx`/`.csv` (`TUGAS-08`) |
| **Produk Tabungan** | Daftar Produk Tabungan (`GET /produk`) | Tambah & Ubah produk tabungan oleh Admin (`TUGAS-05`), Surat keterangan Word `.docx` & potongan `BIAYA_ADMIN` (`TUGAS-09`) |
| **Rekening** | Daftar Rekening (`GET /rekening`) & Buka Rekening (`POST /rekening/buka`) | Pembekuan (`dibekukan`), Penutupan rekening atomik (`tutup`) & buku tabungan (`TUGAS-06`) |
| **Transaksi** | Setor Tunai, Tarik Tunai, Transfer Antar Rekening, & Jurnal Koreksi (ACID) | Cetak struk satuan per `kode_referensi` memakai tata letak `'cetak'` (`TUGAS-03`) |
| **Laporan & Dokumen** | Filter mutasi SARGable (`GET /laporan/mutasi`), Fondasi `LayananDokumenKantor` (Dompdf, PhpSpreadsheet, SimpleXLSX, PhpWord) | Ringkasan total `KREDIT`/`DEBIT` `BCMath` (`TUGAS-02`), Ekspor Mutasi PDF & Excel `.xlsx` (`TUGAS-07`), Log Audit `log_audit` (`TUGAS-10`) |
| **Portal Siswa / Nasabah** | Kolom `nasabah.hash_kata_sandi` sudah tersedia di `skema.sql` | Portal *Self-Service Read-Only* Nasabah untuk cek saldo & mutasi sendiri (`TUGAS-11` — Proyek Akhir / UKK) |
| **Jaminan Mutu (QA/E2E)** | Suite PHP Unit/Integrasi (27 skenario) & Smoke Test Playwright | Ekspansi skenario E2E Playwright alur lengkap keuangan + RBAC `403` (`TUGAS-12`) |

---

## 2. Tingkat 1 — Tugas Dasar (UI, Filter, Ringkasan `BCMath`, & Tata Letak Cetak)

### `TUGAS-01` — Filter Pencarian Kelas/Unit & Jenis Nasabah
- **Kompetensi RPL**: PDO `prepare()` dinamis aman dari SQLi, validasi *whitelist* `ENUM`, dan konsistensi XSS escaping (`amankanHtml()`).
- **Berkas Terkait**: [`ModelNasabah.php`](../../aplikasi/Model/ModelNasabah.php), [`PengendaliNasabah.php`](../../aplikasi/Pengendali/PengendaliNasabah.php), [`Tampilan/nasabah/indeks.php`](../../aplikasi/Tampilan/nasabah/indeks.php).
- **Spesifikasi Teknis**:
  1. Tambahkan form filter metode `GET` pada halaman `/nasabah` dengan pilihan `jenis_nasabah` (`semua`, `siswa`, `guru`, `umum`) dan input pencarian `kelas_atau_unit` atau `kata_kunci`.
  2. Di `ModelNasabah`, tambahkan method `cariNasabahTersaring(?string $jenisNasabah, ?string $kataKunci)` menggunakan *Prepared Statements* dengan operator `LIKE` yang di-bind lewat parameter (dilarang menggabungkan string input ke SQL).
  3. Pertahankan nilai filter terpilih pada form agar pengguna melihat kriteria yang sedang aktif.
- **Kriteria Lulus (*Acceptance Criteria*)**:
  - Pencarian dengan input payload SQLi/XSS seperti `' OR 1=1 --` atau `<script>alert(1)</script>` tidak error dan ter-escape aman.
  - Lolos PHPStan Level 8 & PHPCS 0 warning.

### `TUGAS-02` — Kartu Ringkasan Total `KREDIT`, Total `DEBIT`, & Selisih Bersih di Laporan Mutasi
- **Kompetensi RPL**: Perhitungan keuangan presisi tinggi (`bcadd`, `bcsub`, `keStringNumerik()`), dan penggunaan ulang komponen Design System (`komponenUi('kartu_kpi')`).
- **Berkas Terkait**: [`PengendaliLaporan.php`](../../aplikasi/Pengendali/PengendaliLaporan.php), [`Tampilan/laporan/mutasi.php`](../../aplikasi/Tampilan/laporan/mutasi.php).
- **Spesifikasi Teknis**:
  1. Hitung total nominal `KREDIT`, total nominal `DEBIT`, serta arus bersih (`totalKredit - totalDebit`) dari daftar mutasi yang sedang tampil menggunakan `bcadd()` dan `bcsub()` (skala `2`).
  2. Tampilkan 3 kartu ringkasan tepat di atas tabel buku besar pada `laporan/mutasi.php` menggunakan komponen bersama `komponenUi('kartu_kpi', [...])` dan `formatRupiah()`.
- **Kriteria Lulus**:
  - Tidak ada penggunaan operator `+`, `-`, atau tipe `float` pada penjumlahan nominal Rupiah.

### `TUGAS-03` — Cetak Struk Bukti Transaksi Satuan (`/transaksi/struk`)
- **Kompetensi RPL**: Isolasi tata letak `'cetak'` (`kepala_cetak.php` / `kaki_cetak.php`), penggunaan `komponenUi('kop_surat')`, dan kepatuhan CSP ketat (`script-src 'self'`).
- **Berkas Terkait**: [`ModelMutasi.php`](../../aplikasi/Model/ModelMutasi.php), [`PengendaliTransaksi.php`](../../aplikasi/Pengendali/PengendaliTransaksi.php), buat `Tampilan/transaksi/struk.php`, [`Tampilan/laporan/mutasi.php`](../../aplikasi/Tampilan/laporan/mutasi.php), [`public/index.php`](../../public/index.php).
- **Spesifikasi Teknis**:
  1. Tambahkan method `ambilBerdasarkanKodeReferensi(string $kodeReferensi): ?array` di `ModelMutasi`.
  2. Daftarkan rute `GET /transaksi/struk` (menerima `?kode=...`) pada `PengendaliTransaksi::tampilkanStruk()`. Jika kode tidak ditemukan, lempar `PengecualianHttp(404, ...)`.
  3. Render `transaksi/struk` memakai tata letak `'cetak'` (`$this->tampilkanTampilan('transaksi/struk', [...], 'cetak')`) lengkap dengan `komponenUi('kop_surat')`, kode referensi, nama nasabah, saldo sebelum/sesudah, dan nama petugas.
  4. Tambahkan tombol "Cetak Struk" di setiap baris tabel `/laporan/mutasi` serta tombol cetak di halaman struk memakai atribut `data-cetak-halaman="ya"` (tanpa `onclick=`).
- **Kriteria Lulus**:
  - Membuka `/transaksi/struk?kode=STR-...` menghasilkan tata letak bersih siap print tanpa sidebar/navbar dan 0 error konsol CSP.

---

## 3. Tingkat 2 — Tugas Menengah (CRUD Master Data & Siklus Hidup Rekening ACID)

### `TUGAS-04` — Ubah Profil & Nonaktifkan Nasabah (Dengan Audit Trail JSON)
- **Kompetensi RPL**: Pencatatan perubahan `data_lama` dan `data_baru` pada `log_audit`, aturan integritas referensial (tidak menghapus fisik data yang berelasi transaksi), & pola PRG.
- **Berkas Terkait**: [`ModelNasabah.php`](../../aplikasi/Model/ModelNasabah.php), [`ModelRekening.php`](../../aplikasi/Model/ModelRekening.php), buat `aplikasi/Layanan/LayananNasabah.php`, [`PengendaliNasabah.php`](../../aplikasi/Pengendali/PengendaliNasabah.php), [`Tampilan/nasabah/indeks.php`](../../aplikasi/Tampilan/nasabah/indeks.php), dan buat `pengujian/Integrasi/TesLayananNasabah.php` (wajib 1:1 dengan `LayananNasabah.php`).
- **Spesifikasi Teknis**:
  1. Buat rute `POST /nasabah/ubah` dan `POST /nasabah/nonaktifkan` yang dilindungi `MiddlewareAutentikasi::wajibPeran(['admin', 'teller'])` serta `TokenCsrf`.
  2. Larang penonaktifan nasabah apabila nasabah tersebut masih memiliki rekening berstatus `'aktif'` dengan saldo `> 0.00` (`bccomp(...) > 0`).
  3. Setiap perubahan wajib menyimpan rekaman JSON `data_lama` dan `data_baru` ke `log_audit`.
- **Kriteria Lulus**:
  - Tersedia `TesLayananNasabah.php` terdaftar di `jalankan_semua_tes.php` dengan skenario sukses ubah profil & skenario tolak nonaktif jika saldo > 0.

### `TUGAS-05` — Manajemen Master Produk Tabungan (Khusus Peran `admin`)
- **Kompetensi RPL**: Otorisasi ketat RBAC `403`, validasi batas minimum Rupiah (`setoran_awal_minimum >= saldo_mengendap_minimum`), dan pencatatan audit.
- **Berkas Terkait**: [`ModelProduk.php`](../../aplikasi/Model/ModelProduk.php), buat `LayananProduk.php` + `TesLayananProduk.php`, [`PengendaliProduk.php`](../../aplikasi/Pengendali/PengendaliProduk.php), `Tampilan/produk/indeks.php`, [`public/index.php`](../../public/index.php).
- **Spesifikasi Teknis**:
  1. Tambahkan form Tambah Produk (`POST /produk/tambah`) dan Ubah Aturan Produk (`POST /produk/ubah`) hanya untuk peran **`admin`** (`MiddlewareAutentikasi::wajibPeran(['admin'])`).
  2. Pastikan `teller` yang mencoba membuka/mengirim `POST /produk/tambah` langsung ditolak dengan halaman galat `403` (`PengecualianHttp`).
  3. Validasi seluruh nominal produk memakai `keStringNumerik()` dan `bccomp() >= 0`.
- **Kriteria Lulus**:
  - Tombol ubah/tambah di `Tampilan/produk/indeks.php` hanya tampil untuk `admin`, dan endpoint `POST` menolak `teller`/`supervisor` dengan `403`.

### `TUGAS-06` — Pembekuan & Penutupan Rekening Atomik + Halaman Rincian Buku Tabungan
- **Kompetensi RPL**: State machine status rekening (`aktif` <-> `dibekukan` -> `tutup`), penguncian ACID saat pencairan saldo penutupan, & halaman detail parameter *query string*.
- **Berkas Terkait**: [`ModelRekening.php`](../../aplikasi/Model/ModelRekening.php), [`LayananRekening.php`](../../aplikasi/Layanan/LayananRekening.php), [`PengendaliRekening.php`](../../aplikasi/Pengendali/PengendaliRekening.php), [`TesLayananRekening.php`](../../pengujian/Integrasi/TesLayananRekening.php), `Tampilan/rekening/indeks.php`, & buat `Tampilan/rekening/detail.php`.
- **Spesifikasi Teknis**:
  1. Tambahkan rute `GET /rekening/detail?id=...` untuk menampilkan profil rekening beserta 50 mutasi terakhir milik rekening tersebut (`ModelMutasi::ambilMutasiBerdasarkanRekening()`).
  2. Tambahkan aksi `POST /rekening/ubah-status` (khusus `admin` dan `supervisor`) untuk membekukan/mengaktifkan kembali rekening.
  3. Tambahkan aksi `POST /rekening/tutup`: di dalam satu blok `beginTransaction()` + `kunciRekeningUntukPembaruan()`, jika saldo rekening masih `> 0`, catat transaksi penarikan pelunasan penutupan hingga saldo tepat `'0.00'`, baru ubah `status = 'tutup'` dan isi `tanggal_tutup = NOW()`.
- **Kriteria Lulus**:
  - Rekening berstatus `tutup` tidak dapat dipilih/diproses pada `Setor`, `Tarik`, maupun `Transfer`, dibuktikan di `TesLayananRekening.php`.

---

## 4. Tingkat 3 — Tugas Mahir (Integrasi Dokumen PDF, Excel, Word & Otomasi)

### `TUGAS-07` — Ekspor Laporan Mutasi ke PDF Resmi & Excel `.xlsx`
- **Kompetensi RPL**: Pemanfaatan *Service Adapter* [`LayananDokumenKantor`](../../aplikasi/Layanan/LayananDokumenKantor.php), pengiriman header HTTP download (`Content-Disposition: attachment`), dan konsistensi filter tanggal SARGable.
- **Berkas Terkait**: [`PengendaliLaporan.php`](../../aplikasi/Pengendali/PengendaliLaporan.php), [`Tampilan/laporan/mutasi.php`](../../aplikasi/Tampilan/laporan/mutasi.php), [`public/index.php`](../../public/index.php).
- **Spesifikasi Teknis**:
  1. Tambahkan rute `GET /laporan/mutasi/pdf` dan `GET /laporan/mutasi/excel` yang menerima parameter filter `?tanggal_mulai=...&tanggal_akhir=...` sama persis dengan `/laporan/mutasi`.
  2. Untuk PDF: susun HTML tabel mutasi yang seluruh sel teksnya sudah melewati `amankanHtml()`, bungkus dengan `LayananDokumenKantor::siapkanHtmlCetak()`, lalu kirim hasil biner `LayananDokumenKantor::buatPdfDariHtml()` sebagai `application/pdf`.
  3. Untuk Excel: kirim hasil `LayananDokumenKantor::buatExcelDariTabel()` sebagai unduhan berkas `.xlsx` (`laporan-mutasi-YYYYMMDD.xlsx`).
  4. Tambahkan tombol "Unduh PDF" & "Unduh Excel" pada `Tampilan/laporan/mutasi.php` yang meneruskan filter tanggal aktif.
- **Kriteria Lulus**:
  - Mengunduh `/laporan/mutasi/pdf` menghasilkan berkas `%PDF` valid dan `/laporan/mutasi/excel` menghasilkan berkas `.xlsx` valid yang terbaca di LibreOffice/Excel.

### `TUGAS-08` — Impor Massal Data Nasabah Baru dari Excel `.xlsx` / `.csv`
- **Kompetensi RPL**: Penanganan upload berkas aman (validasi ekstensi, ukuran maks 2 MB, tipe MIME), validasi baris per baris, & transaksi database *All-or-Nothing*.
- **Berkas Terkait**: [`LayananDokumenKantor.php`](../../aplikasi/Layanan/LayananDokumenKantor.php), `LayananNasabah.php` (atau [`PengendaliNasabah.php`](../../aplikasi/Pengendali/PengendaliNasabah.php)), `Tampilan/nasabah/indeks.php`, `public/index.php`.
- **Spesifikasi Teknis**:
  1. Sediakan tombol "Unduh Template Excel Impor Nasabah" (`GET /nasabah/template-impor`) memakai `LayananDokumenKantor::buatExcelDariTabel()` dengan kolom: `nomor_identitas`, `nama_lengkap`, `jenis_nasabah`, `kelas_atau_unit`, `nomor_telepon`, `alamat`.
  2. Tambahkan form upload `enctype="multipart/form-data"` ke `POST /nasabah/impor` (wajib CSRF + peran `admin`/`teller`).
  3. Baca isi file memakai `LayananDokumenKantor::bacaDataDariExcel()` / `bacaDataDariCsv()`, validasi setiap baris (tolak jika `nomor_identitas` duplikat atau `jenis_nasabah` tidak valid), lalu simpan seluruh baris valid di dalam satu transaksi database.
- **Kriteria Lulus**:
  - Jika ada 1 baris rusak/duplikat pada file uji, transaksi di-`rollBack()` dengan laporan nomor baris yang error secara jelas lewat pesan kilat.

### `TUGAS-09` — Surat Keterangan Nasabah Word `.docx` & Pemotongan `BIAYA_ADMIN` Bulanan Massal
- **Kompetensi RPL**: Generasi dokumen `.docx` otomatis via `LayananDokumenKantor::buatDokumenWord()`, serta pemrosesan batch ACID `BIAYA_ADMIN` dengan mekanisme anti-pemotongan ganda pada bulan yang sama.
- **Berkas Terkait**: [`LayananTransaksi.php`](../../aplikasi/Layanan/LayananTransaksi.php), [`PengendaliRekening.php`](../../aplikasi/Pengendali/PengendaliRekening.php), [`PengendaliTransaksi.php`](../../aplikasi/Pengendali/PengendaliTransaksi.php), [`public/index.php`](../../public/index.php).
- **Spesifikasi Teknis**:
  1. Rute `GET /rekening/surat-keterangan?id=...` menghasilkan berkas Word `.docx` resmi berisi data identitas nasabah, nomor rekening, dan saldo terkini dalam format Rupiah.
  2. Rute `POST /transaksi/biaya-admin-bulanan` (khusus peran **`admin`**) memotong `biaya_admin_bulanan > 0` dari rekening berstatus `aktif` hanya jika saldo efektif mencukupi (atau saldo tidak boleh menjadi minus), mencatat mutasi bertipe `BIAYA_ADMIN` (`DEBIT`), serta mencegah eksekusi ganda untuk rekening dan bulan `YYYY-MM` yang sama (*idempotency guard*).
- **Kriteria Lulus**:
  - Pengujian unit/integrasi membuktikan menjalankan proses admin 2 kali di bulan yang sama tidak memotong saldo nasabah dua kali.

---

## 5. Tingkat 4 — Proyek Spesialis & Proyek Akhir / UKK (`TUGAS-10` s.d. `TUGAS-12`)

### `TUGAS-10` — Halaman Jejak Audit (`log_audit`) & Inspektor Log Sistem Harian
- **Kompetensi RPL**: Transparansi audit sistem keuangan, visualisasi `JSON` `data_lama`/`data_baru`, pembatasan akses sensitif (`admin`/`supervisor`), dan pembacaan file log tanpa *Path Traversal*.
- **Berkas Terkait**: [`ModelLogAudit.php`](../../aplikasi/Model/ModelLogAudit.php), [`PengendaliLaporan.php`](../../aplikasi/Pengendali/PengendaliLaporan.php), [`Tampilan/tata_letak/bilah_sisi.php`](../../aplikasi/Tampilan/tata_letak/bilah_sisi.php), buat `Tampilan/laporan/audit.php`, [`public/index.php`](../../public/index.php).
- **Spesifikasi Teknis**:
  1. Daftarkan rute `GET /laporan/audit` (`wajibPeran(['admin', 'supervisor'])`) menampilkan 100 catatan terbaru dari `log_audit` lengkap dengan pemformatan rapi kolom JSON `data_lama` dan `data_baru`.
  2. Tampilkan pula daftar tanggal log harian yang tersedia di `penyimpanan/log/aplikasi-*.log` (khusus `admin`) dengan validasi regex ketat `/^aplikasi-\d{4}-\d{2}-\d{2}\.log$/` agar kebal *Path Traversal*.
  3. Tampilkan menu **Jejak Audit** pada `bilah_sisi.php` hanya apabila peran pengguna adalah `admin` atau `supervisor`.
- **Kriteria Lulus**:
  - Peran `teller` yang mengakses `/laporan/audit` langsung menerima halaman galat HTTP `403`.

### `TUGAS-11` — Proyek Akhir / UKK: Portal Mandiri Siswa & Nasabah (*Read-Only*)
- **Kompetensi RPL**: Arsitektur multi-aktor (*Petugas* vs *Nasabah*), pemisahan kunci sesi (`id_nasabah_masuk` vs `id_pengguna`), proteksi `PembatasLaju`, dan isolasi penuh tata letak `'publik'`.
- **Berkas Terkait**: [`ModelNasabah.php`](../../aplikasi/Model/ModelNasabah.php) (kolom `hash_kata_sandi` sudah tersedia di `skema.sql`!), buat `LayananPortalNasabah.php` + `TesLayananPortalNasabah.php`, buat `PengendaliPortalNasabah.php`, buat `Tampilan/portal_nasabah/` (`masuk.php`, `ringkasan.php`), [`Tampilan/publik/portal.php`](../../aplikasi/Tampilan/publik/portal.php), [`public/index.php`](../../public/index.php).
- **Spesifikasi Teknis**:
  1. Siswa/Nasabah masuk lewat `GET/POST /nasabah/portal-masuk` memakai `nomor_identitas` (NIS/NIP) + kata sandi (yang dapat diatur saat teller membuat/mereset akun nasabah dengan `PASSWORD_BCRYPT`), dilindungi `TokenCsrf` dan `PembatasLaju`.
  2. Setelah masuk, nasabah hanya dapat melihat daftar rekening **miliknya sendiri** (`id_nasabah = Sesi::ambil('id_nasabah_portal')`) dan mutasi transaksinya (**100% Read-Only**, tanpa form transfer/ubah saldo).
  3. Wajib mencegah *IDOR (Insecure Direct Object Reference)*: jika nasabah A memanipulasi URL `?id_rekening=MILIK_B`, sistem wajib melempar `PengecualianHttp(403, ...)`.
  4. Seluruh tampilan memakai tata letak `'publik'` sehingga tidak pernah mengunduh aset dasbor admin.
- **Kriteria Lulus**:
  - Terbukti secara pengujian otomatis bahwa sesi nasabah tidak dapat membuka `/dasbor` dan tidak dapat melihat rekening nasabah lain (*Anti-IDOR*).

### `TUGAS-12` — Ekspansi Suite Pengujian E2E Playwright (Transfer, Koreksi, & RBAC `403`)
- **Kompetensi RPL**: Pengujian perangkat lunak *End-to-End* lintas browser nyata (`Chromium`), verifikasi *Race/Double-Submit Prevention*, & verifikasi visual *0 Console Warning/Error*.
- **Berkas Terkait**: [`pengujian/e2e/alur_keuangan.spec.js`](../../pengujian/e2e/alur_keuangan.spec.js).
- **Spesifikasi Teknis**:
  1. Tambahkan pengujian E2E lengkap untuk alur **Transfer Antar Rekening** (memastikan saldo pengirim berkurang dan saldo penerima bertambah di UI laporan).
  2. Tambahkan pengujian E2E bahwa petugas `teller1` yang mencoba membuka `/transaksi/koreksi` menerima status HTTP `403` dengan halaman galat Design System, sedangkan `admin` berhasil mengeksekusi Jurnal Koreksi.
  3. Pastikan seluruh tes berjalan bersih di Playwright tanpa 1 pun error/warning konsol atau pelanggaran CSP.

---

## 6. Rubrik Penilaian Tugas & Standar Kelulusan Guru / Asesor UKK (Skala 100)

| Aspek Penilaian | Bobot | Kriteria Skor Sempurna |
| :--- | :---: | :--- |
| **1. Kepatuhan Arsitektur (SoC & SoT)** | **25%** | Kode terpisah rapi di `Model`, `Layanan`, `Pengendali`, `Tampilan`; `periksa_kepatuhan_agen.php` mencatat **0 Pelanggaran**. |
| **2. Integritas Bisnis & Keamanan** | **25%** | Hitungan Rupiah memakai `BCMath`, query `prepare()` + SARGable, CSRF + PRG aktif, output ter-escape `amankanHtml()`, bebas inline JS `onclick=` (CSP lolos). |
| **3. Kebersihan Kode & Tipe Statis** | **20%** | Seluruh penamaan variabel `$camelCase` Bahasa Indonesia, PHPDoc `@konsep_rpl` informatif, **PHPCS 0 warning**, dan **PHPStan Level 8 (`0 error`)**. |
| **4. Pengujian Otomatis (QA & E2E)** | **20%** | Menulis skenario unit/integrasi `@skenario` untuk fitur baru (`jalankan_semua_tes.php` 100% lulus) serta E2E Playwright 0 error konsol. |
| **5. Dokumentasi & Git Workflow** | **10%** | Menambahkan catatan Work Log di `04-work-log/`, pesan commit jelas, dan lolos *pre-commit/pre-push hook*. |

### Perintah Verifikasi Wajib Sebelum Mengumpulkan Tugas
```powershell
# Jalankan seluruh gerbang QA lokal (Kepatuhan + Tes PHP E_ALL + PHPCS + PHPStan Level 8 + Playwright E2E)
powershell -ExecutionPolicy Bypass -File .\dokumentasi\06-aturan-agen\skrip\periksa-konsistensi.ps1 -DenganE2E
```
