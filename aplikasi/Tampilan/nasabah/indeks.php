<div class="kartu">
    <h3>Tambah Nasabah Baru</h3>
    <form method="POST" action="<?= amankanHtml(urlDasar('nasabah/tambah')) ?>" data-cegah-ganda="ya">
        <input type="hidden" name="token_csrf" value="<?= amankanHtml($tokenCsrfAktif ?? '') ?>">
        <div class="baris-form">
            <label for="nomor_identitas">Nomor Identitas (NIS / NIP / NIK)</label>
            <input type="text" id="nomor_identitas" name="nomor_identitas" required>
        </div>
        <div class="baris-form">
            <label for="nama_lengkap">Nama Lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" required>
        </div>
        <div class="baris-form">
            <label for="jenis_nasabah">Jenis Nasabah</label>
            <select id="jenis_nasabah" name="jenis_nasabah" required>
                <option value="siswa">Siswa</option>
                <option value="guru">Guru</option>
                <option value="umum">Umum</option>
            </select>
        </div>
        <div class="baris-form">
            <label for="kelas_atau_unit">Kelas / Unit Kerja</label>
            <input type="text" id="kelas_atau_unit" name="kelas_atau_unit" required>
        </div>
        <div class="baris-form">
            <label for="nomor_telepon">Nomor Telepon</label>
            <input type="text" id="nomor_telepon" name="nomor_telepon">
        </div>
        <div class="baris-form">
            <label for="alamat">Alamat</label>
            <textarea id="alamat" name="alamat" rows="2"></textarea>
        </div>
        <button type="submit" class="tombol-utama">Simpan Nasabah</button>
    </form>
</div>

<div class="kartu">
    <h3>Daftar Nasabah Terdaftar</h3>
    <table class="tabel-data">
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
                        <td><?= amankanHtml($barisNasabah['nomor_identitas'] ?? '') ?></td>
                        <td><?= amankanHtml($barisNasabah['nama_lengkap'] ?? '') ?></td>
                        <td><?= amankanHtml($barisNasabah['jenis_nasabah'] ?? '') ?></td>
                        <td><?= amankanHtml($barisNasabah['kelas_atau_unit'] ?? '') ?></td>
                        <td><?= amankanHtml($barisNasabah['status'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">Belum ada data nasabah.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

