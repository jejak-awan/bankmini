<?php

declare(strict_types=1);

namespace Aplikasi\Layanan;

use Aplikasi\Inti\Sesi;
use Aplikasi\Model\ModelLogAudit;
use Aplikasi\Model\ModelPengguna;
use DomainException;

/**
 * LayananAutentikasi
 *
 * Single Source of Truth (SoT) untuk proses verifikasi masuk (login),
 * keluar (logout), dan pencatatan jejak audit autentikasi pengguna internal.
 */
class LayananAutentikasi
{
    private ModelPengguna $modelPengguna;
    private ModelLogAudit $modelLogAudit;

    /**
     * Konstruktor LayananAutentikasi.
     *
     * @param ModelPengguna $modelPengguna Model akses tabel pengguna
     * @param ModelLogAudit $modelLogAudit Model akses tabel log_audit
     */
    public function __construct(ModelPengguna $modelPengguna, ModelLogAudit $modelLogAudit)
    {
        $this->modelPengguna = $modelPengguna;
        $this->modelLogAudit = $modelLogAudit;
    }

    /**
     * Memverifikasi kredensial pengguna dan menginisialisasi sesi aman jika berhasil.
     *
     * @param string $namaPengguna Nama pengguna yang diinput
     * @param string $kataSandi    Kata sandi teks biasa yang akan diverifikasi dengan password_verify
     * @param string $alamatIp     Alamat IP klien
     * @return array<string, mixed> Data profil pengguna yang berhasil masuk
     *
     * @throws DomainException Jika nama pengguna/kata sandi salah atau akun nonaktif
     */
    public function prosesMasuk(string $namaPengguna, string $kataSandi, string $alamatIp): array
    {
        $namaBersih = trim($namaPengguna);
        if ($namaBersih === '' || $kataSandi === '') {
            throw new DomainException('Nama pengguna dan kata sandi wajib diisi.');
        }

        $dataPengguna = $this->modelPengguna->ambilBerdasarkanNamaPengguna($namaBersih);
        if (
            $dataPengguna === null
            || !password_verify($kataSandi, (string) $dataPengguna['hash_kata_sandi'])
        ) {
            throw new DomainException('Nama pengguna atau kata sandi tidak sesuai.');
        }

        if ($dataPengguna['status'] !== 'aktif') {
            throw new DomainException('Akun pengguna sedang dinonaktifkan. Hubungi Administrator.');
        }

        Sesi::regenerasiIdSesi();
        Sesi::tetapkan('id_pengguna', (int) $dataPengguna['id']);
        Sesi::tetapkan('nama_pengguna', (string) $dataPengguna['nama_pengguna']);
        Sesi::tetapkan('nama_lengkap_pengguna', (string) $dataPengguna['nama_lengkap']);
        Sesi::tetapkan('peran_pengguna', (string) $dataPengguna['peran']);

        $this->modelLogAudit->catatLog([
            'id_pengguna' => (int) $dataPengguna['id'],
            'aksi'        => 'MASUK_SISTEM',
            'entitas'     => 'pengguna',
            'id_entitas'  => (string) $dataPengguna['id'],
            'data_lama'   => null,
            'data_baru'   => ['nama_pengguna' => $dataPengguna['nama_pengguna']],
            'alamat_ip'   => $alamatIp,
        ]);

        return [
            'id'            => (int) $dataPengguna['id'],
            'nama_pengguna' => (string) $dataPengguna['nama_pengguna'],
            'nama_lengkap'  => (string) $dataPengguna['nama_lengkap'],
            'peran'         => (string) $dataPengguna['peran'],
        ];
    }

    /**
     * Memproses keluar (logout) pengguna dan membersihkan sesi.
     *
     * @param int|null $idPengguna ID pengguna yang keluar
     * @param string   $alamatIp   Alamat IP klien
     * @return void
     */
    public function prosesKeluar(?int $idPengguna, string $alamatIp): void
    {
        if ($idPengguna !== null && $idPengguna > 0) {
            $this->modelLogAudit->catatLog([
                'id_pengguna' => $idPengguna,
                'aksi'        => 'KELUAR_SISTEM',
                'entitas'     => 'pengguna',
                'id_entitas'  => (string) $idPengguna,
                'data_lama'   => null,
                'data_baru'   => null,
                'alamat_ip'   => $alamatIp,
            ]);
        }

        Sesi::hancurkanSesi();
    }
}

