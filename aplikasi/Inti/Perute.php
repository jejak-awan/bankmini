<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

/**
 * Perute
 *
 * @konsep_rpl Pemetaan Rute HTTP (HTTP Routing) & Pemisahan Metode GET vs POST
 * Memetakan alamat URL yang diminta pengguna (contoh: `GET /transaksi/setor` atau
 * `POST /transaksi/setor`) ke class `Pengendali` dan method aksi yang tepat.
 * Permintaan `GET` hanya digunakan untuk menampilkan data, sedangkan `POST` digunakan
 * untuk mengubah data di server.
 */
final class Perute
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> Daftar rute terdaftar */
    private array $daftarRute = [
        'GET'  => [],
        'POST' => [],
    ];

    /**
     * Mendaftarkan rute HTTP GET.
     *
     * @param string                           $jalur   Jalur URL (contoh: '/transaksi/setor')
     * @param array{0: class-string, 1: string} $tujuan Pasangan [NamaClassPengendali::class, 'namaMethod']
     * @return void
     */
    public function daftarGet(string $jalur, array $tujuan): void
    {
        $jalurNormal = $this->normalisasiJalur($jalur);
        $this->daftarRute['GET'][$jalurNormal] = $tujuan;
    }

    /**
     * Mendaftarkan rute HTTP POST.
     *
     * @param string                           $jalur   Jalur URL (contoh: '/transaksi/setor')
     * @param array{0: class-string, 1: string} $tujuan Pasangan [NamaClassPengendali::class, 'namaMethod']
     * @return void
     */
    public function daftarPost(string $jalur, array $tujuan): void
    {
        $jalurNormal = $this->normalisasiJalur($jalur);
        $this->daftarRute['POST'][$jalurNormal] = $tujuan;
    }

    /**
     * Menjalankan pencocokan rute dan memanggil Pengendali yang sesuai.
     *
     * @param string $metodeHttp Metode HTTP ('GET' atau 'POST')
     * @param string $uriRequest URI yang diminta oleh browser
     * @return void
     */
    public function jalankanRute(string $metodeHttp, string $uriRequest): void
    {
        $metodeBersih = strtoupper($metodeHttp);
        $jalurBersih = $this->ekstrakJalurDariUri($uriRequest);

        $tujuanRute = $this->daftarRute[$metodeBersih][$jalurBersih] ?? null;

        if ($tujuanRute === null) {
            throw new PengecualianHttp(
                404,
                'Halaman Tidak Ditemukan (404)',
                'Rute halaman yang Anda akses tidak terdaftar pada sistem Bank Mini.'
            );
        }

        [$namaClassPengendali, $namaMethod] = $tujuanRute;
        $instansiPengendali = new $namaClassPengendali();
        $instansiPengendali->{$namaMethod}();
    }

    /**
     * Menormalisasi string jalur agar selalu diawali '/' dan tanpa '/' di akhir.
     *
     * @param string $jalur Jalur mentah
     * @return string Jalur ternormalisasi
     */
    private function normalisasiJalur(string $jalur): string
    {
        $bersih = trim($jalur, '/');
        return $bersih === '' ? '/' : '/' . $bersih;
    }

    /**
     * Mengekstrak jalur relatif aplikasi dari REQUEST_URI.
     *
     * @param string $uriRequest URI lengkap dari server
     * @return string Jalur relatif bersih
     */
    private function ekstrakJalurDariUri(string $uriRequest): string
    {
        $jalurUri = (string) parse_url($uriRequest, PHP_URL_PATH);
        $direktoriSkrip = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

        if ($direktoriSkrip !== '/' && $direktoriSkrip !== '' && str_starts_with($jalurUri, $direktoriSkrip)) {
            $jalurUri = substr($jalurUri, strlen($direktoriSkrip));
        }

        return $this->normalisasiJalur($jalurUri);
    }
}
