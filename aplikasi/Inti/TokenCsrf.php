<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

/**
 * TokenCsrf
 *
 * Single Source of Truth (SoT) untuk pembuatan, validasi waktu konstan (hash_equals),
 * dan rotasi token Cross-Site Request Forgery (CSRF) pada setiap form POST.
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

