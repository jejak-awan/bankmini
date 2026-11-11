<?php

declare(strict_types=1);

/**
 * PemeriksaKepatuhanAgen
 *
 * @konsep_rpl Penjaga Kualitas Otomatis (Automated Quality Gate & Static Analysis)
 * Skrip otomatis penjaga konsistensi arsitektur (SoT & SoC), konvensi penamaan
 * dan anotasi edukasi Bahasa Indonesia (@konsep_rpl), keamanan finansial Rupiah (ACID),
 * manajemen versi (VERSION, CHANGELOG.md, LICENSE), sinkronisasi dokumentasi,
 * serta kepatuhan pembuatan file tes QA pada projek Bank Mini.
 *
 * Cara menjalankan:
 * php C:\xampp\htdocs\www\bankmini\dokumentasi\06-aturan-agen\skrip\periksa_kepatuhan_agen.php
 */
final class PemeriksaKepatuhanAgen
{
    private string $jalurDokumentasi;
    private string $jalurAplikasi;

    /** @var array<int, string> Daftar pesan pelanggaran aturan yang wajib diperbaiki */
    private array $daftarPelanggaran = [];

    /** @var array<int, string> Daftar peringatan / informasi pemeriksaan */
    private array $daftarCatatan = [];

    private int $jumlahPemeriksaan = 0;

    /**
     * Konstruktor PemeriksaKepatuhanAgen.
     *
     * @param string $jalurDokumentasi Jalur absolut ke folder dokumentasi
     * @param string $jalurAplikasi    Jalur absolut ke folder aplikasi bankmini
     */
    public function __construct(string $jalurDokumentasi, string $jalurAplikasi)
    {
        $this->jalurDokumentasi = rtrim($jalurDokumentasi, '\\/');
        $this->jalurAplikasi = rtrim($jalurAplikasi, '\\/');
    }

    /**
     * Menjalankan seluruh rangkaian pemeriksaan konsistensi projek.
     *
     * @return int Kode status keluar (0 = Lulus Semua, 1 = Ada Pelanggaran)
     */
    public function jalankanPemeriksaan(): int
    {
        $this->cetakJudul();

        $this->periksaKelengkapanDokumentasi();
        $this->periksaSinkronisasiIndeksAdrDanWorkLog();
        $this->periksaVersiChangelogDanLisensi();
        $this->periksaStrukturFolderAplikasi();
        $this->periksaSkemaBasisData();
        $this->periksaKodePhpDanBatasSoc();
        $this->periksaKepatuhanFileTesQa();

        return $this->cetakLaporanAkhir();
    }

    /**
     * 1. Memeriksa keberadaan folder dan file inti di repositori dokumentasi.
     *
     * @return void
     */
    private function periksaKelengkapanDokumentasi(): void
    {
        $fileDokumentasiWajib = [
            'README.md',
            '01-panduan-pengguna/README.md',
            '01-panduan-pengguna/panduan-admin.md',
            '01-panduan-pengguna/panduan-teller.md',
            '01-panduan-pengguna/panduan-nasabah.md',
            '02-panduan-dev/README.md',
            '02-panduan-dev/arsitektur-sistem.md',
            '02-panduan-dev/dfd-dan-alur-sistem.md',
            '02-panduan-dev/konvensi-penamaan-dan-anotasi.md',
            '02-panduan-dev/standar-kode-dan-keamanan.md',
            '02-panduan-dev/skema-database.md',
            '02-panduan-dev/standar-pengujian-qa.md',
            '02-panduan-dev/manajemen-versi-lisensi-dan-changelog.md',
            '02-panduan-dev/glosarium-dan-daftar-istilah.md',
            '02-panduan-dev/standar-frontend-ui-ux.md',
            '03-adr/README.md',
            '03-adr/ADR-001-php-native-mvc-lite.md',
            '03-adr/ADR-002-integritas-transaksi-keuangan.md',
            '03-adr/ADR-003-standar-keamanan-aplikasi-keuangan.md',
            '03-adr/ADR-004-konvensi-penamaan-bahasa-indonesia.md',
            '03-adr/ADR-005-standar-qa-dan-skrip-penjaga-konsistensi.md',
            '03-adr/ADR-006-manajemen-versi-changelog-dan-lisensi.md',
            '03-adr/ADR-007-arsitektur-frontend-dan-standar-ui-ux.md',
            '04-work-log/README.md',
            '05-template-dokumentasi/template-adr.md',
            '05-template-dokumentasi/template-work-log.md',
            '05-template-dokumentasi/template-spesifikasi-fitur.md',
            '05-template-dokumentasi/template-panduan.md',
            '05-template-dokumentasi/template-skenario-qa.md',
            '05-template-dokumentasi/template-handover-agen.md',
            '06-aturan-agen/README.md',
            '06-aturan-agen/aturan-koding-php-bankmini.md',
            '06-aturan-agen/aturan-pengujian-qa.md',
            '06-aturan-agen/alur-kerja-dokumentasi.md',
            '06-aturan-agen/protokol-handover-dan-gate-agen.md',
            '06-aturan-agen/status-handover-agen.md',
            '06-aturan-agen/skrip/naikkan_versi.php',
        ];

        foreach ($fileDokumentasiWajib as $jalurRelatif) {
            $this->jumlahPemeriksaan++;
            $jalurLengkap = $this->jalurDokumentasi . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $jalurRelatif);
            if (!is_file($jalurLengkap)) {
                $this->catatPelanggaran("[DOKUMENTASI] File dokumentasi wajib tidak ditemukan: {$jalurRelatif}");
            }
        }
    }

    /**
     * 2. Memastikan setiap file ADR dan Work Log terdaftar pada README.md masing-masing.
     *
     * @return void
     */
    private function periksaSinkronisasiIndeksAdrDanWorkLog(): void
    {
        // Periksa sinkronisasi ADR
        $jalurReadmeAdr = $this->jalurDokumentasi . DIRECTORY_SEPARATOR . '03-adr' . DIRECTORY_SEPARATOR . 'README.md';
        if (is_file($jalurReadmeAdr)) {
            $isiReadmeAdr = (string) file_get_contents($jalurReadmeAdr);
            $daftarFileAdr = glob($this->jalurDokumentasi . DIRECTORY_SEPARATOR . '03-adr' . DIRECTORY_SEPARATOR . 'ADR-*.md') ?: [];
            foreach ($daftarFileAdr as $fileAdr) {
                $this->jumlahPemeriksaan++;
                $namaFile = basename($fileAdr);
                if (!str_contains($isiReadmeAdr, $namaFile)) {
                    $this->catatPelanggaran("[SINKRONISASI ADR] File {$namaFile} belum didaftarkan di 03-adr/README.md");
                }
            }
        }

        // Periksa sinkronisasi Work Log
        $jalurReadmeLog = $this->jalurDokumentasi . DIRECTORY_SEPARATOR . '04-work-log' . DIRECTORY_SEPARATOR . 'README.md';
        if (is_file($jalurReadmeLog)) {
            $isiReadmeLog = (string) file_get_contents($jalurReadmeLog);
            $daftarFileLog = glob($this->jalurDokumentasi . DIRECTORY_SEPARATOR . '04-work-log' . DIRECTORY_SEPARATOR . '*.md') ?: [];
            foreach ($daftarFileLog as $fileLog) {
                $namaFile = basename($fileLog);
                if ($namaFile === 'README.md') {
                    continue;
                }
                $this->jumlahPemeriksaan++;
                if (!str_contains($isiReadmeLog, $namaFile)) {
                    $this->catatPelanggaran("[SINKRONISASI WORK LOG] File {$namaFile} belum didaftarkan di 04-work-log/README.md");
                }
            }
        }
    }

    /**
     * 2b. Memeriksa keberadaan dan sinkronisasi VERSION, CHANGELOG.md, dan LICENSE.
     *
     * @return void
     */
    private function periksaVersiChangelogDanLisensi(): void
    {
        $jalurVersi = $this->jalurAplikasi . DIRECTORY_SEPARATOR . 'VERSION';
        $jalurChangelog = $this->jalurAplikasi . DIRECTORY_SEPARATOR . 'CHANGELOG.md';
        $jalurLisensi = $this->jalurAplikasi . DIRECTORY_SEPARATOR . 'LICENSE';

        $this->jumlahPemeriksaan += 3;

        if (!is_file($jalurVersi)) {
            $this->catatPelanggaran('[MANAJEMEN VERSI] File VERSION (Single Source of Truth versi) tidak ditemukan di root bankmini.');
        }

        if (!is_file($jalurChangelog)) {
            $this->catatPelanggaran('[MANAJEMEN VERSI] File CHANGELOG.md tidak ditemukan di root bankmini.');
        }

        if (!is_file($jalurLisensi)) {
            $this->catatPelanggaran('[LISENSI] File LICENSE tidak ditemukan di root bankmini.');
        }

        if (is_file($jalurVersi) && is_file($jalurChangelog)) {
            $this->jumlahPemeriksaan++;
            $versiAktif = trim((string) file_get_contents($jalurVersi));
            $isiChangelog = (string) file_get_contents($jalurChangelog);

            if (!preg_match('/^\d+\.\d+\.\d+$/', $versiAktif)) {
                $this->catatPelanggaran("[MANAJEMEN VERSI] Isi file VERSION ('{$versiAktif}') tidak sesuai format SemVer (MAJOR.MINOR.PATCH).");
            } elseif (!str_contains($isiChangelog, "## [{$versiAktif}]")) {
                $this->catatPelanggaran("[MANAJEMEN VERSI] Versi aktif di VERSION ('{$versiAktif}') belum tercatat pada judul rilis '## [{$versiAktif}]' di CHANGELOG.md.");
            }
        }
    }

    /**
     * 3. Memeriksa struktur folder Bahasa Indonesia di C:\xampp\htdocs\www\bankmini.
     *
     * @return void
     */
    private function periksaStrukturFolderAplikasi(): void
    {
        $this->jumlahPemeriksaan++;
        if (!is_dir($this->jalurAplikasi)) {
            $this->catatPelanggaran("[STRUKTUR] Folder projek tidak ditemukan: {$this->jalurAplikasi}");
            return;
        }

        $isiFolderAplikasi = array_diff(scandir($this->jalurAplikasi) ?: [], ['.', '..']);
        if (count($isiFolderAplikasi) === 0) {
            $this->daftarCatatan[] = "Folder aplikasi bankmini ({$this->jalurAplikasi}) masih kosong (belum di-scaffold). Pemeriksaan kode dilewati sementara.";
            return;
        }

        // Larangan nama folder bahasa Inggris di root bankmini
        $folderInggrisTerlarang = [
            'app'         => 'aplikasi',
            'config'      => 'konfigurasi',
            'database'    => 'basis_data',
            'storage'     => 'penyimpanan',
            'tests'       => 'pengujian',
            'test'        => 'pengujian',
            'controllers' => 'aplikasi/Pengendali',
            'models'      => 'aplikasi/Model',
            'views'       => 'aplikasi/Tampilan',
            'services'    => 'aplikasi/Layanan',
            'helpers'     => 'aplikasi/Pembantu',
        ];

        foreach ($isiFolderAplikasi as $item) {
            $jalurItem = $this->jalurAplikasi . DIRECTORY_SEPARATOR . $item;
            if (is_dir($jalurItem)) {
                $this->jumlahPemeriksaan++;
                $kunciKecil = strtolower($item);
                if (isset($folderInggrisTerlarang[$kunciKecil])) {
                    $saran = $folderInggrisTerlarang[$kunciKecil];
                    $this->catatPelanggaran(
                        "[KONVENSI FOLDER] Ditemukan folder berbahasa Inggris '{$item}/' di bankmini. Gunakan Bahasa Indonesia: '{$saran}/'."
                    );
                }
            }
        }

        // Periksa subfolder di dalam aplikasi/ jika aplikasi/ sudah ada
        $jalurSubAplikasi = $this->jalurAplikasi . DIRECTORY_SEPARATOR . 'aplikasi';
        if (is_dir($jalurSubAplikasi)) {
            $subFolderDiizinkan = ['Inti', 'Pengendali', 'Layanan', 'Model', 'Pembantu', 'Tampilan'];
            $isiSubAplikasi = array_diff(scandir($jalurSubAplikasi) ?: [], ['.', '..']);
            foreach ($isiSubAplikasi as $subItem) {
                $jalurSubItem = $jalurSubAplikasi . DIRECTORY_SEPARATOR . $subItem;
                if (is_dir($jalurSubItem)) {
                    $this->jumlahPemeriksaan++;
                    if (!in_array($subItem, $subFolderDiizinkan, true)) {
                        $this->catatPelanggaran(
                            "[KONVENSI FOLDER] Subfolder 'aplikasi/{$subItem}' tidak sesuai standar. Hanya diizinkan: " . implode(', ', $subFolderDiizinkan)
                        );
                    }
                }
            }
        }
    }

    /**
     * 4. Memeriksa file skema SQL (InnoDB, DECIMAL(15,2) Rupiah, tabel Bahasa Indonesia).
     *
     * @return void
     */
    private function periksaSkemaBasisData(): void
    {
        $jalurSkema = $this->jalurAplikasi . DIRECTORY_SEPARATOR . 'basis_data' . DIRECTORY_SEPARATOR . 'skema.sql';
        if (!is_file($jalurSkema)) {
            return;
        }

        $this->jumlahPemeriksaan++;
        $isiSkema = (string) file_get_contents($jalurSkema);

        // Larangan tipe FLOAT atau DOUBLE untuk uang
        if (preg_match('/\b(FLOAT|DOUBLE)\b/i', $isiSkema)) {
            $this->catatPelanggaran(
                "[INTEGRITAS RUPIAH] Ditemukan tipe data FLOAT atau DOUBLE di basis_data/skema.sql! Wajib gunakan DECIMAL(15,2)."
            );
        }

        // Wajib menggunakan ENGINE=InnoDB
        if (!preg_match('/ENGINE\s*=\s*InnoDB/i', $isiSkema)) {
            $this->catatPelanggaran(
                "[ACID DATABASE] basis_data/skema.sql wajib mendefinisikan ENGINE=InnoDB pada tabel."
            );
        }

        // Larangan nama tabel bahasa Inggris
        $tabelInggrisTerlarang = ['users', 'customers', 'accounts', 'transactions', 'saving_products', 'audit_logs'];
        foreach ($tabelInggrisTerlarang as $namaTabelAsing) {
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"\']?' . preg_quote($namaTabelAsing, '/') . '[`"\']?\b/i', $isiSkema)) {
                $this->catatPelanggaran(
                    "[KONVENSI TABEL] Ditemukan tabel berbahasa Inggris '{$namaTabelAsing}' di skema.sql. Gunakan nama tabel Bahasa Indonesia sesuai skema-database.md."
                );
            }
        }
    }

    /**
     * 5. Memeriksa seluruh file PHP di bankmini (strict_types, PHPDoc Indonesia, camelCase, SoC, Keamanan).
     *
     * @return void
     */
    private function periksaKodePhpDanBatasSoc(): void
    {
        if (!is_dir($this->jalurAplikasi)) {
            return;
        }

        $daftarFilePhp = $this->ambilSemuaFilePhp($this->jalurAplikasi);
        foreach ($daftarFilePhp as $jalurFile) {
            $jalurRelatif = str_replace($this->jalurAplikasi . DIRECTORY_SEPARATOR, '', $jalurFile);
            $jalurRelatifNormal = str_replace('\\', '/', $jalurRelatif);
            $isiFile = (string) file_get_contents($jalurFile);

            $apakahFileTampilan = str_starts_with($jalurRelatifNormal, 'aplikasi/Tampilan/');
            $apakahFilePengendali = str_starts_with($jalurRelatifNormal, 'aplikasi/Pengendali/');
            $apakahFileLayanan = str_starts_with($jalurRelatifNormal, 'aplikasi/Layanan/');
            $apakahFileClass = $apakahFilePengendali
                || $apakahFileLayanan
                || str_starts_with($jalurRelatifNormal, 'aplikasi/Inti/')
                || str_starts_with($jalurRelatifNormal, 'aplikasi/Model/');

            // 5a. Pemeriksaan declare(strict_types=1); pada file non-Tampilan
            if (!$apakahFileTampilan) {
                $this->jumlahPemeriksaan++;
                if (!preg_match('/declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', $isiFile)) {
                    $this->catatPelanggaran(
                        "[STANDAR KODE] File '{$jalurRelatifNormal}' wajib memiliki declare(strict_types=1); di awal file."
                    );
                }
            }

            // 5b. Pemeriksaan konvensi nama file class (tidak boleh memakai akhiran Inggris Controller/Service/Test)
            if ($apakahFileClass) {
                $this->jumlahPemeriksaan++;
                $namaFileDasar = basename($jalurFile);
                if (preg_match('/(Controller|Service|Repository|Middleware|Helper)\.php$/', $namaFileDasar)) {
                    $this->catatPelanggaran(
                        "[KONVENSI BAHASA] Nama file '{$jalurRelatifNormal}' masih memakai istilah Inggris. Gunakan awalan Bahasa Indonesia (Pengendali*, Layanan*, Model*, Middleware*)."
                    );
                }
            }

            // 5c. Pemeriksaan Anotasi PHPDoc Bahasa Indonesia & Variabel camelCase menggunakan Tokenizer
            $this->periksaTokenPhp($isiFile, $jalurRelatifNormal, $apakahFileTampilan);

            // 5d. Pemeriksaan Separation of Concerns (SoC) pada Tampilan (View)
            if ($apakahFileTampilan) {
                $this->jumlahPemeriksaan++;
                if (preg_match('/\b(BasisData::|->prepare\s*\(|->query\s*\(|beginTransaction\s*\(|SELECT\s+.*\s+FROM\s+|INSERT\s+INTO\s+|UPDATE\s+.*\s+SET\s+|DELETE\s+FROM\s+)/i', $isiFile)) {
                    $this->catatPelanggaran(
                        "[PELANGGARAN SoC] File Tampilan '{$jalurRelatifNormal}' dilarang keras memuat query SQL atau memanggil BasisData/Model!"
                    );
                }
            }

            // 5e. Pemeriksaan Separation of Concerns (SoC) pada Pengendali (Controller)
            if ($apakahFilePengendali) {
                $this->jumlahPemeriksaan++;
                if (preg_match('/->(prepare|query|beginTransaction|commit|rollBack)\s*\(/i', $isiFile)) {
                    $this->catatPelanggaran(
                        "[PELANGGARAN SoC] File Pengendali '{$jalurRelatifNormal}' dilarang menulis query PDO atau mengelola transaksi DB secara langsung. Gunakan lapisan Layanan/Model."
                    );
                }
            }

            // 5f. Pemeriksaan Separation of Concerns (SoC) pada Layanan (Service)
            if ($apakahFileLayanan) {
                $this->jumlahPemeriksaan++;
                if (preg_match('/\$_(POST|GET|REQUEST)\b/', $isiFile)) {
                    $this->catatPelanggaran(
                        "[PELANGGARAN SoC] File Layanan '{$jalurRelatifNormal}' dilarang mengakses \$_POST/\$_GET secara langsung. Kirimkan data tervalidasi melalui parameter method dari Pengendali."
                    );
                }
            }

            // 5g. Pemeriksaan Keamanan SQL Injection & Immutable Ledger
            $this->jumlahPemeriksaan++;
            if (preg_match('/->(prepare|query)\s*\(\s*"[^"]*\$[a-zA-Z_]/', $isiFile)) {
                $this->catatPelanggaran(
                    "[BAHAYA SQL INJECTION] Ditemukan penggabungan variabel langsung di dalam query SQL pada '{$jalurRelatifNormal}'. Wajib gunakan parameter terikat (:nama_param)."
                );
            }

            if (preg_match('/DELETE\s+FROM\s+[`"\']?mutasi_transaksi/i', $isiFile)) {
                $this->catatPelanggaran(
                    "[PELANGGARAN IMMUTABLE LEDGER] Ditemukan query DELETE pada tabel mutasi_transaksi di '{$jalurRelatifNormal}'. Tabel mutasi bersifat Append-Only!"
                );
            }

            // 5h. Pemeriksaan khusus LayananTransaksi.php wajib memakai beginTransaction, commit, rollBack
            if (basename($jalurFile) === 'LayananTransaksi.php') {
                $this->jumlahPemeriksaan++;
                if (
                    !str_contains($isiFile, 'beginTransaction')
                    || !str_contains($isiFile, 'commit')
                    || !str_contains($isiFile, 'rollBack')
                ) {
                    $this->catatPelanggaran(
                        "[INTEGRITAS ACID] 'aplikasi/Layanan/LayananTransaksi.php' wajib menggunakan beginTransaction(), commit(), dan rollBack()."
                    );
                }
            }
        }
    }

    /**
     * Memeriksa token PHP untuk memastikan anotasi PHPDoc pada class/method dan format variabel $camelCase Bahasa Indonesia.
     *
     * @param string $isiFile            Kode sumber PHP
     * @param string $jalurRelatifNormal Jalur relatif file untuk pelaporan
     * @param bool   $apakahFileTampilan True jika file berada di aplikasi/Tampilan/
     * @return void
     */
    private function periksaTokenPhp(string $isiFile, string $jalurRelatifNormal, bool $apakahFileTampilan): void
    {
        $daftarToken = token_get_all($isiFile);
        $jumlahToken = count($daftarToken);

        $superglobalPhp = [
            '$_GET', '$_POST', '$_SESSION', '$_SERVER', '$_FILES',
            '$_COOKIE', '$_ENV', '$_REQUEST', '$GLOBALS', '$this',
        ];

        $variabelInggrisTerlarang = [
            '$user', '$users', '$account', '$accounts', '$amount',
            '$balance', '$transaction', '$transactions', '$customer',
            '$customers', '$result', '$results', '$stmt', '$conn', '$db',
        ];

        $adaDocCommentSebelumnya = false;
        $isiDocCommentTerakhir = '';

        for ($indeks = 0; $indeks < $jumlahToken; $indeks++) {
            $token = $daftarToken[$indeks];
            if (!is_array($token)) {
                continue;
            }

            [$idToken, $teksToken, $baris] = $token;

            if ($idToken === T_DOC_COMMENT) {
                $adaDocCommentSebelumnya = true;
                $isiDocCommentTerakhir = $teksToken;
                continue;
            }

            if (in_array($idToken, [T_WHITESPACE, T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_FINAL, T_ABSTRACT, T_READONLY], true)) {
                continue;
            }

            // Periksa deklarasi class & function bernama harus punya PHPDoc (kecuali di file Tampilan)
            if (!$apakahFileTampilan && ($idToken === T_CLASS || $idToken === T_FUNCTION)) {
                $idTokenSebelumnya = $this->ambilIdTokenNonSpasiSebelumnya($daftarToken, $indeks);
                $namaIdentifier = $this->ambilNamaIdentifierBerikutnya($daftarToken, $indeks);

                // Abaikan konstanta ::class (T_DOUBLE_COLON) dan closure anonim tanpa nama
                if ($idToken === T_CLASS && $idTokenSebelumnya === T_DOUBLE_COLON) {
                    continue;
                }
                if ($idToken === T_FUNCTION && $namaIdentifier === '') {
                    continue;
                }

                $this->jumlahPemeriksaan++;
                if (!$adaDocCommentSebelumnya) {
                    $jenis = $idToken === T_CLASS ? 'Class' : 'Method/Fungsi';
                    $this->catatPelanggaran(
                        "[ANOTASI PHPDOC] {$jenis} '{$namaIdentifier}' pada '{$jalurRelatifNormal}' baris {$baris} belum dilengkapi blok anotasi PHPDoc (/** ... */) Bahasa Indonesia."
                    );
                } else {
                    // Jika ini adalah Class di dalam aplikasi/, wajib memuat anotasi edukasi @konsep_rpl untuk siswa RPL
                    if ($idToken === T_CLASS && str_starts_with($jalurRelatifNormal, 'aplikasi/')) {
                        if (!str_contains($isiDocCommentTerakhir, '@konsep_rpl')) {
                            $this->catatPelanggaran(
                                "[EDUKASI RPL] Class '{$namaIdentifier}' di '{$jalurRelatifNormal}' baris {$baris} wajib mencantumkan anotasi '@konsep_rpl' pada PHPDoc-nya sebagai bahan belajar siswa RPL."
                            );
                        }
                    }

                    // Jika ini adalah method uji...() di dalam file tes QA, pastikan ada anotasi @skenario
                    if (
                        str_starts_with($jalurRelatifNormal, 'pengujian/Unit/')
                        || str_starts_with($jalurRelatifNormal, 'pengujian/Integrasi/')
                        || str_starts_with($jalurRelatifNormal, 'pengujian/Keamanan/')
                    ) {
                        if ($idToken === T_FUNCTION) {
                            if (str_starts_with($namaIdentifier, 'uji') && !str_contains($isiDocCommentTerakhir, '@skenario')) {
                                $this->catatPelanggaran(
                                    "[STANDAR QA] Method uji '{$namaIdentifier}()' di '{$jalurRelatifNormal}' baris {$baris} wajib mencantumkan anotasi '@skenario' pada PHPDoc-nya."
                                );
                            }
                        }
                    }
                }
                $adaDocCommentSebelumnya = false;
                continue;
            }

            // Reset flag PHPDoc jika bertemu token lain
            $adaDocCommentSebelumnya = false;

            // Periksa variabel $camelCase Bahasa Indonesia
            if ($idToken === T_VARIABLE) {
                if (in_array($teksToken, $superglobalPhp, true)) {
                    continue;
                }

                $this->jumlahPemeriksaan++;

                // Cek larangan variabel snake_case (mengandung garis bawah setelah karakter $)
                $namaTanpaDolar = substr($teksToken, 1);
                if (str_contains($namaTanpaDolar, '_')) {
                    $this->catatPelanggaran(
                        "[KONVENSI VARIABEL] Variabel '{$teksToken}' di '{$jalurRelatifNormal}' baris {$baris} menggunakan snake_case. Wajib gunakan \$camelCase Bahasa Indonesia."
                    );
                }

                // Cek larangan variabel bahasa Inggris umum
                if (in_array(strtolower($teksToken), $variabelInggrisTerlarang, true)) {
                    $this->catatPelanggaran(
                        "[KONVENSI BAHASA] Variabel '{$teksToken}' di '{$jalurRelatifNormal}' baris {$baris} menggunakan bahasa Inggris. Gunakan variabel Bahasa Indonesia yang deskriptif."
                    );
                }
            }
        }
    }

    /**
     * 6. Memeriksa kepatuhan pembuatan file tes QA (pasangan 1:1 untuk setiap Layanan & struktur Tes*.php).
     *
     * @return void
     */
    private function periksaKepatuhanFileTesQa(): void
    {
        $jalurLayanan = $this->jalurAplikasi . DIRECTORY_SEPARATOR . 'aplikasi' . DIRECTORY_SEPARATOR . 'Layanan';
        if (!is_dir($jalurLayanan)) {
            return;
        }

        $daftarFileLayanan = glob($jalurLayanan . DIRECTORY_SEPARATOR . 'Layanan*.php') ?: [];
        foreach ($daftarFileLayanan as $fileLayanan) {
            $this->jumlahPemeriksaan++;
            $namaFileLayanan = basename($fileLayanan); // contoh: LayananTransaksi.php
            $namaFileTes = 'Tes' . $namaFileLayanan;   // contoh: TesLayananTransaksi.php

            $jalurTesIntegrasi = $this->jalurAplikasi . DIRECTORY_SEPARATOR . 'pengujian' . DIRECTORY_SEPARATOR . 'Integrasi' . DIRECTORY_SEPARATOR . $namaFileTes;
            $jalurTesUnit = $this->jalurAplikasi . DIRECTORY_SEPARATOR . 'pengujian' . DIRECTORY_SEPARATOR . 'Unit' . DIRECTORY_SEPARATOR . $namaFileTes;

            if (!is_file($jalurTesIntegrasi) && !is_file($jalurTesUnit)) {
                $this->catatPelanggaran(
                    "[KEPATUHAN QA] Komponen 'aplikasi/Layanan/{$namaFileLayanan}' belum memiliki file tes QA pendamping! Buat di 'pengujian/Integrasi/{$namaFileTes}' atau 'pengujian/Unit/{$namaFileTes}'."
                );
                continue;
            }

            // Periksa isi file tes agar memiliki minimal 1 method public function uji...()
            $jalurTesAktif = is_file($jalurTesIntegrasi) ? $jalurTesIntegrasi : $jalurTesUnit;
            $isiFileTes = (string) file_get_contents($jalurTesAktif);
            if (!preg_match('/public\s+function\s+uji[A-Z][a-zA-Z0-9]*\s*\(/', $isiFileTes)) {
                $this->catatPelanggaran(
                    "[KEPATUHAN QA] File tes '{$namaFileTes}' wajib memiliki minimal satu method pengujian dengan format 'public function ujiNamaSkenario()'."
                );
            }
        }
    }

    /**
     * Mengambil ID token non-spasi tepat sebelum posisi indeks saat ini.
     *
     * @param array<int, mixed> $daftarToken Daftar token dari token_get_all
     * @param int               $indeksMulai Indeks posisi token saat ini
     * @return int|null ID token sebelumnya atau null
     */
    private function ambilIdTokenNonSpasiSebelumnya(array $daftarToken, int $indeksMulai): ?int
    {
        for ($i = $indeksMulai - 1; $i >= 0; $i--) {
            if (is_array($daftarToken[$i])) {
                if ($daftarToken[$i][0] === T_WHITESPACE) {
                    continue;
                }
                return (int) $daftarToken[$i][0];
            }
            return null;
        }
        return null;
    }

    /**
     * Mengambil nama identifier (T_STRING) langsung setelah token class atau function.
     * Mengembalikan string kosong jika bertemu '(' lebih dulu (closure anonim).
     *
     * @param array<int, mixed> $daftarToken Daftar token dari token_get_all
     * @param int               $indeksMulai Indeks posisi token saat ini
     * @return string Nama fungsi atau class
     */
    private function ambilNamaIdentifierBerikutnya(array $daftarToken, int $indeksMulai): string
    {
        $jumlah = count($daftarToken);
        for ($i = $indeksMulai + 1; $i < $jumlah; $i++) {
            if ($daftarToken[$i] === '(' || $daftarToken[$i] === '{') {
                return '';
            }
            if (is_array($daftarToken[$i]) && $daftarToken[$i][0] === T_STRING) {
                return (string) $daftarToken[$i][1];
            }
        }
        return '';
    }

    /**
     * Mengambil seluruh jalur file .php di dalam sebuah direktori secara rekursif.
     *
     * @param string $direktori Jalur direktori awal
     * @return array<int, string> Daftar jalur file .php
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

    /**
     * Mencatat temuan pelanggaran aturan ke dalam daftar pelanggaran.
     *
     * @param string $pesan Pesan pelanggaran yang jelas dan dapat ditindaklanjuti
     * @return void
     */
    private function catatPelanggaran(string $pesan): void
    {
        $this->daftarPelanggaran[] = $pesan;
    }

    /**
     * Mencetak header informasi skrip pemeriksa.
     *
     * @return void
     */
    private function cetakJudul(): void
    {
        echo "======================================================================\n";
        echo "  PEMERIKSA KEPATUHAN ATURAN AGEN & STANDAR QA - BANK MINI (IDR)      \n";
        echo "======================================================================\n";
        echo "Lokasi Dokumentasi : {$this->jalurDokumentasi}\n";
        echo "Lokasi Aplikasi    : {$this->jalurAplikasi}\n";
        echo "----------------------------------------------------------------------\n";
    }

    /**
     * Mencetak ringkasan akhir hasil pemeriksaan dan mengembalikan exit code.
     *
     * @return int 0 jika tidak ada pelanggaran, 1 jika ditemukan pelanggaran
     */
    private function cetakLaporanAkhir(): int
    {
        if (count($this->daftarCatatan) > 0) {
            echo "\n[INFO / CATATAN]:\n";
            foreach ($this->daftarCatatan as $catatan) {
                echo "  * {$catatan}\n";
            }
        }

        $totalPelanggaran = count($this->daftarPelanggaran);
        echo "\nTotal Titik Pemeriksaan : {$this->jumlahPemeriksaan}\n";
        echo "Total Pelanggaran       : {$totalPelanggaran}\n";

        if ($totalPelanggaran === 0) {
            echo "\n[STATUS: LULUS 100%] Seluruh aturan SoT, SoC, Konvensi Bahasa Indonesia, ACID Rupiah, & Standar QA terpenuhi!\n";
            echo "======================================================================\n";
            return 0;
        }

        echo "\n[STATUS: GAGAL] Ditemukan {$totalPelanggaran} pelanggaran yang WAJIB diperbaiki oleh Agen:\n";
        foreach ($this->daftarPelanggaran as $nomor => $pelanggaran) {
            $urutan = $nomor + 1;
            echo "  {$urutan}. {$pelanggaran}\n";
        }
        echo "======================================================================\n";
        return 1;
    }
}

// Eksekusi skrip jika dipanggil langsung dari CLI
$jalurDokumentasiDefault = dirname(__DIR__, 2);
$indukDokumentasi = dirname($jalurDokumentasiDefault);
$jalurAplikasiDefault = basename($indukDokumentasi) === 'bankmini'
    ? $indukDokumentasi
    : $indukDokumentasi . DIRECTORY_SEPARATOR . 'bankmini';

$pemeriksa = new PemeriksaKepatuhanAgen($jalurDokumentasiDefault, $jalurAplikasiDefault);
exit($pemeriksa->jalankanPemeriksaan());
