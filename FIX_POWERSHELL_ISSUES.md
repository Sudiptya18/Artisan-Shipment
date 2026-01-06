# Fix PowerShell Issues

## Issue 1: SSH Key Generation Error

**Problem:** You entered `$env:USERPROFILE\.ssh\id_rsa.pub` as the file path, but you should just press Enter to accept the default.

**Solution:**

1. Run the command again:
```powershell
ssh-keygen -t rsa -b 4096 -C "chandasudiptya@gmail.com"
```

2. When it asks: `Enter file in which to save the key (C:\Users\Sudiptya Chanda/.ssh/id_rsa):`
   - **Just press Enter** (don't type anything)
   - This will use the default location: `C:\Users\Sudiptya Chanda\.ssh\id_rsa`

3. When it asks for passphrase:
   - **Press Enter** (no passphrase) - or enter a passphrase if you want extra security
   - **Press Enter again** to confirm

4. Your SSH key will be created at:
   - Private key: `C:\Users\Sudiptya Chanda\.ssh\id_rsa`
   - Public key: `C:\Users\Sudiptya Chanda\.ssh\id_rsa.pub`

## Issue 2: PowerShell Script Execution Disabled

**Problem:** PowerShell scripts are blocked from running.

**Solution - Method 1 (Recommended):**

Run PowerShell as Administrator and execute:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

**Solution - Method 2 (Bypass for this session only):**

Run the script with bypass flag:
```powershell
powershell -ExecutionPolicy Bypass -File .\deploy-safe.ps1 -commitMessage "Test SSH" -dryRun
```

**Solution - Method 3 (Run script directly with bypass):**

```powershell
Get-ExecutionPolicy
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process -Force
.\deploy-safe.ps1 -commitMessage "Test SSH" -dryRun
```

**Solution - Method 4 (If you have admin rights):**

1. Right-click on PowerShell icon
2. Select "Run as Administrator"
3. Run:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope LocalMachine
```

## Quick Fix Commands

### Fix SSH Key (Run this first):
```powershell
ssh-keygen -t rsa -b 4096 -C "chandasudiptya@gmail.com"
# Press Enter 3 times (accept default location, no passphrase)
```

### Fix PowerShell Execution (Choose one):

**Option A - Current User (Recommended):**
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

**Option B - Bypass for this session:**
```powershell
powershell -ExecutionPolicy Bypass -File .\deploy-safe.ps1 -commitMessage "Test" -dryRun
```

**Option C - Process scope:**
```powershell
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process -Force
.\deploy-safe.ps1 -commitMessage "Test" -dryRun
```

## Test After Fixing

1. **Test SSH key generation:**
```powershell
ssh-keygen -t rsa -b 4096 -C "chandasudiptya@gmail.com"
# Press Enter 3 times
```

2. **Test PowerShell script:**
```powershell
.\deploy-safe.ps1 -commitMessage "Test" -dryRun
```

## Copy SSH Public Key to Server

After generating the SSH key, copy it to your server:

```powershell
type $env:USERPROFILE\.ssh\id_rsa.pub | ssh ship@ship.artisanbn.com "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && chmod 700 ~/.ssh"
```

Enter password when prompted: `Gya-z2K-ymL-q6H`

