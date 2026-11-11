<?php

declare(strict_types=1);

namespace Pengujian\Keamanan;

use Aplikasi\Inti\BasisData;
use Aplikasi\Layanan\LayananTransaksi;
use InvalidArgumentException;
use Pengujian\Integrasi\ModelLogAuditPalsuUji;
use Pengujian\Integrasi\ModelMutasiMemoriUji;
use Pengujian\Integrasi\ModelRekeningMemoriUji;
use Pengujian\PembantuPengujian\KasusUjiDasar;
use Pengujian\PembantuPengujian\PdoTiruanTransaksi;

/**
 * TesKeamananAplikasi
 *
 * Menguji ketahanan aplikasi terhadap input nominal manipulatif (negatif, nol,
 * injeksi string SQL/XSS) pada LayananTransaksi dan fungsi sanitasi.
 */
class TesKeamananAplikasi extends KasusUjiDasar
{
    /**
     * @skenario QA-SEC-001
     * Memastikan input nominal negatif, nol, atau payload injeksi SQL ditolak oleh validator nominal Rupiah.
     *
     * @return void
     */
    public function ujiInputNominalNegatifNolAtauInjeksiSqlDitolak(): void
    {
        BasisData::aturKoneksiPengujian(new PdoTiruanTransaksi());

        $layananTransaksi = new LayananTransaksi(
            new ModelRekeningMemoriUji(),
            new ModelMutasiMemoriUji(),
            new ModelLogAuditPalsuUji()
        );

        $daftarInputBerbahaya = [
            '-50000.00',
            '0.00',
            '0',
            "10000' OR '1'='1",
            '<script>1000</script>',
        ];

        foreach ($daftarInputBerbahaya as $inputUji) {
            $this->pastikanMelemparPengecualian(
                InvalidArgumentException::class,
                function () use ($layananTransaksi, $inputUji): void {
                    $layananTransaksi->prosesSetorTunai(1, $inputUji, 2, 'Uji keamanan', '127.0.0.1');
                }
            );
        }
    }
}

