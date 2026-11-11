<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

use RuntimeException;

/**
 * MiddlewareAutentikasi
 *
 * Single Source of Truth (SoT) untuk pemeriksaan status autentikasi pengguna
 * dan otorisasi berbasis peran (Role-Based Access Control / RBAC).
 */
final class MiddlewareAutentikasi
{
    /**
     * Memeriksa apakah pengguna sudah masuk (login) ke dalam sesi.
     *
     * @return bool True jika sesi pengguna aktif
     */
    public static function sudahMasuk(): bool
    {
        $idPengguna = Sesi::ambil('id_pengguna');
        return is_int($idPengguna) && $idPengguna > 0;
    }

    /**
     * Mewajibkan pengguna sudah masuk; jika belum, alihkan ke halaman masuk.
     *
     * @return void
     */
    public static function wajibMasuk(): void
    {
        if (!self::sudahMasuk()) {
            Sesi::tetapkanPesanKilat('peringatan', 'Silakan masuk terlebih dahulu untuk mengakses halaman tersebut.');
            header('Location: ' . urlDasar('masuk'));
            exit;
        }
    }

    /**
     * Memeriksa apakah peran pengguna saat ini termasuk dalam daftar peran yang diizinkan.
     *
     * @param array<int, string> $peranDiizinkan Daftar peran (contoh: ['admin', 'teller'])
     * @return void
     *
     * @throws RuntimeException Jika peran pengguna tidak memiliki hak akses
     */
    public static function wajibPeran(array $peranDiizinkan): void
    {
        self::wajibMasuk();

        $peranSaatIni = (string) Sesi::ambil('peran_pengguna', '');
        if (!in_array($peranSaatIni, $peranDiizinkan, true)) {
            throw new RuntimeException('Akses ditolak: Peran Anda tidak memiliki izin untuk operasi ini.');
        }
    }
}

