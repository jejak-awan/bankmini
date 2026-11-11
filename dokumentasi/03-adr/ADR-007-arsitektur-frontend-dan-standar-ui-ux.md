# ADR-007: Arsitektur Frontend & Standar UI/UX (Bootstrap 5 Offline + Vanilla JS)

- **Status**: Disetujui (Accepted)
- **Tanggal**: 2026-11-11
- **Konteks Pembelajaran**: Jenjang SMK Rekayasa Perangkat Lunak (RPL)

---

## 1. Konteks Masalah
Aplikasi **Bank Mini** dibangun sebagai media pembelajaran siswa SMK RPL dengan pendekatan *Server-Side Rendering (SSR)* PHP Native (tanpa Node.js, *bundler*, atau SPA kompleks). Agar ramah lingkungan laboratorium sekolah (sering *offline* tanpa internet) sekaligus mengajarkan standar antarmuka perbankan yang aman dari kesalahan manusia (*error prevention*), dibutuhkan keputusan arsitektur Frontend & UI/UX yang baku.

## 2. Opsi yang Dipertimbangkan
1. **CSS Custom Murni tanpa Framework**: Ringan tetapi siswa harus menulis banyak aturan *grid*, modal, *offcanvas*, dan responsivitas secara manual.
2. **Tailwind CSS CDN / Build Node.js**: Memerlukan internet atau proses `npm run build` yang menambah kompleksitas lingkungan XAMPP SMK.
3. **Bootstrap 5 (Offline Vendor Bundle) + Bootstrap Icons + Vanilla JS + CSS Perbankan**: Tidak butuh Node.js atau internet, langsung jalan di `htdocs`, familier bagi siswa SMK RPL, kaya komponen aksesibel, serta dapat diperkuat dengan lapisan CSS/JS khusus perbankan.

## 3. Keputusan Arsitektur
Tim memilih **Opsi 3: Bootstrap 5.3.3 (Offline) + Bootstrap Icons 1.11.3 (Offline) + Vanilla JS (`aplikasi.js`) + CSS Tema Perbankan (`gaya.css`)**:
1. **100% Aset Lokal Offline**: Disimpan di `public/aset/vendor/bootstrap/` dan `public/aset/vendor/bootstrap-icons/`, sehingga tetap berjalan tanpa koneksi internet di laboratorium sekolah.
2. **Pemisahan Tanggung Jawab (SoC) di Lapisan View**:
   - File `.php` di `aplikasi/Tampilan/` hanya merender HTML + Bootstrap menggunakan data yang dikirim Controller.
   - Seluruh variabel lokal di dalam view wajib `$camelCase` Bahasa Indonesia dan dicetak melalui `amankanHtml()`.
   - View **dilarang** memanggil SQL, Model, Service, `$_SESSION`, atau `konfigurasi/`.
3. **Standar UX Perbankan Khusus (Error Prevention)**:
   - **Pratinjau Rupiah Live** (`data-pratinjau-rupiah`): mencegah salah ketik angka nol (`Rp 500.000` vs `Rp 50.000`).
   - **Pencarian Cepat Dropdown & Info Saldo Efektif** (`data-cari-select`, `data-target-info-saldo`): menampilkan Saldo Total, Mengendap Wajib, dan Saldo Efektif secara langsung sebelum teller mengirim form.
   - **Modal Konfirmasi Transaksi XSS-Safe** (`data-konfirmasi-transaksi="ya"`): ringkasan tujuan & nominal dibentuk lewat DOM API (`createElement` + `textContent`), bukan string HTML mentah.
   - **Tabular Numerals** (`.angka-keuangan`): angka Rupiah sejajar vertikal per digit dan rata kanan (`text-end`).
   - **Pencegahan Double Submit** (`data-cegah-ganda="ya"`): menonaktifkan tombol kirim saat form diproses.
   - **Siap Cetak (@media print)**: menyembunyikan bilah sisi, tombol, dan filter saat mencetak laporan mutasi (`.tidak-cetak`, `.hanya-cetak`).

## 4. Konsekuensi
- Ukuran folder `public/aset/vendor/` bertambah ~500 KB, namun menjamin keandalan 100% *offline*.
- Setiap halaman baru yang ditambahkan di masa depan wajib memanfaatkan komponen Bootstrap 5, atribut `data-*` standar di `aplikasi.js`, dan mematuhi aturan kepatuhan di `periksa_kepatuhan_agen.php`.
