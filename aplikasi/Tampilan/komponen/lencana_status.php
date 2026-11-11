<?php
/**
 * Komponen UI Bersama: Lencana Status Semantik (Status & Ledger Badge).
 * Menyeragamkan warna lencana status rekening, nasabah, dan tipe mutasi buku besar
 * (KREDIT/AKTIF = hijau, DEBIT/TUTUP = merah, KOREKSI/BEKU = kuning) di seluruh tampilan.
 */
$teksStatus = strtoupper(trim((string) ($status ?? '')));
$petaKelasLencana = [
    'KREDIT' => 'text-bg-success',
    'AKTIF' => 'text-bg-success',
    'DEBIT' => 'text-bg-danger',
    'TUTUP' => 'text-bg-danger',
    'KOREKSI' => 'text-bg-warning',
    'BEKU' => 'text-bg-warning',
    'NONAKTIF' => 'text-bg-secondary',
    'SETOR' => 'text-bg-light border text-dark',
    'TARIK' => 'text-bg-light border text-dark',
    'TRANSFER' => 'text-bg-primary bg-opacity-75',
];
$kelasLencana = (string) ($kelasTambahan ?? ($petaKelasLencana[$teksStatus] ?? 'text-bg-secondary'));
?>
<span class="badge <?= amankanHtml($kelasLencana) ?>">
    <?= amankanHtml($teksStatus !== '' ? $teksStatus : '-') ?>
</span>
