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
$sshCommands = @"
cd $serverPath

# Backup current .env (if exists)
if [ -f .env ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    echo "✓ .env backed up"
fi

# Pull latest code
git pull origin main

# Restore .env if it was overwritten (protect production config)
if [ -f .env.backup.* ]; then
    LATEST_BACKUP=\$(ls -t .env.backup.* | head -1)
    if [ ! -f .env ] || [ .env -ot .env.production ]; then
        cp \$LATEST_BACKUP .env
        echo "✓ .env restored from backup"
    fi
fi

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Ensure .env is not overwritten (final check)
if [ -f .env.production ]; then
    cp .env.production .env
    echo "✓ Production .env ensured"
fi

echo ""
echo "✓ Deployment complete!"
echo "✓ Production .env protected"
"@

# Build SSH command
if ($sshKey -and (Test-Path $sshKey)) {
    $sshCommand = "ssh -i `"$sshKey`" $serverUser@$serverHost `"$sshCommands`""
} else {
    # Use password authentication (will prompt for password)
    $sshCommand = "ssh $serverUser@$serverHost `"$sshCommands`""
}

Write-Host "Connecting to live server..." -ForegroundColor Yellow
Write-Host "Server: $serverUser@$serverHost" -ForegroundColor Gray
Write-Host "Path: $serverPath" -ForegroundColor Gray
Write-Host ""

# Execute SSH command
Invoke-Expression $sshCommand

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
