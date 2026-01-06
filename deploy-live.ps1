# PowerShell Script for Deploying to Live Server
# This script pulls from GitHub on the live server via SSH

param(
    [string]$serverUser = "ship",
    [string]$serverHost = "ship.artisanbn.com",
    [string]$serverPath = "/home/artisanbn/public_html/ship.artisanbn.com",
    [string]$sshKey = "t9qMCvHG82tQHAVhhqKh31UkB3edyQwcqyoN8e2Nans"
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Deploying to Live Server" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if SSH is available
$sshAvailable = Get-Command ssh -ErrorAction SilentlyContinue
if (-not $sshAvailable) {
    Write-Host "Error: SSH not found. Please install OpenSSH or use Git Bash." -ForegroundColor Red
    exit 1
}

# Build SSH command
$sshCommand = "cd $serverPath && git pull origin main && php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear"

if ($sshKey) {
    $sshCommand = "ssh -i `"$sshKey`" $serverUser@$serverHost `"$sshCommand`""
} else {
    $sshCommand = "ssh $serverUser@$serverHost `"$sshCommand`""
}

Write-Host "Connecting to live server..." -ForegroundColor Yellow
Write-Host "Command: $sshCommand" -ForegroundColor Gray

# Execute SSH command
Invoke-Expression $sshCommand

if ($LASTEXITCODE -eq 0) {
    Write-Host "Successfully deployed to live server!" -ForegroundColor Green
} else {
    Write-Host "Error: Deployment to live server failed" -ForegroundColor Red
    exit 1
}
