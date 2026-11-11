<?php

declare(strict_types=1);

namespace Pengujian\Unit;

use Aplikasi\Inti\TokenCsrf;
use Pengujian\PembantuPengujian\KasusUjiDasar;

/**
 * TesTokenCsrf
 *
 * Menguji pembuatan, validasi waktu konstan (hash_equals), dan rotasi token CSRF.
 */
class TesTokenCsrf extends KasusUjiDasar
{
    /**
     * @skenario QA-UNT-004
     * Memastikan token CSRF yang dihasilkan memiliki panjang 64 karakter heksadesimal dan valid saat diuji.
     *
     * @return void
     */
    public function ujiPembuatanDanValidasiTokenCsrfSah(): void
    {
        $_SESSION = [];
        $tokenAktif = TokenCsrf::ambilToken();

        $this->pastikanSama(64, strlen($tokenAktif), 'Panjang token CSRF harus 64 karakter hex.');
        $this->pastikanBenar(TokenCsrf::validasiToken($tokenAktif), 'Token CSRF yang sama harus valid.');
    }

    /**
     * @skenario QA-UNT-005
     * Memastikan token CSRF palsu atau token lama setelah rotasi PRG ditolak.
     *
     * @return void
     */
    public function ujiTokenCsrfPalsuAtauSetelahRotasiDitolak(): void
    {
        $_SESSION = [];
        $tokenLama = TokenCsrf::ambilToken();
        $tokenBaru = TokenCsrf::rotasiToken();

        $this->pastikanBenar($tokenLama !== $tokenBaru, 'Token hasil rotasi harus berbeda dari token lama.');
        $this->pastikanBenar(!TokenCsrf::validasiToken($tokenLama), 'Token lama sebelum rotasi harus ditolak.');
        $this->pastikanBenar(!TokenCsrf::validasiToken('token_palsu_123'), 'Token palsu harus ditolak.');
    }
}
