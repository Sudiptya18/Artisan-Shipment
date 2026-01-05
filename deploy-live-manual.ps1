# Manual Deployment Script - Alternative Method
# Uses SFTP/SCP to upload files directly (if SSH deployment doesn't work)

param(
    [string]$serverUser = "your_username",
    [string]$serverHost = "ship.artisanbn.com",
    [string]$serverPath = "/home/artisanbn/public_html/ship.artisanbn.com",
    [string]$sshKey = ""
)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Manual Deployment to Live Server" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Files to exclude (already on server or shouldn't be uploaded)
$excludeFiles = @(
    ".env",
    ".git",
    "node_modules",
    "vendor",
    "storage/logs/*",
    "storage/framework/cache/*",
    "storage/framework/sessions/*",
    "storage/framework/views/*",
    "public/build",
    ".gitignore"
)

# Create a temporary file list
$tempFile = "deploy-files.txt"

Write-Host "[1/3] Creating file list..." -ForegroundColor Yellow
Get-ChildItem -Recurse -File | Where-Object {
    $relativePath = $_.FullName.Replace((Get-Location).Path + "\", "").Replace("\", "/")
    $shouldExclude = $false
    foreach ($exclude in $excludeFiles) {
        if ($relativePath -like $exclude -or $relativePath -like "*$exclude*") {
            $shouldExclude = $true
            break
        }
    }
    return -not $shouldExclude
} | ForEach-Object {
    $relativePath = $_.FullName.Replace((Get-Location).Path + "\", "").Replace("\", "/")
    Write-Output $relativePath
} | Out-File $tempFile

Write-Host "[2/3] Uploading files via SCP..." -ForegroundColor Yellow

# Use SCP to upload files
if ($sshKey) {
    $scpCommand = "scp -i `"$sshKey`" -r * $serverUser@$serverHost:$serverPath"
} else {
    $scpCommand = "scp -r * $serverUser@$serverHost:$serverPath"
}

Write-Host "Note: This method uploads all files. For better control, use deploy-live.ps1" -ForegroundColor Yellow
Write-Host "Command: $scpCommand" -ForegroundColor Gray

# Clean up
Remove-Item $tempFile -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "Manual deployment method. Please run commands on server:" -ForegroundColor Yellow
Write-Host "  cd $serverPath" -ForegroundColor White
Write-Host "  git pull origin main" -ForegroundColor White
Write-Host "  php artisan migrate --force" -ForegroundColor White
Write-Host "  php artisan config:clear" -ForegroundColor White
Write-Host "  php artisan cache:clear" -ForegroundColor White
