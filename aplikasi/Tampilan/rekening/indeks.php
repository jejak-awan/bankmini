<?php
/**
 * Tampilan Pembukaan & Daftar Rekening Tabungan Bank Mini.
 * Menggabungkan formulir pembukaan rekening (dengan pencarian cepat nasabah,
 * pratinjau Rupiah live, dan modal konfirmasi transaksi) beserta tabel daftar rekening.
 */
$petaStatusRekening = [
    'AKTIF' => 'text-bg-success',
    'TUTUP' => 'text-bg-danger',
    'BEKU' => 'text-bg-warning',
];
?>
<div class="row g-4">
    <!-- Kolom Formulir Pembukaan Rekening -->
    <div class="col-12 col-lg-5">
        <div class="card kartu-bankmini">
            <div class="card-header bg-white py-3">
                <h2 class="h6 mb-0 fw-bold">
                    <i class="bi bi-wallet2 me-1 text-primary"></i>
                    Pembukaan Rekening Baru &amp; Setoran Awal
                </h2>
            </div>
            <div class="card-body">
                <form
                    method="POST"
                    action="<?= amankanHtml(urlDasar('rekening/buka')) ?>"
                    data-cegah-ganda="ya"
                    data-konfirmasi-transaksi="ya"
                    data-judul-konfirmasi="Konfirmasi Pembukaan Rekening Baru"
                >
                    <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">

                    <div class="mb-3">
                        <label for="cari_nasabah" class="form-label fw-semibold">Pilih Nasabah</label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                id="cari_nasabah"
                                class="form-control"
                                placeholder="Ketik NIS / nama nasabah untuk memfilter..."
                                data-cari-select="id_nasabah"
                            >
                        </div>
                        <select class="form-select" id="id_nasabah" name="id_nasabah" required>
                            <option value="">-- Pilih Nasabah --</option>
                            <?php if (!empty($daftarNasabah) && is_array($daftarNasabah)) : ?>
                                <?php foreach ($daftarNasabah as $barisNasabah) : ?>
                                    <option value="<?= amankanHtml($barisNasabah['id'] ?? '') ?>">
                                        <?= amankanHtml(($barisNasabah['nomor_identitas'] ?? '') . ' - ' . ($barisNasabah['nama_lengkap'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="id_produk" class="form-label fw-semibold">Pilih Produk Tabungan</label>
                        <select class="form-select" id="id_produk" name="id_produk" required>
                            <option value="">-- Pilih Produk Tabungan --</option>
                            <?php if (!empty($daftarProduk) && is_array($daftarProduk)) : ?>
                                <?php foreach ($daftarProduk as $barisProduk) : ?>
                                    <option value="<?= amankanHtml($barisProduk['id'] ?? '') ?>">
                                        <?= amankanHtml(
                                            ($barisProduk['kode_produk'] ?? '') . ' - ' . ($barisProduk['nama_produk'] ?? '')
                                            . ' (Min. ' . formatRupiah((string) ($barisProduk['setoran_awal_minimum'] ?? '0')) . ')'
                                        ) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nominal_setoran_awal" class="form-label fw-semibold">
                            Nominal Setoran Awal (Rupiah IDR)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold">Rp</span>
                            <input
                                type="number"
                                class="form-control angka-keuangan"
                                id="nominal_setoran_awal"
                                name="nominal_setoran_awal"
                                min="1000"
                                step="500"
                                placeholder="Minimal sesuai ketentuan produk"
                                data-pratinjau-rupiah="pratinjauSetoranAwal"
                                required
                            >
                        </div>
                        <div id="pratinjauSetoranAwal" class="form-text fw-semibold text-primary mt-1">
                            Terbaca: Rp 0
                        </div>
                    </div>

                    <button type="submit" class="btn tombol-bankmini w-100 fw-semibold">
                        <i class="bi bi-check-circle me-1"></i>Buka Rekening &amp; Simpan Setoran Awal
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Tabel Rekening Tabungan -->
    <div class="col-12 col-lg-7">
        <div class="card kartu-bankmini">
            <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="h6 mb-0 fw-bold">
                    <i class="bi bi-credit-card-2-front-fill me-1 text-primary"></i>
                    Daftar Rekening Tabungan
                </h2>
                <div class="input-group input-group-sm" style="max-width: 240px;">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Cari no. rekening / nama..."
                        data-cari-tabel="tabelRekening"
                    >
                </div>
            </div>
            <div class="table-responsive">
                <table id="tabelRekening" class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Rekening</th>
                            <th>Nasabah</th>
                            <th>Produk</th>
                            <th class="text-end">Saldo (IDR)</th>
                            <th class="text-end">Mengendap Min.</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftarRekening) && is_array($daftarRekening)) : ?>
                            <?php foreach ($daftarRekening as $barisRekening) : ?>
                                <?php
                                $statusRekening = strtoupper((string) ($barisRekening['status'] ?? 'AKTIF'));
                                $kelasStatus = $petaStatusRekening[$statusRekening] ?? 'text-bg-secondary';
                                ?>
                                <tr>
                                    <td class="angka-keuangan fw-semibold">
                                        <?= amankanHtml($barisRekening['nomor_rekening'] ?? '') ?>
                                    </td>
                                    <td><?= amankanHtml($barisRekening['nama_nasabah'] ?? '') ?></td>
                                    <td class="small"><?= amankanHtml($barisRekening['nama_produk'] ?? '') ?></td>
                                    <td class="angka-keuangan text-end fw-semibold text-success">
                                        <?= amankanHtml(formatRupiah((string) ($barisRekening['saldo'] ?? '0'))) ?>
                                    </td>
                                    <td class="angka-keuangan text-end small text-muted">
                                        <?= amankanHtml(formatRupiah((string) ($barisRekening['saldo_mengendap_minimum'] ?? '0'))) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= amankanHtml($kelasStatus) ?>">
                                            <?= amankanHtml($statusRekening) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Belum ada data rekening tabungan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
