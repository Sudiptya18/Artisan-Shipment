# Environment Protection Guide

## 🛡️ Protecting Local vs Live Environments

This guide ensures your local development environment never affects your live production server.

## 🔒 What Gets Protected

### Files That NEVER Get Deployed:

1. **Environment Files:**
   - `.env` - Your local environment variables
   - `.env.local` - Local overrides
   - `.env.development` - Development settings
   - `.env.testing` - Test environment

2. **Local Development Files:**
   - `composer.phar` - Local Composer binary
   - `phpunit.phar` - Local PHPUnit binary
   - IDE settings (`.vscode/settings.json`, `.idea/workspace.xml`)
   - Local test databases (`*.sqlite`)

3. **Build Artifacts:**
   - `public/hot` - Vite dev server indicator
   - `public/storage` - Local storage symlink
   - `node_modules/` - Dependencies (should be installed on server)

4. **Cache & Logs:**
   - `storage/logs/*.log` - Local log files
   - `storage/framework/cache/*` - Local cache
   - `storage/framework/sessions/*` - Local sessions

## ✅ What DOES Get Deployed:

- ✅ Application code (PHP, Vue, etc.)
- ✅ Configuration files (except .env)
- ✅ Database migrations
- ✅ Routes, controllers, models
- ✅ Frontend assets (after build)
- ✅ Documentation

## 🚀 Safe Deployment Commands

### Recommended: Use Safe Deployment Script

```powershell
# Safe deployment (protects local files)
.\deploy-safe.ps1 -commitMessage "Your changes"

# Test what would be deployed (dry run)
.\deploy-safe.ps1 -commitMessage "Test" -dryRun

# Push to GitHub only (skip live)
.\deploy-safe.ps1 -commitMessage "Update" -skipDeploy
```

### Regular Deployment (Still Safe)

```powershell
# Regular deployment (also protects .env via .gitignore)
.\deploy.ps1 -commitMessage "Your changes"
```

## 🔍 How Protection Works

### 1. .gitignore Protection

Your `.gitignore` already excludes:
```
.env
.env.backup
.env.production
node_modules/
vendor/
storage/logs/*
```

### 2. Deployment Script Protection

`deploy-safe.ps1`:
- ✅ Checks for protected files before committing
- ✅ Prevents staging of `.env` files
- ✅ Shows what will be committed
- ✅ Blocks deployment if protected files detected

### 3. Live Server Protection

`deploy-live-safe.ps1`:
- ✅ Backs up existing `.env` on server
- ✅ Restores `.env` after git pull (if overwritten)
- ✅ Uses `.env.production` as source of truth
- ✅ Never overwrites production configuration

## 📋 Pre-Deployment Checklist

Before deploying, verify:

- [ ] `.env` is in `.gitignore` (already done)
- [ ] No `.env` files are staged: `git status`
- [ ] Local changes don't include sensitive data
- [ ] Test with `-dryRun` first
- [ ] Review what will be committed

## 🧪 Testing Deployment Safety

### Test 1: Check What Would Be Committed

```powershell
# See what files are changed
git status

# See what would be committed
git diff --cached --name-only
```

### Test 2: Dry Run

```powershell
# Test deployment without actually deploying
.\deploy-safe.ps1 -commitMessage "Test" -dryRun
```

### Test 3: Verify .env Protection

```powershell
# Try to add .env (should fail or be ignored)
git add .env
git status  # Should show .env as untracked or ignored
```

## 🚨 Common Mistakes to Avoid

### ❌ DON'T:

1. **Don't commit .env files:**
   ```powershell
   # BAD - Never do this!
   git add .env
   git commit -m "Update config"
   ```

2. **Don't hardcode local paths:**
   ```php
   // BAD
   $path = 'C:\xampp\htdocs\Artisan-Shipment\storage';
   
   // GOOD
   $path = storage_path();
   ```

3. **Don't commit local database:**
   ```powershell
   # BAD
   git add database/database.sqlite
   ```

4. **Don't override server .env:**
   ```bash
   # BAD - On server
   git pull
   # If .env gets overwritten, restore it immediately!
   ```

### ✅ DO:

1. **Use environment variables:**
   ```php
   // GOOD
   $dbHost = env('DB_HOST', 'localhost');
   ```

2. **Use .env.example:**
   - Keep `.env.example` in Git
   - Document all required variables
   - Never commit actual `.env`

3. **Use safe deployment scripts:**
   ```powershell
   # GOOD
   .\deploy-safe.ps1 -commitMessage "Update"
   ```

4. **Backup before deployment:**
   ```bash
   # On server - before pulling
   cp .env .env.backup
   ```

## 🔧 Server-Side Protection

### Create .env.production on Server

On your live server, create a protected production config:

```bash
# SSH into server
ssh username@ship.artisanbn.com

# Navigate to project
cd /path/to/project

# Create production .env template (if not exists)
cp .env .env.production

# Make it read-only (optional)
chmod 400 .env.production
```

### Post-Deployment Script on Server

Create `post-deploy.sh` on server:

```bash
#!/bin/bash
# Post-deployment script to protect .env

cd /path/to/project

# Restore .env from production template if overwritten
if [ -f .env.production ] && [ ! -f .env ]; then
    cp .env.production .env
    echo "✓ .env restored from .env.production"
fi

# Ensure .env has correct permissions
chmod 600 .env

# Clear caches
php artisan config:clear
php artisan cache:clear
```

## 📝 Environment-Specific Files

### Local Development (.env.local)
```env
APP_ENV=local
APP_DEBUG=true
DB_DATABASE=artisan_shipment_local
```

### Production (.env on server)
```env
APP_ENV=production
APP_DEBUG=false
DB_DATABASE=artisan_shipment_prod
```

## ✅ Verification Commands

### Check What's Ignored:
```powershell
git status --ignored
```

### Check What Would Be Pushed:
```powershell
git diff origin/main --name-only
```

### Verify .env is Protected:
```powershell
git check-ignore .env
# Should output: .env
```

## 🎯 Summary

✅ **Always Use:**
- `deploy-safe.ps1` for deployment
- `.gitignore` to exclude local files
- Environment variables for configuration

✅ **Never Commit:**
- `.env` files
- Local development files
- Sensitive data

✅ **Always Protect:**
- Production `.env` on server
- Server-specific configurations
- Database credentials

## 🆘 Troubleshooting

### Issue: ".env was overwritten on server"
**Solution:**
```bash
# On server
cp .env.production .env
php artisan config:clear
```

### Issue: "Local files are being committed"
**Solution:**
1. Check `.gitignore`
2. Use `deploy-safe.ps1`
3. Review `git status` before committing

### Issue: "Deployment script fails"
**Solution:**
1. Check SSH connection
2. Verify server path
3. Ensure Git is initialized on server
4. Check file permissions

---

**Remember:** When in doubt, use `deploy-safe.ps1` with `-dryRun` first!
