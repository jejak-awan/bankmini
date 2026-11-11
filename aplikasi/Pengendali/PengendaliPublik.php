<?php

declare(strict_types=1);

namespace Aplikasi\Pengendali;

use Aplikasi\Inti\BasisData;
use Aplikasi\Inti\PenanganGalat;
use Aplikasi\Inti\PencatatLog;
use Aplikasi\Inti\PengendaliDasar;
use Aplikasi\Model\ModelProduk;
use Throwable;

/**
 * PengendaliPublik
 *
 * @konsep_rpl Pemisahan Tata Letak Publik & Dasbor serta Health Check Observabilitas
 * Menampilkan halaman Portal Informasi Publik (`GET /portal`) secara ringan dan menyediakan
 * endpoint pemantauan kesehatan sistem (`GET /kesehatan`) tanpa membocorkan kredensial sensitif.
 */
class PengendaliPublik extends PengendaliDasar
{
    /**
     * Memeriksa status kesiapan komponen inti aplikasi untuk endpoint `GET /kesehatan`.
     *
     * @param string|null $direktoriLogOpsional Jalur folder log untuk pengujian terisolasi
     * @return array<string, mixed> Laporan kesehatan terstruktur JSON-ready
     */
    public function susunStatusKesehatan(?string $direktoriLogOpsional = null): array
    {
        $jalurKonfigurasi = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'konfigurasi'
            . DIRECTORY_SEPARATOR . 'aplikasi.php';
        $konfigurasi = is_file($jalurKonfigurasi) ? require $jalurKonfigurasi : [];
        $versiAplikasi = (string) ($konfigurasi['versi'] ?? '1.0.0');

        // [Langkah 1 - Konsep RPL]: Periksa keterhubungan database InnoDB menggunakan ping terpusat `BasisData`.
        $statusDb = BasisData::periksaKesiapanKoneksi() ? 'siap' : 'terganggu';

        // [Langkah 2 - Konsep RPL]: Pastikan folder penyimpanan/log siap ditulis untuk pencatatan galat harian.
        $logSiapTulis = PencatatLog::siapTulis($direktoriLogOpsional);
        $modePemeliharaan = PenanganGalat::apakahModePemeliharaan();

        $sehatKeseluruhan = ($statusDb === 'siap') && $logSiapTulis && !$modePemeliharaan;

        return [
            'status' => $sehatKeseluruhan ? 'sehat' : 'terganggu',
            'versi' => $versiAplikasi,
            'mode_pemeliharaan' => $modePemeliharaan,
            'waktu_server' => date('c'),
            'pemeriksaan' => [
                'php_versi' => PHP_VERSION,
                'basis_data' => $statusDb,
                'penyimpanan_log' => $logSiapTulis ? 'siap_tulis' : 'terkunci',
            ],
        ];
    }

    /**
     * Mengembalikan respons JSON kesiapan aplikasi untuk pemantauan Docker/uptime (`GET /kesehatan`).
     *
     * @return void
     */
    public function kesehatan(): void
    {
        // [Langkah 1 - Konsep RPL]: Hitung status komponen lalu kirimkan HTTP 200 (sehat) atau 503 (terganggu).
        $laporan = $this->susunStatusKesehatan();
        $kodeHttp = ($laporan['status'] ?? 'terganggu') === 'sehat' ? 200 : 503;

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            http_response_code($kodeHttp);
        }

        echo json_encode($laporan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Menampilkan Portal Informasi Publik Bank Mini Sekolah (GET /portal).
     *
     * @return void
     */
    public function portal(): void
    {
        // [Langkah 1 - Konsep RPL]: Ambil daftar produk tabungan aktif untuk ditampilkan ke siswa/calon nasabah
        $daftarProdukAktif = [];
        try {
            $modelProduk = new ModelProduk();
            foreach ($modelProduk->ambilSemuaProduk() as $barisProduk) {
                if (strtolower((string) ($barisProduk['status'] ?? '')) === 'aktif') {
                    $daftarProdukAktif[] = $barisProduk;
                }
            }
        } catch (Throwable) {
            // Abaikan secara aman jika database belum diinisialisasi agar portal informasi tetap terbuka
            $daftarProdukAktif = [];
        }

        // [Langkah 2 - Konsep RPL]: Render memakai kerangka 'publik' (hanya token-desain + komponen-ui + publik + inti-ui)
        $this->tampilkanTampilan(
            'publik/portal',
            [
                'judulHalaman' => 'Portal Informasi Publik — Bank Mini Sekolah (IDR)',
                'kelasBody' => 'd-flex flex-column min-vh-100 bg-light',
                'daftarProdukAktif' => $daftarProdukAktif,
            ],
            'publik'
        );
    }
}
