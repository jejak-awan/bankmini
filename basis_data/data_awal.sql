-- ============================================================================
-- Data Awal (Seeder) Sistem Informasi Bank Mini (Mata Uang: Rupiah IDR)
-- Kata sandi default untuk akun di bawah ini adalah: password
-- ============================================================================

USE `bankmini_db`;

INSERT INTO `pengguna` (`nama_pengguna`, `hash_kata_sandi`, `nama_lengkap`, `peran`, `status`)
VALUES
    ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Utama', 'admin', 'aktif'),
    ('teller1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Petugas Teller Satu', 'teller', 'aktif'),
    ('supervisor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Supervisor Bank Mini', 'supervisor', 'aktif')
ON DUPLICATE KEY UPDATE `nama_lengkap` = VALUES(`nama_lengkap`);

INSERT INTO `produk_tabungan` (`kode_produk`, `nama_produk`, `setoran_awal_minimum`, `saldo_mengendap_minimum`, `biaya_admin_bulanan`, `status`)
VALUES
    ('TAB-SISWA', 'Tabungan Pelajar Siswa', 10000.00, 10000.00, 0.00, 'aktif'),
    ('TAB-GURU', 'Tabungan Guru & Staf', 50000.00, 25000.00, 0.00, 'aktif'),
    ('TAB-UMUM', 'Tabungan Warga Sekolah Umum', 25000.00, 15000.00, 1000.00, 'aktif')
ON DUPLICATE KEY UPDATE `nama_produk` = VALUES(`nama_produk`);

