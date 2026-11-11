<?php

declare(strict_types=1);

namespace Aplikasi\Layanan;

use Aplikasi\Inti\PembatasLaju;
use Aplikasi\Inti\PencatatLog;
use Aplikasi\Inti\Sesi;
use Aplikasi\Model\ModelLogAudit;
use Aplikasi\Model\ModelPengguna;
use DomainException;

/**
 * LayananAutentikasi
 *
 * @konsep_rpl Service Layer, Verifikasi Hash BCrypt (`password_verify`), Anti Brute-Force, & Anti-Session Fixation
 * Memisahkan logika autentikasi dari `PengendaliAutentikasi`:
 * 1. Kata sandi tidak pernah dibandingkan dengan operator `==`, melainkan menggunakan
 *    `password_verify()` terhadap `hash_kata_sandi` BCrypt di database.
 * 2. Melindungi endpoint masuk dari serangan brute-force menggunakan `PembatasLaju` (maks 5 gagal / 5 menit).
 * 3. Setelah autentikasi berhasil, `Sesi::regenerasiIdSesi()` wajib dipanggil sebelum
 *    menyimpan data pengguna ke sesi dan mengatur timer `waktu_aktivitas_terakhir`.
 */
class LayananAutentikasi
{
    private ModelPengguna $modelPengguna;
    private ModelLogAudit $modelLogAudit;
    private PembatasLaju $pembatasLaju;

    /**
     * Konstruktor LayananAutentikasi.
     *
     * @param ModelPengguna     $modelPengguna Model akses tabel pengguna
     * @param ModelLogAudit     $modelLogAudit Model akses tabel log_audit
     * @param PembatasLaju|null $pembatasLaju  Pembatas laju anti brute-force (opsional untuk pengujian)
     */
    public function __construct(
        ModelPengguna $modelPengguna,
        ModelLogAudit $modelLogAudit,
        ?PembatasLaju $pembatasLaju = null
    ) {
        $this->modelPengguna = $modelPengguna;
        $this->modelLogAudit = $modelLogAudit;
        $this->pembatasLaju = $pembatasLaju ?? new PembatasLaju();
    }

    /**
     * Memverifikasi kredensial pengguna dan menginisialisasi sesi aman jika berhasil.
     *
     * @param string $namaPengguna Nama pengguna yang diinput
     * @param string $kataSandi    Kata sandi teks biasa yang akan diverifikasi dengan password_verify
     * @param string $alamatIp     Alamat IP klien
     * @return array<string, mixed> Data profil pengguna yang berhasil masuk
     *
     * @throws DomainException Jika dibatasi rate limit, nama pengguna/kata sandi salah, atau akun nonaktif
     */
    public function prosesMasuk(string $namaPengguna, string $kataSandi, string $alamatIp): array
    {
        // [Langkah 1 - Konsep RPL]: Sanitasi input nama pengguna dan pastikan tidak kosong sebelum query database.
        $namaBersih = trim($namaPengguna);
        if ($namaBersih === '' || $kataSandi === '') {
            throw new DomainException('Nama pengguna dan kata sandi wajib diisi.');
        }

        // [Langkah 2 - Konsep RPL]: Periksa pembatas laju (rate limiter) untuk mencegah tebak sandi otomatis.
        $kunciLaju = 'masuk|' . strtolower($namaBersih) . '|' . $alamatIp;
        $sisaDetikBlokir = $this->pembatasLaju->ambilSisaDetikBlokir($kunciLaju);
        if ($sisaDetikBlokir > 0) {
            PencatatLog::keamanan('Percobaan masuk ditolak sementara oleh pembatas laju (brute-force protection).', [
                'nama_pengguna' => $namaBersih,
                'alamat_ip' => $alamatIp,
                'sisa_detik' => $sisaDetikBlokir,
            ]);
            throw new DomainException(
                "Terlalu banyak percobaan masuk gagal. Silakan tunggu {$sisaDetikBlokir} detik sebelum mencoba lagi."
            );
        }

        // [Langkah 3 - Konsep RPL]: Verifikasi hash kata sandi secara aman menggunakan algoritma bcrypt/argon2 PHP.
        $dataPengguna = $this->modelPengguna->ambilBerdasarkanNamaPengguna($namaBersih);
        if (
            $dataPengguna === null
            || !password_verify($kataSandi, (string) $dataPengguna['hash_kata_sandi'])
        ) {
            $jumlahGagal = $this->pembatasLaju->catatKegagalan($kunciLaju);
            PencatatLog::keamanan('Kredensial masuk tidak sesuai.', [
                'nama_pengguna' => $namaBersih,
                'alamat_ip' => $alamatIp,
                'percobaan_gagal' => $jumlahGagal,
            ]);
            throw new DomainException('Nama pengguna atau kata sandi tidak sesuai.');
        }

        if ($dataPengguna['status'] !== 'aktif') {
            throw new DomainException('Akun pengguna sedang dinonaktifkan. Hubungi Administrator.');
        }

        // [Langkah 4 - Konsep RPL]: Reset pencatat kegagalan, regenerasi ID sesi, dan mulai pelacakan idle timeout.
        $this->pembatasLaju->reset($kunciLaju);
        Sesi::regenerasiIdSesi();
        Sesi::periksaKedaluwarsaSesi();
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
