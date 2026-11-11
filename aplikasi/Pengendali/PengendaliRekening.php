<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\MiddlewareAutentikasi;
use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Inti\Sesi;
use Aplikasi\Layanan\LayananRekening;
use Aplikasi\Model\ModelLogAudit;
use Aplikasi\Model\ModelMutasi;
use Aplikasi\Model\ModelNasabah;
use Aplikasi\Model\ModelProduk;
use Aplikasi\Model\ModelRekening;
use Throwable;

/**
 * PengendaliRekening
 *
 * @konsep_rpl Delegasi Logika Bisnis dari Pengendali ke Layanan (Service Layer)
 * Pengendali ini tidak membuka transaksi database secara langsung, melainkan
 * menyerahkan proses pembukaan rekening & setoran awal ke `LayananRekening`.
 */
class PengendaliRekening extends PengendaliDasar
{
    /**
     * Menampilkan daftar rekening dan formulir pembukaan rekening baru (GET /rekening).
     *
     * @return void
     */
    public function indeks(): void
    {
        MiddlewareAutentikasi::wajibMasuk();

        $modelRekening = new ModelRekening();
        $modelNasabah = new ModelNasabah();
        $modelProduk = new ModelProduk();

        $this->tampilkanTampilan('rekening/indeks', [
            'judulHalaman'   => 'Data Rekening Tabungan Nasabah',
            'daftarRekening' => $modelRekening->ambilSemuaRekeningLengkap(),
            'daftarNasabah'  => $modelNasabah->ambilSemuaNasabah(),
            'daftarProduk'   => $modelProduk->ambilSemuaProduk(),
        ]);
    }

    /**
     * Memproses pembukaan rekening baru beserta setoran awal Rupiah (POST /rekening/buka).
     *
     * @return void
     */
    public function bukaRekening(): void
    {
        MiddlewareAutentikasi::wajibPeran(['admin', 'teller']);

        try {
            $this->wajibValidasiCsrf();

            $layananRekening = new LayananRekening(
                new ModelNasabah(),
                new ModelProduk(),
                new ModelRekening(),
                new ModelMutasi(),
                new ModelLogAudit()
            );

            $idNasabah = (int) ($_POST['id_nasabah'] ?? 0);
            $idProduk = (int) ($_POST['id_produk'] ?? 0);
            $nominalSetoranAwal = (string) ($_POST['nominal_setoran_awal'] ?? '0');
            $idPetugas = (int) Sesi::ambil('id_pengguna', 0);
            $alamatIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            $hasil = $layananRekening->bukaRekeningBaru(
                $idNasabah,
                $idProduk,
                $nominalSetoranAwal,
                $idPetugas,
                $alamatIp
            );

            Sesi::tetapkanPesanKilat(
                'sukses',
                'Rekening baru ' . $hasil['nomor_rekening'] . ' berhasil dibuka dengan saldo awal '
                . formatRupiah((string) $hasil['saldo_awal'])
            );
        } catch (Throwable $pengecualian) {
            Sesi::tetapkanPesanKilat('kesalahan', 'Gagal membuka rekening: ' . $pengecualian->getMessage());
        }

        $this->alihkan('rekening');
    }
}

