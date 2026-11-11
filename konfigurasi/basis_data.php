<?php

declare(strict_types=1);

/**
 * Konfigurasi Koneksi Basis Data MySQL InnoDB (Single Source of Truth)
 *
 * @konsep_rpl Pemisahan Konfigurasi Basis Data
 * Menyimpan parameter koneksi MySQL (host, port, nama database, kredensial)
 * di satu file terpusat yang hanya dibaca oleh `Aplikasi\Inti\BasisData`.
 * Set karakter menggunakan `utf8mb4` agar mendukung seluruh karakter Unicode secara utuh.
 */

return [
    'host'          => '127.0.0.1',
    'port'          => 3306,
    'nama_db'       => 'bankmini_db',
    'nama_pengguna' => 'root',
    'kata_sandi'    => '',
    'set_karakter'  => 'utf8mb4',
];
