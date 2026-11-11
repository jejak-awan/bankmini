<?php
/**
 * Tampilan Halaman Galat Terpusat (403, 404, 500, 503) — Tata Letak Publik.
 * Menampilkan pesan ramah pengguna beserta Correlation ID, dan menampilkan jejak debug
 * terstruktur hanya ketika `mode_debug = true` untuk pembelajaran siswa SMK RPL.
 */
$kodeAngka = (int) ($kodeHttp ?? 500);
$kelasBadge = match ($kodeAngka) {
    403 => 'text-bg-warning text-dark',
    404 => 'text-bg-primary',
    503 => 'text-bg-info text-dark',
    default => 'text-bg-danger',
};
$ikonGalat = match ($kodeAngka) {
    403 => 'bi-shield-lock-fill',
    404 => 'bi-signpost-split-fill',
    503 => 'bi-tools',
    default => 'bi-exclamation-triangle-fill',
};
$tautanTujuan = (string) ($tautanUtama ?? urlDasar('portal'));
$teksTombolUtama = (string) ($labelUtama ?? 'Ke Portal Publik');
?>
<main class="container py-5 my-auto" style="max-width: 780px;">
    <div class="card kartu-bankmini border-0 shadow-sm overflow-hidden">
        <div class="card-body p-4 p-md-5 text-center">
            <div class="mb-3">
                <span class="badge <?= amankanHtml($kelasBadge) ?> px-3 py-2 rounded-pill fs-6">
                    <i class="bi <?= amankanHtml($ikonGalat) ?> me-1"></i>
                    HTTP <?= amankanHtml((string) $kodeAngka) ?>
                </span>
            </div>

            <h1 class="h3 fw-bold text-dark mb-3"><?= amankanHtml($judulGalat ?? 'Terjadi Kendala') ?></h1>
            <p class="text-muted mb-4"><?= amankanHtml($pesanRamah ?? 'Permintaan tidak dapat diproses.') ?></p>

            <div class="d-inline-flex align-items-center gap-2 bg-light border rounded-3 px-3 py-2 mb-4 small text-secondary">
                <i class="bi bi-fingerprint text-primary"></i>
                <span>ID Korelasi Log:</span>
                <code class="fw-semibold text-dark"><?= amankanHtml($idKorelasi ?? '-') ?></code>
            </div>

            <div class="d-flex justify-content-center flex-wrap gap-2">
                <a href="<?= amankanHtml($tautanTujuan) ?>" class="btn tombol-bankmini px-4">
                    <i class="bi bi-house-door-fill me-1"></i><?= amankanHtml($teksTombolUtama) ?>
                </a>
                <a href="<?= amankanHtml(urlDasar('masuk')) ?>" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Halaman Masuk
                </a>
            </div>
        </div>

        <?php if (!empty($modeDebug) && is_array($detailDebug ?? null)) : ?>
            <div class="card-footer bg-dark text-light p-4 text-start border-0">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                    <span class="badge text-bg-warning text-dark">
                        <i class="bi bi-bug-fill me-1"></i>Mode Debug Aktif (Lingkungan Pengembangan RPL)
                    </span>
                    <small class="text-secondary">Atur `BANKMINI_MODE_DEBUG=false` di produksi</small>
                </div>
                <p class="small mb-1">
                    <strong>Kelas Pengecualian:</strong>
                    <code><?= amankanHtml((string) ($detailDebug['kelas'] ?? '-')) ?></code>
                </p>
                <p class="small mb-1">
                    <strong>Lokasi:</strong>
                    <code><?= amankanHtml((string) ($detailDebug['berkas'] ?? '-')) ?>:<?= amankanHtml((string) ($detailDebug['baris'] ?? 0)) ?></code>
                </p>
                <p class="small mb-2">
                    <strong>Pesan Teknis:</strong>
                    <?= amankanHtml((string) ($detailDebug['pesan'] ?? '-')) ?>
                </p>
                <pre class="bg-black bg-opacity-50 text-info p-3 rounded small mb-0 overflow-auto" style="max-height: 240px;"><?= amankanHtml((string) ($detailDebug['jejak'] ?? '')) ?></pre>
            </div>
        <?php endif; ?>
    </div>
</main>
