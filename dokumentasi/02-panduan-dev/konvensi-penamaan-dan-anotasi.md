# Konvensi Penamaan, Struktur Bahasa Indonesia, & Standar Anotasi

Dokumen ini merupakan **Single Source of Truth (SoT)** untuk aturan penamaan file, folder, tabel database, class, fungsi/method, variabel, konstanta, serta format anotasi (PHPDoc & komentar kode) pada projek **Bank Mini**.

Seluruh penamaan domain aplikasi wajib menggunakan **Bahasa Indonesia** yang baku, deskriptif, dan konsisten mengikuti standar format **PSR-12**.

---

## 1. Ringkasan Format Penulisan (Casing Matrix)

| Elemen | Format Penulisan | Bahasa | Contoh Benar | Contoh Salah (Dilarang) |
| :--- | :--- | :--- | :--- | :--- |
| **Folder Infrastruktur / Modul** | `snake_case` (huruf kecil) | Indonesia | `konfigurasi/`, `basis_data/`, `tata_letak/` | `config/`, `database/`, `layouts/` |
| **Folder Namespace Class (`aplikasi/`)** | `PascalCase` | Indonesia | `aplikasi/Pengendali/`, `aplikasi/Layanan/` | `app/Controllers/`, `app/services/` |
| **File Class PHP** | `PascalCase.php` | Indonesia | `LayananTransaksi.php`, `ModelRekening.php` | `TransactionService.php`, `layanan_transaksi.php` |
| **File Tampilan (View) & Konfigurasi** | `snake_case.php` | Indonesia | `setor_tunai.php`, `basis_data.php` | `depositCash.php`, `SetorTunai.php` |
| **Nama Class** | `PascalCase` (Kata Benda) | Indonesia | `PengendaliTransaksi`, `LayananRekening` | `TransaksiController`, `rekening_service` |
| **Nama Method / Fungsi** | `camelCase` (Kata Kerja Aktif) | Indonesia | `prosesSetorTunai()`, `ambilSaldoEfektif()` | `processDeposit()`, `Proses_Setor()` |
| **Nama Variabel** | `$camelCase` (Deskriptif) | Indonesia | `$nominalSetoran`, `$saldoSesudah`, `$idRekening` | `$amount`, `$jml`, `$saldo_sesudah` |
| **Nama Konstanta** | `UPPER_SNAKE_CASE` | Indonesia | `MATA_UANG_RUPIAH`, `STATUS_AKTIF` | `DEFAULT_CURRENCY`, `statusAktif` |
| **Nama Tabel Database** | `snake_case` | Indonesia | `pengguna`, `produk_tabungan`, `mutasi_transaksi`, `log_audit` | `users`, `saving_products`, `audit_logs` |
| **Nama Kolom Database** | `snake_case` | Indonesia | `id_rekening`, `hash_kata_sandi`, `dibuat_pada` | `account_id`, `password_hash`, `created_at` |

> [!NOTE]
> **Pengecualian Teknis Standar Web Server**:
> Hanya `public/index.php`, `.htaccess`, serta sintaks bawaan PHP/SQL (`__construct`, `$_POST`, `$_SESSION`, `PDO`) yang tetap menggunakan kata kunci asli bawaan mesin.

---

## 2. Konvensi Penamaan Tabel & Kolom Database (`snake_case`)

1. **Gunakan istilah Bahasa Indonesia penuh**:
   - Gunakan `pengguna` (bukan `users`), `log_audit` (bukan `audit_logs`), `mutasi_transaksi` (bukan `transactions`).
2. **Primary Key & Foreign Key**:
   - Primary Key setiap tabel dinamai `id`.
   - Foreign Key menggunakan pola `id_<nama_entitas>` (contoh: `id_nasabah`, `id_produk`, `id_rekening`, `id_petugas`, `id_pengguna`).
3. **Kolom Waktu (Timestamps)**:
   - Gunakan `dibuat_pada` (pengganti `created_at`), `diperbarui_pada` (pengganti `updated_at`), `waktu_transaksi`, `tanggal_buka`, `tanggal_tutup`.

---

## 3. Konvensi Penamaan Variabel & Method (`camelCase`)

### 3.1 Variabel
- Hindari singkatan ambigu seperti `$rek`, `$nsb`, `$jml`, `$trx`.
- Gunakan nama utuh yang menjelaskan arti bisnisnya:
  - Nominal uang (Rupiah): `$nominalSetoran`, `$nominalPenarikan`, `$saldoSebelum`, `$saldoSesudah`, `$saldoMengendapMinimum`, `$saldoEfektif`.
  - Data entitas: `$dataRekening`, `$dataNasabah`, `$daftarMutasi`.
  - Boolean (gunakan awalan `apakah`, `sudah`, `boleh`, `valid`): `$apakahSaldoCukup`, `$sudahMasuk`, `$bolehTarikTunai`.

### 3.2 Method / Fungsi
- Gunakan awalan kata kerja Bahasa Indonesia yang konsisten:
  - `ambil...` (Query `SELECT` satu atau banyak data): `ambilBerdasarkanId()`, `ambilBerdasarkanNomorRekening()`, `ambilDaftarMutasi()`.
  - `kunci...` (Query `SELECT ... FOR UPDATE`): `kunciRekeningUntukPembaruan()`.
  - `simpan...` / `tambah...` (Query `INSERT`): `simpanMutasiBaru()`, `tambahNasabah()`.
  - `perbarui...` (Query `UPDATE`): `perbaruiSaldoRekening()`, `perbaruiStatusRekening()`.
  - `proses...` (Eksekusi transaksi bisnis di `Layanan`): `prosesSetorTunai()`, `prosesTarikTunai()`, `prosesTransferAntarRekening()`, `prosesJurnalKoreksi()`.
  - `validasi...` / `periksa...` (Pemeriksaan aturan): `validasiNominalRupiah()`, `periksaPeranPengguna()`.

---

## 4. Standar Anotasi & Komentar Edukasi Siswa RPL (`@konsep_rpl`)

Karena projek **Bank Mini** digunakan sebagai **bahan pembelajaran siswa Rekayasa Perangkat Lunak (RPL)**, setiap file class, properti, dan method **wajib** memiliki anotasi PHPDoc serta komentar blok kode dalam Bahasa Indonesia yang edukatif:
1. **Tag Wajib `@konsep_rpl` pada PHPDoc Class**:
   - Setiap deklarasi `class` di dalam folder `aplikasi/` **wajib** mencantumkan tag `@konsep_rpl` pada blok PHPDoc-nya untuk menjelaskan konsep rekayasa perangkat lunak yang diterapkan oleh class tersebut (misal: *Single Source of Truth*, *Singleton Pattern*, *Separation of Concerns*, *ACID Transaction & Row Locking*, *Immutable Ledger*, *PRG Pattern*).
2. **Penjelasan Mengapa (*Why*), Bukan Sekadar Apa (*What*)**:
   - Di dalam method penting, berikan komentar blok per langkah (`// [Langkah 1 - Konsep RPL]: ...`) yang menjelaskan **alasan teknis** di balik kode tersebut (contoh: mengapa memakai `SELECT ... FOR UPDATE`, mengapa memakai `bcsub`/`bcadd`, mengapa memakai `hash_equals`).
3. **Parameter (`@param`)**, **Nilai Kembalian (`@return`)**, dan **Pengecualian (`@throws`)** beserta satuan datanya (nominal dalam Rupiah `IDR` format string desimal `'100000.00'`).

### Contoh Lengkap Standar Penulisan Kode & Anotasi (Layanan Transaksi)

```php
<?php

declare(strict_types=1);

namespace Aplikasi\Layanan;

use Aplikasi\Inti\BasisData;
use Aplikasi\Model\ModelLogAudit;
use Aplikasi\Model\ModelMutasi;
use Aplikasi\Model\ModelRekening;
use DomainException;
use InvalidArgumentException;
use PDO;
use Throwable;

/**
 * LayananTransaksi
 *
 * Single Source of Truth (SoT) untuk seluruh logika bisnis mutasi keuangan Bank Mini.
 * Setiap perubahan saldo rekening WAJIB melalui class ini di dalam transaksi ACID
 * dengan satuan mata uang tunggal Rupiah (IDR).
 */
class LayananTransaksi
{
    private PDO $koneksi;
    private ModelRekening $modelRekening;
    private ModelMutasi $modelMutasi;
    private ModelLogAudit $modelLogAudit;

    /**
     * Konstruktor LayananTransaksi.
     *
     * @param ModelRekening $modelRekening Model akses data tabel rekening
     * @param ModelMutasi   $modelMutasi   Model akses data tabel mutasi_transaksi
     * @param ModelLogAudit $modelLogAudit Model pencatatan tabel log_audit
     */
    public function __construct(
        ModelRekening $modelRekening,
        ModelMutasi $modelMutasi,
        ModelLogAudit $modelLogAudit
    ) {
        $this->koneksi = BasisData::ambilKoneksi();
        $this->modelRekening = $modelRekening;
        $this->modelMutasi = $modelMutasi;
        $this->modelLogAudit = $modelLogAudit;
    }

    /**
     * Memproses transaksi penarikan tunai (DEBIT) dalam mata uang Rupiah (IDR).
     *
     * Prosedur ACID:
     * 1. Membuka transaksi database (beginTransaction).
     * 2. Mengunci baris rekening dengan SELECT ... FOR UPDATE.
     * 3. Memvalidasi status rekening dan kecukupan saldo efektif Rupiah.
     * 4. Mencatat baris baru di mutasi_transaksi (Append-Only Ledger).
     * 5. Memperbarui saldo di tabel rekening.
     * 6. Mencatat jejak audit di log_audit dan melakukan commit.
     *
     * @param int    $idRekening       ID rekening nasabah yang akan ditarik
     * @param string $nominalPenarikan Nominal penarikan dalam Rupiah (contoh: '50000.00')
     * @param int    $idPetugas        ID petugas/teller yang memproses transaksi
     * @param string $keterangan       Catatan tambahan transaksi
     * @param string $alamatIp         Alamat IP petugas untuk kebutuhan audit
     *
     * @return array<string, mixed> Data ringkasan bukti transaksi yang berhasil disimpan
     *
     * @throws InvalidArgumentException Jika format nominal Rupiah tidak valid atau <= 0
     * @throws DomainException          Jika rekening tidak aktif atau saldo efektif tidak mencukupi
     * @throws Throwable                Jika terjadi kegagalan sistem/database (otomatis rollBack)
     */
    public function prosesTarikTunai(
        int $idRekening,
        string $nominalPenarikan,
        int $idPetugas,
        string $keterangan,
        string $alamatIp
    ): array {
        // Validasi nominal Rupiah harus lebih besar dari Rp 0,00
        if (bccomp($nominalPenarikan, '0.00', 2) <= 0) {
            throw new InvalidArgumentException('Nominal penarikan Rupiah harus lebih besar dari nol.');
        }

        $this->koneksi->beginTransaction();

        try {
            // 1. Kunci baris rekening (SELECT ... FOR UPDATE) untuk mencegah race condition
            $dataRekening = $this->modelRekening->kunciRekeningUntukPembaruan($idRekening);

            if ($dataRekening === null) {
                throw new DomainException('Data rekening tidak ditemukan.');
            }

            if ($dataRekening['status'] !== 'aktif') {
                throw new DomainException('Transaksi ditolak: Status rekening sedang tidak aktif.');
            }

            // 2. Hitung saldo efektif Rupiah (Saldo Saat Ini - Saldo Mengendap Minimum)
            $saldoSebelum = (string) $dataRekening['saldo'];
            $saldoMengendapMinimum = (string) $dataRekening['saldo_mengendap_minimum'];
            $saldoEfektif = bcsub($saldoSebelum, $saldoMengendapMinimum, 2);

            if (bccomp($saldoEfektif, $nominalPenarikan, 2) < 0) {
                throw new DomainException('Saldo efektif Rupiah tidak mencukupi untuk penarikan ini.');
            }

            // 3. Hitung saldo sesudah penarikan secara presisi (DECIMAL 2 digit)
            $saldoSesudah = bcsub($saldoSebelum, $nominalPenarikan, 2);
            $kodeReferensi = $this->buatKodeReferensiTransaksi('TRK');

            // 4. Simpan ke mutasi_transaksi (Immutable / Append-Only Ledger)
            $idMutasi = $this->modelMutasi->simpanMutasiBaru([
                'kode_referensi'         => $kodeReferensi,
                'id_rekening'            => $idRekening,
                'jenis_transaksi'        => 'TARIK',
                'tipe_mutasi'            => 'DEBIT',
                'nominal'                => $nominalPenarikan,
                'saldo_sebelum'          => $saldoSebelum,
                'saldo_sesudah'          => $saldoSesudah,
                'keterangan'             => $keterangan,
                'id_referensi_transaksi' => null,
                'id_petugas'             => $idPetugas,
            ]);

            // 5. Perbarui cached balance pada tabel rekening
            $this->modelRekening->perbaruiSaldoRekening($idRekening, $saldoSesudah);

            // 6. Catat ke log_audit
            $this->modelLogAudit->catatLog([
                'id_pengguna' => $idPetugas,
                'aksi'        => 'TARIK_TUNAI',
                'entitas'     => 'mutasi_transaksi',
                'id_entitas'  => (string) $idMutasi,
                'data_lama'   => ['saldo' => $saldoSebelum],
                'data_baru'   => ['saldo' => $saldoSesudah, 'nominal_tarik' => $nominalPenarikan],
                'alamat_ip'   => $alamatIp,
            ]);

            $this->koneksi->commit();

            return [
                'id_mutasi'      => $idMutasi,
                'kode_referensi' => $kodeReferensi,
                'nominal'        => $nominalPenarikan,
                'saldo_sebelum'  => $saldoSebelum,
                'saldo_sesudah'  => $saldoSesudah,
            ];
        } catch (Throwable $pengecualian) {
            if ($this->koneksi->inTransaction()) {
                $this->koneksi->rollBack();
            }
            throw $pengecualian;
        }
    }

    /**
     * Membuat kode referensi transaksi unik (contoh: TRK-20261111-849201).
     *
     * @param string $awalan Kode awalan jenis transaksi (STR, TRK, TRF, KOR)
     * @return string Kode referensi unik
     */
    private function buatKodeReferensiTransaksi(string $awalan): string
    {
        return sprintf('%s-%s-%06d', $awalan, date('Ymd'), random_int(1, 999999));
    }
}
```

### 4.1 Standar Anotasi File Tampilan (`aplikasi/Tampilan/`) & JavaScript (`public/aset/js/`, `pengujian/e2e/`)
1. **Setiap file View `.php` di `aplikasi/Tampilan/`** wajib memiliki blok komentar deskripsi (`/** ... */`) di awal file yang menjelaskan tujuan antarmuka, komponen UX yang dipakai, dan keterkaitan datanya.
2. **Setiap file di `aplikasi/Layanan/` dan `aplikasi/Pengendali/`** wajib dilengkapi komentar penjelas blok kode per langkah (`// [Langkah X]: ...` atau `// ...`) di dalam method-methodnya.
3. **File JavaScript (`public/aset/js/aplikasi.js`) dan pengujian browser (`pengujian/e2e/*.spec.js`)** wajib menyertakan komentar JSDoc (`/** ... */`) atau komentar penjelas fungsi berbahasa Indonesia.
4. Seluruh ketentuan anotasi di atas diperiksa otomatis oleh skrip [`periksa_kepatuhan_agen.php`](../06-aturan-agen/skrip/periksa_kepatuhan_agen.php).

