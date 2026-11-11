<?php
/**
 * Komponen UI Bersama: Bilah Pencarian Cepat Tabel (Client-side Search Input).
 * Terhubung otomatis dengan skrip `dasbor-interaktif.js` melalui atribut `data-cari-tabel`.
 */
$idTabelTarget = (string) ($idTabel ?? '');
$teksPlaceholder = (string) ($placeholder ?? 'Cari data...');
$lebarMaksimal = (string) ($lebarMaksimal ?? '260px');
?>
<div class="input-group input-group-sm tidak-cetak" style="max-width: <?= amankanHtml($lebarMaksimal) ?>;">
    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
    <input
        type="text"
        class="form-control"
        placeholder="<?= amankanHtml($teksPlaceholder) ?>"
        data-cari-tabel="<?= amankanHtml($idTabelTarget) ?>"
        aria-label="<?= amankanHtml($teksPlaceholder) ?>"
    >
</div>

