<?php

declare(strict_types=1);

/**
 * Konfigurasi Koneksi Basis Data MySQL InnoDB (Single Source of Truth)
 *
 * @konsep_rpl Pemisahan Konfigurasi Basis Data & Variabel Lingkungan (12-Factor App)
 * Menyimpan parameter koneksi MySQL (host, port, nama database, kredensial)
 * di satu file terpusat yang hanya dibaca oleh `Aplikasi\Inti\BasisData`.
 * Mendukung pembacaan variabel lingkungan (`getenv`) untuk container Docker / CI/CD lokal,
 * serta otomatis jatuh kembali (*fallback*) ke pengaturan bawaan XAMPP lokal (`127.0.0.1:3306`).
 */

$hostLingkungan = getenv('BANKMINI_DB_HOST');
$portLingkungan = getenv('BANKMINI_DB_PORT');
$namaDbLingkungan = getenv('BANKMINI_DB_NAMA');
$penggunaLingkungan = getenv('BANKMINI_DB_PENGGUNA');
$sandiLingkungan = getenv('BANKMINI_DB_SANDI');

return [
    'host'          => is_string($hostLingkungan) && $hostLingkungan !== '' ? $hostLingkungan : '127.0.0.1',
    'port'          => is_string($portLingkungan) && is_numeric($portLingkungan) ? (int) $portLingkungan : 3306,
    'nama_db'       => is_string($namaDbLingkungan) && $namaDbLingkungan !== '' ? $namaDbLingkungan : 'bankmini_db',
    'nama_pengguna' => is_string($penggunaLingkungan) && $penggunaLingkungan !== '' ? $penggunaLingkungan : 'root',
    'kata_sandi'    => is_string($sandiLingkungan) ? $sandiLingkungan : '',
    'set_karakter'  => 'utf8mb4',
];
