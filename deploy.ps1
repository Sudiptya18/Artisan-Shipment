# PowerShell Deployment Script for Artisan Shipment
# This script pushes to GitHub and optionally deploys to live server

param(
    [string]$commitMessage = "Update from local development",
    [switch]$skipDeploy = $false
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Artisan Shipment - Deployment Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check git status
Write-Host "[1/5] Checking git status..." -ForegroundColor Yellow
$status = git status --porcelain
if (-not $status) {
    Write-Host "No changes to commit. Exiting." -ForegroundColor Red
    exit 0
}

# Step 2: Add all changes
Write-Host "[2/5] Staging all changes..." -ForegroundColor Yellow
git add .
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to stage files" -ForegroundColor Red
    exit 1
}

# Step 3: Commit changes
Write-Host "[3/5] Committing changes..." -ForegroundColor Yellow
git commit -m $commitMessage
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to commit changes" -ForegroundColor Red
    exit 1
}

# Step 4: Push to GitHub
Write-Host "[4/5] Pushing to GitHub..." -ForegroundColor Yellow
git push origin main
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to push to GitHub" -ForegroundColor Red
    exit 1
}
Write-Host "Successfully pushed to GitHub!" -ForegroundColor Green

# Step 5: Deploy to live server (if not skipped)
if (-not $skipDeploy) {
    Write-Host "[5/5] Deploying to live server..." -ForegroundColor Yellow
    
    # Check if deploy-live.ps1 exists
    if (Test-Path "deploy-live.ps1") {
        & .\deploy-live.ps1
    } else {
        Write-Host "Warning: deploy-live.ps1 not found. Skipping live deployment." -ForegroundColor Yellow
        Write-Host "Create deploy-live.ps1 for automatic live server deployment." -ForegroundColor Yellow
    }
} else {
    Write-Host "[5/5] Skipping live deployment (--skipDeploy flag used)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Deployment Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
