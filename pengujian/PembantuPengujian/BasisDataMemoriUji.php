<?php

declare(strict_types=1);

namespace Pengujian\PembantuPengujian;

use PDO;

/**
 * PdoTiruanTransaksi
 *
 * Turunan PDO ringan di memori untuk mensimulasikan siklus transaksi ACID
 * (beginTransaction, commit, rollBack, inTransaction) tanpa memerlukan
 * server MySQL eksternal saat menjalankan suite pengujian otomatis CLI.
 */
class PdoTiruanTransaksi extends PDO
{
    private bool $sedangTransaksi = false;
    private int $jumlahCommit = 0;
    private int $jumlahRollback = 0;

    /** @var callable|null Callback untuk merestorasi state saat rollBack dipanggil */
    private $fungsiSaatRollback = null;

    /**
     * Konstruktor PdoTiruanTransaksi (tanpa memanggil konstruktor induk PDO).
     */
    public function __construct()
    {
    }

    /**
     * Mendaftarkan callback yang akan dipanggil otomatis saat rollBack() dieksekusi.
     *
     * @param callable $callback Fungsi pemulihan state
     * @return void
     */
    public function aturCallbackRollback(callable $callback): void
    {
        $this->fungsiSaatRollback = $callback;
    }

    /**
     * Memulai transaksi database simulasi.
     *
     * @return bool True jika transaksi berhasil dimulai
     */
    public function beginTransaction(): bool
    {
        $this->sedangTransaksi = true;
        return true;
    }

    /**
     * Menyimpan perubahan transaksi simulasi.
     *
     * @return bool True jika commit berhasil
     */
    public function commit(): bool
    {
        $this->sedangTransaksi = false;
        $this->jumlahCommit++;
        return true;
    }

    /**
     * Membatalkan transaksi simulasi dan memulihkan state sebelum transaksi.
     *
     * @return bool True jika rollback berhasil
     */
    public function rollBack(): bool
    {
        $this->sedangTransaksi = false;
        $this->jumlahRollback++;
        if (is_callable($this->fungsiSaatRollback)) {
            ($this->fungsiSaatRollback)();
        }
        return true;
    }

    /**
     * Memeriksa apakah transaksi sedang aktif.
     *
     * @return bool True jika sedang berada di dalam transaksi
     */
    public function inTransaction(): bool
    {
        return $this->sedangTransaksi;
    }

    /**
     * Mengambil jumlah pemanggilan commit().
     *
     * @return int Total commit
     */
    public function ambilJumlahCommit(): int
    {
        return $this->jumlahCommit;
    }

    /**
     * Mengambil jumlah pemanggilan rollBack().
     *
     * @return int Total rollback
     */
    public function ambilJumlahRollback(): int
    {
        return $this->jumlahRollback;
    }
}
