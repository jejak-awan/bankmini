// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Helper untuk masuk ke aplikasi sebagai petugas Bank Mini.
 * @param {import('@playwright/test').Page} halaman
 * @param {string} namaPengguna
 * @param {string} kataSandi
 */
async function masukSebagai(halaman, namaPengguna, kataSandi) {
  await halaman.goto('/masuk');
  await halaman.fill('#nama_pengguna', namaPengguna);
  await halaman.fill('#kata_sandi', kataSandi);
  await Promise.all([
    halaman.waitForURL(/\/dasbor$/),
    halaman.click('button[type="submit"]'),
  ]);
}

test.describe('Smoke Test UI & Kesiapan Aset Offline Bank Mini', () => {
  test('Halaman login memuat CSS/JS lokal tanpa error dan lolos cek layout', async ({ page }) => {
    const daftarErrorKonsol = [];
    const daftarErrorHalaman = [];
    const daftarAsetGagal = [];

    page.on('console', (pesan) => {
      if (pesan.type() === 'error') {
        daftarErrorKonsol.push(pesan.text());
      }
    });
    page.on('pageerror', (galat) => daftarErrorHalaman.push(galat.message));
    page.on('response', (respons) => {
      const url = respons.url();
      if ((url.includes('.css') || url.includes('.js') || url.includes('.woff')) && respons.status() >= 400) {
        daftarAsetGagal.push(`${respons.status()} ${url}`);
      }
    });

    await page.goto('/masuk');
    await expect(page.locator('h1')).toContainText('Bank Mini Sekolah');

    const inputSandi = page.locator('#kata_sandi');
    await expect(inputSandi).toHaveAttribute('type', 'password');
    await page.click('button[data-toggle-sandi="kata_sandi"]');
    await expect(inputSandi).toHaveAttribute('type', 'text');

    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(overflow).toBeFalsy();

    await page.screenshot({ path: 'test-results/smoke-masuk.png', fullPage: true });

    expect(daftarAsetGagal).toEqual([]);
    expect(daftarErrorKonsol).toEqual([]);
    expect(daftarErrorHalaman).toEqual([]);
  });

  test('Seluruh rute operasional Teller & Admin merender sidebar gelap kontras dan bebas error', async ({ page }) => {
    const daftarErrorKonsol = [];
    const daftarErrorHalaman = [];
    const daftarAsetGagal = [];

    page.on('console', (pesan) => {
      if (pesan.type() === 'error') {
        daftarErrorKonsol.push(pesan.text());
      }
    });
    page.on('pageerror', (galat) => daftarErrorHalaman.push(galat.message));
    page.on('response', (respons) => {
      if (respons.status() >= 400 && !respons.url().includes('favicon.ico')) {
        daftarAsetGagal.push(`${respons.status()} ${respons.url()}`);
      }
    });

    await masukSebagai(page, 'teller1', 'password');

    const ruteTeller = [
      { url: '/dasbor', tangkap: 'test-results/smoke-dasbor.png' },
      { url: '/nasabah', tangkap: null },
      { url: '/produk', tangkap: null },
      { url: '/rekening', tangkap: null },
      { url: '/transaksi/setor', tangkap: 'test-results/smoke-setor.png' },
      { url: '/transaksi/tarik', tangkap: null },
      { url: '/transaksi/transfer', tangkap: null },
      { url: '/laporan/mutasi', tangkap: 'test-results/smoke-laporan.png' },
    ];

    for (const itemRute of ruteTeller) {
      await page.goto(itemRute.url);
      await expect(page.locator('#menuBilahSisi')).toBeVisible();

      const cekVisual = await page.evaluate(() => {
        const bilahSisi = document.getElementById('menuBilahSisi');
        const gaya = bilahSisi ? getComputedStyle(bilahSisi) : null;
        return {
          latarBelakang: gaya ? gaya.backgroundColor : '',
          latarGambar: gaya ? gaya.backgroundImage : '',
          overflowHorizontal: document.documentElement.scrollWidth > window.innerWidth,
        };
      });

      expect(cekVisual.overflowHorizontal).toBeFalsy();
      expect(cekVisual.latarBelakang !== 'rgba(0, 0, 0, 0)' || cekVisual.latarGambar.includes('gradient')).toBeTruthy();

      if (itemRute.tangkap) {
        await page.screenshot({ path: itemRute.tangkap, fullPage: true });
      }
    }

    await expect(page.locator('#menuBilahSisi a[href$="/transaksi/koreksi"]')).toHaveCount(0);

    await page.click('#menuBilahSisi form[action$="/keluar"] button[type="submit"]');
    await expect(page).toHaveURL(/\/masuk$/);

    await masukSebagai(page, 'admin', 'password');
    await page.goto('/transaksi/koreksi');
    await expect(page.locator('h1')).toContainText('Jurnal Koreksi');
    await expect(page.locator('#menuBilahSisi a[href$="/transaksi/koreksi"]')).toHaveCount(1);

    expect(daftarAsetGagal).toEqual([]);
    expect(daftarErrorKonsol).toEqual([]);
    expect(daftarErrorHalaman).toEqual([]);
  });
});
