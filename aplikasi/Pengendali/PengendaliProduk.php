<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\MiddlewareAutentikasi;
use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Model\ModelProduk;

/**
 * PengendaliProduk
 *
 * @konsep_rpl Penyajian Parameter Produk Tabungan Rupiah (IDR)
 * Menampilkan daftar master produk simpanan beserta batas setoran awal minimum
 * dan saldo mengendap minimum.
 */
class PengendaliProduk extends PengendaliDasar
{
    /**
     * Menampilkan daftar produk simpanan (GET /produk).
     *
     * @return void
     */
    public function indeks(): void
    {
        MiddlewareAutentikasi::wajibMasuk();

        $modelProduk = new ModelProduk();
        $this->tampilkanTampilan('produk/indeks', [
            'judulHalaman' => 'Master Produk Tabungan (Rupiah IDR)',
            'daftarProduk' => $modelProduk->ambilSemuaProduk(),
        ]);
    }
}

