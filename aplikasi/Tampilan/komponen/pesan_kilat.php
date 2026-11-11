<?php
/**
 * Komponen UI Bersama: Pesan Kilat (Flash Alert).
 * Menampilkan notifikasi sukses, peringatan, atau kesalahan secara seragam
 * di halaman Publik (Login/Portal) maupun halaman Dasbor Operasional.
 */
if (empty($pesanKilat) || !is_array($pesanKilat)) {
    return;
}

$jenisPesan = (string) ($pesanKilat['jenis'] ?? 'kesalahan');
$kelasAlert = match ($jenisPesan) {
    'sukses' => 'alert-success',
    'peringatan' => 'alert-warning',
    default => 'alert-danger',
};
$ikonAlert = match ($jenisPesan) {
    'sukses' => 'bi-check-circle-fill',
    'peringatan' => 'bi-exclamation-circle-fill',
    default => 'bi-exclamation-triangle-fill',
};
$dapatDitutup = (bool) ($dapatDitutup ?? true);
$kelasMargin = (string) ($kelasMargin ?? 'mb-4');
?>
<div
    class="alert <?= amankanHtml($kelasAlert) ?> <?= $dapatDitutup ? 'alert-dismissible fade show' : '' ?> shadow-sm border-0 <?= amankanHtml($kelasMargin) ?> tidak-cetak d-flex align-items-center gap-2"
    role="alert"
>
    <i class="bi <?= amankanHtml($ikonAlert) ?> flex-shrink-0"></i>
    <span class="flex-grow-1"><?= amankanHtml($pesanKilat['pesan'] ?? '') ?></span>
    <?php if ($dapatDitutup) : ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    <?php endif; ?>
</div>

