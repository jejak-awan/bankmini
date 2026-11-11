<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

use PDO;

/**
 * BasisData
 *
 * Single Source of Truth (SoT) untuk koneksi PDO MySQL InnoDB.
 * Menggunakan pola Singleton agar seluruh proses transaksi ACID (beginTransaction,
 * commit, rollBack) berjalan pada satu instance koneksi yang sama.
 */
final class BasisData
{
    private static ?PDO $instansiKoneksi = null;

    /**
     * Konstruktor privat untuk mencegah pembuatan instance di luar Singleton.
     */
    private function __construct()
    {
    }

    /**
     * Mengambil instance tunggal koneksi PDO MySQL dengan EMULATE_PREPARES = false.
     *
     * @return PDO Instance koneksi PDO aktif
     */
    public static function ambilKoneksi(): PDO
    {
        if (self::$instansiKoneksi === null) {
            $konfigurasi = require dirname(__DIR__, 2)
                . DIRECTORY_SEPARATOR . 'konfigurasi'
                . DIRECTORY_SEPARATOR . 'basis_data.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $konfigurasi['host'],
                (int) $konfigurasi['port'],
                $konfigurasi['nama_db'],
                $konfigurasi['set_karakter']
            );

            $opsiPdo = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            self::$instansiKoneksi = new PDO(
                $dsn,
                (string) $konfigurasi['nama_pengguna'],
                (string) $konfigurasi['kata_sandi'],
                $opsiPdo
            );
        }

        return self::$instansiKoneksi;
    }

    /**
     * Mengatur instance koneksi PDO khusus untuk lingkungan pengujian otomatis QA.
     *
     * @param PDO|null $koneksiUji Instance PDO pengujian
     * @return void
     */
    public static function aturKoneksiPengujian(?PDO $koneksiUji): void
    {
        self::$instansiKoneksi = $koneksiUji;
    }
}

