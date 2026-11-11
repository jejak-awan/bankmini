<div class="grid-statistik">
    <div class="kartu">
        <h3>Total Nasabah</h3>
        <p style="font-size: 1.75rem; font-weight: 700;"><?= amankanHtml($jumlahNasabah ?? 0) ?></p>
    </div>
    <div class="kartu">
        <h3>Total Rekening</h3>
        <p style="font-size: 1.75rem; font-weight: 700;"><?= amankanHtml($jumlahRekening ?? 0) ?></p>
    </div>
    <div class="kartu">
        <h3>Total Dana Tabungan (IDR)</h3>
        <p style="font-size: 1.75rem; font-weight: 700;"><?= amankanHtml(formatRupiah((string) ($totalSaldoRupiah ?? '0.00'))) ?></p>
    </div>
</div>

<div class="kartu">
    <h3>10 Mutasi Transaksi Terbaru (Rupiah IDR)</h3>
    <table class="tabel-data">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Referensi</th>
                <th>No. Rekening</th>
                <th>Nasabah</th>
                <th>Jenis</th>
                <th>Tipe</th>
                <th>Nominal (IDR)</th>
                <th>Saldo Sesudah (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($daftarMutasiTerbaru) && is_array($daftarMutasiTerbaru)) : ?>
                <?php foreach ($daftarMutasiTerbaru as $barisMutasi) : ?>
                    <tr>
                        <td><?= amankanHtml(formatTanggalIndonesia((string) ($barisMutasi['waktu_transaksi'] ?? ''))) ?></td>
                        <td><?= amankanHtml($barisMutasi['kode_referensi'] ?? '') ?></td>
                        <td><?= amankanHtml($barisMutasi['nomor_rekening'] ?? '') ?></td>
                        <td><?= amankanHtml($barisMutasi['nama_nasabah'] ?? '') ?></td>
                        <td><?= amankanHtml($barisMutasi['jenis_transaksi'] ?? '') ?></td>
                        <td><?= amankanHtml($barisMutasi['tipe_mutasi'] ?? '') ?></td>
                        <td><?= amankanHtml(formatRupiah((string) ($barisMutasi['nominal'] ?? '0'))) ?></td>
                        <td><?= amankanHtml(formatRupiah((string) ($barisMutasi['saldo_sesudah'] ?? '0'))) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8">Belum ada riwayat mutasi transaksi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

