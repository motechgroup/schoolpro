# Quick Fix: Login Credentials Not Working

## Problem
The default admin password hash in the database was incorrect, causing login failures.

## Solution

### Option 1: If you haven't imported the database yet
Simply import the updated `database/schema.sql` file - it now contains the correct password hash.

### Option 2: If you've already imported the database
Run the fix script:

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select the `masomo_school_db` database
3. Click on "SQL" tab
4. Copy and paste the contents of `database/fix_admin_password.sql`
5. Click "Go"

Or via command line:
```bash
mysql -u root -p masomo_school_db < database/fix_admin_password.sql
```

### Option 3: Manual Update
Run this SQL query in phpMyAdmin:

```sql
UPDATE users 
SET password = '$2y$10$BJPPKBgYDm5qSKBZ517IKOb1M2S1pD.RhwX2PorA6.6.QQaPeuz2y'
WHERE email = 'admin@school.co.ke';
```

## Verify Fix

After running the fix, try logging in with:
- **Email**: admin@school.co.ke
- **Password**: admin123

## Still Not Working?

If login still fails, check:

1. **Database Connection**: Verify `app/config/config.php` has correct database credentials
2. **User Exists**: Run this query to check:
   ```sql
   SELECT id, email, first_name, last_name, status FROM users WHERE email = 'admin@school.co.ke';
   ```
3. **Role Exists**: Ensure roles table has data:
   ```sql
   SELECT * FROM roles;
   ```
4. **Check PHP Error Log**: Look for errors in Apache/PHP error logs
5. **Session Issues**: Clear browser cookies and try again

## Test Password Hash

You can test if the password hash is correct by running this PHP code:

```php
<?php
$hash = '$2y$10$BJPPKBgYDm5qSKBZ517IKOb1M2S1pD.RhwX2PorA6.6.QQaPeuz2y';
$password = 'admin123';
var_dump(password_verify($password, $hash)); // Should output: bool(true)
```

