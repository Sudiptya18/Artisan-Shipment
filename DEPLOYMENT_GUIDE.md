# Deployment Guide - Local to GitHub to Live Server

This guide explains how to set up automated deployment from your local development environment to GitHub and then to your live server using PowerShell scripts.

## 🎯 Overview

**Workflow:**
```
Local Development → Git Commit → GitHub Push → Live Server Pull
```

## 📋 Prerequisites

1. **Git** installed and configured
2. **GitHub account** and repository
3. **SSH access** to live server
4. **PowerShell** (Windows)

## 🛡️ IMPORTANT: Environment Protection

**Before deploying, read `ENVIRONMENT_PROTECTION.md`** to ensure your local environment files (especially `.env`) never get pushed to live server.

**Recommended:** Always use `deploy-safe.ps1` instead of `deploy.ps1` for maximum protection.

## 🚀 Automated Deployment Setup

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

### Step 2: Set Up SSH Access to Live Server

#### 2.1: Find Your SSH Credentials

Based on your FTP credentials:
- **FTP Username:** `ship@artisanbn.com`
- **FTP Server:** `ftp.artisanbn.com`
- **Website:** `ship.artisanbn.com`

Your SSH credentials are likely:
- **SSH Username:** `ship` or `artisanbn` (try both)
- **SSH Host:** `ship.artisanbn.com` or `ftp.artisanbn.com`
- **SSH Port:** `22` (or `2222` for some hosts)

#### 2.2: Test SSH Connection

Try these commands to find the correct SSH username:

```powershell
# Try with "ship" username
ssh ship@ship.artisanbn.com

# Or try with "artisanbn" username
ssh artisanbn@ship.artisanbn.com

# Or try with full email
ssh ship@artisanbn.com@ship.artisanbn.com
```

**Note:** You may need to enable SSH access in your cPanel first:
1. Log into cPanel
2. Go to "SSH Access" or "Terminal"
3. Enable SSH access if not already enabled

#### 2.3: Set Up SSH Key Authentication (Recommended)

1. **Generate SSH Key** (if you don't have one):
```powershell
ssh-keygen -t rsa -b 4096 -C "chandasudiptya@gmail.com"
```

2. **Copy SSH Public Key to Server**:
```powershell
# Copy public key to server (replace 'ship' with your actual SSH username)
type $env:USERPROFILE\.ssh\id_rsa.pub | ssh ship@ship.artisanbn.com "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"
```

3. **Test SSH Connection**:
```powershell
ssh ship@ship.artisanbn.com
```

If connection works, you'll see a command prompt. Type `exit` to return.

#### 2.4: Find Your Server Path

Once connected via SSH, find your project path:

```bash
# On the server, run:
pwd
# This shows your current directory

# Or navigate to common locations:
cd ~/public_html
ls -la
# Look for ship.artisanbn.com or similar folder
```

Common paths:
- `/home/ship/public_html/ship.artisanbn.com`
- `/home/artisanbn/public_html/ship.artisanbn.com`
- `/home/ship/public_html`
- `/home/artisanbn/public_html`

### Step 3: Configure Deployment Scripts

The deployment scripts are already configured with your server details. Verify and update if needed:

**Edit `deploy-live.ps1`** and update these lines if needed:

```powershell
$serverUser = "ship"                    # Your SSH username (try "ship" or "artisanbn")
$serverHost = "ship.artisanbn.com"      # Your domain
$serverPath = "/home/ship/public_html/ship.artisanbn.com"  # Your server path (update this!)
```

**Edit `deploy-live-safe.ps1`** with the same details for safe deployment.

**Make scripts executable** (if needed):
```powershell
# PowerShell doesn't need chmod, but ensure scripts are not blocked
Unblock-File -Path deploy.ps1
Unblock-File -Path deploy-safe.ps1
Unblock-File -Path deploy-live.ps1
Unblock-File -Path deploy-live-safe.ps1
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

## 📝 Quick Reference Commands

### Daily Development Workflow

```powershell
# 1. Make your changes locally

# 2. Check what changed
git status

# 3. Deploy (pushes to GitHub + deploys to live)
.\deploy-safe.ps1 -commitMessage "Added new feature"

# Or just push to GitHub (skip live deployment)
.\deploy-safe.ps1 -commitMessage "Update" -skipDeploy
```

### Manual Git Commands (Alternative)

```powershell
# Stage all changes
git add .

# Commit
git commit -m "Your message"

# Push to GitHub
git push origin main

# Then manually deploy to server (SSH)
ssh ship@ship.artisanbn.com "cd /home/ship/public_html/ship.artisanbn.com && git pull && php artisan migrate --force && php artisan config:clear"
```

## 🔒 Security Best Practices

1. **Never commit `.env` file** - It's already in `.gitignore`
2. **Use SSH keys** instead of passwords
3. **Use different `.env` files** for local and production
4. **Review changes** before deploying
5. **Test with `-skipDeploy` first** before full deployment

## 🐛 Troubleshooting

### Issue: "Permission denied (publickey)"
**Solution:** 
- Ensure SSH key is added to server's `~/.ssh/authorized_keys`
- Try different SSH username (ship vs artisanbn)
- Check if SSH is enabled in cPanel

### Issue: "Connection refused" or "Connection timed out"
**Solution:**
- Verify SSH is enabled in cPanel
- Try different SSH port (22 or 2222)
- Check firewall settings
- Contact your hosting provider

### Issue: "Git pull fails on server"
**Solution:** 
- Ensure server has Git installed
- Verify repository is initialized on server
- Check Git remote URL is correct
- Ensure you have write permissions

### Issue: "Artisan commands fail"
**Solution:** 
- Check PHP version on server
- Verify file permissions (`chmod -R 775 storage bootstrap/cache`)
- Check database connection in `.env`
- Ensure Composer dependencies are installed (`composer install`)

### Issue: "Script execution is disabled"
**Solution:** Run PowerShell as Administrator or:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Issue: "Path not found" on server
**Solution:**
- SSH into server and find correct path using `pwd` and `ls -la`
- Update `$serverPath` in `deploy-live.ps1` with correct path

## 📦 Files Included

- `deploy.ps1` - Main deployment script (push to GitHub + optional live deploy)
- `deploy-safe.ps1` - Safe deployment script (protects local files)
- `deploy-live.ps1` - Live server deployment via SSH
- `deploy-live-safe.ps1` - Safe live server deployment (protects server .env)
- `DEPLOYMENT_GUIDE.md` - This guide
- `ENVIRONMENT_PROTECTION.md` - Environment protection guide

## ✅ Pre-Deployment Checklist

- [ ] GitHub repository created and configured
- [ ] SSH access to live server working (tested manually)
- [ ] SSH username confirmed (ship or artisanbn)
- [ ] Server path confirmed (via SSH `pwd` command)
- [ ] `deploy-live.ps1` configured with correct server details
- [ ] SSH key authentication set up (optional but recommended)
- [ ] Test deployment with `-skipDeploy` first
- [ ] Test full deployment
- [ ] `.env` file is in `.gitignore` (already done)

## 🎉 You're All Set!

Now you can develop locally and deploy with a single command:
```powershell
.\deploy-safe.ps1 -commitMessage "Your changes"
```

This will:
1. ✅ Check for protected files (like .env)
2. ✅ Stage all changes
3. ✅ Commit with your message
4. ✅ Push to GitHub
5. ✅ Deploy to live server (SSH, pull, migrate, clear cache)

Happy deploying! 🚀
