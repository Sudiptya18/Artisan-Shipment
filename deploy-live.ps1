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

Write-Host "Connecting to live server..." -ForegroundColor Yellow
Write-Host "Server: $serverUser@$serverHost" -ForegroundColor Gray
Write-Host "Path: $serverPath" -ForegroundColor Gray
Write-Host ""

# Create a temporary script file to avoid PowerShell parsing issues
$tempScript = [System.IO.Path]::GetTempFileName()
$scriptContent = @"
cd $serverPath
git pull origin main
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
"@

Set-Content -Path $tempScript -Value $scriptContent -Encoding UTF8

try {
    # Execute SSH command - send script file to server
    if ($sshKey -and (Test-Path $sshKey)) {
        Get-Content $tempScript | ssh -i "$sshKey" "$serverUser@$serverHost" bash
    } else {
        # Use password authentication (will prompt for password)
        Get-Content $tempScript | ssh "$serverUser@$serverHost" bash
    }
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Successfully deployed to live server!" -ForegroundColor Green
    } else {
        Write-Host "Error: Deployment to live server failed" -ForegroundColor Red
        exit 1
    }
} finally {
    # Clean up temp file
    Remove-Item $tempScript -ErrorAction SilentlyContinue
}
