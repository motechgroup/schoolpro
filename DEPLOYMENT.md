# Deployment Guide

## Quick Deployment Instructions

This guide will help you deploy SchoolPro to a production server.

## Option 1: Using the Installer (Recommended)

### Step 1: Upload Files

1. Upload all files to your web server via FTP/SFTP or using a file manager
2. Ensure the directory structure is maintained
3. Recommended directory: `public_html` or your domain's root directory

### Step 2: Set Permissions

**Linux/Unix:**
```bash
chmod 755 public/uploads
chmod 644 .htaccess
chmod 644 app/config/config.php
```

**Or via File Manager:**
- `public/uploads` folder: 755
- `.htaccess` file: 644
- `app/config/config.php`: 644

### Step 3: Run Installer

1. Navigate to: `https://yourdomain.com/install.php`
2. Follow the installation wizard:
   - **Step 1**: Enter database credentials
   - **Step 2**: Configure environment settings
   - **Step 3**: Import database schema
   - **Step 4**: Installation complete

### Step 4: Security

After installation:
1. **Delete or rename** `install.php` for security
2. Review `.env` file settings
3. Change default admin password immediately

## Option 2: Manual Installation

### Step 1: Database Setup

1. Create a MySQL database via cPanel or phpMyAdmin
2. Note down: database name, username, password, and host

### Step 2: Configuration

1. Copy `.env.example` to `.env`
2. Edit `.env` file with your settings:

```env
ENVIRONMENT=production
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
BASE_URL=https://yourdomain.com
APP_NAME=Your School Name
```

### Step 3: Import Database

**Via phpMyAdmin:**
1. Select your database
2. Click "Import" tab
3. Choose `database/schema.sql`
4. Click "Go"

**Via Command Line:**
```bash
mysql -u your_user -p your_database < database/schema.sql
```

### Step 4: Verify

1. Visit: `https://yourdomain.com`
2. Login with default credentials:
   - Email: `admin@school.co.ke`
   - Password: `admin123`
3. Change password immediately

## Base URL Auto-Detection

The application automatically detects your BASE_URL based on:
- Protocol (HTTP/HTTPS)
- Domain name
- Installation path

**No manual configuration needed** unless:
- You want to override the auto-detected URL
- You're using a CDN or reverse proxy
- You have special routing requirements

To override, set `BASE_URL` in your `.env` file:
```env
BASE_URL=https://yourdomain.com
```

## Environment Detection

The application automatically detects environment:
- **Development**: localhost, 127.0.0.1, or any local IP
- **Production**: Any other domain

You can manually set it in `.env`:
```env
ENVIRONMENT=production
```

## File Structure

```
your-domain.com/
├── app/
│   ├── config/
│   │   └── config.php
│   ├── controllers/
│   ├── models/
│   ├── views/
│   └── ...
├── public/
│   └── uploads/
├── database/
│   └── schema.sql
├── .env (create from .env.example)
├── .htaccess
├── index.php
└── install.php (delete after installation)
```

## Server Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Extensions**: 
  - PDO
  - PDO_MySQL
  - GD (for image processing)
  - OpenSSL
  - mbstring
  - fileinfo
- **Apache**: mod_rewrite enabled
- **Permissions**: Write access to `public/uploads/`

## Apache Configuration

### .htaccess (Already Included)

The `.htaccess` file handles:
- Clean URLs
- Security headers
- File protection
- Route handling

If your site is in a subdirectory, update `RewriteBase` in `.htaccess`:
```apache
RewriteBase /your-subdirectory/
```

## Nginx Configuration

If using Nginx, add this to your server block:

```nginx
location / {
    try_files $uri $uri/ /index.php?url=$request_uri;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
    fastcgi_param PATH_INFO $fastcgi_path_info;
}

location ~ /\. {
    deny all;
}
```

## Post-Deployment Checklist

- [ ] Delete or rename `install.php`
- [ ] Set proper file permissions (755 for folders, 644 for files)
- [ ] Update `.env` with production values
- [ ] Change default admin password
- [ ] Configure SMTP for email (if needed)
- [ ] Set up M-Pesa credentials (if using)
- [ ] Test all major features
- [ ] Set up SSL certificate (HTTPS)
- [ ] Configure backups
- [ ] Review error logging settings

## Troubleshooting

### Issue: "Database connection failed"

**Solution:**
1. Check database credentials in `.env`
2. Verify database user has proper permissions
3. Ensure MySQL service is running
4. Check firewall settings

### Issue: "404 Not Found" or routing doesn't work

**Solution:**
1. Ensure `.htaccess` file exists
2. Check `mod_rewrite` is enabled
3. Verify `RewriteBase` in `.htaccess` matches your path
4. Check Apache AllowOverride is set to "All"

### Issue: "Permission denied" for uploads

**Solution:**
```bash
chmod 755 public/uploads
chown www-data:www-data public/uploads  # Linux
```

### Issue: Base URL is incorrect

**Solution:**
1. Set `BASE_URL` manually in `.env` file
2. Clear browser cache
3. Check `.htaccess` RewriteBase setting

## Support

For issues or questions:
1. Check error logs: `public/uploads/` or server error logs
2. Review `.env` configuration
3. Verify server requirements are met
4. Check file permissions

## Updating the Application

1. Backup database and files
2. Upload new files (overwrite existing)
3. Run any new migration scripts in `database/` folder
4. Clear browser cache
5. Test functionality

## Security Recommendations

1. **Always use HTTPS** in production
2. **Delete install.php** after installation
3. **Keep .env file secure** (don't commit to version control)
4. **Regular updates**: Keep PHP and MySQL updated
5. **Backups**: Set up automated backups
6. **Strong passwords**: Use strong passwords for admin accounts
7. **File permissions**: Set proper file permissions
8. **Error reporting**: Disable error display in production (already handled)

## Default Login Credentials

After installation, login with:
- **Email**: `admin@school.co.ke`
- **Password**: `admin123`

**⚠️ IMPORTANT**: Change this password immediately after first login!

---

**Need Help?** Check the main README.md or SETUP.md for more details.

