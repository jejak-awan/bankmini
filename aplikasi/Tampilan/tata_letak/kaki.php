    <footer class="mt-5 pt-3 border-top text-muted small d-flex justify-content-between flex-wrap gap-2 tidak-cetak">
        <span><?= amankanHtml($namaAplikasi ?? 'Sistem Informasi Bank Mini') ?> &mdash; Bahan Pembelajaran RPL</span>
        <span>Versi <?= amankanHtml($versiAplikasi ?? '1.0.0') ?> (Offline Bootstrap 5)</span>
    </footer>
</main>
</div>

<!-- Modal Konfirmasi Transaksi Keuangan (Pencegahan Salah Input / Error Prevention) -->
<div class="modal fade tidak-cetak" id="modalKonfirmasiTransaksi" tabindex="-1" aria-labelledby="judulModalKonfirmasi" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="judulModalKonfirmasi">
                    <i class="bi bi-shield-check me-2"></i>Konfirmasi Transaksi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Periksa kembali rincian transaksi Rupiah (IDR) di bawah ini sebelum diproses ke buku besar:
                </p>
                <div id="isiRingkasanKonfirmasi" class="p-3 bg-light rounded border"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Periksa Ulang</button>
                <button type="button" class="btn tombol-bankmini" id="tombolKonfirmasiEksekusi">
                    <i class="bi bi-check2-circle me-1"></i>Ya, Proses Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= amankanHtml(urlDasar('aset/vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= amankanHtml(urlDasar('aset/js/aplikasi.js')) ?>"></script>
</body>
</html>
