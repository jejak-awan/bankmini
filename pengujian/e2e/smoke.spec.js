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
  test('Portal publik & halaman login hanya memuat aset publik tanpa beban CSS/JS dasbor admin', async ({ page }) => {
    const daftarErrorKonsol = [];
    const daftarErrorHalaman = [];
    const daftarAsetGagal = [];
    const daftarAsetDimuat = [];

    page.on('console', (pesan) => {
      if (pesan.type() === 'error' || pesan.type() === 'warning') {
        daftarErrorKonsol.push(`[${pesan.type()}] ${pesan.text()}`);
      }
    });
    page.on('pageerror', (galat) => daftarErrorHalaman.push(galat.message));
    page.on('response', (respons) => {
      const url = respons.url();
      daftarAsetDimuat.push(url);
      if ((url.includes('.css') || url.includes('.js') || url.includes('.woff')) && respons.status() >= 400) {
        daftarAsetGagal.push(`${respons.status()} ${url}`);
      }
    });

    // 1. Uji Portal Publik (/portal)
    await page.goto('/portal');
    await expect(page.locator('h1')).toContainText('Layanan Tabungan Sekolah');
    await expect(page.locator('#menuBilahSisi')).toHaveCount(0);
    await expect(page.locator('#modalKonfirmasiTransaksi')).toHaveCount(0);
    await page.screenshot({ path: 'test-results/smoke-portal.png', fullPage: true });

    // 2. Uji Halaman Masuk (/masuk) dengan tata letak 'publik'
    await page.goto('/masuk');
    await expect(page.locator('h1')).toContainText('Bank Mini Sekolah');
    await expect(page.locator('#menuBilahSisi')).toHaveCount(0);
    await expect(page.locator('#modalKonfirmasiTransaksi')).toHaveCount(0);

    const inputSandi = page.locator('#kata_sandi');
    await expect(inputSandi).toHaveAttribute('type', 'password');
    await page.click('button[data-toggle-sandi="kata_sandi"]');
    await expect(inputSandi).toHaveAttribute('type', 'text');

    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(overflow).toBeFalsy();

    // Pastikan aset khusus dasbor admin tidak pernah ikut dimuat di rute publik
    expect(daftarAsetDimuat.some((url) => url.includes('dasbor.css'))).toBeFalsy();
    expect(daftarAsetDimuat.some((url) => url.includes('dasbor-interaktif.js'))).toBeFalsy();
    expect(daftarAsetDimuat.some((url) => url.includes('token-desain.css'))).toBeTruthy();
    expect(daftarAsetDimuat.some((url) => url.includes('komponen-ui.css'))).toBeTruthy();
    expect(daftarAsetDimuat.some((url) => url.includes('publik.css'))).toBeTruthy();
    expect(daftarAsetDimuat.some((url) => url.includes('inti-ui.js'))).toBeTruthy();

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
      if (pesan.type() === 'error' || pesan.type() === 'warning') {
        daftarErrorKonsol.push(`[${pesan.type()}] ${pesan.text()}`);
      }
    });
    page.on('pageerror', (galat) => daftarErrorHalaman.push(galat.message));
    page.on('response', (respons) => {
      if (respons.status() >= 400) {
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
      { url: '/dokumentasi', tangkap: 'test-results/smoke-dokumentasi.png' },
      { url: '/dokumentasi?dok=02-panduan-dev/alur-belajar-dan-modul-praktikum-rpl.md&cari=ACID', tangkap: null },
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

  test('Endpoint /kesehatan, halaman galat 404 kustom, & header keamanan CSP aktif tanpa error konsol', async ({ page }) => {
    const daftarErrorKonsol = [];
    const daftarErrorHalaman = [];

    page.on('console', (pesan) => {
      if (pesan.type() === 'error' || pesan.type() === 'warning') {
        daftarErrorKonsol.push(`[${pesan.type()}] ${pesan.text()}`);
      }
    });
    page.on('pageerror', (galat) => daftarErrorHalaman.push(galat.message));

    // 1. Verifikasi Health Check Endpoint GET /kesehatan
    const responsKesehatan = await page.request.get('/kesehatan');
    expect(responsKesehatan.status()).toBe(200);
    expect(responsKesehatan.headers()['x-content-type-options']).toBe('nosniff');
    const jsonKesehatan = await responsKesehatan.json();
    expect(jsonKesehatan.status).toBe('sehat');
    expect(jsonKesehatan.pemeriksaan.basis_data).toBe('siap');

    // 2. Verifikasi Halaman 404 Kustom memakai Design System & Header Keamanan CSP
    const respons404 = await page.goto('/rute-tidak-tersedia-uji-404');
    expect(respons404).not.toBeNull();
    expect(respons404?.status()).toBe(404);
    expect(respons404?.headers()['x-frame-options']).toBe('SAMEORIGIN');
    expect(respons404?.headers()['content-security-policy']).toContain("script-src 'self'");
    await expect(page.locator('h1')).toContainText('Halaman Tidak Ditemukan');
    await expect(page.locator('body')).toContainText('ID Korelasi Log:');

    expect(daftarErrorKonsol).toEqual([]);
    expect(daftarErrorHalaman).toEqual([]);
  });
});
