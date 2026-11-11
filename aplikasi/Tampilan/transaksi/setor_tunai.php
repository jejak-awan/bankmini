<div class="kartu">
    <h3>Formulir Setor Tunai (KREDIT - Rupiah IDR)</h3>
    <form method="POST" action="<?= amankanHtml(urlDasar('transaksi/setor')) ?>" data-cegah-ganda="ya">
        <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">
        <div class="baris-form">
            <label for="id_rekening">Pilih Rekening Tujuan</label>
            <select id="id_rekening" name="id_rekening" required>
                <option value="">-- Pilih Rekening --</option>
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
            <label for="nominal_setoran">Nominal Setoran (Rupiah IDR)</label>
            <input type="number" id="nominal_setoran" name="nominal_setoran" min="1000" step="500" required>
        </div>
        <div class="baris-form">
            <label for="keterangan">Keterangan Setoran</label>
            <input type="text" id="keterangan" name="keterangan" placeholder="Contoh: Setoran tabungan rutin">
        </div>
        <button type="submit" class="tombol-utama">Proses Setor Tunai</button>
    </form>
</div>

