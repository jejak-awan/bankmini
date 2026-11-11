<?php

declare(strict_types=1);

namespace Pengujian\Integrasi;

use Aplikasi\Inti\BasisData;
use Aplikasi\Layanan\LayananTransaksi;
use DomainException;
use Pengujian\PembantuPengujian\KasusUjiDasar;
use Pengujian\PembantuPengujian\PdoTiruanTransaksi;

/**
 * TesLayananTransaksi
 *
 * Menguji integritas transaksi keuangan Rupiah (IDR), kepatuhan ACID
 * (beginTransaction, commit, rollBack), batas saldo mengendap minimum,
 * transfer antar rekening, dan jurnal koreksi (Append-Only Ledger).
 */
class TesLayananTransaksi extends KasusUjiDasar
{
    /**
     * @skenario QA-TRX-001
     * Memastikan setoran tunai Rupiah menambah saldo rekening secara akurat dan mencatat mutasi KREDIT.
     *
     * @return void
     */
    public function ujiSetorTunaiMenambahSaldoSecaraAkurat(): void
    {
        $pdoUji = new PdoTiruanTransaksi();
        BasisData::aturKoneksiPengujian($pdoUji);

        $modelRekening = new ModelRekeningMemoriUji();
        $idRekening = $modelRekening->simpanRekeningBaru([
            'nomor_rekening'          => 'BM-202611-01-100001',
            'id_nasabah'              => 1,
            'id_produk'               => 1,
            'saldo'                   => '100000.00',
            'status'                  => 'aktif',
            'saldo_mengendap_minimum' => '10000.00',
        ]);

        $modelMutasi = new ModelMutasiMemoriUji();
        $layananTransaksi = new LayananTransaksi($modelRekening, $modelMutasi, new ModelLogAuditPalsuUji());

        $hasil = $layananTransaksi->prosesSetorTunai(
            $idRekening,
            '50000.00',
            2,
            'Setoran tabungan mingguan',
            '127.0.0.1'
        );

        $this->pastikanSama('100000.00', $hasil['saldo_sebelum']);
        $this->pastikanSama('150000.00', $hasil['saldo_sesudah']);
        $this->pastikanSama('150000.00', $modelRekening->penyimpananRekening[$idRekening]['saldo']);
        $this->pastikanSama(1, $pdoUji->ambilJumlahCommit());
    }

    /**
     * @skenario QA-TRX-002
     * Memastikan penarikan yang menyisakan saldo di bawah saldo mengendap minimum ditolak
     * dan transaksi database di-rollback tanpa mengubah saldo.
     *
     * @return void
     */
    public function ujiTarikTunaiMelebihiSaldoEfektifDitolakDanRollback(): void
    {
        $pdoUji = new PdoTiruanTransaksi();
        BasisData::aturKoneksiPengujian($pdoUji);

        $modelRekening = new ModelRekeningMemoriUji();
        // Saldo Rp 50.000,00 dengan saldo mengendap minimum Rp 20.000,00 -> Saldo efektif = Rp 30.000,00
        $idRekening = $modelRekening->simpanRekeningBaru([
            'nomor_rekening'          => 'BM-202611-01-100002',
            'id_nasabah'              => 1,
            'id_produk'               => 1,
            'saldo'                   => '50000.00',
            'status'                  => 'aktif',
            'saldo_mengendap_minimum' => '20000.00',
        ]);

        $modelMutasi = new ModelMutasiMemoriUji();
        $layananTransaksi = new LayananTransaksi($modelRekening, $modelMutasi, new ModelLogAuditPalsuUji());

        $this->pastikanMelemparPengecualian(
            DomainException::class,
            function () use ($layananTransaksi, $idRekening): void {
                $layananTransaksi->prosesTarikTunai(
                    $idRekening,
                    '35000.00',
                    2,
                    'Penarikan melebihi saldo efektif',
                    '127.0.0.1'
                );
            }
        );

        $this->pastikanSama('50000.00', $modelRekening->penyimpananRekening[$idRekening]['saldo']);
        $this->pastikanSama(0, count($modelMutasi->penyimpananMutasi));
        $this->pastikanSama(1, $pdoUji->ambilJumlahRollback());
    }

    /**
     * @skenario QA-TRX-003
     * Memastikan transfer antar rekening mengurangi saldo pengirim dan menambah saldo penerima secara atomik.
     *
     * @return void
     */
    public function ujiTransferAntarRekeningMemperbaruiKeduaSaldoSecaraAtomik(): void
    {
        $pdoUji = new PdoTiruanTransaksi();
        BasisData::aturKoneksiPengujian($pdoUji);

        $modelRekening = new ModelRekeningMemoriUji();
        $idAsal = $modelRekening->simpanRekeningBaru([
            'nomor_rekening'          => 'BM-202611-01-200001',
            'id_nasabah'              => 1,
            'id_produk'               => 1,
            'saldo'                   => '100000.00',
            'status'                  => 'aktif',
            'saldo_mengendap_minimum' => '10000.00',
        ]);
        $idTujuan = $modelRekening->simpanRekeningBaru([
            'nomor_rekening'          => 'BM-202611-01-200002',
            'id_nasabah'              => 2,
            'id_produk'               => 1,
            'saldo'                   => '40000.00',
            'status'                  => 'aktif',
            'saldo_mengendap_minimum' => '10000.00',
        ]);

        $modelMutasi = new ModelMutasiMemoriUji();
        $layananTransaksi = new LayananTransaksi($modelRekening, $modelMutasi, new ModelLogAuditPalsuUji());

        $hasil = $layananTransaksi->prosesTransferAntarRekening(
            $idAsal,
            $idTujuan,
            '25000.00',
            2,
            'Pemindahbukuan tabungan',
            '127.0.0.1'
        );

        $this->pastikanSama('75000.00', $hasil['saldo_asal_sesudah']);
        $this->pastikanSama('75000.00', $modelRekening->penyimpananRekening[$idAsal]['saldo']);
        $this->pastikanSama('65000.00', $modelRekening->penyimpananRekening[$idTujuan]['saldo']);
        $this->pastikanSama(2, count($modelMutasi->penyimpananMutasi));
    }

    /**
     * @skenario QA-TRX-004
     * Memastikan Jurnal Koreksi (Reversal) membalikkan saldo tanpa menghapus baris mutasi asli (Append-Only Ledger).
     *
     * @return void
     */
    public function ujiJurnalKoreksiMembalikMutasiTanpaMenghapusDataAsli(): void
    {
        $pdoUji = new PdoTiruanTransaksi();
        BasisData::aturKoneksiPengujian($pdoUji);

        $modelRekening = new ModelRekeningMemoriUji();
        $idRekening = $modelRekening->simpanRekeningBaru([
            'nomor_rekening'          => 'BM-202611-01-300001',
            'id_nasabah'              => 1,
            'id_produk'               => 1,
            'saldo'                   => '100000.00',
            'status'                  => 'aktif',
            'saldo_mengendap_minimum' => '10000.00',
        ]);

        $modelMutasi = new ModelMutasiMemoriUji();
        $layananTransaksi = new LayananTransaksi($modelRekening, $modelMutasi, new ModelLogAuditPalsuUji());

        // Salah input setoran Rp 50.000,00 -> saldo menjadi Rp 150.000,00
        $hasilSetor = $layananTransaksi->prosesSetorTunai(
            $idRekening,
            '50000.00',
            2,
            'Salah input setoran',
            '127.0.0.1'
        );

        // Lakukan Jurnal Koreksi atas mutasi setoran tersebut
        $hasilKoreksi = $layananTransaksi->prosesJurnalKoreksi(
            (int) $hasilSetor['id_mutasi'],
            3,
            'Koreksi salah ketik nominal oleh teller',
            '127.0.0.1'
        );

        $this->pastikanSama('DEBIT', $hasilKoreksi['tipe_mutasi']);
        $this->pastikanSama('100000.00', $hasilKoreksi['saldo_sesudah']);
        // Pastikan kedua baris mutasi tetap ada di ledger (Append-Only)
        $this->pastikanSama(2, count($modelMutasi->penyimpananMutasi));
    }
}

