<?php
/**
 * Tampilan Formulir Jurnal Koreksi / Reversal Bank Mini (Khusus Supervisor & Admin).
 * Menerapkan prinsip Immutable Ledger (Append-Only): transaksi lama tidak pernah
 * dihapus, melainkan dibalikkan melalui entri transaksi baru bertipe KOREKSI.
 */
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="alert alert-warning border-warning-subtle d-flex align-items-start gap-2 mb-4">
            <i class="bi bi-shield-exclamation fs-5 mt-1"></i>
            <div>
                <div class="fw-bold">Prinsip Audit Append-Only / Immutable Ledger</div>
                <div class="small">
                    Sesuai standar akuntansi perbankan, transaksi yang salah input <strong>tidak boleh dihapus</strong>,
                    melainkan dibalikkan nilainya menggunakan transaksi baru bertipe <strong>KOREKSI</strong>.
                </div>
            </div>
        </div>

        <div class="card kartu-bankmini">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h2 class="h6 mb-0 fw-bold">
                    <i class="bi bi-arrow-counterclockwise text-warning me-1"></i>
                    Formulir Jurnal Koreksi / Reversal
                </h2>
                <span class="badge text-bg-warning">KOREKSI</span>
            </div>
            <div class="card-body p-4">
                <form
                    method="POST"
                    action="<?= amankanHtml(urlDasar('transaksi/koreksi')) ?>"
                    data-cegah-ganda="ya"
                    data-konfirmasi-transaksi="ya"
                    data-judul-konfirmasi="Konfirmasi Pembuatan Jurnal Koreksi"
                >
                    <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">

                    <div class="mb-3">
                        <label for="id_mutasi_asal" class="form-label fw-semibold">
                            Pilih Transaksi yang Akan Dikoreksi
                        </label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Cari kode referensi / nomor rekening / nama..."
                                data-cari-select="id_mutasi_asal"
                            >
                        </div>
                        <select class="form-select" id="id_mutasi_asal" name="id_mutasi_asal" required>
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

                    <div class="mb-4">
                        <label for="alasan_koreksi" class="form-label fw-semibold">
                            Alasan Koreksi (Wajib Diisi untuk Audit)
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="alasan_koreksi"
                            name="alasan_koreksi"
                            required
                            placeholder="Contoh: Kesalahan input nominal oleh teller"
                        >
                    </div>

                    <button type="submit" class="btn btn-warning w-100 py-2 fw-semibold">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Proses Jurnal Koreksi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
