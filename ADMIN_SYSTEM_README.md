# GiveToGrow Admin System

## Overview
Complete admin panel for managing schools and their needs on the GiveToGrow donation platform.

## Features

### 1. **Admin Dashboard** (`admin/dashboard.php`)
- Overview statistics (total schools, needs, funds raised)
- Quick action buttons to add schools and needs
- Recent schools table with progress tracking
- Dark mode support

### 2. **Add School** (`admin/add_school.php`)
- Form to register new underresourced schools
- Required fields:
  - School name
  - Location/City
  - Country (dropdown with African countries)
  - Description
  - Image URL
  - Total students
  - Fundraising goal (GHS)
  - Status (active/inactive)
- After adding, redirects to add needs for that school

### 3. **Add School Need** (`admin/add_need.php`)
- Form to add items/resources needed by schools
- Required fields:
  - School selection (dropdown)
  - Item name
  - Item description
  - Category (Books, Desks, Supplies, Technology, Water, Other)
  - Priority (low, medium, high, urgent)
  - Unit price (GHS)
  - Quantity needed
  - Item image URL
- Can pre-select school via URL parameter: `?school_id=X`

### 4. **Manage Schools** (`admin/manage_schools.php`)
- View all registered schools in a table
- Columns: School info, location, students, fundraising progress, needs count, status
- Actions: View school detail, add need
- Shows total and active needs per school

## File Structure

```
admin/
├── dashboard.php           # Admin dashboard with statistics
├── add_school.php         # Form to add new schools
├── add_need.php           # Form to add school needs
└── manage_schools.php     # List and manage all schools

actions/
├── admin_add_school.php   # Backend: Process school addition
└── admin_add_need.php     # Backend: Process need addition

settings/
└── admin_check.php        # Middleware: Check admin authentication

db/
└── create_admin_user.sql  # SQL to create admin account
```

## Setup Instructions

### 1. Create an Admin Account

**Option A: Using SQL**
```sql
-- Run this in your MySQL database (dbforlab)
-- Creates admin with email: admin@givetogrow.org, password: Admin@123
INSERT INTO users (user_name, user_email, password_hash, user_role, is_active)
VALUES ('Admin User', 'admin@givetogrow.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);
```

**Option B: Update Existing User**
```sql
-- Make your existing user an admin (change email to yours)
UPDATE users SET user_role = 1 WHERE user_email = 'your-email@gmail.com';
```

**Option C: Generate New Password Hash**
```bash
# In terminal, generate hash for your password
php -r "echo password_hash('YourPassword123', PASSWORD_DEFAULT);"

# Then insert with your hash
INSERT INTO users (user_name, user_email, password_hash, user_role, is_active)
VALUES ('Your Name', 'your@email.com', 'YOUR_HASH_HERE', 1, 1);
```

### 2. Login as Admin
1. Go to: `http://localhost:8888/GiveToGrow_Web/login/login.php`
2. Enter admin credentials
3. After login, you'll see "Admin Panel" link in the navigation

### 3. Access Admin Panel
- **URL**: `http://localhost:8888/GiveToGrow_Web/admin/dashboard.php`
- **Navigation**: Click "Admin Panel" in the top navigation (only visible to admins)

## User Roles

| Role | Value | Description |
|------|-------|-------------|
| Admin | 1 | Full access to admin panel, can add/manage schools and needs |
| Customer | 2 | Regular user, can browse and donate to schools |

## Workflow

### Adding a New School
1. Login as admin
2. Navigate to Admin Panel → Add School
3. Fill in all required fields:
   - School name, location, country
   - Compelling description
   - School image URL
   - Number of students
   - Fundraising goal
4. Submit form
5. Automatically redirected to Add School Need page

### Adding School Needs
1. From dashboard or after adding school
2. Select school from dropdown
3. Enter item details:
   - Name (e.g., "Science Textbooks")
   - Description
   - Category and priority
   - Price and quantity
   - Item image
4. Submit form
5. Need appears on school detail page and schools listing

### School Visibility
- **Active schools**: Visible on public `schools.php` page
- **Inactive schools**: Hidden from public, visible only in admin panel
- Schools with active needs show up in category filters

## Security

### Admin Authentication
- `settings/admin_check.php` - Protects all admin pages
- Checks for valid session and `user_role = 1`
- Redirects non-admins to dashboard with error message

### Access Control
```php
// In every admin page
require_once __DIR__ . '/../settings/admin_check.php';
```

### Session Variables
```php
$_SESSION['user_id']       // User ID
$_SESSION['user_name']     // Display name
$_SESSION['user_email']    // Email address
$_SESSION['user_role']     // 1 = admin, 2 = customer
$_SESSION['logged_in']     // true/false
```

## Database Schema

### Schools Table
```sql
schools (
    school_id INT PRIMARY KEY AUTO_INCREMENT,
    school_name VARCHAR(255),
    location VARCHAR(255),
    country VARCHAR(100),
    description TEXT,
    image_url VARCHAR(500),
    total_students INT,
    fundraising_goal DECIMAL(10, 2),
    amount_raised DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'completed'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

### School Needs Table
```sql
school_needs (
    need_id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT FOREIGN KEY,
    item_name VARCHAR(255),
    item_description TEXT,
    item_category ENUM('Books', 'Desks', 'Supplies', 'Technology', 'Water', 'Other'),
    unit_price DECIMAL(10, 2),
    quantity_needed INT,
    quantity_fulfilled INT DEFAULT 0,
    image_url VARCHAR(500),
    priority ENUM('low', 'medium', 'high', 'urgent'),
    status ENUM('active', 'fulfilled', 'inactive'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

## Features

### Dashboard Statistics
- Total schools (with active count)
- Total needs (with active count)
- Total funds raised across all schools
- Recent schools with quick actions

### School Management
- View all schools with details
- See fundraising progress bars
- Count of needs per school
- Quick links to view or add needs

### Data Flow
1. Admin adds school → School appears in database
2. Admin adds needs → Needs linked to school
3. Public users see schools → Browse active schools and needs
4. Users donate → Updates `amount_raised` and `quantity_fulfilled`
5. Admin monitors → Dashboard shows progress

## Testing

### Test Workflow
1. Create admin account using SQL
2. Login as admin
3. Add a test school:
   - Name: "Test Primary School"
   - Location: "Accra"
   - Country: "Ghana"
   - Fundraising Goal: 5000 GHS
4. Add test needs:
   - 50 textbooks @ 20 GHS each
   - 30 desks @ 50 GHS each
5. View on public site (`schools.php`)
6. Verify school appears and is clickable
7. Test donation flow

### Validation
- All required fields enforced
- Positive numbers for prices and quantities
- Valid URLs for images
- School must exist when adding needs

## Troubleshooting

### "Access Denied" Error
- **Issue**: Not logged in as admin
- **Solution**: Check `user_role = 1` in database for your user

### Images Not Loading
- **Issue**: Invalid image URLs
- **Solution**: Use valid HTTPS URLs from image hosting services

### School Not Appearing on Public Site
- **Issue**: Status set to 'inactive'
- **Solution**: Check school status in database or manage schools page

### Can't Add Needs
- **Issue**: No schools in database
- **Solution**: Add at least one school first

## Image URL Resources

For school and item images, you can use:
- **Google Images** (with proper URLs)
- **Unsplash**: https://unsplash.com (free stock photos)
- **Pexels**: https://pexels.com (free stock photos)
- **Placeholder services**: https://placeholder.com

## Default Admin Credentials

**For Testing Only:**
- Email: `admin@givetogrow.org`
- Password: `Admin@123`
- **Change immediately after first login!**

## Production Recommendations

1. **Change default admin password**
2. **Use HTTPS** for all admin pages
3. **Implement file upload** instead of URL input
4. **Add edit/delete functionality** for schools and needs
5. **Add audit logging** for admin actions
6. **Implement role-based permissions** (super admin, school admin, etc.)
7. **Add bulk import** for multiple schools
8. **Email notifications** when schools/needs are added

## Support

For issues or questions:
- Check error logs: `/Applications/MAMP/logs/php_error.log`
- Verify database connection in `settings/db_class.php`
- Ensure all files have proper permissions
- Test with `user_role = 1` in database

---

**Last Updated**: November 25, 2025
**Version**: 1.0
**Author**: GiveToGrow Development Team
