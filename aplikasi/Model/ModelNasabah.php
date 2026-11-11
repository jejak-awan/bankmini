<?php

declare(strict_types=1);

namespace Aplikasi\Model;

use Aplikasi\Inti\BasisData;
use PDO;

/**
 * ModelNasabah
 *
 * Lapisan akses data (Data Access Layer) murni untuk tabel `nasabah`.
 * Seluruh query menggunakan PDO Prepared Statements.
 */
class ModelNasabah
{
    private PDO $koneksi;

    /**
     * Konstruktor ModelNasabah.
     */
    public function __construct()
    {
        $this->koneksi = BasisData::ambilKoneksi();
    }

    /**
     * Mengambil seluruh daftar nasabah yang terdaftar.
     *
     * @return array<int, array<string, mixed>> Daftar nasabah
     */
    public function ambilSemuaNasabah(): array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT id, nomor_identitas, nama_lengkap, jenis_nasabah, kelas_atau_unit,
                    nomor_telepon, alamat, status, dibuat_pada
             FROM nasabah
             ORDER BY nama_lengkap ASC'
        );
        $pernyataan->execute();

        return $pernyataan->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil data nasabah berdasarkan ID.
     *
     * @param int $idNasabah ID nasabah
     * @return array<string, mixed>|null Data nasabah atau null jika tidak ditemukan
     */
    public function ambilBerdasarkanId(int $idNasabah): ?array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT id, nomor_identitas, nama_lengkap, jenis_nasabah, kelas_atau_unit,
                    nomor_telepon, alamat, status, dibuat_pada
             FROM nasabah
             WHERE id = :id_nasabah
             LIMIT 1'
        );
        $pernyataan->execute(['id_nasabah' => $idNasabah]);
        $barisData = $pernyataan->fetch(PDO::FETCH_ASSOC);

        return is_array($barisData) ? $barisData : null;
    }

    /**
     * Menyimpan data nasabah baru ke dalam tabel `nasabah`.
     *
     * @param array<string, mixed> $dataNasabah Data nasabah baru
     * @return int ID nasabah yang baru disimpan
     */
    public function tambahNasabahBaru(array $dataNasabah): int
    {
        $pernyataan = $this->koneksi->prepare(
            'INSERT INTO nasabah
                (nomor_identitas, nama_lengkap, jenis_nasabah, kelas_atau_unit, nomor_telepon, alamat, status)
             VALUES
                (:nomor_identitas, :nama_lengkap, :jenis_nasabah, :kelas_atau_unit, :nomor_telepon, :alamat, :status)'
        );
        $pernyataan->execute([
            'nomor_identitas' => (string) $dataNasabah['nomor_identitas'],
            'nama_lengkap'    => (string) $dataNasabah['nama_lengkap'],
            'jenis_nasabah'   => (string) $dataNasabah['jenis_nasabah'],
            'kelas_atau_unit' => (string) $dataNasabah['kelas_atau_unit'],
            'nomor_telepon'   => $dataNasabah['nomor_telepon'] ?? null,
            'alamat'          => $dataNasabah['alamat'] ?? null,
            'status'          => (string) ($dataNasabah['status'] ?? 'aktif'),
        ]);

        return (int) $this->koneksi->lastInsertId();
    }
}

