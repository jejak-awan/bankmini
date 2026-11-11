<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= amankanHtml($judulHalaman ?? 'Masuk Sistem Bank Mini') ?></title>
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/vendor/bootstrap/css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/vendor/bootstrap-icons/font/bootstrap-icons.min.css')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/css/gaya.css')) ?>">
</head>
<body class="halaman-masuk-bankmini d-flex align-items-center justify-content-center min-vh-100 p-3 p-md-4">
<div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="width: 100%; max-width: 440px;">
    <div class="card-body p-4 p-md-5 bg-white">
        <div class="text-center mb-4">
            <div class="bilah-sisi-merek-ikon mx-auto mb-3" style="width: 58px; height: 58px; font-size: 1.65rem; border-radius: 1rem;">
                <i class="bi bi-bank2"></i>
            </div>
            <h1 class="h4 fw-bold text-dark mb-1">Bank Mini Sekolah</h1>
            <p class="text-muted small mb-0">Portal Operasional Petugas &bull; Mata Uang Rupiah (IDR)</p>
        </div>

        <?php if (!empty($pesanKilat) && is_array($pesanKilat)) : ?>
            <?php $kelasAlert = ($pesanKilat['jenis'] ?? '') === 'sukses' ? 'alert-success' : 'alert-danger'; ?>
            <div class="alert <?= amankanHtml($kelasAlert) ?> border-0 py-2 px-3 small d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-info-circle-fill"></i>
                <span><?= amankanHtml($pesanKilat['pesan'] ?? '') ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= amankanHtml(urlDasar('masuk')) ?>" data-cegah-ganda="ya">
            <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">

            <div class="mb-3">
                <label for="nama_pengguna" class="form-label fw-semibold small text-secondary">Nama Pengguna</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-person"></i></span>
                    <input
                        type="text"
                        class="form-control border-start-0 ps-1"
                        id="nama_pengguna"
                        name="nama_pengguna"
                        required
                        autocomplete="username"
                        placeholder="Contoh: teller1"
                        autofocus
                    >
                </div>
            </div>

            <div class="mb-4">
                <label for="kata_sandi" class="form-label fw-semibold small text-secondary">Kata Sandi</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-lock"></i></span>
                    <input
                        type="password"
                        class="form-control border-start-0 border-end-0 ps-1"
                        id="kata_sandi"
                        name="kata_sandi"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi"
                    >
                    <button
                        class="btn btn-light border border-start-0 text-secondary"
                        type="button"
                        data-toggle-sandi="kata_sandi"
                        aria-label="Tampilkan atau sembunyikan kata sandi"
                    >
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn tombol-bankmini w-100 py-2 fw-semibold rounded-3">
                <i class="bi bi-box-arrow-in-right me-1"></i>Masuk ke Sistem
            </button>
        </form>

        <div class="mt-4 pt-3 border-top text-center">
            <div class="small text-muted">
                Versi <?= amankanHtml($versiAplikasi ?? '1.0.0') ?> &bull; Bahan Pembelajaran SMK RPL
            </div>
        </div>
    </div>
</div>
<script src="<?= amankanHtml(urlDasar('aset/vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= amankanHtml(urlDasar('aset/js/aplikasi.js')) ?>"></script>
</body>
</html>
