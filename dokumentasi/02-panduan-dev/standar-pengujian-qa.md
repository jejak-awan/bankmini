# Standar Pengujian & Quality Assurance (QA) (`bankmini`)

Setiap fitur dan layanan pada aplikasi **Bank Mini (PHP Native)** wajib dilengkapi dengan pengujian otomatis (*automated test*) dan skenario uji QA sebelum dinyatakan selesai (*Definition of Done*).

---

## 1. Struktur Folder Pengujian di `C:\xampp\htdocs\www\bankmini`

Seluruh file pengujian ditempatkan di dalam folder `pengujian/` menggunakan Bahasa Indonesia:

```text
C:\xampp\htdocs\www\bankmini\
└── pengujian/                         # Root folder pengujian QA
    ├── jalankan_semua_tes.php         # Test Runner CLI tunggal (PHP Native)
    ├── PembantuPengujian/             # Utilitas asersi & penyiapan database uji
    │   ├── KasusUjiDasar.php          # Class induk asersi (pastikanSama, pastikanBenar, pastikanGagal)
    │   └── BasisDataUji.php           # Pengelola transaksi rollback otomatis untuk isolasi tes
    ├── Unit/                          # Pengujian logika murni, helper, & validasi
    │   ├── TesFungsiUmum.php          # Menguji formatRupiah(), amankanHtml(), formatTanggalIndonesia()
    │   └── TesTokenCsrf.php           # Menguji pembuatan & validasi token CSRF
    ├── Integrasi/                     # Pengujian Layanan + Model + Database ACID
    │   ├── TesLayananAutentikasi.php  # Menguji masuk, keluar, & blokir akun nonaktif
    │   ├── TesLayananRekening.php     # Menguji pembukaan rekening & setoran awal Rupiah
    │   └── TesLayananTransaksi.php    # Menguji setor, tarik, transfer, koreksi, & penguncian FOR UPDATE
    └── Keamanan/                      # Pengujian ketahanan keamanan finansial
        └── TesKeamananAplikasi.php    # Menguji ketahanan SQLi, XSS, CSRF, & batas SoC
```

---

## 2. Konvensi Penamaan File & Method Tes (Bahasa Indonesia)

| Komponen | Konvensi | Contoh Benar | Contoh Salah |
| :--- | :--- | :--- | :--- |
| **Nama File Tes** | `Tes<NamaTarget>.php` (`PascalCase`) | `TesLayananTransaksi.php` | `TransaksiServiceTest.php`, `tes_transaksi.php` |
| **Nama Class Tes** | `Tes<NamaTarget>` (`PascalCase`) | `class TesLayananTransaksi` | `class TransactionTest` |
| **Nama Method Tes** | `uji<kondisiDanHasilYangDiharapkan>()` (`camelCase`) | `ujiTarikTunaiMelebihiSaldoEfektifDitolak()` | `testWithdrawFailed()`, `uji_tarik()` |
| **MethodAsersi** | `pastikan...()` (`camelCase`) | `$this->pastikanSama('150000.00', $hasil['saldo_sesudah'])` | `$this->assertEquals(...)` |

---

## 3. Aturan Rasio & Cakupan Wajib QA (Mandatory Test Coverage)

1. **Aturan Pasangan 1:1 untuk Lapisan `Layanan/`**:
   - Setiap class di `aplikasi/Layanan/Layanan<Modul>.php` **wajib** memiliki file tes pendamping di `pengujian/Integrasi/TesLayanan<Modul>.php` atau `pengujian/Unit/TesLayanan<Modul>.php`.
2. **4 Kategori Skenario Wajib pada Setiap Fitur Transaksi Rupiah**:
   - **Kasus Positif (*Happy Path*)**: Input valid, saldo bertambah/berkurang tepat sesuai nominal Rupiah (`DECIMAL(15,2)`), dan baris `mutasi_transaksi` serta `log_audit` tercatat.
   - **Kasus Batas & Negatif (*Boundary & Negative Case*)**: Nominal `<= 0`, nominal negatif, rekening berstatus `dibekukan`/`tutup`, atau penarikan yang melanggar `saldo_mengendap_minimum` wajib ditolak (`DomainException` / `InvalidArgumentException`).
   - **Kasus Integritas ACID (*Rollback Verification*)**: Jika terjadi kegagalan di tengah proses, saldo di tabel `rekening` dan jumlah baris di `mutasi_transaksi` dipastikan tidak berubah sama sekali.
   - **Kasus Keamanan (*Security Case*)**: Input yang mengandung payload XSS (`<script>`) atau karakter kutip SQL (`' OR 1=1 --`) dipastikan aman dan tidak merusak query maupun tampilan.

---

## 4. Contoh Standar Penulisan File Tes QA (Bahasa Indonesia)

```php
<?php

declare(strict_types=1);

namespace Pengujian\Integrasi;

use Aplikasi\Layanan\LayananTransaksi;
use DomainException;
use Pengujian\PembantuPengujian\KasusUjiDasar;

/**
 * TesLayananTransaksi
 *
 * Menguji integritas transaksi keuangan Rupiah (IDR), kepatuhan ACID,
 * dan validasi saldo mengendap minimum pada LayananTransaksi.
 */
class TesLayananTransaksi extends KasusUjiDasar
{
    /**
     * @skenario QA-TRX-001
     * Memastikan setoran tunai Rupiah menambah saldo rekening dan mencatat mutasi KREDIT.
     *
     * @return void
     */
    public function ujiSetorTunaiMenambahSaldoSecaraAkurat(): void
    {
        // 1. Persiapan data awal (Arrange)
        $idRekening = $this->buatRekeningUjiDenganSaldoAwal('100000.00');

        // 2. Eksekusi aksi (Act)
        $hasil = $this->layananTransaksi->prosesSetorTunai(
            idRekening: $idRekening,
            nominalSetoran: '50000.00',
            idPetugas: 1,
            keterangan: 'Setoran tabungan mingguan',
            alamatIp: '127.0.0.1'
        );

        // 3. Verifikasi hasil (Assert)
        $this->pastikanSama('100000.00', $hasil['saldo_sebelum'], 'Saldo sebelum harus Rp 100.000,00');
        $this->pastikanSama('150000.00', $hasil['saldo_sesudah'], 'Saldo sesudah harus Rp 150.000,00');
    }

    /**
     * @skenario QA-TRX-002
     * Memastikan penarikan yang menyisakan saldo di bawah saldo mengendap minimum ditolak
     * dan transaksi database di-rollback.
     *
     * @return void
     */
    public function ujiTarikTunaiMelebihiSaldoEfektifDitolakDanRollback(): void
    {
        // Saldo awal Rp 50.000,00 dengan saldo mengendap minimum Rp 20.000,00 (Saldo efektif = Rp 30.000,00)
        $idRekening = $this->buatRekeningUjiDenganSaldoAwal('50000.00', '20000.00');

        $this->pastikanMelemparPengecualian(
            DomainException::class,
            function () use ($idRekening): void {
                $this->layananTransaksi->prosesTarikTunai(
                    idRekening: $idRekening,
                    nominalPenarikan: '35000.00',
                    idPetugas: 1,
                    keterangan: 'Percobaan tarik melebihi saldo efektif',
                    alamatIp: '127.0.0.1'
                );
            }
        );

        // Pastikan saldo tetap utuh Rp 50.000,00 setelah rollback
        $saldoSaatIni = $this->ambilSaldoRekening($idRekening);
        $this->pastikanSama('50000.00', $saldoSaatIni, 'Saldo tidak boleh berubah setelah transaksi ditolak.');
    }
}
```

---

## 5. Quality Gate Tambahan: Lint PSR-12, PHPStan, & Playwright E2E

Selain unit/integrasi test PHP Native di atas, projek **Bank Mini** dilengkapi tiga penjaga kualitas modern:

| Lapisan | Alat / File Konfigurasi | Perintah Jalankan | Tujuan |
| :--- | :--- | :--- | :--- |
| **Linting PSR-12** | `squizlabs/php_codesniffer` (`phpcs.xml`) | `composer lint` (`composer lint:fix`) | Menjaga kerapian penulisan PHP sesuai standar PSR-12 |
| **Analisis Statis** | `phpstan/phpstan` Level 5 (`phpstan.neon`) | `composer stan` | Mendeteksi *type mismatch*, variabel tidak terdefinisi, dan bug logika sebelum runtime |
| **Smoke & E2E Test** | `@playwright/test` (`playwright.config.js`) | `npm run test:e2e` / `npm run test:smoke` | Menguji browser Chrome nyata: status HTTP aset CSS/JS offline, kontras sidebar, tidak ada overflow, pratinjau Rupiah, dan modal konfirmasi transaksi |


