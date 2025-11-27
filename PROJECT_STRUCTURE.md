# GiveToGrow - MVC Project Structure

## 📁 Directory Structure

```
GiveToGrow_Web/
├── index.php                  # Entry point (redirects to views/index.php)
│
├── views/                     # View Layer - All display pages
│   ├── index.php             # Landing page
│   ├── dashboard.php         # User dashboard
│   ├── schools.php           # School listing page
│   ├── school_detail.php     # Individual school details
│   ├── cart.php              # Shopping cart
│   ├── checkout.php          # Payment checkout
│   ├── my_impact.php         # Donor impact dashboard
│   ├── my_updates.php        # User updates listing
│   ├── school_updates.php    # School-specific updates
│   ├── donation_success.php  # Payment success page
│   ├── donate_item.php       # Item donation page
│   ├── paystack_callback.php # Payment callback handler
│   └── about.php             # About page
│
├── models/                    # Model Layer - Database operations
│   └── customer_model.php    # Customer data access
│
├── controllers/               # Controller Layer - Business logic
│   └── customer_controller.php # Customer business logic
│
├── actions/                   # Action handlers - Form processing
│   ├── login_customer.php    # Login processing
│   ├── register_customer.php # Registration processing
│   ├── add_to_cart.php       # Add items to cart
│   ├── update_cart.php       # Update cart quantities
│   ├── process_payment.php   # Process donations/payments
│   └── logout.php            # Logout handler
│
├── settings/                  # Configuration files
│   ├── db_class.php          # Database connection class
│   ├── db_cred.php           # Database credentials
│   └── core.php              # Core helper functions
│
├── login/                     # Authentication views
│   ├── login.php             # Login form
│   └── register.php          # Registration form
│
├── admin/                     # Admin panel (separate module)
│   └── ...                   # Admin-specific files
│
├── assets/                    # Static files
│   ├── images/               # Images
│   ├── css/                  # Stylesheets (if any)
│   └── logo.png              # Site logo
│
├── js/                        # JavaScript files
│
└── db/                        # Database schemas
    └── schools_schema.sql    # Database structure
```

## 🔄 Path Updates

After reorganization, paths were updated as follows:

### From View Files (in `views/` folder):
- Assets: `../assets/`
- Actions: `../actions/`
- Admin: `../admin/`
- Login: `../login/`
- Settings: `../settings/`

### From Action Files (in `actions/` folder):
- Views: `../views/`
- Controllers: `../controllers/`
- Settings: `../settings/`

### From Login Files (in `login/` folder):
- Actions: `../actions/`
- Views: `../views/`

## 🚀 Entry Points

- **Main Site**: `http://localhost/GiveToGrow_Web/` → Redirects to `views/index.php`
- **Direct Access**: `http://localhost/GiveToGrow_Web/views/dashboard.php`
- **Login**: `http://localhost/GiveToGrow_Web/login/login.php`
- **Admin**: `http://localhost/GiveToGrow_Web/admin/`

## 📝 MVC Pattern

**Model** → Handles data and database operations
- `models/customer_model.php`

**View** → Displays information to users
- All files in `views/` folder

**Controller** → Processes business logic
- `controllers/customer_controller.php`

**Actions** → Processes form submissions and user actions
- All files in `actions/` folder

## 🔧 Configuration

Database configuration is in `settings/db_cred.php`:
- **Local (MAMP)**: Automatically detected when running on localhost
- **Production**: Uses production credentials when deployed

## ✅ Benefits of This Structure

1. **Separation of Concerns**: Clear distinction between data, logic, and presentation
2. **Maintainability**: Easy to find and update specific functionality
3. **Scalability**: Simple to add new features in the right place
4. **Organization**: Professional structure that's easy to understand
5. **Reusability**: Models and controllers can be reused across different views
