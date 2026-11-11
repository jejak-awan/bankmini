# ==============================================================================
# Skrip PowerShell Penjaga Konsistensi & Pengujian QA - Projek Bank Mini
# ==============================================================================
$ErrorActionPreference = "Stop"

$JalurSkripPhp = Join-Path $PSScriptRoot "periksa_kepatuhan_agen.php"
$JalurRunnerQa = "C:\xampp\htdocs\www\bankmini\pengujian\jalankan_semua_tes.php"

# Cari binary PHP (di PATH atau di C:\xampp\php\php.exe)
$BinaryPhp = "php"
if (-not (Get-Command $BinaryPhp -ErrorAction SilentlyContinue)) {
    if (Test-Path "C:\xampp\php\php.exe") {
        $BinaryPhp = "C:\xampp\php\php.exe"
    } else {
        Write-Error "Binary PHP tidak ditemukan di PATH maupun di C:\xampp\php\php.exe."
        exit 1
    }
}

Write-Host "Menjalankan Pemeriksa Kepatuhan Aturan Agen..." -ForegroundColor Cyan
& $BinaryPhp $JalurSkripPhp
$KodeKeluarKepatuhan = $LASTEXITCODE

if ($KodeKeluarKepatuhan -ne 0) {
    Write-Host "`nPemeriksaan kepatuhan GAGAL. Harap perbaiki pelanggaran di atas sebelum lanjut!" -ForegroundColor Red
    exit $KodeKeluarKepatuhan
}

# Jika Test Runner QA sudah ada di bankmini/pengujian/jalankan_semua_tes.php, jalankan otomatis
if (Test-Path $JalurRunnerQa) {
    Write-Host "`nMenjalankan Suite Pengujian Otomatis QA..." -ForegroundColor Cyan
    & $BinaryPhp $JalurRunnerQa
    $KodeKeluarQa = $LASTEXITCODE
    if ($KodeKeluarQa -ne 0) {
        Write-Host "`nPengujian QA GAGAL. Harap perbaiki tes yang gagal!" -ForegroundColor Red
        exit $KodeKeluarQa
    }
}

exit 0
