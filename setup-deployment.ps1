# Setup Script - Configures deployment for first time use

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Artisan Shipment - Deployment Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if deploy-live.ps1 exists
if (-not (Test-Path "deploy-live.ps1")) {
    Write-Host "Error: deploy-live.ps1 not found!" -ForegroundColor Red
    exit 1
}

Write-Host "This script will help you configure deployment." -ForegroundColor Yellow
Write-Host ""

# Get server details
$serverUser = Read-Host "Enter your server username (cPanel username)"
$serverHost = Read-Host "Enter your server hostname (e.g., ship.artisanbn.com)"
$serverPath = Read-Host "Enter your server path (e.g., /home/username/public_html/ship.artisanbn.com)"

# Update deploy-live.ps1
Write-Host ""
Write-Host "Updating deploy-live.ps1..." -ForegroundColor Yellow

$content = Get-Content "deploy-live.ps1" -Raw
$content = $content -replace '\[string\]\$serverUser = "your_username"', "[string]`$serverUser = `"$serverUser`""
$content = $content -replace '\[string\]\$serverHost = "ship.artisanbn.com"', "[string]`$serverHost = `"$serverHost`""
$content = $content -replace '\[string\]\$serverPath = "/home/artisanbn/public_html/ship.artisanbn.com"', "[string]`$serverPath = `"$serverPath`""

Set-Content -Path "deploy-live.ps1" -Value $content

Write-Host "Configuration updated!" -ForegroundColor Green
Write-Host ""

# Test SSH connection
Write-Host "Testing SSH connection..." -ForegroundColor Yellow
$testConnection = Read-Host "Do you want to test SSH connection? (y/n)"

if ($testConnection -eq "y" -or $testConnection -eq "Y") {
    Write-Host "Attempting to connect..." -ForegroundColor Yellow
    ssh -o ConnectTimeout=5 "$serverUser@$serverHost" "echo 'Connection successful!'"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "SSH connection successful!" -ForegroundColor Green
    } else {
        Write-Host "SSH connection failed. Please check:" -ForegroundColor Red
        Write-Host "  1. SSH key is set up correctly" -ForegroundColor Yellow
        Write-Host "  2. Server allows SSH connections" -ForegroundColor Yellow
        Write-Host "  3. Username and hostname are correct" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "You can now deploy using:" -ForegroundColor Cyan
Write-Host "  .\deploy.ps1 -commitMessage `"Your message`"" -ForegroundColor White
Write-Host ""
