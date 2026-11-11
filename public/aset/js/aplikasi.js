/**
 * Skrip Interaksi Antarmuka & Pencegahan Salah Input (UX Perbankan) - Bank Mini
 * Menggunakan Vanilla JS murni + Bootstrap 5 Modal API (100% Offline, Tanpa jQuery/Node.js)
 */
document.addEventListener('DOMContentLoaded', function () {
    const pemformatRupiah = new Intl.NumberFormat('id-ID');

    /**
     * 1. Pratinjau Format Rupiah Live (Error Prevention untuk salah angka nol)
     * Membaca nilai dari input ber-atribut `data-pratinjau-rupiah="idElemenTujuan"`.
     */
    const daftarInputNominal = document.querySelectorAll('input[data-pratinjau-rupiah]');
    daftarInputNominal.forEach(function (inputNominal) {
        const idTarget = inputNominal.getAttribute('data-pratinjau-rupiah');
        const elemenTarget = idTarget ? document.getElementById(idTarget) : null;
        if (!elemenTarget) {
            return;
        }

        const perbaruiPratinjau = function () {
            const angkaMentah = parseFloat(inputNominal.value);
            if (isNaN(angkaMentah) || angkaMentah <= 0) {
                elemenTarget.textContent = 'Terbaca: Rp 0';
                return;
            }
            elemenTarget.textContent = 'Terbaca: Rp ' + pemformatRupiah.format(angkaMentah);
        };

        inputNominal.addEventListener('input', perbaruiPratinjau);
        perbaruiPratinjau();
    });

    /**
     * 2. Pencarian Cepat Rekening & Info Saldo Efektif Real-Time
     * Menghubungkan input pencarian (`data-cari-select="idSelect"`) dan panel info rekening.
     */
    const daftarInputCariSelect = document.querySelectorAll('input[data-cari-select]');
    daftarInputCariSelect.forEach(function (inputCari) {
        const idSelect = inputCari.getAttribute('data-cari-select');
        const elemenSelect = idSelect ? document.getElementById(idSelect) : null;
        if (!elemenSelect) {
            return;
        }

        const semuaOpsiAsli = Array.from(elemenSelect.options).map(function (opsi) {
            return {
                value: opsi.value,
                text: opsi.text,
                saldo: opsi.getAttribute('data-saldo') || '',
                saldoMengendap: opsi.getAttribute('data-saldo-mengendap') || '',
                namaNasabah: opsi.getAttribute('data-nama-nasabah') || '',
                nomorRekening: opsi.getAttribute('data-nomor-rekening') || ''
            };
        });

        inputCari.addEventListener('input', function () {
            const kataKunci = inputCari.value.trim().toLowerCase();
            const nilaiTerpilihSebelumnya = elemenSelect.value;

            elemenSelect.innerHTML = '';
            semuaOpsiAsli.forEach(function (item, indeks) {
                if (indeks === 0 || item.text.toLowerCase().includes(kataKunci)) {
                    const opsiBaru = document.createElement('option');
                    opsiBaru.value = item.value;
                    opsiBaru.textContent = item.text;
                    if (item.saldo !== '') {
                        opsiBaru.setAttribute('data-saldo', item.saldo);
                        opsiBaru.setAttribute('data-saldo-mengendap', item.saldoMengendap);
                        opsiBaru.setAttribute('data-nama-nasabah', item.namaNasabah);
                        opsiBaru.setAttribute('data-nomor-rekening', item.nomorRekening);
                    }
                    if (item.value === nilaiTerpilihSebelumnya) {
                        opsiBaru.selected = true;
                    }
                    elemenSelect.appendChild(opsiBaru);
                }
            });
        });
    });

    // Tampilkan informasi Saldo Saat Ini & Saldo Efektif saat rekening dipilih
    const daftarSelectInfoSaldo = document.querySelectorAll('select[data-target-info-saldo]');
    daftarSelectInfoSaldo.forEach(function (elemenSelect) {
        const idPanel = elemenSelect.getAttribute('data-target-info-saldo');
        const panelInfo = idPanel ? document.getElementById(idPanel) : null;
        if (!panelInfo) {
            return;
        }

        const perbaruiInfoSaldo = function () {
            const opsiAktif = elemenSelect.options[elemenSelect.selectedIndex];
            if (!opsiAktif || !opsiAktif.value) {
                panelInfo.classList.add('d-none');
                return;
            }

            const saldo = parseFloat(opsiAktif.getAttribute('data-saldo') || '0');
            const saldoMengendap = parseFloat(opsiAktif.getAttribute('data-saldo-mengendap') || '0');
            const saldoEfektif = Math.max(0, saldo - saldoMengendap);

            const elSaldo = panelInfo.querySelector('[data-nilai-saldo]');
            const elEfektif = panelInfo.querySelector('[data-nilai-efektif]');
            const elMengendap = panelInfo.querySelector('[data-nilai-mengendap]');

            if (elSaldo) {
                elSaldo.textContent = 'Rp ' + pemformatRupiah.format(saldo);
            }
            if (elMengendap) {
                elMengendap.textContent = 'Rp ' + pemformatRupiah.format(saldoMengendap);
            }
            if (elEfektif) {
                elEfektif.textContent = 'Rp ' + pemformatRupiah.format(saldoEfektif);
            }

            panelInfo.classList.remove('d-none');
        };

        elemenSelect.addEventListener('change', perbaruiInfoSaldo);
        perbaruiInfoSaldo();
    });

    /**
     * 3. Filter Pencarian Tabel Cepat (Client-side Table Filter)
     */
    const daftarFilterTabel = document.querySelectorAll('input[data-cari-tabel]');
    daftarFilterTabel.forEach(function (inputFilter) {
        const idTabel = inputFilter.getAttribute('data-cari-tabel');
        const elemenTabel = idTabel ? document.getElementById(idTabel) : null;
        if (!elemenTabel) {
            return;
        }

        inputFilter.addEventListener('input', function () {
            const kataKunci = inputFilter.value.trim().toLowerCase();
            const daftarBaris = elemenTabel.querySelectorAll('tbody tr');
            daftarBaris.forEach(function (baris) {
                const teksBaris = (baris.textContent || '').toLowerCase();
                baris.style.display = teksBaris.includes(kataKunci) ? '' : 'none';
            });
        });
    });

    /**
     * 4. Modal Konfirmasi Transaksi Keuangan & Pencegahan Double Submit
     */
    const elemenModal = document.getElementById('modalKonfirmasiTransaksi');
    const modalBootstrap = elemenModal && typeof bootstrap !== 'undefined'
        ? new bootstrap.Modal(elemenModal)
        : null;
    const tombolSetujuModal = document.getElementById('tombolKonfirmasiEksekusi');
    let formulirMenungguKonfirmasi = null;

    const nonaktifkanTombolKirim = function (formulir) {
        const tombolKirim = formulir.querySelector('button[type="submit"]');
        if (tombolKirim) {
            tombolKirim.disabled = true;
            tombolKirim.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
        }
    };

    const daftarFormulir = document.querySelectorAll('form[data-cegah-ganda="ya"]');
    daftarFormulir.forEach(function (formulir) {
        formulir.addEventListener('submit', function (acara) {
            const butuhKonfirmasi = formulir.getAttribute('data-konfirmasi-transaksi') === 'ya';
            const sudahDikonfirmasi = formulir.getAttribute('data-sudah-konfirmasi') === 'ya';

            if (butuhKonfirmasi && !sudahDikonfirmasi && modalBootstrap) {
                acara.preventDefault();
                formulirMenungguKonfirmasi = formulir;

                const judulAksi = formulir.getAttribute('data-judul-konfirmasi') || 'Konfirmasi Transaksi';
                const deskripsiEl = document.getElementById('isiRingkasanKonfirmasi');
                const judulEl = document.getElementById('judulModalKonfirmasi');

                if (judulEl) {
                    judulEl.textContent = judulAksi;
                }

                if (deskripsiEl) {
                    deskripsiEl.innerHTML = '';

                    const tambahBarisTeks = function (judulLabel, isiNilai, kelasTambahan) {
                        const baris = document.createElement('div');
                        if (kelasTambahan) {
                            baris.className = kelasTambahan;
                        }
                        const labelEl = document.createElement('strong');
                        labelEl.textContent = judulLabel + ': ';
                        baris.appendChild(labelEl);
                        baris.appendChild(document.createTextNode(isiNilai));
                        deskripsiEl.appendChild(baris);
                    };

                    const semuaSelect = formulir.querySelectorAll('select');
                    semuaSelect.forEach(function (sel) {
                        const label = formulir.querySelector('label[for="' + sel.id + '"]');
                        const opsi = sel.options[sel.selectedIndex];
                        if (label && opsi && opsi.value) {
                            tambahBarisTeks(label.textContent || 'Pilihan', opsi.textContent || '', 'mb-1');
                        }
                    });

                    const inputUang = formulir.querySelector('input[type="number"]');
                    if (inputUang && inputUang.value) {
                        const nilaiAngka = parseFloat(inputUang.value);
                        const teksRupiah = isNaN(nilaiAngka) ? inputUang.value : 'Rp ' + pemformatRupiah.format(nilaiAngka);
                        tambahBarisTeks('Nominal', teksRupiah, 'mt-2 fs-5 text-primary');
                    }

                    const inputAlasan = formulir.querySelector('input[name="alasan_koreksi"]');
                    if (inputAlasan && inputAlasan.value) {
                        tambahBarisTeks('Alasan', inputAlasan.value, 'mt-1');
                    }

                    if (!deskripsiEl.hasChildNodes()) {
                        deskripsiEl.textContent = 'Apakah Anda yakin data yang dimasukkan sudah benar?';
                    }
                }

                modalBootstrap.show();
                return;
            }

            nonaktifkanTombolKirim(formulir);
        });
    });

    if (tombolSetujuModal) {
        tombolSetujuModal.addEventListener('click', function () {
            if (!formulirMenungguKonfirmasi) {
                return;
            }
            formulirMenungguKonfirmasi.setAttribute('data-sudah-konfirmasi', 'ya');
            if (modalBootstrap) {
                modalBootstrap.hide();
            }
            nonaktifkanTombolKirim(formulirMenungguKonfirmasi);
            formulirMenungguKonfirmasi.submit();
        });
    }
});
