<?php

declare(strict_types=1);

namespace Pengujian\Integrasi;

use Aplikasi\Inti\Sesi;
use Aplikasi\Layanan\LayananAutentikasi;
use Aplikasi\Model\ModelLogAudit;
use Aplikasi\Model\ModelPengguna;
use DomainException;
use Pengujian\PembantuPengujian\KasusUjiDasar;

/**
 * ModelPenggunaPalsuUji
 *
 * Tiruan ModelPengguna di memori untuk pengujian integrasi LayananAutentikasi.
 */
class ModelPenggunaPalsuUji extends ModelPengguna
{
    /** @var array<string, array<string, mixed>> Data pengguna uji di memori */
    private array $daftarPenggunaUji;

    /**
     * Konstruktor ModelPenggunaPalsuUji.
     */
    public function __construct()
    {
        $this->daftarPenggunaUji = [
            'teller1' => [
                'id'              => 2,
                'nama_pengguna'   => 'teller1',
                'hash_kata_sandi' => password_hash('rahasia123', PASSWORD_BCRYPT),
                'nama_lengkap'    => 'Petugas Teller Uji',
                'peran'           => 'teller',
                'status'          => 'aktif',
                'dibuat_pada'     => '2026-11-11 10:00:00',
            ],
            'nonaktif1' => [
                'id'              => 3,
                'nama_pengguna'   => 'nonaktif1',
                'hash_kata_sandi' => password_hash('rahasia123', PASSWORD_BCRYPT),
                'nama_lengkap'    => 'Petugas Nonaktif',
                'peran'           => 'teller',
                'status'          => 'nonaktif',
                'dibuat_pada'     => '2026-11-11 10:00:00',
            ],
        ];
    }

    /**
     * Mengambil data pengguna uji berdasarkan nama_pengguna.
     *
     * @param string $namaPengguna Nama pengguna
     * @return array<string, mixed>|null Data pengguna atau null
     */
    public function ambilBerdasarkanNamaPengguna(string $namaPengguna): ?array
    {
        return $this->daftarPenggunaUji[$namaPengguna] ?? null;
    }
}

/**
 * ModelLogAuditPalsuUji
 *
 * Tiruan ModelLogAudit di memori untuk mencatat jejak audit selama pengujian.
 */
class ModelLogAuditPalsuUji extends ModelLogAudit
{
    /** @var array<int, array<string, mixed>> Daftar log audit yang tercatat */
    public array $catatanAudit = [];

    /**
     * Konstruktor ModelLogAuditPalsuUji.
     */
    public function __construct()
    {
    }

    /**
     * Menyimpan catatan audit ke dalam memori.
     *
     * @param array<string, mixed> $dataAudit Data log audit
     * @return int Nomor urut log
     */
    public function catatLog(array $dataAudit): int
    {
        $this->catatanAudit[] = $dataAudit;
        return count($this->catatanAudit);
    }
}

/**
 * TesLayananAutentikasi
 *
 * Menguji LayananAutentikasi (login berhasil, kata sandi salah, akun nonaktif, dan pencatatan audit).
 */
class TesLayananAutentikasi extends KasusUjiDasar
{
    /**
     * @skenario QA-AUT-001
     * Memastikan pengguna aktif dengan kata sandi benar berhasil masuk dan menetapkan sesi serta log audit.
     *
     * @return void
     */
    public function ujiMasukDenganKredensialSahBerhasilMenetapkanSesi(): void
    {
        $_SESSION = [];
        $modelPengguna = new ModelPenggunaPalsuUji();
        $modelAudit = new ModelLogAuditPalsuUji();
        $layanan = new LayananAutentikasi($modelPengguna, $modelAudit);

        $profil = $layanan->prosesMasuk('teller1', 'rahasia123', '127.0.0.1');

        $this->pastikanSama(2, $profil['id']);
        $this->pastikanSama('teller', Sesi::ambil('peran_pengguna'));
        $this->pastikanSama(1, count($modelAudit->catatanAudit));
    }

    /**
     * @skenario QA-AUT-002
     * Memastikan kata sandi salah atau akun berstatus nonaktif ditolak dengan DomainException.
     *
     * @return void
     */
    public function ujiMasukDenganKataSandiSalahAtauAkunNonaktifDitolak(): void
    {
        $_SESSION = [];
        $layanan = new LayananAutentikasi(new ModelPenggunaPalsuUji(), new ModelLogAuditPalsuUji());

        $this->pastikanMelemparPengecualian(
            DomainException::class,
            function () use ($layanan): void {
                $layanan->prosesMasuk('teller1', 'sandi_salah', '127.0.0.1');
            }
        );

        $this->pastikanMelemparPengecualian(
            DomainException::class,
            function () use ($layanan): void {
                $layanan->prosesMasuk('nonaktif1', 'rahasia123', '127.0.0.1');
            }
        );
    }
}
