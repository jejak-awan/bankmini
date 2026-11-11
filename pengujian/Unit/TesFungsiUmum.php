<?php

declare(strict_types=1);

namespace Pengujian\Unit;

use Pengujian\PembantuPengujian\KasusUjiDasar;

/**
 * TesFungsiUmum
 *
 * Menguji fungsi pembantu murni (stateless helpers): formatRupiah(),
 * amankanHtml(), dan formatTanggalIndonesia().
 */
class TesFungsiUmum extends KasusUjiDasar
{
    /**
     * @skenario QA-UNT-001
     * Memastikan formatRupiah() memformat nilai nominal menjadi string mata uang Rupiah (IDR) dengan benar.
     *
     * @return void
     */
    public function ujiFormatRupiahMenampilkanSimbolDanPemisahRibuan(): void
    {
        $this->pastikanSama('Rp 150.000', formatRupiah('150000.00'));
        $this->pastikanSama('Rp 150.000,00', formatRupiah('150000.00', true));
        $this->pastikanSama('Rp 0', formatRupiah(0));
    }

    /**
     * @skenario QA-UNT-002
     * Memastikan amankanHtml() melakukan sanitasi karakter berbahaya XSS menjadi entitas HTML aman.
     *
     * @return void
     */
    public function ujiAmankanHtmlMenangkalPayloadXss(): void
    {
        $masukanBerbahaya = '<script>alert("xss")</script>';
        $hasilAman = amankanHtml($masukanBerbahaya);

        $this->pastikanSama('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $hasilAman);
    }

    /**
     * @skenario QA-UNT-003
     * Memastikan formatTanggalIndonesia() menerjemahkan tanggal SQL ke nama bulan Bahasa Indonesia.
     *
     * @return void
     */
    public function ujiFormatTanggalIndonesiaMenampilkanBulanIndonesia(): void
    {
        $hasilTanggal = formatTanggalIndonesia('2026-11-11 17:30:00', true);
        $this->pastikanSama('11 November 2026 17:30', $hasilTanggal);
    }
}
