@echo off
REM Batch Deployment Script for Artisan Shipment
REM Alternative to PowerShell script for CMD users

setlocal enabledelayedexpansion

set "COMMIT_MSG=%~1"
if "%COMMIT_MSG%"=="" set "COMMIT_MSG=Update from local development"

echo ========================================
echo Artisan Shipment - Deployment Script
echo ========================================
echo.

echo [1/5] Checking git status...
git status --porcelain >nul 2>&1
if errorlevel 1 (
    echo No changes to commit. Exiting.
    exit /b 0
)

echo [2/5] Staging all changes...
git add .
if errorlevel 1 (
    echo Error: Failed to stage files
    exit /b 1
)

echo [3/5] Committing changes...
git commit -m "%COMMIT_MSG%"
if errorlevel 1 (
    echo Error: Failed to commit changes
    exit /b 1
)

echo [4/5] Pushing to GitHub...
git push origin main
if errorlevel 1 (
    echo Error: Failed to push to GitHub
    exit /b 1
)
echo Successfully pushed to GitHub!

echo [5/5] Deployment complete!
echo.
echo ========================================
echo Deployment Complete!
echo ========================================
echo.
echo Note: For automatic live server deployment, use deploy.ps1
echo       or manually SSH into server and run: git pull origin main

endlocal
