<?php
/**
 * Tampilan Manajemen Data Nasabah Bank Mini.
 * Menyediakan tata letak 2 kolom (Formulir Pendaftaran Nasabah + Tabel Pencarian Cepat)
 * berbasis komponen UI bersama dengan proteksi CSRF dan anti double-submit.
 */
?>
<div class="row g-4">
    <!-- Kolom Formulir Pendaftaran Nasabah -->
    <div class="col-12 col-lg-4">
        <div class="card kartu-bankmini">
            <?= komponenUi('kepala_kartu', [
                'judul' => 'Tambah Nasabah Baru',
                'ikon' => 'bi-person-plus-fill',
            ]) ?>
            <div class="card-body">
                <form method="POST" action="<?= amankanHtml(urlDasar('nasabah/tambah')) ?>" data-cegah-ganda="ya">
                    <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">

                    <div class="mb-3">
                        <label for="nomor_identitas" class="form-label fw-semibold">
                            Nomor Identitas (NIS / NIP / NIK)
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="nomor_identitas"
                            name="nomor_identitas"
                            placeholder="Contoh: 20261001"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label fw-semibold">Nama Lengkap</label>
                        <input
                            type="text"
                            class="form-control"
                            id="nama_lengkap"
                            name="nama_lengkap"
                            placeholder="Nama sesuai kartu pelajar/KTP"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="jenis_nasabah" class="form-label fw-semibold">Jenis Nasabah</label>
                        <select class="form-select" id="jenis_nasabah" name="jenis_nasabah" required>
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru</option>
                            <option value="umum">Umum</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="kelas_atau_unit" class="form-label fw-semibold">Kelas / Unit Kerja</label>
                        <input
                            type="text"
                            class="form-control"
                            id="kelas_atau_unit"
                            name="kelas_atau_unit"
                            placeholder="Contoh: XI RPL 1 / Tata Usaha"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="nomor_telepon" class="form-label fw-semibold">Nomor Telepon</label>
                        <input
                            type="text"
                            class="form-control"
                            id="nomor_telepon"
                            name="nomor_telepon"
                            placeholder="08xxxxxxxxxx (opsional)"
                        >
                    </div>

                    <div class="mb-3">
                        <label for="alamat" class="form-label fw-semibold">Alamat</label>
                        <textarea
                            class="form-control"
                            id="alamat"
                            name="alamat"
                            rows="2"
                            placeholder="Alamat tempat tinggal (opsional)"
                        ></textarea>
                    </div>

                    <button type="submit" class="btn tombol-bankmini w-100 fw-semibold">
                        <i class="bi bi-save me-1"></i>Simpan Nasabah
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Daftar Nasabah + Filter Cepat -->
    <div class="col-12 col-lg-8">
        <div class="card kartu-bankmini">
            <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="h6 mb-0 fw-bold">
                    <i class="bi bi-people-fill me-1 text-primary"></i>
                    Daftar Nasabah Terdaftar
                </h2>
                <?= komponenUi('pencarian_tabel', [
                    'idTabel' => 'tabelNasabah',
                    'placeholder' => 'Cari NIS / nama / kelas...',
                ]) ?>
            </div>
            <div class="table-responsive">
                <table id="tabelNasabah" class="table tabel-bankmini table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Identitas</th>
                            <th>Nama Lengkap</th>
                            <th>Jenis</th>
                            <th>Kelas / Unit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftarNasabah) && is_array($daftarNasabah)) : ?>
                            <?php foreach ($daftarNasabah as $barisNasabah) : ?>
                                <tr>
                                    <td class="angka-keuangan"><?= amankanHtml($barisNasabah['nomor_identitas'] ?? '') ?></td>
                                    <td class="fw-semibold"><?= amankanHtml($barisNasabah['nama_lengkap'] ?? '') ?></td>
                                    <td>
                                        <span class="badge text-bg-light border text-dark text-capitalize">
                                            <?= amankanHtml($barisNasabah['jenis_nasabah'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td><?= amankanHtml($barisNasabah['kelas_atau_unit'] ?? '') ?></td>
                                    <td>
                                        <?= komponenUi('lencana_status', [
                                            'status' => (string) ($barisNasabah['status'] ?? 'AKTIF'),
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Belum ada data nasabah.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
