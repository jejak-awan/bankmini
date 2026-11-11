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
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
<div class="card kartu-bankmini border-0 shadow-sm" style="width: 100%; max-width: 440px;">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3" style="width: 64px; height: 64px;">
                <i class="bi bi-bank2 fs-2"></i>
            </div>
            <h1 class="h4 fw-bold mb-1">Sistem Informasi Bank Mini</h1>
            <p class="text-muted small mb-0">Portal Operasional Petugas &bull; Mata Uang Rupiah (IDR)</p>
        </div>

        <?php if (!empty($pesanKilat) && is_array($pesanKilat)) : ?>
            <?php $kelasAlert = ($pesanKilat['jenis'] ?? '') === 'sukses' ? 'alert-success' : 'alert-danger'; ?>
            <div class="alert <?= amankanHtml($kelasAlert) ?> py-2 small" role="alert">
                <?= amankanHtml($pesanKilat['pesan'] ?? '') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= amankanHtml(urlDasar('masuk')) ?>" data-cegah-ganda="ya">
            <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">
            <div class="mb-3">
                <label for="nama_pengguna" class="form-label fw-semibold">Nama Pengguna</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="nama_pengguna" name="nama_pengguna" required autocomplete="username" placeholder="Contoh: teller1">
                </div>
            </div>
            <div class="mb-4">
                <label for="kata_sandi" class="form-label fw-semibold">Kata Sandi</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="kata_sandi" name="kata_sandi" required autocomplete="current-password" placeholder="Masukkan kata sandi">
                </div>
            </div>
            <button type="submit" class="btn tombol-bankmini w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i>Masuk ke Sistem
            </button>
        </form>

        <div class="mt-4 pt-3 border-top text-center text-muted" style="font-size: 0.78rem;">
            Versi <?= amankanHtml($versiAplikasi ?? '1.0.0') ?> &bull; Bahan Belajar RPL PHP Native
        </div>
    </div>
</div>
<script src="<?= amankanHtml(urlDasar('aset/vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= amankanHtml(urlDasar('aset/js/aplikasi.js')) ?>"></script>
</body>
</html>
