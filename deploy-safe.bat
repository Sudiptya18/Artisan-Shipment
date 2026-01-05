@echo off
REM Safe Deployment Batch Script - Protects Local Environment
REM Alternative to PowerShell for CMD users

setlocal enabledelayedexpansion

set "COMMIT_MSG=%~1"
if "%COMMIT_MSG%"=="" set "COMMIT_MSG=Update from local development"

echo ========================================
echo Artisan Shipment - Safe Deployment
echo ========================================
echo.

echo [1/5] Checking for protected files...
git diff --cached --name-only > temp_staged.txt 2>nul
findstr /C:".env" temp_staged.txt >nul 2>&1
if %errorlevel% equ 0 (
    echo ERROR: .env file detected in staged files!
    echo This file should NEVER be committed!
    del temp_staged.txt 2>nul
    exit /b 1
)
del temp_staged.txt 2>nul

echo [2/5] Checking git status...
git status --porcelain >nul 2>&1
if errorlevel 1 (
    echo No changes to commit. Exiting.
    exit /b 0
)

echo [3/5] Staging changes (excluding local files)...
REM Unstage any protected files
git reset HEAD .env .env.local .env.development 2>nul
git add .
REM Double-check: Unstage protected files again
git reset HEAD .env .env.local .env.development 2>nul
if errorlevel 1 (
    echo Error: Failed to stage files
    exit /b 1
)

echo [4/5] Committing changes...
git commit -m "%COMMIT_MSG%"
if errorlevel 1 (
    echo Error: Failed to commit changes
    exit /b 1
)

echo [5/5] Pushing to GitHub...
git push origin main
if errorlevel 1 (
    echo Error: Failed to push to GitHub
    exit /b 1
)
echo Successfully pushed to GitHub!

echo.
echo ========================================
echo Safe Deployment Complete!
echo ========================================
echo.
echo Note: For automatic live server deployment, use deploy-safe.ps1
echo       or manually SSH into server and run: git pull origin main

endlocal
