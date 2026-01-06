# SSH Setup Guide for Live Server Deployment

## 🔑 Finding Your SSH Credentials

Based on your FTP credentials:
- **FTP Username:** `ship@artisanbn.com`
- **FTP Server:** `ftp.artisanbn.com`
- **Website:** `ship.artisanbn.com`

## 📋 SSH Credentials to Try

Your SSH username is likely one of these:
1. `ship` (most common)
2. `artisanbn` (cPanel username)
3. `ship@artisanbn.com` (full email - less common)

**SSH Host:** `ship.artisanbn.com` or `ftp.artisanbn.com`  
**SSH Port:** `22` (default) or `2222` (some hosts)

## 🚀 Step-by-Step SSH Setup

### Step 1: Enable SSH in cPanel

1. Log into your cPanel: `https://ship.artisanbn.com:2083` or your cPanel URL
2. Search for "SSH Access" or "Terminal"
3. Enable SSH access if not already enabled
4. Note your SSH username (usually shown in the SSH Access section)

### Step 2: Test SSH Connection

Open PowerShell and try these commands one by one:

```powershell
# Try option 1: "ship" username
ssh ship@ship.artisanbn.com

# If that doesn't work, try option 2: "artisanbn" username
ssh artisanbn@ship.artisanbn.com

# If that doesn't work, try option 3: Full email
ssh ship@artisanbn.com@ship.artisanbn.com
```

**What to expect:**
- If it asks for a password, enter your cPanel/FTP password: `Gya-z2K-ymL-q6H`
- If connection succeeds, you'll see a command prompt
- Type `exit` to return to your local machine

### Step 3: Find Your Server Path

Once connected via SSH, find your project directory:

```bash
# Check current directory
pwd

# Navigate to common locations
cd ~/public_html
ls -la

# Look for ship.artisanbn.com folder or your project folder
cd ship.artisanbn.com
pwd
```

**Common paths:**
- `/home/ship/public_html/ship.artisanbn.com`
- `/home/artisanbn/public_html/ship.artisanbn.com`
- `/home/ship/public_html`
- `/home/artisanbn/public_html`

### Step 4: Set Up SSH Key Authentication (Optional but Recommended)

This allows you to connect without entering a password each time.

#### 4.1: Generate SSH Key (if you don't have one)

```powershell
# Generate SSH key
ssh-keygen -t rsa -b 4096 -C "chandasudiptya@gmail.com"

# Press Enter to accept default location: C:\Users\YourName\.ssh\id_rsa
# Press Enter twice to skip passphrase (or set one for extra security)
```

#### 4.2: Copy Public Key to Server

```powershell
# Replace 'ship' with your actual SSH username
type $env:USERPROFILE\.ssh\id_rsa.pub | ssh ship@ship.artisanbn.com "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && chmod 700 ~/.ssh"
```

**Or manually:**
1. Display your public key:
```powershell
type $env:USERPROFILE\.ssh\id_rsa.pub
```

2. Copy the entire output (starts with `ssh-rsa`)

3. SSH into server:
```powershell
ssh ship@ship.artisanbn.com
```

4. On the server, run:
```bash
mkdir -p ~/.ssh
echo "PASTE_YOUR_PUBLIC_KEY_HERE" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
exit
```

#### 4.3: Test Passwordless Connection

```powershell
ssh ship@ship.artisanbn.com
```

If it connects without asking for a password, you're all set!

### Step 5: Verify Git is Set Up on Server

```bash
# SSH into server
ssh ship@ship.artisanbn.com

# Check if Git is installed
git --version

# Navigate to your project
cd /home/ship/public_html/ship.artisanbn.com

# Check if Git repository is initialized
git status

# If not initialized, initialize it:
git init
git remote add origin https://github.com/Sudiptya18/Artisan-Shipment.git
# Or: git remote add origin git@github.com:Sudiptya18/Artisan-Shipment.git
```

## 🔧 Update Deployment Scripts

Once you've confirmed your SSH credentials, update these files:

### Update `deploy-live.ps1`:

```powershell
$serverUser = "ship"                    # Your confirmed SSH username
$serverHost = "ship.artisanbn.com"       # Your domain
$serverPath = "/home/ship/public_html/ship.artisanbn.com"  # Your confirmed server path
```

### Update `deploy-live-safe.ps1`:

Same values as above.

## ✅ Test Deployment

```powershell
# Test with dry run first
.\deploy-safe.ps1 -commitMessage "Test SSH connection" -dryRun

# Test push to GitHub only (skip live deployment)
.\deploy-safe.ps1 -commitMessage "Test" -skipDeploy

# Test full deployment
.\deploy-safe.ps1 -commitMessage "Test deployment"
```

## 🐛 Common Issues

### Issue: "Permission denied (publickey)"
**Solution:**
- Verify SSH key is in `~/.ssh/authorized_keys` on server
- Check file permissions: `chmod 600 ~/.ssh/authorized_keys`
- Try password authentication first, then set up keys

### Issue: "Connection refused"
**Solution:**
- Verify SSH is enabled in cPanel
- Try different SSH port: `ssh -p 2222 ship@ship.artisanbn.com`
- Contact hosting provider if SSH is not available

### Issue: "Host key verification failed"
**Solution:**
```powershell
# Remove old host key
ssh-keygen -R ship.artisanbn.com
```

### Issue: "No such file or directory" (server path)
**Solution:**
- SSH into server and find correct path using `pwd`
- Update `$serverPath` in deployment scripts

## 📝 Quick Reference

**SSH Connection:**
```powershell
ssh ship@ship.artisanbn.com
```

**Find Server Path:**
```bash
ssh ship@ship.artisanbn.com
pwd
cd ~/public_html
ls -la
```

**Test Deployment:**
```powershell
.\deploy-safe.ps1 -commitMessage "Test" -skipDeploy
```

## 🎯 Next Steps

1. ✅ Enable SSH in cPanel
2. ✅ Test SSH connection
3. ✅ Find server path
4. ✅ Set up SSH keys (optional)
5. ✅ Update deployment scripts
6. ✅ Test deployment

Once all steps are complete, you can deploy with a single command:
```powershell
.\deploy-safe.ps1 -commitMessage "Your changes"
```

