<?php

declare(strict_types=1);

/**
 * GerbangHandoverAgen
 *
 * Skrip Gate Onboarding (Entry Gate) & Validasi Serah Terima (Handover Gate)
 * untuk memastikan setiap agen baru yang masuk ke sesi kerja memiliki konteks
 * yang 100% utuh, konsisten, dan terverifikasi.
 *
 * Cara menjalankan:
 * php C:\xampp\htdocs\www\dokumentasi\06-aturan-agen\skrip\gerbang_handover_agen.php
 */
final class GerbangHandoverAgen
{
    private string $jalurDokumentasi;
    private string $jalurAplikasi;
    private string $jalurFileHandover;

    /**
     * Konstruktor GerbangHandoverAgen.
     *
     * @param string $jalurDokumentasi Jalur absolut ke folder dokumentasi
     * @param string $jalurAplikasi    Jalur absolut ke folder aplikasi bankmini
     */
    public function __construct(string $jalurDokumentasi, string $jalurAplikasi)
    {
        $this->jalurDokumentasi = rtrim($jalurDokumentasi, '\\/');
        $this->jalurAplikasi = rtrim($jalurAplikasi, '\\/');
        $this->jalurFileHandover = $this->jalurDokumentasi
            . DIRECTORY_SEPARATOR . '06-aturan-agen'
            . DIRECTORY_SEPARATOR . 'status-handover-agen.md';
    }

    /**
     * Menjalankan pemeriksaan Gate Handover dan menampilkan ringkasan konteks untuk Agen.
     *
     * @return int Kode keluar (0 = Siap & Konsisten, 1 = Handover Tidak Valid)
     */
    public function jalankanGerbang(): int
    {
        echo "======================================================================\n";
        echo "  GERBANG HANDOVER & ONBOARDING AGEN BARU - PROJEK BANK MINI (IDR)    \n";
        echo "======================================================================\n";

        if (!is_file($this->jalurFileHandover)) {
            echo "[GAGAL] File SSOT Handover tidak ditemukan: {$this->jalurFileHandover}\n";
            return 1;
        }

        $isiHandover = (string) file_get_contents($this->jalurFileHandover);
        $daftarBagianWajib = [
            '## 1. Ringkasan Konteks Wajib',
            '## 2. Komponen yang Sudah Selesai',
            '## 3. Tugas Aktif / Sedang Dikerjakan',
            '## 4. Langkah Selanjutnya (Prioritas Agen Berikutnya)',
            '## 5. Catatan Konteks Penting & Keputusan Terakhir',
        ];

        $daftarKekurangan = [];
        foreach ($daftarBagianWajib as $bagian) {
            if (!str_contains($isiHandover, $bagian)) {
                $daftarKekurangan[] = "Bagian wajib '{$bagian}' tidak ditemukan di status-handover-agen.md";
            }
        }

        // Pastikan Work Log terakhir yang dirujuk di status-handover-agen.md benar-benar ada
        if (preg_match('/Work Log Terakhir\*\*:\s*\[`?([^`\]]+\.md)`?\]/', $isiHandover, $cocokLog)) {
            $namaLogTerakhir = $cocokLog[1];
            $jalurLogTerakhir = $this->jalurDokumentasi . DIRECTORY_SEPARATOR . '04-work-log' . DIRECTORY_SEPARATOR . $namaLogTerakhir;
            if (!is_file($jalurLogTerakhir)) {
                $daftarKekurangan[] = "Work Log terakhir yang dirujuk ({$namaLogTerakhir}) tidak ditemukan di 04-work-log/.";
            }
        } else {
            $daftarKekurangan[] = "Referensi 'Work Log Terakhir' belum tercantum dengan benar di status-handover-agen.md.";
        }

        if (count($daftarKekurangan) > 0) {
            echo "[STATUS GATE: TIDAK VALID]\n";
            foreach ($daftarKekurangan as $pesan) {
                echo "  - {$pesan}\n";
            }
            return 1;
        }

        // Hitung inventaris file di bankmini
        $jumlahFileAplikasi = is_dir($this->jalurAplikasi) ? count($this->ambilSemuaFilePhp($this->jalurAplikasi)) : 0;

        echo "[STATUS GATE: SIAP & TERVERIFIKASI]\n";
        echo "Total File PHP di bankmini : {$jumlahFileAplikasi} file\n";
        echo "----------------------------------------------------------------------\n";
        echo $isiHandover . "\n";
        echo "======================================================================\n";
        return 0;
    }

    /**
     * Mengambil seluruh jalur file .php di dalam direktori secara rekursif.
     *
     * @param string $direktori Jalur direktori
     * @return array<int, string> Daftar file PHP
     */
    private function ambilSemuaFilePhp(string $direktori): array
    {
        $hasil = [];
        $folderDikecualikan = ['vendor', 'node_modules', '.git', 'test-results', 'playwright-report'];
        $iteratorDirektori = new RecursiveDirectoryIterator($direktori, FilesystemIterator::SKIP_DOTS);
        $filterIterator = new RecursiveCallbackFilterIterator(
            $iteratorDirektori,
            static function (SplFileInfo $saatIni) use ($folderDikecualikan): bool {
                if ($saatIni->isDir() && in_array($saatIni->getFilename(), $folderDikecualikan, true)) {
                    return false;
                }
                return true;
            }
        );
        $iterator = new RecursiveIteratorIterator($filterIterator);

        foreach ($iterator as $infoFile) {
            if ($infoFile instanceof SplFileInfo && strtolower($infoFile->getExtension()) === 'php') {
                $hasil[] = $infoFile->getPathname();
            }
        }

        return $hasil;
    }
}

$jalurDokumentasiDefault = dirname(__DIR__, 2);
$indukDokumentasi = dirname($jalurDokumentasiDefault);
$jalurAplikasiDefault = basename($indukDokumentasi) === 'bankmini'
    ? $indukDokumentasi
    : $indukDokumentasi . DIRECTORY_SEPARATOR . 'bankmini';

$gerbang = new GerbangHandoverAgen($jalurDokumentasiDefault, $jalurAplikasiDefault);
exit($gerbang->jalankanGerbang());

