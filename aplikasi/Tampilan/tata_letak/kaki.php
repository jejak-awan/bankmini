    </div>

    <footer class="mt-auto px-3 px-md-4 py-3 bg-white border-top text-muted small d-flex justify-content-between flex-wrap gap-2 tidak-cetak">
        <span><?= amankanHtml($namaAplikasi ?? 'Sistem Informasi Bank Mini') ?> &mdash; Bahan Pembelajaran SMK RPL</span>
        <span>Versi <?= amankanHtml($versiAplikasi ?? '1.0.0') ?> &bull; Bootstrap 5 Offline</span>
    </footer>
</main>
</div>

<!-- Modal Konfirmasi Transaksi Keuangan (Pencegahan Salah Input / Error Prevention) -->
<div class="modal fade tidak-cetak" id="modalKonfirmasiTransaksi" tabindex="-1" aria-labelledby="judulModalKonfirmasi" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg, #0b2545 0%, #1d4ed8 100%);">
                <h5 class="modal-title fw-bold" id="judulModalKonfirmasi">
                    <i class="bi bi-shield-check me-2"></i>Konfirmasi Transaksi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    Periksa kembali rincian transaksi Rupiah (IDR) di bawah ini sebelum dicatat permanen ke buku besar:
                </p>
                <div id="isiRingkasanKonfirmasi" class="p-3 bg-light rounded-3 border"></div>
            </div>
            <div class="modal-footer bg-light border-0 px-4 py-3">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Periksa Ulang</button>
                <button type="button" class="btn tombol-bankmini px-4 fw-semibold" id="tombolKonfirmasiEksekusi">
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
