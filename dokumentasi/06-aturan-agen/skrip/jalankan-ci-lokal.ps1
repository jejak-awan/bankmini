# ==============================================================================
# Pipeline CI/CD Lokal (Docker / Native Dual-Engine) - Projek Bank Mini
# Menjalankan seluruh tahapan Continuous Integration secara lokal di komputer
# pengembang/sekolah tanpa memakai kuota menit GitHub Actions Runner (0 Cloud Cost).
# ==============================================================================
param(
    [ValidateSet("otomatis", "docker", "native")]
    [string]$Mode = "otomatis",
    [switch]$TanpaE2E
)

$ErrorActionPreference = "Stop"
$WaktuMulai = Get-Date
$AkarProjek = "C:\xampp\htdocs\www\bankmini"
$JalurKonsistensi = Join-Path $PSScriptRoot "periksa-konsistensi.ps1"
$JalurPasangDb = Join-Path $AkarProjek "basis_data\pasang_basis_data.php"

Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "  PIPELINE CI/CD LOKAL BANK MINI (0 KUOTA GITHUB RUNNER)              " -ForegroundColor Cyan
Write-Host "======================================================================" -ForegroundColor Cyan

# Deteksi apakah Docker Engine aktif apabila mode = otomatis / docker
$GunakanDocker = $false
if ($Mode -eq "docker" -or $Mode -eq "otomatis") {
    if (Get-Command "docker" -ErrorAction SilentlyContinue) {
        try {
            $null = & docker info 2>$null
            if ($LASTEXITCODE -eq 0) {
                $GunakanDocker = $true
            }
        } catch {
            $GunakanDocker = $false
        }
    }
    if ($Mode -eq "docker" -and -not $GunakanDocker) {
        Write-Host "[GAGAL] Mode 'docker' dipilih tetapi Docker Engine belum aktif atau belum terpasang." -ForegroundColor Red
        exit 1
    }
}

if ($GunakanDocker) {
    Write-Host "[MODE CI/CD]: DOCKER CONTAINER PIPELINE (docker-compose.ci.yml)" -ForegroundColor Yellow
    Push-Location $AkarProjek
    try {
        Write-Host "`n[STAGE 1/4] Membangun & Menyalakan Stack Container Isolasi (MySQL 8.0 + PHP 8.2)..." -ForegroundColor Cyan
        & docker compose -f docker-compose.ci.yml up -d --build
        if ($LASTEXITCODE -ne 0) { throw "Gagal menyalakan container CI Docker." }

        Write-Host "`n[STAGE 2/4] Migrasi & Seeding Basis Data InnoDB di Container..." -ForegroundColor Cyan
        & docker compose -f docker-compose.ci.yml exec -T ci_aplikasi php basis_data/pasang_basis_data.php
        if ($LASTEXITCODE -ne 0) { throw "Gagal menjalankan pasang_basis_data.php di container." }

        Write-Host "`n[STAGE 3/4] Menjalankan Kepatuhan, Unit/Integrasi QA, PHPCS (0 Warning), & PHPStan Level 8..." -ForegroundColor Cyan
        & docker compose -f docker-compose.ci.yml exec -T ci_aplikasi php dokumentasi/06-aturan-agen/skrip/periksa_kepatuhan_agen.php
        if ($LASTEXITCODE -ne 0) { throw "Pemeriksaan kepatuhan gagal di container." }

        & docker compose -f docker-compose.ci.yml exec -T ci_aplikasi php pengujian/jalankan_semua_tes.php
        if ($LASTEXITCODE -ne 0) { throw "Suite pengujian QA gagal di container." }

        & docker compose -f docker-compose.ci.yml exec -T ci_aplikasi php vendor/squizlabs/php_codesniffer/bin/phpcs --standard=phpcs.xml --warning-severity=1
        if ($LASTEXITCODE -ne 0) { throw "Lint PSR-12 PHPCS gagal di container." }

        & docker compose -f docker-compose.ci.yml exec -T ci_aplikasi php vendor/phpstan/phpstan/phpstan analyse -c phpstan.neon --memory-limit=512M
        if ($LASTEXITCODE -ne 0) { throw "Analisis statis PHPStan Level 8 gagal di container." }

        if (-not $TanpaE2E) {
            Write-Host "`n[STAGE 4/4] Menjalankan Playwright Smoke & E2E Browser Test..." -ForegroundColor Cyan
            $JalurNodeUser = Join-Path $env:LOCALAPPDATA "Programs\nodejs"
            $env:Path = "$JalurNodeUser;C:\xampp\php;" + $env:Path
            & "$AkarProjek\node_modules\.bin\playwright.cmd" test
            if ($LASTEXITCODE -ne 0) { throw "Playwright E2E gagal." }
        }
    } finally {
        Write-Host "`n[CLEANUP] Mematikan & membersihkan container CI sementara..." -ForegroundColor DarkGray
        & docker compose -f docker-compose.ci.yml down -v
        Pop-Location
    }
} else {
    Write-Host "[MODE CI/CD]: NATIVE LOCAL PIPELINE (XAMPP PHP + MySQL + Playwright)" -ForegroundColor Yellow

    $BinaryPhp = if (Test-Path "C:\xampp\php\php.exe") { "C:\xampp\php\php.exe" } else { "php" }

    Write-Host "`n[STAGE 1/2] Menyiapkan & Memverifikasi Skema Basis Data MySQL InnoDB..." -ForegroundColor Cyan
    & $BinaryPhp $JalurPasangDb
    if ($LASTEXITCODE -ne 0) {
        Write-Host "`n[GAGAL] Instalasi/verifikasi basis data gagal! Pastikan MySQL XAMPP aktif." -ForegroundColor Red
        exit $LASTEXITCODE
    }

    Write-Host "`n[STAGE 2/2] Menjalankan 5 Gerbang Quality Assurance (0 Error & 0 Warning)..." -ForegroundColor Cyan
    if ($TanpaE2E) {
        & powershell -NoProfile -ExecutionPolicy Bypass -File $JalurKonsistensi
    } else {
        & powershell -NoProfile -ExecutionPolicy Bypass -File $JalurKonsistensi -DenganE2E
    }
    if ($LASTEXITCODE -ne 0) {
        Write-Host "`n[GAGAL] Pipeline CI/CD Lokal berhenti karena ada tahapan QA yang belum bersih!" -ForegroundColor Red
        exit $LASTEXITCODE
    }
}

$DurasiDetik = [math]::Round(((Get-Date) - $WaktuMulai).TotalSeconds, 2)
Write-Host "`n======================================================================" -ForegroundColor Green
Write-Host "  [CI/CD LOKAL SUKSES 100%] Durasi: $DurasiDetik detik | 0 Error & 0 Warning" -ForegroundColor Green
Write-Host "======================================================================" -ForegroundColor Green
exit 0
