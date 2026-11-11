<?php
/**
 * Tampilan Portal Informasi Publik Bank Mini Sekolah.
 * Menggunakan kerangka 'publik' yang ringan tanpa memuat aset CSS/JS Dasbor Admin/Teller,
 * serta memanfaatkan komponen UI bersama (`lencana_status` dan `pesan_kilat`).
 */
?>
<header class="navbar navbar-expand-lg navbar-publik-bankmini sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2 text-dark" href="<?= amankanHtml(urlDasar('portal')) ?>">
            <span class="merek-ikon-bankmini"><i class="bi bi-bank2"></i></span>
            <span>Bank Mini Sekolah <span class="badge text-bg-primary ms-1">IDR</span></span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= amankanHtml(urlDasar('masuk')) ?>" class="btn tombol-bankmini fw-semibold px-3">
                <i class="bi bi-box-arrow-in-right me-1"></i>Masuk Petugas
            </a>
        </div>
    </div>
</header>

<main class="container py-4 py-md-5 flex-grow-1">
    <?= komponenUi('pesan_kilat', ['pesanKilat' => $pesanKilat ?? null]) ?>

    <!-- Hero Pengenalan Bank Mini -->
    <section class="hero-publik-bankmini p-4 p-md-5 mb-5">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-8">
                <span class="badge rounded-pill bg-info bg-opacity-25 text-info border border-info border-opacity-25 mb-3 px-3 py-2">
                    <i class="bi bi-shield-check me-1"></i>Laboratorium Perbankan &amp; Rekayasa Perangkat Lunak
                </span>
                <h1 class="display-6 fw-bold mb-3">
                    Layanan Tabungan Sekolah Modern, Transparan, &amp; Akurat
                </h1>
                <p class="lead text-white-50 mb-4">
                    Sistem Informasi Bank Mini memfasilitasi pembukaan rekening siswa, setoran dan penarikan tunai,
                    transfer antar-rekening sekolah, serta pencatatan buku besar berprinsip ACID dalam mata uang Rupiah (IDR).
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= amankanHtml(urlDasar('masuk')) ?>" class="btn btn-light fw-semibold text-primary px-4">
                        <i class="bi bi-person-badge me-1"></i>Portal Petugas / Teller
                    </a>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10">
                    <div class="small text-uppercase text-info fw-bold mb-2">Standar Sistem</div>
                    <ul class="list-unstyled small mb-0 d-flex flex-column gap-2 text-white-50">
                        <li><i class="bi bi-check2-circle text-info me-2"></i>Mata Uang Rupiah (`DECIMAL(15,2)`)</li>
                        <li><i class="bi bi-check2-circle text-info me-2"></i>Buku Besar *Immutable* (Append-Only)</li>
                        <li><i class="bi bi-check2-circle text-info me-2"></i>Antarmuka 100% Bahasa Indonesia</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Daftar Produk Simpanan Publik -->
    <section>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h2 class="h4 fw-bold mb-1">Produk Simpanan Aktif</h2>
                <p class="text-muted small mb-0">Pilihan produk tabungan resmi yang tersedia untuk siswa, guru, dan warga sekolah.</p>
            </div>
        </div>

        <div class="row g-3">
            <?php if (!empty($daftarProdukAktif) && is_array($daftarProdukAktif)) : ?>
                <?php foreach ($daftarProdukAktif as $barisProduk) : ?>
                    <div class="col-12 col-md-4">
                        <div class="kartu-produk-publik p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <span class="badge text-bg-light border font-monospace">
                                        <?= amankanHtml($barisProduk['kode_produk'] ?? '') ?>
                                    </span>
                                    <?= komponenUi('lencana_status', [
                                        'status' => (string) ($barisProduk['status'] ?? 'AKTIF'),
                                    ]) ?>
                                </div>
                                <h3 class="h5 fw-bold mb-3"><?= amankanHtml($barisProduk['nama_produk'] ?? '') ?></h3>
                            </div>
                            <div class="border-top pt-3 small d-flex flex-column gap-1">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Setoran Awal Min.</span>
                                    <span class="fw-semibold angka-keuangan">
                                        <?= amankanHtml(formatRupiah((string) ($barisProduk['setoran_awal_minimum'] ?? '0'))) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Saldo Mengendap Min.</span>
                                    <span class="fw-semibold angka-keuangan">
                                        <?= amankanHtml(formatRupiah((string) ($barisProduk['saldo_mengendap_minimum'] ?? '0'))) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12">
                    <div class="card kartu-bankmini p-4 text-center text-muted">
                        Informasi produk tabungan akan tampil setelah basis data diaktifkan.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer class="mt-auto py-3 bg-white border-top text-muted small">
    <div class="container d-flex justify-content-between flex-wrap gap-2">
        <span><?= amankanHtml($namaAplikasi ?? 'Sistem Informasi Bank Mini') ?> &mdash; Portal Informasi Publik</span>
        <span>Versi <?= amankanHtml($versiAplikasi ?? '1.0.0') ?> &bull; Design System Offline</span>
    </div>
</footer>

