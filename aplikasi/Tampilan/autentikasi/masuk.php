<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= amankanHtml($judulHalaman ?? 'Masuk Sistem Bank Mini') ?></title>
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/css/gaya.css')) ?>">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
<div class="kartu" style="width: 100%; max-width: 420px;">
    <h2 style="margin-bottom: 1rem;">Masuk Sistem Bank Mini</h2>
    <?php if (!empty($pesanKilat) && is_array($pesanKilat)) : ?>
        <div class="pesan-kilat <?= ($pesanKilat['jenis'] ?? '') === 'sukses' ? 'pesan-sukses' : 'pesan-kesalahan' ?>">
            <?= amankanHtml($pesanKilat['pesan'] ?? '') ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="<?= amankanHtml(urlDasar('masuk')) ?>" data-cegah-ganda="ya">
        <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">
        <div class="baris-form">
            <label for="nama_pengguna">Nama Pengguna</label>
            <input type="text" id="nama_pengguna" name="nama_pengguna" required autocomplete="username">
        </div>
        <div class="baris-form">
            <label for="kata_sandi">Kata Sandi</label>
            <input type="password" id="kata_sandi" name="kata_sandi" required autocomplete="current-password">
        </div>
        <button type="submit" class="tombol-utama" style="width: 100%;">Masuk</button>
    </form>
</div>
</body>
</html>

