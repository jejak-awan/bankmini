<?php
/**
 * Tampilan Formulir Setor Tunai (KREDIT) Bank Mini.
 * Dilengkapi pencarian rekening cepat, panel informasi saldo real-time,
 * pratinjau nominal Rupiah langsung, dan modal konfirmasi transaksi.
 */
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card kartu-bankmini">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h2 class="h6 mb-0 fw-bold">
                    <i class="bi bi-arrow-down-circle-fill text-success me-1"></i>
                    Formulir Setor Tunai (Rupiah IDR)
                </h2>
                <span class="badge text-bg-success">KREDIT (+)</span>
            </div>
            <div class="card-body p-4">
                <form
                    method="POST"
                    action="<?= amankanHtml(urlDasar('transaksi/setor')) ?>"
                    data-cegah-ganda="ya"
                    data-konfirmasi-transaksi="ya"
                    data-judul-konfirmasi="Konfirmasi Transaksi Setor Tunai"
                >
                    <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">

                    <div class="mb-3">
                        <label for="id_rekening" class="form-label fw-semibold">
                            Pilih Rekening Tujuan
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
                            data-target-info-saldo="panelInfoSaldoSetor"
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

                    <!-- Panel Informasi Saldo Real-Time -->
                    <div id="panelInfoSaldoSetor" class="alert alert-light border d-none mb-3">
                        <div class="row g-2 text-center text-md-start">
                            <div class="col-12 col-md-4">
                                <div class="small text-muted">Saldo Saat Ini</div>
                                <div class="fw-bold text-dark angka-keuangan" data-nilai-saldo>Rp 0</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="small text-muted">Saldo Mengendap Min.</div>
                                <div class="fw-semibold text-secondary angka-keuangan" data-nilai-mengendap>Rp 0</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="small text-muted">Saldo Efektif</div>
                                <div class="fw-bold text-success angka-keuangan" data-nilai-efektif>Rp 0</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nominal_setoran" class="form-label fw-semibold">
                            Nominal Setoran (Rupiah IDR)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold">Rp</span>
                            <input
                                type="number"
                                class="form-control angka-keuangan"
                                id="nominal_setoran"
                                name="nominal_setoran"
                                min="1000"
                                step="500"
                                placeholder="Minimal Rp 1.000"
                                data-pratinjau-rupiah="pratinjauSetor"
                                required
                            >
                        </div>
                        <div id="pratinjauSetor" class="form-text fw-semibold text-primary mt-1">
                            Terbaca: Rp 0
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="keterangan" class="form-label fw-semibold">Keterangan Setoran</label>
                        <input
                            type="text"
                            class="form-control"
                            id="keterangan"
                            name="keterangan"
                            placeholder="Contoh: Setoran tabungan rutin"
                        >
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i>Proses Setor Tunai
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
