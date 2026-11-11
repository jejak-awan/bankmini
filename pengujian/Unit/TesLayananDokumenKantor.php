<?php

declare(strict_types=1);

namespace Pengujian\Unit;

use Aplikasi\Layanan\LayananDokumenKantor;
use Pengujian\PembantuPengujian\KasusUjiDasar;

/**
 * TesLayananDokumenKantor
 *
 * Pengujian otomatis untuk memverifikasi kesiapan pustaka PDF (`dompdf/dompdf`),
 * Excel (`phpoffice/phpspreadsheet`, `shuchkin/simplexlsxgen`, `shuchkin/simplexlsx`),
 * Word (`phpoffice/phpword`), CSV, serta template HTML siap cetak pada LayananDokumenKantor.
 */
final class TesLayananDokumenKantor extends KasusUjiDasar
{
    /**
     * Memastikan seluruh pustaka PDF, Excel, dan Word siap dimuat dan pembuatan biner PDF sah.
     *
     * @skenario QA-UNIT-006 Verifikasi Kesiapan Pustaka Dokumen & Pembuatan Biner PDF (Dompdf)
     * @return void
     */
    public function ujiPembuatanPdfDariHtmlMenghasilkanBinerPdfSah(): void
    {
        $layananDokumen = new LayananDokumenKantor();
        $statusPustaka = $layananDokumen->periksaKesiapanPustaka();

        $this->pastikanBenar($statusPustaka['pdf'], 'Pustaka PDF (Dompdf) harus siap.');
        $this->pastikanBenar($statusPustaka['excel'], 'Pustaka Excel (SimpleXLSX & PhpSpreadsheet) harus siap.');
        $this->pastikanBenar($statusPustaka['word'], 'Pustaka Word (PhpWord) harus siap.');

        $binerPdf = $layananDokumen->buatPdfDariHtml(
            '<table><tr><th>No. Rekening</th><th>Nominal Setoran</th></tr>'
            . '<tr><td>BM-2026-0001</td><td>Rp 150.000,00</td></tr></table>'
        );

        $this->pastikanBenar(
            str_starts_with($binerPdf, '%PDF-'),
            'Luaran buatPdfDariHtml() wajib berupa biner PDF dengan magic header %PDF-.'
        );
        $this->pastikanBenar(
            strlen($binerPdf) > 500,
            'Ukuran biner PDF harus lebih dari 500 byte.'
        );
    }

    /**
     * Memastikan proses ekspor ke Excel (.xlsx) & CSV serta impor kembali berjalan akurat tanpa kehilangan presisi.
     *
     * @skenario QA-UNIT-007 Verifikasi Ekspor & Impor Tabel Excel (.xlsx) serta CSV
     * @return void
     */
    public function ujiEksporDanImporExcelXlsxSertaCsvBerjalanAkurat(): void
    {
        $layananDokumen = new LayananDokumenKantor();
        $judulKolom = ['Nomor Rekening', 'Nama Nasabah', 'Saldo Akhir (IDR)'];
        $barisData = [
            ['BM-2026-0001', 'Budi Santoso', '250000.00'],
            ['BM-2026-0002', 'Siti Aminah', '1750500.00'],
        ];

        $binerExcel = $layananDokumen->buatExcelDariTabel($judulKolom, $barisData, 'Rekening Aktif');
        $this->pastikanBenar(
            str_starts_with($binerExcel, "PK\x03\x04"),
            'Luaran .xlsx wajib memiliki header arsip OpenXML (PK\\x03\\x04).'
        );

        $hasilImporExcel = $layananDokumen->bacaDataDariExcel($binerExcel, false);
        $this->pastikanSama(3, count($hasilImporExcel), 'Impor Excel harus menghasilkan 1 baris header + 2 baris data.');
        $this->pastikanSama('BM-2026-0001', $hasilImporExcel[1][0], 'Nomor rekening baris pertama harus sama persis.');
        $this->pastikanSama('1750500.00', $hasilImporExcel[2][2], 'Nominal Rupiah pada baris kedua harus terjaga.');

        $kontenCsv = $layananDokumen->buatCsvDariTabel($judulKolom, $barisData);
        $hasilImporCsv = $layananDokumen->bacaDataDariCsv($kontenCsv);
        $this->pastikanSama(3, count($hasilImporCsv), 'Impor CSV harus menghasilkan 3 baris.');
        $this->pastikanSama('Siti Aminah', $hasilImporCsv[2][1], 'Nama nasabah pada hasil impor CSV harus sesuai.');
    }

    /**
     * Memastikan pembuatan dokumen Microsoft Word (.docx) dan pembungkus HTML siap cetak berhasil.
     *
     * @skenario QA-UNIT-008 Verifikasi Pembuatan Dokumen Word (.docx) & Template HTML Cetak
     * @return void
     */
    public function ujiPembuatanDokumenWordDocxDanHtmlSiapCetakBerhasil(): void
    {
        $layananDokumen = new LayananDokumenKantor();

        $binerDocx = $layananDokumen->buatDokumenWord(
            'SURAT KETERANGAN AKTIF REKENING SISWA',
            [
                'Menerangkan bahwa siswa di bawah ini adalah nasabah aktif Bank Mini Sekolah.',
                'Dokumen ini diterbitkan secara sah oleh Sistem Informasi Bank Mini (IDR).',
            ],
            ['Nomor Rekening', 'Nama Siswa', 'Saldo Efektif'],
            [['BM-2026-0001', 'Budi Santoso', 'Rp 250.000,00']]
        );

        $this->pastikanBenar(
            str_starts_with($binerDocx, "PK\x03\x04"),
            'Luaran .docx wajib berupa arsip OpenXML sah (PK\\x03\\x04).'
        );
        $this->pastikanBenar(
            strlen($binerDocx) > 1000,
            'Ukuran biner dokumen Word (.docx) harus lebih dari 1000 byte.'
        );

        $htmlCetak = $layananDokumen->siapkanHtmlCetak(
            'Slip Setoran Tunai',
            '<p>Nominal: Rp 100.000,00</p>',
            true
        );
        $this->pastikanBenar(
            str_contains($htmlCetak, 'window.print()'),
            'HTML cetak otomatis harus menyertakan skrip window.print().'
        );
    }
}
