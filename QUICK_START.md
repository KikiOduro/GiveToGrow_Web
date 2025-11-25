# GiveToGrow Payment Integration - Quick Start

## 🚀 What's New

Your donation platform now has **Paystack payment integration**! Users can make secure donations using:
- 💳 **Credit/Debit Cards** (Visa, Mastercard, Verve)
- 📱 **Mobile Money** (MTN, Vodafone, AirtelTigo)
- 🏦 **Bank Transfers**
- 💰 **USSD**

## 📁 New Files Created

### Configuration
✅ `settings/paystack_config.php` - API keys and settings

### Payment Processing
✅ `actions/paystack_init_transaction.php` - Start payment
✅ `actions/paystack_verify_payment.php` - Verify and process donation

### User Interface
✅ `checkout.php` - Updated with Paystack integration
✅ `paystack_callback.php` - Payment return handler
✅ `cart.php` - Already connects to checkout.php ✓

### Documentation
✅ `PAYSTACK_SETUP.md` - Complete setup guide
✅ `QUICK_START.md` - This file

## ⚡ Quick Setup (3 Steps)

### Step 1: Get Paystack Keys (2 minutes)

1. Go to **[paystack.com](https://paystack.com)** and sign up
2. Navigate to **Settings → API Keys**
3. Copy your Test Keys:
   - Secret Key (sk_test_...)
   - Public Key (pk_test_...)

### Step 2: Configure Keys (1 minute)

Open `settings/paystack_config.php` and update:

```php
// Line 8-9: Replace with your keys
define('PAYSTACK_SECRET_KEY', 'sk_test_YOUR_KEY_HERE');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_YOUR_KEY_HERE');

// Line 17: Update to your URL
define('APP_BASE_URL', 'http://localhost:8888/GiveToGrow_Web');
```

### Step 3: Test Payment (2 minutes)

1. Login to your GiveToGrow account
2. Add items to cart
3. Click "Proceed to Payment"
4. Use test card: **4084084084084081**
5. Expiry: **12/25**, CVV: **123**
6. OTP: **123456**
7. Complete payment ✓

## 🎯 Payment Flow

```
Cart Page (cart.php)
    ↓ Click "Proceed to Payment"
Checkout Page (checkout.php)
    ↓ Fill info → Click "Complete Donation"
Initialize Payment (paystack_init_transaction.php)
    ↓ Redirect to Paystack
Paystack Payment Gateway
    ↓ User enters payment details
Callback Page (paystack_callback.php)
    ↓ Verify payment
Verify Payment (paystack_verify_payment.php)
    ↓ Create donations, update schools
Success Page (donation_success.php)
    ✓ Show receipt
```

## 🧪 Test Cards

| Card Number | Type | Result |
|-------------|------|--------|
| 4084084084084081 | Visa | ✅ Success |
| 5060666666666666666 | Mastercard | ❌ Declined |

**OTP for all test cards:** 123456

## 🔧 Configuration Options

### Change Currency (USD → GHS)

In `settings/paystack_config.php` line 30:
```php
'currency' => 'GHS', // Was 'USD'
```

### Update Logo/Branding

In `checkout.php` line 107:
```php
<img src="assets/logo.png" alt="GiveToGrow Logo"/>
```

## ✅ Verification Checklist

Before testing, verify:

- [ ] Paystack keys added to `paystack_config.php`
- [ ] APP_BASE_URL matches your local/server URL
- [ ] Database table `donations` has new columns (run `db/update_donations_safe.sql`)
- [ ] User is logged in
- [ ] Cart has items
- [ ] Internet connection active (for Paystack API)

## 📊 Database Update

Run this SQL if you haven't yet:

```sql
-- Run file: db/update_donations_safe.sql
-- Or execute these commands:

ALTER TABLE donations ADD COLUMN quantity INT DEFAULT 1 AFTER amount;
ALTER TABLE donations ADD COLUMN payment_method VARCHAR(50) DEFAULT 'card' AFTER quantity;
ALTER TABLE donations ADD COLUMN donor_name VARCHAR(255) AFTER payment_status;
ALTER TABLE donations ADD COLUMN donor_email VARCHAR(255) AFTER donor_name;
ALTER TABLE donations ADD COLUMN transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER donor_email;
```

## 🚨 Common Issues

### "Invalid API Key"
- Check you copied the full key (starts with `sk_test_`)
- No extra spaces before/after key
- Using test key in test mode

### "Connection Error"
- Check internet connection
- Verify Paystack API is up: [status.paystack.com](https://status.paystack.com)
- Check firewall isn't blocking HTTPS

### "Amount Mismatch"
- Don't modify cart during payment
- Start checkout process again

### "Cart is Empty"
- Add items to cart before checkout
- Don't clear cart during payment

## 📱 Mobile Money Testing

In test mode, any phone number will work:
- MTN: 0244123456
- Vodafone: 0501234567
- AirtelTigo: 0261234567

All will show success in test mode.

## 🔐 Security Features

✅ HTTPS encryption
✅ Amount verification
✅ Transaction atomicity
✅ Unique references
✅ Payment status validation
✅ Session management

## 🎉 Go Live Checklist

When ready for production:

1. Get **Live API Keys** from Paystack
2. Update keys in `paystack_config.php`
3. Change `APP_ENVIRONMENT` to `'live'`
4. Update `APP_BASE_URL` to your domain
5. Enable SSL certificate (HTTPS)
6. Test with small real payment
7. Monitor first few transactions

## 📞 Support

- **Paystack Docs:** [paystack.com/docs](https://paystack.com/docs)
- **API Reference:** [paystack.com/docs/api](https://paystack.com/docs/api)
- **Support Email:** support@paystack.com

## 🎓 What Happens During Payment?

1. **User clicks "Complete Donation"**
   - Form validates (email, name)
   - Calls `paystack_init_transaction.php`

2. **System initializes payment**
   - Creates unique reference (GTG-123-1234567890)
   - Sends amount + email to Paystack
   - Gets back `authorization_url`

3. **User redirects to Paystack**
   - Enters card details or selects mobile money
   - Completes payment securely on Paystack

4. **Paystack redirects back**
   - Goes to `paystack_callback.php` with reference
   - Page calls `paystack_verify_payment.php`

5. **System verifies payment**
   - Confirms with Paystack API
   - Checks amount matches cart total
   - Creates donation records
   - Updates school funding
   - Clears cart

6. **Success!**
   - Shows `donation_success.php` with receipt
   - Email sent (if configured)
   - User can continue donating or view history

## 💡 Pro Tips

- Always test in **test mode** first
- Keep test and live keys separate
- Monitor error logs: `error_log` in PHP
- Use real email for testing to receive receipts
- Test edge cases: empty cart, expired session
- Verify donation records in database

---

**Ready to test?** Follow Step 1-3 above! 🚀

**Need help?** Check `PAYSTACK_SETUP.md` for detailed documentation.
