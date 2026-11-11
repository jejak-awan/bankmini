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
 * @konsep_rpl Agregasi Data Dasbor & Pemisahan Presentasi (View)
 * Mengumpulkan statistik ringkasan (jumlah nasabah, jumlah rekening, total dana
 * tabungan Rupiah aktif, dan 10 mutasi terakhir) untuk dikirimkan ke `dasbor/indeks.php`
 * tanpa mencampur kode query di dalam file HTML.
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
        // [Langkah 1 - Konsep RPL]: Terapkan middleware autentikasi agar dasbor tidak dapat diakses tanpa login.
        MiddlewareAutentikasi::wajibMasuk();

        // [Langkah 2 - Konsep RPL]: Ambil data ringkasan nasabah, rekening, dan mutasi terbaru dari Model.
        $modelNasabah = new ModelNasabah();
        $modelRekening = new ModelRekening();
        $modelMutasi = new ModelMutasi();

        $daftarNasabah = $modelNasabah->ambilSemuaNasabah();
        $daftarRekening = $modelRekening->ambilSemuaRekeningLengkap();
        $daftarMutasiTerbaru = $modelMutasi->ambilMutasiTerbaru(10);

        $totalSaldoRupiah = '0.00';
        foreach ($daftarRekening as $barisRekening) {
            if ($barisRekening['status'] === 'aktif') {
                $totalSaldoRupiah = bcadd($totalSaldoRupiah, keStringNumerik($barisRekening['saldo']), 2);
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
