<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

use RuntimeException;

/**
 * MiddlewareAutentikasi
 *
 * @konsep_rpl Autentikasi vs Otorisasi (Role-Based Access Control / RBAC) & Idle Timeout
 * Dalam keamanan perangkat lunak terdapat dua tahap pemeriksaan:
 * 1. Autentikasi (`wajibMasuk()`): Memastikan pengguna sudah teridentifikasi (login) dan
 *    sesi tidak kedaluwarsa karena idle timeout (30 menit tanpa aktivitas).
 * 2. Otorisasi (`wajibPeran()`): Memastikan peran pengguna (`admin`, `teller`, `supervisor`)
 *    memiliki wewenang terhadap fitur tersebut (contoh: hanya `supervisor` dan `admin` yang
 *    boleh mengeksekusi Jurnal Koreksi), jika tidak melemparkan HTTP 403 (`PengecualianHttp`).
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
     * Mewajibkan pengguna sudah masuk dan sesinya belum kedaluwarsa; jika belum, alihkan ke halaman masuk.
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

        if (!Sesi::periksaKedaluwarsaSesi()) {
            PencatatLog::peringatan('Sesi petugas diakhiri otomatis karena batas waktu diam (idle timeout).');
            Sesi::tetapkanPesanKilat(
                'peringatan',
                'Sesi Anda telah berakhir otomatis setelah 30 menit tidak aktif. Silakan masuk kembali.'
            );
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
     * @throws PengecualianHttp|RuntimeException Jika peran pengguna tidak memiliki hak akses (HTTP 403)
     */
    public static function wajibPeran(array $peranDiizinkan): void
    {
        self::wajibMasuk();

        $peranSaatIni = (string) Sesi::ambil('peran_pengguna', '');
        if (!in_array($peranSaatIni, $peranDiizinkan, true)) {
            throw new PengecualianHttp(
                403,
                'Akses Ditolak (403)',
                'Akses ditolak: Peran Anda tidak memiliki izin untuk operasi ini.'
            );
        }
    }
}
