<?php
$daftarMenu = [
    ['tampilan' => 'dasbor/indeks', 'url' => 'dasbor', 'ikon' => 'bi-speedometer2', 'label' => 'Dasbor'],
    ['tampilan' => 'nasabah/indeks', 'url' => 'nasabah', 'ikon' => 'bi-people', 'label' => 'Data Nasabah'],
    ['tampilan' => 'produk/indeks', 'url' => 'produk', 'ikon' => 'bi-journal-bookmark', 'label' => 'Produk Tabungan'],
    ['tampilan' => 'rekening/indeks', 'url' => 'rekening', 'ikon' => 'bi-wallet2', 'label' => 'Data Rekening'],
    ['tampilan' => 'transaksi/setor_tunai', 'url' => 'transaksi/setor', 'ikon' => 'bi-arrow-down-circle', 'label' => 'Setor Tunai'],
    ['tampilan' => 'transaksi/tarik_tunai', 'url' => 'transaksi/tarik', 'ikon' => 'bi-arrow-up-circle', 'label' => 'Tarik Tunai'],
    ['tampilan' => 'transaksi/transfer', 'url' => 'transaksi/transfer', 'ikon' => 'bi-arrow-left-right', 'label' => 'Transfer Rekening'],
    ['tampilan' => 'transaksi/koreksi', 'url' => 'transaksi/koreksi', 'ikon' => 'bi-arrow-counterclockwise', 'label' => 'Jurnal Koreksi'],
    ['tampilan' => 'laporan/mutasi', 'url' => 'laporan/mutasi', 'ikon' => 'bi-file-earmark-spreadsheet', 'label' => 'Laporan Mutasi'],
];
?>
<aside class="offcanvas-lg offcanvas-start bilah-sisi-bankmini p-3 d-flex flex-column tidak-cetak" tabindex="-1" id="menuBilahSisi">
    <div class="offcanvas-header d-lg-none border-bottom border-secondary pb-2 mb-3">
        <h5 class="offcanvas-title text-white"><i class="bi bi-bank2 me-2"></i>Bank Mini (IDR)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#menuBilahSisi"></button>
    </div>

    <div class="d-none d-lg-flex align-items-center justify-content-between border-bottom border-secondary pb-3 mb-3">
        <div>
            <div class="fs-5 fw-bold text-white"><i class="bi bi-bank2 me-2"></i>Bank Mini</div>
            <small class="text-white-50">Mata Uang: Rupiah (IDR)</small>
        </div>
        <span class="badge text-bg-info">v<?= amankanHtml($versiAplikasi ?? '1.0.0') ?></span>
    </div>

    <?php if (!empty($namaPenggunaAktif)) : ?>
        <div class="bg-white bg-opacity-10 rounded-3 p-2 mb-3 small">
            <div class="fw-semibold text-white text-truncate">
                <i class="bi bi-person-circle me-1"></i><?= amankanHtml($namaPenggunaAktif) ?>
            </div>
            <div class="text-white-50 text-uppercase" style="font-size: 0.72rem;">
                Peran: <?= amankanHtml($peranPenggunaAktif ?? '-') ?>
            </div>
        </div>
    <?php endif; ?>

    <ul class="nav nav-pills flex-column gap-1 mb-auto">
        <?php foreach ($daftarMenu as $itemMenu) : ?>
            <?php
            $apakahAktif = ($tampilanAktif ?? '') === $itemMenu['tampilan'];
            if (
                $itemMenu['tampilan'] === 'transaksi/koreksi'
                && !in_array(($peranPenggunaAktif ?? ''), ['admin', 'supervisor'], true)
            ) {
                continue;
            }
            ?>
            <li class="nav-item">
                <a class="nav-link <?= $apakahAktif ? 'active' : '' ?>" href="<?= amankanHtml(urlDasar($itemMenu['url'])) ?>">
                    <i class="bi <?= amankanHtml($itemMenu['ikon']) ?> me-2"></i><?= amankanHtml($itemMenu['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <form method="POST" action="<?= amankanHtml(urlDasar('keluar')) ?>" class="mt-4 pt-3 border-top border-secondary">
        <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">
        <button type="submit" class="btn btn-danger w-100">
            <i class="bi bi-box-arrow-right me-1"></i>Keluar Sistem
        </button>
    </form>
</aside>

<main class="flex-grow-1 p-3 p-md-4 konten-utama-bankmini">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom tidak-cetak">
        <h1 class="h3 fw-bold mb-0"><?= amankanHtml($judulHalaman ?? '') ?></h1>
        <span class="badge text-bg-light border text-secondary">
            <i class="bi bi-shield-lock me-1"></i>ACID InnoDB &bull; IDR
        </span>
    </div>

    <?php if (!empty($pesanKilat) && is_array($pesanKilat)) : ?>
        <?php
        $jenisPesan = $pesanKilat['jenis'] ?? 'kesalahan';
        $kelasAlert = $jenisPesan === 'sukses' ? 'alert-success' : ($jenisPesan === 'peringatan' ? 'alert-warning' : 'alert-danger');
        $ikonAlert = $jenisPesan === 'sukses' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        ?>
        <div class="alert <?= amankanHtml($kelasAlert) ?> alert-dismissible fade show shadow-sm tidak-cetak" role="alert">
            <i class="bi <?= amankanHtml($ikonAlert) ?> me-2"></i><?= amankanHtml($pesanKilat['pesan'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
