# Ngrok Setup for Development

## Quick Setup

When using ngrok with a subdirectory installation (like `/masomo`):

### Step 1: Update .htaccess

Ensure `.htaccess` has the correct RewriteBase:

```apache
RewriteBase /masomo/
```

### Step 2: Set BASE_URL (Optional but Recommended)

Create or edit `.env` file:

```env
BASE_URL=https://your-ngrok-url.ngrok-free.app/masomo
```

Replace `your-ngrok-url.ngrok-free.app` with your actual ngrok URL.

### Step 3: Start ngrok

```bash
ngrok http 80 --host-header="localhost"
```

Or if your local server runs on port 8080:

```bash
ngrok http 8080 --host-header="localhost"
```

### Step 4: Access via ngrok

Visit: `https://your-ngrok-url.ngrok-free.app/masomo`

## Automatic Detection

The system automatically detects:
- ✅ Protocol (HTTPS for ngrok)
- ✅ Host (your ngrok domain)
- ✅ Path (/masomo from SCRIPT_NAME)

So manual BASE_URL configuration is usually not needed.

## Troubleshooting

**404 Not Found:**
- Check `.htaccess` RewriteBase matches your subdirectory
- Verify mod_rewrite is enabled
- Check file permissions

**Redirect Loops:**
- Clear browser cache
- Verify BASE_URL in `.env` matches ngrok URL
- Check server error logs

**Ngrok Warning Page:**
- Click "Visit Site" button on ngrok warning page
- Or add ngrok domain to bypass list (not recommended for production)

## Notes

- Ngrok URLs change on free plan (unless using reserved domain)
- Update BASE_URL in `.env` if ngrok URL changes
- Use ngrok for testing only, not production

