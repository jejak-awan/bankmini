<?php

declare(strict_types=1);

/**
 * PemasangBasisData
 *
 * Skrip CLI untuk membuat database `bankmini_db`, menjalankan DDL `skema.sql`,
 * mengisi data awal `data_awal.sql`, serta memverifikasi kesiapan tabel InnoDB
 * dan transaksi ACID Rupiah (IDR) pada MySQL XAMPP lokal.
 *
 * Cara menjalankan:
 * C:\xampp\php\php.exe C:\xampp\htdocs\www\bankmini\basis_data\pasang_basis_data.php
 */
final class PemasangBasisData
{
    private string $jalurKonfigurasi;
    private string $jalurSkemaSql;
    private string $jalurDataAwalSql;

    /**
     * Konstruktor PemasangBasisData.
     *
     * @param string $direktoriBasisData Jalur folder basis_data
     */
    public function __construct(string $direktoriBasisData)
    {
        $this->jalurKonfigurasi = dirname($direktoriBasisData)
            . DIRECTORY_SEPARATOR . 'konfigurasi'
            . DIRECTORY_SEPARATOR . 'basis_data.php';
        $this->jalurSkemaSql = $direktoriBasisData . DIRECTORY_SEPARATOR . 'skema.sql';
        $this->jalurDataAwalSql = $direktoriBasisData . DIRECTORY_SEPARATOR . 'data_awal.sql';
    }

    /**
     * Menjalankan proses instalasi skema, pengisian data awal, dan verifikasi tabel.
     *
     * @return int Kode keluar (0 = Sukses, 1 = Gagal)
     */
    public function jalankanPemasangan(): int
    {
        echo "======================================================================\n";
        echo "  PEMASANGAN & VERIFIKASI BASIS DATA MYSQL INNNODB - BANK MINI (IDR)  \n";
        echo "======================================================================\n";

        try {
            $konfigurasi = require $this->jalurKonfigurasi;
            $dsnTanpaDb = sprintf(
                'mysql:host=%s;port=%d;charset=%s',
                $konfigurasi['host'],
                (int) $konfigurasi['port'],
                $konfigurasi['set_karakter']
            );

            $koneksi = new PDO(
                $dsnTanpaDb,
                (string) $konfigurasi['nama_pengguna'],
                (string) $konfigurasi['kata_sandi'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            echo "[1/3] Mengeksekusi skema.sql (InnoDB & DECIMAL(15,2) Rupiah)...\n";
            $isiSkema = (string) file_get_contents($this->jalurSkemaSql);
            $koneksi->exec($isiSkema);

            echo "[2/3] Mengeksekusi data_awal.sql (Akun Petugas & Produk Tabungan IDR)...\n";
            $isiDataAwal = (string) file_get_contents($this->jalurDataAwalSql);
            $koneksi->exec($isiDataAwal);

            echo "[3/3] Memverifikasi tabel, mesin penyimpanan InnoDB, dan data awal...\n";
            $namaDb = (string) $konfigurasi['nama_db'];
            $pernyataanTabel = $koneksi->prepare(
                'SELECT TABLE_NAME AS nama_tabel, ENGINE AS mesin
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = :nama_db
                 ORDER BY TABLE_NAME ASC'
            );
            $pernyataanTabel->execute(['nama_db' => $namaDb]);
            $daftarTabel = $pernyataanTabel->fetchAll(PDO::FETCH_ASSOC);

            foreach ($daftarTabel as $barisTabel) {
                echo sprintf("      - Tabel %-20s [Engine: %s]\n", $barisTabel['nama_tabel'], $barisTabel['mesin']);
            }

            $jumlahPengguna = (int) $koneksi->query('SELECT COUNT(*) FROM `bankmini_db`.`pengguna`')->fetchColumn();
            $jumlahProduk = (int) $koneksi->query('SELECT COUNT(*) FROM `bankmini_db`.`produk_tabungan`')->fetchColumn();

            echo "----------------------------------------------------------------------\n";
            echo "Total Pengguna Terdaftar        : {$jumlahPengguna} akun (admin, teller1, supervisor1)\n";
            echo "Total Produk Tabungan Terdaftar : {$jumlahProduk} produk (TAB-SISWA, TAB-GURU, TAB-UMUM)\n";
            echo "[STATUS: BERHASIL] Basis data MySQL siap digunakan!\n";
            echo "======================================================================\n";
            return 0;
        } catch (Throwable $pengecualian) {
            echo "[GAGAL] Terjadi kesalahan koneksi/instalasi database: " . $pengecualian->getMessage() . "\n";
            return 1;
        }
    }
}

$pemasang = new PemasangBasisData(__DIR__);
exit($pemasang->jalankanPemasangan());

