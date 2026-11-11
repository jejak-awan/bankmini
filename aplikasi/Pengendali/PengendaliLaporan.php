<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\MiddlewareAutentikasi;
use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Model\ModelMutasi;

/**
 * PengendaliLaporan
 *
 * @konsep_rpl Pelaporan Buku Besar (Ledger Reporting) & Filter Parameter GET
 * Menggunakan metode HTTP `GET` untuk filter rentang tanggal (`tanggal_mulai` & `tanggal_akhir`)
 * karena proses pencarian/pelaporan tidak mengubah data di server (bersifat Idempotent).
 */
class PengendaliLaporan extends PengendaliDasar
{
    /**
     * Menampilkan halaman riwayat mutasi transaksi (GET /laporan/mutasi).
     *
     * @return void
     */
    public function mutasi(): void
    {
        MiddlewareAutentikasi::wajibMasuk();

        $tanggalMulai = isset($_GET['tanggal_mulai']) ? trim((string) $_GET['tanggal_mulai']) : '';
        $tanggalAkhir = isset($_GET['tanggal_akhir']) ? trim((string) $_GET['tanggal_akhir']) : '';

        $modelMutasi = new ModelMutasi();
        $daftarMutasi = $modelMutasi->ambilMutasiBerdasarkanTanggal(
            $tanggalMulai !== '' ? $tanggalMulai : null,
            $tanggalAkhir !== '' ? $tanggalAkhir : null,
            100
        );

        $this->tampilkanTampilan('laporan/mutasi', [
            'judulHalaman' => 'Laporan Mutasi Transaksi (Rupiah IDR)',
            'daftarMutasi' => $daftarMutasi,
            'tanggalMulai' => $tanggalMulai,
            'tanggalAkhir' => $tanggalAkhir,
        ]);
    }
}
