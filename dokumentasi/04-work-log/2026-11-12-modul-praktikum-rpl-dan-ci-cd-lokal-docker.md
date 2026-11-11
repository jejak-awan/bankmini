# Work Log: Paket Pembelajaran & Adaptasi Siswa RPL + Pipeline CI/CD Lokal Docker/Native (`v1.4.0`)

- **Tanggal**: 2026-11-12
- **Versi Terkait**: `1.4.0`
- **Status**: Selesai & Terverifikasi 100%

---

## Ringkasan Pekerjaan
Melengkapi kebutuhan adaptasi dan pembelajaran siswa SMK RPL (peta belajar 5 modul, bedah kode *end-to-end*, resep menambah fitur baru, latihan praktikum, skrip onboarding 1 klik, serta konfigurasi VS Code) sekaligus membangun **Pipeline CI/CD Lokal Dual-Engine (`Docker` / `Native XAMPP`)** agar pengujian otomatis berjalan penuh di lokal tanpa menghabiskan kuota menit GitHub Actions Runner.

## Perubahan yang Dilakukan
1. **Dukungan Variabel Lingkungan (`getenv`) & Onboarding Lingkungan Siswa**:
   - Memperbarui [`konfigurasi/basis_data.php`](../../konfigurasi/basis_data.php) dan [`konfigurasi/aplikasi.php`](../../konfigurasi/aplikasi.php) agar mendukung variabel lingkungan `BANKMINI_*` (untuk Docker/CI) dengan *fallback* otomatis ke pengaturan XAMPP lokal.
   - Menambahkan [`.env.example`](../../.env.example), [`.editorconfig`](../../.editorconfig), [`.vscode/extensions.json`](../../.vscode/extensions.json), dan [`.vscode/tasks.json`](../../.vscode/tasks.json).
   - Membuat skrip onboarding satu perintah [`siapkan-projek.ps1`](../06-aturan-agen/skrip/siapkan-projek.ps1).
2. **Paket Docker & Pipeline CI/CD Lokal (0 Kuota GitHub Runner)**:
   - Menambahkan [`Dockerfile`](../../Dockerfile), [`docker-compose.yml`](../../docker-compose.yml), dan [`docker-compose.ci.yml`](../../docker-compose.ci.yml).
   - Membuat orkestrator CI/CD lokal [`jalankan-ci-lokal.ps1`](../06-aturan-agen/skrip/jalankan-ci-lokal.ps1) yang mendukung mode `otomatis`, `docker`, dan `native`.
3. **Materi Pembelajaran, Bedah Kode, & Dokumentasi Arsitektur**:
   - Menerbitkan [`alur-belajar-dan-modul-praktikum-rpl.md`](../02-panduan-dev/alur-belajar-dan-modul-praktikum-rpl.md) (5 Modul Belajar, Bedah Fitur Setor Tunai Hulu-ke-Hilir, Resep 6 Langkah Fitur Baru, & 5 Tantangan Praktikum).
   - Menerbitkan [`panduan-ci-cd-lokal-docker.md`](../02-panduan-dev/panduan-ci-cd-lokal-docker.md) dan [`ADR-008-ci-cd-lokal-docker-dan-modul-praktikum-rpl.md`](../03-adr/ADR-008-ci-cd-lokal-docker-dan-modul-praktikum-rpl.md).
   - Memperbarui [`README.md`](../../README.md) utama, [`01-panduan-pengguna/README.md`](../01-panduan-pengguna/README.md), [`02-panduan-dev/README.md`](../02-panduan-dev/README.md), [`03-adr/README.md`](../03-adr/README.md), serta memperketat [`periksa_kepatuhan_agen.php`](../06-aturan-agen/skrip/periksa_kepatuhan_agen.php).

## Verifikasi Akhir
- `siapkan-projek.ps1` & `jalankan-ci-lokal.ps1`: Lulus 100% (`0 Error & 0 Warning`).
- `periksa_kepatuhan_agen.php`: 2415 titik pemeriksaan — 0 Pelanggaran.
- `phpcs` (Zero Warning), `phpstan` (**Level 8** Zero Error), `jalankan_semua_tes.php` (`15/15` strict `E_ALL`), dan `playwright test` (`3/3` E2E): Lulus 100%.
