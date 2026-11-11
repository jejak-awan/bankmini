<?php
/**
 * Tampilan Pusat Dokumentasi Terindeks & Materi Pembelajaran Siswa RPL.
 * Menyajikan katalog seluruh berkas Markdown (.md) per kategori, mesin pencarian
 * kata kunci instan, isi dokumen terformat Bootstrap 5, serta Daftar Isi (TOC).
 */
?>
<!-- Header Cetak Modul Dokumentasi -->
<div class="hanya-cetak">
    <h2 class="fw-bold mb-1">MODUL DOKUMENTASI &amp; MATERI RPL — BANK MINI SEKOLAH</h2>
    <p class="mb-0 small">
        Dokumen: <strong><?= amankanHtml($dokumenAktif['judul'] ?? '') ?></strong>
        (<code><?= amankanHtml($dokumenAktif['jalur'] ?? '') ?></code>)
    </p>
</div>

<!-- Bilah Pencarian & Aksi Cepat Dokumentasi -->
<div class="card kartu-bankmini border-0 shadow-sm mb-4 tidak-cetak">
    <div class="card-body p-3 p-md-4">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-lg-7">
                <form method="GET" action="<?= amankanHtml(urlDasar('dokumentasi')) ?>" class="d-flex gap-2">
                    <input type="hidden" name="dok" value="<?= amankanHtml($dokumenAktif['jalur'] ?? 'README.md') ?>">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            name="cari"
                            id="cari_dokumentasi"
                            class="form-control border-start-0"
                            placeholder="Cari materi RPL, istilah (ACID, DFD, ERD, CSRF, Docker)..."
                            value="<?= amankanHtml($kataKunciCari ?? '') ?>"
                        >
                        <button type="submit" class="btn tombol-bankmini px-3">
                            Cari Materi
                        </button>
                        <?php if (!empty($kataKunciCari)) : ?>
                            <a
                                href="<?= amankanHtml(urlDasar('dokumentasi?dok=' . rawurlencode((string) ($dokumenAktif['jalur'] ?? 'README.md')))) ?>"
                                class="btn btn-outline-secondary"
                            >
                                Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="col-12 col-lg-5 d-flex flex-wrap justify-content-lg-end gap-2">
                <a
                    href="<?= amankanHtml(urlDasar('dokumentasi?dok=02-panduan-dev/alur-belajar-dan-modul-praktikum-rpl.md')) ?>"
                    class="btn btn-sm btn-outline-primary fw-semibold"
                >
                    <i class="bi bi-mortarboard-fill me-1"></i>Alur Belajar RPL
                </a>
                <a
                    href="<?= amankanHtml(urlDasar('dokumentasi?dok=02-panduan-dev/glosarium-dan-daftar-istilah.md')) ?>"
                    class="btn btn-sm btn-outline-secondary fw-semibold"
                >
                    <i class="bi bi-book me-1"></i>Glosarium &amp; Kamus Data
                </a>
                <button type="button" class="btn btn-sm btn-outline-dark" data-aksi-cetak="ya">
                    <i class="bi bi-printer me-1"></i>Cetak Modul
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($kataKunciCari)) : ?>
    <!-- Panel Hasil Pencarian Kata Kunci -->
    <div class="card kartu-bankmini border-0 shadow-sm mb-4 tidak-cetak">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h2 class="h6 fw-bold mb-0 text-primary">
                <i class="bi bi-search me-2"></i>Hasil Pencarian untuk: &ldquo;<?= amankanHtml($kataKunciCari) ?>&rdquo;
            </h2>
            <span class="badge text-bg-primary rounded-pill"><?= count($hasilPencarian ?? []) ?> Dokumen</span>
        </div>
        <div class="card-body p-3">
            <?php if (empty($hasilPencarian)) : ?>
                <p class="text-muted small mb-0">
                    Tidak ditemukan dokumen yang memuat kata kunci <strong><?= amankanHtml($kataKunciCari) ?></strong>.
                </p>
            <?php else : ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($hasilPencarian as $itemHasil) : ?>
                        <a
                            href="<?= amankanHtml(urlDasar('dokumentasi?dok=' . rawurlencode($itemHasil['jalur']))) ?>"
                            class="list-group-item list-group-item-action py-3 px-2"
                        >
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                <span class="fw-bold text-primary"><?= amankanHtml($itemHasil['judul']) ?></span>
                                <span class="badge bg-light text-secondary border"><?= amankanHtml($itemHasil['kategori']) ?></span>
                            </div>
                            <div class="small text-muted mb-1"><?= amankanHtml($itemHasil['cuplikan']) ?></div>
                            <code class="small text-secondary"><?= amankanHtml($itemHasil['jalur']) ?></code>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Tata Letak 3 Kolom: Indeks Kategori Kiri + Isi Dokumen Tengah + Daftar Isi Kanan -->
<div class="row g-4 align-items-start">
    <!-- Kolom 1: Pohon Indeks Dokumentasi -->
    <div class="col-12 col-lg-3 tidak-cetak">
        <div class="card kartu-bankmini border-0 shadow-sm sticky-lg-top" style="top: 1rem;">
            <div class="card-header bg-white py-3 border-bottom">
                <h2 class="h6 fw-bold mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-folder2-open text-primary"></i>
                    <span>Indeks Dokumentasi</span>
                </h2>
            </div>
            <div class="card-body p-2" style="max-height: 75vh; overflow-y: auto;">
                <?php foreach (($katalogDokumentasi ?? []) as $kodeKat => $infoKat) : ?>
                    <?php if (empty($infoKat['daftar_dokumen'])) : ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <div class="px-2 pt-2 pb-1">
                        <div class="text-uppercase fw-bold text-secondary" style="font-size: 0.7rem; letter-spacing: 0.04em;">
                            <?= amankanHtml($infoKat['judul_kategori']) ?>
                        </div>
                    </div>
                    <div class="list-group list-group-flush mb-2">
                        <?php foreach ($infoKat['daftar_dokumen'] as $itemDok) : ?>
                            <?php $apakahDokAktif = ($dokumenAktif['jalur'] ?? '') === $itemDok['jalur']; ?>
                            <a
                                href="<?= amankanHtml(urlDasar('dokumentasi?dok=' . rawurlencode($itemDok['jalur']))) ?>"
                                class="list-group-item list-group-item-action border-0 rounded-2 py-1 px-2 small d-flex align-items-center gap-2 <?= $apakahDokAktif ? 'bg-primary text-white fw-semibold' : 'text-dark' ?>"
                            >
                                <i class="bi <?= $apakahDokAktif ? 'bi-file-earmark-text-fill' : 'bi-file-earmark-text text-secondary' ?>"></i>
                                <span class="text-truncate" title="<?= amankanHtml($itemDok['judul']) ?>">
                                    <?= amankanHtml($itemDok['judul']) ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Kolom 2: Area Pembaca Dokumen Utama -->
    <div class="col-12 <?= !empty($dokumenAktif['daftar_isi']) ? 'col-lg-7' : 'col-lg-9' ?>">
        <div class="card kartu-bankmini border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2 tidak-cetak">
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <i class="bi bi-file-earmark-richtext text-primary"></i>
                    <span>Berkas SoT:</span>
                    <code class="px-2 py-1 rounded bg-light text-dark border">
                        dokumentasi/<?= amankanHtml($dokumenAktif['jalur'] ?? '') ?>
                    </code>
                </div>
                <?php if (!empty($dokumenAktif['waktu_ubah'])) : ?>
                    <span class="badge bg-light text-secondary border">
                        <i class="bi bi-clock me-1"></i>Diperbarui: <?= amankanHtml(formatTanggalIndonesia($dokumenAktif['waktu_ubah'])) ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body p-4 p-md-5 area-baca-dokumentasi">
                <?= $dokumenAktif['html'] ?? '' ?>
            </div>
        </div>
    </div>

    <!-- Kolom 3: Daftar Isi Halaman (On This Page) -->
    <?php if (!empty($dokumenAktif['daftar_isi'])) : ?>
        <div class="col-12 col-lg-2 d-none d-lg-block tidak-cetak">
            <div class="card kartu-bankmini border-0 shadow-sm sticky-lg-top" style="top: 1rem;">
                <div class="card-header bg-white py-2 px-3 border-bottom">
                    <div class="fw-bold small text-secondary text-uppercase" style="font-size: 0.7rem;">
                        <i class="bi bi-list-nested me-1"></i>Daftar Isi
                    </div>
                </div>
                <div class="card-body p-2" style="max-height: 70vh; overflow-y: auto;">
                    <nav class="nav flex-column">
                        <?php foreach ($dokumenAktif['daftar_isi'] as $butirIsi) : ?>
                            <a
                                href="#<?= amankanHtml($butirIsi['id']) ?>"
                                class="nav-link py-1 px-2 small text-secondary text-truncate <?= ($butirIsi['level'] ?? 2) === 3 ? 'ps-3' : 'fw-semibold' ?>"
                                style="font-size: 0.78rem;"
                                title="<?= amankanHtml($butirIsi['teks']) ?>"
                            >
                                <?= amankanHtml($butirIsi['teks']) ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

