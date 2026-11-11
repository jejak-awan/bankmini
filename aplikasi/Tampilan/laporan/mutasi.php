<?php
/**
 * Tampilan Laporan Mutasi & Buku Besar Transaksi Bank Mini.
 * Mendukung filter rentang tanggal, pencarian instan pada tabel,
 * badge semantik KREDIT/DEBIT/KOREKSI, serta mode siap cetak (@media print).
 */
$petaLencanaMutasi = [
    'KREDIT' => 'text-bg-success',
    'DEBIT' => 'text-bg-danger',
    'KOREKSI' => 'text-bg-warning',
];
?>
<!-- Kop Laporan Khusus Saat Dicetak (Tampil hanya di @media print) -->
<div class="hanya-cetak mb-3 border-bottom pb-2">
    <h2 class="h4 fw-bold mb-1">LAPORAN MUTASI TRANSAKSI BANK MINI SEKOLAH</h2>
    <div class="small">
        Periode: <?= amankanHtml($tanggalMulai ?? '-') ?> s.d. <?= amankanHtml($tanggalAkhir ?? '-') ?> |
        Dicetak oleh: <?= amankanHtml($namaPenggunaAktif ?? 'Petugas') ?> (<?= amankanHtml($versiAplikasi ?? 'v1.0.0') ?>)
    </div>
</div>

<!-- Filter Rentang Tanggal & Aksi Cetak (Disembunyikan saat print) -->
<div class="card kartu-bankmini mb-4 tidak-cetak">
    <div class="card-header bg-white py-3">
        <h2 class="h6 mb-0 fw-bold">
            <i class="bi bi-funnel-fill text-primary me-1"></i>
            Filter Rentang Tanggal &amp; Cetak Laporan Mutasi
        </h2>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= amankanHtml(urlDasar('laporan/mutasi')) ?>" class="row g-3 align-items-end">
            <div class="col-12 col-sm-4 col-md-3">
                <label for="tanggal_mulai" class="form-label fw-semibold small mb-1">Tanggal Mulai</label>
                <input
                    type="date"
                    class="form-control"
                    id="tanggal_mulai"
                    name="tanggal_mulai"
                    value="<?= amankanHtml($tanggalMulai ?? '') ?>"
                >
            </div>
            <div class="col-12 col-sm-4 col-md-3">
                <label for="tanggal_akhir" class="form-label fw-semibold small mb-1">Tanggal Akhir</label>
                <input
                    type="date"
                    class="form-control"
                    id="tanggal_akhir"
                    name="tanggal_akhir"
                    value="<?= amankanHtml($tanggalAkhir ?? '') ?>"
                >
            </div>
            <div class="col-12 col-sm-4 col-md-6 d-flex flex-wrap gap-2">
                <button type="submit" class="btn tombol-bankmini fw-semibold">
                    <i class="bi bi-search me-1"></i>Tampilkan Mutasi
                </button>
                <button type="button" class="btn btn-success fw-semibold" onclick="window.print();">
                    <i class="bi bi-printer me-1"></i>Cetak Laporan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Buku Besar Mutasi Transaksi -->
<div class="card kartu-bankmini">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h2 class="h6 mb-0 fw-bold">
            <i class="bi bi-file-earmark-spreadsheet-fill text-primary me-1"></i>
            Buku Besar Mutasi Transaksi (Immutable Ledger - Rupiah IDR)
        </h2>
        <div class="input-group input-group-sm tidak-cetak" style="max-width: 260px;">
            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
            <input
                type="text"
                class="form-control"
                placeholder="Cari referensi / nama / keterangan..."
                data-cari-tabel="tabelLaporanMutasi"
            >
        </div>
    </div>
    <div class="table-responsive">
        <table id="tabelLaporanMutasi" class="table tabel-bankmini table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Kode Referensi</th>
                    <th>No. Rekening</th>
                    <th>Nasabah</th>
                    <th>Jenis</th>
                    <th>Tipe</th>
                    <th class="text-end">Saldo Sebelum</th>
                    <th class="text-end">Nominal (IDR)</th>
                    <th class="text-end">Saldo Sesudah</th>
                    <th>Petugas</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($daftarMutasi) && is_array($daftarMutasi)) : ?>
                    <?php foreach ($daftarMutasi as $barisMutasi) : ?>
                        <?php
                        $jenisTransaksi = (string) ($barisMutasi['jenis_transaksi'] ?? '');
                        $tipeMutasi = (string) ($barisMutasi['tipe_mutasi'] ?? '');
                        $kunciBadge = $jenisTransaksi === 'KOREKSI' ? 'KOREKSI' : $tipeMutasi;
                        $kelasBadge = $petaLencanaMutasi[$kunciBadge] ?? 'text-bg-secondary';
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
                                    <span class="badge text-bg-warning">KOREKSI</span>
                                <?php else : ?>
                                    <span class="small fw-semibold"><?= amankanHtml($jenisTransaksi) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= amankanHtml($kelasBadge) ?>">
                                    <?= amankanHtml($tipeMutasi) ?>
                                </span>
                            </td>
                            <td class="angka-keuangan text-end small">
                                <?= amankanHtml(formatRupiah((string) ($barisMutasi['saldo_sebelum'] ?? '0'))) ?>
                            </td>
                            <td class="angka-keuangan text-end fw-semibold">
                                <?= amankanHtml(formatRupiah((string) ($barisMutasi['nominal'] ?? '0'))) ?>
                            </td>
                            <td class="angka-keuangan text-end fw-semibold">
                                <?= amankanHtml(formatRupiah((string) ($barisMutasi['saldo_sesudah'] ?? '0'))) ?>
                            </td>
                            <td class="small"><?= amankanHtml($barisMutasi['nama_petugas'] ?? '') ?></td>
                            <td class="small"><?= amankanHtml($barisMutasi['keterangan'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            Belum ada data mutasi transaksi pada rentang tanggal ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
