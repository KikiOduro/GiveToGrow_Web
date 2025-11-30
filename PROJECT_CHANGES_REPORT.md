# GiveToGrow Web Application - Project Changes Report

**Project Name:** GiveToGrow - School Donation Platform  
**Report Date:** November 30, 2025  
**Live Site:** http://169.239.251.102:442/~akua.oduro  

---

## Table of Contents
1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Database Schema](#database-schema)
4. [Features Implemented](#features-implemented)
5. [Technical Changes & Bug Fixes](#technical-changes--bug-fixes)
6. [File Structure](#file-structure)
7. [Security Implementations](#security-implementations)
8. [Payment Integration](#payment-integration)
9. [Admin System](#admin-system)
10. [Future Recommendations](#future-recommendations)

---

## 1. Project Overview

GiveToGrow is a web-based e-commerce donation platform that connects donors with schools in need across Ghana. The platform allows users to browse schools, view their specific needs, and make donations through a secure payment gateway.

### Core Functionality
- **School Management:** Admin can add, edit, and manage schools
- **Needs Management:** Admin can add, edit, and delete school needs/items
- **Shopping Cart:** Users can add donation items to cart
- **Payment Processing:** Integrated Paystack payment gateway (GHS currency)
- **Impact Tracking:** Donors can view their donation history and impact
- **User Authentication:** Separate roles for customers and administrators

---

## 2. System Architecture

### Technology Stack
| Component | Technology |
|-----------|------------|
| Backend | PHP 7.4+ |
| Database | MySQL (MariaDB) |
| Frontend | HTML5, TailwindCSS, JavaScript |
| Payment Gateway | Paystack API |
| Notifications | SweetAlert2 |
| Server | Apache (MAMP locally, Linux server for production) |

### Architecture Pattern
The application follows an **MVC-like pattern**:
- **Models:** `/models/` - Database interaction logic
- **Views:** `/views/` - User-facing pages
- **Controllers:** `/controllers/` - Business logic
- **Actions:** `/actions/` - Form handlers and API endpoints

---

## 3. Database Schema

### Tables Structure

#### `users` Table
```sql
- user_id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_name (VARCHAR 100)
- user_email (VARCHAR 100, UNIQUE)
- user_password (VARCHAR 255, hashed)
- user_role (ENUM: 'customer', 'admin')
- created_at (TIMESTAMP)
```

#### `schools` Table
```sql
- school_id (INT, PRIMARY KEY, AUTO_INCREMENT)
- school_name (VARCHAR 200)
- location (VARCHAR 200)
- country (VARCHAR 100)
- description (TEXT)
- image_url (VARCHAR 500)
- total_students (INT)
- fundraising_goal (DECIMAL 10,2)
- amount_raised (DECIMAL 10,2)
- is_verified (BOOLEAN)
- status (ENUM: 'active', 'inactive')
- created_at, updated_at (TIMESTAMPS)
```

#### `school_needs` Table
```sql
- need_id (INT, PRIMARY KEY, AUTO_INCREMENT)
- school_id (INT, FOREIGN KEY)
- item_name (VARCHAR 200)
- item_description (TEXT)
- item_category (ENUM: 'Books', 'TextBooks', 'Technology', 'Furniture', 
                 'Sports Equipment', 'Art Supplies', 'Laboratory Equipment',
                 'Musical Instruments', 'School Supplies', 'Infrastructure',
                 'Uniforms', 'Clothes', 'Desks', 'Library Resources', 'Computers')
- unit_price (DECIMAL 10,2)
- quantity_needed (INT)
- quantity_donated (INT, DEFAULT 0)
- image_url (VARCHAR 500)
- priority (ENUM: 'low', 'medium', 'high', 'urgent')
- status (ENUM: 'active', 'fulfilled', 'inactive')
- created_at, updated_at (TIMESTAMPS)
```

#### `cart` Table
```sql
- cart_id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY)
- need_id (INT, FOREIGN KEY)
- quantity (INT)
- created_at (TIMESTAMP)
```

#### `donations` Table
```sql
- donation_id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY)
- need_id (INT, FOREIGN KEY)
- school_id (INT, FOREIGN KEY)
- quantity (INT)
- amount (DECIMAL 10,2)
- payment_reference (VARCHAR 100)
- payment_status (ENUM: 'pending', 'completed', 'failed')
- transaction_date (TIMESTAMP)
```

---

## 4. Features Implemented

### 4.1 Public Features (Customer-Facing)

| Feature | Description | File(s) |
|---------|-------------|---------|
| Home Page | Landing page with featured schools | `/views/index.php` |
| Schools Listing | Browse all active schools | `/views/schools.php` |
| School Details | View school info and donation needs | `/views/school_detail.php` |
| Shopping Cart | Add/remove items, adjust quantities | `/views/cart.php` |
| Checkout | Review order before payment | `/views/checkout.php` |
| Payment Processing | Paystack integration | `/views/paystack_callback.php` |
| Donation Success | Confirmation page with all items | `/views/donation_success.php` |
| My Impact | View donation history | `/views/my_impact.php` |
| My Updates | School updates and notifications | `/views/my_updates.php` |
| User Registration | Create customer account | `/login/register.php` |
| User Login | Authenticate users | `/login/login.php` |

### 4.2 Admin Features

| Feature | Description | File(s) |
|---------|-------------|---------|
| Admin Dashboard | Statistics overview, school listing | `/admin/dashboard.php` |
| Add School | Create new school entries | `/admin/add_school.php` |
| Manage Schools | Edit/delete schools | `/admin/manage_schools.php` |
| Add School Need | Add items schools need | `/admin/add_need.php` |
| Manage Needs | Edit/delete school needs | `/admin/manage_needs.php` |
| Post Updates | Send updates to donors | `/admin/post_update.php` |

---

## 5. Technical Changes & Bug Fixes

### 5.1 Form Submission Fixes

#### Issue: Add School Need Form Returning 302 Error
**Problem:** Form submission failed with redirect error  
**Cause:** `bind_param` type mismatch in `/actions/admin_add_need.php`  
**Solution:** Corrected type string from `isssdiis` to `isssdiss`

```php
// BEFORE (incorrect)
$stmt->bind_param('isssdiis', ...);

// AFTER (correct)
$stmt->bind_param('isssdiss', 
    $school_id,      // i = integer
    $item_name,      // s = string
    $item_description, // s = string
    $item_category,  // s = string
    $unit_price,     // d = double
    $quantity_needed, // i = integer
    $image_url,      // s = string (was incorrectly 'i')
    $priority        // s = string
);
```

#### Issue: Category ENUM Mismatch
**Problem:** Form categories didn't match database ENUM values  
**Solution:** Updated form `<option>` values to match exact database ENUM values

### 5.2 Image Upload System

#### Original Approach: Cloudinary Widget
**Problem:** Cloudinary upload widget was unreliable, saving "0" instead of URLs  
**Cause:** Widget callback issues with server-side handling

#### New Approach: Direct URL Input
**Solution:** Replaced Cloudinary widget with simple URL text input
- Admin pastes image URL directly
- Preview function validates image loads correctly
- URL stored as string in database

**Files Modified:**
- `/admin/add_need.php` - Complete rewrite of image handling
- `/admin/manage_needs.php` - Updated edit functionality

### 5.3 Payment Integration Fixes

#### Issue: Paystack Callback URL Incorrect
**Problem:** Callback pointed to localhost instead of live server  
**File:** `/settings/paystack_config.php`

```php
// BEFORE
define('APP_BASE_URL', 'http://localhost:8888/GiveToGrow_Web');

// AFTER
define('APP_BASE_URL', 'http://169.239.251.102:442/~akua.oduro');
```

#### Issue: Processing Fee Confusion
**Problem:** Cart showed processing fee that wasn't being charged  
**Solution:** Removed processing fee display from cart summary

**File:** `/views/cart.php`
```php
// Removed processing fee calculation
// Now shows only: Subtotal → Total
```

### 5.4 Donation Success Page Fix

#### Issue: Only One Item Shown After Multi-Item Purchase
**Problem:** Success page displayed only the last donated item  
**Cause:** Query fetched single donation by ID instead of all from transaction

**Solution:** Updated query to fetch all donations from same transaction

**File:** `/views/donation_success.php`
```php
// BEFORE
$donation = $db->db_fetch_one("SELECT * FROM donations WHERE donation_id = ?", [$donation_id]);

// AFTER
$donations = $db->db_fetch_all(
    "SELECT d.*, sn.item_name, sn.image_url, s.school_name 
     FROM donations d 
     JOIN school_needs sn ON d.need_id = sn.need_id 
     JOIN schools s ON d.school_id = s.school_id 
     WHERE d.user_id = ? AND DATE(d.transaction_date) = DATE(?) 
     ORDER BY d.donation_id DESC",
    [$user_id, $transaction_date]
);
```

### 5.5 Currency Standardization

#### Issue: Mixed Currency Symbols
**Problem:** Some pages showed `$` instead of `₵` (Ghana Cedis)  
**Solution:** Updated all currency displays to use `₵` symbol

**Files Modified:**
- `/views/cart.php`
- `/views/checkout.php`
- `/views/donation_success.php`
- `/views/school_detail.php`
- `/views/schools.php`
- `/views/index.php`
- `/views/my_impact.php`
- `/views/dashboard.php`
- `/admin/dashboard.php`

### 5.6 User Registration Role Fix

#### Issue: New Users Automatically Became Admins
**Problem:** Registration defaulted to admin role  
**File:** `/actions/register_customer.php`

```php
// Verified correct - role is set to 'customer'
$role = 'customer';
```

**Note:** The code was already correct. Issue may have been with existing database records.

### 5.7 Admin Dashboard Improvements

#### Issue: Dashboard Showing Limited Schools
**Problem:** Only displayed 5 most recent schools  
**Solution:** Removed LIMIT clause to show all schools

**File:** `/admin/dashboard.php`
```php
// BEFORE
$recent_schools = $db->db_fetch_all("SELECT * FROM schools ORDER BY created_at DESC LIMIT 5");

// AFTER
$recent_schools = $db->db_fetch_all("SELECT * FROM schools ORDER BY created_at DESC");
```

### 5.8 UI/UX Fixes

#### Issue: False Error Popup on Page Load
**Problem:** "Image Error" SweetAlert appeared after successful form submission  
**Cause:** Inline `onerror` handler fired when image element had empty `src`

**Solution:** Moved error handling to JavaScript function, only triggers during active preview

---

## 6. File Structure

```
GiveToGrow_Web/
├── index.php                 # Entry point (redirects to views/index.php)
├── test_db.php              # Database connection tester
│
├── actions/                  # Form handlers & API endpoints
│   ├── add_to_cart.php
│   ├── admin_add_need.php   # ✓ Fixed bind_param types
│   ├── admin_add_school.php
│   ├── delete_school.php
│   ├── get_cart_count.php
│   ├── login_customer.php
│   ├── logout.php
│   ├── paystack_init_transaction.php
│   ├── paystack_verify_payment.php
│   ├── process_payment.php
│   ├── register_customer.php
│   ├── send_update_notifications.php
│   ├── update_cart.php
│   ├── update_need.php      # ✓ Created for edit functionality
│   └── delete_need.php      # ✓ Created for delete functionality
│
├── admin/                    # Admin panel pages
│   ├── dashboard.php        # ✓ Fixed to show all schools
│   ├── add_school.php
│   ├── manage_schools.php
│   ├── add_need.php         # ✓ Rewrote image handling
│   ├── manage_needs.php     # ✓ Created new file
│   ├── post_update.php
│   └── add_impact_metric.php
│
├── assets/
│   └── images/              # Local image assets
│
├── controllers/
│   └── customer_controller.php
│
├── db/                       # Database schemas & migrations
│   ├── schools_schema.sql
│   ├── impact_tracking_schema.sql
│   ├── create_admin_user.sql
│   └── [various fix scripts]
│
├── js/                       # JavaScript files
│
├── login/
│   ├── login.php
│   └── register.php
│
├── models/
│   └── customer_model.php
│
├── settings/                 # Configuration files
│   ├── admin_check.php
│   ├── cloudinary_config.php
│   ├── core.php
│   ├── db_class.php
│   ├── db_cred.php
│   └── paystack_config.php  # ✓ Updated callback URL
│
└── views/                    # Public-facing pages
    ├── index.php
    ├── schools.php          # ✓ Currency symbol fix
    ├── school_detail.php    # ✓ Currency symbol fix
    ├── cart.php             # ✓ Removed processing fee
    ├── checkout.php         # ✓ Currency symbol fix
    ├── donation_success.php # ✓ Fixed multi-item display
    ├── my_impact.php        # ✓ Currency symbol fix
    ├── my_updates.php
    ├── school_updates.php
    ├── dashboard.php
    ├── about.php
    ├── donate_item.php
    └── paystack_callback.php
```

---

## 7. Security Implementations

### 7.1 Password Security
- Passwords hashed using PHP's `password_hash()` with bcrypt
- Password verification using `password_verify()`

### 7.2 SQL Injection Prevention
- Prepared statements used throughout the application
- Parameter binding for all user inputs

### 7.3 Session Management
- Session-based authentication
- Role-based access control (customer vs admin)
- Admin routes protected with session checks

### 7.4 Input Validation
- Server-side validation for all form inputs
- Email format validation
- Required field checks

### 7.5 XSS Prevention
- `htmlspecialchars()` used for output escaping
- User inputs sanitized before display

---

## 8. Payment Integration

### Paystack Configuration
- **Currency:** GHS (Ghana Cedis)
- **Mode:** Live/Test configurable
- **Callback URL:** `http://169.239.251.102:442/~akua.oduro/views/paystack_callback.php`

### Payment Flow
1. User adds items to cart
2. User proceeds to checkout
3. System initializes Paystack transaction
4. User completes payment on Paystack
5. Paystack redirects to callback URL
6. System verifies payment with Paystack API
7. On success: donations recorded, cart cleared
8. User redirected to success page

### Transaction Recording
- Each cart item creates separate donation record
- All items share same `transaction_date` for grouping
- Payment reference stored for reconciliation

---

## 9. Admin System

### Admin Access
- Admin accounts created manually in database
- Role must be set to 'admin' in users table
- Admin check performed via `/settings/admin_check.php`

### Admin Capabilities
| Action | Description |
|--------|-------------|
| View Dashboard | See statistics and all schools |
| Add School | Create new school with details and image |
| Edit School | Modify school information |
| Delete School | Remove school from system |
| Add Need | Create donation items for schools |
| Edit Need | Modify need details and images |
| Delete Need | Remove needs from system |
| Post Updates | Send updates to donors |

### Statistics Tracked
- Total Schools (all / active)
- Total Needs (all / active)
- Total Amount Raised

---

## 10. Future Recommendations

### 10.1 Suggested Improvements
1. **Email Notifications:** Send confirmation emails after donations
2. **Receipt Generation:** PDF receipts for tax purposes
3. **Social Sharing:** Share donations on social media
4. **Progress Tracking:** Real-time progress bars for school goals
5. **Recurring Donations:** Monthly donation subscriptions

### 10.2 Technical Enhancements
1. **Image Upload:** Implement proper file upload to server/cloud storage
2. **Caching:** Add Redis/Memcached for performance
3. **API Documentation:** Create REST API documentation
4. **Unit Tests:** Implement PHPUnit testing
5. **HTTPS:** Ensure SSL certificate for secure transactions

### 10.3 Security Enhancements
1. **Rate Limiting:** Prevent brute force attacks
2. **CSRF Tokens:** Add to all forms
3. **Two-Factor Auth:** For admin accounts
4. **Audit Logging:** Track all admin actions

---

## Summary of Key Changes

| Category | Change | Impact |
|----------|--------|--------|
| Bug Fix | bind_param type correction | Images now save correctly |
| Feature | Manage Needs page | Full CRUD for school needs |
| Bug Fix | Donation success multi-item | All items now displayed |
| UX | Currency standardization | Consistent ₵ symbol |
| Bug Fix | Paystack callback URL | Payments work on live server |
| UX | Removed processing fee | Clearer pricing |
| Feature | Image URL input | Reliable image handling |
| Bug Fix | Dashboard school count | Shows all schools |

---

**Document Prepared By:** Development Team  
**Last Updated:** November 30, 2025
