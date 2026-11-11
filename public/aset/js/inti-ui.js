/**
 * Skrip Interaksi Antarmuka Dasar (Shared Ringan) - Sistem Informasi Bank Mini
 * Dimuat oleh halaman Publik dan Dasbor untuk fitur umum:
 * 1. Toggle tampil/sembunyikan kata sandi (`data-toggle-sandi="idInput"`).
 * 2. Pencegahan pengiriman formulir ganda (`data-cegah-ganda="ya"`) untuk form non-modal.
 */
document.addEventListener('DOMContentLoaded', function () {
    /**
     * 1. Toggle Tampilkan / Sembunyikan Kata Sandi
     */
    const tombolToggleSandi = document.querySelectorAll('button[data-toggle-sandi]');
    tombolToggleSandi.forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            const idInput = tombol.getAttribute('data-toggle-sandi');
            const inputSandi = idInput ? document.getElementById(idInput) : null;
            if (!inputSandi) {
                return;
            }
            const apakahPassword = inputSandi.type === 'password';
            inputSandi.type = apakahPassword ? 'text' : 'password';
            const ikon = tombol.querySelector('i');
            if (ikon) {
                ikon.className = apakahPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            }
        });
    });

    /**
     * 2. Pencegahan Double Submit pada Formulir Standar (Login, Tambah Nasabah, Keluar)
     * Formulir transaksi dengan `data-konfirmasi-transaksi="ya"` ditangani oleh dasbor-interaktif.js.
     */
    const daftarFormulirBiasa = document.querySelectorAll('form[data-cegah-ganda="ya"]:not([data-konfirmasi-transaksi="ya"])');
    daftarFormulirBiasa.forEach(function (formulir) {
        formulir.addEventListener('submit', function () {
            const tombolKirim = formulir.querySelector('button[type="submit"]');
            if (tombolKirim) {
                tombolKirim.disabled = true;
                tombolKirim.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
            }
        });
    });
});
