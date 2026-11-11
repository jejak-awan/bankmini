<?php
/**
 * Tampilan Formulir Tarik Tunai (DEBIT) Bank Mini.
 * Menampilkan Saldo Efektif secara real-time agar teller dapat langsung mengetahui
 * batas maksimal penarikan sebelum dikurangi saldo mengendap minimum.
 */
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card kartu-bankmini">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h2 class="h6 mb-0 fw-bold">
                    <i class="bi bi-arrow-up-circle-fill text-danger me-1"></i>
                    Formulir Tarik Tunai (Rupiah IDR)
                </h2>
                <span class="badge text-bg-danger">DEBIT (-)</span>
            </div>
            <div class="card-body p-4">
                <form
                    method="POST"
                    action="<?= amankanHtml(urlDasar('transaksi/tarik')) ?>"
                    data-cegah-ganda="ya"
                    data-konfirmasi-transaksi="ya"
                    data-judul-konfirmasi="Konfirmasi Transaksi Tarik Tunai"
                >
                    <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">

                    <div class="mb-3">
                        <label for="id_rekening" class="form-label fw-semibold">
                            Pilih Rekening Penarikan
                        </label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Ketik nomor rekening atau nama nasabah..."
                                data-cari-select="id_rekening"
                            >
                        </div>
                        <select
                            class="form-select"
                            id="id_rekening"
                            name="id_rekening"
                            data-target-info-saldo="panelInfoSaldoTarik"
                            required
                        >
                            <option value="">-- Pilih Rekening --</option>
                            <?php if (!empty($daftarRekening) && is_array($daftarRekening)) : ?>
                                <?php foreach ($daftarRekening as $barisRekening) : ?>
                                    <option
                                        value="<?= amankanHtml($barisRekening['id'] ?? '') ?>"
                                        data-saldo="<?= amankanHtml($barisRekening['saldo'] ?? '0') ?>"
                                        data-saldo-mengendap="<?= amankanHtml($barisRekening['saldo_mengendap_minimum'] ?? '0') ?>"
                                        data-nama-nasabah="<?= amankanHtml($barisRekening['nama_nasabah'] ?? '') ?>"
                                        data-nomor-rekening="<?= amankanHtml($barisRekening['nomor_rekening'] ?? '') ?>"
                                    >
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

                    <!-- Panel Informasi Saldo Efektif (Batas Tarik Maksimal) -->
                    <div id="panelInfoSaldoTarik" class="alert alert-light border d-none mb-3">
                        <div class="row g-2 text-center text-md-start">
                            <div class="col-12 col-md-4">
                                <div class="small text-muted">Saldo Total</div>
                                <div class="fw-bold text-dark angka-keuangan" data-nilai-saldo>Rp 0</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="small text-muted">Mengendap Wajib</div>
                                <div class="fw-semibold text-secondary angka-keuangan" data-nilai-mengendap>Rp 0</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="small text-muted">Maksimal Dapat Ditarik</div>
                                <div class="fw-bold text-primary angka-keuangan" data-nilai-efektif>Rp 0</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nominal_penarikan" class="form-label fw-semibold">
                            Nominal Penarikan (Rupiah IDR)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold">Rp</span>
                            <input
                                type="number"
                                class="form-control angka-keuangan"
                                id="nominal_penarikan"
                                name="nominal_penarikan"
                                min="1000"
                                step="500"
                                placeholder="Minimal Rp 1.000"
                                data-pratinjau-rupiah="pratinjauTarik"
                                required
                            >
                        </div>
                        <div id="pratinjauTarik" class="form-text fw-semibold text-primary mt-1">
                            Terbaca: Rp 0
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="keterangan" class="form-label fw-semibold">Keterangan Penarikan</label>
                        <input
                            type="text"
                            class="form-control"
                            id="keterangan"
                            name="keterangan"
                            placeholder="Contoh: Penarikan tunai keperluan sekolah"
                        >
                    </div>

                    <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i>Proses Tarik Tunai
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
