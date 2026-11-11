<?php

declare(strict_types=1);

namespace Aplikasi\Model;

use Aplikasi\Inti\BasisData;
use PDO;

/**
 * ModelLogAudit
 *
 * Lapisan akses data (Data Access Layer) untuk mencatat jejak audit aktivitas
 * sensitif ke dalam tabel `log_audit`.
 */
class ModelLogAudit
{
    private PDO $koneksi;

    /**
     * Konstruktor ModelLogAudit.
     */
    public function __construct()
    {
        $this->koneksi = BasisData::ambilKoneksi();
    }

    /**
     * Mencatat satu entri jejak audit baru ke tabel `log_audit`.
     *
     * @param array<string, mixed> $dataAudit Rincian aktivitas yang diaudit
     * @return int ID baris log audit yang disimpan
     */
    public function catatLog(array $dataAudit): int
    {
        $pernyataan = $this->koneksi->prepare(
            'INSERT INTO log_audit
                (id_pengguna, aksi, entitas, id_entitas, data_lama, data_baru, alamat_ip)
             VALUES
                (:id_pengguna, :aksi, :entitas, :id_entitas, :data_lama, :data_baru, :alamat_ip)'
        );

        $jsonLama = isset($dataAudit['data_lama'])
            ? json_encode($dataAudit['data_lama'], JSON_UNESCAPED_UNICODE)
            : null;
        $jsonBaru = isset($dataAudit['data_baru'])
            ? json_encode($dataAudit['data_baru'], JSON_UNESCAPED_UNICODE)
            : null;

        $pernyataan->execute([
            'id_pengguna' => isset($dataAudit['id_pengguna']) ? (int) $dataAudit['id_pengguna'] : null,
            'aksi'        => (string) $dataAudit['aksi'],
            'entitas'     => (string) $dataAudit['entitas'],
            'id_entitas'  => (string) $dataAudit['id_entitas'],
            'data_lama'   => $jsonLama,
            'data_baru'   => $jsonBaru,
            'alamat_ip'   => (string) ($dataAudit['alamat_ip'] ?? '127.0.0.1'),
        ]);

        return (int) $this->koneksi->lastInsertId();
    }
}

