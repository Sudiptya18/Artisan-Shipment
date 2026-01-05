# Deployment Guide - Local to GitHub to Live Server

This guide explains how to set up automated deployment from your local development environment to GitHub and then to your live server.

## 🎯 Overview

**Workflow:**
```
Local Development → Git Commit → GitHub Push → Live Server Pull
```

## 📋 Prerequisites

1. **Git** installed and configured
2. **GitHub account** and repository
3. **SSH access** to live server (or alternative method)
4. **PowerShell** (Windows) or **Bash** (Linux/Mac)

## 🛡️ IMPORTANT: Environment Protection

**Before deploying, read `ENVIRONMENT_PROTECTION.md`** to ensure your local environment files (especially `.env`) never get pushed to live server.

**Recommended:** Always use `deploy-safe.ps1` instead of `deploy.ps1` for maximum protection.

## 🚀 Method 1: Automated Deployment (Recommended)

### Step 1: Configure GitHub Repository

If you don't have a GitHub repository yet:

```powershell
# Check current remote
git remote -v

# If no remote exists, add one:
git remote add origin https://github.com/yourusername/artisan-shipment.git

# Or if using SSH:
git remote add origin git@github.com:yourusername/artisan-shipment.git
```

### Step 2: Configure Live Server SSH Access

1. **Generate SSH Key** (if you don't have one):
```powershell
ssh-keygen -t rsa -b 4096 -C "your_email@example.com"
```

2. **Copy SSH Key to Server**:
```powershell
# Copy public key to server
ssh-copy-id username@ship.artisanbn.com

# Or manually:
cat ~/.ssh/id_rsa.pub | ssh username@ship.artisanbn.com "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"
```

3. **Test SSH Connection**:
```powershell
ssh username@ship.artisanbn.com
```

### Step 3: Configure Deployment Scripts

1. **Edit `deploy-live.ps1`** with your server details:
```powershell
$serverUser = "your_cpanel_username"
$serverHost = "ship.artisanbn.com"
$serverPath = "/home/artisanbn/public_html/ship.artisanbn.com"
```

2. **Make scripts executable** (if needed):
```powershell
# PowerShell doesn't need chmod, but ensure scripts are not blocked
Unblock-File -Path deploy.ps1
Unblock-File -Path deploy-live.ps1
```

### Step 4: Deploy!

**⚠️ RECOMMENDED: Use safe deployment (protects local files):**
```powershell
.\deploy-safe.ps1 -commitMessage "Your commit message"
```

**Simple deployment (push to GitHub only):**
```powershell
.\deploy-safe.ps1 -commitMessage "Your commit message" -skipDeploy
```

**Test what would be deployed (dry run):**
```powershell
.\deploy-safe.ps1 -commitMessage "Test" -dryRun
```

**Full deployment (push to GitHub + deploy to live):**
```powershell
.\deploy-safe.ps1 -commitMessage "Your commit message"
```

**Alternative (regular deployment - still safe via .gitignore):**
```powershell
.\deploy.ps1 -commitMessage "Your commit message"
```

## 🔄 Method 2: GitHub Actions (Fully Automated)

### Step 1: Create GitHub Actions Workflow

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Live Server

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Deploy to server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.SERVER_HOST }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SSH_PRIVATE_KEY }}
        script: |
          cd /home/artisanbn/public_html/ship.artisanbn.com
          git pull origin main
          php artisan migrate --force
          php artisan config:clear
          php artisan cache:clear
          php artisan route:clear
          php artisan view:clear
```

### Step 2: Add GitHub Secrets

1. Go to your GitHub repository
2. Settings → Secrets and variables → Actions
3. Add these secrets:
   - `SERVER_HOST`: `ship.artisanbn.com`
   - `SERVER_USER`: Your server username
   - `SSH_PRIVATE_KEY`: Your private SSH key content

### Step 3: Push and Auto-Deploy

Now every push to `main` branch will automatically deploy to live server!

## 🛠️ Method 3: Manual Deployment (Alternative)

If SSH doesn't work or you prefer manual control:

### Option A: Using Git on Server

1. **SSH into server:**
```powershell
ssh username@ship.artisanbn.com
```

2. **Navigate to project:**
```bash
cd /home/artisanbn/public_html/ship.artisanbn.com
```

3. **Pull latest changes:**
```bash
git pull origin main
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Option B: Using FTP/SFTP Client

1. **Upload changed files** via FTP/SFTP client
2. **SSH into server** and run artisan commands

## 📝 Quick Reference Commands

### Local Development Workflow

```powershell
# 1. Make your changes locally

# 2. Check what changed
git status

# 3. Deploy (pushes to GitHub + deploys to live)
.\deploy.ps1 -commitMessage "Added new feature"

# Or just push to GitHub
.\deploy.ps1 -commitMessage "Update" -skipDeploy
```

### Manual Git Commands

```powershell
# Stage all changes
git add .

# Commit
git commit -m "Your message"

# Push to GitHub
git push origin main

# Then manually deploy to server (SSH)
ssh username@ship.artisanbn.com "cd /path/to/project && git pull && php artisan migrate --force && php artisan config:clear"
```

## 🔒 Security Best Practices

1. **Never commit `.env` file** - It's already in `.gitignore`
2. **Use SSH keys** instead of passwords
3. **Keep secrets in GitHub Secrets** (for Actions)
4. **Use different `.env` files** for local and production
5. **Review changes** before deploying

## 🐛 Troubleshooting

### Issue: "Permission denied (publickey)"
**Solution:** Ensure SSH key is added to server's `~/.ssh/authorized_keys`

### Issue: "Git pull fails on server"
**Solution:** Ensure server has Git installed and repository is initialized

### Issue: "Artisan commands fail"
**Solution:** Check PHP version, permissions, and database connection

### Issue: "Script execution is disabled"
**Solution:** Run PowerShell as Administrator or:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

## 📦 Files Included

- `deploy.ps1` - Main deployment script (push to GitHub + optional live deploy)
- `deploy-live.ps1` - Live server deployment via SSH
- `deploy-live-manual.ps1` - Alternative manual deployment method
- `DEPLOYMENT_GUIDE.md` - This guide

## ✅ Checklist

- [ ] GitHub repository created and configured
- [ ] SSH access to live server working
- [ ] `deploy-live.ps1` configured with server details
- [ ] Test deployment with `-skipDeploy` first
- [ ] Test full deployment
- [ ] Set up GitHub Actions (optional, for auto-deploy)

## 🎉 You're All Set!

Now you can develop locally and deploy with a single command:
```powershell
.\deploy.ps1 -commitMessage "Your changes"
```

This will:
1. ✅ Stage all changes
2. ✅ Commit with your message
3. ✅ Push to GitHub
4. ✅ Deploy to live server (if configured)

Happy deploying! 🚀
