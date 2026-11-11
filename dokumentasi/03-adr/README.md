# Architecture Decision Records (ADR)

Folder ini menyimpan seluruh keputusan arsitektur penting dalam pengembangan **Bank Mini**. Setiap kali tim atau agen AI mengambil keputusan besar terkait arsitektur kode, skema database, keamanan, atau konvensi penamaan, keputusan tersebut wajib dicatat di sini.

## Indeks ADR

| ID | Judul Keputusan | Status | Tanggal |
| :--- | :--- | :--- | :--- |
| [ADR-001](./ADR-001-php-native-mvc-lite.md) | Arsitektur Layered MVC-Lite, Single Source of Truth (SoT), & Separation of Concerns (SoC) | **Disetujui (Accepted)** | 2026-11-11 |
| [ADR-002](./ADR-002-integritas-transaksi-keuangan.md) | Standar Transaksi Mata Uang Rupiah (IDR), Ledger Append-Only, & Penguncian ACID | **Disetujui (Accepted)** | 2026-11-11 |
| [ADR-003](./ADR-003-standar-keamanan-aplikasi-keuangan.md) | Standar Keamanan Aplikasi Keuangan (PDO Prepared Statements, CSRF + PRG, & Sanitasi XSS) | **Disetujui (Accepted)** | 2026-11-11 |
| [ADR-004](./ADR-004-konvensi-penamaan-bahasa-indonesia.md) | Konvensi Penamaan, Struktur Folder, Tabel, & Anotasi dalam Bahasa Indonesia | **Disetujui (Accepted)** | 2026-11-11 |
| [ADR-005](./ADR-005-standar-qa-dan-skrip-penjaga-konsistensi.md) | Standar Pengujian QA & Skrip Otomatis Penjaga Konsistensi Aturan Agen | **Disetujui (Accepted)** | 2026-11-11 |
| [ADR-006](./ADR-006-manajemen-versi-changelog-dan-lisensi.md) | Standar Manajemen Versi (SemVer + Tanggal), Changelog, Bump Version, & Lisensi MIT | **Disetujui (Accepted)** | 2026-11-11 |
| [ADR-007](./ADR-007-arsitektur-frontend-dan-standar-ui-ux.md) | Arsitektur Frontend & Standar UI/UX (Bootstrap 5 Offline + Vanilla JS) | **Disetujui (Accepted)** | 2026-11-11 |
| [ADR-008](./ADR-008-ci-cd-lokal-docker-dan-modul-praktikum-rpl.md) | Pipeline CI/CD Lokal Berbasis Docker/Native & Modul Praktikum Adaptasi Siswa RPL | **Disetujui (Accepted)** | 2026-11-12 |
| [ADR-009](./ADR-009-fondasi-keamanan-performa-logging-dan-penanganan-galat.md) | Fondasi Keamanan Berlapis, Performa Kueri/Aset, Logging Terpusat, & Penanganan Galat | **Disetujui (Accepted)** | 2026-11-12 |

## Cara Menambahkan ADR Baru
Salin template dari [`../05-template-dokumentasi/template-adr.md`](../05-template-dokumentasi/template-adr.md), beri nomor urut berikutnya (`ADR-010-...`), dan daftarkan pada tabel indeks di atas.
