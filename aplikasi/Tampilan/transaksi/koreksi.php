<div class="kartu">
    <h3>Formulir Jurnal Koreksi / Reversal (Khusus Supervisor &amp; Admin)</h3>
    <p style="margin-bottom: 1rem; color: #4b5563;">
        Sesuai standar <em>Append-Only / Immutable Ledger</em>, transaksi yang salah input tidak dihapus,
        melainkan dibalikkan menggunakan transaksi baru bertipe <strong>KOREKSI</strong>.
    </p>
    <form method="POST" action="<?= amankanHtml(urlDasar('transaksi/koreksi')) ?>" data-cegah-ganda="ya">
        <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">
        <div class="baris-form">
            <label for="id_mutasi_asal">Pilih Transaksi yang Akan Dikoreksi</label>
            <select id="id_mutasi_asal" name="id_mutasi_asal" required>
                <option value="">-- Pilih Kode Referensi Transaksi --</option>
                <?php if (!empty($daftarMutasi) && is_array($daftarMutasi)) : ?>
                    <?php foreach ($daftarMutasi as $barisMutasi) : ?>
                        <?php if (($barisMutasi['jenis_transaksi'] ?? '') !== 'KOREKSI') : ?>
                            <option value="<?= amankanHtml($barisMutasi['id'] ?? '') ?>">
                                <?= amankanHtml(
                                    ($barisMutasi['kode_referensi'] ?? '') . ' | '
                                    . ($barisMutasi['nomor_rekening'] ?? '') . ' - '
                                    . ($barisMutasi['nama_nasabah'] ?? '') . ' | '
                                    . ($barisMutasi['jenis_transaksi'] ?? '') . ' ('
                                    . formatRupiah((string) ($barisMutasi['nominal'] ?? '0')) . ')'
                                ) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="baris-form">
            <label for="alasan_koreksi">Alasan Koreksi (Wajib Diisi untuk Audit)</label>
            <input type="text" id="alasan_koreksi" name="alasan_koreksi" required placeholder="Contoh: Kesalahan input nominal oleh teller">
        </div>
        <button type="submit" class="tombol-utama">Proses Jurnal Koreksi</button>
    </form>
</div>

