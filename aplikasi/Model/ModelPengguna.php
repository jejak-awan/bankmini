<?php

declare(strict_types=1);

namespace Aplikasi\Model;

use Aplikasi\Inti\BasisData;
use PDO;

/**
 * ModelPengguna
 *
 * Lapisan akses data (Data Access Layer) murni untuk tabel `pengguna`.
 * Seluruh query menggunakan PDO Prepared Statements.
 */
class ModelPengguna
{
    private PDO $koneksi;

    /**
     * Konstruktor ModelPengguna.
     */
    public function __construct()
    {
        $this->koneksi = BasisData::ambilKoneksi();
    }

    /**
     * Mengambil satu baris pengguna berdasarkan nama_pengguna.
     *
     * @param string $namaPengguna Nama pengguna unik
     * @return array<string, mixed>|null Data pengguna atau null jika tidak ditemukan
     */
    public function ambilBerdasarkanNamaPengguna(string $namaPengguna): ?array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT id, nama_pengguna, hash_kata_sandi, nama_lengkap, peran, status, dibuat_pada
             FROM pengguna
             WHERE nama_pengguna = :nama_pengguna
             LIMIT 1'
        );
        $pernyataan->execute(['nama_pengguna' => $namaPengguna]);
        $barisData = $pernyataan->fetch(PDO::FETCH_ASSOC);

        return is_array($barisData) ? $barisData : null;
    }

    /**
     * Mengambil satu baris pengguna berdasarkan ID.
     *
     * @param int $idPengguna ID pengguna
     * @return array<string, mixed>|null Data pengguna atau null jika tidak ditemukan
     */
    public function ambilBerdasarkanId(int $idPengguna): ?array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT id, nama_pengguna, nama_lengkap, peran, status, dibuat_pada
             FROM pengguna
             WHERE id = :id_pengguna
             LIMIT 1'
        );
        $pernyataan->execute(['id_pengguna' => $idPengguna]);
        $barisData = $pernyataan->fetch(PDO::FETCH_ASSOC);

        return is_array($barisData) ? $barisData : null;
    }

    /**
     * Mengambil seluruh daftar pengguna internal.
     *
     * @return array<int, array<string, mixed>> Daftar pengguna
     */
    public function ambilSemuaPengguna(): array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT id, nama_pengguna, nama_lengkap, peran, status, dibuat_pada
             FROM pengguna
             ORDER BY id ASC'
        );
        $pernyataan->execute();

        return $pernyataan->fetchAll(PDO::FETCH_ASSOC);
    }
}

