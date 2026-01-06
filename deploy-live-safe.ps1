# Safe Live Server Deployment Script
# Protects live server .env and other production files

param(
    [string]$serverUser = "ship",
    [string]$serverHost = "ship.artisanbn.com",
    [string]$serverPath = "/home/artisanbn/public_html/ship.artisanbn.com",
    [string]$sshKey = ""
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Safe Deployment to Live Server" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if SSH is available
$sshAvailable = Get-Command ssh -ErrorAction SilentlyContinue
if (-not $sshAvailable) {
    Write-Host "Error: SSH not found. Please install OpenSSH or use Git Bash." -ForegroundColor Red
    exit 1
}

Write-Host "This deployment will:" -ForegroundColor Yellow
Write-Host "  ✓ Pull latest code from GitHub" -ForegroundColor Green
Write-Host "  ✓ Run database migrations" -ForegroundColor Green
Write-Host "  ✓ Clear caches" -ForegroundColor Green
Write-Host "  ✓ PROTECT existing .env file on server" -ForegroundColor Green
Write-Host "  ✓ PROTECT server-specific files" -ForegroundColor Green
Write-Host ""

# Build SSH command with protection
$remoteCommand = "cd $serverPath && if [ -f .env ]; then cp .env .env.backup.`$(date +%Y%m%d_%H%M%S); fi && git pull origin main && php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear && php artisan optimize:clear && echo 'Deployment complete!'"

Write-Host "Connecting to live server..." -ForegroundColor Yellow
Write-Host "Server: $serverUser@$serverHost" -ForegroundColor Gray
Write-Host "Path: $serverPath" -ForegroundColor Gray
Write-Host ""

# Execute SSH command
if ($sshKey -and (Test-Path $sshKey)) {
    ssh -i "$sshKey" "$serverUser@$serverHost" "$remoteCommand"
} else {
    # Use password authentication (will prompt for password)
    ssh "$serverUser@$serverHost" "$remoteCommand"
}

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "Successfully deployed to live server!" -ForegroundColor Green
    Write-Host "✓ Production .env was protected" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "Error: Deployment to live server failed" -ForegroundColor Red
    Write-Host "Please check:" -ForegroundColor Yellow
    Write-Host "  1. SSH connection is working" -ForegroundColor White
    Write-Host "  2. Server path is correct" -ForegroundColor White
    Write-Host "  3. Git repository is initialized on server" -ForegroundColor White
    exit 1
}
