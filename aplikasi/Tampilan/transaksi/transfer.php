<?php
/**
 * Tampilan Formulir Pemindahbukuan / Transfer Antar Rekening Bank Mini.
 * Menyediakan pencarian cepat untuk rekening pengirim maupun penerima,
 * informasi saldo efektif pengirim, pratinjau nominal Rupiah, dan modal konfirmasi.
 */
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card kartu-bankmini">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h2 class="h6 mb-0 fw-bold">
                    <i class="bi bi-arrow-left-right text-primary me-1"></i>
                    Formulir Pemindahbukuan / Transfer Antar Rekening
                </h2>
                <span class="badge text-bg-primary">DEBIT &rarr; KREDIT</span>
            </div>
            <div class="card-body p-4">
                <form
                    method="POST"
                    action="<?= amankanHtml(urlDasar('transaksi/transfer')) ?>"
                    data-cegah-ganda="ya"
                    data-konfirmasi-transaksi="ya"
                    data-judul-konfirmasi="Konfirmasi Pemindahbukuan / Transfer"
                >
                    <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">

                    <!-- Rekening Pengirim -->
                    <div class="mb-3">
                        <label for="id_rekening_asal" class="form-label fw-semibold">
                            Rekening Pengirim <span class="badge text-bg-danger ms-1">DEBIT</span>
                        </label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Cari rekening pengirim..."
                                data-cari-select="id_rekening_asal"
                            >
                        </div>
                        <select
                            class="form-select"
                            id="id_rekening_asal"
                            name="id_rekening_asal"
                            data-target-info-saldo="panelInfoSaldoAsal"
                            required
                        >
                            <option value="">-- Pilih Rekening Pengirim --</option>
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

                    <!-- Panel Informasi Saldo Pengirim -->
                    <div id="panelInfoSaldoAsal" class="alert alert-light border d-none mb-3">
                        <div class="row g-2 text-center text-md-start">
                            <div class="col-12 col-md-4">
                                <div class="small text-muted">Saldo Pengirim</div>
                                <div class="fw-bold text-dark angka-keuangan" data-nilai-saldo>Rp 0</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="small text-muted">Mengendap Min.</div>
                                <div class="fw-semibold text-secondary angka-keuangan" data-nilai-mengendap>Rp 0</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="small text-muted">Maksimal Transfer</div>
                                <div class="fw-bold text-primary angka-keuangan" data-nilai-efektif>Rp 0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Rekening Penerima -->
                    <div class="mb-3">
                        <label for="id_rekening_tujuan" class="form-label fw-semibold">
                            Rekening Penerima <span class="badge text-bg-success ms-1">KREDIT</span>
                        </label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Cari rekening penerima..."
                                data-cari-select="id_rekening_tujuan"
                            >
                        </div>
                        <select class="form-select" id="id_rekening_tujuan" name="id_rekening_tujuan" required>
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

                    <div class="mb-3">
                        <label for="nominal_transfer" class="form-label fw-semibold">
                            Nominal Transfer (Rupiah IDR)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold">Rp</span>
                            <input
                                type="number"
                                class="form-control angka-keuangan"
                                id="nominal_transfer"
                                name="nominal_transfer"
                                min="1000"
                                step="500"
                                placeholder="Minimal Rp 1.000"
                                data-pratinjau-rupiah="pratinjauTransfer"
                                required
                            >
                        </div>
                        <div id="pratinjauTransfer" class="form-text fw-semibold text-primary mt-1">
                            Terbaca: Rp 0
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="keterangan" class="form-label fw-semibold">Keterangan Transfer</label>
                        <input
                            type="text"
                            class="form-control"
                            id="keterangan"
                            name="keterangan"
                            placeholder="Contoh: Pemindahbukuan tabungan"
                        >
                    </div>

                    <button type="submit" class="btn tombol-bankmini w-100 py-2 fw-semibold">
                        <i class="bi bi-send-check me-1"></i>Proses Transfer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
