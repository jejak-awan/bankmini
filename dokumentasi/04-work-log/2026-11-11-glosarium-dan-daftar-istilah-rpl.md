# Work Log: 2026-11-11 — Penambahan Glosarium & Daftar Istilah Teknis, Kamus Data, dan Fundamental RPL (v1.1.1)

- **Tanggal**: 2026-11-11
- **Pelaksana**: Tim Pengembang & AI Agent
- **Modul Terkait**: Dokumentasi Bahan Belajar RPL (`02-panduan-dev/glosarium-dan-daftar-istilah.md`)
- **Status**: Selesai

## 1. Tujuan Pekerjaan
Menambahkan dokumen **Glosarium & Daftar Istilah** yang menjelaskan seluruh istilah fundamental, teknis, kamus data basis data, transaksi keuangan perbankan (ACID/Rupiah), keamanan web, QA, manajemen versi, dan Git untuk bahan ajar siswa **Rekayasa Perangkat Lunak (RPL)**.

## 2. File yang Dibuat / Diubah
- `dokumentasi/02-panduan-dev/glosarium-dan-daftar-istilah.md` — Kamus istilah lengkap terbagi ke dalam 5 kategori utama.
- `dokumentasi/02-panduan-dev/README.md` — Menambahkan tautan ke Glosarium (poin 8).
- `dokumentasi/02-panduan-dev/skema-database.md` — Menambahkan rujukan silang ke Glosarium Kamus Data.
- `dokumentasi/01-panduan-pengguna/README.md` — Menambahkan tautan cepat ke kamus istilah perbankan & sistem.
- `dokumentasi/README.md` — Memperbarui lokasi folder dokumentasi dan menambahkan tautan Glosarium.
- `dokumentasi/06-aturan-agen/skrip/periksa_kepatuhan_agen.php` — Mendaftarkan `glosarium-dan-daftar-istilah.md` ke daftar dokumen wajib.
- `VERSION` & `CHANGELOG.md` — Naik versi (*patch*) ke `1.1.1` melalui `naikkan_versi.php`.

## 3. Pengujian & Verifikasi
- [x] Skrip `periksa_kepatuhan_agen.php` memverifikasi sinkronisasi `VERSION` (`1.1.1`), `CHANGELOG.md`, dan seluruh dokumen dengan **0 Pelanggaran**.
- [x] Suite QA `jalankan_semua_tes.php` lulus 14/14 skenario.
- [x] Skrip `gerbang_handover_agen.php` lulus verifikasi.

