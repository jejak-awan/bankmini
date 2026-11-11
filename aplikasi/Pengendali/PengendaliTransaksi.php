<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\MiddlewareAutentikasi;
use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Inti\Sesi;
use Aplikasi\Layanan\LayananTransaksi;
use Aplikasi\Model\ModelLogAudit;
use Aplikasi\Model\ModelMutasi;
use Aplikasi\Model\ModelRekening;
use Throwable;

/**
 * PengendaliTransaksi
 *
 * @konsep_rpl Orkestrasi HTTP Transaksi Keuangan & Otorisasi Berjenjang (RBAC)
 * Mengatur hak akses berdasarkan jenis transaksi:
 * - Setor, Tarik, dan Transfer dapat diproses oleh `teller` maupun `admin`.
 * - Jurnal Koreksi / Reversal (`/transaksi/koreksi`) hanya dapat diakses oleh
 *   `supervisor` dan `admin` sebagai bentuk pengendalian internal (Internal Control).
 */
class PengendaliTransaksi extends PengendaliDasar
{
    private LayananTransaksi $layananTransaksi;
    private ModelRekening $modelRekening;
    private ModelMutasi $modelMutasi;

    /**
     * Konstruktor PengendaliTransaksi.
     */
    public function __construct()
    {
        $this->modelRekening = new ModelRekening();
        $this->modelMutasi = new ModelMutasi();
        $this->layananTransaksi = new LayananTransaksi(
            $this->modelRekening,
            $this->modelMutasi,
            new ModelLogAudit()
        );
    }

    /**
     * Menampilkan formulir Setor Tunai (GET /transaksi/setor).
     *
     * @return void
     */
    public function tampilkanSetorTunai(): void
    {
        MiddlewareAutentikasi::wajibPeran(['admin', 'teller']);

        $this->tampilkanTampilan('transaksi/setor_tunai', [
            'judulHalaman'   => 'Transaksi Setor Tunai (Rupiah IDR)',
            'daftarRekening' => $this->modelRekening->ambilSemuaRekeningLengkap(),
        ]);
    }

    /**
     * Memproses pengiriman formulir Setor Tunai (POST /transaksi/setor) dengan PRG.
     *
     * @return void
     */
    public function prosesSetorTunai(): void
    {
        MiddlewareAutentikasi::wajibPeran(['admin', 'teller']);

        try {
            $this->wajibValidasiCsrf();

            $idRekening = (int) ($_POST['id_rekening'] ?? 0);
            $nominalSetoran = (string) ($_POST['nominal_setoran'] ?? '0');
            $keterangan = (string) ($_POST['keterangan'] ?? '');
            $idPetugas = (int) Sesi::ambil('id_pengguna', 0);
            $alamatIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            $hasil = $this->layananTransaksi->prosesSetorTunai(
                $idRekening,
                $nominalSetoran,
                $idPetugas,
                $keterangan,
                $alamatIp
            );

            Sesi::tetapkanPesanKilat(
                'sukses',
                'Setor tunai berhasil (' . $hasil['kode_referensi'] . '). Saldo akhir: '
                . formatRupiah((string) $hasil['saldo_sesudah'])
            );
        } catch (Throwable $pengecualian) {
            Sesi::tetapkanPesanKilat('kesalahan', $pengecualian->getMessage());
        }

        $this->alihkan('transaksi/setor');
    }

    /**
     * Menampilkan formulir Tarik Tunai (GET /transaksi/tarik).
     *
     * @return void
     */
    public function tampilkanTarikTunai(): void
    {
        MiddlewareAutentikasi::wajibPeran(['admin', 'teller']);

        $this->tampilkanTampilan('transaksi/tarik_tunai', [
            'judulHalaman'   => 'Transaksi Tarik Tunai (Rupiah IDR)',
            'daftarRekening' => $this->modelRekening->ambilSemuaRekeningLengkap(),
        ]);
    }

    /**
     * Memproses pengiriman formulir Tarik Tunai (POST /transaksi/tarik) dengan PRG.
     *
     * @return void
     */
    public function prosesTarikTunai(): void
    {
        MiddlewareAutentikasi::wajibPeran(['admin', 'teller']);

        try {
            $this->wajibValidasiCsrf();

            $idRekening = (int) ($_POST['id_rekening'] ?? 0);
            $nominalPenarikan = (string) ($_POST['nominal_penarikan'] ?? '0');
            $keterangan = (string) ($_POST['keterangan'] ?? '');
            $idPetugas = (int) Sesi::ambil('id_pengguna', 0);
            $alamatIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            $hasil = $this->layananTransaksi->prosesTarikTunai(
                $idRekening,
                $nominalPenarikan,
                $idPetugas,
                $keterangan,
                $alamatIp
            );

            Sesi::tetapkanPesanKilat(
                'sukses',
                'Tarik tunai berhasil (' . $hasil['kode_referensi'] . '). Saldo akhir: '
                . formatRupiah((string) $hasil['saldo_sesudah'])
            );
        } catch (Throwable $pengecualian) {
            Sesi::tetapkanPesanKilat('kesalahan', $pengecualian->getMessage());
        }

        $this->alihkan('transaksi/tarik');
    }

    /**
     * Menampilkan formulir Transfer Antar Rekening (GET /transaksi/transfer).
     *
     * @return void
     */
    public function tampilkanTransfer(): void
    {
        MiddlewareAutentikasi::wajibPeran(['admin', 'teller']);

        $this->tampilkanTampilan('transaksi/transfer', [
            'judulHalaman'   => 'Transfer Antar Rekening (Rupiah IDR)',
            'daftarRekening' => $this->modelRekening->ambilSemuaRekeningLengkap(),
        ]);
    }

    /**
     * Memproses pengiriman formulir Transfer Antar Rekening (POST /transaksi/transfer) dengan PRG.
     *
     * @return void
     */
    public function prosesTransfer(): void
    {
        MiddlewareAutentikasi::wajibPeran(['admin', 'teller']);

        try {
            $this->wajibValidasiCsrf();

            $idRekeningAsal = (int) ($_POST['id_rekening_asal'] ?? 0);
            $idRekeningTujuan = (int) ($_POST['id_rekening_tujuan'] ?? 0);
            $nominalTransfer = (string) ($_POST['nominal_transfer'] ?? '0');
            $keterangan = (string) ($_POST['keterangan'] ?? '');
            $idPetugas = (int) Sesi::ambil('id_pengguna', 0);
            $alamatIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            $hasil = $this->layananTransaksi->prosesTransferAntarRekening(
                $idRekeningAsal,
                $idRekeningTujuan,
                $nominalTransfer,
                $idPetugas,
                $keterangan,
                $alamatIp
            );

            Sesi::tetapkanPesanKilat(
                'sukses',
                'Transfer berhasil (' . $hasil['kode_ref_keluar'] . '). Saldo pengirim: '
                . formatRupiah((string) $hasil['saldo_asal_sesudah'])
            );
        } catch (Throwable $pengecualian) {
            Sesi::tetapkanPesanKilat('kesalahan', $pengecualian->getMessage());
        }

        $this->alihkan('transaksi/transfer');
    }

    /**
     * Menampilkan formulir Jurnal Koreksi / Reversal (GET /transaksi/koreksi).
     * Hanya diizinkan untuk peran admin dan supervisor.
     *
     * @return void
     */
    public function tampilkanKoreksi(): void
    {
        MiddlewareAutentikasi::wajibPeran(['admin', 'supervisor']);

        $this->tampilkanTampilan('transaksi/koreksi', [
            'judulHalaman' => 'Jurnal Koreksi / Reversal (Append-Only Ledger)',
            'daftarMutasi' => $this->modelMutasi->ambilMutasiTerbaru(50),
        ]);
    }

    /**
     * Memproses pengiriman formulir Jurnal Koreksi / Reversal (POST /transaksi/koreksi) dengan PRG.
     *
     * @return void
     */
    public function prosesKoreksi(): void
    {
        MiddlewareAutentikasi::wajibPeran(['admin', 'supervisor']);

        try {
            $this->wajibValidasiCsrf();

            $idMutasiAsal = (int) ($_POST['id_mutasi_asal'] ?? 0);
            $alasanKoreksi = (string) ($_POST['alasan_koreksi'] ?? '');
            $idPetugas = (int) Sesi::ambil('id_pengguna', 0);
            $alamatIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            $hasil = $this->layananTransaksi->prosesJurnalKoreksi(
                $idMutasiAsal,
                $idPetugas,
                $alasanKoreksi,
                $alamatIp
            );

            Sesi::tetapkanPesanKilat(
                'sukses',
                'Jurnal koreksi berhasil disimpan (' . $hasil['kode_referensi'] . '). Saldo akhir: '
                . formatRupiah((string) $hasil['saldo_sesudah'])
            );
        } catch (Throwable $pengecualian) {
            Sesi::tetapkanPesanKilat('kesalahan', $pengecualian->getMessage());
        }

        $this->alihkan('transaksi/koreksi');
    }
}
