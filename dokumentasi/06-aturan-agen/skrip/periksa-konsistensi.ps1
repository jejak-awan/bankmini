# ==============================================================================
# Skrip PowerShell Penjaga Konsistensi & Quality Gate Lengkap - Projek Bank Mini
# Memastikan Kebijakan Zero Error & Zero Warning sebelum Commit maupun Push.
# ==============================================================================
param(
    [switch]$DenganE2E
)

$ErrorActionPreference = "Stop"

$AkarProjek = "C:\xampp\htdocs\www\bankmini"
$JalurSkripPhp = Join-Path $PSScriptRoot "periksa_kepatuhan_agen.php"
$JalurRunnerQa = Join-Path $AkarProjek "pengujian\jalankan_semua_tes.php"
$JalurPhpcs = Join-Path $AkarProjek "vendor\squizlabs\php_codesniffer\bin\phpcs"
$JalurPhpcsConfig = Join-Path $AkarProjek "phpcs.xml"
$JalurPhpstan = Join-Path $AkarProjek "vendor\phpstan\phpstan\phpstan"
$JalurPhpstanConfig = Join-Path $AkarProjek "phpstan.neon"
$JalurPlaywright = Join-Path $AkarProjek "node_modules\.bin\playwright.cmd"

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

$TotalLangkah = if ($DenganE2E) { 5 } else { 4 }

Write-Host "[1/$TotalLangkah] Menjalankan Pemeriksa Kepatuhan Aturan Agen..." -ForegroundColor Cyan
& $BinaryPhp $JalurSkripPhp
if ($LASTEXITCODE -ne 0) {
    Write-Host "`nPemeriksaan kepatuhan GAGAL. Harap perbaiki pelanggaran di atas sebelum lanjut!" -ForegroundColor Red
    exit $LASTEXITCODE
}

if (Test-Path $JalurRunnerQa) {
    Write-Host "`n[2/$TotalLangkah] Menjalankan Suite Pengujian Otomatis QA PHP (Zero Warning)..." -ForegroundColor Cyan
    & $BinaryPhp $JalurRunnerQa
    if ($LASTEXITCODE -ne 0) {
        Write-Host "`nPengujian QA GAGAL. Harap perbaiki tes atau PHP warning!" -ForegroundColor Red
        exit $LASTEXITCODE
    }
}

if ((Test-Path $JalurPhpcs) -and (Test-Path $JalurPhpcsConfig)) {
    Write-Host "`n[3/$TotalLangkah] Menjalankan Lint PSR-12 PHPCS (Zero Error & Zero Warning)..." -ForegroundColor Cyan
    & $BinaryPhp $JalurPhpcs "--standard=$JalurPhpcsConfig" "--warning-severity=1"
    if ($LASTEXITCODE -ne 0) {
        Write-Host "`nLint PHPCS GAGAL (ditemukan error atau warning). Jalankan 'composer lint:fix' atau perbaiki manual!" -ForegroundColor Red
        exit $LASTEXITCODE
    }
}

if ((Test-Path $JalurPhpstan) -and (Test-Path $JalurPhpstanConfig)) {
    Write-Host "`n[4/$TotalLangkah] Menjalankan Analisis Statis PHPStan (Level Aktif di phpstan.neon)..." -ForegroundColor Cyan
    Push-Location $AkarProjek
    & $BinaryPhp $JalurPhpstan analyse "-c" $JalurPhpstanConfig "--memory-limit=512M"
    $KodeStan = $LASTEXITCODE
    Pop-Location
    if ($KodeStan -ne 0) {
        Write-Host "`nAnalisis PHPStan GAGAL!" -ForegroundColor Red
        exit $KodeStan
    }
}

if ($DenganE2E) {
    if (-not (Test-Path $JalurPlaywright)) {
        Write-Host "`nPlaywright belum terpasang di node_modules. Jalankan 'npm install' terlebih dahulu!" -ForegroundColor Red
        exit 1
    }
    Write-Host "`n[5/$TotalLangkah] Menjalankan Playwright Smoke Test & E2E (Zero Console Error/Warning)..." -ForegroundColor Cyan
    $JalurNodeUser = Join-Path $env:LOCALAPPDATA "Programs\nodejs"
    $env:Path = "$JalurNodeUser;C:\xampp\php;" + $env:Path
    Push-Location $AkarProjek
    & $JalurPlaywright test
    $KodeE2e = $LASTEXITCODE
    Pop-Location
    if ($KodeE2e -ne 0) {
        Write-Host "`nPlaywright E2E GAGAL! Perbaiki bugs tampilan/alur transaksi sebelum commit & push." -ForegroundColor Red
        exit $KodeE2e
    }
}

Write-Host "`nSeluruh Quality Gate LULUS 100% (0 Error & 0 Warning)!" -ForegroundColor Green
if (-not $DenganE2E) {
    Write-Host "Catatan: Untuk memvalidasi uji browser E2E, jalankan dengan flag -DenganE2E atau 'npm run test:e2e'." -ForegroundColor Yellow
}
exit 0
