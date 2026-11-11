<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\MiddlewareAutentikasi;
use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Model\ModelMutasi;
use Aplikasi\Model\ModelNasabah;
use Aplikasi\Model\ModelRekening;

/**
 * PengendaliDasbor
 *
 * Menampilkan ringkasan statistik operasional Bank Mini (total nasabah,
 * total rekening, total dana tabungan Rupiah, dan mutasi terbaru).
 */
class PengendaliDasbor extends PengendaliDasar
{
    /**
     * Menampilkan halaman utama dasbor (GET / atau GET /dasbor).
     *
     * @return void
     */
    public function indeks(): void
    {
        MiddlewareAutentikasi::wajibMasuk();

        $modelNasabah = new ModelNasabah();
        $modelRekening = new ModelRekening();
        $modelMutasi = new ModelMutasi();

        $daftarNasabah = $modelNasabah->ambilSemuaNasabah();
        $daftarRekening = $modelRekening->ambilSemuaRekeningLengkap();
        $daftarMutasiTerbaru = $modelMutasi->ambilMutasiTerbaru(10);

        $totalSaldoRupiah = '0.00';
        foreach ($daftarRekening as $barisRekening) {
            if ($barisRekening['status'] === 'aktif') {
                $totalSaldoRupiah = bcadd($totalSaldoRupiah, (string) $barisRekening['saldo'], 2);
            }
        }

        $this->tampilkanTampilan('dasbor/indeks', [
            'judulHalaman'        => 'Dasbor Operasional Bank Mini',
            'jumlahNasabah'       => count($daftarNasabah),
            'jumlahRekening'      => count($daftarRekening),
            'totalSaldoRupiah'    => $totalSaldoRupiah,
            'daftarMutasiTerbaru' => $daftarMutasiTerbaru,
        ]);
    }
}

