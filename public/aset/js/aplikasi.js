/**
 * Skrip Interaksi Antarmuka Bank Mini
 */
document.addEventListener('DOMContentLoaded', function () {
    // Mencegah klik ganda (double submit) pada formulir transaksi keuangan
    const daftarFormulir = document.querySelectorAll('form[data-cegah-ganda="ya"]');
    daftarFormulir.forEach(function (formulir) {
        formulir.addEventListener('submit', function () {
            const tombolKirim = formulir.querySelector('button[type="submit"]');
            if (tombolKirim) {
                tombolKirim.disabled = true;
                tombolKirim.textContent = 'Memproses...';
            }
        });
    });
});

