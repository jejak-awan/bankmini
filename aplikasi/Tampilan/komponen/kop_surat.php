<?php
/**
 * Komponen UI Bersama: Kop Surat Resmi Mode Cetak (Print Header).
 * Tampil khusus saat mencetak halaman (@media print) melalui kelas `.hanya-cetak`.
 */
$judulDokumen = (string) ($judulDokumen ?? 'DOKUMEN RESMI BANK MINI SEKOLAH');
$subjudulDokumen = (string) ($subjudulDokumen ?? '');
?>
<div class="hanya-cetak mb-3 border-bottom pb-2">
    <div class="small text-uppercase fw-semibold text-secondary">
        Sistem Informasi Bank Mini Sekolah &bull; Mata Uang Rupiah (IDR)
    </div>
    <h2 class="h4 fw-bold mb-1"><?= amankanHtml($judulDokumen) ?></h2>
    <?php if ($subjudulDokumen !== '') : ?>
        <div class="small"><?= amankanHtml($subjudulDokumen) ?></div>
    <?php endif; ?>
</div>

