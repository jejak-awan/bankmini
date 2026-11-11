<?php
/**
 * Komponen UI Bersama: Kartu Statistik Utama (KPI Card).
 * Menampilkan ringkasan metrik dengan varian aksen warna Design Token (biru, toska, hijau),
 * tipografi angka tabular, dan ikon Bootstrap yang seragam.
 */
$aksenKartu = (string) ($aksen ?? 'biru');
$petaAksen = [
    'biru' => [
        'kelasKartu' => 'kartu-statistik-aksen-biru',
        'kelasIkon' => 'bg-primary-subtle text-primary',
        'kelasNilai' => 'fs-3 text-dark',
    ],
    'toska' => [
        'kelasKartu' => 'kartu-statistik-aksen-toska',
        'kelasIkon' => 'bg-info-subtle text-info',
        'kelasNilai' => 'fs-3 text-dark',
    ],
    'hijau' => [
        'kelasKartu' => 'kartu-statistik-aksen-hijau',
        'kelasIkon' => 'bg-success-subtle text-success',
        'kelasNilai' => 'fs-4 text-success',
    ],
];
$gayaAktif = $petaAksen[$aksenKartu] ?? $petaAksen['biru'];
?>
<div class="card kartu-bankmini <?= amankanHtml($gayaAktif['kelasKartu']) ?> h-100">
    <div class="card-body d-flex align-items-center justify-content-between gap-3">
        <div>
            <div class="text-muted small fw-semibold text-uppercase">
                <?= amankanHtml($judul ?? '') ?>
            </div>
            <div class="<?= amankanHtml($gayaAktif['kelasNilai']) ?> fw-bold mt-1 angka-keuangan">
                <?= amankanHtml($nilai ?? '0') ?>
            </div>
        </div>
        <div class="kartu-statistik-ikon <?= amankanHtml($gayaAktif['kelasIkon']) ?>">
            <i class="bi <?= amankanHtml($ikon ?? 'bi-bar-chart-fill') ?>"></i>
        </div>
    </div>
</div>

