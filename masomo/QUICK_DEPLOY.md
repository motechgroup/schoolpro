# Quick Deployment Guide

## 🚀 Fastest Way to Deploy

### Step 1: Upload Files
Upload all files to your server (via FTP, SFTP, or cPanel File Manager)

### Step 2: Run Installer
1. Visit: `https://yourdomain.com/install.php`
2. Follow the 4-step wizard
3. Done! ✅

**That's it!** The installer handles everything automatically.

---

## 📋 What the Installer Does

1. ✅ Tests database connection
2. ✅ Creates database if needed
3. ✅ Generates `.env` configuration file
4. ✅ Imports database schema
5. ✅ Sets up uploads directory
6. ✅ Creates installation lock file

---

## 🔧 Manual Configuration (If Needed)

If you prefer manual setup:

1. **Copy `.env.example` to `.env`**
2. **Edit `.env` with your settings:**
   ```env
   ENVIRONMENT=production
   DB_HOST=localhost
   DB_NAME=your_database
   DB_USER=your_user
   DB_PASS=your_password
   BASE_URL=https://yourdomain.com
   ```
3. **Import database:** `database/schema.sql`
4. **Set permissions:** `chmod 755 public/uploads`

---

## 🌐 Base URL Auto-Detection

**No manual configuration needed!** The app automatically detects:
- ✅ Protocol (HTTP/HTTPS)
- ✅ Domain name
- ✅ Installation path

Only set `BASE_URL` in `.env` if you need to override.

---

## 🔒 After Installation

1. **Delete `install.php`** (for security)
2. **Login:** `admin@school.co.ke` / `admin123`
3. **Change password** immediately!

---

## ⚠️ Troubleshooting

**Can't access install.php?**
- Check file permissions (644)
- Verify PHP is working

**Database connection fails?**
- Verify credentials
- Check MySQL is running
- Ensure user has permissions

**404 errors?**
- Check `.htaccess` exists
- Verify `mod_rewrite` is enabled
- Update `RewriteBase` in `.htaccess` if in subdirectory

---

## 📖 Full Documentation

See `DEPLOYMENT.md` for detailed instructions.

---

**Need Help?** Check server error logs or contact support.

