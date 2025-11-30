# GiveToGrow

A web-based donation platform connecting donors with under-resourced schools in Ghana. Built with PHP, MySQL, and TailwindCSS.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.0-06B6D4?style=flat&logo=tailwindcss&logoColor=white)

## Overview

GiveToGrow enables donors to browse schools, view their specific needs (textbooks, desks, supplies, etc.), and make secure donations via Paystack. Donors can track their impact and receive updates from schools they've supported.

## Features

### For Donors
- **Browse Schools** - View schools with their fundraising goals and progress
- **Guest Browsing** - Explore schools without logging in
- **Shopping Cart** - Add specific items (needs) to cart before checkout
- **Secure Payments** - Pay via Paystack (supports mobile money & cards)
- **Impact Dashboard** - See donation history and total contributions
- **School Updates** - Receive progress updates from supported schools

### For Administrators
- **School Management** - Add, edit, and delete schools
- **Needs Management** - Create and manage school needs with priorities
- **Post Updates** - Share progress reports and milestones with donors
- **Dashboard Analytics** - View platform statistics

## Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 7.4+ |
| Database | MySQL 8.0+ |
| Frontend | TailwindCSS (CDN), HTML5 |
| Payments | Paystack API |
| Images | Cloudinary |
| Icons | Google Material Symbols |
| Alerts | SweetAlert2 |

## Project Structure

```
GiveToGrow_Web/
├── index.php              # Entry point - redirects to views/index.php
├── actions/               # Backend handlers (AJAX endpoints)
│   ├── add_to_cart.php
│   ├── login_customer.php
│   ├── register_customer.php
│   ├── paystack_init_transaction.php
│   ├── paystack_verify_payment.php
│   └── ...
├── admin/                 # Admin panel pages
│   ├── dashboard.php
│   ├── add_school.php
│   ├── add_need.php
│   ├── manage_schools.php
│   └── post_update.php
├── assets/                # Static files (images, logo)
├── controllers/           # Business logic layer
│   └── customer_controller.php
├── login/                 # Authentication pages
│   ├── login.php
│   └── register.php
├── models/                # Database interaction layer
│   └── customer_model.php
├── settings/              # Configuration files
│   ├── db_class.php       # Database wrapper
│   ├── db_cred.php        # Database credentials
│   ├── paystack_config.php
│   ├── cloudinary_config.php
│   └── admin_check.php
└── views/                 # Public-facing pages
    ├── index.php          # Landing page
    ├── dashboard.php      # User home (after login)
    ├── schools.php        # Browse all schools
    ├── school_detail.php  # Individual school page
    ├── cart.php           # Shopping cart
    ├── checkout.php       # Payment page
    ├── my_impact.php      # Donation history
    ├── my_updates.php     # School updates feed
    └── about.php          # About us page
```

## Database Schema

### Core Tables
- **users** - Donor and admin accounts
- **schools** - School profiles with fundraising goals
- **school_needs** - Specific items schools need (with prices, quantities)
- **donations** - Completed donation records
- **cart** - Temporary cart storage before checkout

### Impact Tracking Tables
- **school_updates** - Progress posts from schools
- **update_notifications** - Tracks which users received updates
- **donor_subscriptions** - Links donors to schools they support
- **impact_metrics** - Quantifiable impact data

## Setup Instructions

### Prerequisites
- MAMP/XAMPP or similar PHP development environment
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Paystack account (for payments)
- Cloudinary account (for image uploads)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/GiveToGrow_Web.git
   ```

2. **Configure database credentials**
   
   Edit `settings/db_cred.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'your_database');
   ```

3. **Configure Paystack**
   
   Edit `settings/paystack_config.php`:
   ```php
   define('PAYSTACK_SECRET_KEY', 'sk_test_xxxxx');
   define('PAYSTACK_PUBLIC_KEY', 'pk_test_xxxxx');
   ```

4. **Configure Cloudinary**
   
   Edit `settings/cloudinary_config.php`:
   ```php
   define('CLOUDINARY_CLOUD_NAME', 'your_cloud_name');
   define('CLOUDINARY_UPLOAD_PRESET', 'your_preset');
   ```

5. **Import database schema**
   
   Run the SQL scripts in your MySQL database to create the required tables.

6. **Create admin user**
   
   Insert an admin user directly in the database or use the registration flow and update the `user_role` to 'admin'.

7. **Access the application**
   ```
   http://localhost/GiveToGrow_Web/
   ```

## User Roles

| Role | Access |
|------|--------|
| Guest | Browse schools, view details (no cart/checkout) |
| Customer | Full access to donate, cart, checkout, impact tracking |
| Admin | All customer features + admin panel for managing schools/needs |

## Payment Flow

1. User adds items to cart
2. Proceeds to checkout
3. Paystack payment form loads
4. User completes payment (card/mobile money)
5. Paystack redirects to callback URL
6. System verifies payment via Paystack API
7. Donations recorded, school amounts updated
8. User redirected to success page

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/actions/add_to_cart.php` | POST | Add item to cart |
| `/actions/update_cart.php` | POST | Update cart quantity |
| `/actions/get_cart_count.php` | GET | Get cart item count |
| `/actions/paystack_init_transaction.php` | POST | Initialize payment |
| `/actions/paystack_verify_payment.php` | GET | Verify payment status |
| `/actions/login_customer.php` | POST | User authentication |
| `/actions/register_customer.php` | POST | User registration |

## Currency

All amounts are in **Ghana Cedis (GHS/₵)**. Paystack handles the payment processing for Ghanaian currency.

## Author

Akua Oduro

## License

This project is for educational purposes.
