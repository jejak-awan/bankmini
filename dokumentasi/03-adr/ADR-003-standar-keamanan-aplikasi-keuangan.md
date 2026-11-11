# ADR-003: Standar Keamanan Aplikasi Keuangan (PDO Prepared Statements, CSRF + PRG, & Sanitasi XSS)

- **Status**: Disetujui (*Accepted*)
- **Tanggal**: 2026-11-11
- **Pengambil Keputusan**: Tim Pengembang & Arsitek Sistem

## 1. Konteks dan Permasalahan
Aplikasi keuangan **Bank Mini** mengelola data rekening dan mutasi dana Rupiah nasabah. Celah keamanan seperti SQL Injection, pengiriman ulang form transaksi akibat *refresh* browser (`F5`), Cross-Site Request Forgery (CSRF), maupun Cross-Site Scripting (XSS) dapat mengakibatkan kerugian finansial dan rusaknya integritas data.

## 2. Keputusan Arsitektur
1. **Wajib 100% PDO Prepared Statements (`EMULATE_PREPARES = false`)**:
   - Seluruh interaksi database wajib menggunakan PDO dengan opsi `PDO::ATTR_EMULATE_PREPARES => false` dan `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
   - Penggabungan variabel langsung (*string interpolation/concatenation*) pada query SQL dilarang di seluruh codebase.
2. **Proteksi Token CSRF + Pola Post-Redirect-Get (PRG)**:
   - Setiap form `POST` wajib menyertakan token CSRF kriptografis (`random_bytes(32)`) yang divalidasi di sisi server menggunakan `hash_equals()`.
   - Setiap aksi `POST` (terutama setor, tarik, transfer, koreksi) wajib diakhiri dengan **HTTP Redirect (`302`/`303`)** ke halaman `GET` (pola **Post-Redirect-Get / PRG**) serta rotasi token sekali pakai (*one-time token*) agar penekanan tombol `F5` / *Refresh* tidak menduplikasi transaksi.
3. **Sanitasi Output XSS (`htmlspecialchars`)**:
   - Seluruh output variabel ke dalam template HTML (`aplikasi/Tampilan/`) wajib melewati fungsi helper `amankanHtml()` yang membungkus `htmlspecialchars((string) $nilai, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
4. **Hardening Autentikasi & Sesi**:
   - Kata sandi di-hash menggunakan `password_hash(..., PASSWORD_BCRYPT)` pada kolom `hash_kata_sandi`.
   - ID sesi wajib diregenerasi (`session_regenerate_id(true)`) saat pengguna berhasil masuk, dengan cookie `HttpOnly` dan `SameSite=Lax`.

## 3. Konsekuensi
- **Positif**: Menutup celah SQL Injection, mencegah duplikasi transaksi akibat *refresh* halaman, serta melindungi sesi petugas/nasabah dari pembajakan skrip (XSS/CSRF).
- **Negatif**: Setiap form HTML wajib menyertakan helper `TokenCsrf` dan setiap `Pengendali` `POST` wajib mengikuti alur pengalihan PRG.

