<aside class="bilah-sisi">
    <h2>Bank Mini (IDR)</h2>
    <ul>
        <li><a href="<?= amankanHtml(urlDasar('dasbor')) ?>">Dasbor</a></li>
        <li><a href="<?= amankanHtml(urlDasar('nasabah')) ?>">Data Nasabah</a></li>
        <li><a href="<?= amankanHtml(urlDasar('produk')) ?>">Produk Tabungan</a></li>
        <li><a href="<?= amankanHtml(urlDasar('rekening')) ?>">Data Rekening</a></li>
        <li><a href="<?= amankanHtml(urlDasar('transaksi/setor')) ?>">Setor Tunai</a></li>
        <li><a href="<?= amankanHtml(urlDasar('transaksi/tarik')) ?>">Tarik Tunai</a></li>
        <li><a href="<?= amankanHtml(urlDasar('transaksi/transfer')) ?>">Transfer Rekening</a></li>
        <li><a href="<?= amankanHtml(urlDasar('transaksi/koreksi')) ?>">Jurnal Koreksi</a></li>
        <li><a href="<?= amankanHtml(urlDasar('laporan/mutasi')) ?>">Laporan Mutasi</a></li>
    </ul>
    <form method="POST" action="<?= amankanHtml(urlDasar('keluar')) ?>" style="margin-top: 2rem;">
        <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">
        <button type="submit" class="tombol-utama" style="width: 100%; background-color: #b91c1c;">Keluar Sistem</button>
    </form>
</aside>
<main class="konten-utama">
    <h1 style="margin-bottom: 1.25rem;"><?= amankanHtml($judulHalaman ?? '') ?></h1>
    <?php if (!empty($pesanKilat) && is_array($pesanKilat)) : ?>
        <div class="pesan-kilat <?= ($pesanKilat['jenis'] ?? '') === 'sukses' ? 'pesan-sukses' : 'pesan-kesalahan' ?>">
            <?= amankanHtml($pesanKilat['pesan'] ?? '') ?>
        </div>
    <?php endif; ?>

