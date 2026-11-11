# Status Handover & Konteks Aktif Agen (SSOT Lintas Sesi)

- **Pembaruan Terakhir**: YYYY-MM-DD
- **Work Log Terakhir**: [`YYYY-MM-DD-nama-log.md`](../04-work-log/YYYY-MM-DD-nama-log.md)
- **Fase Projek Saat Ini**: [Contoh: Fase 2 — Implementasi Fitur Transaksi]
- **Status Kepatuhan & QA**: LULUS 100% (0 Pelanggaran)

---

## 1. Ringkasan Konteks Wajib (Jangan Diubah Tanpa ADR Baru)
- **Bahasa & Konvensi**: 100% Bahasa Indonesia (PSR-12: Class `PascalCase`, method/variabel `camelCase`, tabel/kolom `snake_case`, wajib PHPDoc Bahasa Indonesia).
- **Arsitektur SoT & SoC**: `public/index.php` -> `Pengendali` -> `Layanan` (Batas Transaksi ACID) -> `Model` (PDO Prepared Statements) -> `Tampilan` (`amankanHtml()`, `formatRupiah()`).
- **Standar Keuangan**: Mata uang tunggal **Rupiah (`IDR`)** dengan tipe MySQL InnoDB `DECIMAL(15,2)`, penguncian baris `SELECT ... FOR UPDATE`, dan `mutasi_transaksi` bersifat *Append-Only*.

---

## 2. Komponen yang Sudah Selesai & Terverifikasi
- [x] [Nama Komponen 1 beserta jalur file]
- [x] [Nama Komponen 2 beserta jalur file]

---

## 3. Tugas Aktif / Sedang Dikerjakan
- [ ] [Tidak ada tugas menggantung / Sebutkan file yang sedang dikerjakan jika ada]

---

## 4. Langkah Selanjutnya (Prioritas Agen Berikutnya)
1. **[Prioritas 1]**: [Deskripsi spesifik tugas berikutnya + file yang harus dibuat/diubah]
2. **[Prioritas 2]**: [Deskripsi spesifik tugas berikutnya + file tes QA yang harus ditambahkan]

---

## 5. Catatan Konteks Penting & Keputusan Terakhir
- [Catatan teknis penting untuk agen selanjutnya]

