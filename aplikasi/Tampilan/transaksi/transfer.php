<div class="kartu">
    <h3>Formulir Pemindahbukuan / Transfer Antar Rekening (Rupiah IDR)</h3>
    <form method="POST" action="<?= amankanHtml(urlDasar('transaksi/transfer')) ?>" data-cegah-ganda="ya">
        <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">
        <div class="baris-form">
            <label for="id_rekening_asal">Rekening Pengirim (DEBIT)</label>
            <select id="id_rekening_asal" name="id_rekening_asal" required>
                <option value="">-- Pilih Rekening Pengirim --</option>
                <?php if (!empty($daftarRekening) && is_array($daftarRekening)) : ?>
                    <?php foreach ($daftarRekening as $barisRekening) : ?>
                        <option value="<?= amankanHtml($barisRekening['id'] ?? '') ?>">
                            <?= amankanHtml(
                                ($barisRekening['nomor_rekening'] ?? '') . ' - '
                                . ($barisRekening['nama_nasabah'] ?? '')
                                . ' (Saldo: ' . formatRupiah((string) ($barisRekening['saldo'] ?? '0')) . ')'
                            ) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="baris-form">
            <label for="id_rekening_tujuan">Rekening Penerima (KREDIT)</label>
            <select id="id_rekening_tujuan" name="id_rekening_tujuan" required>
                <option value="">-- Pilih Rekening Penerima --</option>
                <?php if (!empty($daftarRekening) && is_array($daftarRekening)) : ?>
                    <?php foreach ($daftarRekening as $barisRekening) : ?>
                        <option value="<?= amankanHtml($barisRekening['id'] ?? '') ?>">
                            <?= amankanHtml(($barisRekening['nomor_rekening'] ?? '') . ' - ' . ($barisRekening['nama_nasabah'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="baris-form">
            <label for="nominal_transfer">Nominal Transfer (Rupiah IDR)</label>
            <input type="number" id="nominal_transfer" name="nominal_transfer" min="1000" step="500" required>
        </div>
        <div class="baris-form">
            <label for="keterangan">Keterangan Transfer</label>
            <input type="text" id="keterangan" name="keterangan" placeholder="Contoh: Pemindahbukuan tabungan">
        </div>
        <button type="submit" class="tombol-utama">Proses Transfer</button>
    </form>
</div>

