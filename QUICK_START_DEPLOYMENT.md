# Quick Start - Deployment Setup

## 🚀 Fast Setup (5 Minutes)

### Step 1: Configure Server Details

Edit `deploy-live.ps1` and update these lines:
```powershell
$serverUser = "your_cpanel_username"      # Your cPanel username
$serverHost = "ship.artisanbn.com"        # Your domain
$serverPath = "/home/artisanbn/public_html/ship.artisanbn.com"  # Your server path
```

### Step 2: Test SSH Connection

```powershell
ssh your_username@ship.artisanbn.com
```

If it works, you're ready! If not, set up SSH keys first.

### Step 3: Deploy!

```powershell
.\deploy.ps1 -commitMessage "Initial deployment setup"
```

That's it! 🎉

---

## 📝 Common Server Paths

**cPanel/Shared Hosting:**
- `/home/username/public_html/domain.com`
- `/home/username/public_html/ship.artisanbn.com`

**VPS/Dedicated:**
- `/var/www/html`
- `/var/www/domain.com`
- `/home/user/domain.com`

**Find your path:**
```bash
# SSH into server and run:
pwd
# Or check your hosting panel's file manager
```

---

## 🔑 SSH Key Setup (If Needed)

1. **Generate key:**
```powershell
ssh-keygen -t rsa -b 4096
```

2. **Copy to server:**
```powershell
ssh-copy-id username@ship.artisanbn.com
```

3. **Test:**
```powershell
ssh username@ship.artisanbn.com
```

---

## ⚡ Daily Usage

**After making changes locally (RECOMMENDED - Safe):**
```powershell
.\deploy-safe.ps1 -commitMessage "Description of changes"
```

**That's all you need!** The script will:
- ✅ Protect local files (especially .env)
- ✅ Commit your changes
- ✅ Push to GitHub
- ✅ Deploy to live server (safely)

**Alternative (still safe via .gitignore):**
```powershell
.\deploy.ps1 -commitMessage "Description of changes"
```

**⚠️ Important:** Always use `deploy-safe.ps1` to ensure your local `.env` and other local files never get pushed to live server!

---

## 🆘 Need Help?

See `DEPLOYMENT_GUIDE.md` for detailed instructions.
