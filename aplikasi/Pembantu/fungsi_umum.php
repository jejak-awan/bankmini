<?php

declare(strict_types=1);

/**
 * Kumpulan Fungsi Pembantu Umum (Stateless Helpers)
 *
 * Single Source of Truth (SoT) untuk sanitasi XSS HTML, pemformatan mata uang
 * tunggal Rupiah (IDR), pemformatan tanggal Indonesia, dan pembuatan URL dasar.
 */

if (!function_exists('amankanHtml')) {
    /**
     * Mengamankan nilai dinamis dari serangan Cross-Site Scripting (XSS) sebelum dicetak ke HTML.
     *
     * @param mixed $nilai Nilai yang akan ditampilkan pada elemen HTML
     * @return string Teks yang sudah di-escape dengan aman
     */
    function amankanHtml(mixed $nilai): string
    {
        return htmlspecialchars((string) $nilai, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('formatRupiah')) {
    /**
     * Memformat angka nominal menjadi teks mata uang Rupiah (IDR).
     *
     * @param string|int|float $nominal      Nilai nominal uang dalam Rupiah
     * @param bool             $tampilkanSen True jika ingin menampilkan 2 desimal sen (,00)
     * @return string Contoh: "Rp 150.000" atau "Rp 150.000,00"
     */
    function formatRupiah(string|int|float $nominal, bool $tampilkanSen = false): string
    {
        $nilaiAngka = (float) $nominal;
        $jumlahDesimal = $tampilkanSen ? 2 : 0;
        return 'Rp ' . number_format($nilaiAngka, $jumlahDesimal, ',', '.');
    }
}

if (!function_exists('formatTanggalIndonesia')) {
    /**
     * Memformat string tanggal SQL (YYYY-MM-DD HH:MM:SS) menjadi format tanggal Bahasa Indonesia.
     *
     * @param string|null $waktuSql     String tanggal dari database
     * @param bool        $sertakanJam  True jika ingin menampilkan jam:menit
     * @return string Contoh: "11 November 2026 17:30"
     */
    function formatTanggalIndonesia(?string $waktuSql, bool $sertakanJam = true): string
    {
        if ($waktuSql === null || trim($waktuSql) === '') {
            return '-';
        }

        $detikUnix = strtotime($waktuSql);
        if ($detikUnix === false) {
            return $waktuSql;
        }

        $daftarBulan = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $tanggal = date('d', $detikUnix);
        $nomorBulan = (int) date('n', $detikUnix);
        $tahun = date('Y', $detikUnix);
        $hasil = $tanggal . ' ' . ($daftarBulan[$nomorBulan] ?? '') . ' ' . $tahun;

        if ($sertakanJam) {
            $hasil .= ' ' . date('H:i', $detikUnix);
        }

        return $hasil;
    }
}

if (!function_exists('urlDasar')) {
    /**
     * Menghasilkan URL lengkap berdasarkan jalur relatif terhadap url_dasar aplikasi.
     *
     * @param string $jalurRelatif Jalur relatif (contoh: 'transaksi/setor')
     * @return string URL absolut aplikasi
     */
    function urlDasar(string $jalurRelatif = ''): string
    {
        $konfigurasi = require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'konfigurasi' . DIRECTORY_SEPARATOR . 'aplikasi.php';
        $dasar = rtrim((string) $konfigurasi['url_dasar'], '/');
        $jalurBersih = ltrim($jalurRelatif, '/');

        return $jalurBersih === '' ? $dasar : $dasar . '/' . $jalurBersih;
    }
}

