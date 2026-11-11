<?php

declare(strict_types=1);

namespace Aplikasi\Model;

use Aplikasi\Inti\BasisData;
use PDO;

/**
 * ModelProduk
 *
 * @konsep_rpl Parameter Bisnis Terpusat & Tipe DECIMAL(15,2) Rupiah (IDR)
 * Menyimpan aturan produk simpanan seperti `setoran_awal_minimum` dan
 * `saldo_mengendap_minimum` dalam satuan Rupiah (`DECIMAL(15,2)`) agar aturan
 * tersebut tidak ditulis statis (hardcode) di dalam kode PHP.
 */
class ModelProduk
{
    private PDO $koneksi;

    /**
     * Konstruktor ModelProduk.
     */
    public function __construct()
    {
        $this->koneksi = BasisData::ambilKoneksi();
    }

    /**
     * Mengambil seluruh daftar produk tabungan.
     *
     * @return array<int, array<string, mixed>> Daftar produk tabungan
     */
    public function ambilSemuaProduk(): array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT id, kode_produk, nama_produk, setoran_awal_minimum,
                    saldo_mengendap_minimum, biaya_admin_bulanan, status, dibuat_pada
             FROM produk_tabungan
             ORDER BY kode_produk ASC'
        );
        $pernyataan->execute();

        return $pernyataan->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil satu produk tabungan berdasarkan ID.
     *
     * @param int $idProduk ID produk tabungan
     * @return array<string, mixed>|null Data produk atau null jika tidak ditemukan
     */
    public function ambilBerdasarkanId(int $idProduk): ?array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT id, kode_produk, nama_produk, setoran_awal_minimum,
                    saldo_mengendap_minimum, biaya_admin_bulanan, status
             FROM produk_tabungan
             WHERE id = :id_produk
             LIMIT 1'
        );
        $pernyataan->execute(['id_produk' => $idProduk]);
        $barisData = $pernyataan->fetch(PDO::FETCH_ASSOC);

        return is_array($barisData) ? $barisData : null;
    }
}

