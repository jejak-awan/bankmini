# Aturan Koding PHP, Konvensi Bahasa Indonesia, & Integritas Finansial (Wajib untuk AI Agent)

Setiap baris kode yang ditulis oleh AI Agent di dalam `C:\xampp\htdocs\www\bankmini` **wajib** mematuhi aturan keras (*hard rules*) berikut:

---

## 1. Kewajiban Penamaan & Anotasi Bahasa Indonesia
1. **Struktur Folder & File**:
   - Wajib menggunakan folder berbahasa Indonesia: `konfigurasi/`, `aplikasi/Inti/`, `aplikasi/Pengendali/`, `aplikasi/Layanan/`, `aplikasi/Model/`, `aplikasi/Pembantu/`, `aplikasi/Tampilan/`, `basis_data/`, `penyimpanan/`.
   - Pengecualian hanya untuk `public/index.php` dan `.htaccess`.
2. **Penamaan Kode (PSR-12 Bahasa Indonesia)**:
   - **Class**: `PascalCase` Bahasa Indonesia (contoh: `LayananTransaksi`, `ModelRekening`, `PengendaliNasabah`).
   - **Method / Fungsi**: `camelCase` Bahasa Indonesia dengan kata kerja aktif (contoh: `prosesSetorTunai()`, `kunciRekeningUntukPembaruan()`, `amankanHtml()`, `formatRupiah()`).
   - **Variabel**: `$camelCase` Bahasa Indonesia yang utuh dan jelas (contoh: `$idRekening`, `$nominalSetoran`, `$saldoSebelum`, `$saldoSesudah`). **Dilarang** menggunakan variabel bahasa Inggris (`$amount`, `$user`, `$account`) atau singkatan ambigu (`$jml`, `$rek`).
   - **Konstanta**: `UPPER_SNAKE_CASE` Bahasa Indonesia (contoh: `MATA_UANG_RUPIAH = 'IDR'`).
3. **Penamaan Tabel & Kolom Database (`snake_case` Bahasa Indonesia)**:
   - Tabel wajib menggunakan nama: `pengguna`, `nasabah`, `produk_tabungan`, `rekening`, `mutasi_transaksi`, `log_audit`.
   - Kolom Foreign Key wajib berpola `id_<entitas>` (`id_nasabah`, `id_produk`, `id_rekening`, `id_petugas`, `id_pengguna`).
   - Kolom waktu menggunakan `dibuat_pada`, `diperbarui_pada`, `waktu_transaksi`.
4. **Anotasi PHPDoc & Komentar Edukasi Siswa RPL (`@konsep_rpl`)**:
   - Karena projek ini digunakan sebagai bahan pembelajaran siswa **Rekayasa Perangkat Lunak (RPL)**, setiap class di dalam `aplikasi/` **wajib** mencantumkan tag `@konsep_rpl` pada blok PHPDoc-nya untuk menjelaskan konsep arsitektur/keamanan yang diterapkan.
   - Setiap method wajib dilengkapi blok anotasi PHPDoc (`@param`, `@return`, `@throws`) serta komentar penjelasan langkah logika (`// [Langkah X - Konsep RPL]: ...`) dalam Bahasa Indonesia sesuai [`../02-panduan-dev/konvensi-penamaan-dan-anotasi.md`](../02-panduan-dev/konvensi-penamaan-dan-anotasi.md).
5. **Manajemen Versi (`VERSION`), `CHANGELOG.md`, & `LICENSE`**:
   - Nomor versi aktif disimpan di file tunggal `VERSION` (SoT) dalam format SemVer (`MAJOR.MINOR.PATCH`).
   - Setiap penambahan fitur atau perbaikan bug wajib dicatat di `CHANGELOG.md` (atau melalui skrip `naikkan_versi.php`).

---

## 2. Kepatuhan Arsitektur SoT & SoC (Layered MVC-Lite)
- **Single Entry Point**: Seluruh request HTTP wajib masuk melalui `public/index.php`.
- **Batas Tanggung Jawab Lapisan (SoC)**:
  - `Pengendali` (`aplikasi/Pengendali/`): Hanya validasi input HTTP, memanggil `Layanan`, dan melakukan pengalihan **PRG (Post-Redirect-Get)** atau memanggil `Tampilan`. **Dilarang** menulis query SQL atau transaksi DB di `Pengendali`.
  - `Layanan` (`aplikasi/Layanan/`): Satu-satunya pemilik logika bisnis perbankan dan transaksi ACID (`PDO::beginTransaction()`, `commit()`, `rollBack()`). **Dilarang** mengakses `$_POST`/`$_GET` atau mencetak HTML.
  - `Model` (`aplikasi/Model/`): Hanya menjalankan query PDO Prepared Statements ke MySQL InnoDB.
  - `Tampilan` (`aplikasi/Tampilan/`): Hanya menampilkan HTML dengan variabel yang sudah di-escape menggunakan `amankanHtml()` dan format uang via `formatRupiah()`. **Dilarang keras** memanggil query database di `Tampilan`.

---

## 3. Integritas Saldo Rupiah (IDR) & Transaksi ACID (Zero Tolerance)
- **Mata Uang Tunggal Rupiah (`IDR`)**: Seluruh nilai uang dan saldo menggunakan satuan **Rupiah (`IDR`)** dengan tipe data MySQL **`DECIMAL(15,2)`** (storage engine **InnoDB**).
- **DILARANG** menggunakan tipe data `float` atau `double` di MySQL untuk kolom nominal/saldo. Gunakan fungsi `bcmath` (`bcadd`, `bcsub`, `bccomp`) di PHP untuk operasi aritmatika saldo.
- **DILARANG** melakukan `UPDATE rekening SET saldo = ...` di luar blok `PDO::beginTransaction()` dan tanpa terlebih dahulu mengunci baris dengan `SELECT ... FOR UPDATE`.
- **DILARANG** menyediakan fitur atau query `DELETE` maupun `UPDATE nominal` pada tabel `mutasi_transaksi` (*Append-Only / Immutable Ledger*). Koreksi kesalahan hanya boleh melalui transaksi baru bertipe `KOREKSI` (*Jurnal Balik / Reversal*).

---

## 4. Standar Keamanan Aplikasi Keuangan
- **100% PDO Prepared Statements**: Wajib mengaktifkan `PDO::ATTR_EMULATE_PREPARES => false` dan `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`. **DILARANG** menggabungkan variabel langsung ke dalam string SQL.
- **CSRF + Pola PRG**: Wajib memvalidasi token CSRF (`hash_equals`) pada setiap endpoint `POST` dan mengakhiri aksi `POST` dengan pengalihan HTTP (`PRG`) agar penekanan tombol `F5` / *Refresh* tidak menduplikasi transaksi.
- **Sanitasi XSS**: Wajib membungkus seluruh output dinamis di `Tampilan` dengan fungsi `amankanHtml()` (`htmlspecialchars((string) $nilai, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`).
- **Autentikasi & Audit**: Gunakan `password_hash()` / `password_verify()`, `session_regenerate_id(true)` saat masuk (*login*), serta catat aksi sensitif ke tabel `log_audit`.
