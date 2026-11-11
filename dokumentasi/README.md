# Dokumentasi Projek Bank Mini (PHP Native)

Selamat datang di repositori dokumentasi resmi **Sistem Informasi Bank Mini**. Dokumentasi ini dirancang sebagai *Single Source of Truth* (SSOT) bagi pengembang (developer), agen AI, penguji, maupun pengguna akhir.

## Lokasi Projek
- **Source Code Aplikasi**: `C:\xampp\htdocs\www\bankmini`
- **Pusat Dokumentasi**: `C:\xampp\htdocs\www\dokumentasi`

---

## Struktur Direktori Dokumentasi

| Folder | Deskripsi | Target Pembaca |
| :--- | :--- | :--- |
| [`01-panduan-pengguna/`](./01-panduan-pengguna/README.md) | Panduan operasional aplikasi berdasarkan hak akses (Admin, Teller/Petugas, Nasabah). | Pengguna Akhir, QA, Trainer |
| [`02-panduan-dev/`](./02-panduan-dev/README.md) | Panduan teknis instalasi, arsitektur PHP Native, skema database, dan standar keamanan finansial. | Developer, Reviewer, AI Agent |
| [`03-adr/`](./03-adr/README.md) | *Architecture Decision Records* — catatan resmi keputusan teknis dan arsitektur beserta alasannya. | Tech Lead, Developer, AI Agent |
| [`04-work-log/`](./04-work-log/README.md) | Log perkembangan pengerjaan harian/fitur, perubahan skema, dan *audit trail* pengembangan. | Seluruh Tim & AI Agent |
| [`05-template-dokumentasi/`](./05-template-dokumentasi/template-spesifikasi-fitur.md) | Kumpulan template standar untuk menjaga konsistensi penulisan dokumen baru. | Developer, Technical Writer, AI Agent |
| [`06-aturan-agen/`](./06-aturan-agen/README.md) | *Guardrails*, standar kode, dan SOP wajib yang harus dipatuhi oleh AI Coding Agent. | AI Coding Agent |

---

## Prinsip Utama Rekayasa Sistem Bank Mini
1. **Integritas Finansial (ACID)**: Setiap mutasi saldo (setor, tarik, transfer, biaya admin) **wajib** dibungkus dalam *Database Transaction* (`BEGIN`, `COMMIT`, `ROLLBACK`) dengan penguncian baris (`SELECT ... FOR UPDATE`).
2. **Keamanan Berlapis (Defense in Depth)**: Wajib menggunakan *PDO Prepared Statements*, proteksi CSRF pada setiap form mutasi, sanitasi output (`htmlspecialchars`), dan hashing password dengan `password_hash()`.
3. **Auditabilitas (Immutable Ledger)**: Tabel transaksi/mutasi bersifat *append-only* (tidak boleh ada `DELETE` atau `UPDATE` nominal transaksi yang sudah tercatat; koreksi dilakukan melalui jurnal balik/*reversal*).
4. **Dokumentasi Hidup (*Living Documentation*)**: Setiap penambahan fitur atau perubahan arsitektur di `bankmini` wajib diikuti pembaruan pada `04-work-log/` dan dokumen terkait.

