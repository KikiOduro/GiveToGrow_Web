# Paystack Payment Integration for GiveToGrow

## Overview
This donation platform now uses Paystack for secure payment processing. Users can donate to schools using various payment methods including Mobile Money, Cards, and Bank Transfers (via Paystack).

## Files Structure

### Configuration Files
- **`settings/paystack_config.php`** - Paystack API configuration and helper functions

### Backend Processing
- **`actions/paystack_init_transaction.php`** - Initializes Paystack payment
- **`actions/paystack_verify_payment.php`** - Verifies payment and processes donations

### Frontend Pages
- **`checkout.php`** - Payment checkout page with Paystack integration
- **`paystack_callback.php`** - Callback handler after Paystack redirect
- **`donation_success.php`** - Success page after verified donation

## Setup Instructions

### 1. Get Paystack API Keys

1. Sign up at [https://paystack.com](https://paystack.com)
2. Navigate to **Settings → API Keys & Webhooks**
3. Copy your **Test Secret Key** (starts with `sk_test_`)
4. Copy your **Test Public Key** (starts with `pk_test_`)

### 2. Configure Paystack Keys

Edit `settings/paystack_config.php` and replace the placeholder keys:

```php
// Replace these with your actual keys
define('PAYSTACK_SECRET_KEY', 'sk_test_YOUR_SECRET_KEY_HERE');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_YOUR_PUBLIC_KEY_HERE');
```

### 3. Update Application URL

In `settings/paystack_config.php`, update the base URL to match your setup:

```php
// For local development (MAMP)
define('APP_BASE_URL', 'http://localhost:8888/GiveToGrow_Web');

// For production (replace with your domain)
define('APP_BASE_URL', 'https://yourdomain.com');
```

### 4. Configure Currency

The default currency is set to **USD**. To change to Ghana Cedis (GHS):

In `settings/paystack_config.php`, find:
```php
'currency' => 'USD', // Change to 'GHS' for Ghana Cedis
```

And update to:
```php
'currency' => 'GHS',
```

## Payment Flow

```
User Journey:
1. Browse Schools → Select Items → Add to Cart
2. Review Cart → Click "Proceed to Payment"
3. Fill Billing Info → Click "Complete Donation"
4. Redirect to Paystack Gateway → Enter Payment Details
5. Paystack Redirects to paystack_callback.php
6. System Verifies Payment → Creates Donations
7. Redirect to donation_success.php → Show Receipt
```

### Technical Flow

1. **Initialization** (`checkout.php` → `paystack_init_transaction.php`)
   - User submits checkout form
   - System generates unique reference
   - Calls Paystack API to initialize transaction
   - Returns `authorization_url` for redirect

2. **Payment** (User → Paystack Gateway)
   - User is redirected to Paystack
   - Enters payment details (Card, Mobile Money, etc.)
   - Completes payment on Paystack

3. **Callback** (Paystack → `paystack_callback.php`)
   - Paystack redirects user back with reference
   - Page calls `paystack_verify_payment.php`

4. **Verification** (`paystack_verify_payment.php`)
   - Verifies transaction with Paystack API
   - Validates payment amount matches cart total
   - Creates donation records in database
   - Updates school funding progress
   - Clears user's cart

5. **Success** (`donation_success.php`)
   - Displays donation receipt
   - Shows donation details
   - Provides navigation options

## Database Updates Required

Ensure your `donations` table has these columns:

```sql
ALTER TABLE donations 
ADD COLUMN quantity INT DEFAULT 1 AFTER amount,
ADD COLUMN payment_method VARCHAR(50) DEFAULT 'card' AFTER quantity,
ADD COLUMN donor_name VARCHAR(255) AFTER payment_status,
ADD COLUMN donor_email VARCHAR(255) AFTER donor_name,
ADD COLUMN transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER donor_email;

-- Add indexes for performance
ALTER TABLE donations ADD INDEX idx_donation_user (user_id);
ALTER TABLE donations ADD INDEX idx_donation_date (transaction_date);
```

Run the SQL file: `db/update_donations_safe.sql`

## Testing

### Test Cards (Paystack Test Mode)

**Successful Payment:**
- Card Number: `4084084084084081`
- Expiry: Any future date (e.g., 12/25)
- CVV: Any 3 digits (e.g., 123)

**Declined Payment:**
- Card Number: `5060666666666666666`

**OTP Testing:**
- When prompted, use: `123456`

### Test Mobile Money
- Use any valid phone number format
- In test mode, all transactions will succeed

### Testing Steps

1. **Login** to your account
2. **Browse Schools** and add items to cart
3. Go to **Cart** and click "Proceed to Payment"
4. Fill in your **email** (use a real email for receipts)
5. Click "**Complete Donation**"
6. You'll be redirected to Paystack
7. Use test card details above
8. Complete payment
9. You should be redirected back and see success page

## Security Features

✅ **SSL/HTTPS Required** - All API calls use HTTPS
✅ **Amount Verification** - Server validates payment amount matches cart
✅ **Transaction Atomicity** - Database transactions with rollback on failure
✅ **Reference Tracking** - Unique references prevent duplicate donations
✅ **Session Validation** - User must be logged in
✅ **Payment Status Check** - Only 'success' status accepted

## Error Handling

Common errors and solutions:

| Error | Cause | Solution |
|-------|-------|----------|
| "Invalid email address" | Email format invalid | Enter valid email |
| "Amount mismatch" | Cart modified during payment | Start checkout again |
| "Cart is empty" | Cart cleared before payment | Add items to cart |
| "Session expired" | User logged out | Login again |
| "Connection error" | API not reachable | Check internet, API keys |

## Production Deployment

### Before Going Live:

1. **Switch to Live Keys**
   ```php
   define('PAYSTACK_SECRET_KEY', 'sk_live_YOUR_LIVE_KEY');
   define('PAYSTACK_PUBLIC_KEY', 'pk_live_YOUR_LIVE_KEY');
   define('APP_ENVIRONMENT', 'live');
   ```

2. **Update Base URL**
   ```php
   define('APP_BASE_URL', 'https://yourdomain.com');
   ```

3. **Enable SSL Certificate** - Paystack requires HTTPS in production

4. **Test Thoroughly** - Use test mode first, then switch to live

5. **Setup Webhooks** (Optional but recommended)
   - Go to Paystack Dashboard → Settings → Webhooks
   - Add webhook URL: `https://yourdomain.com/webhooks/paystack.php`
   - Select events: `charge.success`, `charge.failed`

## Support

- **Paystack Documentation:** [https://paystack.com/docs](https://paystack.com/docs)
- **Test API:** [https://paystack.com/docs/api/](https://paystack.com/docs/api/)
- **Support:** support@paystack.com

## Currency Support

Paystack supports:
- 🇬🇭 Ghana Cedis (GHS)
- 🇳🇬 Nigerian Naira (NGN)
- 🇺🇸 US Dollars (USD)
- 🇿🇦 South African Rand (ZAR)
- 🇰🇪 Kenyan Shillings (KES)

Update the `currency` parameter in `paystack_config.php` accordingly.

## Troubleshooting

### Payment not verifying?
- Check `error_log` in your PHP logs
- Verify API keys are correct
- Ensure callback URL is accessible
- Check if cart items exist

### Redirecting to wrong URL?
- Update `APP_BASE_URL` in config
- Clear browser cache
- Check `.htaccess` for redirects

### Amount showing in cents/pesewas?
- Paystack uses smallest currency unit
- System automatically converts: $1.00 = 100 cents
- Display uses `number_format($amount, 2)`

---

**Made with ❤️ for GiveToGrow**
