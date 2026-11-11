# ADR-001: Arsitektur Layered MVC-Lite, Single Source of Truth (SoT), & Separation of Concerns (SoC)

- **Status**: Disetujui (*Accepted*)
- **Tanggal**: 2026-11-11
- **Pengambil Keputusan**: Tim Pengembang & Arsitek Sistem

## 1. Konteks dan Permasalahan
Projek **Bank Mini** dibangun menggunakan **PHP Native** di lingkungan XAMPP (`C:\xampp\htdocs\www\bankmini`). Risiko terbesar pada PHP Native tradisional adalah *spaghetti code* — di mana koneksi DB, query SQL, perhitungan saldo, dan tag HTML bercampur di satu file (`setor.php`, `tarik.php`). Hal ini menyebabkan:
- Inkonsistensi saldo dan aturan bisnis karena tidak adanya *Single Source of Truth (SoT)*.
- Celah keamanan (SQL Injection, CSRF, bypass RBAC) mudah terlewat.
- Sulit dipelihara ketika fitur bertambah.

## 2. Keputusan Arsitektur
Menerapkan pola **Layered MVC-Lite + Service Layer** berbahasa Indonesia (`public/index.php` -> `Pengendali` -> `Layanan` -> `Model` -> `Tampilan`) dengan aturan **SoT** dan **SoC** berikut:

### A. Ketetapan Single Source of Truth (SoT)
1. **SoT Saldo & Transaksi**: Tabel `mutasi_transaksi` adalah sumber kebenaran mutlak riwayat keuangan. Kolom `rekening.saldo` hanya boleh diubah oleh `LayananTransaksi` bersamaan dengan pencatatan mutasi di dalam satu transaksi database.
2. **SoT Koneksi Database**: Hanya `aplikasi/Inti/BasisData.php` yang boleh membuat koneksi PDO (`Singleton` melalui `BasisData::ambilKoneksi()`), menjamin `beginTransaction()` bekerja pada instance koneksi yang sama.
3. **SoT Pintu Masuk & Keamanan**: `public/index.php` adalah satu-satunya *entry point* HTTP. Seluruh pemeriksaan sesi (`Sesi.php`), hak akses (`MiddlewareAutentikasi.php`), dan token CSRF (`TokenCsrf.php`) terpusat di lapisan `aplikasi/Inti/`.
4. **SoT Format Rupiah & Sanitasi**: Pemformatan mata uang Rupiah (`formatRupiah()`) dan sanitasi output HTML (`amankanHtml()`) terpusat di `aplikasi/Pembantu/fungsi_umum.php`.

### B. Ketetapan Separation of Concerns (SoC)
1. **Pengendali (`aplikasi/Pengendali/`)**: Hanya menangani HTTP request/response, validasi input awal, dan pengalihan **PRG (Post-Redirect-Get)**. Dilarang menulis query SQL atau membuka transaksi DB.
2. **Layanan (`aplikasi/Layanan/`)**: Satu-satunya tempat logika bisnis perbankan dan batas transaksi ACID (`beginTransaction`, `commit`, `rollBack`). Bebas dari kode HTML dan akses langsung ke superglobal `$_POST`/`$_GET`.
3. **Model (`aplikasi/Model/`)**: Hanya mengeksekusi query PDO Prepared Statements (`SELECT`, `INSERT`, `UPDATE`, termasuk `SELECT ... FOR UPDATE`).
4. **Tampilan (`aplikasi/Tampilan/`)**: Hanya berisi template tampilan HTML dan pemanggilan helper `amankanHtml()` serta `formatRupiah()`. Dilarang keras memanggil `Model`/`BasisData`.

## 3. Konsekuensi
- **Positif**: Kode rapi, aman, mudah diaudit, bebas duplikasi logika saldo, dan tetap ringan berjalan di XAMPP tanpa ketergantungan framework eksternal.
- **Negatif / Mitigasi**: Membutuhkan disiplin penempatan file sesuai lapisan, yang dijaga ketat melalui [`02-panduan-dev/arsitektur-sistem.md`](../02-panduan-dev/arsitektur-sistem.md) dan [`06-aturan-agen/`](../06-aturan-agen/README.md).
