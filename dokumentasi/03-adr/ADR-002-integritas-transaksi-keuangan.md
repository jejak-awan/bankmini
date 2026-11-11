# ADR-002: Standar Transaksi Mata Uang Rupiah (IDR), Ledger Append-Only, & Penguncian ACID

- **Status**: Disetujui (*Accepted*)
- **Tanggal**: 2026-11-11
- **Pengambil Keputusan**: Tim Pengembang & Arsitek Sistem

## 1. Konteks dan Permasalahan
Operasi setor, tarik, dan transfer pada **Bank Mini** berisiko mengalami *race condition* (*double spending*), galat pembulatan angka desimal, atau ketidakcocokan saldo jika proses gagal di tengah jalan. Selain itu, sistem perlu menetapkan standar mata uang operasional yang konsisten.

## 2. Keputusan Arsitektur
1. **Mata Uang Tunggal Rupiah (`IDR`)**:
   - Seluruh produk tabungan, saldo rekening, biaya admin, dan mutasi transaksi di dalam sistem ditetapkan menggunakan mata uang **Rupiah (`IDR`)**.
2. **Storage Engine Wajib MySQL InnoDB**:
   - Seluruh tabel wajib menggunakan storage engine **InnoDB** untuk menjamin dukungan penuh terhadap transaksi ACID (`Atomicity, Consistency, Isolation, Durability`), *foreign key constraints*, dan *row-level locking*.
3. **Tipe Data `DECIMAL(15,2)` (Larangan Mutlak `FLOAT`/`DOUBLE`)**:
   - Seluruh kolom nominal Rupiah dan saldo wajib menggunakan `DECIMAL(15,2)`. Tipe data `FLOAT` atau `DOUBLE` dilarang keras karena ketidakakuratan representasi *floating-point* biner.
4. **Pembungkusan `PDO::beginTransaction()` & Penguncian Baris `SELECT ... FOR UPDATE`**:
   - Setiap mutasi saldo wajib dibungkus dalam `PDO::beginTransaction()` ... `commit()` / `rollBack()`.
   - Sebelum membaca dan memvalidasi saldo untuk perubahan, baris pada tabel `rekening` wajib dikunci menggunakan `SELECT ... FOR UPDATE`.
   - Untuk transaksi transfer antar dua rekening, penguncian dilakukan berurutan mulai dari `rekening.id` terkecil guna mencegah *deadlock*.
5. **Tabel Mutasi Bersifat Append-Only / Immutable Ledger**:
   - Tabel `mutasi_transaksi` mencatat `saldo_sebelum`, `nominal`, dan `saldo_sesudah` dalam Rupiah (`IDR`).
   - Dilarang melakukan `DELETE` atau mengubah nominal (`UPDATE`) pada baris transaksi yang sudah tersimpan.
   - Koreksi kesalahan pencatatan hanya boleh dilakukan melalui **Jurnal Balik / Reversal** (membuat transaksi baru bertipe `KOREKSI` yang merujuk ke `id_referensi_transaksi` asal).

## 3. Konsekuensi
- **Positif**: Saldo Rupiah (`IDR`) 100% akurat hingga satuan terkecil, bebas *race condition*, dan setiap perubahan saldo memiliki jejak audit permanen yang tidak dapat dimanipulasi.
- **Negatif**: Koreksi salah input memerlukan alur *Reversal* resmi oleh petugas berwenang, bukan penghapusan baris database.
