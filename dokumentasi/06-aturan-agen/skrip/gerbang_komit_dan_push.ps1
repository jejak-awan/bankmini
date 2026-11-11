# ==============================================================================
# Gerbang Rilis Aman (Commit & Push Gate Blocker) - Projek Bank Mini
# Memblokir `git commit` dan `git push` apabila ada 1 saja QA Gate yang gagal
# (mengandung error, warning, atau bugs).
# ==============================================================================
param(
    [Parameter(Mandatory = $false)]
    [string]$PesanCommit = "",
    [switch]$JalankanPush
)

$ErrorActionPreference = "Stop"
$AkarProjek = "C:\xampp\htdocs\www\bankmini"
$JalurKonsistensi = Join-Path $PSScriptRoot "periksa-konsistensi.ps1"

Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "  GERBANG AMAN KOMIT & PUSH - WAJIB LULUS 100% QA (0 ERROR/WARNING)   " -ForegroundColor Cyan
Write-Host "======================================================================" -ForegroundColor Cyan

& powershell -NoProfile -ExecutionPolicy Bypass -File $JalurKonsistensi -DenganE2E
if ($LASTEXITCODE -ne 0) {
    Write-Host "`n[DIBLOKIR] Terdapat masalah QA / bugs / error / warning. Commit & push dibatalkan!" -ForegroundColor Red
    exit 1
}

Push-Location $AkarProjek
try {
    if ($PesanCommit.Trim() -ne "") {
        & git add -A
        & git commit -m $PesanCommit
        if ($LASTEXITCODE -ne 0) {
            Write-Host "`n[GAGAL] Proses git commit dihentikan." -ForegroundColor Red
            exit $LASTEXITCODE
        }
    }

    if ($JalankanPush) {
        & git push origin main
        if ($LASTEXITCODE -ne 0) {
            Write-Host "`n[GAGAL] Proses git push dihentikan." -ForegroundColor Red
            exit $LASTEXITCODE
        }
    }
} finally {
    Pop-Location
}

Write-Host "`n[SUKSES] Seluruh gerbang QA bersih dan aman." -ForegroundColor Green
exit 0

