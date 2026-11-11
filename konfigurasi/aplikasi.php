<?php

declare(strict_types=1);

/**
 * Konfigurasi Utama Aplikasi Bank Mini (Single Source of Truth)
 *
 * @konsep_rpl Single Source of Truth (SoT) Konfigurasi & Manajemen Versi
 * Mengapa file konfigurasi ini dipisahkan dari kode logika?
 * 1. Agar parameter sistem seperti nama aplikasi, mata uang (IDR), zona waktu,
 *    dan URL dasar hanya didefinisikan di satu tempat (tidak ditulis berulang/hardcode).
 * 2. Nomor versi aplikasi dibaca langsung dari file `VERSION` di direktori utama
 *    sehingga versi yang tampil di aplikasi selalu sinkron dengan `CHANGELOG.md`.
 * 3. Mendukung variabel lingkungan (`BANKMINI_URL_DASAR`, `BANKMINI_MODE_DEBUG`) untuk Docker/CI.
 */

date_default_timezone_set('Asia/Jakarta');

$jalurFileVersi = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'VERSION';
$versiAktif = is_file($jalurFileVersi)
    ? trim((string) file_get_contents($jalurFileVersi))
    : '1.0.0';

$urlDasarLingkungan = getenv('BANKMINI_URL_DASAR');
$modeDebugLingkungan = getenv('BANKMINI_MODE_DEBUG');

return [
    'nama_aplikasi' => 'Sistem Informasi Bank Mini',
    'versi'         => $versiAktif,
    'mata_uang'     => 'IDR',
    'simbol_uang'   => 'Rp',
    'zona_waktu'    => 'Asia/Jakarta',
    'url_dasar'     => is_string($urlDasarLingkungan) && $urlDasarLingkungan !== ''
        ? $urlDasarLingkungan
        : 'http://localhost/www/bankmini/public',
    'mode_debug'    => is_string($modeDebugLingkungan)
        ? filter_var($modeDebugLingkungan, FILTER_VALIDATE_BOOLEAN)
        : true,
];
