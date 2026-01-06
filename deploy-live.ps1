# PowerShell Script for Deploying to Live Server
# This script pulls from GitHub on the live server via SSH

param(
    [string]$serverUser = "ship",
    [string]$serverHost = "ship.artisanbn.com",
    [string]$serverPath = "/home/artisanbn/public_html/ship.artisanbn.com",
    [string]$sshKey = ""
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

# Build SSH command - escape the command properly for remote execution
$remoteCommand = "cd $serverPath && git pull origin main && php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear"

# Build SSH command with proper escaping
if ($sshKey -and (Test-Path $sshKey)) {
    $sshCommand = "ssh -i `"$sshKey`" $serverUser@$serverHost `"$remoteCommand`""
} else {
    # Use password authentication (will prompt for password)
    $sshCommand = "ssh $serverUser@$serverHost `"$remoteCommand`""
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
