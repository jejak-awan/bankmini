<?php

declare(strict_types=1);

namespace Aplikasi\Inti;

/**
 * PencatatLog
 *
 * @konsep_rpl Pencatatan Log Terpusat (Centralized Logging), Rotasi Harian, & Redaksi Rahasia
 * Mengapa aplikasi tidak boleh menggunakan `echo error` atau meninggalkan `var_dump()` di produksi?
 * 1. Pencatatan Terpusat & Rotasi Harian: Seluruh kesalahan sistem, peringatan keamanan,
 *    dan jejak debugging dicatat rapi ke file `penyimpanan/log/aplikasi-YYYY-MM-DD.log`.
 * 2. Correlation ID: Setiap siklus permintaan HTTP diberi kode unik (`idKorelasi`) agar teknisi
 *    dapat mencocokkan laporan pengguna di halaman galat dengan baris log server secara akurat.
 * 3. Redaksi Data Sensitif (Secret Masking): Kunci seperti `kata_sandi` atau `token_csrf` otomatis
 *    diganti menjadi `[DIRAHASIAKAN]` sebelum ditulis ke disk agar log tidak membocorkan rahasia.
 */
final class PencatatLog
{
    public const TINGKAT_INFO = 'INFO';
    public const TINGKAT_PERINGATAN = 'PERINGATAN';
    public const TINGKAT_GALAT = 'GALAT';
    public const TINGKAT_KEAMANAN = 'KEAMANAN';

    /** @var list<string> Daftar kata kunci sensitif yang wajib disensor di dalam log */
    private const DAFTAR_KUNCI_RAHASIA = [
        'kata_sandi',
        'hash_kata_sandi',
        'password',
        'token_csrf',
        'token',
        'rahasia',
        'secret',
    ];

    private static ?string $idKorelasiAktif = null;
    private static ?string $direktoriLogKustom = null;

    /**
     * Mengambil atau membangkitkan Correlation ID unik untuk permintaan HTTP/CLI saat ini.
     *
     * @return string Kode korelasi (contoh: req-20261112-a1b2c3d4)
     */
    public static function ambilIdKorelasi(): string
    {
        if (self::$idKorelasiAktif === null) {
            self::$idKorelasiAktif = 'req-' . date('Ymd') . '-' . bin2hex(random_bytes(4));
        }

        return self::$idKorelasiAktif;
    }

    /**
     * Mengatur ulang Correlation ID (berguna di pengujian unit untuk isolasi skenario).
     *
     * @param string|null $idBaru Nilai ID baru atau null agar digenerate ulang
     * @return void
     */
    public static function aturIdKorelasi(?string $idBaru = null): void
    {
        self::$idKorelasiAktif = $idBaru;
    }

    /**
     * Mengatur direktori penyimpanan log kustom (khusus pengujian terisolasi) atau reset ke default.
     *
     * @param string|null $direktori Jalur folder log sementara atau null untuk kembali ke default
     * @return void
     */
    public static function aturDirektoriLogPengujian(?string $direktori): void
    {
        self::$direktoriLogKustom = $direktori;
    }

    /**
     * Menentukan jalur direktori penyimpanan log harian yang aktif.
     *
     * @return string Jalur absolut direktori penyimpanan/log
     */
    public static function ambilDirektoriLog(): string
    {
        if (self::$direktoriLogKustom !== null && trim(self::$direktoriLogKustom) !== '') {
            return rtrim(self::$direktoriLogKustom, '/\\');
        }

        return dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'penyimpanan'
            . DIRECTORY_SEPARATOR . 'log';
    }

    /**
     * Memverifikasi secara nyata bahwa direktori log tersedia dan dapat ditulisi lintas OS.
     *
     * @param string|null $direktoriOpsional Jalur direktori khusus pengujian atau null memakai default
     * @return bool True jika berkas uji berhasil ditulis lalu dibersihkan kembali
     */
    public static function siapTulis(?string $direktoriOpsional = null): bool
    {
        $direktori = $direktoriOpsional !== null && trim($direktoriOpsional) !== ''
            ? rtrim($direktoriOpsional, '/\\')
            : self::ambilDirektoriLog();

        if (!is_dir($direktori)) {
            @mkdir($direktori, 0775, true);
        }
        if (!is_dir($direktori)) {
            return false;
        }

        $berkasUji = $direktori . DIRECTORY_SEPARATOR . '.uji_tulis_' . bin2hex(random_bytes(3));
        $berhasil = @file_put_contents($berkasUji, 'ok') !== false;
        if ($berhasil) {
            @unlink($berkasUji);
        }

        return $berhasil;
    }

    /**
     * Menuliskan satu baris catatan log berformat terstruktur ke berkas harian.
     *
     * @param string               $tingkat Tingkat keparahan (INFO, PERINGATAN, GALAT, KEAMANAN)
     * @param string               $pesan   Ringkasan kejadian
     * @param array<string, mixed> $konteks Data tambahan terstruktur yang aman dicatat
     * @return void
     */
    public static function tulis(string $tingkat, string $pesan, array $konteks = []): void
    {
        $tingkatSah = strtoupper(trim($tingkat));
        $daftarTingkat = [
            self::TINGKAT_INFO,
            self::TINGKAT_PERINGATAN,
            self::TINGKAT_GALAT,
            self::TINGKAT_KEAMANAN,
        ];
        if (!in_array($tingkatSah, $daftarTingkat, true)) {
            $tingkatSah = self::TINGKAT_INFO;
        }

        $direktori = self::ambilDirektoriLog();
        if (!is_dir($direktori)) {
            @mkdir($direktori, 0775, true);
        }

        $jalurBerkas = $direktori
            . DIRECTORY_SEPARATOR
            . 'aplikasi-' . date('Y-m-d') . '.log';

        $konteksAman = self::bersihkanKonteks($konteks);
        $teksKonteks = $konteksAman === []
            ? ''
            : ' | konteks=' . (json_encode($konteksAman, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}');

        $pesanBersih = str_replace(["\r", "\n"], ' ', trim($pesan));
        $barisLog = sprintf(
            "[%s] [%s] [id:%s] %s%s\n",
            date('Y-m-d H:i:s'),
            $tingkatSah,
            self::ambilIdKorelasi(),
            $pesanBersih,
            $teksKonteks
        );

        @file_put_contents($jalurBerkas, $barisLog, FILE_APPEND | LOCK_EX);
    }

    /**
     * Mencatat log informasi operasional sistem (INFO).
     *
     * @param string               $pesan   Pesan log
     * @param array<string, mixed> $konteks Konteks tambahan
     * @return void
     */
    public static function info(string $pesan, array $konteks = []): void
    {
        self::tulis(self::TINGKAT_INFO, $pesan, $konteks);
    }

    /**
     * Mencatat peringatan non-fatal atau rute tidak ditemukan (PERINGATAN).
     *
     * @param string               $pesan   Pesan peringatan
     * @param array<string, mixed> $konteks Konteks tambahan
     * @return void
     */
    public static function peringatan(string $pesan, array $konteks = []): void
    {
        self::tulis(self::TINGKAT_PERINGATAN, $pesan, $konteks);
    }

    /**
     * Mencatat kesalahan aplikasi / pengecualian tidak terduga (GALAT).
     *
     * @param string               $pesan   Pesan galat
     * @param array<string, mixed> $konteks Konteks tambahan
     * @return void
     */
    public static function galat(string $pesan, array $konteks = []): void
    {
        self::tulis(self::TINGKAT_GALAT, $pesan, $konteks);
    }

    /**
     * Mencatat insiden atau percobaan pelanggaran keamanan (KEAMANAN).
     *
     * @param string               $pesan   Pesan keamanan
     * @param array<string, mixed> $konteks Konteks tambahan
     * @return void
     */
    public static function keamanan(string $pesan, array $konteks = []): void
    {
        self::tulis(self::TINGKAT_KEAMANAN, $pesan, $konteks);
    }

    /**
     * Menyensor kata kunci sensitif secara rekursif agar tidak pernah bocor ke file log.
     *
     * @param array<string, mixed> $konteks Data konteks asli
     * @return array<string, mixed> Data konteks yang telah disensor
     */
    public static function bersihkanKonteks(array $konteks): array
    {
        $hasil = [];
        foreach ($konteks as $kunci => $nilai) {
            $kunciHurufKecil = strtolower((string) $kunci);
            $apakahSensitif = false;

            foreach (self::DAFTAR_KUNCI_RAHASIA as $kataKunciRahasia) {
                if (str_contains($kunciHurufKecil, $kataKunciRahasia)) {
                    $apakahSensitif = true;
                    break;
                }
            }

            if ($apakahSensitif) {
                $hasil[(string) $kunci] = '[DIRAHASIAKAN]';
                continue;
            }

            if (is_array($nilai)) {
                /** @var array<string, mixed> $subArray */
                $subArray = $nilai;
                $hasil[(string) $kunci] = self::bersihkanKonteks($subArray);
                continue;
            }

            $hasil[(string) $kunci] = $nilai;
        }

        return $hasil;
    }
}
