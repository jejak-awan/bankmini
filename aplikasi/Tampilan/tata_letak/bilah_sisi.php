<?php
/**
 * Bilah Sisi Navigasi Responsif (Offcanvas Mobile + Sidebar Tetap Desktop) & Topbar Konten.
 * Menggunakan struktur resmi Bootstrap `.offcanvas-lg` -> `.offcanvas-body`
 * serta menjaga seluruh variabel lokal dalam format `$camelCase`.
 */
$grupMenu = [
    'Menu Utama' => [
        ['tampilan' => 'dasbor/indeks', 'url' => 'dasbor', 'ikon' => 'bi-speedometer2', 'label' => 'Dasbor'],
        ['tampilan' => 'nasabah/indeks', 'url' => 'nasabah', 'ikon' => 'bi-people', 'label' => 'Data Nasabah'],
        ['tampilan' => 'produk/indeks', 'url' => 'produk', 'ikon' => 'bi-journal-bookmark', 'label' => 'Produk Tabungan'],
        ['tampilan' => 'rekening/indeks', 'url' => 'rekening', 'ikon' => 'bi-wallet2', 'label' => 'Data Rekening'],
    ],
    'Operasional Teller' => [
        ['tampilan' => 'transaksi/setor_tunai', 'url' => 'transaksi/setor', 'ikon' => 'bi-arrow-down-circle', 'label' => 'Setor Tunai'],
        ['tampilan' => 'transaksi/tarik_tunai', 'url' => 'transaksi/tarik', 'ikon' => 'bi-arrow-up-circle', 'label' => 'Tarik Tunai'],
        ['tampilan' => 'transaksi/transfer', 'url' => 'transaksi/transfer', 'ikon' => 'bi-arrow-left-right', 'label' => 'Transfer Rekening'],
    ],
    'Audit & Laporan' => [
        ['tampilan' => 'transaksi/koreksi', 'url' => 'transaksi/koreksi', 'ikon' => 'bi-arrow-counterclockwise', 'label' => 'Jurnal Koreksi', 'hanyaSupervisor' => true],
        ['tampilan' => 'laporan/mutasi', 'url' => 'laporan/mutasi', 'ikon' => 'bi-file-earmark-spreadsheet', 'label' => 'Laporan Mutasi'],
    ],
    'Materi & Dokumentasi RPL' => [
        ['tampilan' => 'dokumentasi/indeks', 'url' => 'dokumentasi', 'ikon' => 'bi-book-half', 'label' => 'Pusat Dokumentasi'],
    ],
];
?>
<aside
    class="offcanvas-lg offcanvas-start bilah-sisi-bankmini tidak-cetak"
    tabindex="-1"
    id="menuBilahSisi"
    aria-labelledby="judulMenuBilahSisi"
>
    <div class="offcanvas-header d-lg-none border-bottom border-white border-opacity-10 px-3 py-3">
        <h5 class="offcanvas-title text-white fw-bold mb-0" id="judulMenuBilahSisi">
            <i class="bi bi-bank2 me-2 text-info"></i>Bank Mini (IDR)
        </h5>
        <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="offcanvas"
            data-bs-target="#menuBilahSisi"
            aria-label="Tutup menu"
        ></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-3 w-100 h-100">
        <!-- Brand Header Desktop -->
        <div class="d-none d-lg-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-white border-opacity-10">
            <div class="d-flex align-items-center gap-2">
                <div class="bilah-sisi-merek-ikon">
                    <i class="bi bi-bank2"></i>
                </div>
                <div>
                    <div class="fw-bold text-white lh-sm">Bank Mini</div>
                    <div class="text-white-50" style="font-size: 0.72rem;">Core Banking RPL</div>
                </div>
            </div>
            <span class="badge rounded-pill bg-primary bg-opacity-25 text-info border border-info border-opacity-25">
                v<?= amankanHtml($versiAplikasi ?? '1.0.0') ?>
            </span>
        </div>

        <!-- Daftar Menu Navigasi -->
        <div class="flex-grow-1">
            <?php foreach ($grupMenu as $namaGrup => $daftarItem) : ?>
                <div class="bilah-sisi-grup-label"><?= amankanHtml($namaGrup) ?></div>
                <ul class="nav nav-pills flex-column gap-1 mb-2">
                    <?php foreach ($daftarItem as $itemMenu) : ?>
                        <?php
                        $apakahKhususSupervisor = !empty($itemMenu['hanyaSupervisor']);
                        if (
                            $apakahKhususSupervisor
                            && !in_array(($peranPenggunaAktif ?? ''), ['admin', 'supervisor'], true)
                        ) {
                            continue;
                        }
                        $apakahAktif = ($tampilanAktif ?? '') === $itemMenu['tampilan'];
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $apakahAktif ? 'active' : '' ?>" href="<?= amankanHtml(urlDasar($itemMenu['url'])) ?>">
                                <i class="bi <?= amankanHtml($itemMenu['ikon']) ?>"></i>
                                <span><?= amankanHtml($itemMenu['label']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </div>

        <!-- Kartu Profil Petugas & Tombol Keluar -->
        <div class="mt-3 pt-3 border-top border-white border-opacity-10">
            <?php if (!empty($namaPenggunaAktif)) : ?>
                <div class="kartu-profil-petugas p-2 px-3 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.85rem;">
                            <?= amankanHtml(strtoupper(substr((string) $namaPenggunaAktif, 0, 1))) ?>
                        </div>
                        <div class="overflow-hidden">
                            <div class="fw-semibold text-white small text-truncate">
                                <?= amankanHtml($namaPenggunaAktif) ?>
                            </div>
                            <div class="text-white-50 text-uppercase" style="font-size: 0.68rem;">
                                <?= amankanHtml($peranPenggunaAktif ?? '-') ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= amankanHtml(urlDasar('keluar')) ?>">
                <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">
                <button type="submit" class="btn btn-outline-light btn-sm w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar Sistem</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<main class="konten-utama-bankmini">
    <!-- Topbar Konten Bersih -->
    <header class="bilah-atas-bankmini px-3 px-md-4 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 tidak-cetak">
        <div>
            <h1 class="h4 fw-bold mb-0 text-dark"><?= amankanHtml($judulHalaman ?? '') ?></h1>
            <div class="small text-muted">Sistem Informasi Tabungan Sekolah &bull; Mata Uang Tunggal Rupiah (IDR)</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                <i class="bi bi-shield-lock-fill me-1"></i>ACID InnoDB Aktif
            </span>
        </div>
    </header>

    <div class="p-3 p-md-4 flex-grow-1">
        <?= komponenUi('pesan_kilat', ['pesanKilat' => $pesanKilat ?? null]) ?>
