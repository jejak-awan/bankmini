<?php

declare(strict_types=1);

/**
 * Pengendali Depan Tunggal (Single Front Controller)
 *
 * @konsep_rpl Pola Desain Front Controller & Autoloading (PSR-4)
 * Mengapa aplikasi PHP Native profesional menggunakan satu pintu masuk (`public/index.php`)?
 * 1. Menghindari Spaghetti Code: Pengguna tidak mengakses file `setor.php` atau `tarik.php`
 *    secara langsung, melainkan melewati satu gerbang terpusat.
 * 2. Keamanan Terpusat: Sesi aman (`Sesi::mulaiSesiAman()`) dan autoloader diinisialisasi
 *    satu kali di sini sebelum request diteruskan ke `Perute` (Router).
 * 3. Autoloading Otomatis: Fungsi `spl_autoload_register` memetakan namespace `Aplikasi\...`
 *    langsung ke lokasi file di folder `aplikasi/...` tanpa perlu menulis puluhan `require_once`.
 */

$jalurAutoloadComposer = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($jalurAutoloadComposer)) {
    require_once $jalurAutoloadComposer;
}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'aplikasi' . DIRECTORY_SEPARATOR . 'Pembantu' . DIRECTORY_SEPARATOR . 'fungsi_umum.php';

spl_autoload_register(
    /**
     * Memuat file class secara otomatis berdasarkan namespace Aplikasi\.
     *
     * @param string $namaClassLengkap Nama class beserta namespace (contoh: Aplikasi\Layanan\LayananTransaksi)
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
use Aplikasi\Pengendali\PengendaliDokumentasi;
use Aplikasi\Pengendali\PengendaliLaporan;
use Aplikasi\Pengendali\PengendaliNasabah;
use Aplikasi\Pengendali\PengendaliProduk;
use Aplikasi\Pengendali\PengendaliRekening;
use Aplikasi\Pengendali\PengendaliTransaksi;

// [Langkah 1 - Konsep RPL]: Mulai sesi dengan cookie HttpOnly dan SameSite=Lax
Sesi::mulaiSesiAman();

// [Langkah 2 - Konsep RPL]: Daftarkan seluruh peta rute HTTP GET dan POST pada Perute tunggal
$perute = new Perute();

// Rute Autentikasi (Masuk & Keluar Sistem)
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

// Rute Transaksi Keuangan (Mata Uang Rupiah IDR - ACID)
$perute->daftarGet('/transaksi/setor', [PengendaliTransaksi::class, 'tampilkanSetorTunai']);
$perute->daftarPost('/transaksi/setor', [PengendaliTransaksi::class, 'prosesSetorTunai']);
$perute->daftarGet('/transaksi/tarik', [PengendaliTransaksi::class, 'tampilkanTarikTunai']);
$perute->daftarPost('/transaksi/tarik', [PengendaliTransaksi::class, 'prosesTarikTunai']);
$perute->daftarGet('/transaksi/transfer', [PengendaliTransaksi::class, 'tampilkanTransfer']);
$perute->daftarPost('/transaksi/transfer', [PengendaliTransaksi::class, 'prosesTransfer']);
$perute->daftarGet('/transaksi/koreksi', [PengendaliTransaksi::class, 'tampilkanKoreksi']);
$perute->daftarPost('/transaksi/koreksi', [PengendaliTransaksi::class, 'prosesKoreksi']);

// Rute Laporan Buku Besar Mutasi & Pusat Dokumentasi RPL
$perute->daftarGet('/laporan/mutasi', [PengendaliLaporan::class, 'mutasi']);
$perute->daftarGet('/dokumentasi', [PengendaliDokumentasi::class, 'indeks']);

// [Langkah 3 - Konsep RPL]: Eksekusi rute berdasarkan metode HTTP dan URI yang diminta browser
$metodeHttp = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
$uriRequest = (string) ($_SERVER['REQUEST_URI'] ?? '/');

$perute->jalankanRute($metodeHttp, $uriRequest);
