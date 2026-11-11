<?php

declare(strict_types=1);

namespace Aplikasi\Layanan;

use DomainException;
use InvalidArgumentException;

/**
 * LayananDokumentasi
 *
 * @konsep_rpl Pengindeksan Dokumen, Konversi Markdown, & Pencegahan Path Traversal
 * Layanan ini mengindeks seluruh berkas `.md` di direktori `dokumentasi/` secara
 * otomatis (Single Source of Truth), menyediakan fitur pencarian kata kunci penuh,
 * mengonversi Markdown menjadi HTML Bootstrap 5 yang aman dari XSS, serta mencegah
 * serangan Path Traversal (`../`) menggunakan validasi batas direktori `realpath()`.
 */
class LayananDokumentasi
{
    private string $direktoriDokumentasi;

    /**
     * Konstruktor LayananDokumentasi.
     *
     * @param string|null $direktoriDokumentasi Jalur absolut folder dokumentasi (opsional)
     */
    public function __construct(?string $direktoriDokumentasi = null)
    {
        // [Langkah 1 - Konsep RPL]: Tetapkan direktori dasar dokumentasi sebagai batas keamanan pembacaan berkas.
        $jalurBawaan = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'dokumentasi';
        $jalurPilihan = $direktoriDokumentasi !== null ? $direktoriDokumentasi : $jalurBawaan;
        $jalurNyata = realpath($jalurPilihan);

        $this->direktoriDokumentasi = $jalurNyata !== false ? $jalurNyata : $jalurPilihan;
    }

    /**
     * Mengambil katalog seluruh dokumen `.md` yang dikelompokkan per kategori folder.
     *
     * @return array<string, array{
     *     kode: string,
     *     judul_kategori: string,
     *     ikon: string,
     *     daftar_dokumen: array<int, array{jalur: string, judul: string, nama_berkas: string}>
     * }>
     */
    public function ambilKatalogTerindeks(): array
    {
        // [Langkah 1 - Konsep RPL]: Definisikan peta kategori standar dokumentasi Bank Mini.
        $petaKategori = [
            '00-ikhtisar' => [
                'kode'           => '00-ikhtisar',
                'judul_kategori' => '00. Ikhtisar & Pintu Masuk',
                'ikon'           => 'bi-compass',
                'daftar_dokumen' => [],
            ],
            '01-panduan-pengguna' => [
                'kode'           => '01-panduan-pengguna',
                'judul_kategori' => '01. Panduan Pengguna (Admin/Teller/Nasabah)',
                'ikon'           => 'bi-person-Check',
                'daftar_dokumen' => [],
            ],
            '02-panduan-dev' => [
                'kode'           => '02-panduan-dev',
                'judul_kategori' => '02. Panduan Dev & Materi Belajar RPL',
                'ikon'           => 'bi-mortarboard',
                'daftar_dokumen' => [],
            ],
            '03-adr' => [
                'kode'           => '03-adr',
                'judul_kategori' => '03. Keputusan Arsitektur (ADR)',
                'ikon'           => 'bi-diagram-3',
                'daftar_dokumen' => [],
            ],
            '04-work-log' => [
                'kode'           => '04-work-log',
                'judul_kategori' => '04. Catatan Progres (Work Log)',
                'ikon'           => 'bi-clock-history',
                'daftar_dokumen' => [],
            ],
            '05-template-dokumentasi' => [
                'kode'           => '05-template-dokumentasi',
                'judul_kategori' => '05. Template Standar Dokumentasi',
                'ikon'           => 'bi-file-earmark-code',
                'daftar_dokumen' => [],
            ],
            '06-aturan-agen' => [
                'kode'           => '06-aturan-agen',
                'judul_kategori' => '06. Aturan Agen & Quality Gate',
                'ikon'           => 'bi-shield-check',
                'daftar_dokumen' => [],
            ],
        ];

        $berkasUtama = $this->direktoriDokumentasi . DIRECTORY_SEPARATOR . 'README.md';
        if (is_file($berkasUtama)) {
            $petaKategori['00-ikhtisar']['daftar_dokumen'][] = [
                'jalur'       => 'README.md',
                'judul'       => $this->ekstrakJudulMarkdown($berkasUtama, 'README.md'),
                'nama_berkas' => 'README.md',
            ];
        }

        foreach ($petaKategori as $namaFolder => $dataKategori) {
            if ($namaFolder === '00-ikhtisar') {
                continue;
            }

            $jalurFolder = $this->direktoriDokumentasi . DIRECTORY_SEPARATOR . $namaFolder;
            if (!is_dir($jalurFolder)) {
                continue;
            }

            $daftarIsiFolder = scandir($jalurFolder);
            if ($daftarIsiFolder === false) {
                continue;
            }

            sort($daftarIsiFolder);
            /** @var array<int, array{jalur: string, judul: string, nama_berkas: string}> $daftarDokumenKategori */
            $daftarDokumenKategori = [];

            foreach ($daftarIsiFolder as $namaBerkas) {
                if (!str_ends_with(strtolower($namaBerkas), '.md')) {
                    continue;
                }

                $jalurPenuh = $jalurFolder . DIRECTORY_SEPARATOR . $namaBerkas;
                if (!is_file($jalurPenuh)) {
                    continue;
                }

                $jalurRelatif = $namaFolder . '/' . $namaBerkas;
                $itemDokumen = [
                    'jalur'       => $jalurRelatif,
                    'judul'       => $this->ekstrakJudulMarkdown($jalurPenuh, $namaBerkas),
                    'nama_berkas' => $namaBerkas,
                ];

                if ($namaBerkas === 'README.md') {
                    array_unshift($daftarDokumenKategori, $itemDokumen);
                } else {
                    $daftarDokumenKategori[] = $itemDokumen;
                }
            }

            $petaKategori[$namaFolder] = [
                'kode'           => $dataKategori['kode'],
                'judul_kategori' => $dataKategori['judul_kategori'],
                'ikon'           => $dataKategori['ikon'],
                'daftar_dokumen' => $daftarDokumenKategori,
            ];
        }

        return $petaKategori;
    }

    /**
     * Membaca dan mengonversi dokumen Markdown secara aman (Anti-Path Traversal).
     *
     * @param string $jalurRelatif Jalur relatif terhadap folder dokumentasi (contoh: '02-panduan-dev/arsitektur-sistem.md')
     * @return array{
     *     jalur: string,
     *     judul: string,
     *     kategori: string,
     *     html: string,
     *     daftar_isi: array<int, array{id: string, teks: string, level: int}>,
     *     waktu_ubah: string
     * }
     *
     * @throws InvalidArgumentException Jika jalur mengandung upaya Path Traversal atau bukan file .md
     * @throws DomainException          Jika berkas dokumentasi tidak ditemukan
     */
    public function bacaDokumenAman(string $jalurRelatif): array
    {
        // [Langkah 1 - Konsep RPL]: Tolak karakter berbahaya atau ekstensi selain .md (Anti Path Traversal).
        $jalurBersih = str_replace('\\', '/', trim($jalurRelatif));
        $jalurBersih = ltrim($jalurBersih, '/');

        if ($jalurBersih === '') {
            $jalurBersih = 'README.md';
        }

        if (
            str_contains($jalurBersih, '..')
            || str_contains($jalurBersih, "\0")
            || !preg_match('/^[a-zA-Z0-9_\-\/]+\.md$/', $jalurBersih)
        ) {
            throw new InvalidArgumentException('Jalur dokumen tidak sah atau melanggar batas keamanan direktori.');
        }

        $jalurTarget = $this->direktoriDokumentasi
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $jalurBersih);
        $jalurNyataTarget = realpath($jalurTarget);
        $jalurNyataDasar = realpath($this->direktoriDokumentasi);

        if ($jalurNyataTarget === false || !is_file($jalurNyataTarget)) {
            throw new DomainException('Berkas dokumentasi yang diminta tidak ditemukan.');
        }

        // [Langkah 2 - Konsep RPL]: Pastikan realpath berkas benar-benar berada di dalam direktori dokumentasi.
        if (
            $jalurNyataDasar === false
            || !str_starts_with(
                strtolower($jalurNyataTarget),
                strtolower($jalurNyataDasar . DIRECTORY_SEPARATOR)
            )
        ) {
            throw new InvalidArgumentException('Akses berkas di luar direktori dokumentasi ditolak.');
        }

        $isiMentah = (string) file_get_contents($jalurNyataTarget);
        $judulDokumen = $this->ekstrakJudulMarkdown($jalurNyataTarget, basename($jalurBersih));
        $hasilKonversi = $this->konversiMarkdownKeHtml($isiMentah, $jalurBersih);
        $potonganJalur = explode('/', $jalurBersih);
        $kodeKategori = count($potonganJalur) > 1 ? $potonganJalur[0] : '00-ikhtisar';
        $detikModifikasi = filemtime($jalurNyataTarget);

        return [
            'jalur'      => $jalurBersih,
            'judul'      => $judulDokumen,
            'kategori'   => $kodeKategori,
            'html'       => $hasilKonversi['html'],
            'daftar_isi' => $hasilKonversi['daftar_isi'],
            'waktu_ubah' => $detikModifikasi !== false ? date('Y-m-d H:i:s', $detikModifikasi) : '',
        ];
    }

    /**
     * Mencari kata kunci di seluruh berkas dokumentasi yang terindeks.
     *
     * @param string $kataKunci Kata kunci pencarian
     * @return array<int, array{jalur: string, judul: string, kategori: string, cuplikan: string}>
     */
    public function cariDokumen(string $kataKunci): array
    {
        // [Langkah 1 - Konsep RPL]: Bersihkan kata kunci dan lakukan pencarian case-insensitive di seluruh katalog.
        $kunciBersih = trim($kataKunci);
        if ($kunciBersih === '') {
            return [];
        }

        $katalog = $this->ambilKatalogTerindeks();
        $daftarHasil = [];

        foreach ($katalog as $infoKategori) {
            foreach ($infoKategori['daftar_dokumen'] as $itemDok) {
                $jalurBerkas = $this->direktoriDokumentasi
                    . DIRECTORY_SEPARATOR
                    . str_replace('/', DIRECTORY_SEPARATOR, $itemDok['jalur']);

                if (!is_file($jalurBerkas)) {
                    continue;
                }

                $isiTeks = (string) file_get_contents($jalurBerkas);
                $posisiJudul = mb_stripos($itemDok['judul'], $kunciBersih);
                $posisiIsi = mb_stripos($isiTeks, $kunciBersih);

                if ($posisiJudul === false && $posisiIsi === false) {
                    continue;
                }

                $teksTanpaSimbol = trim((string) preg_replace('/[#*`>|_\-]+/', ' ', $isiTeks));
                $teksTanpaSimbol = (string) preg_replace('/\s+/', ' ', $teksTanpaSimbol);

                $posisiCuplikan = mb_stripos($teksTanpaSimbol, $kunciBersih);
                $awalPotong = $posisiCuplikan !== false ? max(0, $posisiCuplikan - 60) : 0;
                $cuplikan = mb_substr($teksTanpaSimbol, $awalPotong, 180);

                if ($awalPotong > 0) {
                    $cuplikan = '...' . $cuplikan;
                }
                if (mb_strlen($teksTanpaSimbol) > ($awalPotong + 180)) {
                    $cuplikan .= '...';
                }

                $daftarHasil[] = [
                    'jalur'    => $itemDok['jalur'],
                    'judul'    => $itemDok['judul'],
                    'kategori' => $infoKategori['judul_kategori'],
                    'cuplikan' => $cuplikan,
                ];
            }
        }

        return $daftarHasil;
    }

    /**
     * Mengekstrak judul utama (`# Judul`) dari berkas Markdown.
     *
     * @param string $jalurBerkas Jalur absolut berkas `.md`
     * @param string $cadangan    Judul cadangan jika heading `#` tidak ditemukan
     * @return string Judul dokumen bersih
     */
    private function ekstrakJudulMarkdown(string $jalurBerkas, string $cadangan): string
    {
        $isi = (string) file_get_contents($jalurBerkas);
        if (preg_match('/^#\s+(.+)$/m', $isi, $cocokJudul)) {
            return trim(strip_tags($cocokJudul[1]));
        }

        return $cadangan;
    }

    /**
     * Mengonversi teks Markdown menjadi HTML Bootstrap 5 rapi beserta daftar isi otomatis.
     *
     * @param string $isiMarkdown       Teks Markdown mentah
     * @param string $jalurDokumenAktif Jalur relatif dokumen saat ini untuk resolusi tautan relatif
     * @return array{html: string, daftar_isi: array<int, array{id: string, teks: string, level: int}>}
     */
    private function konversiMarkdownKeHtml(string $isiMarkdown, string $jalurDokumenAktif): array
    {
        // [Langkah 1 - Konsep RPL]: Normalisasi baris baru dan siapkan penampung blok HTML & daftar isi.
        $teksNormal = str_replace(["\r\n", "\r"], "\n", $isiMarkdown);
        $daftarBaris = explode("\n", $teksNormal);
        $jumlahBaris = count($daftarBaris);

        $kumpulanHtml = [];
        $daftarIsi = [];
        $indeks = 0;

        while ($indeks < $jumlahBaris) {
            $baris = $daftarBaris[$indeks];
            $barisPangkas = trim($baris);

            // 1. Baris kosong
            if ($barisPangkas === '') {
                $indeks++;
                continue;
            }

            // 2. Garis pemisah horizontal (---)
            if (preg_match('/^-{3,}$/', $barisPangkas)) {
                $kumpulanHtml[] = '<hr class="my-4 border-secondary border-opacity-25">';
                $indeks++;
                continue;
            }

            // 3. Blok kode berpagar (```bahasa ... ```)
            if (preg_match('/^```([a-zA-Z0-9_\-]*)/', $barisPangkas, $cocokKode)) {
                $bahasaKode = strtolower(trim($cocokKode[1]));
                $isiKode = [];
                $indeks++;

                while ($indeks < $jumlahBaris && ! str_starts_with(trim($daftarBaris[$indeks]), '```')) {
                    $isiKode[] = $daftarBaris[$indeks];
                    $indeks++;
                }
                $indeks++; // Lewati baris penutup ```

                $labelBahasa = $bahasaKode !== '' ? strtoupper($bahasaKode) : 'TEKS / KODE';
                $ikonKode = $bahasaKode === 'mermaid' ? 'bi-diagram-3' : 'bi-terminal';
                $kodeAman = amankanHtml(implode("\n", $isiKode));

                $kumpulanHtml[] = '<div class="blok-kode-dokumentasi my-3 rounded-3 overflow-hidden border">'
                    . '<div class="d-flex align-items-center justify-content-between px-3 py-1 bg-dark text-white-50 small border-bottom border-secondary border-opacity-25">'
                    . '<span><i class="bi ' . $ikonKode . ' me-1 text-info"></i>' . amankanHtml($labelBahasa) . '</span>'
                    . '</div>'
                    . '<pre class="m-0 p-3 bg-dark text-light small overflow-auto"><code>' . $kodeAman . '</code></pre>'
                    . '</div>';
                continue;
            }

            // 4. Heading (# s.d. ####)
            if (preg_match('/^(#{1,4})\s+(.+)$/', $barisPangkas, $cocokHeading)) {
                $levelHeading = strlen($cocokHeading[1]);
                $teksMentahHeading = trim($cocokHeading[2]);
                $teksBersihId = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', strip_tags($teksMentahHeading)));
                $idBagian = 'bagian-' . trim($teksBersihId, '-') . '-' . $indeks;
                $isiHeadingHtml = $this->formatInlineMarkdown($teksMentahHeading, $jalurDokumenAktif);

                if ($levelHeading === 2 || $levelHeading === 3) {
                    $daftarIsi[] = [
                        'id'    => $idBagian,
                        'teks'  => trim((string) preg_replace('/[`*_]+/', '', $teksMentahHeading)),
                        'level' => $levelHeading,
                    ];
                }

                $kelasHeading = match ($levelHeading) {
                    1 => 'h3 fw-bold text-dark pb-2 mb-3 border-bottom',
                    2 => 'h5 fw-bold text-primary mt-4 mb-3 pt-2',
                    3 => 'h6 fw-bold text-dark mt-3 mb-2',
                    default => 'fw-semibold text-secondary mt-2 mb-1',
                };

                $kumpulanHtml[] = sprintf(
                    '<h%d id="%s" class="%s">%s</h%d>',
                    $levelHeading,
                    amankanHtml($idBagian),
                    $kelasHeading,
                    $isiHeadingHtml,
                    $levelHeading
                );
                $indeks++;
                continue;
            }

            // 5. Blockquote & GitHub Alert (> [!NOTE], > [!IMPORTANT], dll.)
            if (str_starts_with($barisPangkas, '>')) {
                $barisKutipan = [];
                while ($indeks < $jumlahBaris && str_starts_with(trim($daftarBaris[$indeks]), '>')) {
                    $barisKutipan[] = ltrim(substr(trim($daftarBaris[$indeks]), 1));
                    $indeks++;
                }

                $barisPertama = $barisKutipan[0] ?? '';
                if (preg_match('/^\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]\s*(.*)$/i', $barisPertama, $cocokAlert)) {
                    $jenisAlert = strtoupper($cocokAlert[1]);
                    $barisKutipan[0] = $cocokAlert[2];
                    $isiAlert = trim(implode(' ', array_filter($barisKutipan, static fn (string $teks): bool => $teks !== '')));

                    $petaGayaAlert = [
                        'NOTE'      => ['kelas' => 'alert-info', 'ikon' => 'bi-info-circle-fill', 'label' => 'Catatan'],
                        'TIP'       => ['kelas' => 'alert-success', 'ikon' => 'bi-lightbulb-fill', 'label' => 'Tips Praktis'],
                        'IMPORTANT' => ['kelas' => 'alert-primary', 'ikon' => 'bi-bookmark-star-fill', 'label' => 'Penting'],
                        'WARNING'   => ['kelas' => 'alert-warning', 'ikon' => 'bi-exclamation-triangle-fill', 'label' => 'Perhatian'],
                        'CAUTION'   => ['kelas' => 'alert-danger', 'ikon' => 'bi-shield-exclamation', 'label' => 'Peringatan Keras'],
                    ];
                    $infoGaya = $petaGayaAlert[$jenisAlert] ?? $petaGayaAlert['NOTE'];

                    $kumpulanHtml[] = '<div class="alert ' . $infoGaya['kelas'] . ' border-0 shadow-sm my-3">'
                        . '<div class="fw-bold mb-1"><i class="bi ' . $infoGaya['ikon'] . ' me-2"></i>'
                        . amankanHtml($infoGaya['label']) . '</div>'
                        . '<div class="small">' . $this->formatInlineMarkdown($isiAlert, $jalurDokumenAktif) . '</div>'
                        . '</div>';
                } else {
                    $isiKutipan = $this->formatInlineMarkdown(implode(' ', $barisKutipan), $jalurDokumenAktif);
                    $kumpulanHtml[] = '<blockquote class="blockquote bg-light border-start border-4 border-primary p-3 rounded-end my-3 small text-secondary">'
                        . $isiKutipan
                        . '</blockquote>';
                }
                continue;
            }

            // 6. Tabel Markdown (| Kolom 1 | Kolom 2 |)
            if (str_starts_with($barisPangkas, '|') && str_ends_with($barisPangkas, '|')) {
                $barisTabel = [];
                while (
                    $indeks < $jumlahBaris
                    && str_starts_with(trim($daftarBaris[$indeks]), '|')
                ) {
                    $barisTabel[] = trim($daftarBaris[$indeks]);
                    $indeks++;
                }

                $kumpulanHtml[] = $this->bangunTabelHtml($barisTabel, $jalurDokumenAktif);
                continue;
            }

            // 7. Daftar Tidak Berurutan (- atau * ) & Daftar Berurutan (1. )
            if (preg_match('/^([-*]|\d+\.)\s+/', $barisPangkas)) {
                $apakahBerurutan = (bool) preg_match('/^\d+\.\s+/', $barisPangkas);
                $tagDaftar = $apakahBerurutan ? 'ol' : 'ul';
                $itemDaftar = [];

                while (
                    $indeks < $jumlahBaris
                    && preg_match('/^\s*([-*]|\d+\.)\s+(.+)$/', $daftarBaris[$indeks], $cocokItem)
                ) {
                    $itemDaftar[] = '<li class="mb-1">'
                        . $this->formatInlineMarkdown(trim($cocokItem[2]), $jalurDokumenAktif)
                        . '</li>';
                    $indeks++;
                }

                $kumpulanHtml[] = sprintf(
                    '<%s class="my-2 ps-4">%s</%s>',
                    $tagDaftar,
                    implode('', $itemDaftar),
                    $tagDaftar
                );
                continue;
            }

            // 8. Paragraf Biasa
            $barisParagraf = [];
            while (
                $indeks < $jumlahBaris
                && trim($daftarBaris[$indeks]) !== ''
                && !preg_match('/^(#{1,4}\s+|```|---|\||>|\s*[-*]\s+|\s*\d+\.\s+)/', trim($daftarBaris[$indeks]))
            ) {
                $barisParagraf[] = trim($daftarBaris[$indeks]);
                $indeks++;
            }

            if (count($barisParagraf) > 0) {
                $kumpulanHtml[] = '<p class="mb-3 lh-lg">'
                    . $this->formatInlineMarkdown(implode(' ', $barisParagraf), $jalurDokumenAktif)
                    . '</p>';
            } else {
                $indeks++;
            }
        }

        return [
            'html'       => implode("\n", $kumpulanHtml),
            'daftar_isi' => $daftarIsi,
        ];
    }

    /**
     * Membangun tabel HTML Bootstrap 5 dari kumpulan baris tabel Markdown.
     *
     * @param array<int, string> $barisTabel        Daftar baris tabel Markdown
     * @param string             $jalurDokumenAktif Jalur dokumen aktif
     * @return string Elemen `<div class="table-responsive"><table ...>`
     */
    private function bangunTabelHtml(array $barisTabel, string $jalurDokumenAktif): string
    {
        if (count($barisTabel) === 0) {
            return '';
        }

        $pecahSel = static function (string $barisMentah): array {
            $isiTengah = trim($barisMentah);
            $isiTengah = trim($isiTengah, '|');
            $daftarSel = explode('|', $isiTengah);
            return array_map('trim', $daftarSel);
        };

        $selKepala = $pecahSel($barisTabel[0]);
        $htmlKepala = '<thead><tr class="table-light">';
        foreach ($selKepala as $teksKolom) {
            $htmlKepala .= '<th class="fw-semibold small text-uppercase text-secondary">'
                . $this->formatInlineMarkdown($teksKolom, $jalurDokumenAktif)
                . '</th>';
        }
        $htmlKepala .= '</tr></thead>';

        $mulaiBarisIsi = isset($barisTabel[1]) && preg_match('/^[\|\s:\-]+$/', $barisTabel[1]) ? 2 : 1;
        $htmlBadan = '<tbody>';
        $jumlahTotal = count($barisTabel);

        for ($nomor = $mulaiBarisIsi; $nomor < $jumlahTotal; $nomor++) {
            $selBaris = $pecahSel($barisTabel[$nomor]);
            $htmlBadan .= '<tr>';
            foreach ($selBaris as $isiSel) {
                $htmlBadan .= '<td class="small">'
                    . $this->formatInlineMarkdown($isiSel, $jalurDokumenAktif)
                    . '</td>';
            }
            $htmlBadan .= '</tr>';
        }
        $htmlBadan .= '</tbody>';

        return '<div class="table-responsive my-3">'
            . '<table class="table table-bordered table-hover align-middle mb-0">'
            . $htmlKepala . $htmlBadan
            . '</table></div>';
    }

    /**
     * Memformat elemen inline Markdown (kode, tebal, miring, tautan `.md` internal, dan `<br>`).
     *
     * @param string $teks              Teks satu baris/paragraf
     * @param string $jalurDokumenAktif Jalur dokumen saat ini
     * @return string HTML inline yang telah di-escape dengan aman
     */
    private function formatInlineMarkdown(string $teks, string $jalurDokumenAktif): string
    {
        $daftarToken = [];
        $nomorToken = 0;

        // 1. Amankan inline code (`...`) terlebih dahulu
        $teksProses = (string) preg_replace_callback(
            '/`([^`]+)`/',
            static function (array $cocok) use (&$daftarToken, &$nomorToken): string {
                $kunci = '@@TOKEN_KODE_' . (++$nomorToken) . '@@';
                $daftarToken[$kunci] = '<code class="px-1 py-0 rounded bg-light text-danger border small">'
                    . amankanHtml($cocok[1])
                    . '</code>';
                return $kunci;
            },
            $teks
        );

        // 2. Konversi tautan Markdown [Label](Target)
        $teksProses = (string) preg_replace_callback(
            '/\[([^\]]+)\]\(([^)]+)\)/',
            function (array $cocok) use (&$daftarToken, &$nomorToken, $jalurDokumenAktif): string {
                $kunci = '@@TOKEN_TAUTAN_' . (++$nomorToken) . '@@';
                $labelTautan = $cocok[1];
                $tujuanMentah = trim($cocok[2]);

                $labelBersih = (string) preg_replace('/^`(.+)`$/', '$1', trim($labelTautan));
                $urlTujuan = $this->resolusiTautanDokumen($tujuanMentah, $jalurDokumenAktif);

                $daftarToken[$kunci] = '<a href="' . amankanHtml($urlTujuan)
                    . '" class="text-decoration-none fw-semibold link-primary">'
                    . amankanHtml($labelBersih)
                    . '</a>';
                return $kunci;
            },
            $teksProses
        );

        // 3. Escape sisa teks biasa untuk mencegah XSS, izinkan <br> menjadi baris baru
        $teksAman = amankanHtml($teksProses);
        $teksAman = str_ireplace(['&lt;br&gt;', '&lt;br/&gt;', '&lt;br /&gt;'], '<br>', $teksAman);

        // 4. Format tebal (**teks**) dan miring (*teks*)
        $teksAman = (string) preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $teksAman);
        $teksAman = (string) preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $teksAman);

        // 5. Kembalikan token kode & tautan
        if (count($daftarToken) > 0) {
            $teksAman = strtr($teksAman, $daftarToken);
        }

        return $teksAman;
    }

    /**
     * Mengubah tautan relatif `.md` menjadi URL rute `/dokumentasi?dok=...`.
     *
     * @param string $tujuanMentah      URL atau jalur relatif di dalam Markdown
     * @param string $jalurDokumenAktif Jalur dokumen saat ini
     * @return string URL tujuan yang aman
     */
    private function resolusiTautanDokumen(string $tujuanMentah, string $jalurDokumenAktif): string
    {
        if (str_starts_with($tujuanMentah, 'http://') || str_starts_with($tujuanMentah, 'https://')) {
            return $tujuanMentah;
        }

        if (str_starts_with($tujuanMentah, '#')) {
            return $tujuanMentah;
        }

        $pisahAnchor = explode('#', $tujuanMentah, 2);
        $jalurTanpaAnchor = $pisahAnchor[0];

        if (!str_ends_with(strtolower($jalurTanpaAnchor), '.md')) {
            return $tujuanMentah;
        }

        $direktoriSaatIni = dirname(str_replace('\\', '/', $jalurDokumenAktif));
        if ($direktoriSaatIni === '.') {
            $direktoriSaatIni = '';
        }

        $gabungan = $direktoriSaatIni !== ''
            ? $direktoriSaatIni . '/' . $jalurTanpaAnchor
            : $jalurTanpaAnchor;

        $segmen = explode('/', str_replace('\\', '/', $gabungan));
        $segmenNormal = [];

        foreach ($segmen as $bagian) {
            if ($bagian === '' || $bagian === '.') {
                continue;
            }
            if ($bagian === '..') {
                array_pop($segmenNormal);
                continue;
            }
            $segmenNormal[] = $bagian;
        }

        $jalurHasil = implode('/', $segmenNormal);

        return urlDasar('dokumentasi?dok=' . rawurlencode($jalurHasil));
    }
}
