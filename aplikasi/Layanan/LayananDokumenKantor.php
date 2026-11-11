<?php

declare(strict_types=1);

namespace Aplikasi\Layanan;

use Dompdf\Dompdf;
use Dompdf\Options;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpWord\IOFactory as PabrikIoWord;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings as PengaturanWord;
use RuntimeException;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

/**
 * LayananDokumenKantor
 *
 * @konsep_rpl Pola Desain Adapter & Facade untuk Pemrosesan Dokumen (PDF, Excel, Word, & Cetak)
 * Mengapa aplikasi tidak memanggil pustaka pihak ketiga (`Dompdf`, `PhpSpreadsheet`, `SimpleXLSX`,
 * `PhpWord`) secara langsung di dalam Pengendali (Controller)?
 * 1. Decoupling (Keterpisahan Ketergantungan): Jika di masa depan sekolah mengganti pustaka PDF
 *    atau Excel, cukup ubah class `LayananDokumenKantor` ini tanpa menyentuh puluhan Controller.
 * 2. Kompatibilitas Lintas Lingkungan (Zero-Config XAMPP Fallback): Pustaka Office sering gagal
 *    di laptop siswa karena ekstensi `ext-zip` belum diaktifkan di `php.ini`. Layanan ini
 *    otomatis mengaktifkan fallback `PCLZip` (`ext-zlib`) dan generator OpenXML murni sehingga
 *    ekspor/impor PDF, Excel (`.xlsx`/`.csv`), dan Word (`.docx`) selalu berhasil 100%.
 * 3. Presisi Rupiah & Identitas: Saat mengimpor Excel/CSV, seluruh nilai sel dinormalisasi menjadi
 *    string bersih agar nomor rekening, NIK/NIS (yang diawali angka 0), dan nominal Rupiah tidak
 *    rusak oleh konversi float otomatis.
 */
final class LayananDokumenKantor
{
    /**
     * Memeriksa kesiapan seluruh pustaka dokumen (PDF, Excel, dan Word) pada runtime PHP.
     *
     * @return array{pdf: bool, excel: bool, word: bool} Status kesiapan masing-masing mesin dokumen
     */
    public function periksaKesiapanPustaka(): array
    {
        // [Langkah 1 - Konsep RPL]: Pastikan class utama dari vendor/autoload.php dapat dimuat
        return [
            'pdf' => class_exists(Dompdf::class),
            'excel' => class_exists(SimpleXLSXGen::class)
                && class_exists(SimpleXLSX::class)
                && class_exists(Spreadsheet::class),
            'word' => class_exists(PhpWord::class),
        ];
    }

    /**
     * Membuat biner dokumen PDF dari konten HTML menggunakan mesin Dompdf.
     *
     * @param string $kontenHtml   Untai HTML yang akan dirender ke PDF
     * @param string $ukuranKertas Ukuran halaman standar (contoh: 'A4', 'A5', 'letter')
     * @param string $orientasi    Orientasi kertas ('portrait' atau 'landscape')
     * @return string Byte biner berkas PDF (%PDF-1.x) siap unduh atau tampil
     */
    public function buatPdfDariHtml(
        string $kontenHtml,
        string $ukuranKertas = 'A4',
        string $orientasi = 'portrait'
    ): string {
        if (trim($kontenHtml) === '') {
            throw new InvalidArgumentException('Konten HTML untuk pembuatan PDF tidak boleh kosong.');
        }

        // [Langkah 1 - Konsep RPL]: Konfigurasi Dompdf secara aman (matikan akses jaringan eksternal)
        $opsiDompdf = new Options();
        $opsiDompdf->set('isRemoteEnabled', false);
        $opsiDompdf->set('isHtml5ParserEnabled', true);
        $opsiDompdf->set('defaultFont', 'Helvetica');

        $mesinPdf = new Dompdf($opsiDompdf);
        $htmlLengkap = str_contains(strtolower($kontenHtml), '<html')
            ? $kontenHtml
            : $this->siapkanHtmlCetak('Dokumen Resmi Bank Mini', $kontenHtml, false);

        // [Langkah 2 - Konsep RPL]: Muat HTML, atur kertas, dan render menjadi biner PDF
        $mesinPdf->loadHtml($htmlLengkap, 'UTF-8');
        $orientasiValid = strtolower($orientasi) === 'landscape' ? 'landscape' : 'portrait';
        $mesinPdf->setPaper($ukuranKertas, $orientasiValid);
        $mesinPdf->render();

        $luaranPdf = $mesinPdf->output();
        if (!is_string($luaranPdf) || $luaranPdf === '') {
            throw new RuntimeException('Gagal menghasilkan biner dokumen PDF.');
        }

        return $luaranPdf;
    }

    /**
     * Membuat biner berkas Microsoft Excel (.xlsx) dari daftar judul kolom dan baris data.
     *
     * @param array<int, string>                        $judulKolom Daftar nama kolom header
     * @param array<int, array<int, string|int|float>>  $barisData  Matriks baris data tabel
     * @param string                                    $namaLembar Nama worksheet Excel
     * @return string Byte biner berkas .xlsx (OpenXML)
     */
    public function buatExcelDariTabel(
        array $judulKolom,
        array $barisData,
        string $namaLembar = 'Data Bank Mini'
    ): string {
        if (count($judulKolom) === 0) {
            throw new InvalidArgumentException('Judul kolom tabel Excel tidak boleh kosong.');
        }

        // [Langkah 1 - Konsep RPL]: Susun baris header dan seluruh baris data secara berurutan
        /** @var array<int, array<int, string|int|float>> $kumpulanBaris */
        $kumpulanBaris = [];
        $kumpulanBaris[] = array_values($judulKolom);

        foreach ($barisData as $baris) {
            $barisTerformat = [];
            foreach (array_values($baris) as $nilaiSel) {
                if (is_string($nilaiSel) && preg_match('/^(0\d+|\d+\.\d+)$/', $nilaiSel) === 1) {
                    $barisTerformat[] = "\0" . $nilaiSel;
                } else {
                    $barisTerformat[] = $nilaiSel;
                }
            }
            $kumpulanBaris[] = $barisTerformat;
        }

        // [Langkah 2 - Konsep RPL]: Bangkitkan biner OpenXML (.xlsx) berbasis ext-zlib
        $generatorExcel = SimpleXLSXGen::fromArray($kumpulanBaris, $namaLembar);
        return (string) $generatorExcel;
    }

    /**
     * Membaca dan mengekstrak matriks baris data dari berkas atau biner Microsoft Excel (.xlsx).
     *
     * @param string $sumberExcel      Jalur berkas .xlsx atau byte biner langsung
     * @param bool   $apakahJalurBerkas True jika parameter pertama adalah jalur berkas di disk
     * @return array<int, array<int, string>> Matriks baris dan kolom yang telah dinormalisasi ke string
     */
    public function bacaDataDariExcel(string $sumberExcel, bool $apakahJalurBerkas = true): array
    {
        if (trim($sumberExcel) === '') {
            throw new InvalidArgumentException('Sumber data Excel tidak boleh kosong.');
        }

        // [Langkah 1 - Konsep RPL]: Parse berkas atau byte biner .xlsx menggunakan SimpleXLSX
        if ($apakahJalurBerkas) {
            if (!is_file($sumberExcel)) {
                throw new InvalidArgumentException('Berkas Excel yang akan diimpor tidak ditemukan.');
            }
            $pembacaExcel = SimpleXLSX::parseFile($sumberExcel);
        } else {
            $pembacaExcel = SimpleXLSX::parseData($sumberExcel);
        }

        if ($pembacaExcel === false) {
            throw new RuntimeException('Format berkas Excel (.xlsx) tidak valid atau rusak.');
        }

        // [Langkah 2 - Konsep RPL]: Normalisasi seluruh sel menjadi string bersih (jaga presisi Rupiah & NIS/NIK)
        /** @var array<int, array<int, string>> $hasilBaris */
        $hasilBaris = [];
        $barisMentah = $pembacaExcel->rows();

        foreach ($barisMentah as $baris) {
            if (!is_array($baris)) {
                continue;
            }
            /** @var array<int, string> $barisBersih */
            $barisBersih = [];
            foreach ($baris as $nilaiSel) {
                if (is_scalar($nilaiSel)) {
                    $barisBersih[] = trim((string) $nilaiSel);
                } else {
                    $barisBersih[] = '';
                }
            }
            $hasilBaris[] = $barisBersih;
        }

        return $hasilBaris;
    }

    /**
     * Membuat string CSV (UTF-8 dengan pemisah titik koma ';') yang kompatibel dengan Excel Indonesia.
     *
     * @param array<int, string>                       $judulKolom Daftar nama kolom header
     * @param array<int, array<int, string|int|float>> $barisData  Matriks baris data
     * @return string Konten CSV siap unduh
     */
    public function buatCsvDariTabel(array $judulKolom, array $barisData): string
    {
        if (count($judulKolom) === 0) {
            throw new InvalidArgumentException('Judul kolom CSV tidak boleh kosong.');
        }

        // [Langkah 1 - Konsep RPL]: Gunakan stream memori sementara untuk memformat CSV secara aman
        $aliranMemori = fopen('php://temp', 'r+');
        if ($aliranMemori === false) {
            throw new RuntimeException('Gagal membuka aliran memori untuk pembuatan CSV.');
        }

        fputcsv($aliranMemori, array_values($judulKolom), ';', '"', '\\');
        foreach ($barisData as $baris) {
            $barisString = array_map(
                static fn (string|int|float $nilai): string => (string) $nilai,
                array_values($baris)
            );
            fputcsv($aliranMemori, $barisString, ';', '"', '\\');
        }

        rewind($aliranMemori);
        $isiCsv = (string) stream_get_contents($aliranMemori);
        fclose($aliranMemori);

        return $isiCsv;
    }

    /**
     * Membaca konten teks CSV menjadi matriks baris dan kolom terstandar.
     *
     * @param string $isiCsv Teks CSV yang akan diimpor
     * @return array<int, array<int, string>> Matriks baris data CSV
     */
    public function bacaDataDariCsv(string $isiCsv): array
    {
        $isiBersih = trim($isiCsv);
        if ($isiBersih === '') {
            return [];
        }

        // [Langkah 1 - Konsep RPL]: Deteksi pemisah kolom (; atau ,) secara otomatis dari baris pertama
        $daftarBarisTeks = preg_split('/\r\n|\r|\n/', $isiBersih) ?: [];
        $barisPertama = $daftarBarisTeks[0] ?? '';
        $pemisah = substr_count($barisPertama, ';') >= substr_count($barisPertama, ',') ? ';' : ',';

        /** @var array<int, array<int, string>> $hasilData */
        $hasilData = [];
        foreach ($daftarBarisTeks as $barisTeks) {
            if (trim($barisTeks) === '') {
                continue;
            }
            $kolom = str_getcsv($barisTeks, $pemisah, '"', '\\');
            $barisString = [];
            foreach ($kolom as $nilai) {
                $barisString[] = trim((string) ($nilai ?? ''));
            }
            $hasilData[] = $barisString;
        }

        return $hasilData;
    }

    /**
     * Membuat biner dokumen Microsoft Word (.docx) menggunakan PhpWord dengan fallback otomatis PCLZip.
     *
     * @param string                                   $judulDokumen    Judul utama pada kepala dokumen Word
     * @param array<int, string>                       $daftarParagraf  Daftar paragraf isi surat/laporan
     * @param array<int, string>                       $judulKolomTabel Opsional daftar judul kolom tabel
     * @param array<int, array<int, string|int|float>> $barisTabel      Opsional daftar baris data tabel
     * @return string Byte biner berkas .docx (OpenXML Word2007)
     */
    public function buatDokumenWord(
        string $judulDokumen,
        array $daftarParagraf,
        array $judulKolomTabel = [],
        array $barisTabel = []
    ): string {
        if (trim($judulDokumen) === '') {
            throw new InvalidArgumentException('Judul dokumen Word tidak boleh kosong.');
        }

        // [Langkah 1 - Konsep RPL]: Gunakan PCLZip otomatis jika ekstensi ZipArchive belum aktif di php.ini
        if (!class_exists('ZipArchive')) {
            PengaturanWord::setZipClass(PengaturanWord::PCLZIP);
        }

        $dokumenWord = new PhpWord();
        $dokumenWord->getDocInfo()->setCreator('Sistem Informasi Bank Mini Sekolah (IDR)');
        $dokumenWord->getDocInfo()->setTitle($judulDokumen);

        // [Langkah 2 - Konsep RPL]: Susun bagian halaman, kop surat, dan paragraf isi
        $bagianHalaman = $dokumenWord->addSection();
        $bagianHalaman->addText(
            'BANK MINI SEKOLAH (MATA UANG RUPIAH - IDR)',
            ['bold' => true, 'size' => 12, 'color' => '0F172A']
        );
        $bagianHalaman->addText(
            $judulDokumen,
            ['bold' => true, 'size' => 14, 'color' => '1E3A8A']
        );
        $bagianHalaman->addTextBreak(1);

        foreach ($daftarParagraf as $teksParagraf) {
            $bagianHalaman->addText($teksParagraf, ['size' => 11]);
        }

        // [Langkah 3 - Konsep RPL]: Tambahkan tabel data jika diberikan
        if (count($judulKolomTabel) > 0) {
            $bagianHalaman->addTextBreak(1);
            $tabelWord = $bagianHalaman->addTable([
                'borderSize' => 6,
                'borderColor' => 'CBD5E1',
                'cellMargin' => 80,
            ]);

            $tabelWord->addRow();
            foreach ($judulKolomTabel as $teksHeader) {
                $tabelWord->addCell(2400)->addText((string) $teksHeader, ['bold' => true, 'size' => 10]);
            }

            foreach ($barisTabel as $baris) {
                $tabelWord->addRow();
                foreach ($baris as $nilaiSel) {
                    $tabelWord->addCell(2400)->addText((string) $nilaiSel, ['size' => 10]);
                }
            }
        }

        // [Langkah 4 - Konsep RPL]: Simpan ke berkas sementara dan kembalikan byte binernya
        $berkasSementara = tempnam(sys_get_temp_dir(), 'bm_word_');
        if ($berkasSementara === false) {
            throw new RuntimeException('Gagal membuat berkas sementara untuk dokumen Word.');
        }

        $levelErrorSebelumnya = error_reporting();
        try {
            error_reporting($levelErrorSebelumnya & ~E_DEPRECATED);
            $penulisWord = PabrikIoWord::createWriter($dokumenWord, 'Word2007');
            $penulisWord->save($berkasSementara);
            $binerDocx = (string) file_get_contents($berkasSementara);
        } finally {
            error_reporting($levelErrorSebelumnya);
            if (is_file($berkasSementara)) {
                @unlink($berkasSementara);
            }
        }

        if ($binerDocx === '') {
            throw new RuntimeException('Gagal menghasilkan biner dokumen Word (.docx).');
        }

        return $binerDocx;
    }

    /**
     * Membungkus konten HTML dengan tata letak Kop Surat Bank Mini dan CSS @media print siap cetak.
     *
     * @param string $judulDokumen    Judul dokumen pada header cetak
     * @param string $kontenHtmlUtama Isi HTML utama (tabel mutasi, slip transaksi, atau surat)
     * @param bool   $cetakOtomatis   True jika ingin memicu dialog window.print() saat halaman dibuka
     * @return string Dokumen HTML5 lengkap siap cetak atau konversi PDF
     */
    public function siapkanHtmlCetak(
        string $judulDokumen,
        string $kontenHtmlUtama,
        bool $cetakOtomatis = false
    ): string {
        $judulAman = amankanHtml($judulDokumen);
        $skripCetak = $cetakOtomatis
            ? '<script>window.addEventListener("load", function () { window.print(); });</script>'
            : '';

        // [Langkah 1 - Konsep RPL]: Susun template HTML5 mandiri dengan gaya tabel & kop surat cetak
        return '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">'
            . '<title>' . $judulAman . ' - Bank Mini Sekolah</title>'
            . '<style>'
            . 'body { font-family: Helvetica, Arial, sans-serif; color: #0f172a; font-size: 12px; margin: 24px; }'
            . '.kop-surat { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 18px; }'
            . '.kop-surat h1 { font-size: 16px; margin: 0 0 4px 0; text-transform: uppercase; }'
            . '.kop-surat p { font-size: 11px; margin: 0; color: #475569; }'
            . '.judul-dokumen { font-size: 14px; font-weight: bold; margin-bottom: 12px; }'
            . 'table { width: 100%; border-collapse: collapse; margin-top: 8px; }'
            . 'th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }'
            . 'th { background-color: #f1f5f9; font-weight: bold; }'
            . '@media print { .sembunyikan-cetak { display: none !important; } body { margin: 0; } }'
            . '</style></head><body>'
            . '<div class="kop-surat">'
            . '<h1>Sistem Informasi Bank Mini Sekolah (IDR)</h1>'
            . '<p>Laboratorium Praktik Perbankan &amp; Rekayasa Perangkat Lunak (SMK RPL)</p>'
            . '</div>'
            . '<div class="judul-dokumen">' . $judulAman . '</div>'
            . '<div class="isi-dokumen">' . $kontenHtmlUtama . '</div>'
            . $skripCetak
            . '</body></html>';
    }
}
