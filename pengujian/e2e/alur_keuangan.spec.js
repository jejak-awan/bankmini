// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Pengujian E2E Alur Nasabah, Rekening, & Transaksi Rupiah', () => {
  test('Daftar nasabah, buka rekening, setor tunai, & tarik tunai via Modal Konfirmasi', async ({ page }) => {
    await page.goto('/masuk');
    await page.fill('#nama_pengguna', 'teller1');
    await page.fill('#kata_sandi', 'password');
    await Promise.all([
      page.waitForURL(/\/dasbor$/),
      page.click('button[type="submit"]'),
    ]);

    // 1. Jika belum ada rekening di database, buat nasabah & buka rekening terlebih dahulu
    await page.goto('/transaksi/setor');
    let jumlahOpsiRekening = await page.locator('#id_rekening option').count();

    if (jumlahOpsiRekening < 2) {
      const nisUnik = 'E2E' + Date.now().toString().slice(-6);
      await page.goto('/nasabah');
      await page.fill('#nomor_identitas', nisUnik);
      await page.fill('#nama_lengkap', 'Siswa Uji E2E ' + nisUnik);
      await page.selectOption('#jenis_nasabah', 'siswa');
      await page.fill('#kelas_atau_unit', 'XII RPL 1');
      await Promise.all([
        page.waitForURL(/\/nasabah$/),
        page.click('form[action$="/nasabah/tambah"] button[type="submit"]'),
      ]);
      await expect(page.locator('.alert-success')).toContainText('berhasil');

      await page.goto('/rekening');
      const opsiNasabah = await page.locator('#id_nasabah option').nth(1).getAttribute('value');
      const opsiProduk = await page.locator('#id_produk option').nth(1).getAttribute('value');
      await page.selectOption('#id_nasabah', opsiNasabah || '');
      await page.selectOption('#id_produk', opsiProduk || '');
      await page.fill('#nominal_setoran_awal', '50000');
      await expect(page.locator('#pratinjauSetoranAwal')).toContainText('Terbaca: Rp 50.000');

      await page.click('form[action$="/rekening/buka"] button[type="submit"]');
      await expect(page.locator('#modalKonfirmasiTransaksi')).toBeVisible();
      await Promise.all([
        page.waitForURL(/\/rekening$/),
        page.click('#tombolKonfirmasiEksekusi'),
      ]);
      await expect(page.locator('.alert-success')).toContainText('berhasil');

      await page.goto('/transaksi/setor');
      jumlahOpsiRekening = await page.locator('#id_rekening option').count();
    }

    expect(jumlahOpsiRekening).toBeGreaterThanOrEqual(2);

    // 2. Uji Setor Tunai Rp 10.000 + Pratinjau Rupiah + Panel Info Saldo + Modal Konfirmasi
    const nilaiRekening = await page.locator('#id_rekening option').nth(1).getAttribute('value');
    await page.selectOption('#id_rekening', nilaiRekening || '');
    await expect(page.locator('#panelInfoSaldoSetor')).toBeVisible();
    await expect(page.locator('#panelInfoSaldoSetor [data-nilai-efektif]')).toContainText('Rp ');

    await page.fill('#nominal_setoran', '10000');
    await expect(page.locator('#pratinjauSetor')).toContainText('Terbaca: Rp 10.000');
    await page.fill('#keterangan', 'Uji E2E Playwright Setor');

    await page.click('form[action$="/transaksi/setor"] button[type="submit"]');
    await expect(page.locator('#modalKonfirmasiTransaksi')).toBeVisible();
    await expect(page.locator('#isiRingkasanKonfirmasi')).toContainText('Rp 10.000');

    await Promise.all([
      page.waitForURL(/\/transaksi\/setor$/),
      page.click('#tombolKonfirmasiEksekusi'),
    ]);
    await expect(page.locator('.alert-success')).toContainText('berhasil');

    // 3. Uji Tarik Tunai Rp 10.000 untuk mengembalikan saldo
    await page.goto('/transaksi/tarik');
    await page.selectOption('#id_rekening', nilaiRekening || '');
    await expect(page.locator('#panelInfoSaldoTarik')).toBeVisible();

    await page.fill('#nominal_penarikan', '10000');
    await expect(page.locator('#pratinjauTarik')).toContainText('Terbaca: Rp 10.000');
    await page.fill('#keterangan', 'Uji E2E Playwright Tarik');

    await page.click('form[action$="/transaksi/tarik"] button[type="submit"]');
    await expect(page.locator('#modalKonfirmasiTransaksi')).toBeVisible();

    await Promise.all([
      page.waitForURL(/\/transaksi\/tarik$/),
      page.click('#tombolKonfirmasiEksekusi'),
    ]);
    await expect(page.locator('.alert-success')).toContainText('berhasil');
  });
});
