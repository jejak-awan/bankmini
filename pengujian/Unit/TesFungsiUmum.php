<?php

declare(strict_types=1);

namespace Pengujian\Unit;

use InvalidArgumentException;
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

    /**
     * @skenario QA-UNT-004
     * Memastikan keStringNumerik() menerima desimal valid dan melempar InvalidArgumentException bila tidak valid.
     *
     * @return void
     */
    public function ujiKeStringNumerikMemvalidasiAngkaDanMenolakInputNonNumerik(): void
    {
        $this->pastikanSama('150000.50', keStringNumerik(' 150000.50 '));

        $gagalSesuaiHarapan = false;
        try {
            keStringNumerik('bukan_angka');
        } catch (InvalidArgumentException) {
            $gagalSesuaiHarapan = true;
        }

        $this->pastikanBenar($gagalSesuaiHarapan, 'Input non-numerik wajib ditolak dengan InvalidArgumentException.');
    }

    /**
     * @skenario QA-UNT-005
     * Memastikan komponenUi() merender komponen Design System dengan escaping XSS dan menolak traversal.
     *
     * @return void
     */
    public function ujiKomponenUiMerenderDanMenolakPathTraversal(): void
    {
        $htmlKartu = komponenUi('kartu_kpi', [
            'judul' => 'Total Nasabah <script>',
            'nilai' => '42',
            'ikon' => 'bi-people-fill',
            'aksen' => 'biru',
        ]);
        $this->pastikanBenar(
            str_contains($htmlKartu, 'Total Nasabah &lt;script&gt;'),
            'Properti komponenUi() wajib melewati amankanHtml().'
        );
        $this->pastikanBenar(
            str_contains($htmlKartu, 'kartu-statistik-aksen-biru'),
            'Komponen kartu_kpi harus menerapkan kelas aksen token desain.'
        );

        $htmlLencana = komponenUi('lencana_status', ['status' => 'kredit']);
        $this->pastikanBenar(
            str_contains($htmlLencana, 'text-bg-success') && str_contains($htmlLencana, 'KREDIT'),
            'Komponen lencana_status harus memetakan KREDIT ke text-bg-success.'
        );

        $ditolakSesuaiHarapan = false;
        try {
            komponenUi('../konfigurasi/basis_data');
        } catch (InvalidArgumentException) {
            $ditolakSesuaiHarapan = true;
        }
        $this->pastikanBenar($ditolakSesuaiHarapan, 'Jalur traversal pada komponenUi() wajib ditolak.');
    }
}
