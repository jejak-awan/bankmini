<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\MiddlewareAutentikasi;
use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Inti\Sesi;
use Aplikasi\Layanan\LayananDokumentasi;
use Throwable;

/**
 * PengendaliDokumentasi
 *
 * @konsep_rpl Pusat Dokumentasi Terindeks di Browser & Pencarian Materi RPL
 * Menangani rute `GET /dokumentasi` untuk menampilkan katalog dokumentasi
 * terindeks (Panduan Pengguna, Panduan Dev, DFD/ERD, Glosarium, Modul Praktikum,
 * ADR, & Work Log) langsung di dalam antarmuka web Bank Mini.
 */
class PengendaliDokumentasi extends PengendaliDasar
{
    /**
     * Menampilkan halaman pembaca dokumentasi terindeks dan hasil pencarian (GET /dokumentasi).
     *
     * @return void
     */
    public function indeks(): void
    {
        // [Langkah 1 - Konsep RPL]: Pastikan hanya pengguna terautentikasi yang dapat membuka dokumentasi internal.
        MiddlewareAutentikasi::wajibMasuk();

        // [Langkah 2 - Konsep RPL]: Ambil parameter dokumen dan kata kunci pencarian dari query string GET.
        $dokumenDiminta = isset($_GET['dok']) ? trim((string) $_GET['dok']) : 'README.md';
        $kataKunciCari = isset($_GET['cari']) ? trim((string) $_GET['cari']) : '';

        $layananDokumentasi = new LayananDokumentasi();
        $katalogDokumentasi = $layananDokumentasi->ambilKatalogTerindeks();
        $hasilPencarian = $kataKunciCari !== ''
            ? $layananDokumentasi->cariDokumen($kataKunciCari)
            : [];

        try {
            $dokumenAktif = $layananDokumentasi->bacaDokumenAman($dokumenDiminta);
        } catch (Throwable $pengecualian) {
            Sesi::tetapkanPesanKilat('kesalahan', $pengecualian->getMessage());
            $dokumenAktif = $layananDokumentasi->bacaDokumenAman('README.md');
        }

        $this->tampilkanTampilan('dokumentasi/indeks', [
            'judulHalaman'       => 'Pusat Dokumentasi & Materi RPL — ' . $dokumenAktif['judul'],
            'katalogDokumentasi' => $katalogDokumentasi,
            'dokumenAktif'       => $dokumenAktif,
            'kataKunciCari'      => $kataKunciCari,
            'hasilPencarian'     => $hasilPencarian,
        ]);
    }
}
