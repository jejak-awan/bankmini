<?php

declare(strict_types=1);

namespace Aplikasi\Model;

use Aplikasi\Inti\BasisData;
use PDO;

/**
 * ModelMutasi
 *
 * @konsep_rpl Buku Besar Abadi (Append-Only / Immutable Ledger)
 * Dalam rekayasa sistem keuangan, tabel mutasi TIDAK BOLEH memiliki fungsi `DELETE`
 * atau `UPDATE nominal`. Setiap transaksi baru ditambahkan sebagai baris baru (`INSERT`)
 * yang menyimpan `saldo_sebelum`, `nominal`, dan `saldo_sesudah` secara berkesinambungan.
 */
class ModelMutasi
{
    private PDO $koneksi;

    /**
     * Konstruktor ModelMutasi.
     */
    public function __construct()
    {
        $this->koneksi = BasisData::ambilKoneksi();
    }

    /**
     * Menyimpan baris mutasi baru ke dalam buku besar `mutasi_transaksi`.
     *
     * @param array<string, mixed> $dataMutasi Data mutasi Rupiah (IDR)
     * @return int ID baris mutasi yang baru dibuat
     */
    public function simpanMutasiBaru(array $dataMutasi): int
    {
        $pernyataan = $this->koneksi->prepare(
            'INSERT INTO mutasi_transaksi
                (kode_referensi, id_rekening, jenis_transaksi, tipe_mutasi,
                 nominal, saldo_sebelum, saldo_sesudah, keterangan,
                 id_referensi_transaksi, id_petugas)
             VALUES
                (:kode_referensi, :id_rekening, :jenis_transaksi, :tipe_mutasi,
                 :nominal, :saldo_sebelum, :saldo_sesudah, :keterangan,
                 :id_referensi_transaksi, :id_petugas)'
        );
        $pernyataan->execute([
            'kode_referensi'         => (string) $dataMutasi['kode_referensi'],
            'id_rekening'            => (int) $dataMutasi['id_rekening'],
            'jenis_transaksi'        => (string) $dataMutasi['jenis_transaksi'],
            'tipe_mutasi'            => (string) $dataMutasi['tipe_mutasi'],
            'nominal'                => (string) $dataMutasi['nominal'],
            'saldo_sebelum'          => (string) $dataMutasi['saldo_sebelum'],
            'saldo_sesudah'          => (string) $dataMutasi['saldo_sesudah'],
            'keterangan'             => (string) $dataMutasi['keterangan'],
            'id_referensi_transaksi' => isset($dataMutasi['id_referensi_transaksi'])
                ? (int) $dataMutasi['id_referensi_transaksi']
                : null,
            'id_petugas'             => (int) $dataMutasi['id_petugas'],
        ]);

        return (int) $this->koneksi->lastInsertId();
    }

    /**
     * Mengambil satu baris mutasi berdasarkan ID.
     *
     * @param int $idMutasi ID mutasi transaksi
     * @return array<string, mixed>|null Data mutasi atau null jika tidak ditemukan
     */
    public function ambilBerdasarkanId(int $idMutasi): ?array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT id, kode_referensi, id_rekening, jenis_transaksi, tipe_mutasi,
                    nominal, saldo_sebelum, saldo_sesudah, keterangan,
                    id_referensi_transaksi, id_petugas, waktu_transaksi
             FROM mutasi_transaksi
             WHERE id = :id_mutasi
             LIMIT 1'
        );
        $pernyataan->execute(['id_mutasi' => $idMutasi]);
        $barisData = $pernyataan->fetch(PDO::FETCH_ASSOC);

        return is_array($barisData) ? $barisData : null;
    }

    /**
     * Mengambil daftar riwayat mutasi terbaru beserta informasi rekening dan petugas.
     *
     * @param int $batasJumlah Jumlah maksimum baris yang diambil
     * @return array<int, array<string, mixed>> Daftar mutasi transaksi
     */
    public function ambilMutasiTerbaru(int $batasJumlah = 50): array
    {
        $pernyataan = $this->koneksi->prepare(
            'SELECT m.id, m.kode_referensi, m.id_rekening, m.jenis_transaksi, m.tipe_mutasi,
                    m.nominal, m.saldo_sebelum, m.saldo_sesudah, m.keterangan, m.waktu_transaksi,
                    r.nomor_rekening, n.nama_lengkap AS nama_nasabah, p.nama_lengkap AS nama_petugas
             FROM mutasi_transaksi m
             JOIN rekening r ON r.id = m.id_rekening
             JOIN nasabah n ON n.id = r.id_nasabah
             JOIN pengguna p ON p.id = m.id_petugas
             ORDER BY m.id DESC
             LIMIT :batas_jumlah'
        );
        $pernyataan->bindValue(':batas_jumlah', $batasJumlah, PDO::PARAM_INT);
        $pernyataan->execute();

        return $pernyataan->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil daftar mutasi berdasarkan filter rentang tanggal (YYYY-MM-DD).
     *
     * @param string|null $tanggalMulai Tanggal awal (YYYY-MM-DD) atau null
     * @param string|null $tanggalAkhir Tanggal akhir (YYYY-MM-DD) atau null
     * @param int         $batasJumlah  Batas maksimum baris yang ditampilkan
     * @return array<int, array<string, mixed>> Daftar mutasi terfilter
     */
    public function ambilMutasiBerdasarkanTanggal(
        ?string $tanggalMulai,
        ?string $tanggalAkhir,
        int $batasJumlah = 100
    ): array {
        if (
            $tanggalMulai !== null && $tanggalMulai !== ''
            && $tanggalAkhir !== null && $tanggalAkhir !== ''
        ) {
            // [Konsep RPL - SARGable Query]: Hindari fungsi `DATE(kolom)` pada sisi kiri WHERE
            // karena membungkus kolom dengan fungsi menonaktifkan indeks B-Tree `idx_mutasi_waktu`.
            $waktuMulai = trim($tanggalMulai) . ' 00:00:00';
            $waktuAkhir = trim($tanggalAkhir) . ' 23:59:59';

            $pernyataan = $this->koneksi->prepare(
                'SELECT m.id, m.kode_referensi, m.id_rekening, m.jenis_transaksi, m.tipe_mutasi,
                        m.nominal, m.saldo_sebelum, m.saldo_sesudah, m.keterangan, m.waktu_transaksi,
                        r.nomor_rekening, n.nama_lengkap AS nama_nasabah, p.nama_lengkap AS nama_petugas
                 FROM mutasi_transaksi m
                 JOIN rekening r ON r.id = m.id_rekening
                 JOIN nasabah n ON n.id = r.id_nasabah
                 JOIN pengguna p ON p.id = m.id_petugas
                 WHERE m.waktu_transaksi >= :waktu_mulai AND m.waktu_transaksi <= :waktu_akhir
                 ORDER BY m.id DESC
                 LIMIT :batas_jumlah'
            );
            $pernyataan->bindValue(':waktu_mulai', $waktuMulai, PDO::PARAM_STR);
            $pernyataan->bindValue(':waktu_akhir', $waktuAkhir, PDO::PARAM_STR);
            $pernyataan->bindValue(':batas_jumlah', $batasJumlah, PDO::PARAM_INT);
            $pernyataan->execute();

            return $pernyataan->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->ambilMutasiTerbaru($batasJumlah);
    }
}
