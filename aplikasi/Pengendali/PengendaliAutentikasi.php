<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\MiddlewareAutentikasi;
use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Inti\Sesi;
use Aplikasi\Layanan\LayananAutentikasi;
use Aplikasi\Model\ModelLogAudit;
use Aplikasi\Model\ModelPengguna;
use Throwable;

/**
 * PengendaliAutentikasi
 *
 * @konsep_rpl Lapisan Pengendali HTTP (Controller), Validasi CSRF, & Alur Masuk/Keluar
 * Bertugas menerima input `$_POST` dari formulir login/logout, memvalidasi token CSRF,
 * mendelegasikan verifikasi ke `LayananAutentikasi`, lalu mengarahkan pengguna dengan
 * pola Post-Redirect-Get (PRG).
 */
class PengendaliAutentikasi extends PengendaliDasar
{
    private LayananAutentikasi $layananAutentikasi;

    /**
     * Konstruktor PengendaliAutentikasi.
     */
    public function __construct()
    {
        $this->layananAutentikasi = new LayananAutentikasi(
            new ModelPengguna(),
            new ModelLogAudit()
        );
    }

    /**
     * Menampilkan halaman formulir masuk (GET /masuk).
     *
     * @return void
     */
    public function tampilkanHalamanMasuk(): void
    {
        if (MiddlewareAutentikasi::sudahMasuk()) {
            $this->alihkan('dasbor');
        }

        $this->tampilkanTampilan(
            'autentikasi/masuk',
            ['judulHalaman' => 'Masuk Sistem Bank Mini'],
            false
        );
    }

    /**
     * Memproses pengiriman formulir masuk (POST /masuk) dengan pola PRG.
     *
     * @return void
     */
    public function prosesMasuk(): void
    {
        try {
            // [Langkah 1 - Konsep RPL]: Tolak permintaan POST tanpa token CSRF valid untuk mencegah pemalsuan request.
            $this->wajibValidasiCsrf();

            // [Langkah 2 - Konsep RPL]: Delegasikan validasi kredensial dan pencatatan sesi ke lapisan Layanan.
            $namaPengguna = (string) ($_POST['nama_pengguna'] ?? '');
            $kataSandi = (string) ($_POST['kata_sandi'] ?? '');
            $alamatIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

            $profil = $this->layananAutentikasi->prosesMasuk($namaPengguna, $kataSandi, $alamatIp);
            Sesi::tetapkanPesanKilat('sukses', 'Selamat datang, ' . $profil['nama_lengkap'] . '!');
            $this->alihkan('dasbor');
        } catch (Throwable $pengecualian) {
            Sesi::tetapkanPesanKilat('kesalahan', $pengecualian->getMessage());
            $this->alihkan('masuk');
        }
    }

    /**
     * Memproses keluar sesi pengguna (POST /keluar).
     *
     * @return void
     */
    public function prosesKeluar(): void
    {
        $this->wajibValidasiCsrf();

        $idPengguna = Sesi::ambil('id_pengguna');
        $alamatIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        $this->layananAutentikasi->prosesKeluar(
            is_int($idPengguna) ? $idPengguna : null,
            $alamatIp
        );

        Sesi::mulaiSesiAman();
        Sesi::tetapkanPesanKilat('sukses', 'Anda telah berhasil keluar dari sistem.');
        $this->alihkan('masuk');
    }
}
