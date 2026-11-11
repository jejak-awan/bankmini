<?php
/**
 * Tampilan Dasbor Utama Bank Mini (PHP Native + Bootstrap 5 Offline).
 * Menampilkan ringkasan KPI (Total Nasabah, Rekening, Total Dana Tabungan),
 * tombol aksi cepat teller, dan 10 mutasi transaksi terbaru menggunakan komponen UI bersama.
 */
?>
<!-- Ringkasan Statistik Utama (KPI) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <?= komponenUi('kartu_kpi', [
            'judul' => 'Total Nasabah',
            'nilai' => $jumlahNasabah ?? 0,
            'ikon' => 'bi-people-fill',
            'aksen' => 'biru',
        ]) ?>
    </div>

    <div class="col-12 col-md-4">
        <?= komponenUi('kartu_kpi', [
            'judul' => 'Total Rekening Aktif',
            'nilai' => $jumlahRekening ?? 0,
            'ikon' => 'bi-credit-card-2-front-fill',
            'aksen' => 'toska',
        ]) ?>
    </div>

    <div class="col-12 col-md-4">
        <?= komponenUi('kartu_kpi', [
            'judul' => 'Total Dana Tabungan (IDR)',
            'nilai' => formatRupiah((string) ($totalSaldoRupiah ?? '0.00')),
            'ikon' => 'bi-safe-fill',
            'aksen' => 'hijau',
        ]) ?>
    </div>
</div>

<!-- Aksi Cepat Operasional Teller & Petugas -->
<div class="card kartu-bankmini mb-4 tidak-cetak">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="fw-semibold text-secondary">
            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
            Aksi Cepat Layanan Nasabah:
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= amankanHtml(urlDasar('transaksi/setor')) ?>" class="btn btn-sm btn-success">
                <i class="bi bi-arrow-down-circle me-1"></i>Setor Tunai
            </a>
            <a href="<?= amankanHtml(urlDasar('transaksi/tarik')) ?>" class="btn btn-sm btn-danger">
                <i class="bi bi-arrow-up-circle me-1"></i>Tarik Tunai
            </a>
            <a href="<?= amankanHtml(urlDasar('transaksi/transfer')) ?>" class="btn btn-sm tombol-bankmini">
                <i class="bi bi-arrow-left-right me-1"></i>Transfer
            </a>
            <a href="<?= amankanHtml(urlDasar('rekening')) ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-circle me-1"></i>Buka Rekening
            </a>
        </div>
    </div>
</div>

<!-- Tabel 10 Mutasi Transaksi Terbaru -->
<div class="card kartu-bankmini">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h2 class="h6 mb-0 fw-bold">
            <i class="bi bi-clock-history me-1 text-primary"></i>
            10 Mutasi Transaksi Terbaru (Rupiah IDR)
        </h2>
        <a href="<?= amankanHtml(urlDasar('laporan/mutasi')) ?>" class="btn btn-sm btn-outline-secondary tidak-cetak">
            Lihat Semua Mutasi <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table tabel-bankmini table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Referensi</th>
                    <th>No. Rekening</th>
                    <th>Nasabah</th>
                    <th>Jenis</th>
                    <th>Tipe</th>
                    <th class="text-end">Nominal (IDR)</th>
                    <th class="text-end">Saldo Sesudah (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($daftarMutasiTerbaru) && is_array($daftarMutasiTerbaru)) : ?>
                    <?php foreach ($daftarMutasiTerbaru as $barisMutasi) : ?>
                        <?php
                        $jenisTransaksi = (string) ($barisMutasi['jenis_transaksi'] ?? '');
                        $tipeMutasi = (string) ($barisMutasi['tipe_mutasi'] ?? '');
                        ?>
                        <tr>
                            <td class="small text-nowrap">
                                <?= amankanHtml(formatTanggalIndonesia((string) ($barisMutasi['waktu_transaksi'] ?? ''))) ?>
                            </td>
                            <td class="small font-monospace text-nowrap">
                                <?= amankanHtml($barisMutasi['kode_referensi'] ?? '') ?>
                            </td>
                            <td class="angka-keuangan">
                                <?= amankanHtml($barisMutasi['nomor_rekening'] ?? '') ?>
                            </td>
                            <td class="text-nowrap"><?= amankanHtml($barisMutasi['nama_nasabah'] ?? '') ?></td>
                            <td>
                                <?php if ($jenisTransaksi === 'KOREKSI') : ?>
                                    <?= komponenUi('lencana_status', ['status' => 'KOREKSI']) ?>
                                <?php else : ?>
                                    <span class="small fw-semibold"><?= amankanHtml($jenisTransaksi) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= komponenUi('lencana_status', [
                                    'status' => $jenisTransaksi === 'KOREKSI' ? 'KOREKSI' : $tipeMutasi,
                                ]) ?>
                            </td>
                            <td class="angka-keuangan text-end fw-semibold">
                                <?= amankanHtml(formatRupiah((string) ($barisMutasi['nominal'] ?? '0'))) ?>
                            </td>
                            <td class="angka-keuangan text-end">
                                <?= amankanHtml(formatRupiah((string) ($barisMutasi['saldo_sesudah'] ?? '0'))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Belum ada riwayat mutasi transaksi.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
