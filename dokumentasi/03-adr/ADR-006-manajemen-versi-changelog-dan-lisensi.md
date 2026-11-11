# ADR-006: Standar Manajemen Versi (SemVer + Tanggal), Changelog, Bump Version, & Lisensi MIT

- **Status**: Disetujui (*Accepted*)
- **Tanggal**: 2026-11-11
- **Pengambil Keputusan**: Tim Pengembang & Arsitek Sistem

## 1. Konteks dan Permasalahan
Sebagai projek standar rekayasa perangkat lunak sekaligus bahan ajar bagi siswa **RPL (Rekayasa Perangkat Lunak)**, sistem **Bank Mini** memerlukan tata kelola rilis yang jelas: bagaimana nomor versi ditentukan (SemVer vs CalVer), di mana sumber kebenaran (*Single Source of Truth*) versi disimpan, bagaimana riwayat perubahan dicatat (`CHANGELOG.md`), serta bagaimana lisensi penggunaan kode diatur (`LICENSE`).

## 2. Keputusan Arsitektur
1. **Skema Penomoran Versi**:
   - Menggunakan **Semantic Versioning (`MAJOR.MINOR.PATCH`)** yang dipadukan dengan stempel tanggal rilis (`YYYY-MM-DD`) pada `CHANGELOG.md`.
2. **Single Source of Truth (SoT) Versi**:
   - Nomor versi aktif disimpan di file tunggal `VERSION` pada root projek (`C:\xampp\htdocs\www\bankmini\VERSION`).
   - File `konfigurasi/aplikasi.php` wajib membaca nilai versi secara dinamis dari file `VERSION`.
3. **Pencatatan `CHANGELOG.md` & Otomasi *Bump Version***:
   - Seluruh rilis perubahan fitur wajib dicatat di `CHANGELOG.md` menggunakan format *Keep a Changelog* berbahasa Indonesia.
   - Disediakan skrip CLI `dokumentasi/06-aturan-agen/skrip/naikkan_versi.php` untuk menaikkan versi (`major`, `minor`, `patch`) dan menyinkronkan `VERSION` serta `CHANGELOG.md`.
4. **Lisensi Perangkat Lunak (`LICENSE`)**:
   - Menggunakan **MIT License** yang dilengkapi penjelasan edukasi Bahasa Indonesia untuk siswa RPL.
5. **Penegakan Otomatis**:
   - Skrip `periksa_kepatuhan_agen.php` memverifikasi keberadaan `VERSION`, `CHANGELOG.md`, `LICENSE`, serta kecocokan versi di `VERSION` dengan entri terbaru di `CHANGELOG.md`.

## 3. Konsekuensi
- **Positif**: Siswa RPL dan pengembang dapat melacak evolusi fitur secara tertib, tidak ada perbedaan nomor versi antar file, dan repositori GitHub memenuhi standar projek *open-source* profesional.
- **Negatif**: Setiap kenaikan versi fitur baru harus dijalankan melalui `naikkan_versi.php` atau menyinkronkan `VERSION` dan `CHANGELOG.md`.

