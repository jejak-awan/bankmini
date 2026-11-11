<?php
/**
 * Komponen UI Bersama: Kepala Kartu Standar (Card Header).
 * Menampilkan judul bagian beserta ikon Bootstrap secara konsisten pada kartu tabel maupun formulir.
 */
$judulKartu = (string) ($judul ?? '');
$ikonKartu = (string) ($ikon ?? 'bi-card-list');
?>
<div class="card-header bg-white py-3">
    <h2 class="h6 mb-0 fw-bold">
        <i class="bi <?= amankanHtml($ikonKartu) ?> me-1 text-primary"></i>
        <?= amankanHtml($judulKartu) ?>
    </h2>
</div>
