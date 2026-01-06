# Safe Deployment Script - Protects Local Environment from Live
# This script ensures local-only files are never deployed

param(
    [string]$commitMessage = "Update from local development",
    [switch]$skipDeploy = $false,
    [switch]$dryRun = $false
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Artisan Shipment - Safe Deployment" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Verify .env is not staged
Write-Host "[1/6] Checking for protected files..." -ForegroundColor Yellow
$stagedFiles = git diff --cached --name-only
$protectedFiles = @('.env', '.env.local', '.env.development', 'composer.phar', 'phpunit.phar')

$foundProtected = $false
foreach ($file in $stagedFiles) {
    foreach ($protected in $protectedFiles) {
        if ($file -like "*$protected*") {
            Write-Host "ERROR: Protected file detected: $file" -ForegroundColor Red
            Write-Host "This file should NEVER be committed!" -ForegroundColor Red
            $foundProtected = $true
        }
    }
}

if ($foundProtected) {
    Write-Host ""
    Write-Host "Please unstage protected files:" -ForegroundColor Yellow
    Write-Host "  git reset HEAD <file>" -ForegroundColor White
    exit 1
}

# Step 2: Check git status
Write-Host "[2/6] Checking git status..." -ForegroundColor Yellow
$status = git status --porcelain
if (-not $status) {
    Write-Host "No changes to commit. Exiting." -ForegroundColor Red
    exit 0
}

# Step 3: Show what will be committed (exclude local files)
Write-Host "[3/6] Files to be committed (excluding local-only files):" -ForegroundColor Yellow
git status --short | Where-Object {
    $_ -notmatch '\.env\.local' -and
    $_ -notmatch '\.env\.development' -and
    $_ -notmatch 'composer\.phar' -and
    $_ -notmatch 'phpunit\.phar' -and
    $_ -notmatch '\.vscode/settings\.json' -and
    $_ -notmatch '\.idea/workspace\.xml'
} | ForEach-Object {
    Write-Host "  $_" -ForegroundColor Gray
}

if ($dryRun) {
    Write-Host ""
    Write-Host "DRY RUN: No changes were made." -ForegroundColor Yellow
    Write-Host "Remove -dryRun flag to actually deploy." -ForegroundColor Yellow
    exit 0
}

# Step 4: Add changes (excluding local files)
Write-Host "[4/6] Staging changes (excluding local files)..." -ForegroundColor Yellow

# Unstage any local files that might have been added
git reset HEAD .env.local .env.development composer.phar phpunit.phar 2>$null

# Add all changes
git add .

# Double-check: Unstage any protected files
foreach ($protected in $protectedFiles) {
    git reset HEAD "*$protected*" 2>$null
}

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to stage files" -ForegroundColor Red
    exit 1
}

# Step 5: Commit changes
Write-Host "[5/6] Committing changes..." -ForegroundColor Yellow
git commit -m $commitMessage
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to commit changes" -ForegroundColor Red
    exit 1
}

# Step 6: Push to GitHub
Write-Host "[6/6] Pushing to GitHub..." -ForegroundColor Yellow
git push origin main
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to push to GitHub" -ForegroundColor Red
    exit 1
}
Write-Host "Successfully pushed to GitHub!" -ForegroundColor Green

# Step 7: Deploy to live server (if not skipped)
if (-not $skipDeploy) {
    Write-Host ""
    Write-Host "[Deploy] Deploying to live server..." -ForegroundColor Yellow
    
    if (Test-Path "deploy-live-safe.ps1") {
        & .\deploy-live-safe.ps1
    } elseif (Test-Path "deploy-live.ps1") {
        & .\deploy-live.ps1
    } else {
        Write-Host "Warning: Live deployment script not found." -ForegroundColor Yellow
        Write-Host "Create deploy-live-safe.ps1 for safe live deployment." -ForegroundColor Yellow
    }
} else {
    Write-Host ""
    Write-Host "[Deploy] Skipping live deployment (--skipDeploy flag used)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Safe Deployment Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "[OK] Local files protected" -ForegroundColor Green
Write-Host "[OK] Changes pushed to GitHub" -ForegroundColor Green
if (-not $skipDeploy) {
    Write-Host "[OK] Live server updated" -ForegroundColor Green
}
