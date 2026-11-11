<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Model\ModelProduk;
use Throwable;

/**
 * PengendaliPublik
 *
 * @konsep_rpl Pemisahan Tata Letak Publik & Dasbor (Public vs Admin Shell Isolation)
 * Menampilkan halaman Portal Informasi Publik Bank Mini Sekolah (`GET /portal`)
 * dengan tata letak `'publik'` agar pengunjung umum tidak perlu mengunduh
 * stylesheet sidebar, modal transaksi, maupun skrip dasbor admin/teller.
 */
class PengendaliPublik extends PengendaliDasar
{
    /**
     * Menampilkan Portal Informasi Publik Bank Mini Sekolah (GET /portal).
     *
     * @return void
     */
    public function portal(): void
    {
        // [Langkah 1 - Konsep RPL]: Ambil daftar produk tabungan aktif untuk ditampilkan ke siswa/calon nasabah
        $daftarProdukAktif = [];
        try {
            $modelProduk = new ModelProduk();
            foreach ($modelProduk->ambilSemuaProduk() as $barisProduk) {
                if (strtolower((string) ($barisProduk['status'] ?? '')) === 'aktif') {
                    $daftarProdukAktif[] = $barisProduk;
                }
            }
        } catch (Throwable) {
            // Abaikan secara aman jika database belum diinisialisasi agar portal informasi tetap terbuka
            $daftarProdukAktif = [];
        }

        // [Langkah 2 - Konsep RPL]: Render memakai kerangka 'publik' (hanya token-desain + komponen-ui + publik + inti-ui)
        $this->tampilkanTampilan(
            'publik/portal',
            [
                'judulHalaman' => 'Portal Informasi Publik — Bank Mini Sekolah (IDR)',
                'kelasBody' => 'd-flex flex-column min-vh-100 bg-light',
                'daftarProdukAktif' => $daftarProdukAktif,
            ],
            'publik'
        );
    }
}
