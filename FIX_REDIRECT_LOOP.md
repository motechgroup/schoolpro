# Fix Redirect Loop Issue

## Problem
If you're experiencing a redirect loop with URLs like:
`https://yourdomain.com/index.php/auth/login/auth/login/auth/login...`

## Solution

### Step 1: Update .htaccess RewriteBase

If your site is installed at **root** (e.g., `https://yourdomain.com`), set RewriteBase to `/`:

```apache
RewriteBase /
```

If your site is in a **subdirectory** (e.g., `https://yourdomain.com/masomo`), set RewriteBase to your subdirectory:

```apache
RewriteBase /masomo/
```

**Current .htaccess location:** Root of your installation

### Step 2: Set BASE_URL in .env (Optional but Recommended)

Create or edit `.env` file in the root directory and set:

```env
BASE_URL=https://yourdomain.com
```

For root installation:
```env
BASE_URL=https://yourdomain.com
```

For subdirectory installation:
```env
BASE_URL=https://yourdomain.com/masomo
```

### Step 3: Clear Browser Cache

Clear your browser cache or use incognito/private mode to test.

### Step 4: Verify

1. Visit your domain: `https://yourdomain.com`
2. You should be redirected to: `https://yourdomain.com/auth/login`
3. The redirect loop should be gone.

## Automatic Fix

The latest code update automatically detects the installation path from `SCRIPT_NAME` instead of `REQUEST_URI`, which prevents the loop. However, you still need to ensure `.htaccess` has the correct `RewriteBase`.

## Troubleshooting

If the issue persists:

1. **Check your .htaccess RewriteBase** - Must match your installation path
2. **Check your .env BASE_URL** - Should match your domain
3. **Check file permissions** - .htaccess should be readable (644)
4. **Check Apache mod_rewrite** - Must be enabled
5. **Check server error logs** - Look for any PHP errors

## Testing

After making changes:
1. Clear browser cache
2. Visit: `https://yourdomain.com`
3. Should redirect cleanly to: `https://yourdomain.com/auth/login`

