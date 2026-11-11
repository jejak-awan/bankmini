<?php

declare(strict_types=1);

namespace Pengujian\Unit;

use Aplikasi\Inti\BasisData;
use Aplikasi\Inti\PembatasLaju;
use Aplikasi\Inti\PenanganGalat;
use Aplikasi\Inti\PencatatLog;
use Aplikasi\Inti\PengecualianHttp;
use Aplikasi\Inti\Sesi;
use Aplikasi\Layanan\LayananAutentikasi;
use Aplikasi\Model\ModelLogAudit;
use Aplikasi\Model\ModelPengguna;
use Aplikasi\Pengendali\PengendaliPublik;
use DomainException;
use PDO;
use Pengujian\PembantuPengujian\KasusUjiDasar;
use RuntimeException;

/**
 * ModelPenggunaUjiLaju
 *
 * Tiruan ModelPengguna minimal di memori untuk menguji proteksi brute-force di LayananAutentikasi.
 */
final class ModelPenggunaUjiLaju extends ModelPengguna
{
    /**
     * Konstruktor tanpa koneksi database.
     */
    public function __construct()
    {
    }

    /**
     * Mengembalikan data pengguna uji deterministik.
     *
     * @param string $namaPengguna Nama pengguna
     * @return array<string, mixed>|null Data pengguna
     */
    public function ambilBerdasarkanNamaPengguna(string $namaPengguna): ?array
    {
        if ($namaPengguna !== 'teller_uji') {
            return null;
        }

        return [
            'id'              => 99,
            'nama_pengguna'   => 'teller_uji',
            'hash_kata_sandi' => password_hash('sandiBenar99', PASSWORD_BCRYPT),
            'nama_lengkap'    => 'Teller Uji Fondasi',
            'peran'           => 'teller',
            'status'          => 'aktif',
        ];
    }
}

/**
 * ModelAuditUjiLaju
 *
 * Tiruan ModelLogAudit di memori tanpa menyentuh MySQL.
 */
final class ModelAuditUjiLaju extends ModelLogAudit
{
    /**
     * Konstruktor tanpa koneksi database.
     */
    public function __construct()
    {
    }

    /**
     * Mengembalikan ID log fiktif.
     *
     * @param array<string, mixed> $dataAudit Data audit
     * @return int ID log
     */
    public function catatLog(array $dataAudit): int
    {
        return 1;
    }
}

/**
 * TesFondasiSistem
 *
 * Menguji fondasi v1.8.0: PencatatLog + redaksi rahasia, PenanganGalat (debug vs produksi & 503),
 * PembatasLaju anti brute-force, Idle Timeout Sesi, dan Health Check `/kesehatan`.
 */
class TesFondasiSistem extends KasusUjiDasar
{
    /**
     * @skenario QA-FND-001
     * Memastikan PencatatLog membuat berkas log harian dengan Correlation ID dan menyensor kata kunci rahasia.
     *
     * @return void
     */
    public function ujiPencatatLogRotasiHarianDanRedaksiRahasia(): void
    {
        $direktoriUji = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bankmini_log_' . bin2hex(random_bytes(4));
        mkdir($direktoriUji, 0775, true);

        try {
            PencatatLog::aturDirektoriLogPengujian($direktoriUji);
            PencatatLog::aturIdKorelasi('req-uji-001');

            PencatatLog::keamanan('Percobaan login gagal', [
                'nama_pengguna' => 'admin',
                'kata_sandi'    => 'rahasiaSuper123',
                'token_csrf'    => 'abc999xyz',
            ]);

            $berkasLog = $direktoriUji . DIRECTORY_SEPARATOR . 'aplikasi-' . date('Y-m-d') . '.log';
            $this->pastikanBenar(is_file($berkasLog), 'File log harian harus dibuat.');

            $isiLog = (string) file_get_contents($berkasLog);
            $this->pastikanBenar(str_contains($isiLog, '[KEAMANAN]'), 'Harus berisi tingkat KEAMANAN.');
            $this->pastikanBenar(str_contains($isiLog, '[id:req-uji-001]'), 'Harus berisi correlation ID.');
            $this->pastikanBenar(str_contains($isiLog, '[DIRAHASIAKAN]'), 'Harus berisi redaksi rahasia.');
            $this->pastikanBenar(!str_contains($isiLog, 'rahasiaSuper123'), 'Kata sandi wajib disensor.');
            $this->pastikanBenar(!str_contains($isiLog, 'abc999xyz'), 'Token CSRF wajib disensor.');
        } finally {
            PencatatLog::aturDirektoriLogPengujian(null);
            PencatatLog::aturIdKorelasi(null);
            $this->hapusDirektoriRekursif($direktoriUji);
        }
    }

    /**
     * @skenario QA-FND-002
     * Memastikan PenanganGalat menyembunyikan detail teknis saat produksi dan menampilkannya saat debug.
     *
     * @return void
     */
    public function ujiPenanganGalatModeDebugDanProduksi(): void
    {
        $galatSql = new RuntimeException('SQLSTATE[42S02]: Table rahasia_bank missing');
        $dataProduksi = PenanganGalat::bangunDataTampilanGalat($galatSql, false);

        $this->pastikanSama(500, $dataProduksi['kodeHttp']);
        $this->pastikanSama(false, $dataProduksi['modeDebug']);
        $this->pastikanSama(null, $dataProduksi['detailDebug']);
        $this->pastikanBenar(
            !str_contains((string) $dataProduksi['pesanRamah'], 'SQLSTATE'),
            'Mode produksi tidak boleh membocorkan pesan SQL ke pengguna.'
        );

        $dataDebug = PenanganGalat::bangunDataTampilanGalat($galatSql, true);
        $this->pastikanSama(true, $dataDebug['modeDebug']);
        $this->pastikanBenar(is_array($dataDebug['detailDebug']), 'Mode debug wajib memuat detail pengecualian.');
        $this->pastikanBenar(str_contains((string) $dataDebug['detailDebug']['pesan'], 'SQLSTATE'));

        $galatRbac = new PengecualianHttp(403, 'Akses Ditolak (403)', 'Khusus Supervisor.');
        $dataRbac = PenanganGalat::bangunDataTampilanGalat($galatRbac, false);
        $this->pastikanSama(403, $dataRbac['kodeHttp']);
        $this->pastikanSama('Khusus Supervisor.', $dataRbac['pesanRamah']);
    }

    /**
     * @skenario QA-FND-003
     * Memastikan mode pemeliharaan melempar HTTP 503 kecuali untuk endpoint /kesehatan.
     *
     * @return void
     */
    public function ujiModePemeliharaanMelemparHttp503KecualiKesehatan(): void
    {
        $berkasBendera = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bankmini_maint_' . bin2hex(random_bytes(4)) . '.flag';
        file_put_contents($berkasBendera, 'maintenance');

        try {
            $this->pastikanBenar(PenanganGalat::apakahModePemeliharaan($berkasBendera));

            // Endpoint /kesehatan harus tetap lolos agar monitoring Docker tidak error saat pemeliharaan
            PenanganGalat::periksaModePemeliharaan('/kesehatan', $berkasBendera);

            $this->pastikanMelemparPengecualian(
                PengecualianHttp::class,
                function () use ($berkasBendera): void {
                    PenanganGalat::periksaModePemeliharaan('/dasbor', $berkasBendera);
                }
            );
        } finally {
            if (is_file($berkasBendera)) {
                unlink($berkasBendera);
            }
        }
    }

    /**
     * @skenario QA-FND-004
     * Memastikan PembatasLaju memblokir percobaan login brute-force setelah 5 kali gagal lalu reset saat sukses.
     *
     * @return void
     */
    public function ujiPembatasLajuMemblokirBruteForceDanResetSaatBerhasil(): void
    {
        $direktoriUji = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bankmini_laju_' . bin2hex(random_bytes(4));
        $direktoriLog = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bankmini_log_laju_' . bin2hex(random_bytes(4));
        mkdir($direktoriUji, 0775, true);
        mkdir($direktoriLog, 0775, true);
        $_SESSION = [];

        try {
            PencatatLog::aturDirektoriLogPengujian($direktoriLog);
            $pembatas = new PembatasLaju($direktoriUji, 5, 300);
            $layanan = new LayananAutentikasi(new ModelPenggunaUjiLaju(), new ModelAuditUjiLaju(), $pembatas);

            for ($percobaan = 1; $percobaan <= 5; $percobaan++) {
                $this->pastikanMelemparPengecualian(
                    DomainException::class,
                    function () use ($layanan): void {
                        $layanan->prosesMasuk('teller_uji', 'sandiSalah', '10.0.0.8');
                    }
                );
            }

            $kunci = 'masuk|teller_uji|10.0.0.8';
            $this->pastikanBenar(
                $pembatas->ambilSisaDetikBlokir($kunci) > 0,
                'Percobaan gagal ke-5 harus memicu blokir sementara.'
            );

            // Percobaan ke-6 meskipun kata sandi benar wajib ditolak oleh pembatas laju
            try {
                $layanan->prosesMasuk('teller_uji', 'sandiBenar99', '10.0.0.8');
                $this->pastikanBenar(false, 'Seharusnya diblokir pembatas laju.');
            } catch (DomainException $galatBlokir) {
                $this->pastikanBenar(
                    str_contains($galatBlokir->getMessage(), 'Terlalu banyak percobaan masuk gagal')
                );
            }

            // Setelah reset manual atau kadaluwarsa, login valid harus sukses kembali
            $pembatas->reset($kunci);
            $profil = $layanan->prosesMasuk('teller_uji', 'sandiBenar99', '10.0.0.8');
            $this->pastikanSama(99, $profil['id']);
        } finally {
            PencatatLog::aturDirektoriLogPengujian(null);
            $this->hapusDirektoriRekursif($direktoriUji);
            $this->hapusDirektoriRekursif($direktoriLog);
        }
    }

    /**
     * @skenario QA-FND-005
     * Memastikan Sesi::periksaKedaluwarsaSesi menghancurkan sesi apabila idle melebihi batas waktu.
     *
     * @return void
     */
    public function ujiSesiIdleTimeoutMenghancurkanSesiKedaluwarsa(): void
    {
        $_SESSION = ['id_pengguna' => 5, 'peran_pengguna' => 'teller'];

        $masihAktif = Sesi::periksaKedaluwarsaSesi(1800, 1000);
        $this->pastikanBenar($masihAktif);
        $this->pastikanSama(1000, $_SESSION['waktu_aktivitas_terakhir']);

        $sesudah15Menit = Sesi::periksaKedaluwarsaSesi(1800, 1900);
        $this->pastikanBenar($sesudah15Menit);

        $sesudah40Menit = Sesi::periksaKedaluwarsaSesi(1800, 4000);
        $this->pastikanBenar(!$sesudah40Menit, 'Idle lebih dari 30 menit wajib mengakhiri sesi.');
        $this->pastikanSesiKosong();
    }

    /**
     * @skenario QA-FND-006
     * Memastikan PengendaliPublik::susunStatusKesehatan mengembalikan status siap tanpa membocorkan kredensial DB.
     *
     * @return void
     */
    public function ujiEndpointKesehatanMengembalikanStatusKomponenTanpaKredensial(): void
    {
        $pdoSqlite = new PDO('sqlite::memory:');
        $direktoriLogUji = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bankmini_health_' . bin2hex(random_bytes(4));
        mkdir($direktoriLogUji, 0775, true);

        try {
            BasisData::aturKoneksiPengujian($pdoSqlite);
            $pengendali = new PengendaliPublik();
            $laporan = $pengendali->susunStatusKesehatan($direktoriLogUji);

            $this->pastikanSama('siap', $laporan['pemeriksaan']['basis_data']);
            $this->pastikanSama('siap_tulis', $laporan['pemeriksaan']['penyimpanan_log']);
            $this->pastikanSama('sehat', $laporan['status']);

            $teksJson = json_encode($laporan) ?: '';
            $this->pastikanBenar(!str_contains($teksJson, 'kata_sandi'), 'Health check dilarang memuat kredensial.');
        } finally {
            BasisData::aturKoneksiPengujian(null);
            $this->hapusDirektoriRekursif($direktoriLogUji);
        }
    }

    /**
     * Memastikan array sesi benar-benar kosong setelah sesi dihancurkan.
     *
     * @return void
     */
    private function pastikanSesiKosong(): void
    {
        $this->pastikanSama([], $_SESSION);
    }

    /**
     * Membersihkan folder pengujian sementara secara aman.
     *
     * @param string $direktori Jalur folder sementara
     * @return void
     */
    private function hapusDirektoriRekursif(string $direktori): void
    {
        if (!is_dir($direktori)) {
            return;
        }

        $daftarItem = scandir($direktori);
        if (is_array($daftarItem)) {
            foreach ($daftarItem as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $jalurItem = $direktori . DIRECTORY_SEPARATOR . $item;
                if (is_dir($jalurItem)) {
                    $this->hapusDirektoriRekursif($jalurItem);
                } else {
                    @unlink($jalurItem);
                }
            }
        }

        @rmdir($direktori);
    }
}
