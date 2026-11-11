# Manajemen Versi (SemVer & CalVer), Lisensi, & Changelog — Bahan Belajar RPL

Dokumen ini merupakan panduan teknis sekaligus **materi pembelajaran Rekayasa Perangkat Lunak (RPL)** mengenai cara mengelola siklus rilis perangkat lunak (*Software Release Management*), penomoran versi, pencatatan perubahan (`CHANGELOG.md`), dan lisensi kode sumber (`LICENSE`).

---

## 1. Mengenal Penomoran Versi: SemVer vs CalVer (Konsep RPL)

Dalam industri perangkat lunak, terdapat dua standar penomoran versi yang paling populer:

| Karakteristik | Semantic Versioning (SemVer 2.0.0) | Calendar Versioning (CalVer) |
| :--- | :--- | :--- |
| **Pola Format** | `MAJOR.MINOR.PATCH` (contoh: `1.1.0`) | `YYYY.MM.MICRO` (contoh: `2026.11.1`) |
| **Fokus Utama** | Mengukur **dampak perubahan kode** & kompatibilitas fitur/API. | Mengukur **waktu rilis** berdasarkan kalender. |
| **Kapan Angka Pertama Naik?** | Saat ada perubahan besar yang merusak kompatibilitas lama (*Breaking Change*). | Setiap pergantian tahun kalender (`YYYY`). |
| **Kapan Angka Kedua Naik?** | Saat ada **fitur baru** yang ditambahkan tanpa merusak fitur lama. | Setiap pergantian bulan (`MM`). |
| **Kapan Angka Ketiga Naik?** | Saat ada **perbaikan bug (*bug fix*)** atau tambalan keamanan. | Setiap rilis perbaikan di bulan yang sama. |

### Keputusan Projek Bank Mini: SemVer + Tanggal Rilis (`MAJOR.MINOR.PATCH - YYYY-MM-DD`)
Projek **Bank Mini** menggunakan **Semantic Versioning (`MAJOR.MINOR.PATCH`)** di file [`VERSION`](../../VERSION) yang dipadukan dengan pencatatan tanggal kalender (`YYYY-MM-DD`) pada setiap rilis di [`CHANGELOG.md`](../../CHANGELOG.md). Dengan pendekatan ini, siswa RPL dapat memahami baik makna perubahan fitur (`SemVer`) maupun kronologi waktu rilis (`CalVer`).

---

## 2. Single Source of Truth (SoT) Versi Aplikasi

1. **File [`VERSION`](../../VERSION)**:
   - Menyimpan satu baris teks berisi nomor versi aktif (contoh: `1.1.0`).
   - File [`konfigurasi/aplikasi.php`](../../konfigurasi/aplikasi.php) membaca langsung isi file `VERSION` ini sehingga tidak ada penulisan nomor versi ganda (*hardcode*).
2. **File [`CHANGELOG.md`](../../CHANGELOG.md)**:
   - Mencatat setiap perubahan versi secara kronologis dari versi terbaru di bagian paling atas.
   - Menggunakan 4 kategori standar *Keep a Changelog*:
     - `### Ditambahkan (Added)` — untuk fitur atau dokumen baru.
     - `### Diperbarui (Changed)` — untuk perubahan pada fitur yang sudah ada.
     - `### Diperbaiki (Fixed)` — untuk perbaikan bug/kutu.
     - `### Keamanan (Security)` — untuk peningkatan celah keamanan.

---

## 3. Cara Menaikkan Versi (*Bump Version*) Secara Otomatis

Setiap kali fitur baru selesai dikerjakan dan lulus pengujian QA, pengembang atau AI Agent dapat menaikkan versi menggunakan skrip otomatis [`naikkan_versi.php`](../06-aturan-agen/skrip/naikkan_versi.php):

```powershell
# 1. Kenaikan PATCH (1.1.0 -> 1.1.1) untuk perbaikan bug / dokumentasi kecil
C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\naikkan_versi.php patch "Perbaikan validasi input nomor telepon nasabah"

# 2. Kenaikan MINOR (1.1.0 -> 1.2.0) untuk penambahan fitur/modul baru
C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\naikkan_versi.php minor "Penambahan fitur cetak slip bukti transaksi"

# 3. Kenaikan MAJOR (1.1.0 -> 2.0.0) untuk perombakan arsitektur/skema besar
C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\naikkan_versi.php major "Perombakan skema multi-cabang"
```

---

## 4. Lisensi Perangkat Lunak (`LICENSE`)

Projek ini dilengkapi file [`LICENSE`](../../LICENSE) di direktori utama menggunakan **Lisensi MIT (MIT License)**:
- Merupakan lisensi perangkat lunak bebas (*permissive open-source license*) yang paling banyak digunakan di dunia pendidikan dan industri.
- Mengizinkan siswa RPL dan pengembang untuk mempelajari, memodifikasi, dan mengembangkan ulang kode selama tetap mencantumkan atribusi hak cipta asli.

