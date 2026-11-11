# ==============================================================================
# Skrip Onboarding Satu Klik Siswa RPL (siapkan-projek.ps1) - Projek Bank Mini
# Menyiapkan Git Hooks, Basis Data MySQL InnoDB, dan memverifikasi kesiapan
# lingkungan belajar hanya dengan 1 perintah.
# ==============================================================================
$ErrorActionPreference = "Stop"
$AkarProjek = "C:\xampp\htdocs\www\bankmini"
$BinaryPhp = if (Test-Path "C:\xampp\php\php.exe") { "C:\xampp\php\php.exe" } else { "php" }
$BinaryGit = if (Test-Path "C:\Progra~1\Git\cmd\git.exe") { "C:\Progra~1\Git\cmd\git.exe" } else { "git" }

Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "  ONBOARDING & PERSIAPAN LINGKUNGAN BELAJAR SISWA RPL - BANK MINI     " -ForegroundColor Cyan
Write-Host "======================================================================" -ForegroundColor Cyan

Push-Location $AkarProjek
try {
    Write-Host "`n[1/4] Mengaktifkan Git Hooks Pemblokir Commit/Push (.githooks)..." -ForegroundColor Cyan
    & $BinaryGit config core.hooksPath .githooks
    Write-Host "      -> core.hooksPath berhasil diarahkan ke .githooks" -ForegroundColor Green

    Write-Host "`n[2/4] Memasang Skema & Data Awal Basis Data MySQL (bankmini_db)..." -ForegroundColor Cyan
    & $BinaryPhp "basis_data\pasang_basis_data.php"
    if ($LASTEXITCODE -ne 0) {
        Write-Host "      -> Pastikan layanan MySQL di XAMPP Control Panel sudah dinyalakan (Start)!" -ForegroundColor Red
        exit $LASTEXITCODE
    }

    Write-Host "`n[3/4] Memeriksa Gerbang Handover & Prasyarat Runtime..." -ForegroundColor Cyan
    & $BinaryPhp "dokumentasi\06-aturan-agen\skrip\gerbang_handover_agen.php"
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }

    Write-Host "`n[4/4] Menjalankan Pemeriksaan Kepatuhan Kode & Dokumentasi..." -ForegroundColor Cyan
    & $BinaryPhp "dokumentasi\06-aturan-agen\skrip\periksa_kepatuhan_agen.php"
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
} finally {
    Pop-Location
}

Write-Host "`n======================================================================" -ForegroundColor Green
Write-Host "  [SIAP BELAJAR!] Lingkungan Bank Mini telah siap 100%." -ForegroundColor Green
Write-Host "  - URL Aplikasi : http://localhost/www/bankmini/public/masuk" -ForegroundColor White
Write-Host "  - Akun Uji     : admin / teller1 / supervisor1 (Kata Sandi: password)" -ForegroundColor White
Write-Host "  - Mulai Belajar: dokumentasi/02-panduan-dev/alur-belajar-dan-modul-praktikum-rpl.md" -ForegroundColor Yellow
Write-Host "======================================================================" -ForegroundColor Green
exit 0
