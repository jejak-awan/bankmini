<div class="kartu">
    <h3>Filter Rentang Tanggal &amp; Cetak Laporan Mutasi</h3>
    <form method="GET" action="<?= amankanHtml(urlDasar('laporan/mutasi')) ?>" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div class="baris-form" style="margin-bottom: 0;">
            <label for="tanggal_mulai">Tanggal Mulai</label>
            <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= amankanHtml($tanggalMulai ?? '') ?>">
        </div>
        <div class="baris-form" style="margin-bottom: 0;">
            <label for="tanggal_akhir">Tanggal Akhir</label>
            <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="<?= amankanHtml($tanggalAkhir ?? '') ?>">
        </div>
        <button type="submit" class="tombol-utama">Tampilkan Mutasi</button>
        <button type="button" class="tombol-utama" style="background-color: #15803d;" onclick="window.print();">Cetak Laporan</button>
    </form>
</div>

<div class="kartu">
    <h3>Buku Besar Mutasi Transaksi (Immutable Ledger - Rupiah IDR)</h3>
    <table class="tabel-data">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Kode Referensi</th>
                <th>No. Rekening</th>
                <th>Nasabah</th>
                <th>Jenis</th>
                <th>Tipe</th>
                <th>Saldo Sebelum</th>
                <th>Nominal (IDR)</th>
                <th>Saldo Sesudah</th>
                <th>Petugas</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($daftarMutasi) && is_array($daftarMutasi)) : ?>
                <?php foreach ($daftarMutasi as $barisMutasi) : ?>
                    <tr>
                        <td><?= amankanHtml(formatTanggalIndonesia((string) ($barisMutasi['waktu_transaksi'] ?? ''))) ?></td>
                        <td><?= amankanHtml($barisMutasi['kode_referensi'] ?? '') ?></td>
                        <td><?= amankanHtml($barisMutasi['nomor_rekening'] ?? '') ?></td>
                        <td><?= amankanHtml($barisMutasi['nama_nasabah'] ?? '') ?></td>
                        <td><?= amankanHtml($barisMutasi['jenis_transaksi'] ?? '') ?></td>
                        <td><?= amankanHtml($barisMutasi['tipe_mutasi'] ?? '') ?></td>
                        <td><?= amankanHtml(formatRupiah((string) ($barisMutasi['saldo_sebelum'] ?? '0'))) ?></td>
                        <td><?= amankanHtml(formatRupiah((string) ($barisMutasi['nominal'] ?? '0'))) ?></td>
                        <td><?= amankanHtml(formatRupiah((string) ($barisMutasi['saldo_sesudah'] ?? '0'))) ?></td>
                        <td><?= amankanHtml($barisMutasi['nama_petugas'] ?? '') ?></td>
                        <td><?= amankanHtml($barisMutasi['keterangan'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="11">Belum ada data mutasi transaksi pada rentang tanggal ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
