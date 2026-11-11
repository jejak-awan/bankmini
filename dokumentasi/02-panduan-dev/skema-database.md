# Skema Database & Desain Ledger (`bankmini_db`)

Seluruh tabel dan kolom wajib menggunakan **Bahasa Indonesia** dengan format `snake_case`, storage engine **InnoDB**, character set `utf8mb4_unicode_ci`, dan satuan mata uang tunggal **Rupiah (`IDR`)** dengan tipe data `DECIMAL(15,2)`.

---

## 1. Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    PENGGUNA ||--o{ MUTASI_TRANSAKSI : "memproses (id_petugas)"
    PENGGUNA ||--o{ LOG_AUDIT : "mencatat (id_pengguna)"
    NASABAH ||--o{ REKENING : "memiliki (id_nasabah)"
    PRODUK_TABUNGAN ||--o{ REKENING : "mengatur (id_produk)"
    REKENING ||--o{ MUTASI_TRANSAKSI : "mencatat_mutasi (id_rekening)"

    PENGGUNA {
        int id PK
        string nama_pengguna UK
        string hash_kata_sandi
        string nama_lengkap
        enum peran "admin, teller, supervisor"
        enum status "aktif, nonaktif"
        datetime dibuat_pada
        datetime diperbarui_pada
    }

    NASABAH {
        int id PK
        string nomor_identitas UK "NIS / NIP / NIK"
        string nama_lengkap
        enum jenis_nasabah "siswa, guru, umum"
        string kelas_atau_unit
        string nomor_telepon
        text alamat
        string hash_kata_sandi "Opsional untuk portal nasabah"
        enum status "aktif, nonaktif"
        datetime dibuat_pada
        datetime diperbarui_pada
    }

    PRODUK_TABUNGAN {
        int id PK
        string kode_produk UK "Contoh: TAB-SISWA"
        string nama_produk
        decimal setoran_awal_minimum "DECIMAL(15,2) Rupiah"
        decimal saldo_mengendap_minimum "DECIMAL(15,2) Rupiah"
        decimal biaya_admin_bulanan "DECIMAL(15,2) Rupiah"
        enum status "aktif, nonaktif"
        datetime dibuat_pada
    }

    REKENING {
        int id PK
        string nomor_rekening UK
        int id_nasabah FK
        int id_produk FK
        decimal saldo "DECIMAL(15,2) Rupiah - Cached Balance"
        enum status "aktif, dibekukan, tutup"
        datetime tanggal_buka
        datetime tanggal_tutup
        datetime diperbarui_pada
    }

    MUTASI_TRANSAKSI {
        bigint id PK
        string kode_referensi UK "Contoh: STR-20261111-104829"
        int id_rekening FK
        enum jenis_transaksi "SETOR, TARIK, TRANSFER_MASUK, TRANSFER_KELUAR, BIAYA_ADMIN, KOREKSI"
        enum tipe_mutasi "KREDIT, DEBIT"
        decimal nominal "DECIMAL(15,2) Rupiah"
        decimal saldo_sebelum "DECIMAL(15,2) Rupiah"
        decimal saldo_sesudah "DECIMAL(15,2) Rupiah"
        string keterangan
        bigint id_referensi_transaksi "FK opsional untuk pasangan transfer/koreksi"
        int id_petugas FK
        datetime waktu_transaksi
    }

    LOG_AUDIT {
        bigint id PK
        int id_pengguna FK
        string aksi
        string entitas
        string id_entitas
        json data_lama
        json data_baru
        string alamat_ip
        datetime dibuat_pada
    }
```

---

## 2. Daftar Tabel & Konvensi Kolom Bahasa Indonesia

| Nama Tabel | Deskripsi | Kolom Kunci & Foreign Key |
| :--- | :--- | :--- |
| `pengguna` | Menyimpan akun petugas internal (Admin, Teller, Supervisor). | PK: `id`, UK: `nama_pengguna` |
| `nasabah` | Menyimpan profil pemilik rekening (Siswa, Guru, Umum). | PK: `id`, UK: `nomor_identitas` |
| `produk_tabungan` | Master parameter produk simpanan dalam Rupiah (`IDR`). | PK: `id`, UK: `kode_produk` |
| `rekening` | Menyimpan rekening tabungan nasabah dan *cached balance* (`saldo`). | PK: `id`, UK: `nomor_rekening`, FK: `id_nasabah`, `id_produk` |
| `mutasi_transaksi` | Buku besar (*Immutable Ledger*) pencatat seluruh mutasi masuk/keluar Rupiah. | PK: `id`, UK: `kode_referensi`, FK: `id_rekening`, `id_petugas`, `id_referensi_transaksi` |
| `log_audit` | Jejak audit aktivitas penting dan perubahan data sensitif. | PK: `id`, FK: `id_pengguna` |

---

## 3. Aturan Invarian Database (Database Invariants)
1. **Invarian Saldo (`rekening.saldo`)**:
   Nilai `rekening.saldo` (dalam Rupiah `DECIMAL(15,2)`) setiap saat harus sama dengan total mutasi `KREDIT` dikurangi total mutasi `DEBIT` pada `mutasi_transaksi` untuk `id_rekening` tersebut, serta sama dengan `saldo_sesudah` pada baris mutasi terakhir.
2. **Immutable `mutasi_transaksi`**:
   Aplikasi tidak memiliki fitur `UPDATE nominal` atau `DELETE` pada tabel `mutasi_transaksi`. Jika terjadi kesalahan pencatatan, sistem membuat baris baru dengan `jenis_transaksi = 'KOREKSI'` yang membalikkan mutasi sebelumnya dan menunjuk ke `id_referensi_transaksi` asal.
3. **Indeks Wajib**:
   - `rekening (nomor_rekening)` — `UNIQUE`
   - `mutasi_transaksi (id_rekening, waktu_transaksi)` — Mempercepat pencarian mutasi rekening per rentang tanggal.
   - `mutasi_transaksi (kode_referensi)` — `UNIQUE`
