# Spesifikasi Fitur: [Nama Fitur]

- **Modul**: [Contoh: Transaksi / Master Data / Laporan]
- **Aktor / Role**: [`admin` | `teller` | `supervisor` | `nasabah`]
- **Prioritas**: Tinggi | Sedang | Rendah

## 1. Deskripsi Bisnis
[Penjelasan singkat tujuan fitur dan manfaatnya bagi operasional Bank Mini.]

## 2. Aturan Bisnis & Validasi (Business Rules)
1. [Contoh: Nominal setoran harus berupa angka positif minimal Rp 5.000.]
2. [Contoh: Rekening tujuan harus berstatus `aktif`.]

## 3. Alur Proses (Flow)
1. Pengguna membuka halaman [...]
2. Sistem memvalidasi input & CSRF token [...]
3. Sistem membuka transaksi database (`beginTransaction`) dan mengunci baris rekening (`FOR UPDATE`) [...]
4. Sistem menyimpan mutasi dan memperbarui saldo [...]

## 4. Dampak Skema Database
- Tabel yang dibaca: `[nama_tabel]`
- Tabel yang ditulis: `[nama_tabel]`

## 5. Skenario Pengujian (Acceptance Criteria)
- **Kasus Positif**: [Input valid -> Saldo bertambah sesuai nominal & mutasi tercatat]
- **Kasus Negatif**: [Saldo tidak cukup -> Transaksi ditolak & `rollBack()`]

