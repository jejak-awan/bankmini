<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\MiddlewareAutentikasi;
use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Model\ModelMutasi;

/**
 * PengendaliLaporan
 *
 * Menampilkan laporan buku besar mutasi transaksi (Immutable Ledger) dalam Rupiah (IDR)
 * beserta filter rentang tanggal.
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
