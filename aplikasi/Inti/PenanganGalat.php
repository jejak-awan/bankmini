<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

use ErrorException;
use Throwable;

/**
 * PenanganGalat
 *
 * @konsep_rpl Penanganan Galat Terpusat (Global Error Handler), Mode Debug, & Header Keamanan HTTP
 * Menjaga keamanan sekaligus kenyamanan pengguna saat terjadi kondisi tidak normal (403, 404, 500, 503):
 * 1. Saat `mode_debug = false` (Produksi), detail SQL, stack trace, dan path server disembunyikan,
 *    digantikan halaman galat Design System yang ramah beserta Correlation ID untuk pelaporan.
 * 2. Saat `mode_debug = true` (Pengembangan RPL), rincian kelas pengecualian, berkas, dan trace
 *    ditampilkan agar siswa mudah mendiagnosis sumber bug secara terstruktur.
 * 3. Memasang Header Keamanan HTTP (CSP ketat `script-src 'self'`, X-Frame-Options, nosniff) dan
 *    mendukung sakelar Mode Pemeliharaan (`penyimpanan/pemeliharaan.flag` -> HTTP 503).
 */
final class PenanganGalat
{
    /**
     * Mengirim header keamanan HTTP standar OWASP termasuk Content-Security-Policy (CSP).
     *
     * @param bool $apakahJson True jika respons berupa JSON API (misal /kesehatan)
     * @return void
     */
    public static function kirimHeaderKeamanan(bool $apakahJson = false): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');

        if (!$apakahJson) {
            $kebijakanCsp = "default-src 'self'; "
                . "script-src 'self'; "
                . "style-src 'self' 'unsafe-inline'; "
                . "img-src 'self' data:; "
                . "font-src 'self' data:; "
                . "object-src 'none'; "
                . "base-uri 'self'; "
                . "form-action 'self'; "
                . "frame-ancestors 'self'";
            header('Content-Security-Policy: ' . $kebijakanCsp);
        }
    }

    /**
     * Memeriksa apakah berkas bendera pemeliharaan (`penyimpanan/pemeliharaan.flag`) sedang aktif.
     *
     * @param string|null $jalurBendera Jalur khusus untuk pengujian atau null memakai default
     * @return bool True jika sistem dalam mode pemeliharaan
     */
    public static function apakahModePemeliharaan(?string $jalurBendera = null): bool
    {
        $jalurTarget = $jalurBendera ?? (
            dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'penyimpanan'
            . DIRECTORY_SEPARATOR . 'pemeliharaan.flag'
        );

        return is_file($jalurTarget);
    }

    /**
     * Menghentikan permintaan dengan HTTP 503 apabila mode pemeliharaan sedang aktif,
     * kecuali untuk endpoint pemantauan kesehatan (`/kesehatan`).
     *
     * @param string      $jalurUri     Jalur URI bersih saat ini
     * @param string|null $jalurBendera Jalur khusus untuk pengujian atau null
     * @return void
     *
     * @throws PengecualianHttp HTTP 503 jika mode pemeliharaan aktif
     */
    public static function periksaModePemeliharaan(string $jalurUri = '', ?string $jalurBendera = null): void
    {
        $jalurBersih = '/' . trim($jalurUri, '/');
        if ($jalurBersih === '/kesehatan' || str_ends_with($jalurBersih, '/kesehatan')) {
            return;
        }

        if (self::apakahModePemeliharaan($jalurBendera)) {
            throw new PengecualianHttp(
                503,
                'Sistem Sedang Dalam Pemeliharaan',
                'Layanan Bank Mini sedang diperbarui sementara oleh tim teknis. Silakan coba beberapa menit lagi.'
            );
        }
    }

    /**
     * Mendaftarkan penangan error PHP dan pengecualian global di Front Controller.
     *
     * @return void
     */
    public static function pasang(): void
    {
        set_error_handler([self::class, 'tanganiErrorPhp']);
        set_exception_handler([self::class, 'tanganiPengecualian']);
    }

    /**
     * Mengonversi warning/notice PHP menjadi ErrorException agar tertangani terpusat dan terlog.
     *
     * @param int    $tingkat Tingkat error PHP
     * @param string $pesan   Pesan error
     * @param string $berkas  Lokasi berkas
     * @param int    $baris   Nomor baris
     * @return bool False jika error ditekan dengan operator @
     *
     * @throws ErrorException Jika tingkat error aktif di error_reporting()
     */
    public static function tanganiErrorPhp(int $tingkat, string $pesan, string $berkas, int $baris): bool
    {
        if ((error_reporting() & $tingkat) === 0) {
            return false;
        }

        throw new ErrorException($pesan, 0, $tingkat, $berkas, $baris);
    }

    /**
     * Menyusun metadata halaman galat berdasarkan pengecualian dan status `mode_debug`.
     *
     * @param Throwable $pengecualian Pengecualian yang tertangkap
     * @param bool      $modeDebug    Apakah rincian stack trace diizinkan tampil
     * @return array<string, mixed> Data terstruktur untuk dirender halaman galat
     */
    public static function bangunDataTampilanGalat(Throwable $pengecualian, bool $modeDebug): array
    {
        $kodeHttp = $pengecualian instanceof PengecualianHttp
            ? $pengecualian->ambilKodeHttp()
            : 500;

        $daftarKodeDidukung = [403, 404, 500, 503];
        if (!in_array($kodeHttp, $daftarKodeDidukung, true)) {
            $kodeHttp = 500;
        }

        $judulBawaan = match ($kodeHttp) {
            403 => 'Akses Ditolak (403)',
            404 => 'Halaman Tidak Ditemukan (404)',
            503 => 'Pemeliharaan Terjadwal (503)',
            default => 'Gangguan Sistem Internal (500)',
        };

        $judul = $pengecualian instanceof PengecualianHttp
            ? $pengecualian->ambilJudul()
            : $judulBawaan;

        $pesanRamah = match ($kodeHttp) {
            403 => $pengecualian->getMessage() !== ''
                ? $pengecualian->getMessage()
                : 'Peran akun Anda tidak memiliki hak akses untuk membuka halaman atau operasi ini.',
            404 => 'Alamat halaman atau rute yang Anda tuju tidak tersedia di sistem Bank Mini.',
            503 => $pengecualian->getMessage() !== ''
                ? $pengecualian->getMessage()
                : 'Sistem sedang menjalani pemeliharaan terjadwal. Silakan coba beberapa saat lagi.',
            default => 'Terjadi kendala internal pada server. Kejadian ini telah dicatat ke log sistem dengan ID korelasi di bawah.',
        };

        $detailDebug = null;
        if ($modeDebug) {
            $detailDebug = [
                'kelas'  => $pengecualian::class,
                'pesan'  => $pengecualian->getMessage(),
                'berkas' => $pengecualian->getFile(),
                'baris'  => $pengecualian->getLine(),
                'jejak'  => $pengecualian->getTraceAsString(),
            ];
        }

        return [
            'kodeHttp'     => $kodeHttp,
            'judulHalaman' => $judul . ' — Sistem Informasi Bank Mini',
            'judulGalat'   => $judul,
            'pesanRamah'   => $pesanRamah,
            'idKorelasi'   => PencatatLog::ambilIdKorelasi(),
            'modeDebug'    => $modeDebug,
            'detailDebug'  => $detailDebug,
        ];
    }

    /**
     * Menangani pengecualian tak tertangkap, mencatatnya ke log harian, dan merender halaman galat.
     *
     * @param Throwable $pengecualian Pengecualian tak tertangkap
     * @return void
     */
    public static function tanganiPengecualian(Throwable $pengecualian): void
    {
        $konfigurasi = self::muatKonfigurasiAplikasi();
        $modeDebug = (bool) ($konfigurasi['mode_debug'] ?? false);
        $versiAplikasi = (string) ($konfigurasi['versi'] ?? '1.0.0');
        $namaAplikasi = (string) ($konfigurasi['nama_aplikasi'] ?? 'Sistem Informasi Bank Mini');

        $dataGalat = self::bangunDataTampilanGalat($pengecualian, $modeDebug);
        $kodeHttp = (int) $dataGalat['kodeHttp'];

        $tingkatLog = match ($kodeHttp) {
            403 => PencatatLog::TINGKAT_KEAMANAN,
            404 => PencatatLog::TINGKAT_PERINGATAN,
            503 => PencatatLog::TINGKAT_INFO,
            default => PencatatLog::TINGKAT_GALAT,
        };

        PencatatLog::tulis($tingkatLog, $pengecualian->getMessage(), [
            'kode_http' => $kodeHttp,
            'kelas'     => $pengecualian::class,
            'metode'    => (string) ($_SERVER['REQUEST_METHOD'] ?? 'CLI'),
            'uri'       => (string) ($_SERVER['REQUEST_URI'] ?? '/'),
            'berkas'    => basename($pengecualian->getFile()),
            'baris'     => $pengecualian->getLine(),
        ]);

        if (!headers_sent()) {
            self::kirimHeaderKeamanan();
            http_response_code($kodeHttp);
        }

        self::renderTampilanGalat($dataGalat, $versiAplikasi, $namaAplikasi);
    }

    /**
     * Merender templat halaman galat Design System secara aman dengan cadangan darurat.
     *
     * @param array<string, mixed> $dataGalat     Metadata galat
     * @param string               $versiAplikasi Versi dari SoT VERSION
     * @param string               $namaAplikasi  Nama aplikasi
     * @return void
     */
    private static function renderTampilanGalat(
        array $dataGalat,
        string $versiAplikasi,
        string $namaAplikasi
    ): void {
        $folderTampilan = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Tampilan';
        $berkasGalat = $folderTampilan
            . DIRECTORY_SEPARATOR . 'galat'
            . DIRECTORY_SEPARATOR . 'halaman_galat.php';
        $kepalaPublik = $folderTampilan
            . DIRECTORY_SEPARATOR . 'tata_letak'
            . DIRECTORY_SEPARATOR . 'kepala_publik.php';
        $kakiPublik = $folderTampilan
            . DIRECTORY_SEPARATOR . 'tata_letak'
            . DIRECTORY_SEPARATOR . 'kaki_publik.php';

        if (!is_file($berkasGalat) || !is_file($kepalaPublik) || !is_file($kakiPublik)) {
            $kode = (int) ($dataGalat['kodeHttp'] ?? 500);
            $judul = htmlspecialchars((string) ($dataGalat['judulGalat'] ?? 'Galat'), ENT_QUOTES, 'UTF-8');
            $pesan = htmlspecialchars((string) ($dataGalat['pesanRamah'] ?? ''), ENT_QUOTES, 'UTF-8');
            echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>{$judul}</title></head>"
                . "<body><h1>{$kode} — {$judul}</h1><p>{$pesan}</p></body></html>";
            return;
        }

        $judulHalaman = (string) ($dataGalat['judulHalaman'] ?? 'Galat Sistem');
        $kelasBody = 'd-flex flex-column min-vh-100 bg-light';
        $kodeHttp = (int) ($dataGalat['kodeHttp'] ?? 500);
        $judulGalat = (string) ($dataGalat['judulGalat'] ?? 'Galat Sistem');
        $pesanRamah = (string) ($dataGalat['pesanRamah'] ?? '');
        $idKorelasi = (string) ($dataGalat['idKorelasi'] ?? '');
        $modeDebug = (bool) ($dataGalat['modeDebug'] ?? false);
        $detailDebug = $dataGalat['detailDebug'] ?? null;
        $sudahMasuk = MiddlewareAutentikasi::sudahMasuk();
        $tautanUtama = $sudahMasuk ? urlDasar('dasbor') : urlDasar('portal');
        $labelUtama = $sudahMasuk ? 'Kembali ke Dasbor' : 'Ke Portal Publik';

        require $kepalaPublik;
        require $berkasGalat;
        require $kakiPublik;
    }

    /**
     * Memuat konfigurasi aplikasi secara aman sebagai fallback ketika error handler aktif.
     *
     * @return array<string, mixed> Konfigurasi aplikasi
     */
    private static function muatKonfigurasiAplikasi(): array
    {
        $jalurKonfig = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'konfigurasi'
            . DIRECTORY_SEPARATOR . 'aplikasi.php';

        if (!is_file($jalurKonfig)) {
            return ['mode_debug' => false, 'versi' => '1.0.0', 'nama_aplikasi' => 'Bank Mini'];
        }

        /** @var array<string, mixed> $konfig */
        $konfig = require $jalurKonfig;

        return $konfig;
    }
}
