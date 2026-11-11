<?php

declare(strict_types=1);

namespace Aplikasi\Model;

use Aplikasi\Inti\BasisData;
use PDO;

/**
 * ModelRekening
 *
 * @konsep_rpl Penguncian Baris Pesimistis (Pessimistic Row Locking: SELECT ... FOR UPDATE)
 * Method `kunciRekeningUntukPembaruan()` menggunakan klausa `FOR UPDATE` pada MySQL InnoDB.
 * Ketika dijalankan di dalam transaksi (`beginTransaction()`), baris rekening ini dikunci
 * sehingga request lain yang mencoba mengubah saldo rekening yang sama harus menunggu
 * hingga transaksi selesai (`commit()` / `rollBack()`), mencegah bug Double Spending!
 */
class ModelRekening
{
    private PDO $koneksi;

    /**
     * Konstruktor ModelRekening.
     */
    public function __construct()
    {
        $this->koneksi = BasisData::ambilKoneksi();
    }

    /**
     * Mengambil seluruh daftar rekening beserta nama nasabah dan nama produk.
     *
     * @return array<int, array<string, mixed>> Daftar rekening lengkap
     */
    public function ambilSemuaRekeningLengkap(): array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT r.id, r.nomor_rekening, r.id_nasabah, r.id_produk, r.saldo, r.status,
                    r.tanggal_buka, n.nama_lengkap AS nama_nasabah, n.nomor_identitas,
                    p.kode_produk, p.nama_produk, p.saldo_mengendap_minimum
             FROM rekening r
             JOIN nasabah n ON n.id = r.id_nasabah
             JOIN produk_tabungan p ON p.id = r.id_produk
             ORDER BY r.id DESC'
        );
        $pernyataan->execute();

        return $pernyataan->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil data rekening berdasarkan nomor_rekening (tanpa kunci baris).
     *
     * @param string $nomorRekening Nomor rekening unik
     * @return array<string, mixed>|null Data rekening atau null jika tidak ditemukan
     */
    public function ambilBerdasarkanNomorRekening(string $nomorRekening): ?array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT r.id, r.nomor_rekening, r.id_nasabah, r.id_produk, r.saldo, r.status,
                    n.nama_lengkap AS nama_nasabah, p.nama_produk, p.saldo_mengendap_minimum
             FROM rekening r
             JOIN nasabah n ON n.id = r.id_nasabah
             JOIN produk_tabungan p ON p.id = r.id_produk
             WHERE r.nomor_rekening = :nomor_rekening
             LIMIT 1'
        );
        $pernyataan->execute(['nomor_rekening' => $nomorRekening]);
        $barisData = $pernyataan->fetch(PDO::FETCH_ASSOC);

        return is_array($barisData) ? $barisData : null;
    }

    /**
     * Mengunci baris rekening dengan SELECT ... FOR UPDATE di dalam transaksi database aktif.
     *
     * @param int $idRekening ID rekening yang akan dikunci dan diperbarui saldonya
     * @return array<string, mixed>|null Data rekening beserta saldo_mengendap_minimum
     */
    public function kunciRekeningUntukPembaruan(int $idRekening): ?array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT r.id, r.nomor_rekening, r.id_nasabah, r.id_produk, r.saldo, r.status,
                    p.saldo_mengendap_minimum
             FROM rekening r
             JOIN produk_tabungan p ON p.id = r.id_produk
             WHERE r.id = :id_rekening
             FOR UPDATE'
        );
        $pernyataan->execute(['id_rekening' => $idRekening]);
        $barisData = $pernyataan->fetch(PDO::FETCH_ASSOC);

        return is_array($barisData) ? $barisData : null;
    }

    /**
     * Menyimpan baris rekening baru ke dalam tabel `rekening`.
     *
     * @param array<string, mixed> $dataRekening Data pembukaan rekening baru
     * @return int ID rekening baru
     */
    public function simpanRekeningBaru(array $dataRekening): int
    {
        $pernyataan = $this->koneksi->prepare(
            'INSERT INTO rekening (nomor_rekening, id_nasabah, id_produk, saldo, status)
             VALUES (:nomor_rekening, :id_nasabah, :id_produk, :saldo, :status)'
        );
        $pernyataan->execute([
            'nomor_rekening' => (string) $dataRekening['nomor_rekening'],
            'id_nasabah'     => (int) $dataRekening['id_nasabah'],
            'id_produk'      => (int) $dataRekening['id_produk'],
            'saldo'          => (string) $dataRekening['saldo'],
            'status'         => (string) ($dataRekening['status'] ?? 'aktif'),
        ]);

        return (int) $this->koneksi->lastInsertId();
    }

    /**
     * Memperbarui cached balance (`saldo`) pada tabel `rekening` (wajib di dalam transaksi ACID).
     *
     * @param int    $idRekening   ID rekening yang diperbarui
     * @param string $saldoSesudah Nilai saldo baru dalam Rupiah DECIMAL(15,2)
     * @return void
     */
    public function perbaruiSaldoRekening(int $idRekening, string $saldoSesudah): void
    {
        $pernyataan = $this->koneksi->prepare(
            'UPDATE rekening
             SET saldo = :saldo_sesudah
             WHERE id = :id_rekening'
        );
        $pernyataan->execute([
            'saldo_sesudah' => $saldoSesudah,
            'id_rekening'   => $idRekening,
        ]);
    }
}
