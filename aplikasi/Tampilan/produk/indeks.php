<?php
/**
 * Tampilan Daftar Produk Tabungan Bank Mini.
 * Menyajikan tabel parameter produk tabungan (setoran awal minimum,
 * saldo mengendap minimum, biaya admin bulanan) dengan perataan angka tabular.
 */
$petaStatusProduk = [
    'AKTIF' => 'text-bg-success',
    'NONAKTIF' => 'text-bg-secondary',
];
?>
<div class="card kartu-bankmini">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h2 class="h6 mb-0 fw-bold">
            <i class="bi bi-journal-bookmark-fill me-1 text-primary"></i>
            Daftar Produk Tabungan (Mata Uang: Rupiah IDR)
        </h2>
        <span class="badge text-bg-light border text-secondary">
            Parameter Aturan Produk Perbankan
        </span>
    </div>
    <div class="table-responsive">
        <table class="table tabel-bankmini table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th class="text-end">Setoran Awal Min. (IDR)</th>
                    <th class="text-end">Saldo Mengendap Min. (IDR)</th>
                    <th class="text-end">Biaya Admin Bulanan (IDR)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($daftarProduk) && is_array($daftarProduk)) : ?>
                    <?php foreach ($daftarProduk as $barisProduk) : ?>
                        <?php
                        $statusProduk = strtoupper((string) ($barisProduk['status'] ?? 'AKTIF'));
                        $kelasStatus = $petaStatusProduk[$statusProduk] ?? 'text-bg-secondary';
                        ?>
                        <tr>
                            <td class="font-monospace fw-semibold">
                                <?= amankanHtml($barisProduk['kode_produk'] ?? '') ?>
                            </td>
                            <td><?= amankanHtml($barisProduk['nama_produk'] ?? '') ?></td>
                            <td class="angka-keuangan text-end">
                                <?= amankanHtml(formatRupiah((string) ($barisProduk['setoran_awal_minimum'] ?? '0'))) ?>
                            </td>
                            <td class="angka-keuangan text-end">
                                <?= amankanHtml(formatRupiah((string) ($barisProduk['saldo_mengendap_minimum'] ?? '0'))) ?>
                            </td>
                            <td class="angka-keuangan text-end">
                                <?= amankanHtml(formatRupiah((string) ($barisProduk['biaya_admin_bulanan'] ?? '0'))) ?>
                            </td>
                            <td>
                                <span class="badge <?= amankanHtml($kelasStatus) ?>">
                                    <?= amankanHtml($statusProduk) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Belum ada data produk tabungan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
