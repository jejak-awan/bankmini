<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\MiddlewareAutentikasi;
use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Inti\Sesi;
use Aplikasi\Model\ModelNasabah;
use Throwable;

/**
 * PengendaliNasabah
 *
 * @konsep_rpl Pengendali CRUD Master Data & Pola Post-Redirect-Get (PRG)
 * Menangani tampilan daftar nasabah (`GET /nasabah`) dan penyimpanan data nasabah
 * baru (`POST /nasabah/tambah`) dengan pemeriksaan peran (`admin` / `teller`) dan CSRF.
 */
class PengendaliNasabah extends PengendaliDasar
{
    /**
     * Menampilkan daftar nasabah dan formulir registrasi (GET /nasabah).
     *
     * @return void
     */
    public function indeks(): void
    {
        MiddlewareAutentikasi::wajibMasuk();

        $modelNasabah = new ModelNasabah();
        $this->tampilkanTampilan('nasabah/indeks', [
            'judulHalaman'  => 'Manajemen Data Nasabah',
            'daftarNasabah' => $modelNasabah->ambilSemuaNasabah(),
        ]);
    }

    /**
     * Memproses penyimpanan nasabah baru (POST /nasabah/tambah) dengan pola PRG.
     *
     * @return void
     */
    public function simpanNasabah(): void
    {
        MiddlewareAutentikasi::wajibPeran(['admin', 'teller']);

        try {
            $this->wajibValidasiCsrf();

            $modelNasabah = new ModelNasabah();
            $modelNasabah->tambahNasabahBaru([
                'nomor_identitas' => trim((string) ($_POST['nomor_identitas'] ?? '')),
                'nama_lengkap'    => trim((string) ($_POST['nama_lengkap'] ?? '')),
                'jenis_nasabah'   => (string) ($_POST['jenis_nasabah'] ?? 'siswa'),
                'kelas_atau_unit' => trim((string) ($_POST['kelas_atau_unit'] ?? '')),
                'nomor_telepon'   => trim((string) ($_POST['nomor_telepon'] ?? '')),
                'alamat'          => trim((string) ($_POST['alamat'] ?? '')),
                'status'          => 'aktif',
            ]);

            Sesi::tetapkanPesanKilat('sukses', 'Data nasabah baru berhasil ditambahkan.');
        } catch (Throwable $pengecualian) {
            Sesi::tetapkanPesanKilat('kesalahan', 'Gagal menambah nasabah: ' . $pengecualian->getMessage());
        }

        $this->alihkan('nasabah');
    }
}

