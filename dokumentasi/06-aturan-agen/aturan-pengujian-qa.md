# Aturan Pembuatan File Tes QA & Verifikasi Konsistensi (Wajib untuk AI Agent)

Setiap AI Agent yang membuat atau memodifikasi fitur di `C:\xampp\htdocs\www\bankmini` **wajib** menyertakan pengujian otomatis untuk Quality Assurance (QA) dan menjalankan skrip penjaga konsistensi sebelum menyelesaikan tugas.

---

## 1. Aturan Wajib Pembuatan File Tes QA (`pengujian/`)

1. **Pasangan 1:1 untuk Lapisan `Layanan/`**:
   - Setiap kali agen membuat class `C:\xampp\htdocs\www\bankmini\aplikasi\Layanan\Layanan<Nama>.php`, agen **wajib** membuat file tes QA di `C:\xampp\htdocs\www\bankmini\pengujian\Integrasi\TesLayanan<Nama>.php` (atau di `pengujian/Unit/TesLayanan<Nama>.php`).
2. **Standar Penamaan & Struktur File Tes (Bahasa Indonesia)**:
   - Nama file dan class tes wajib diawali `Tes` dalam `PascalCase` (contoh: `TesLayananTransaksi.php`).
   - File tes wajib mengaktifkan `declare(strict_types=1);` di baris awal.
   - Setiap method pengujian wajib `public function uji<DeskripsiSkenario>(): void` dalam `camelCase`.
   - Setiap method `uji...()` wajib memiliki blok anotasi PHPDoc berbahasa Indonesia yang memuat tag `@skenario QA-<MODUL>-<NOMOR>` (contoh: `@skenario QA-TRX-001`).
3. **Minimal 4 Skenario Uji untuk Fitur Transaksi Keuangan (Rupiah IDR)**:
   - **Uji Positif**: Memastikan saldo bertambah/berkurang akurat dalam `DECIMAL(15,2)` Rupiah dan tercatat di `mutasi_transaksi`.
   - **Uji Batas Saldo / Negatif**: Memastikan penarikan yang melanggar `saldo_mengendap_minimum` atau nominal `<= 0` ditolak (`DomainException` / `InvalidArgumentException`).
   - **Uji Rollback ACID**: Memastikan saat transaksi gagal, saldo rekening tetap utuh seperti sebelum transaksi.
   - **Uji Keamanan**: Memastikan input berbahaya (XSS/SQLi/CSRF) ditangani sesuai standar.

---

## 2. Kewajiban Menjalankan Skrip Penjaga Konsistensi

Sebelum melaporkan pekerjaan selesai kepada pengguna, AI Agent **wajib** menjalankan perintah berikut dari terminal:

```powershell
php C:\xampp\htdocs\www\dokumentasi\06-aturan-agen\skrip\periksa_kepatuhan_agen.php
```
atau melalui skrip PowerShell:
```powershell
powershell -ExecutionPolicy Bypass -File C:\xampp\htdocs\www\dokumentasi\06-aturan-agen\skrip\periksa-konsistensi.ps1
```

### Kriteria Lulus (Quality Gate)
- Skrip harus menghasilkan **0 Pelanggaran (Exit Code 0)**.
- Apabila skrip melaporkan pelanggaran (misal: ada variabel `$snake_case`, fungsi belum diberi anotasi PHPDoc Bahasa Indonesia, query SQL bocor ke `Tampilan/` atau `Pengendali/`, atau `Layanan` belum punya file `TesLayanan*.php`), agen **wajib memperbaikinya terlebih dahulu** dan menjalankan ulang skrip hingga lulus 100%.

