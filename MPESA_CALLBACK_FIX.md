# M-Pesa Payment Confirmation Fix

## Problem
Payment keeps loading even after parent receives M-Pesa SMS confirmation.

## Solutions Applied

### 1. **More Aggressive Status Checking**
- Status check now queries M-Pesa API immediately if no payment_id found
- Polling frequency increased from 3 seconds to 2 seconds
- Polling duration increased to 3 minutes (90 attempts)

### 2. **Improved Callback Handling**
- Better error logging for callback debugging
- Handles failed/cancelled payments properly
- CORS headers added for callback endpoint

### 3. **Enhanced API Query**
- Queries M-Pesa API directly if callback hasn't arrived
- Handles multiple response formats
- Better reconciliation logic

## Important: Callback URL Configuration

### For Ngrok:
1. **Update Callback URL in Settings:**
   - Go to Settings → M-Pesa Settings
   - Set Callback URL to: `https://your-ngrok-url.ngrok-free.app/masomo/mpesa/callback`
   - Click "Use Current URL" button if available

2. **Verify Callback is Accessible:**
   - Test URL: `https://your-ngrok-url.ngrok-free.app/masomo/mpesa/callback`
   - Should return JSON response (even if error)
   - Must be publicly accessible (not behind firewall)

### For Production:
1. Set Callback URL in Settings to your production domain
2. Ensure HTTPS is enabled
3. Verify the endpoint is publicly accessible

## Debugging Steps

### Check Server Logs:
Look for these log entries:
- `M-Pesa Callback Received:` - Callback was received
- `M-Pesa API Query Result:` - Status check results
- `M-Pesa Payment: Updated from API` - Payment detected from API query
- `M-Pesa Reconciliation:` - Payment reconciliation status

### Common Issues:

**1. Callback URL Not Accessible:**
- Check if ngrok is running
- Verify callback URL in Settings matches ngrok URL
- Test the callback URL directly in browser

**2. Callback Not Reaching Server:**
- Check server error logs
- Verify .htaccess allows POST requests
- Check firewall/security settings

**3. Transaction Not Reconciling:**
- Check database: `SELECT * FROM mpesa_transactions WHERE checkout_request_id = 'YOUR_ID'`
- Check payments table: `SELECT * FROM payments WHERE mpesa_transaction_id = 'YOUR_ID'`
- Look for error messages in logs

## Manual Reconciliation

If automatic confirmation fails:
1. Enter M-Pesa receipt number manually
2. Click "Reconcile Payment"
3. Payment will be processed immediately

## Testing

After making changes:
1. Make a test payment
2. Watch browser console for polling messages
3. Check server logs for callback/API query activity
4. Payment should confirm within 30-60 seconds

