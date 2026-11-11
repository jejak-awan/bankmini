<?php

declare(strict_types=1);

/**
 * Pengendali Depan Tunggal (Single Front Controller)
 *
 * Satu-satunya pintu masuk HTTP untuk aplikasi Bank Mini (PHP Native).
 * Memuat fungsi pembantu, autoloader PSR-4 untuk namespace Aplikasi\,
 * menginisialisasi sesi aman, dan menjalankan Perute (Router).
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'aplikasi' . DIRECTORY_SEPARATOR . 'Pembantu' . DIRECTORY_SEPARATOR . 'fungsi_umum.php';

spl_autoload_register(
    /**
     * Memuat file class secara otomatis berdasarkan namespace Aplikasi\.
     *
     * @param string $namaClassLengkap Nama class beserta namespace
     * @return void
     */
    function (string $namaClassLengkap): void {
        $awalanNamespace = 'Aplikasi\\';
        if (!str_starts_with($namaClassLengkap, $awalanNamespace)) {
            return;
        }

        $bagianRelatif = substr($namaClassLengkap, strlen($awalanNamespace));
        $jalurFile = dirname(__DIR__)
            . DIRECTORY_SEPARATOR . 'aplikasi'
            . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $bagianRelatif)
            . '.php';

        if (is_file($jalurFile)) {
            require_once $jalurFile;
        }
    }
);

use Aplikasi\Inti\Perute;
use Aplikasi\Inti\Sesi;
use Aplikasi\Pengendali\PengendaliAutentikasi;
use Aplikasi\Pengendali\PengendaliDasbor;
use Aplikasi\Pengendali\PengendaliLaporan;
use Aplikasi\Pengendali\PengendaliNasabah;
use Aplikasi\Pengendali\PengendaliProduk;
use Aplikasi\Pengendali\PengendaliRekening;
use Aplikasi\Pengendali\PengendaliTransaksi;

Sesi::mulaiSesiAman();

$perute = new Perute();

// Rute Autentikasi
$perute->daftarGet('/masuk', [PengendaliAutentikasi::class, 'tampilkanHalamanMasuk']);
$perute->daftarPost('/masuk', [PengendaliAutentikasi::class, 'prosesMasuk']);
$perute->daftarPost('/keluar', [PengendaliAutentikasi::class, 'prosesKeluar']);

// Rute Dasbor & Master Data
$perute->daftarGet('/', [PengendaliDasbor::class, 'indeks']);
$perute->daftarGet('/dasbor', [PengendaliDasbor::class, 'indeks']);
$perute->daftarGet('/nasabah', [PengendaliNasabah::class, 'indeks']);
$perute->daftarPost('/nasabah/tambah', [PengendaliNasabah::class, 'simpanNasabah']);
$perute->daftarGet('/produk', [PengendaliProduk::class, 'indeks']);
$perute->daftarGet('/rekening', [PengendaliRekening::class, 'indeks']);
$perute->daftarPost('/rekening/buka', [PengendaliRekening::class, 'bukaRekening']);

// Rute Transaksi Keuangan (Rupiah IDR)
$perute->daftarGet('/transaksi/setor', [PengendaliTransaksi::class, 'tampilkanSetorTunai']);
$perute->daftarPost('/transaksi/setor', [PengendaliTransaksi::class, 'prosesSetorTunai']);
$perute->daftarGet('/transaksi/tarik', [PengendaliTransaksi::class, 'tampilkanTarikTunai']);
$perute->daftarPost('/transaksi/tarik', [PengendaliTransaksi::class, 'prosesTarikTunai']);
$perute->daftarGet('/transaksi/transfer', [PengendaliTransaksi::class, 'tampilkanTransfer']);
$perute->daftarPost('/transaksi/transfer', [PengendaliTransaksi::class, 'prosesTransfer']);
$perute->daftarGet('/transaksi/koreksi', [PengendaliTransaksi::class, 'tampilkanKoreksi']);
$perute->daftarPost('/transaksi/koreksi', [PengendaliTransaksi::class, 'prosesKoreksi']);

// Rute Laporan Buku Besar
$perute->daftarGet('/laporan/mutasi', [PengendaliLaporan::class, 'mutasi']);

$metodeHttp = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
$uriRequest = (string) ($_SERVER['REQUEST_URI'] ?? '/');

$perute->jalankanRute($metodeHttp, $uriRequest);

