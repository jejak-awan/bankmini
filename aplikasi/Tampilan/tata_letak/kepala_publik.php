<?php
/**
 * Tata Letak Kepala Publik (Public Shell Head) Bank Mini.
 * Digunakan oleh halaman publik (/portal) dan halaman autentikasi (/masuk) agar ringan
 * tanpa membawa beban CSS sidebar, topbar petugas, maupun komponen khusus dasbor admin.
 */
$kelasBodyPublik = (string) ($kelasBody ?? 'd-flex flex-column min-vh-100');
$versiAset = (string) ($versiAplikasi ?? '1.0.0');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= amankanHtml($judulHalaman ?? 'Sistem Informasi Bank Mini') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= amankanHtml(urlDasar('aset/favicon.svg')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/vendor/bootstrap/css/bootstrap.min.css?v=' . $versiAset)) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/vendor/bootstrap-icons/font/bootstrap-icons.min.css?v=' . $versiAset)) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/css/token-desain.css?v=' . $versiAset)) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/css/komponen-ui.css?v=' . $versiAset)) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/css/publik.css?v=' . $versiAset)) ?>">
</head>
<body class="<?= amankanHtml($kelasBodyPublik) ?>">

