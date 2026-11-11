# Dokumen Skenario Pengujian QA: [Nama Modul / Fitur]

- **Kode Modul**: `[Contoh: TRX / REK / AUT]`
- **File Tes Otomatis**: `C:\xampp\htdocs\www\bankmini\pengujian\[Unit|Integrasi]\Tes[NamaKomponen].php`
- **Tanggal Pengujian**: YYYY-MM-DD
- **Status QA**: Lulus Semua (*Pass*) | Gagal (*Fail*)

---

## 1. Daftar Skenario Pengujian (Test Cases)

| ID Skenario | Kategori | Method Tes Otomatis (`uji...`) | Prasyarat / Input | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `QA-XXX-001` | Positif (*Happy Path*) | `uji...()` | [Contoh: Saldo awal Rp 100.000, setor Rp 50.000] | [Saldo menjadi Rp 150.000 & mutasi tercatat] | [ ] Lulus |
| `QA-XXX-002` | Negatif / Batas | `uji...()` | [Contoh: Tarik Rp 90.000 saat saldo mengendap Rp 20.000] | [Melempar `DomainException`, transaksi `rollBack()`] | [ ] Lulus |
| `QA-XXX-003` | Integritas ACID | `uji...()` | [Contoh: Kegagalan di tengah proses transfer] | [Saldo rekening asal & tujuan tidak berubah] | [ ] Lulus |
| `QA-XXX-004` | Keamanan | `uji...()` | [Contoh: Input keterangan `<script>alert(1)</script>`] | [Teks di-escape oleh `amankanHtml()`] | [ ] Lulus |

---

## 2. Verifikasi Kepatuhan Aturan Agen
- [ ] Skrip `periksa_kepatuhan_agen.php` dijalankan dan menghasilkan **0 Pelanggaran**.
- [ ] Seluruh method tes memiliki anotasi `@skenario` dalam Bahasa Indonesia.
- [ ] Tidak ada perubahan saldo di luar transaksi ACID Rupiah (`DECIMAL(15,2)`).

