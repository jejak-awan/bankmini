<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

/**
 * Sesi
 *
 * @konsep_rpl Keamanan Sesi (Session Hardening) & Pesan Kilat (Flash Message PRG)
 * Mengapa kita membungkus `$_SESSION` di dalam class `Sesi`?
 * 1. Mencegah Pencurian Cookie Sesi (XSS): Mengatur `cookie_httponly = 1` agar JavaScript
 *    berbahaya tidak dapat membaca `document.cookie`.
 * 2. Mencegah Session Fixation: Method `regenerasiIdSesi()` mengganti ID sesi lama dengan
 *    yang baru sesaat setelah pengguna berhasil masuk (login).
 * 3. Pesan Kilat (Flash Message): Menyimpan pesan sukses/gagal di sesi yang otomatis terhapus
 *    setelah dibaca satu kali pada halaman hasil redirect (pola Post-Redirect-Get).
 */
final class Sesi
{
    /**
     * Memulai sesi PHP dengan konfigurasi keamanan berlapis (HttpOnly, SameSite=Lax).
     *
     * @return void
     */
    public static function mulaiSesiAman(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                ini_set('session.use_strict_mode', '1');
                ini_set('session.cookie_httponly', '1');
                session_set_cookie_params([
                    'lifetime' => 0,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
                session_start();
            }
        }
    }

    /**
     * Meregenerasi ID sesi untuk mencegah serangan Session Fixation setelah pengguna masuk.
     *
     * @return void
     */
    public static function regenerasiIdSesi(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    /**
     * Menyimpan nilai ke dalam sesi berdasarkan kunci.
     *
     * @param string $kunci Nama kunci sesi
     * @param mixed  $nilai Nilai yang disimpan
     * @return void
     */
    public static function tetapkan(string $kunci, mixed $nilai): void
    {
        $_SESSION[$kunci] = $nilai;
    }

    /**
     * Mengambil nilai dari sesi berdasarkan kunci.
     *
     * @param string $kunci        Nama kunci sesi
     * @param mixed  $nilaiDefault Nilai kembali jika kunci tidak ditemukan
     * @return mixed Nilai sesi
     */
    public static function ambil(string $kunci, mixed $nilaiDefault = null): mixed
    {
        return $_SESSION[$kunci] ?? $nilaiDefault;
    }

    /**
     * Menyimpan pesan kilat (Flash Message) satu kali tampil untuk pola PRG (Post-Redirect-Get).
     *
     * @param string $jenisPesan Kategori pesan ('sukses', 'kesalahan', 'peringatan')
     * @param string $isiPesan   Teks pesan untuk ditampilkan ke pengguna
     * @return void
     */
    public static function tetapkanPesanKilat(string $jenisPesan, string $isiPesan): void
    {
        $_SESSION['pesan_kilat'] = [
            'jenis' => $jenisPesan,
            'pesan' => $isiPesan,
        ];
    }

    /**
     * Mengambil sekaligus menghapus pesan kilat agar hanya tampil satu kali.
     *
     * @return array<string, string>|null Data pesan kilat atau null jika kosong
     */
    public static function ambilPesanKilat(): ?array
    {
        if (!isset($_SESSION['pesan_kilat']) || !is_array($_SESSION['pesan_kilat'])) {
            return null;
        }

        $pesanKilat = $_SESSION['pesan_kilat'];
        unset($_SESSION['pesan_kilat']);

        return $pesanKilat;
    }

    /**
     * Menghancurkan seluruh data sesi saat pengguna keluar (logout).
     *
     * @return void
     */
    public static function hancurkanSesi(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_destroy();
        }
    }
}
