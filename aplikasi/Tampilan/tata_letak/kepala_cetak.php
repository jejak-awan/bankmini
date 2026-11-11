<?php
/**
 * Tata Letak Kepala Mode Cetak (Print Shell Head) Bank Mini.
 * Hanya memuat Design Token & Komponen UI untuk kebutuhan cetak/dokumen bebas navigasi dan skrip JS.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= amankanHtml($judulHalaman ?? 'Cetak Dokumen Bank Mini') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= amankanHtml(urlDasar('aset/favicon.svg')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/vendor/bootstrap/css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/css/token-desain.css')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/css/komponen-ui.css')) ?>">
</head>
<body class="bg-white p-3 p-md-4">
