<?php

declare(strict_types=1);

namespace Pengujian\PembantuPengujian;

use RuntimeException;
use Throwable;

/**
 * KasusUjiDasar
 *
 * Class induk asersi pengujian otomatis Quality Assurance (QA) untuk projek Bank Mini.
 * Menyediakan method verifikasi berbahasa Indonesia (pastikanSama, pastikanBenar,
 * pastikanMelemparPengecualian).
 */
abstract class KasusUjiDasar
{
    /**
     * Memastikan dua nilai identik (===); melempar pengecualian jika berbeda.
     *
     * @param mixed  $nilaiHarapan Nilai yang diharapkan
     * @param mixed  $nilaiAktual  Nilai aktual hasil eksekusi
     * @param string $pesanGagal   Pesan penjelasan jika asersi gagal
     * @return void
     *
     * @throws RuntimeException Jika nilai aktual tidak sama dengan nilai harapan
     */
    protected function pastikanSama(mixed $nilaiHarapan, mixed $nilaiAktual, string $pesanGagal = ''): void
    {
        if ($nilaiHarapan !== $nilaiAktual) {
            $rincian = sprintf(
                'Asersi gagal: Diharapkan [%s], tetapi diperoleh [%s]. %s',
                var_export($nilaiHarapan, true),
                var_export($nilaiAktual, true),
                $pesanGagal
            );
            throw new RuntimeException(trim($rincian));
        }
    }

    /**
     * Memastikan kondisi bernilai true.
     *
     * @param bool   $kondisi    Kondisi boolean yang diuji
     * @param string $pesanGagal Pesan penjelasan jika kondisi bernilai false
     * @return void
     *
     * @throws RuntimeException Jika kondisi bernilai false
     */
    protected function pastikanBenar(bool $kondisi, string $pesanGagal = 'Kondisi diharapkan bernilai true.'): void
    {
        if ($kondisi !== true) {
            throw new RuntimeException('Asersi gagal: ' . $pesanGagal);
        }
    }

    /**
     * Memastikan sebuah blok fungsi melempar pengecualian dengan tipe class tertentu.
     *
     * @param class-string<Throwable> $namaClassPengecualian Nama class Exception yang diharapkan
     * @param callable                $fungsiUji             Blok fungsi yang harus melempar pengecualian
     * @param string                  $pesanGagal            Pesan tambahan jika tidak melempar pengecualian
     * @return void
     *
     * @throws RuntimeException Jika pengecualian tidak dilempar atau tipenya berbeda
     */
    protected function pastikanMelemparPengecualian(
        string $namaClassPengecualian,
        callable $fungsiUji,
        string $pesanGagal = ''
    ): void {
        try {
            $fungsiUji();
        } catch (Throwable $tangkap) {
            if ($tangkap instanceof $namaClassPengecualian) {
                return;
            }

            throw new RuntimeException(
                sprintf(
                    'Asersi gagal: Diharapkan pengecualian [%s], tetapi dilempar [%s: %s]. %s',
                    $namaClassPengecualian,
                    get_class($tangkap),
                    $tangkap->getMessage(),
                    $pesanGagal
                )
            );
        }

        throw new RuntimeException(
            sprintf('Asersi gagal: Pengecualian [%s] tidak dilempar. %s', $namaClassPengecualian, $pesanGagal)
        );
    }
}

