# Glosarium & Daftar Istilah Teknis, Kamus Data, dan Fundamental RPL

Dokumen ini merupakan **kamus referensi istilah lengkap** bagi siswa **Rekayasa Perangkat Lunak (RPL)**, pengembang, penguji QA, maupun pengguna dalam memahami berbagai istilah fundamental, teknis, arsitektur, basis data, perbankan, serta keamanan yang digunakan di dalam projek **Sistem Informasi Bank Mini (PHP Native)**.

---

## 1. Istilah Fundamental Rekayasa Perangkat Lunak (RPL) & Arsitektur

| Istilah | Kepanjangan / Asal | Penjelasan Lengkap & Penerapan di Bank Mini |
| :--- | :--- | :--- |
| **PHP Native** | PHP Murni (Tanpa Framework Eksternal) | Pengembangan aplikasi menggunakan bahasa PHP murni tanpa framework seperti Laravel/CodeIgniter, namun tetap menerapkan struktur arsitektur modern agar siswa RPL memahami pondasi cara kerja web dari dasar. |
| **SoT / SSOT** | *Single Source of Truth* | Prinsip menetapkan **satu sumber kebenaran tunggal** untuk setiap data atau aturan agar tidak terjadi duplikasi dan inkonsistensi. Contoh: file `VERSION` adalah SoT versi aplikasi; tabel `mutasi_transaksi` adalah SoT riwayat saldo; `BasisData::ambilKoneksi()` adalah SoT koneksi PDO. |
| **SoC** | *Separation of Concerns* | Prinsip memisahkan kode program ke dalam lapisan-lapisan terpisah berdasarkan tanggung jawabnya (`Pengendali -> Layanan -> Model -> Tampilan`) sehingga kode SQL, logika bisnis, dan HTML tidak bercampur (*spaghetti code*). |
| **Spaghetti Code** | Kode Kusut | Istilah untuk kode yang tidak terstruktur—misalnya mencampur koneksi database, query SQL, perhitungan saldo, dan tag HTML dalam satu file (`setor.php`). Arsitektur Bank Mini dirancang khusus untuk menghindari hal ini. |
| **Layered MVC-Lite + Service Layer** | *Model - View - Controller* + *Service* | Pola arsitektur aplikasi Bank Mini: **Pengendali (Controller)** menangani HTTP/input, **Layanan (Service)** menjalankan aturan bisnis & transaksi ACID, **Model** mengeksekusi query database, dan **Tampilan (View)** merender HTML. |
| **Front Controller** | Pengendali Depan Tunggal | Pola desain di mana seluruh permintaan HTTP masuk melalui **satu file tunggal** (`public/index.php`) untuk inisialisasi sesi, autoloading, dan perutean (*routing*). |
| **Router (`Perute`)** | Pengarah Rute URL | Komponen di `aplikasi/Inti/Perute.php` yang memetakan alamat URL dan metode HTTP (`GET`/`POST`) ke class `Pengendali` dan method aksi yang tepat. |
| **Namespace & PSR-4 Autoloading** | *PHP Standards Recommendation 4* | Sistem pengelompokan nama class secara hierarkis (contoh: `Aplikasi\Layanan\LayananTransaksi`) agar PHP dapat memuat file class secara otomatis (`spl_autoload_register`) sesuai lokasi foldernya. |
| **PSR-12** | *PHP Standards Recommendation 12* | Standar resmi gaya penulisan kode PHP (indentasi 4 spasi, penamaan class `PascalCase`, method/variabel `camelCase`, konstanta `UPPER_SNAKE_CASE`). |
| **Strict Types** | `declare(strict_types=1);` | Instruksi di baris awal file PHP agar PHP memeriksa tipe data parameter dan nilai kembalian secara ketat (misal `int` tidak otomatis dianggap sama dengan `string` tanpa konversi eksplisit). |
| **Singleton Pattern** | Pola Desain Instansi Tunggal | Pola pemrograman yang memastikan sebuah class hanya memiliki **satu objek (instance)** aktif selama aplikasi berjalan. Dipakai di `BasisData` agar semua Model dan Layanan berbagi satu koneksi PDO yang sama saat transaksi `beginTransaction()`. |
| **Stateless / Pure Function** | Fungsi Murni Tanpa State | Fungsi yang hasilnya hanya bergantung pada input parameternya dan tidak mengubah database atau sesi. Contoh: `formatRupiah()` dan `amankanHtml()` di `aplikasi/Pembantu/fungsi_umum.php`. |
| **PHPDoc & `@konsep_rpl`** | Dokumentasi Blok Kode PHP | Komentar terstruktur (`/** ... */`) di atas class dan method yang menjelaskan tujuan, `@param`, `@return`, `@throws`, serta tag edukasi `@konsep_rpl` untuk bahan belajar siswa RPL. |
| **ADR** | *Architecture Decision Record* | Dokumen singkat yang mencatat keputusan teknis/arsitektur penting beserta alasan dan konsekuensinya (tersimpan di `dokumentasi/03-adr/`). |
| **Living Documentation** | Dokumentasi Hidup | Prinsip bahwa dokumentasi projek harus selalu diperbarui seiring perubahan kode (tidak dibiarkan usang), dijaga melalui `04-work-log/` dan skrip penjaga konsistensi. |

---

## 2. Istilah Basis Data (Database) & Kamus Data

| Istilah | Kepanjangan / Konteks | Penjelasan Lengkap & Penerapan di Bank Mini |
| :--- | :--- | :--- |
| **RDBMS** | *Relational Database Management System* | Sistem manajemen basis data berbasis relasi antar tabel (di projek ini menggunakan **MySQL / MariaDB**). |
| **InnoDB** | Mesin Penyimpanan (*Storage Engine*) MySQL | Mesin penyimpanan default yang **wajib** dipakai pada sistem perbankan karena mendukung **Transaksi ACID**, *Foreign Key*, dan penguncian baris (*Row-Level Locking*). |
| **DDL** | *Data Definition Language* | Perintah SQL untuk mendefinisikan struktur tabel (`CREATE TABLE`, `ALTER TABLE`, `DROP TABLE`), tersimpan di `basis_data/skema.sql`. |
| **DML** | *Data Manipulation Language* | Perintah SQL untuk memanipulasi isi data (`SELECT`, `INSERT`, `UPDATE`, `DELETE`), dijalankan melalui `aplikasi/Model/`. |
| **Entitas (*Entity*)** | Objek Dunia Nyata di Database | Objek yang datanya disimpan dalam bentuk tabel. Di Bank Mini terdapat 6 entitas: `pengguna`, `nasabah`, `produk_tabungan`, `rekening`, `mutasi_transaksi`, dan `log_audit`. |
| **Atribut (*Attribute*)** | Kolom / Field Tabel | Properti atau karakteristik yang menjelaskan sebuah entitas (contoh: entitas `nasabah` memiliki atribut `nomor_identitas`, `nama_lengkap`, `kelas_atau_unit`). |
| **Kamus Data (*Data Dictionary*)** | Spesifikasi Metadata Tabel | Katalog resmi yang mendefinisikan nama kolom, tipe data, panjang karakter, batasan (*constraint*), dan arti bisnis setiap atribut tabel (lihat [`skema-database.md`](./skema-database.md)). |
| **PK** | *Primary Key* (Kunci Utama) | Kolom identitas unik untuk membedakan setiap baris di dalam tabel (pada projek ini seluruh tabel memakai kolom `id` dengan `AUTO_INCREMENT`). |
| **FK** | *Foreign Key* (Kunci Asing) | Kolom yang merujuk ke `Primary Key` tabel lain untuk menjaga integritas relasi (contoh: `rekening.id_nasabah` merujuk ke `nasabah.id`). |
| **Self-Referencing FK** | Kunci Asing ke Diri Sendiri | Kolom FK yang merujuk ke PK di tabel yang sama. Contoh: `mutasi_transaksi.id_referensi_transaksi` merujuk ke `mutasi_transaksi.id` untuk menautkan transaksi `KOREKSI` dengan transaksi asalnya. |
| **UK** | *Unique Key* (Kunci Unik) | Batasan yang melarang adanya dua baris dengan nilai yang sama pada kolom tersebut (contoh: `nomor_rekening`, `kode_referensi`, `nama_pengguna`). |
| **UNSIGNED** | Bilangan Bulat Non-Negatif | Pengubah tipe angka (`INT UNSIGNED`, `BIGINT UNSIGNED`) agar kolom hanya menyimpan angka positif `>= 0` (karena `id` tidak pernah bernilai negatif), sekaligus menggandakan kapasitas nilai positifnya. |
| **DECIMAL(15,2)** | Tipe Data Angka Desimal Presisi | Tipe data wajib untuk menyimpan uang **Rupiah (`IDR`)** dengan total 15 digit dan 2 digit di belakang koma secara **eksak/akurat**. Dilarang memakai `FLOAT`/`DOUBLE` karena galat pembulatan biner. |
| **ENUM** | *Enumerated Type* | Tipe data yang membatasi isi kolom hanya pada daftar nilai tertentu yang sudah ditetapkan (contoh: `status` hanya boleh `'aktif'` atau `'nonaktif'`). |
| **JSON** | *JavaScript Object Notation* | Format pertukaran data terstruktur pasangan *key-value* (dipakai pada kolom `data_lama` dan `data_baru` di tabel `log_audit` untuk merekam perubahan data sebelum dan sesudah). |
| **NULL / NULLABLE** | Nilai Kosong (Tidak Diisi) | Menandakan kolom boleh tidak memiliki nilai (`DEFAULT NULL`), berbeda dengan angka `0` atau teks kosong `''`. |
| **Kardinalitas (`1:1`, `1:N`, `N:M`)** | Derajat Relasi Antar Entitas | Menunjukkan jumlah maksimum baris yang saling terhubung antar tabel. Contoh: `nasabah` ke `rekening` berelasi `1:N` (*One-to-Many* — satu nasabah boleh punya banyak rekening). |
| **Normalisasi (`1NF`, `2NF`, `3NF`)** | Perancangan Tabel Bebas Redundansi | Proses memecah tabel agar data tidak berulang-ulang dan bebas anomali (*update/insert/delete anomaly*). Skema Bank Mini memenuhi hingga **Bentuk Normal Ketiga (3NF)**. |
| **Cached Balance** | *Controlled Denormalization* | Penyimpanan hasil hitungan saldo terkini di kolom `rekening.saldo` agar pengecekan saldo berlangsung cepat tanpa `SUM()` seluruh riwayat transaksi, namun tetap dijaga konsistensinya lewat transaksi ACID. |
| **Index (`INDEX`)** | Indeks Pencarian Cepat | Struktur data internal database (B-Tree) untuk mempercepat query `WHERE` dan `ORDER BY` (contoh: `idx_mutasi_rekening_waktu` pada `mutasi_transaksi`). |
| **ERD** | *Entity Relationship Diagram* | Diagram visual yang memodelkan tabel-tabel database, atributnya, serta hubungan/kardinalitas antar tabel. |
| **DFD** | *Data Flow Diagram* | Diagram yang memodelkan aliran data dari **Entitas Eksternal (Terminator)**, masuk ke **Proses**, dan disimpan ke **Data Store** (Level 0/Konteks, Level 1, Level 2). |
| **Sequence Diagram & Flowchart** | Diagram Urutan & Alur Algoritma | Diagram yang menggambarkan urutan panggilan antar objek/lapisan (`Sequence Diagram`) dan alur percabangan logika `if/else` langkah demi langkah (`Flowchart`). |

---

## 3. Istilah Transaksi Keuangan & Perbankan Bank Mini (Rupiah `IDR`)

| Istilah | Penjelasan Lengkap & Penerapan di Bank Mini |
| :--- | :--- |
| **ACID** | Singkatan dari **Atomicity, Consistency, Isolation, Durability** — empat sifat wajib transaksi database perbankan agar data uang tidak pernah rusak atau setengah tersimpan. |
| **Database Transaction** | Rangkaian perintah SQL yang dibungkus `beginTransaction()` ... `commit()` (simpan semua jika sukses) atau `rollBack()` (batalkan semua jika ada error di tengah jalan). |
| **Row-Level Locking (`SELECT ... FOR UPDATE`)** | Teknik **Penguncian Baris Pesimistis** (*Pessimistic Locking*) untuk mengunci satu baris `rekening` selama transaksi berlangsung agar tidak bisa diubah secara bersamaan oleh proses lain. |
| **Race Condition & Double Spending** | Kondisi cacat sistem akibat dua proses berjalan serentak (misal dua teller menarik saldo rekening yang sama pada milidetik yang sama), sehingga total penarikan melebihi saldo yang seharusnya. Dicegah dengan `SELECT ... FOR UPDATE`. |
| **Deadlock** | Kondisi dua transaksi saling menunggu kunci baris yang dipegang satu sama lain sehingga macet permanen. Dicegah di `LayananTransaksi::prosesTransferAntarRekening()` dengan selalu mengunci `rekening.id` terkecil lebih dulu (`min()` lalu `max()`). |
| **Immutable / Append-Only Ledger** | Buku besar transaksi (`mutasi_transaksi`) yang **hanya boleh ditambah (`INSERT`)** dan **dilarang keras dihapus (`DELETE`) atau diubah nominalnya (`UPDATE`)** demi jejak audit yang jujur. |
| **KREDIT vs DEBIT (Tabungan)** | Dalam pencatatan rekening tabungan nasabah: **`KREDIT`** berarti transaksi yang **menambah** saldo tabungan nasabah (Setor Tunai, Transfer Masuk); **`DEBIT`** berarti transaksi yang **mengurangi** saldo tabungan nasabah (Tarik Tunai, Transfer Keluar, Biaya Admin). |
| **Setoran Awal Minimum** | Jumlah minimal uang Rupiah yang harus disetorkan nasabah pertama kali saat membuka rekening baru pada produk tabungan tertentu. |
| **Saldo Mengendap Minimum** | Batas saldo terendah dalam Rupiah yang **wajib tersisa** di dalam rekening dan tidak boleh ditarik selama rekening masih aktif. |
| **Saldo Efektif** | Jumlah maksimal uang Rupiah yang benar-benar boleh ditarik atau ditransfer oleh nasabah saat itu, dihitung dengan rumus: $\text{Saldo Efektif} = \text{Saldo Saat Ini} - \text{Saldo Mengendap Minimum}$. |
| **Jurnal Koreksi (*Reversal*)** | Mekanisme resmi pembetulan transaksi yang salah input dengan cara membuat transaksi baru bertipe `KOREKSI` yang membalikkan arah mutasi (`KREDIT` dibalik `DEBIT`, atau sebaliknya) dan merujuk ke `id_referensi_transaksi` asal. |
| **Kode Referensi Transaksi** | Nomor bukti transaksi unik pada setiap baris mutasi dan struk cetak (contoh: `STR-20261111-104829` untuk Setor, `TRK-...` untuk Tarik, `KOR-...` untuk Koreksi). |
| **BCMath (`bcadd`, `bcsub`, `bccomp`)** | Ekstensi matematika presisi tinggi di PHP untuk menjumlahkan, mengurangi, dan membandingkan string angka desimal Rupiah tanpa kehilangan akurasi seperti tipe `float`. |
| **IDR (Rupiah)** | Kode standar internasional (ISO 4217) untuk mata uang tunggal **Rupiah** yang ditetapkan di seluruh sistem Bank Mini. |

---

## 4. Istilah Keamanan Aplikasi Web (*Web Security*)

| Istilah | Penjelasan Lengkap & Penerapan di Bank Mini |
| :--- | :--- |
| **SQL Injection (SQLi)** | Serangan dengan menyisipkan sintaks SQL berbahaya lewat input pengguna untuk membobol atau merusak database. Dicegah 100% dengan **PDO Prepared Statements** (`EMULATE_PREPARES = false`). |
| **PDO Prepared Statements** | Mekanisme mengirim kerangka query SQL dengan placeholder (`:id_rekening`) secara terpisah dari data nilainya, sehingga input pengguna tidak pernah dieksekusi sebagai perintah SQL. |
| **XSS (*Cross-Site Scripting*)** | Serangan menyuntikkan skrip JavaScript jahat (`<script>...</script>`) ke input agar dieksekusi di browser pengguna lain. Dicegah dengan membungkus seluruh output HTML memakai `amankanHtml()` (`htmlspecialchars()`). |
| **CSRF (*Cross-Site Request Forgery*)** | Serangan yang memaksa browser pengguna yang sedang login untuk mengirim aksi `POST` tanpa sepengetahuannya dari situs lain. Dicegah dengan **Token CSRF** rahasia di setiap form dan diverifikasi dengan `hash_equals()`. |
| **Timing Attack & `hash_equals()`** | Serangan menebak token rahasia berdasarkan selisih waktu respons perbandingan string biasa (`===`). Fungsi `hash_equals()` membandingkan dua string dalam waktu konstan sehingga kebal *Timing Attack*. |
| **Pola PRG (*Post-Redirect-Get*)** | Alur standar memproses form: `POST` (proses data) -> `Redirect HTTP 303` -> `GET` (tampilkan halaman hasil + pesan kilat). Mencegah pengiriman transaksi ganda saat pengguna menekan tombol *Refresh (`F5`)*. |
| **Session Hardening & HttpOnly** | Pengamanan cookie sesi PHP dengan atribut `httponly = true` (tidak bisa dibaca JavaScript) dan `samesite = 'Lax'` melalui `Sesi::mulaiSesiAman()`. |
| **Session Fixation** | Serangan pembajakan sesi dengan memaksa korban memakai ID sesi yang sudah diketahui penyerang. Dicegah dengan memanggil `session_regenerate_id(true)` saat login berhasil. |
| **Hashing Kata Sandi (BCrypt)** | Mengubah kata sandi teks biasa menjadi sidik jari kriptografis satu arah (`password_hash(..., PASSWORD_BCRYPT)`) yang tidak bisa dikembalikan ke teks asli dan hanya diverifikasi menggunakan `password_verify()`. |
| **Autentikasi vs Otorisasi (RBAC)** | **Autentikasi** memverifikasi *siapa Anda* (login); **Otorisasi / RBAC (*Role-Based Access Control*)** memverifikasi *hak akses peran Anda* (`admin`, `teller`, `supervisor`). |
| **Audit Trail (`log_audit`)** | Catatan kronologis seluruh aktivitas penting sistem (siapa pelakunya, kapan, dari IP mana, serta data sebelum dan sesudah) untuk investigasi keamanan. |
| **CSP (*Content-Security-Policy*)** | Header keamanan HTTP yang memberi tahu browser sumber skrip mana yang sah dieksekusi (`script-src 'self'`), memblokir skrip injeksi inline (XSS). |
| **Rate Limiting & Brute-Force** | Pembatasan jumlah percobaan gagal login per jangka waktu (`PembatasLaju`: maks 5 kali dalam 5 menit) untuk menggagalkan serangan tebak kata sandi otomatis (*Brute-Force*). |
| **Idle Timeout** | Batas waktu diam maksimum (30 menit / 1800 detik di `Sesi`) sebelum sesi pengguna diakhiri otomatis demi mencegah penyalahgunaan komputer teller yang ditinggalkan terbuka. |
| **Logging Harian & Secret Masking** | Pencatatan error dan aktivitas ke berkas harian terotasi (`penyimpanan/log/aplikasi-YYYY-MM-DD.log`) oleh `PencatatLog` dengan menyensor field rahasia menjadi `[DIRAHASIAKAN]`. |
| **Correlation ID** | Kode unik per siklus permintaan HTTP (`req-YYYYMMDD-...`) yang tampil di halaman galat dan tercatat di log server untuk mencocokkan laporan pengguna dengan bukti teknis. |
| **Maintenance Mode (`503`)** | Mode pemeliharaan sementara (aktif saat file `penyimpanan/pemeliharaan.flag` ada) yang menampilkan halaman HTTP `503 Service Unavailable` secara tertib saat pembaruan server. |
| **SARGable Query** | *Search Argument Able* — Penulisan klausa `WHERE` tanpa membungkus kolom terindeks dengan fungsi SQL (memakai `>= 00:00:00 AND <= 23:59:59`, bukan `DATE(kolom)`) agar indeks B-Tree MySQL bekerja optimal. |
| **Cache-Busting & Gzip Compression** | Penambahan parameter versi `?v=X.Y.Z` pada URL CSS/JS agar browser langsung memuat aset terbaru saat aplikasi naik versi, dikombinasikan dengan kompresi `mod_deflate` Apache. |
| **Health Check Endpoint (`/kesehatan`)** | Alamat khusus (`GET /kesehatan`) yang mengembalikan status JSON kesiapan PHP, koneksi InnoDB, dan disk log untuk pemantauan otomatis (*uptime monitoring* & Docker `HEALTHCHECK`). |

---

## 5. Istilah Pengujian (QA), Manajemen Rilis, & Git

| Istilah | Penjelasan Lengkap & Penerapan di Bank Mini |
| :--- | :--- |
| **QA (*Quality Assurance*)** | Proses penjaminan kualitas perangkat lunak untuk memastikan fitur bekerja sesuai aturan bisnis dan bebas bug sebelum diserahkan ke pengguna. |
| **Unit Test vs Integration Test** | **Unit Test** menguji satu fungsi/komponen kecil secara terpisah (contoh: `TesFungsiUmum`); **Integration Test** menguji kerja sama antar komponen seperti `Layanan` + `Model` + transaksi (contoh: `TesLayananTransaksi`). |
| **Assertion (`pastikanSama`)** | Pernyataan pengecekan di dalam kode tes otomatis untuk membandingkan hasil aktual program dengan hasil yang diharapkan. |
| **Mock / Tiruan (*Fake*)** | Objek tiruan di memori (contoh: `PdoTiruanTransaksi`, `ModelRekeningMemoriUji`) untuk menyimulasikan database atau layanan saat menjalankan tes otomatis secara cepat dan terisolasi. |
| **Happy Path vs Edge Case** | **Happy Path** adalah skenario pengujian dengan input normal/valid; **Edge Case / Negative Case** adalah skenario batas ekstrem atau input salah (contoh: penarikan Rp 0, negatif, atau melebihi saldo efektif). |
| **Idempotent** | Sifat operasi yang jika dijalankan berulang kali dengan parameter sama tidak menimbulkan efek samping perubahan data ganda (contoh: pencarian laporan dengan metode `GET`). |
| **SemVer vs CalVer** | **SemVer (`MAJOR.MINOR.PATCH`)** menomori versi berdasarkan dampak kode (perubahan besar/fitur baru/perbaikan bug); **CalVer (`YYYY.MM.MICRO`)** menomori versi berdasarkan tanggal kalender rilis. |
| **Bump Version** | Tindakan menaikkan nomor versi rilis secara resmi pada file `VERSION` dan `CHANGELOG.md` (diotomatiskan lewat skrip `naikkan_versi.php`). |
| **MIT License** | Lisensi perangkat lunak *open-source* yang mengizinkan siapa saja mempelajari, memodifikasi, dan mendistribusikan kode selama tetap menyertakan catatan hak cipta asli (`LICENSE`). |
| **Repository, Commit, Branch, Remote** | **Repository** adalah ruang penyimpanan projek Git; **Commit** adalah titik simpan riwayat perubahan kode; **Branch (`main`)** adalah jalur pengembangan; **Remote (`origin`)** adalah alamat repositori di server GitHub. |
| **`.gitignore`** | File konfigurasi yang memberi tahu Git daftar file/folder yang tidak boleh ikut diunggah ke GitHub (seperti file `.env`, kredensial lokal, log runtime, dan file sementara OS). |
| **Entry Gate & Exit/Handover Gate** | Gerbang kontrol kualitas wajib bagi AI Agent: **Entry Gate** (`gerbang_handover_agen.php`) dijalankan saat memulai sesi agar memahami konteks terakhir; **Exit Gate** (`periksa_kepatuhan_agen.php` + `jalankan_semua_tes.php`) dijalankan sebelum menyelesaikan tugas. |

