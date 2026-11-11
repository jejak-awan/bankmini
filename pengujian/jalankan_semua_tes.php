<?php

/**
 * PelariSemuaTesQa
 *
 * Test Runner CLI tunggal untuk mengeksekusi seluruh pengujian Unit, Integrasi,
 * dan Keamanan pada projek Bank Mini (PHP Native).
 *
 * Cara menjalankan:
 * C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\pengujian\jalankan_semua_tes.php
 */

declare(strict_types=1);

// [Konsep RPL - Zero Warning QA Policy]: Ubah seluruh PHP warning, notice, dan deprecated menjadi kegagalan tes.
error_reporting(E_ALL);
set_error_handler(
    static function (int $tingkatKesalahan, string $pesanKesalahan, string $namaBerkas, int $nomorBaris): bool {
        if (!(error_reporting() & $tingkatKesalahan)) {
            return false;
        }
        throw new ErrorException($pesanKesalahan, 0, $tingkatKesalahan, $namaBerkas, $nomorBaris);
    }
);

$akarProjek = dirname(__DIR__);

require_once $akarProjek . DIRECTORY_SEPARATOR . 'aplikasi' . DIRECTORY_SEPARATOR . 'Pembantu' . DIRECTORY_SEPARATOR . 'fungsi_umum.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'PembantuPengujian' . DIRECTORY_SEPARATOR . 'KasusUjiDasar.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'PembantuPengujian' . DIRECTORY_SEPARATOR . 'BasisDataMemoriUji.php';

spl_autoload_register(
    /**
     * Memuat file class Aplikasi\ dan Pengujian\ secara otomatis.
     *
     * @param string $namaClassLengkap Nama class beserta namespace
     * @return void
     */
    function (string $namaClassLengkap) use ($akarProjek): void {
        if (str_starts_with($namaClassLengkap, 'Aplikasi\\')) {
            $bagian = substr($namaClassLengkap, strlen('Aplikasi\\'));
            $jalur = $akarProjek . DIRECTORY_SEPARATOR . 'aplikasi' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $bagian) . '.php';
            if (is_file($jalur)) {
                require_once $jalur;
            }
            return;
        }

        if (str_starts_with($namaClassLengkap, 'Pengujian\\')) {
            $bagian = substr($namaClassLengkap, strlen('Pengujian\\'));
            $jalur = $akarProjek . DIRECTORY_SEPARATOR . 'pengujian' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $bagian) . '.php';
            if (is_file($jalur)) {
                require_once $jalur;
            }
        }
    }
);

use Pengujian\Integrasi\TesLayananAutentikasi;
use Pengujian\Integrasi\TesLayananRekening;
use Pengujian\Integrasi\TesLayananTransaksi;
use Pengujian\Keamanan\TesKeamananAplikasi;
use Pengujian\Unit\TesFungsiUmum;
use Pengujian\Unit\TesTokenCsrf;

$daftarClassTes = [
    TesFungsiUmum::class,
    TesTokenCsrf::class,
    TesLayananAutentikasi::class,
    TesLayananRekening::class,
    TesLayananTransaksi::class,
    TesKeamananAplikasi::class,
];

echo "======================================================================\n";
echo "  SUITE PENGUJIAN OTOMATIS QA - SISTEM INFORMASI BANK MINI (IDR)      \n";
echo "======================================================================\n";

$totalTesDijalankan = 0;
$totalTesLulus = 0;
$daftarKegagalan = [];

foreach ($daftarClassTes as $namaClassTes) {
    $instansiTes = new $namaClassTes();
    $daftarMethod = get_class_methods($instansiTes);

    foreach ($daftarMethod as $namaMethod) {
        if (!str_starts_with($namaMethod, 'uji')) {
            continue;
        }

        $totalTesDijalankan++;
        try {
            $instansiTes->{$namaMethod}();
            $totalTesLulus++;
            echo "  [LULUS] {$namaClassTes}::{$namaMethod}()\n";
        } catch (Throwable $tangkap) {
            $pesanGagal = "{$namaClassTes}::{$namaMethod}() -> " . $tangkap->getMessage();
            $daftarKegagalan[] = $pesanGagal;
            echo "  [GAGAL] {$pesanGagal}\n";
        }
    }
}

echo "----------------------------------------------------------------------\n";
echo "Total Skenario Uji : {$totalTesDijalankan}\n";
echo "Total Lulus        : {$totalTesLulus}\n";
echo "Total Gagal        : " . count($daftarKegagalan) . "\n";

if (count($daftarKegagalan) === 0) {
    echo "\n[STATUS QA: LULUS 100%] Seluruh skenario Unit, Integrasi ACID, & Keamanan berhasil!\n";
    echo "======================================================================\n";
    exit(0);
}

echo "\n[STATUS QA: GAGAL] Terdapat skenario uji yang belum lulus.\n";
echo "======================================================================\n";
exit(1);
