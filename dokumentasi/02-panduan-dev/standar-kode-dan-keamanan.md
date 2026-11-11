# Standar Kode & Keamanan Finansial (PHP Native)

Aplikasi perbankan menuntut ketelitian tinggi terhadap presisi angka, konkurensi transaksi, dan keamanan akses. Seluruh kode di `C:\xampp\htdocs\www\bankmini` wajib mematuhi standar berikut serta konvensi penamaan di [`konvensi-penamaan-dan-anotasi.md`](./konvensi-penamaan-dan-anotasi.md).

---

## 1. Standar Penulisan Kode PHP & Bahasa Indonesia
1. **Strict Types**: Setiap file `.php` (kecuali file template `Tampilan/` murni) **wajib** diawali dengan deklarasi:
   ```php
   <?php

   declare(strict_types=1);
   ```
2. **Standar Gaya Kode (PSR-12) Berbahasa Indonesia**:
   - Nama Class menggunakan `PascalCase` Bahasa Indonesia (contoh: `LayananTransaksi`, `ModelRekening`, `PengendaliNasabah`).
   - Nama method dan variabel menggunakan `camelCase` Bahasa Indonesia (contoh: `prosesSetorTunai()`, `$saldoSesudah`, `$nominalSetoran`).
   - Nama konstanta menggunakan `UPPER_SNAKE_CASE` Bahasa Indonesia (contoh: `MATA_UANG_RUPIAH = 'IDR'`, `STATUS_AKTIF = 'aktif'`).
   - Nama tabel dan kolom database menggunakan `snake_case` Bahasa Indonesia (contoh: `mutasi_transaksi`, `nomor_rekening`, `saldo_sesudah`).
   - Seluruh anotasi PHPDoc dan komentar penjelasan logika wajib ditulis dalam **Bahasa Indonesia**.

---

## 2. Standar Integritas Saldo, Mata Uang Rupiah (IDR), & Transaksi ACID

> [!CAUTION]
> **Jangan pernah** menggunakan tipe data `FLOAT` atau `DOUBLE` untuk menyimpan atau menghitung uang karena masalah pembulatan *floating-point* biner!

### 2.1 Ketetapan Mata Uang: Rupiah (`IDR`)
- Seluruh produk simpanan, saldo rekening, dan transaksi mutasi di dalam sistem **Bank Mini** menggunakan mata uang tunggal **Rupiah (`IDR`)**.
- **Representasi Database**: Wajib menggunakan MySQL **InnoDB** dengan tipe data **`DECIMAL(15, 2)`** (mendukung nilai hingga Rp 9.999.999.999.999,99 secara presisi tanpa galat pembulatan).
- **Kalkulasi di PHP**: Gunakan ekstensi `bcmath` (`bcadd`, `bcsub`, `bccomp` dengan skala `2`), dan tampilkan ke antarmuka pengguna hanya melalui helper tunggal `formatRupiah()` di `aplikasi/Pembantu/fungsi_umum.php`:
  ```php
  /**
   * Memformat angka nominal menjadi teks mata uang Rupiah (IDR).
   *
   * @param string|int|float $nominal      Nilai nominal uang dalam Rupiah
   * @param bool             $tampilkanSen Tampilkan 2 angka desimal sen jika true
   * @return string Contoh: "Rp 150.000" atau "Rp 150.000,00"
   */
  function formatRupiah(string|int|float $nominal, bool $tampilkanSen = false): string
  {
      $nilai = (float) $nominal;
      return 'Rp ' . number_format($nilai, $tampilkanSen ? 2 : 0, ',', '.');
  }
  ```

### 2.2 Database Transaction & Row Locking Wajib (`SELECT ... FOR UPDATE`)
Setiap operasi perubahan saldo (Setor, Tarik, Transfer, Biaya Admin, maupun Jurnal Koreksi) wajib dieksekusi di dalam `aplikasi/Layanan/LayananTransaksi.php` dalam satu blok transaksi database dengan penguncian baris (`SELECT ... FOR UPDATE`) guna mencegah *Race Condition* (*Double Spending*):

```php
$koneksi->beginTransaction();
try {
    // 1. Kunci baris rekening agar tidak bisa dimodifikasi request lain secara bersamaan
    $pernyataan = $koneksi->prepare(
        'SELECT r.id, r.saldo, r.status, p.saldo_mengendap_minimum
         FROM rekening r
         JOIN produk_tabungan p ON p.id = r.id_produk
         WHERE r.id = :id_rekening
         FOR UPDATE'
    );
    $pernyataan->execute(['id_rekening' => $idRekening]);
    $dataRekening = $pernyataan->fetch(PDO::FETCH_ASSOC);

    if (!$dataRekening || $dataRekening['status'] !== 'aktif') {
        throw new DomainException('Rekening tidak ditemukan atau sedang tidak aktif.');
    }

    // 2. Validasi saldo efektif Rupiah (khusus Tarik / Transfer Keluar)
    $saldoEfektif = bcsub(
        (string) $dataRekening['saldo'],
        (string) $dataRekening['saldo_mengendap_minimum'],
        2
    );
    if (bccomp($saldoEfektif, $nominalRupiah, 2) < 0) {
        throw new DomainException('Saldo efektif Rupiah tidak mencukupi untuk penarikan.');
    }

    $saldoSebelum = (string) $dataRekening['saldo'];
    $saldoSesudah = bcsub($saldoSebelum, $nominalRupiah, 2);

    // 3. Simpan catatan mutasi ke tabel mutasi_transaksi (Append-Only / Immutable Ledger)
    // 4. Perbarui kolom saldo di tabel rekening = $saldoSesudah
    // 5. Simpan jejak audit ke tabel log_audit

    $koneksi->commit();
} catch (Throwable $pengecualian) {
    if ($koneksi->inTransaction()) {
        $koneksi->rollBack();
    }
    throw $pengecualian;
}
```

### 2.3 Pencegahan Deadlock pada Transfer Antar Rekening
Saat memproses transfer dari Rekening Asal ke Rekening Tujuan, kunci kedua baris rekening (`SELECT ... FOR UPDATE`) secara berurutan berdasarkan **`rekening.id` terkecil terlebih dahulu** sebelum memvalidasi dan mengubah saldo:
```php
$idPertama = min($idRekeningAsal, $idRekeningTujuan);
$idKedua   = max($idRekeningAsal, $idRekeningTujuan);
$this->modelRekening->kunciRekeningUntukPembaruan($idPertama);
$this->modelRekening->kunciRekeningUntukPembaruan($idKedua);
```

### 2.4 Append-Only / Immutable Ledger (Jurnal Balik / Reversal)
- Tabel `mutasi_transaksi` bersifat **Append-Only**.
- Dilarang menyediakan fungsi `DELETE` atau `UPDATE nominal` pada `mutasi_transaksi`.
- Jika terjadi kesalahan input oleh Teller, koreksi hanya boleh dilakukan melalui transaksi baru bertipe `KOREKSI` (*Jurnal Balik / Reversal*) yang membalik mutasi sebelumnya dan menyimpan `id_referensi_transaksi` dari transaksi yang dikoreksi.

---

## 3. Standar Keamanan Aplikasi Keuangan

1. **100% PDO Prepared Statements (`EMULATE_PREPARES = false`)**:
   - Konfigurasi wajib pada `aplikasi/Inti/BasisData.php`:
     ```php
     $opsiPdo = [
         PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES   => false,
     ];
     ```
   - Dilarang keras menggabungkan variabel langsung ke dalam string SQL.
2. **Proteksi Token CSRF & Pola Post-Redirect-Get (PRG)**:
   - Setiap form `POST` wajib menyertakan `<input type="hidden" name="token_csrf" value="<?= amankanHtml(TokenCsrf::ambilToken()) ?>">`.
   - Validasi di sisi server wajib menggunakan perbandingan waktu konstan `hash_equals($_SESSION['token_csrf'], $_POST['token_csrf'])`.
   - Setelah request `POST` selesai diproses (baik sukses maupun gagal validasi), `Pengendali` **wajib** melakukan pengalihan HTTP (`302 Found` / `303 See Other`) ke halaman `GET` (pola **PRG**) dan merotasi token transaksi agar penekanan tombol `F5` / *Refresh* pada browser tidak pernah menduplikasi transaksi.
3. **Sanitasi XSS (`htmlspecialchars`)**:
   - Seluruh output variabel dinamis pada `aplikasi/Tampilan/` wajib melewati helper `amankanHtml()`:
     ```php
     /**
      * Mengamankan teks dari serangan Cross-Site Scripting (XSS) sebelum dicetak ke HTML.
      *
      * @param mixed $nilai Nilai yang akan ditampilkan di HTML
      * @return string Teks yang sudah di-escape dengan aman
      */
     function amankanHtml(mixed $nilai): string
     {
         return htmlspecialchars((string) $nilai, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
     }
     ```
4. **Hardening Sesi, Idle Timeout, & Anti Brute-Force**:
   - Gunakan `password_hash($kataSandi, PASSWORD_BCRYPT)` dan `password_verify()` pada kolom `hash_kata_sandi`.
   - Panggil `session_regenerate_id(true)` setiap kali pengguna berhasil masuk (*login*) atau terjadi perubahan peran.
   - Cookie sesi wajib menggunakan atribut `httponly = true`, `use_strict_mode = 1`, `samesite = 'Lax'`, dan `secure` otomatis saat berjalan di atas HTTPS.
   - `Sesi::periksaKedaluwarsaSesi(1800)` menghancurkan sesi secara otomatis bila petugas tidak aktif selama 30 menit (*idle timeout*).
   - `PembatasLaju` di `LayananAutentikasi` membatasi percobaan login gagal (maksimal 5 kali per 300 detik per `nama_pengguna + IP`) lalu mencatat insiden melalui `PencatatLog::keamanan()`.

---

## 4. Header Keamanan HTTP (OWASP) & Content-Security-Policy (CSP)

Melalui `PenanganGalat::kirimHeaderKeamanan()` di `public/index.php`, setiap respons HTTP menyertakan header perlindungan browser modern:
1. **`X-Frame-Options: SAMEORIGIN`** — Mencegah halaman dasbor disisipkan ke dalam `<iframe>` situs pihak ketiga (*Clickjacking*).
2. **`X-Content-Type-Options: nosniff`** — Melarang browser menebak tipe MIME secara sembarangan.
3. **`Referrer-Policy: strict-origin-when-cross-origin`** — Melindungi kebocoran URL internal saat membuka tautan eksternal.
4. **`Permissions-Policy`** — Menonaktifkan akses kamera, mikrofon, geolokasi, dan sensor pembayaran yang tidak dibutuhkan.
5. **`Content-Security-Policy` Ketat (`script-src 'self'`)** — Browser hanya mengizinkan eksekusi file `.js` yang berasal dari domain sendiri. Oleh karena itu, **dilarang menulis `<script>...</script>` inline maupun atribut `onclick="..."` di file `Tampilan/`**; gunakan atribut `data-*` yang ditangani oleh `inti-ui.js` atau `dasbor-interaktif.js`.

---

## 5. Logging Terpusat, Penanganan Galat (403/404/500/503), & Proteksi Direktori

1. **Pencatatan Log Harian (`PencatatLog`)**:
   - Seluruh error PHP, peringatan rute, dan kejadian keamanan ditulis ke `penyimpanan/log/aplikasi-YYYY-MM-DD.log` disertai `idKorelasi` (`req-YYYYMMDD-...`).
   - Kata kunci sensitif (`kata_sandi`, `hash_kata_sandi`, `token_csrf`, `token`, `rahasia`) disensor otomatis menjadi **`[DIRAHASIAKAN]`**.
2. **Halaman Galat Terpusat & Sakelar `mode_debug` (`PenanganGalat`)**:
   - Kode status `403` (RBAC), `404` (rute tidak ada), `500` (galat internal), dan `503` (mode pemeliharaan via file `penyimpanan/pemeliharaan.flag`) dirender rapi memakai Design System di `aplikasi/Tampilan/galat/halaman_galat.php`.
   - Saat **`mode_debug = true`** (pengembangan RPL), kelas pengecualian, berkas, nomor baris, dan *stack trace* ditampilkan untuk memudahkan pembelajaran/debugging.
   - Saat **`mode_debug = false`** (produksi via `BANKMINI_MODE_DEBUG=false`), rincian teknis disembunyikan total dan pengguna hanya melihat pesan ramah + **ID Korelasi** untuk dilaporkan ke admin.
3. **Isolasi Folder Non-Publik (`.htaccess`)**:
   - Folder internal (`aplikasi/`, `konfigurasi/`, `penyimpanan/`, `basis_data/`, `pengujian/`) dan berkas sensitif (`.env`, `.git`, `composer.*`) diblokir akses HTTP langsungnya dengan `Require all denied` serta `Options -Indexes`.

---

## 6. Standar Performa (SARGable SQL, Cache-Busting, Gzip, & `/kesehatan`)

1. **Kueri SARGable (*Search Argument Able*)**: Di `ModelMutasi::ambilMutasiBerdasarkanTanggal()`, filter tanggal wajib membandingkan kolom langsung (`WHERE m.waktu_transaksi >= :waktu_mulai AND m.waktu_transaksi <= :waktu_akhir`), **bukan** membungkus kolom dengan fungsi seperti `WHERE DATE(m.waktu_transaksi) ...` yang menonaktifkan indeks `idx_mutasi_waktu`.
2. **Cache-Busting & Kompresi Aset**: Seluruh CSS dan JS menyertakan parameter `?v=VERSI` dari `VERSION` (`$versiAplikasi`) dan dilayani dengan `mod_deflate` (Gzip) + `mod_expires` di `public/.htaccess`.
3. **Pemantauan Kesehatan (`GET /kesehatan`)**: Menyediakan JSON kesiapan server (status database InnoDB `SELECT 1`, kesiapan tulis `penyimpanan/log`, versi aplikasi, dan status mode pemeliharaan) untuk probe Docker `HEALTHCHECK` tanpa membocorkan kredensial database.
