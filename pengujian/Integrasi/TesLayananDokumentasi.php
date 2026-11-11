<?php

declare(strict_types=1);

namespace Pengujian\Integrasi;

use Aplikasi\Layanan\LayananDokumentasi;
use InvalidArgumentException;
use Pengujian\PembantuPengujian\KasusUjiDasar;

/**
 * TesLayananDokumentasi
 *
 * Menguji pengindeksan katalog dokumentasi, konversi Markdown ke HTML,
 * pencarian kata kunci materi RPL, serta proteksi keamanan Anti-Path Traversal.
 */
class TesLayananDokumentasi extends KasusUjiDasar
{
    /**
     * @skenario QA-INT-009
     * Memastikan katalog dokumentasi mengindeks kategori utama dan dapat membaca berkas .md secara sah.
     *
     * @return void
     */
    public function ujiKatalogDanPembacaanDokumenSahBerhasil(): void
    {
        $layanan = new LayananDokumentasi();
        $katalog = $layanan->ambilKatalogTerindeks();

        $this->pastikanBenar(isset($katalog['02-panduan-dev']), 'Kategori 02-panduan-dev wajib terindeks.');
        $this->pastikanBenar(
            count($katalog['02-panduan-dev']['daftar_dokumen']) > 0,
            'Kategori 02-panduan-dev wajib memiliki daftar dokumen.'
        );

        $dokumen = $layanan->bacaDokumenAman('02-panduan-dev/arsitektur-sistem.md');
        $this->pastikanSama('02-panduan-dev/arsitektur-sistem.md', $dokumen['jalur']);
        $this->pastikanBenar(
            str_contains($dokumen['html'], 'Single Source of Truth'),
            'HTML hasil konversi harus memuat isi materi arsitektur sistem.'
        );
        $this->pastikanBenar(
            count($dokumen['daftar_isi']) > 0,
            'Daftar isi otomatis (TOC) harus diekstrak dari subjudul dokumen.'
        );
    }

    /**
     * @skenario QA-INT-010
     * Memastikan pencarian kata kunci menemukan dokumen materi RPL dan menolak upaya Path Traversal (../).
     *
     * @return void
     */
    public function ujiPencarianKataKunciDanPenolakanPathTraversal(): void
    {
        $layanan = new LayananDokumentasi();
        $hasilCari = $layanan->cariDokumen('InnoDB');

        $this->pastikanBenar(count($hasilCari) > 0, 'Pencarian kata kunci InnoDB harus menemukan dokumen terkait.');

        $ditolakSesuaiHarapan = false;
        try {
            $layanan->bacaDokumenAman('../../konfigurasi/basis_data.php');
        } catch (InvalidArgumentException) {
            $ditolakSesuaiHarapan = true;
        }

        $this->pastikanBenar(
            $ditolakSesuaiHarapan,
            'Upaya Path Traversal keluar dari folder dokumentasi wajib ditolak dengan InvalidArgumentException.'
        );
    }
}
