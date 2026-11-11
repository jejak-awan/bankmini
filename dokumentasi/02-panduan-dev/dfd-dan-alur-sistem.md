# Data Flow Diagram (DFD), Sequence Diagram, & Alur Sistem — Bahan Belajar RPL

Dokumen ini disusun khusus sebagai **materi pembelajaran Rekayasa Perangkat Lunak (RPL)** untuk memahami pemodelan alur data dan proses bisnis pada **Sistem Informasi Bank Mini (PHP Native)**.

---

## 1. Perbedaan DFD, ERD, dan Flowchart (Konsep Dasar RPL)

Sebelum membaca diagram di bawah ini, siswa RPL perlu memahami perbedaan fungsi ketiga diagram pemodelan perangkat lunak:

| Jenis Diagram | Fokus Pemodelan | Pertanyaan yang Dijawab |
| :--- | :--- | :--- |
| **DFD (*Data Flow Diagram*)** | **Aliran data** dari pengguna luar, melewati proses, menuju tempat penyimpanan data (*Data Store*). | *"Dari mana data berasal, diproses oleh modul apa, dan disimpan ke tabel mana?"* |
| **ERD (*Entity Relationship Diagram*)** | **Struktur data & relasi antar tabel** di dalam basis data. | *"Tabel apa saja yang ada di database dan bagaimana hubungan antar tabel tersebut?"* |
| **Flowchart / Sequence Diagram** | **Urutan langkah algoritma** dan interaksi antar objek/lapisan kode (`Pengendali -> Layanan -> Model`). | *"Bagaimana urutan logika program dari awal hingga akhir (termasuk percabangan `if/else`)?"* |

---

## 2. DFD Level 0 (Diagram Konteks / Context Diagram)

**Diagram Konteks (DFD Level 0)** memandang seluruh aplikasi **Bank Mini** sebagai satu proses tunggal (`0.0 Sistem Informasi Bank Mini`) yang berinteraksi dengan 4 **Entitas Eksternal (Terminator)**:
1. **Administrator (`admin`)**
2. **Petugas Teller (`teller`)**
3. **Supervisor (`supervisor`)**
4. **Nasabah (`nasabah`)**

```mermaid
flowchart LR
    Admin["Entitas: Administrator"]
    Teller["Entitas: Petugas Teller"]
    Supervisor["Entitas: Supervisor"]
    Nasabah["Entitas: Nasabah"]

    Sistem(("0.0 Sistem Informasi Bank Mini (Rupiah IDR)"))

    Admin -- "Data Pengguna, Parameter Produk Tabungan" --> Sistem
    Sistem -- "Laporan Rekapitulasi, Log Audit Sistem" --> Admin

    Teller -- "Data Nasabah Baru, Pembukaan Rekening, Input Setor/Tarik/Transfer" --> Sistem
    Sistem -- "Bukti Transaksi, Nomor Rekening, Info Saldo Efektif" --> Teller

    Supervisor -- "Persetujuan & Input Jurnal Koreksi (Reversal), Filter Tanggal Mutasi" --> Sistem
    Sistem -- "Laporan Buku Besar Mutasi & Bukti Koreksi" --> Supervisor

    Nasabah -- "Formulir Registrasi, Bukti Setor/Tarik" --> Teller
    Sistem -- "Informasi Saldo & Riwayat Mutasi Rekening" --> Nasabah
```

---

## 3. DFD Level 1 (Dekomposisi Proses Utama)

Pada **DFD Level 1**, proses tunggal `0.0` dipecah menjadi **5 Proses Utama (`1.0` s.d. `5.0`)** dan **6 Penyimpanan Data (*Data Store* `D1` s.d. `D6`)** yang merepresentasikan tabel di MySQL InnoDB:

```mermaid
flowchart TD
    %% Entitas Eksternal
    Admin["Entitas: Admin"]
    Teller["Entitas: Teller"]
    Supervisor["Entitas: Supervisor"]

    %% Proses Utama
    P1(("1.0 Autentikasi & Otorisasi Peran"))
    P2(("2.0 Manajemen Master Nasabah & Produk"))
    P3(("3.0 Pembukaan Rekening Baru"))
    P4(("4.0 Transaksi Keuangan Rupiah ACID"))
    P5(("5.0 Pelaporan Mutasi & Audit"))

    %% Data Store (Tabel Database)
    D1[("D1: pengguna")]
    D2[("D2: nasabah")]
    D3[("D3: produk_tabungan")]
    D4[("D4: rekening")]
    D5[("D5: mutasi_transaksi")]
    D6[("D6: log_audit")]

    %% Aliran ke Proses 1.0
    Admin & Teller & Supervisor -- "Kredensial Masuk + Token CSRF" --> P1
    P1 <--> D1
    P1 -- "Catat Masuk/Keluar" --> D6

    %% Aliran ke Proses 2.0
    Teller -- "Profil Nasabah Baru" --> P2
    Admin -- "Parameter Produk Rupiah" --> P2
    P2 --> D2
    P2 --> D3

    %% Aliran ke Proses 3.0
    Teller -- "ID Nasabah, ID Produk, Setoran Awal IDR" --> P3
    D2 -- "Status Aktif Nasabah" --> P3
    D3 -- "Batas Setoran Awal Minimum" --> P3
    P3 -- "Rekening Baru + Saldo Awal" --> D4
    P3 -- "Mutasi KREDIT Awal" --> D5
    P3 -- "Log Buka Rekening" --> D6

    %% Aliran ke Proses 4.0
    Teller -- "Setor / Tarik / Transfer (IDR)" --> P4
    Supervisor -- "Jurnal Koreksi (Reversal)" --> P4
    D3 -- "Batas Saldo Mengendap Minimum" --> P4
    P4 <--> D4
    P4 -- "Simpan Mutasi Append-Only" --> D5
    P4 -- "Log Transaksi Keuangan" --> D6

    %% Aliran ke Proses 5.0
    D4 & D5 & D6 --> P5
    P5 -- "Laporan Buku Besar Mutasi & Dasbor" --> Admin & Teller & Supervisor
```

---

## 4. DFD Level 2 — Rincian Proses `4.0 Transaksi Keuangan Rupiah ACID`

Proses `4.0` merupakan jantung dari sistem perbankan dan dipecah lagi menjadi 4 sub-proses (`4.1` s.d. `4.4`) yang berada di dalam `aplikasi/Layanan/LayananTransaksi.php`:

```mermaid
flowchart TD
    Teller["Entitas: Teller"]
    Supervisor["Entitas: Supervisor / Admin"]

    P41(("4.1 Proses Setor Tunai (KREDIT)"))
    P42(("4.2 Proses Tarik Tunai (DEBIT)"))
    P43(("4.3 Proses Transfer Antar Rekening"))
    P44(("4.4 Proses Jurnal Koreksi / Reversal"))

    D3[("D3: produk_tabungan")]
    D4[("D4: rekening")]
    D5[("D5: mutasi_transaksi")]
    D6[("D6: log_audit")]

    Teller -- "ID Rekening, Nominal Setoran IDR" --> P41
    P41 -- "Kunci FOR UPDATE & Tambah Saldo" --> D4
    P41 -- "Insert Mutasi SETOR (KREDIT)" --> D5
    P41 -- "Catat Audit SETOR_TUNAI" --> D6

    Teller -- "ID Rekening, Nominal Penarikan IDR" --> P42
    D3 -- "Cek Saldo Mengendap Minimum" --> P42
    P42 -- "Kunci FOR UPDATE & Kurangi Saldo" --> D4
    P42 -- "Insert Mutasi TARIK (DEBIT)" --> D5
    P42 -- "Catat Audit TARIK_TUNAI" --> D6

    Teller -- "ID Rekening Asal, ID Tujuan, Nominal IDR" --> P43
    D3 -- "Cek Saldo Mengendap Pengirim" --> P43
    P43 -- "Kunci Berurutan (ID Kecil -> Besar) & Update 2 Saldo" --> D4
    P43 -- "Insert 2 Mutasi (TRANSFER_KELUAR & TRANSFER_MASUK)" --> D5
    P43 -- "Catat Audit TRANSFER" --> D6

    Supervisor -- "ID Mutasi Asal, Alasan Koreksi" --> P44
    D5 -- "Baca Mutasi Asal" --> P44
    P44 -- "Kunci FOR UPDATE & Balikkan Saldo" --> D4
    P44 -- "Insert Mutasi KOREKSI (id_referensi_transaksi)" --> D5
    P44 -- "Catat Audit JURNAL_KOREKSI" --> D6
```

---

## 5. Sequence Diagram: Siklus Transaksi Tarik Tunai (SoC & ACID)

Diagram urutan (*Sequence Diagram*) berikut memperlihatkan bagaimana sebuah klik tombol **"Proses Tarik Tunai"** mengalir melintasi lapisan arsitektur (`Pengendali -> Layanan -> Model -> MySQL InnoDB`) dengan pola **PRG (Post-Redirect-Get)**:

```mermaid
sequenceDiagram
    actor Teller as Petugas Teller
    participant Index as public/index.php
    participant Ctrl as PengendaliTransaksi
    participant Csrf as TokenCsrf
    participant Svc as LayananTransaksi
    participant MdlRek as ModelRekening
    participant MdlMut as ModelMutasi
    participant DB as MySQL InnoDB

    Teller->>Index: POST /transaksi/tarik (id_rekening, nominal_penarikan, token_csrf)
    Index->>Ctrl: prosesTarikTunai()
    Ctrl->>Csrf: validasiToken($_POST['token_csrf'])
    Csrf-->>Ctrl: true (hash_equals cocok)
    Ctrl->>Svc: prosesTarikTunai($idRekening, $nominalPenarikan, $idPetugas, ...)
    Svc->>DB: beginTransaction()
    Svc->>MdlRek: kunciRekeningUntukPembaruan($idRekening)
    MdlRek->>DB: SELECT ... FROM rekening JOIN produk_tabungan WHERE id = :id FOR UPDATE
    DB-->>MdlRek: Baris rekening terkunci (saldo, saldo_mengendap_minimum)
    alt Saldo Efektif < Nominal Penarikan
        Svc->>DB: rollBack()
        Svc-->>Ctrl: throw DomainException("Saldo efektif tidak mencukupi")
        Ctrl->>Teller: HTTP 303 Redirect GET /transaksi/tarik (Pesan Kilat Kesalahan)
    else Saldo Efektif Mencukupi
        Svc->>MdlMut: simpanMutasiBaru(TARIK, DEBIT, nominal, saldo_sebelum, saldo_sesudah)
        MdlMut->>DB: INSERT INTO mutasi_transaksi ...
        Svc->>MdlRek: perbaruiSaldoRekening($idRekening, $saldoSesudah)
        MdlRek->>DB: UPDATE rekening SET saldo = :saldo_sesudah WHERE id = :id
        Svc->>DB: commit()
        Svc-->>Ctrl: Ringkasan Bukti Transaksi (kode_referensi, saldo_sesudah)
        Ctrl->>Csrf: rotasiToken()
        Ctrl->>Teller: HTTP 303 Redirect GET /transaksi/tarik (Pesan Kilat Sukses)
    end
```

---

## 6. Flowchart Algoritma Perhitungan Saldo Efektif & Penguncian Anti-Deadlock

```mermaid
flowchart TD
    Mulai(["Mulai Transaksi di LayananTransaksi"]) --> ValidasiNominal{"Apakah Format Nominal Rupiah Valid & > Rp 0?"}
    ValidasiNominal -- "Tidak" --> LemparInvalid["Lempar InvalidArgumentException"]
    ValidasiNominal -- "Ya" --> BukaTx["Buka Transaksi DB: $koneksi->beginTransaction()"]
    BukaTx --> KunciBaris["Kunci Baris Rekening: SELECT ... FOR UPDATE"]
    KunciBaris --> CekAktif{"Apakah Rekening Ditemukan & Status = 'aktif'?"}
    CekAktif -- "Tidak" --> Rollback1["$koneksi->rollBack() + Lempar DomainException"]
    CekAktif -- "Ya" --> HitungEfektif["Hitung: $saldoEfektif = bcsub($saldoSebelum, $saldoMengendapMinimum, 2)"]
    HitungEfektif --> CekCukup{"Apakah bccomp($saldoEfektif, $nominal, 2) >= 0?"}
    CekCukup -- "Tidak (Kurang)" --> Rollback2["$koneksi->rollBack() + Lempar DomainException"]
    CekCukup -- "Ya (Cukup)" --> HitungAkhir["Hitung: $saldoSesudah = bcsub($saldoSebelum, $nominal, 2)"]
    HitungAkhir --> SimpanMutasi["INSERT ke mutasi_transaksi (Append-Only)"]
    SimpanMutasi --> UpdateSaldo["UPDATE rekening SET saldo = $saldoSesudah"]
    UpdateSaldo --> CatatAudit["INSERT ke log_audit"]
    CatatAudit --> CommitTx["Simpan Permanen: $koneksi->commit()"]
    CommitTx --> Selesai(["Selesai & Kembalikan Bukti Transaksi"])
```
