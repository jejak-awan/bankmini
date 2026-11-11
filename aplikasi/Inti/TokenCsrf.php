<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

/**
 * TokenCsrf
 *
 * @konsep_rpl Pencegahan Serangan Cross-Site Request Forgery (CSRF) & Timing Attack
 * Mengapa setiap formulir POST wajib menyertakan `token_csrf`?
 * 1. Memastikan formulir benar-benar dikirim oleh petugas dari halaman resmi Bank Mini,
 *    bukan dipicu secara diam-diam oleh situs web pihak ketiga.
 * 2. Menggunakan `hash_equals()` untuk membandingkan token dalam waktu konstan sehingga
 *    penyerang tidak dapat menebak karakter token melalui pengukuran waktu (Timing Attack).
 */
final class TokenCsrf
{
    private const KUNCI_SESI_CSRF = 'token_csrf_bankmini';

    /**
     * Mengambil token CSRF aktif atau membuat token baru jika belum tersedia.
     *
     * @return string Token CSRF heksadesimal 64 karakter
     */
    public static function ambilToken(): string
    {
        if (empty($_SESSION[self::KUNCI_SESI_CSRF]) || !is_string($_SESSION[self::KUNCI_SESI_CSRF])) {
            $_SESSION[self::KUNCI_SESI_CSRF] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KUNCI_SESI_CSRF];
    }

    /**
     * Memvalidasi token CSRF yang dikirim dari form POST menggunakan hash_equals.
     *
     * @param string|null $tokenMasukan Token dari input form ($_POST['token_csrf'])
     * @return bool True jika token valid dan cocok
     */
    public static function validasiToken(?string $tokenMasukan): bool
    {
        if ($tokenMasukan === null || $tokenMasukan === '') {
            return false;
        }

        $tokenTersimpan = $_SESSION[self::KUNCI_SESI_CSRF] ?? '';
        if (!is_string($tokenTersimpan) || $tokenTersimpan === '') {
            return false;
        }

        return hash_equals($tokenTersimpan, $tokenMasukan);
    }

    /**
     * Merotasi (memperbarui) token CSRF setelah transaksi POST selesai diproses (pola PRG).
     *
     * @return string Token CSRF baru
     */
    public static function rotasiToken(): string
    {
        $_SESSION[self::KUNCI_SESI_CSRF] = bin2hex(random_bytes(32));
        return $_SESSION[self::KUNCI_SESI_CSRF];
    }
}
