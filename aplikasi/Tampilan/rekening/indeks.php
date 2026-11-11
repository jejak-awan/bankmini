<div class="kartu">
    <h3>Pembukaan Rekening Baru & Setoran Awal (Rupiah IDR)</h3>
    <form method="POST" action="<?= amankanHtml(urlDasar('rekening/buka')) ?>" data-cegah-ganda="ya">
        <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">
        <div class="baris-form">
            <label for="id_nasabah">Pilih Nasabah</label>
            <select id="id_nasabah" name="id_nasabah" required>
                <option value="">-- Pilih Nasabah --</option>
                <?php if (!empty($daftarNasabah) && is_array($daftarNasabah)) : ?>
                    <?php foreach ($daftarNasabah as $barisNasabah) : ?>
                        <option value="<?= amankanHtml($barisNasabah['id'] ?? '') ?>">
                            <?= amankanHtml(($barisNasabah['nomor_identitas'] ?? '') . ' - ' . ($barisNasabah['nama_lengkap'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="baris-form">
            <label for="id_produk">Pilih Produk Tabungan</label>
            <select id="id_produk" name="id_produk" required>
                <option value="">-- Pilih Produk Tabungan --</option>
                <?php if (!empty($daftarProduk) && is_array($daftarProduk)) : ?>
                    <?php foreach ($daftarProduk as $barisProduk) : ?>
                        <option value="<?= amankanHtml($barisProduk['id'] ?? '') ?>">
                            <?= amankanHtml(
                                ($barisProduk['kode_produk'] ?? '') . ' - ' . ($barisProduk['nama_produk'] ?? '')
                                . ' (Min. ' . formatRupiah((string) ($barisProduk['setoran_awal_minimum'] ?? '0')) . ')'
                            ) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="baris-form">
            <label for="nominal_setoran_awal">Nominal Setoran Awal (Rupiah IDR)</label>
            <input type="number" id="nominal_setoran_awal" name="nominal_setoran_awal" min="1000" step="500" required>
        </div>
        <button type="submit" class="tombol-utama">Buka Rekening & Simpan Setoran Awal</button>
    </form>
</div>

<div class="kartu">
    <h3>Daftar Rekening Tabungan</h3>
    <table class="tabel-data">
        <thead>
            <tr>
                <th>No. Rekening</th>
                <th>Nasabah</th>
                <th>Produk</th>
                <th>Saldo Saat Ini (IDR)</th>
                <th>Saldo Mengendap Min. (IDR)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($daftarRekening) && is_array($daftarRekening)) : ?>
                <?php foreach ($daftarRekening as $barisRekening) : ?>
                    <tr>
                        <td><?= amankanHtml($barisRekening['nomor_rekening'] ?? '') ?></td>
                        <td><?= amankanHtml($barisRekening['nama_nasabah'] ?? '') ?></td>
                        <td><?= amankanHtml($barisRekening['nama_produk'] ?? '') ?></td>
                        <td><?= amankanHtml(formatRupiah((string) ($barisRekening['saldo'] ?? '0'))) ?></td>
                        <td><?= amankanHtml(formatRupiah((string) ($barisRekening['saldo_mengendap_minimum'] ?? '0'))) ?></td>
                        <td><?= amankanHtml($barisRekening['status'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6">Belum ada data rekening tabungan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

