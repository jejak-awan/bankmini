<?php

declare(strict_types=1);

/**
 * PenaikVersiAplikasi (Bump Version CLI Tool)
 *
 * @konsep_rpl Manajemen Rilis & Semantic Versioning (SemVer 2.0.0)
 * Dalam Rekayasa Perangkat Lunak (RPL), nomor versi mengikuti format MAJOR.MINOR.PATCH:
 * - MAJOR (contoh: 1.0.0 -> 2.0.0): Perubahan besar yang tidak kompatibel ke belakang (breaking changes).
 * - MINOR (contoh: 1.0.0 -> 1.1.0): Penambahan fitur baru tanpa merusak fitur yang sudah ada.
 * - PATCH (contoh: 1.1.0 -> 1.1.1): Perbaikan kutu (bug fix) atau peningkatan keamanan kecil.
 *
 * Cara penggunaan dari terminal:
 * php dokumentasi/06-aturan-agen/skrip/naikkan_versi.php patch "Perbaikan validasi form setoran"
 * php dokumentasi/06-aturan-agen/skrip/naikkan_versi.php minor "Penambahan fitur cetak slip transaksi"
 */
final class PenaikVersiAplikasi
{
    private string $jalurFileVersi;
    private string $jalurFileChangelog;

    /**
     * Konstruktor PenaikVersiAplikasi.
     *
     * @param string $akarAplikasi Jalur direktori utama projek bankmini
     */
    public function __construct(string $akarAplikasi)
    {
        $akarBersih = rtrim($akarAplikasi, '\\/');
        $this->jalurFileVersi = $akarBersih . DIRECTORY_SEPARATOR . 'VERSION';
        $this->jalurFileChangelog = $akarBersih . DIRECTORY_SEPARATOR . 'CHANGELOG.md';
    }

    /**
     * Menaikkan nomor versi pada file VERSION dan menambahkan entri baru di CHANGELOG.md.
     *
     * @param string $jenisKenaikan Jenis kenaikan ('major', 'minor', atau 'patch')
     * @param string $ringkasan     Deskripsi perubahan untuk dicatat ke CHANGELOG.md
     * @return int Kode keluar (0 = Berhasil, 1 = Gagal)
     */
    public function jalankanKenaikan(string $jenisKenaikan, string $ringkasan): int
    {
        if (!is_file($this->jalurFileVersi)) {
            echo "[GAGAL] File VERSION tidak ditemukan di: {$this->jalurFileVersi}\n";
            return 1;
        }

        $versiSaatIni = trim((string) file_get_contents($this->jalurFileVersi));
        if (!preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $versiSaatIni, $bagianVersi)) {
            echo "[GAGAL] Format VERSION saat ini tidak valid ({$versiSaatIni}). Harus MAJOR.MINOR.PATCH.\n";
            return 1;
        }

        $angkaMajor = (int) $bagianVersi[1];
        $angkaMinor = (int) $bagianVersi[2];
        $angkaPatch = (int) $bagianVersi[3];

        $jenisBersih = strtolower(trim($jenisKenaikan));
        if ($jenisBersih === 'major') {
            $angkaMajor++;
            $angkaMinor = 0;
            $angkaPatch = 0;
        } elseif ($jenisBersih === 'minor') {
            $angkaMinor++;
            $angkaPatch = 0;
        } elseif ($jenisBersih === 'patch') {
            $angkaPatch++;
        } else {
            echo "[GAGAL] Jenis kenaikan harus salah satu dari: major | minor | patch\n";
            return 1;
        }

        $versiBaru = sprintf('%d.%d.%d', $angkaMajor, $angkaMinor, $angkaPatch);
        $tanggalHariIni = date('Y-m-d');
        $catatanBersih = trim($ringkasan) !== '' ? trim($ringkasan) : 'Pembaruan sistem dan dokumentasi.';

        // 1. Perbarui file VERSION (Single Source of Truth)
        file_put_contents($this->jalurFileVersi, $versiBaru . "\n");

        // 2. Sisipkan entri versi baru ke dalam CHANGELOG.md
        if (is_file($this->jalurFileChangelog)) {
            $isiChangelog = (string) file_get_contents($this->jalurFileChangelog);
            $blokBaru = sprintf(
                "---\n\n## [%s] - %s\n\n### Diperbarui (Changed)\n- %s\n",
                $versiBaru,
                $tanggalHariIni,
                $catatanBersih
            );

            $posisiGaris = strpos($isiChangelog, '---');
            if ($posisiGaris !== false) {
                $isiChangelogBaru = substr($isiChangelog, 0, $posisiGaris)
                    . $blokBaru . "\n"
                    . substr($isiChangelog, $posisiGaris);
                file_put_contents($this->jalurFileChangelog, $isiChangelogBaru);
            }
        }

        echo "======================================================================\n";
        echo "  KENAIKAN VERSI (BUMP VERSION) BERHASIL - BANK MINI                  \n";
        echo "======================================================================\n";
        echo "Versi Sebelumnya : {$versiSaatIni}\n";
        echo "Versi Baru       : {$versiBaru} ({$tanggalHariIni})\n";
        echo "Catatan Rilis    : {$catatanBersih}\n";
        echo "======================================================================\n";
        return 0;
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $argumenJenis = $argv[1] ?? '';
    $argumenPesan = $argv[2] ?? '';

    if ($argumenJenis === '') {
        echo "Penggunaan: php naikkan_versi.php <major|minor|patch> \"Ringkasan perubahan\"\n";
        exit(1);
    }

    $akarProjek = dirname(__DIR__, 3);
    $penaik = new PenaikVersiAplikasi($akarProjek);
    exit($penaik->jalankanKenaikan($argumenJenis, $argumenPesan));
}
