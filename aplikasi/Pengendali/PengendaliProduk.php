<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\MiddlewareAutentikasi;
use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Model\ModelProduk;

/**
 * PengendaliProduk
 *
 * Menampilkan daftar master produk tabungan dalam satuan Rupiah (IDR).
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

