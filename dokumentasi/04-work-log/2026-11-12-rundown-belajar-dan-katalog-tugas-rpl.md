# Work Log: Rundown Belajar 8 Pertemuan & Katalog 12 Tugas Pengembangan Modul/Fitur RPL (`v1.8.2`)

- **Tanggal**: 2026-11-12
- **Versi Terkait**: `1.8.2`
- **Status**: **Selesai & Lulus Seluruh Gerbang QA (100%)**

---

## Ringkasan Pekerjaan

1. **Pemutakhiran Kurikulum & Rundown Belajar Terstruktur (`dokumentasi/02-panduan-dev/alur-belajar-dan-modul-praktikum-rpl.md`)**
   - Mengubah alur belajar 5 modul lama menjadi **Rundown Belajar Terstruktur 8 Pertemuan (2–4 JP per pertemuan)** yang selaras 100% dengan kondisi kode `v1.8.1+` (Portal Default `/`, Multi-Shell Tata Letak `publik`/`dasbor`/`cetak`, Design System `komponenUi()`, `PencatatLog`, `PenanganGalat`, `PembatasLaju`, SARGable SQL, `LayananDokumenKantor`, PHPStan Level 8, dan Playwright E2E).
   - Menambahkan target kompetensi, urutan file bacaan wajib, serta *Checkpoint Pemahaman* pada setiap pertemuan, sekaligus memperbarui bedah fitur Setor Tunai Rp 50.000 hulu-ke-hilir agar sesuai dengan simbol kode terbaru (`daftarPost()`, `kepala_dasbor.php`, `dasbor-interaktif.js`).

2. **Pembuatan Katalog 12 Tugas Pengembangan Modul & Fitur (`dokumentasi/02-panduan-dev/katalog-tugas-pengembangan-fitur-rpl.md`)**
   - Memetakan status fitur saat ini vs celah modul yang siap dikerjakan siswa dalam 4 tingkatan:
     - **Tingkat 1 (Dasar)**: `TUGAS-01` (Filter Kelas/Jenis Nasabah), `TUGAS-02` (Kartu Ringkasan `KREDIT`/`DEBIT` `BCMath`), `TUGAS-03` (Cetak Struk Satuan `'cetak'` + `kop_surat`).
     - **Tingkat 2 (Menengah)**: `TUGAS-04` (Ubah & Nonaktifkan Nasabah + Audit JSON), `TUGAS-05` (CRUD Produk Tabungan Khusus `admin`), `TUGAS-06` (Pembekuan/Penutupan Rekening Atomik ACID & Detail Buku Tabungan).
     - **Tingkat 3 (Mahir)**: `TUGAS-07` (Ekspor PDF & Excel `.xlsx` Mutasi via `LayananDokumenKantor`), `TUGAS-08` (Impor Massal Nasabah dari Excel/CSV), `TUGAS-09` (Surat Keterangan Word `.docx` & Pemotongan `BIAYA_ADMIN` Bulanan Massal).
     - **Tingkat 4 (Spesialis & UKK)**: `TUGAS-10` (Halaman Jejak Audit `log_audit` & Inspektor Log Harian), `TUGAS-11` (Proyek Akhir UKK Portal Mandiri Siswa/Nasabah *Read-Only* Anti-IDOR), `TUGAS-12` (Ekspansi E2E Playwright Transfer, Koreksi, & RBAC `403`).
   - Melengkapi katalog dengan **Rubrik Penilaian Guru/Asesor UKK (Skala 100)** dan daftar periksa *Quality Gate*.

3. **Sinkronisasi Indeks Dokumentasi**
   - Memperbarui [`dokumentasi/02-panduan-dev/README.md`](../02-panduan-dev/README.md) serta [`status-handover-agen.md`](../06-aturan-agen/status-handover-agen.md); katalog baru otomatis terindeks pada pembaca web `/dokumentasi`.
