<?php

declare(strict_types=1);

namespace Pengujian\Integrasi;

use Aplikasi\Inti\BasisData;
use Aplikasi\Layanan\LayananRekening;
use Aplikasi\Model\ModelMutasi;
use Aplikasi\Model\ModelNasabah;
use Aplikasi\Model\ModelProduk;
use Aplikasi\Model\ModelRekening;
use DomainException;
use Pengujian\PembantuPengujian\KasusUjiDasar;
use Pengujian\PembantuPengujian\PdoTiruanTransaksi;

/**
 * ModelNasabahPalsuUji
 *
 * Tiruan ModelNasabah di memori untuk pengujian LayananRekening.
 */
class ModelNasabahPalsuUji extends ModelNasabah
{
    /**
     * Konstruktor ModelNasabahPalsuUji.
     */
    public function __construct()
    {
    }

    /**
     * Mengambil data nasabah uji berdasarkan ID.
     *
     * @param int $idNasabah ID nasabah
     * @return array<string, mixed>|null Data nasabah
     */
    public function ambilBerdasarkanId(int $idNasabah): ?array
    {
        if ($idNasabah === 1) {
            return [
                'id'              => 1,
                'nomor_identitas' => 'NIS-1001',
                'nama_lengkap'    => 'Budi Santoso',
                'status'          => 'aktif',
            ];
        }
        return null;
    }
}

/**
 * ModelProdukPalsuUji
 *
 * Tiruan ModelProduk di memori untuk pengujian LayananRekening.
 */
class ModelProdukPalsuUji extends ModelProduk
{
    /**
     * Konstruktor ModelProdukPalsuUji.
     */
    public function __construct()
    {
    }

    /**
     * Mengambil data produk uji berdasarkan ID.
     *
     * @param int $idProduk ID produk
     * @return array<string, mixed>|null Data produk
     */
    public function ambilBerdasarkanId(int $idProduk): ?array
    {
        if ($idProduk === 1) {
            return [
                'id'                      => 1,
                'kode_produk'             => 'TAB-SISWA',
                'nama_produk'             => 'Tabungan Siswa',
                'setoran_awal_minimum'    => '10000.00',
                'saldo_mengendap_minimum' => '10000.00',
                'status'                  => 'aktif',
            ];
        }
        return null;
    }
}

/**
 * ModelRekeningMemoriUji
 *
 * Tiruan ModelRekening di memori untuk pengujian LayananRekening dan LayananTransaksi.
 */
class ModelRekeningMemoriUji extends ModelRekening
{
    /** @var array<int, array<string, mixed>> Data rekening di memori */
    public array $penyimpananRekening = [];

    /**
     * Konstruktor ModelRekeningMemoriUji.
     */
    public function __construct()
    {
    }

    /**
     * Menyimpan rekening baru ke memori.
     *
     * @param array<string, mixed> $dataRekening Data rekening
     * @return int ID rekening baru
     */
    public function simpanRekeningBaru(array $dataRekening): int
    {
        $idBaru = count($this->penyimpananRekening) + 1;
        $this->penyimpananRekening[$idBaru] = [
            'id'                      => $idBaru,
            'nomor_rekening'          => (string) $dataRekening['nomor_rekening'],
            'id_nasabah'              => (int) $dataRekening['id_nasabah'],
            'id_produk'               => (int) $dataRekening['id_produk'],
            'saldo'                   => (string) $dataRekening['saldo'],
            'status'                  => (string) ($dataRekening['status'] ?? 'aktif'),
            'saldo_mengendap_minimum' => (string) ($dataRekening['saldo_mengendap_minimum'] ?? '10000.00'),
        ];
        return $idBaru;
    }

    /**
     * Mengunci dan mengambil data rekening dari memori.
     *
     * @param int $idRekening ID rekening
     * @return array<string, mixed>|null Data rekening
     */
    public function kunciRekeningUntukPembaruan(int $idRekening): ?array
    {
        return $this->penyimpananRekening[$idRekening] ?? null;
    }

    /**
     * Memperbarui saldo rekening di memori.
     *
     * @param int    $idRekening   ID rekening
     * @param string $saldoSesudah Saldo baru dalam Rupiah DECIMAL(15,2)
     * @return void
     */
    public function perbaruiSaldoRekening(int $idRekening, string $saldoSesudah): void
    {
        if (isset($this->penyimpananRekening[$idRekening])) {
            $this->penyimpananRekening[$idRekening]['saldo'] = $saldoSesudah;
        }
    }
}

/**
 * ModelMutasiMemoriUji
 *
 * Tiruan ModelMutasi di memori (Append-Only Ledger) untuk pengujian QA.
 */
class ModelMutasiMemoriUji extends ModelMutasi
{
    /** @var array<int, array<string, mixed>> Daftar mutasi tersimpan */
    public array $penyimpananMutasi = [];

    /**
     * Konstruktor ModelMutasiMemoriUji.
     */
    public function __construct()
    {
    }

    /**
     * Menyimpan baris mutasi baru ke dalam memori.
     *
     * @param array<string, mixed> $dataMutasi Data mutasi
     * @return int ID mutasi baru
     */
    public function simpanMutasiBaru(array $dataMutasi): int
    {
        $idBaru = count($this->penyimpananMutasi) + 1;
        $dataMutasi['id'] = $idBaru;
        $this->penyimpananMutasi[$idBaru] = $dataMutasi;
        return $idBaru;
    }

    /**
     * Mengambil data mutasi berdasarkan ID dari memori.
     *
     * @param int $idMutasi ID mutasi
     * @return array<string, mixed>|null Data mutasi
     */
    public function ambilBerdasarkanId(int $idMutasi): ?array
    {
        return $this->penyimpananMutasi[$idMutasi] ?? null;
    }
}

/**
 * TesLayananRekening
 *
 * Menguji pembukaan rekening baru beserta validasi setoran awal minimum Rupiah (IDR).
 */
class TesLayananRekening extends KasusUjiDasar
{
    /**
     * @skenario QA-REK-001
     * Memastikan pembukaan rekening dengan setoran awal memenuhi batas minimum berhasil membuat rekening dan mutasi awal.
     *
     * @return void
     */
    public function ujiBukaRekeningDenganSetoranAwalMemenuhiMinimumBerhasil(): void
    {
        $pdoUji = new PdoTiruanTransaksi();
        BasisData::aturKoneksiPengujian($pdoUji);

        $modelRekening = new ModelRekeningMemoriUji();
        $modelMutasi = new ModelMutasiMemoriUji();
        $layananRekening = new LayananRekening(
            new ModelNasabahPalsuUji(),
            new ModelProdukPalsuUji(),
            $modelRekening,
            $modelMutasi,
            new ModelLogAuditPalsuUji()
        );

        $hasil = $layananRekening->bukaRekeningBaru(1, 1, '25000.00', 2, '127.0.0.1');

        $this->pastikanSama('25000.00', $hasil['saldo_awal']);
        $this->pastikanSama(1, count($modelMutasi->penyimpananMutasi));
        $this->pastikanSama(1, $pdoUji->ambilJumlahCommit());
    }

    /**
     * @skenario QA-REK-002
     * Memastikan pembukaan rekening dengan setoran awal di bawah minimum produk ditolak dengan DomainException.
     *
     * @return void
     */
    public function ujiBukaRekeningKurangDariSetoranAwalMinimumDitolak(): void
    {
        $pdoUji = new PdoTiruanTransaksi();
        BasisData::aturKoneksiPengujian($pdoUji);

        $layananRekening = new LayananRekening(
            new ModelNasabahPalsuUji(),
            new ModelProdukPalsuUji(),
            new ModelRekeningMemoriUji(),
            new ModelMutasiMemoriUji(),
            new ModelLogAuditPalsuUji()
        );

        $this->pastikanMelemparPengecualian(
            DomainException::class,
            function () use ($layananRekening): void {
                $layananRekening->bukaRekeningBaru(1, 1, '5000.00', 2, '127.0.0.1');
            }
        );
    }
}

