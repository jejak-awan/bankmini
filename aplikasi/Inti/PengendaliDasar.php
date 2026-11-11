<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

use RuntimeException;

/**
 * PengendaliDasar
 *
 * @konsep_rpl Pewarisan (Inheritance), Separation of Concerns (SoC), & Pola PRG
 * Seluruh class `Pengendali` mewarisi (`extends`) class abstrak ini agar memiliki:
 * 1. `tampilkanTampilan()`: Mengirimkan variabel ke file HTML di `aplikasi/Tampilan/`
 *    sekaligus menyuntikkan pesan kilat dan token CSRF secara konsisten.
 * 2. `alihkan()`: Melakukan redirect HTTP `303 See Other` setelah `POST` (pola
 *    Post-Redirect-Get / PRG) agar menekan tombol Refresh (F5) di browser tidak
 *    mengirim ulang transaksi uang.
 */
abstract class PengendaliDasar
{
    /**
     * Merender file Tampilan (View) di dalam tata letak utama atau secara mandiri.
     *
     * @param string               $namaTampilan       Jalur relatif di dalam aplikasi/Tampilan/ (contoh: 'dasbor/indeks')
     * @param array<string, mixed> $dataTampilan       Variabel yang dikirim ke Tampilan
     * @param bool                 $gunakanTataLetak   True jika dibungkus kepala.php, bilah_sisi.php, dan kaki.php
     * @return void
     *
     * @throws RuntimeException Jika file tampilan tidak ditemukan
     */
    protected function tampilkanTampilan(
        string $namaTampilan,
        array $dataTampilan = [],
        bool $gunakanTataLetak = true
    ): void {
        $folderTampilan = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Tampilan';
        $jalurFileTampilan = $folderTampilan
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $namaTampilan)
            . '.php';

        if (!is_file($jalurFileTampilan)) {
            throw new RuntimeException("File tampilan tidak ditemukan: {$namaTampilan}");
        }

        $pesanKilat = Sesi::ambilPesanKilat();
        $tokenCsrfAktif = TokenCsrf::ambilToken();

        extract($dataTampilan, EXTR_SKIP);

        if ($gunakanTataLetak) {
            require $folderTampilan . DIRECTORY_SEPARATOR . 'tata_letak' . DIRECTORY_SEPARATOR . 'kepala.php';
            require $folderTampilan . DIRECTORY_SEPARATOR . 'tata_letak' . DIRECTORY_SEPARATOR . 'bilah_sisi.php';
            require $jalurFileTampilan;
            require $folderTampilan . DIRECTORY_SEPARATOR . 'tata_letak' . DIRECTORY_SEPARATOR . 'kaki.php';
            return;
        }

        require $jalurFileTampilan;
    }

    /**
     * Mengalihkan (redirect HTTP 303 See Other) ke jalur tujuan sesuai pola Post-Redirect-Get (PRG).
     *
     * @param string $jalurTujuan Jalur relatif tujuan (contoh: 'transaksi/setor')
     * @return void
     */
    protected function alihkan(string $jalurTujuan): void
    {
        TokenCsrf::rotasiToken();
        header('Location: ' . urlDasar($jalurTujuan), true, 303);
        exit;
    }

    /**
     * Memvalidasi token CSRF pada request POST; melempar pengecualian jika tidak sah.
     *
     * @return void
     *
     * @throws RuntimeException Jika token CSRF kosong atau tidak cocok
     */
    protected function wajibValidasiCsrf(): void
    {
        $tokenMasukan = isset($_POST['token_csrf']) ? (string) $_POST['token_csrf'] : null;
        if (!TokenCsrf::validasiToken($tokenMasukan)) {
            throw new RuntimeException('Sesi formulir tidak valid atau telah kedaluwarsa (CSRF). Silakan ulangi.');
        }
    }
}

