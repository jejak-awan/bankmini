<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

/**
 * PembatasLaju
 *
 * @konsep_rpl Pembatasan Laju Permintaan (Rate Limiting) & Pertahanan Anti Brute-Force
 * Mencegah penyerang menebak kata sandi teller/admin secara otomatis ratusan kali per menit:
 * 1. Menghitung jumlah kegagalan login per kunci unik (`nama_pengguna + alamat_ip`) dalam jendela waktu tertentu.
 * 2. Jika jumlah kegagalan mencapai batas maksimal (default 5 kali dalam 300 detik), akses login
 *    dikunci sementara dan insiden dicatat ke `PencatatLog::keamanan()`.
 * 3. Saat login berhasil, penghitung otomatis di-reset (`hapusCatatan()`).
 */
final class PembatasLaju
{
    private string $direktoriPenyimpanan;
    private int $maksimalPercobaan;
    private int $jendelaDetik;

    /**
     * Konstruktor PembatasLaju.
     *
     * @param string|null $direktoriPenyimpanan Lokasi folder state pembatas laju (null = default log)
     * @param int         $maksimalPercobaan    Batas kegagalan sebelum dikunci sementara
     * @param int         $jendelaDetik         Durasi jendela waktu pembatasan dalam detik
     */
    public function __construct(
        ?string $direktoriPenyimpanan = null,
        int $maksimalPercobaan = 5,
        int $jendelaDetik = 300
    ) {
        $this->direktoriPenyimpanan = $direktoriPenyimpanan !== null && trim($direktoriPenyimpanan) !== ''
            ? rtrim($direktoriPenyimpanan, '/\\')
            : dirname(__DIR__, 2)
                . DIRECTORY_SEPARATOR . 'penyimpanan'
                . DIRECTORY_SEPARATOR . 'log'
                . DIRECTORY_SEPARATOR . 'pembatas_laju';
        $this->maksimalPercobaan = max(1, $maksimalPercobaan);
        $this->jendelaDetik = max(1, $jendelaDetik);
    }

    /**
     * Memeriksa apakah kunci saat ini sedang diblokir sementara beserta sisa detik tunggu.
     *
     * @param string   $kunci       Identitas unik percobaan (misalnya 'masuk|teller1|127.0.0.1')
     * @param int|null $waktuAcuan  Timestamp UNIX opsional untuk pengujian deterministik
     * @return int Sisa detik blokir (0 jika diizinkan melanjutkan)
     */
    public function ambilSisaDetikBlokir(string $kunci, ?int $waktuAcuan = null): int
    {
        $sekarang = $waktuAcuan ?? time();
        $data = $this->bacaCatatan($kunci);
        $daftarWaktu = $this->saringJendelaAktif($data['percobaan'], $sekarang);

        if ($daftarWaktu === [] || count($daftarWaktu) < $this->maksimalPercobaan) {
            return 0;
        }

        $percobaanPertama = min($daftarWaktu);
        $waktuBebas = $percobaanPertama + $this->jendelaDetik;

        return max(0, $waktuBebas - $sekarang);
    }

    /**
     * Mencatat satu kejadian kegagalan untuk kunci tertentu dan mengembalikan total percobaan aktif.
     *
     * @param string   $kunci       Identitas unik percobaan
     * @param int|null $waktuAcuan  Timestamp UNIX opsional untuk pengujian deterministik
     * @return int Total percobaan gagal di dalam jendela waktu aktif
     */
    public function catatKegagalan(string $kunci, ?int $waktuAcuan = null): int
    {
        $sekarang = $waktuAcuan ?? time();
        $data = $this->bacaCatatan($kunci);
        $daftarWaktu = $this->saringJendelaAktif($data['percobaan'], $sekarang);
        $daftarWaktu[] = $sekarang;

        $this->simpanCatatan($kunci, ['percobaan' => $daftarWaktu]);

        return count($daftarWaktu);
    }

    /**
     * Menghapus catatan kegagalan setelah autentikasi berhasil.
     *
     * @param string $kunci Identitas unik percobaan
     * @return void
     */
    public function reset(string $kunci): void
    {
        $jalur = $this->bangunJalurBerkas($kunci);
        if (is_file($jalur)) {
            @unlink($jalur);
        }
    }

    /**
     * Mengambil batas maksimal percobaan yang dikonfigurasi.
     *
     * @return int Jumlah maksimal percobaan
     */
    public function ambilMaksimalPercobaan(): int
    {
        return $this->maksimalPercobaan;
    }

    /**
     * Menyaring daftar timestamp kegagalan agar hanya mempertahankan yang masih berada di jendela waktu.
     *
     * @param array<int, int> $daftarWaktu Daftar timestamp kegagalan sebelumnya
     * @param int             $sekarang    Timestamp saat ini
     * @return list<int> Daftar timestamp yang masih aktif
     */
    private function saringJendelaAktif(array $daftarWaktu, int $sekarang): array
    {
        $batasAwal = $sekarang - $this->jendelaDetik;
        $hasil = [];
        foreach ($daftarWaktu as $waktu) {
            $nilaiWaktu = (int) $waktu;
            if ($nilaiWaktu > $batasAwal && $nilaiWaktu <= $sekarang) {
                $hasil[] = $nilaiWaktu;
            }
        }

        return $hasil;
    }

    /**
     * Membaca berkas JSON catatan percobaan gagal untuk sebuah kunci.
     *
     * @param string $kunci Kunci identifikasi
     * @return array{percobaan: list<int>} Data catatan
     */
    private function bacaCatatan(string $kunci): array
    {
        $jalur = $this->bangunJalurBerkas($kunci);
        if (!is_file($jalur)) {
            return ['percobaan' => []];
        }

        $isi = @file_get_contents($jalur);
        if ($isi === false || trim($isi) === '') {
            return ['percobaan' => []];
        }

        $data = json_decode($isi, true);
        if (!is_array($data) || !isset($data['percobaan']) || !is_array($data['percobaan'])) {
            return ['percobaan' => []];
        }

        $percobaan = [];
        foreach ($data['percobaan'] as $item) {
            if (is_int($item) || is_numeric($item)) {
                $percobaan[] = (int) $item;
            }
        }

        return ['percobaan' => $percobaan];
    }

    /**
     * Menyimpan catatan percobaan gagal secara atomik ke berkas JSON.
     *
     * @param string                  $kunci Kunci identifikasi
     * @param array{percobaan: list<int>} $data Data yang disimpan
     * @return void
     */
    private function simpanCatatan(string $kunci, array $data): void
    {
        if (!is_dir($this->direktoriPenyimpanan)) {
            @mkdir($this->direktoriPenyimpanan, 0775, true);
        }

        $jalur = $this->bangunJalurBerkas($kunci);
        $kontenJson = json_encode($data, JSON_UNESCAPED_SLASHES) ?: '{"percobaan":[]}';
        @file_put_contents($jalur, $kontenJson, LOCK_EX);
    }

    /**
     * Menghasilkan jalur file aman berbasis SHA-256 agar nama file bebas karakter ilegal.
     *
     * @param string $kunci Kunci identifikasi
     * @return string Jalur lengkap file state
     */
    private function bangunJalurBerkas(string $kunci): string
    {
        return $this->direktoriPenyimpanan . DIRECTORY_SEPARATOR . hash('sha256', $kunci) . '.json';
    }
}
