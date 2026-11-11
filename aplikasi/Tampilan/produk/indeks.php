<div class="kartu">
    <h3>Daftar Produk Tabungan (Mata Uang: Rupiah IDR)</h3>
    <table class="tabel-data">
        <thead>
            <tr>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Setoran Awal Min. (IDR)</th>
                <th>Saldo Mengendap Min. (IDR)</th>
                <th>Biaya Admin Bulanan (IDR)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($daftarProduk) && is_array($daftarProduk)) : ?>
                <?php foreach ($daftarProduk as $barisProduk) : ?>
                    <tr>
                        <td><?= amankanHtml($barisProduk['kode_produk'] ?? '') ?></td>
                        <td><?= amankanHtml($barisProduk['nama_produk'] ?? '') ?></td>
                        <td><?= amankanHtml(formatRupiah((string) ($barisProduk['setoran_awal_minimum'] ?? '0'))) ?></td>
                        <td><?= amankanHtml(formatRupiah((string) ($barisProduk['saldo_mengendap_minimum'] ?? '0'))) ?></td>
                        <td><?= amankanHtml(formatRupiah((string) ($barisProduk['biaya_admin_bulanan'] ?? '0'))) ?></td>
                        <td><?= amankanHtml($barisProduk['status'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6">Belum ada data produk tabungan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

