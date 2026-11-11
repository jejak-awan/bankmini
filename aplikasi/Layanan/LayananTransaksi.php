<?php

declare(strict_types=1);

namespace Aplikasi\Layanan;

use Aplikasi\Inti\BasisData;
use Aplikasi\Model\ModelLogAudit;
use Aplikasi\Model\ModelMutasi;
use Aplikasi\Model\ModelRekening;
use DomainException;
use InvalidArgumentException;
use PDO;
use Throwable;

/**
 * LayananTransaksi
 *
 * @konsep_rpl Transaksi Keuangan ACID, Presisi BCMath Rupiah, & Pencegahan Deadlock
 * Class ini adalah jantung logika bisnis Bank Mini (Single Source of Truth mutasi saldo):
 * 1. Presisi Desimal (`bcadd`, `bcsub`, `bccomp`): Menghindari galat pembulatan tipe `float`
 *    pada perhitungan mata uang Rupiah (`DECIMAL(15,2)`).
 * 2. Row Locking (`SELECT ... FOR UPDATE`): Mengunci baris rekening selama transaksi berlangsung.
 * 3. Urutan Penguncian Transfer (`min()` lalu `max()`): Saat transfer antar 2 rekening, penguncian
 *    selalu dimulai dari `id` rekening terkecil untuk mencegah Deadlock jika ada 2 transfer
 *    silang di waktu bersamaan.
 * 4. Jurnal Koreksi (*Reversal*): Kesalahan input tidak dihapus (`DELETE`), melainkan dibalik
 *    dengan mutasi baru bertipe `KOREKSI`.
 */
class LayananTransaksi
{
    private PDO $koneksi;
    private ModelRekening $modelRekening;
    private ModelMutasi $modelMutasi;
    private ModelLogAudit $modelLogAudit;

    /**
     * Konstruktor LayananTransaksi.
     *
     * @param ModelRekening $modelRekening Model akses data tabel rekening
     * @param ModelMutasi   $modelMutasi   Model akses data tabel mutasi_transaksi
     * @param ModelLogAudit $modelLogAudit Model pencatatan tabel log_audit
     */
    public function __construct(
        ModelRekening $modelRekening,
        ModelMutasi $modelMutasi,
        ModelLogAudit $modelLogAudit
    ) {
        $this->koneksi = BasisData::ambilKoneksi();
        $this->modelRekening = $modelRekening;
        $this->modelMutasi = $modelMutasi;
        $this->modelLogAudit = $modelLogAudit;
    }

    /**
     * Memproses transaksi setoran tunai (KREDIT) dalam mata uang Rupiah (IDR).
     *
     * @param int    $idRekening     ID rekening nasabah tujuan setoran
     * @param string $nominalSetoran Nominal setoran dalam Rupiah (contoh: '50000.00')
     * @param int    $idPetugas      ID petugas/teller yang memproses transaksi
     * @param string $keterangan     Catatan transaksi
     * @param string $alamatIp       Alamat IP petugas untuk audit
     * @return array<string, mixed> Ringkasan bukti transaksi setoran
     *
     * @throws InvalidArgumentException Jika nominal Rupiah tidak valid atau <= 0
     * @throws DomainException          Jika rekening tidak ditemukan atau tidak aktif
     * @throws Throwable                Jika terjadi kegagalan sistem/database (otomatis rollBack)
     */
    public function prosesSetorTunai(
        int $idRekening,
        string $nominalSetoran,
        int $idPetugas,
        string $keterangan,
        string $alamatIp
    ): array {
        $nominalNormal = $this->validasiNominalPositifRupiah($nominalSetoran);

        $this->koneksi->beginTransaction();

        try {
            $dataRekening = $this->modelRekening->kunciRekeningUntukPembaruan($idRekening);
            if ($dataRekening === null) {
                throw new DomainException('Data rekening tidak ditemukan.');
            }

            if ($dataRekening['status'] !== 'aktif') {
                throw new DomainException('Transaksi ditolak: Status rekening sedang tidak aktif.');
            }

            $saldoSebelum = (string) $dataRekening['saldo'];
            $saldoSesudah = bcadd($saldoSebelum, $nominalNormal, 2);
            $kodeReferensi = $this->buatKodeReferensiTransaksi('STR');

            $idMutasi = $this->modelMutasi->simpanMutasiBaru([
                'kode_referensi'         => $kodeReferensi,
                'id_rekening'            => $idRekening,
                'jenis_transaksi'        => 'SETOR',
                'tipe_mutasi'            => 'KREDIT',
                'nominal'                => $nominalNormal,
                'saldo_sebelum'          => $saldoSebelum,
                'saldo_sesudah'          => $saldoSesudah,
                'keterangan'             => trim($keterangan) !== '' ? trim($keterangan) : 'Setoran tunai',
                'id_referensi_transaksi' => null,
                'id_petugas'             => $idPetugas,
            ]);

            $this->modelRekening->perbaruiSaldoRekening($idRekening, $saldoSesudah);

            $this->modelLogAudit->catatLog([
                'id_pengguna' => $idPetugas,
                'aksi'        => 'SETOR_TUNAI',
                'entitas'     => 'mutasi_transaksi',
                'id_entitas'  => (string) $idMutasi,
                'data_lama'   => ['saldo' => $saldoSebelum],
                'data_baru'   => ['saldo' => $saldoSesudah, 'nominal_setor' => $nominalNormal],
                'alamat_ip'   => $alamatIp,
            ]);

            $this->koneksi->commit();

            return [
                'id_mutasi'      => $idMutasi,
                'kode_referensi' => $kodeReferensi,
                'nominal'        => $nominalNormal,
                'saldo_sebelum'  => $saldoSebelum,
                'saldo_sesudah'  => $saldoSesudah,
            ];
        } catch (Throwable $pengecualian) {
            if ($this->koneksi->inTransaction()) {
                $this->koneksi->rollBack();
            }
            throw $pengecualian;
        }
    }

    /**
     * Memproses transaksi penarikan tunai (DEBIT) dalam mata uang Rupiah (IDR).
     *
     * @param int    $idRekening       ID rekening nasabah yang akan ditarik
     * @param string $nominalPenarikan Nominal penarikan dalam Rupiah (contoh: '50000.00')
     * @param int    $idPetugas        ID petugas/teller yang memproses transaksi
     * @param string $keterangan       Catatan tambahan transaksi
     * @param string $alamatIp         Alamat IP petugas untuk kebutuhan audit
     * @return array<string, mixed> Data ringkasan bukti transaksi yang berhasil disimpan
     *
     * @throws InvalidArgumentException Jika format nominal Rupiah tidak valid atau <= 0
     * @throws DomainException          Jika rekening tidak aktif atau saldo efektif tidak mencukupi
     * @throws Throwable                Jika terjadi kegagalan sistem/database (otomatis rollBack)
     */
    public function prosesTarikTunai(
        int $idRekening,
        string $nominalPenarikan,
        int $idPetugas,
        string $keterangan,
        string $alamatIp
    ): array {
        $nominalNormal = $this->validasiNominalPositifRupiah($nominalPenarikan);

        $this->koneksi->beginTransaction();

        try {
            $dataRekening = $this->modelRekening->kunciRekeningUntukPembaruan($idRekening);
            if ($dataRekening === null) {
                throw new DomainException('Data rekening tidak ditemukan.');
            }

            if ($dataRekening['status'] !== 'aktif') {
                throw new DomainException('Transaksi ditolak: Status rekening sedang tidak aktif.');
            }

            $saldoSebelum = (string) $dataRekening['saldo'];
            $saldoMengendapMinimum = (string) $dataRekening['saldo_mengendap_minimum'];
            $saldoEfektif = bcsub($saldoSebelum, $saldoMengendapMinimum, 2);

            if (bccomp($saldoEfektif, $nominalNormal, 2) < 0) {
                throw new DomainException('Saldo efektif Rupiah tidak mencukupi untuk penarikan ini.');
            }

            $saldoSesudah = bcsub($saldoSebelum, $nominalNormal, 2);
            $kodeReferensi = $this->buatKodeReferensiTransaksi('TRK');

            $idMutasi = $this->modelMutasi->simpanMutasiBaru([
                'kode_referensi'         => $kodeReferensi,
                'id_rekening'            => $idRekening,
                'jenis_transaksi'        => 'TARIK',
                'tipe_mutasi'            => 'DEBIT',
                'nominal'                => $nominalNormal,
                'saldo_sebelum'          => $saldoSebelum,
                'saldo_sesudah'          => $saldoSesudah,
                'keterangan'             => trim($keterangan) !== '' ? trim($keterangan) : 'Penarikan tunai',
                'id_referensi_transaksi' => null,
                'id_petugas'             => $idPetugas,
            ]);

            $this->modelRekening->perbaruiSaldoRekening($idRekening, $saldoSesudah);

            $this->modelLogAudit->catatLog([
                'id_pengguna' => $idPetugas,
                'aksi'        => 'TARIK_TUNAI',
                'entitas'     => 'mutasi_transaksi',
                'id_entitas'  => (string) $idMutasi,
                'data_lama'   => ['saldo' => $saldoSebelum],
                'data_baru'   => ['saldo' => $saldoSesudah, 'nominal_tarik' => $nominalNormal],
                'alamat_ip'   => $alamatIp,
            ]);

            $this->koneksi->commit();

            return [
                'id_mutasi'      => $idMutasi,
                'kode_referensi' => $kodeReferensi,
                'nominal'        => $nominalNormal,
                'saldo_sebelum'  => $saldoSebelum,
                'saldo_sesudah'  => $saldoSesudah,
            ];
        } catch (Throwable $pengecualian) {
            if ($this->koneksi->inTransaction()) {
                $this->koneksi->rollBack();
            }
            throw $pengecualian;
        }
    }

    /**
     * Memproses pemindahbukuan / transfer antar rekening dalam mata uang Rupiah (IDR)
     * dengan penguncian berurutan berdasarkan ID rekening terkecil untuk mencegah deadlock.
     *
     * @param int    $idRekeningAsal   ID rekening pengirim (DEBIT)
     * @param int    $idRekeningTujuan ID rekening penerima (KREDIT)
     * @param string $nominalTransfer  Nominal transfer dalam Rupiah (contoh: '25000.00')
     * @param int    $idPetugas        ID petugas/teller yang memproses
     * @param string $keterangan       Keterangan transfer
     * @param string $alamatIp         Alamat IP petugas
     * @return array<string, mixed> Ringkasan bukti transfer keluar dan masuk
     *
     * @throws InvalidArgumentException Jika rekening asal dan tujuan sama atau nominal tidak valid
     * @throws DomainException          Jika rekening tidak aktif atau saldo efektif asal tidak mencukupi
     * @throws Throwable                Jika terjadi kegagalan sistem/database (otomatis rollBack)
     */
    public function prosesTransferAntarRekening(
        int $idRekeningAsal,
        int $idRekeningTujuan,
        string $nominalTransfer,
        int $idPetugas,
        string $keterangan,
        string $alamatIp
    ): array {
        if ($idRekeningAsal === $idRekeningTujuan) {
            throw new InvalidArgumentException('Rekening asal dan rekening tujuan tidak boleh sama.');
        }

        $nominalNormal = $this->validasiNominalPositifRupiah($nominalTransfer);

        $this->koneksi->beginTransaction();

        try {
            // Kunci berurutan dari ID terkecil untuk mencegah Deadlock
            $idPertama = min($idRekeningAsal, $idRekeningTujuan);
            $idKedua = max($idRekeningAsal, $idRekeningTujuan);

            $rekeningPertama = $this->modelRekening->kunciRekeningUntukPembaruan($idPertama);
            $rekeningKedua = $this->modelRekening->kunciRekeningUntukPembaruan($idKedua);

            $dataAsal = $idRekeningAsal === $idPertama ? $rekeningPertama : $rekeningKedua;
            $dataTujuan = $idRekeningTujuan === $idPertama ? $rekeningPertama : $rekeningKedua;

            if ($dataAsal === null || $dataTujuan === null) {
                throw new DomainException('Rekening asal atau rekening tujuan tidak ditemukan.');
            }

            if ($dataAsal['status'] !== 'aktif' || $dataTujuan['status'] !== 'aktif') {
                throw new DomainException('Kedua rekening harus berstatus aktif untuk melakukan transfer.');
            }

            $saldoAsalSebelum = (string) $dataAsal['saldo'];
            $saldoEfektifAsal = bcsub($saldoAsalSebelum, (string) $dataAsal['saldo_mengendap_minimum'], 2);

            if (bccomp($saldoEfektifAsal, $nominalNormal, 2) < 0) {
                throw new DomainException('Saldo efektif rekening pengirim tidak mencukupi untuk transfer.');
            }

            $saldoAsalSesudah = bcsub($saldoAsalSebelum, $nominalNormal, 2);
            $saldoTujuanSebelum = (string) $dataTujuan['saldo'];
            $saldoTujuanSesudah = bcadd($saldoTujuanSebelum, $nominalNormal, 2);

            $kodeRefKeluar = $this->buatKodeReferensiTransaksi('TRK');
            $kodeRefMasuk = $this->buatKodeReferensiTransaksi('TRM');
            $catatanBersih = trim($keterangan) !== '' ? trim($keterangan) : 'Transfer antar rekening';

            $idMutasiKeluar = $this->modelMutasi->simpanMutasiBaru([
                'kode_referensi'         => $kodeRefKeluar,
                'id_rekening'            => $idRekeningAsal,
                'jenis_transaksi'        => 'TRANSFER_KELUAR',
                'tipe_mutasi'            => 'DEBIT',
                'nominal'                => $nominalNormal,
                'saldo_sebelum'          => $saldoAsalSebelum,
                'saldo_sesudah'          => $saldoAsalSesudah,
                'keterangan'             => $catatanBersih . ' ke ' . $dataTujuan['nomor_rekening'],
                'id_referensi_transaksi' => null,
                'id_petugas'             => $idPetugas,
            ]);

            $idMutasiMasuk = $this->modelMutasi->simpanMutasiBaru([
                'kode_referensi'         => $kodeRefMasuk,
                'id_rekening'            => $idRekeningTujuan,
                'jenis_transaksi'        => 'TRANSFER_MASUK',
                'tipe_mutasi'            => 'KREDIT',
                'nominal'                => $nominalNormal,
                'saldo_sebelum'          => $saldoTujuanSebelum,
                'saldo_sesudah'          => $saldoTujuanSesudah,
                'keterangan'             => $catatanBersih . ' dari ' . $dataAsal['nomor_rekening'],
                'id_referensi_transaksi' => $idMutasiKeluar,
                'id_petugas'             => $idPetugas,
            ]);

            $this->modelRekening->perbaruiSaldoRekening($idRekeningAsal, $saldoAsalSesudah);
            $this->modelRekening->perbaruiSaldoRekening($idRekeningTujuan, $saldoTujuanSesudah);

            $this->modelLogAudit->catatLog([
                'id_pengguna' => $idPetugas,
                'aksi'        => 'TRANSFER_ANTAR_REKENING',
                'entitas'     => 'mutasi_transaksi',
                'id_entitas'  => (string) $idMutasiKeluar,
                'data_lama'   => [
                    'saldo_asal'   => $saldoAsalSebelum,
                    'saldo_tujuan' => $saldoTujuanSebelum,
                ],
                'data_baru'   => [
                    'saldo_asal'       => $saldoAsalSesudah,
                    'saldo_tujuan'     => $saldoTujuanSesudah,
                    'nominal_transfer' => $nominalNormal,
                    'id_mutasi_masuk'  => $idMutasiMasuk,
                ],
                'alamat_ip'   => $alamatIp,
            ]);

            $this->koneksi->commit();

            return [
                'id_mutasi_keluar'   => $idMutasiKeluar,
                'id_mutasi_masuk'    => $idMutasiMasuk,
                'kode_ref_keluar'    => $kodeRefKeluar,
                'kode_ref_masuk'     => $kodeRefMasuk,
                'nominal'            => $nominalNormal,
                'saldo_asal_sesudah' => $saldoAsalSesudah,
            ];
        } catch (Throwable $pengecualian) {
            if ($this->koneksi->inTransaction()) {
                $this->koneksi->rollBack();
            }
            throw $pengecualian;
        }
    }

    /**
     * Memproses Jurnal Balik / Reversal (`KOREKSI`) untuk membalik transaksi yang salah input
     * tanpa menghapus atau mengubah baris mutasi asli (Append-Only Ledger).
     *
     * @param int    $idMutasiAsal   ID baris mutasi_transaksi yang akan dikoreksi
     * @param int    $idPetugas      ID supervisor/admin yang menyetujui koreksi
     * @param string $alasanKoreksi  Alasan koreksi wajib diisi
     * @param string $alamatIp       Alamat IP petugas
     * @return array<string, mixed> Ringkasan bukti transaksi koreksi
     *
     * @throws InvalidArgumentException Jika alasan koreksi kosong
     * @throws DomainException          Jika mutasi asal tidak ditemukan atau saldo tidak mencukupi
     * @throws Throwable                Jika terjadi kegagalan sistem/database (otomatis rollBack)
     */
    public function prosesJurnalKoreksi(
        int $idMutasiAsal,
        int $idPetugas,
        string $alasanKoreksi,
        string $alamatIp
    ): array {
        $alasanBersih = trim($alasanKoreksi);
        if ($alasanBersih === '') {
            throw new InvalidArgumentException('Alasan jurnal koreksi wajib diisi.');
        }

        $this->koneksi->beginTransaction();

        try {
            $mutasiAsal = $this->modelMutasi->ambilBerdasarkanId($idMutasiAsal);
            if ($mutasiAsal === null) {
                throw new DomainException('Data transaksi asal yang akan dikoreksi tidak ditemukan.');
            }

            if ($mutasiAsal['jenis_transaksi'] === 'KOREKSI') {
                throw new DomainException('Transaksi bertipe KOREKSI tidak dapat dikoreksi ulang.');
            }

            $idRekening = (int) $mutasiAsal['id_rekening'];
            $dataRekening = $this->modelRekening->kunciRekeningUntukPembaruan($idRekening);
            if ($dataRekening === null || $dataRekening['status'] !== 'aktif') {
                throw new DomainException('Rekening terkait tidak ditemukan atau sedang tidak aktif.');
            }

            $nominalKoreksi = (string) $mutasiAsal['nominal'];
            $saldoSebelum = (string) $dataRekening['saldo'];

            // Balikkan arah mutasi: jika asalnya KREDIT maka koreksinya DEBIT, begitu pula sebaliknya
            if ($mutasiAsal['tipe_mutasi'] === 'KREDIT') {
                $tipeMutasiKoreksi = 'DEBIT';
                if (bccomp($saldoSebelum, $nominalKoreksi, 2) < 0) {
                    throw new DomainException('Saldo rekening saat ini tidak mencukupi untuk membalik setoran tersebut.');
                }
                $saldoSesudah = bcsub($saldoSebelum, $nominalKoreksi, 2);
            } else {
                $tipeMutasiKoreksi = 'KREDIT';
                $saldoSesudah = bcadd($saldoSebelum, $nominalKoreksi, 2);
            }

            $kodeReferensi = $this->buatKodeReferensiTransaksi('KOR');
            $idMutasiKoreksi = $this->modelMutasi->simpanMutasiBaru([
                'kode_referensi'         => $kodeReferensi,
                'id_rekening'            => $idRekening,
                'jenis_transaksi'        => 'KOREKSI',
                'tipe_mutasi'            => $tipeMutasiKoreksi,
                'nominal'                => $nominalKoreksi,
                'saldo_sebelum'          => $saldoSebelum,
                'saldo_sesudah'          => $saldoSesudah,
                'keterangan'             => 'Koreksi atas ' . $mutasiAsal['kode_referensi'] . ': ' . $alasanBersih,
                'id_referensi_transaksi' => $idMutasiAsal,
                'id_petugas'             => $idPetugas,
            ]);

            $this->modelRekening->perbaruiSaldoRekening($idRekening, $saldoSesudah);

            $this->modelLogAudit->catatLog([
                'id_pengguna' => $idPetugas,
                'aksi'        => 'JURNAL_KOREKSI',
                'entitas'     => 'mutasi_transaksi',
                'id_entitas'  => (string) $idMutasiKoreksi,
                'data_lama'   => ['id_mutasi_asal' => $idMutasiAsal, 'saldo_sebelum' => $saldoSebelum],
                'data_baru'   => ['saldo_sesudah' => $saldoSesudah, 'alasan' => $alasanBersih],
                'alamat_ip'   => $alamatIp,
            ]);

            $this->koneksi->commit();

            return [
                'id_mutasi_koreksi' => $idMutasiKoreksi,
                'kode_referensi'    => $kodeReferensi,
                'tipe_mutasi'       => $tipeMutasiKoreksi,
                'nominal'           => $nominalKoreksi,
                'saldo_sebelum'     => $saldoSebelum,
                'saldo_sesudah'     => $saldoSesudah,
            ];
        } catch (Throwable $pengecualian) {
            if ($this->koneksi->inTransaction()) {
                $this->koneksi->rollBack();
            }
            throw $pengecualian;
        }
    }

    /**
     * Memvalidasi dan menormalisasi string nominal Rupiah agar bernilai positif > 0.00.
     *
     * @param string $nominalMasukan String nominal uang
     * @return string String nominal format DECIMAL(15,2)
     *
     * @throws InvalidArgumentException Jika format bukan angka positif > 0
     */
    private function validasiNominalPositifRupiah(string $nominalMasukan): string
    {
        $nominalBersih = trim($nominalMasukan);
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $nominalBersih)) {
            throw new InvalidArgumentException('Format nominal Rupiah tidak valid.');
        }

        $nominalNormal = bcadd($nominalBersih, '0.00', 2);
        if (bccomp($nominalNormal, '0.00', 2) <= 0) {
            throw new InvalidArgumentException('Nominal transaksi Rupiah harus lebih besar dari Rp 0.');
        }

        return $nominalNormal;
    }

    /**
     * Membuat kode referensi transaksi unik (contoh: STR-20261111-849201).
     *
     * @param string $awalan Kode awalan jenis transaksi (STR, TRK, TRM, KOR)
     * @return string Kode referensi unik
     */
    private function buatKodeReferensiTransaksi(string $awalan): string
    {
        return sprintf('%s-%s-%06d', $awalan, date('Ymd'), random_int(1, 999999));
    }
}

