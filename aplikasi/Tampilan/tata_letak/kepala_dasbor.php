<?php
/**
 * Tata Letak Kepala Dasbor Operasional (Dashboard Shell Head) Bank Mini.
 * Memuat Design Token, Komponen UI bersama, dan stylesheet khusus Dasbor (`dasbor.css`),
 * serta menyertakan bilah navigasi mobile (offcanvas trigger).
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= amankanHtml($judulHalaman ?? 'Sistem Informasi Bank Mini') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= amankanHtml(urlDasar('aset/favicon.svg')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/vendor/bootstrap/css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/vendor/bootstrap-icons/font/bootstrap-icons.min.css')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/css/token-desain.css')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/css/komponen-ui.css')) ?>">
    <link rel="stylesheet" href="<?= amankanHtml(urlDasar('aset/css/dasbor.css')) ?>">
</head>
<body>
<nav class="navbar navbar-dark d-lg-none tidak-cetak" style="background-color: #0b2545;">
    <div class="container-fluid px-3">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= amankanHtml(urlDasar('dasbor')) ?>">
            <span class="badge text-bg-primary p-2"><i class="bi bi-bank2"></i></span>
            <span>Bank Mini (IDR)</span>
        </a>
        <button
            class="navbar-toggler border-0"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#menuBilahSisi"
            aria-controls="menuBilahSisi"
            aria-label="Buka menu navigasi"
        >
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>
<div class="d-flex w-100">
