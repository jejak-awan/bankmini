<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

use RuntimeException;
use Throwable;

/**
 * PengecualianHttp
 *
 * @konsep_rpl Pengecualian Terstruktur (Typed HTTP Exception) untuk Status 403/404/500/503
 * Memisahkan sinyal status HTTP (misal 403 Akses Ditolak dari RBAC atau 404 Rute Tidak Ada dari
 * Perute) dengan pesan teknis internal agar `PenanganGalat` dapat menampilkan halaman galat
 * yang ramah pengguna dan sesuai dengan Design System aplikasi.
 */
final class PengecualianHttp extends RuntimeException
{
    private int $kodeHttp;
    private string $judul;

    /**
     * Konstruktor PengecualianHttp.
     *
     * @param int            $kodeHttp    Kode status HTTP (403, 404, 500, 503)
     * @param string         $judul       Judul ringkas halaman galat
     * @param string         $pesan       Pesan ramah untuk pengguna
     * @param Throwable|null $sebelumnya  Pengecualian penyebab sebelumnya bila ada
     */
    public function __construct(
        int $kodeHttp,
        string $judul,
        string $pesan = '',
        ?Throwable $sebelumnya = null
    ) {
        parent::__construct($pesan !== '' ? $pesan : $judul, $kodeHttp, $sebelumnya);
        $this->kodeHttp = $kodeHttp;
        $this->judul = $judul;
    }

    /**
     * Mengambil kode status HTTP terkait pengecualian ini.
     *
     * @return int Kode status HTTP
     */
    public function ambilKodeHttp(): int
    {
        return $this->kodeHttp;
    }

    /**
     * Mengambil judul ringkas galat untuk dirender pada halaman Tampilan.
     *
     * @return string Judul galat
     */
    public function ambilJudul(): string
    {
        return $this->judul;
    }
}
