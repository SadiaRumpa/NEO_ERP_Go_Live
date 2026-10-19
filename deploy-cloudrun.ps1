# ==============================================================================
# NeoERP — Google Cloud Run Automated Deployment Script (Windows PowerShell)
# ==============================================================================
param(
    [string]$ProjectId = "",
    [string]$Region = "europe-west2",
    [string]$ServiceName = "neoerp"
)

$ErrorActionPreference = "Stop"

Write-Host "=================================================================" -ForegroundColor Cyan
Write-Host "   NeoERP Enterprise — Google Cloud Run Automated Deployment" -ForegroundColor White
Write-Host "=================================================================" -ForegroundColor Cyan

# 1. Locate gcloud command
$gcloudCmd = "$env:LOCALAPPDATA\Google\Cloud SDK\google-cloud-sdk\bin\gcloud.cmd"
if (-not (Test-Path $gcloudCmd)) {
    $found = Get-Command gcloud -ErrorAction SilentlyContinue
    if ($found) {
        $gcloudCmd = "gcloud"
    } else {
        Write-Host "[-] Google Cloud SDK not found. Installing..." -ForegroundColor Yellow
        winget install --id Google.CloudSDK --silent --accept-source-agreements --accept-package-agreements
        $gcloudCmd = "$env:LOCALAPPDATA\Google\Cloud SDK\google-cloud-sdk\bin\gcloud.cmd"
    }
}

Write-Host "[+] Using Google Cloud SDK: $gcloudCmd" -ForegroundColor Green

# 2. Authenticate if no active account
Write-Host "[*] Checking Google Cloud authentication..." -ForegroundColor Cyan
$authAccounts = & $gcloudCmd auth list --filter=status:ACTIVE --format="value(account)" 2>$null
if (-not $authAccounts) {
    Write-Host "[!] No active Google account found. Launching authentication..." -ForegroundColor Yellow
    Write-Host "    (Your browser will open to complete sign-in)" -ForegroundColor Gray
    & $gcloudCmd auth login --brief
    $authAccounts = & $gcloudCmd auth list --filter=status:ACTIVE --format="value(account)"
}
Write-Host "[+] Authenticated as: $authAccounts" -ForegroundColor Green

# 3. Select / Configure Project
if (-not $ProjectId) {
    $currentProject = & $gcloudCmd config get-value project 2>$null
    if ($currentProject -and $currentProject -ne "(unset)") {
        $ProjectId = $currentProject
        Write-Host "[+] Using currently configured project: $ProjectId" -ForegroundColor Green
    } else {
        Write-Host "[*] Fetching available Google Cloud projects..." -ForegroundColor Cyan
        & $gcloudCmd projects list --format="table(projectId,name,projectNumber)"
        Write-Host ""
        $ProjectId = Read-Host "Enter your Google Cloud Project ID"
    }
}

if (-not $ProjectId) {
    Write-Host "[-] Error: Project ID is required to deploy to Cloud Run." -ForegroundColor Red
    exit 1
}

Write-Host "[*] Setting active project to: $ProjectId" -ForegroundColor Cyan
& $gcloudCmd config set project $ProjectId

# 4. Enable Required Google Cloud APIs
Write-Host "[*] Ensuring Cloud Run, Cloud Build & Artifact Registry APIs are enabled..." -ForegroundColor Cyan
& $gcloudCmd services enable run.googleapis.com cloudbuild.googleapis.com artifactregistry.googleapis.com

# 5. Build and Deploy directly to Google Cloud Run
Write-Host "=================================================================" -ForegroundColor Cyan
Write-Host "   Deploying NeoERP container to Google Cloud Run ($Region)..." -ForegroundColor Yellow
Write-Host "=================================================================" -ForegroundColor Cyan

& $gcloudCmd run deploy $ServiceName `
    --source . `
    --region $Region `
    --platform managed `
    --allow-unauthenticated `
    --memory 1Gi `
    --cpu 1 `
    --port 8080 `
    --set-env-vars "APP_NAME=NeoERP,APP_ENV=production,APP_DEBUG=false,DB_CONNECTION=sqlite,DB_DATABASE=/var/www/html/database/database.sqlite,SESSION_DRIVER=file,CACHE_STORE=file,QUEUE_CONNECTION=database,LOG_CHANNEL=stderr"

# 6. Retrieve Live Service URL
$serviceUrl = & $gcloudCmd run services describe $ServiceName --region $Region --format="value(status.url)"

Write-Host ""
Write-Host "=================================================================" -ForegroundColor Green
Write-Host "   🎉 NeoERP Google Cloud Run Deployment Successful!" -ForegroundColor Green
Write-Host "=================================================================" -ForegroundColor Green
Write-Host ""
Write-Host "   🚀 LIVE URL:   $serviceUrl" -ForegroundColor Cyan
Write-Host ""
Write-Host "   =============================================================" -ForegroundColor Gray
Write-Host "   SHOWCASE LOGIN CREDENTIALS" -ForegroundColor Yellow
Write-Host "   =============================================================" -ForegroundColor Gray
Write-Host "   Role:          Super Admin" -ForegroundColor White
Write-Host "   Email:         admin@neoerp.local" -ForegroundColor White
Write-Host "   Password:      NeoErp!Admin2026#Secure" -ForegroundColor White
Write-Host ""
Write-Host "   Role:          Commercial Director" -ForegroundColor White
Write-Host "   Email:         marcus.vance@neoerp.local" -ForegroundColor White
Write-Host "   Password:      NeoErp!Staff2026#" -ForegroundColor White
Write-Host ""
Write-Host "   Role:          Lead Quantity Surveyor" -ForegroundColor White
Write-Host "   Email:         priya.patel@neoerp.local" -ForegroundColor White
Write-Host "   Password:      NeoErp!Staff2026#" -ForegroundColor White
Write-Host ""
Write-Host "   Role:          Project Engineer" -ForegroundColor White
Write-Host "   Email:         sarah.engineer@neoerp.local" -ForegroundColor White
Write-Host "   Password:      NeoErp!Staff2026#" -ForegroundColor White
Write-Host "=================================================================" -ForegroundColor Green
