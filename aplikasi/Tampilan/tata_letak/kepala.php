<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= amankanHtml($judulHalaman ?? 'Sistem Informasi Bank Mini') ?></title>
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/vendor/bootstrap/css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/vendor/bootstrap-icons/font/bootstrap-icons.min.css')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/css/gaya.css')) ?>">
</head>
<body>
<nav class="navbar navbar-dark bg-dark d-lg-none tidak-cetak">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="<?= amankanHtml(urlDasar('dasbor')) ?>">
            <i class="bi bi-bank2 me-2"></i>Bank Mini (IDR)
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuBilahSisi" aria-controls="menuBilahSisi">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>
<div class="d-flex">
