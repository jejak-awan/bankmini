<?php

declare(strict_types=1);

namespace Aplikasi\Layanan;

use Aplikasi\Inti\BasisData;
use Aplikasi\Model\ModelLogAudit;
use Aplikasi\Model\ModelMutasi;
use Aplikasi\Model\ModelNasabah;
use Aplikasi\Model\ModelProduk;
use Aplikasi\Model\ModelRekening;
use DomainException;
use InvalidArgumentException;
use PDO;
use Throwable;

/**
 * LayananRekening
 *
 * @konsep_rpl Atomisitas Pembukaan Rekening & Setoran Awal (ACID Transaction Boundary)
 * Mengapa pembukaan rekening dan pencatatan mutasi setoran awal wajib dibungkus
 * dalam satu `$this->koneksi->beginTransaction()`?
 * Agar tidak pernah terjadi kondisi "Rekening berhasil dibuat dengan saldo Rp 25.000,
 * tetapi baris mutasinya gagal tercatat di tabel `mutasi_transaksi`". Jika salah satu
 * langkah gagal, seluruhnya dibatalkan (`rollBack()`).
 */
class LayananRekening
{
    private PDO $koneksi;
    private ModelNasabah $modelNasabah;
    private ModelProduk $modelProduk;
    private ModelRekening $modelRekening;
    private ModelMutasi $modelMutasi;
    private ModelLogAudit $modelLogAudit;

    /**
     * Konstruktor LayananRekening.
     *
     * @param ModelNasabah  $modelNasabah  Model akses tabel nasabah
     * @param ModelProduk   $modelProduk   Model akses tabel produk_tabungan
     * @param ModelRekening $modelRekening Model akses tabel rekening
     * @param ModelMutasi   $modelMutasi   Model akses tabel mutasi_transaksi
     * @param ModelLogAudit $modelLogAudit Model akses tabel log_audit
     */
    public function __construct(
        ModelNasabah $modelNasabah,
        ModelProduk $modelProduk,
        ModelRekening $modelRekening,
        ModelMutasi $modelMutasi,
        ModelLogAudit $modelLogAudit
    ) {
        $this->koneksi = BasisData::ambilKoneksi();
        $this->modelNasabah = $modelNasabah;
        $this->modelProduk = $modelProduk;
        $this->modelRekening = $modelRekening;
        $this->modelMutasi = $modelMutasi;
        $this->modelLogAudit = $modelLogAudit;
    }

    /**
     * Membuka rekening tabungan baru untuk nasabah dan mencatat mutasi setoran awal Rupiah.
     *
     * @param int    $idNasabah          ID nasabah pemilik rekening
     * @param int    $idProduk           ID produk tabungan yang dipilih
     * @param string $nominalSetoranAwal Nominal setoran awal Rupiah (contoh: '25000.00')
     * @param int    $idPetugas          ID petugas/teller yang memproses pembukaan
     * @param string $alamatIp           Alamat IP petugas
     * @return array<string, mixed> Data rekening baru beserta kode referensi mutasi awal
     *
     * @throws InvalidArgumentException Jika nominal setoran awal tidak valid
     * @throws DomainException          Jika nasabah/produk tidak aktif atau setoran kurang dari minimum
     * @throws Throwable                Jika terjadi kegagalan database (otomatis rollBack)
     */
    public function bukaRekeningBaru(
        int $idNasabah,
        int $idProduk,
        string $nominalSetoranAwal,
        int $idPetugas,
        string $alamatIp
    ): array {
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $nominalSetoranAwal)) {
            throw new InvalidArgumentException('Format nominal setoran awal Rupiah tidak valid.');
        }

        $nominalNormal = bcadd($nominalSetoranAwal, '0.00', 2);

        $dataNasabah = $this->modelNasabah->ambilBerdasarkanId($idNasabah);
        if ($dataNasabah === null || $dataNasabah['status'] !== 'aktif') {
            throw new DomainException('Data nasabah tidak ditemukan atau berstatus nonaktif.');
        }

        $dataProduk = $this->modelProduk->ambilBerdasarkanId($idProduk);
        if ($dataProduk === null || $dataProduk['status'] !== 'aktif') {
            throw new DomainException('Produk tabungan tidak ditemukan atau sedang tidak aktif.');
        }

        $setoranMinimum = (string) $dataProduk['setoran_awal_minimum'];
        if (bccomp($nominalNormal, $setoranMinimum, 2) < 0) {
            throw new DomainException(
                'Setoran awal kurang dari batas minimum produk (' . formatRupiah($setoranMinimum) . ').'
            );
        }

        $this->koneksi->beginTransaction();

        try {
            $nomorRekeningBaru = $this->buatNomorRekeningUnik($idProduk);

            $idRekening = $this->modelRekening->simpanRekeningBaru([
                'nomor_rekening' => $nomorRekeningBaru,
                'id_nasabah'     => $idNasabah,
                'id_produk'      => $idProduk,
                'saldo'          => $nominalNormal,
                'status'         => 'aktif',
            ]);

            $kodeReferensi = sprintf('STR-%s-%06d', date('Ymd'), random_int(1, 999999));
            $idMutasi = $this->modelMutasi->simpanMutasiBaru([
                'kode_referensi'         => $kodeReferensi,
                'id_rekening'            => $idRekening,
                'jenis_transaksi'        => 'SETOR',
                'tipe_mutasi'            => 'KREDIT',
                'nominal'                => $nominalNormal,
                'saldo_sebelum'          => '0.00',
                'saldo_sesudah'          => $nominalNormal,
                'keterangan'             => 'Setoran awal pembukaan rekening',
                'id_referensi_transaksi' => null,
                'id_petugas'             => $idPetugas,
            ]);

            $this->modelLogAudit->catatLog([
                'id_pengguna' => $idPetugas,
                'aksi'        => 'BUKA_REKENING_BARU',
                'entitas'     => 'rekening',
                'id_entitas'  => (string) $idRekening,
                'data_lama'   => null,
                'data_baru'   => [
                    'nomor_rekening' => $nomorRekeningBaru,
                    'saldo_awal'     => $nominalNormal,
                ],
                'alamat_ip'   => $alamatIp,
            ]);

            $this->koneksi->commit();

            return [
                'id_rekening'    => $idRekening,
                'nomor_rekening' => $nomorRekeningBaru,
                'saldo_awal'     => $nominalNormal,
                'id_mutasi'      => $idMutasi,
                'kode_referensi' => $kodeReferensi,
            ];
        } catch (Throwable $pengecualian) {
            if ($this->koneksi->inTransaction()) {
                $this->koneksi->rollBack();
            }
            throw $pengecualian;
        }
    }

    /**
     * Menghasilkan nomor rekening unik dengan format BM-<TahunBulan>-<IdProduk>-<6DigitAcak>.
     *
     * @param int $idProduk ID produk tabungan
     * @return string Nomor rekening unik
     */
    private function buatNomorRekeningUnik(int $idProduk): string
    {
        return sprintf('BM-%s-%02d-%06d', date('Ym'), $idProduk, random_int(100000, 999999));
    }
}
