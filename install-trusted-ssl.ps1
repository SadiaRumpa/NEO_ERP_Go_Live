# ==============================================================================
# NeoERP Enterprise — PowerShell SSL Trust Installer for Windows
# Imports the NeoERP Root CA into Cert:\CurrentUser\Root
# ==============================================================================
$certPath = Join-Path $PSScriptRoot "public\neoerp-root-ca.crt"
if (-not (Test-Path $certPath)) {
    $certPath = Join-Path $PSScriptRoot "docker\nginx\ssl\neoerp-root-ca.crt"
}

if (-not (Test-Path $certPath)) {
    Write-Host "[ERROR] Could not find neoerp-root-ca.crt at $certPath" -ForegroundColor Red
    exit 1
}

Write-Host "==============================================================" -ForegroundColor Cyan
Write-Host "  NeoERP Enterprise — Installing SSL Certificate for 8.228.59.10" -ForegroundColor Cyan
Write-Host "==============================================================" -ForegroundColor Cyan
Write-Host "Certificate: $certPath" -ForegroundColor Gray
Write-Host ""
Write-Host "Note: When the Windows confirmation dialog appears, click [Yes]." -ForegroundColor Yellow
Write-Host ""

& certutil.exe -addstore -user Root $certPath

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "==============================================================" -ForegroundColor Green
    Write-Host " [SUCCESS] NeoERP SSL Certificate has been successfully trusted!" -ForegroundColor Green
    Write-Host "==============================================================" -ForegroundColor Green
    Write-Host " 1. Restart Google Chrome or Microsoft Edge." -ForegroundColor White
    Write-Host " 2. Visit: https://8.228.59.10" -ForegroundColor White
    Write-Host " 3. The connection will show as fully verified and secure." -ForegroundColor White
} else {
    Write-Host "[ERROR] Failed to install certificate. Error code: $LASTEXITCODE" -ForegroundColor Red
}
