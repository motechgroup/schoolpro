# Setup Instructions

## Quick Start Guide

### Step 1: Install XAMPP (Windows) or LAMP (Linux)

**Windows:**
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP to `C:\xampp`
3. Start Apache and MySQL from XAMPP Control Panel

**Linux:**
```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysql
```

### Step 2: Place Project Files

**Windows:**
```
Copy project to: C:\xampp\htdocs\masomo
```

**Linux:**
```bash
sudo cp -r masomo /var/www/html/
sudo chown -R www-data:www-data /var/www/html/masomo
```

### Step 3: Create Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "Import" tab
3. Select `database/schema.sql`
4. Click "Go" to import

Or via command line:
```bash
mysql -u root -p < database/schema.sql
```

### Step 4: Configure Application

Edit `app/config/config.php`:

```php
// Update these values if different
define('DB_HOST', 'localhost');
define('DB_NAME', 'masomo_school_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password

// Update base URL
define('BASE_URL', 'http://localhost/masomo');
```

### Step 5: Set Permissions

**Linux:**
```bash
sudo chmod 755 public/uploads
sudo chmod 644 .htaccess
```

**Windows:**
- Right-click `public/uploads` folder
- Properties → Security → Edit
- Give "Modify" permission to IIS_IUSRS or your user

### Step 6: Access Application

1. Open browser: http://localhost/masomo
2. Login with:
   - Email: admin@school.co.ke
   - Password: admin123

### Step 7: Change Default Password

1. After login, go to Profile/Settings
2. Change the default password immediately

## M-Pesa Setup (Optional)

### Sandbox Testing

1. Register at https://developer.safaricom.co.ke/
2. Create an app to get credentials
3. Update `app/config/config.php`:

```php
define('MPESA_CONSUMER_KEY', 'your_consumer_key');
define('MPESA_CONSUMER_SECRET', 'your_consumer_secret');
define('MPESA_SHORTCODE', 'your_shortcode');
define('MPESA_PASSKEY', 'your_passkey');
```

4. Set callback URL in Daraja portal:
   `http://yourdomain.com/masomo/api/mpesa/callback`

## Production Deployment

### Requirements

1. PHP 7.4+ with extensions:
   - PDO
   - PDO_MySQL
   - cURL
   - JSON
   - mbstring

2. MySQL 5.7+ or MariaDB 10.3+

3. Apache with mod_rewrite enabled

### Steps

1. Upload files to server via FTP/SFTP
2. Set proper file permissions:
   ```bash
   chmod 755 app/
   chmod 755 public/uploads
   chmod 644 .htaccess
   ```

3. Update `app/config/config.php`:
   - Set `ENVIRONMENT` to `'production'`
   - Update database credentials
   - Update `BASE_URL` to your domain
   - Configure M-Pesa production credentials

4. Import database on production server

5. Test all functionality

## Common Issues

### Issue: 404 Errors on Pages

**Solution:**
- Ensure mod_rewrite is enabled
- Check `.htaccess` file exists
- Verify Apache `AllowOverride All` is set

### Issue: Database Connection Failed

**Solution:**
- Verify MySQL is running
- Check credentials in `config.php`
- Ensure database exists

### Issue: File Upload Not Working

**Solution:**
- Check `public/uploads` folder permissions
- Verify PHP `upload_max_filesize` setting
- Check `php.ini` for upload settings

### Issue: M-Pesa Callback Not Working

**Solution:**
- Verify callback URL is publicly accessible
- Check server has SSL certificate (required for production)
- Review M-Pesa transaction logs in database

## Next Steps

1. Add your school logo to `public/uploads/redcoinlogo.png`
2. Configure school details in `app/config/config.php`
3. Add grades and classes
4. Create user accounts for staff
5. Add students and parents
6. Set up fee structure
7. Configure learning areas for CBC

## Support

For technical support, contact your system administrator.

