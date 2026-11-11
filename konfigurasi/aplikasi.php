<?php

declare(strict_types=1);

/**
 * Konfigurasi Utama Aplikasi Bank Mini (Single Source of Truth)
 */

date_default_timezone_set('Asia/Jakarta');

return [
    'nama_aplikasi' => 'Sistem Informasi Bank Mini',
    'versi'         => '1.0.0',
    'mata_uang'     => 'IDR',
    'simbol_uang'   => 'Rp',
    'zona_waktu'    => 'Asia/Jakarta',
    'url_dasar'     => 'http://localhost/www/bankmini/public',
    'mode_debug'    => true,
];

