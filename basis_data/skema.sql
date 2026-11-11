-- ============================================================================
-- Skema Basis Data Sistem Informasi Bank Mini (Single Source of Truth)
-- Mesin Penyimpanan : MySQL InnoDB (Wajib Transaksi ACID & Row Locking)
-- Mata Uang Tunggal : Rupiah (IDR) - DECIMAL(15,2)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `bankmini_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `bankmini_db`;

-- 1. Tabel pengguna (Petugas Internal: Admin, Teller, Supervisor)
CREATE TABLE IF NOT EXISTS `pengguna` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama_pengguna` VARCHAR(50) NOT NULL UNIQUE,
    `hash_kata_sandi` VARCHAR(255) NOT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `peran` ENUM('admin', 'teller', 'supervisor') NOT NULL DEFAULT 'teller',
    `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    `dibuat_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `diperbarui_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel nasabah (Pemilik Rekening: Siswa, Guru, Umum)
CREATE TABLE IF NOT EXISTS `nasabah` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nomor_identitas` VARCHAR(50) NOT NULL UNIQUE COMMENT 'NIS / NIP / NIK',
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `jenis_nasabah` ENUM('siswa', 'guru', 'umum') NOT NULL DEFAULT 'siswa',
    `kelas_atau_unit` VARCHAR(50) NOT NULL,
    `nomor_telepon` VARCHAR(25) DEFAULT NULL,
    `alamat` TEXT DEFAULT NULL,
    `hash_kata_sandi` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    `dibuat_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `diperbarui_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_nasabah_nama` (`nama_lengkap`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel produk_tabungan (Master Produk Simpanan Rupiah IDR)
CREATE TABLE IF NOT EXISTS `produk_tabungan` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kode_produk` VARCHAR(20) NOT NULL UNIQUE,
    `nama_produk` VARCHAR(100) NOT NULL,
    `setoran_awal_minimum` DECIMAL(15,2) NOT NULL DEFAULT 10000.00,
    `saldo_mengendap_minimum` DECIMAL(15,2) NOT NULL DEFAULT 10000.00,
    `biaya_admin_bulanan` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    `dibuat_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel rekening (Rekening Tabungan Nasabah & Cached Balance Rupiah IDR)
CREATE TABLE IF NOT EXISTS `rekening` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nomor_rekening` VARCHAR(30) NOT NULL UNIQUE,
    `id_nasabah` INT UNSIGNED NOT NULL,
    `id_produk` INT UNSIGNED NOT NULL,
    `saldo` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('aktif', 'dibekukan', 'tutup') NOT NULL DEFAULT 'aktif',
    `tanggal_buka` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `tanggal_tutup` DATETIME DEFAULT NULL,
    `diperbarui_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_rekening_status` (`status`),
    CONSTRAINT `fk_rekening_nasabah` FOREIGN KEY (`id_nasabah`) REFERENCES `nasabah` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_rekening_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk_tabungan` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel mutasi_transaksi (Append-Only / Immutable Ledger Rupiah IDR)
CREATE TABLE IF NOT EXISTS `mutasi_transaksi` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kode_referensi` VARCHAR(40) NOT NULL UNIQUE,
    `id_rekening` INT UNSIGNED NOT NULL,
    `jenis_transaksi` ENUM('SETOR', 'TARIK', 'TRANSFER_MASUK', 'TRANSFER_KELUAR', 'BIAYA_ADMIN', 'KOREKSI') NOT NULL,
    `tipe_mutasi` ENUM('KREDIT', 'DEBIT') NOT NULL,
    `nominal` DECIMAL(15,2) NOT NULL,
    `saldo_sebelum` DECIMAL(15,2) NOT NULL,
    `saldo_sesudah` DECIMAL(15,2) NOT NULL,
    `keterangan` VARCHAR(255) NOT NULL,
    `id_referensi_transaksi` BIGINT UNSIGNED DEFAULT NULL,
    `id_petugas` INT UNSIGNED NOT NULL,
    `waktu_transaksi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_mutasi_rekening_waktu` (`id_rekening`, `waktu_transaksi`),
    INDEX `idx_mutasi_waktu` (`waktu_transaksi`),
    CONSTRAINT `fk_mutasi_rekening` FOREIGN KEY (`id_rekening`) REFERENCES `rekening` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_mutasi_petugas` FOREIGN KEY (`id_petugas`) REFERENCES `pengguna` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_mutasi_referensi` FOREIGN KEY (`id_referensi_transaksi`) REFERENCES `mutasi_transaksi` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel log_audit (Jejak Audit Aktivitas & Perubahan Data Sensitif)
CREATE TABLE IF NOT EXISTS `log_audit` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_pengguna` INT UNSIGNED DEFAULT NULL,
    `aksi` VARCHAR(60) NOT NULL,
    `entitas` VARCHAR(60) NOT NULL,
    `id_entitas` VARCHAR(60) NOT NULL,
    `data_lama` JSON DEFAULT NULL,
    `data_baru` JSON DEFAULT NULL,
    `alamat_ip` VARCHAR(45) NOT NULL,
    `dibuat_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_waktu` (`dibuat_pada`),
    CONSTRAINT `fk_audit_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

