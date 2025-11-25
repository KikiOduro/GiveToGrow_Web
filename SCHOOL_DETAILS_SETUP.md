# GiveToGrow School Details System - Setup Instructions

## Database Setup

### Step 1: Run the SQL Schema
1. Open phpMyAdmin at `http://localhost:8888/phpMyAdmin`
2. Select your database `dbforlab`
3. Go to the SQL tab
4. Copy and paste the contents of `/db/schools_schema.sql`
5. Click "Go" to execute

This will create the following tables:
- `schools` - Stores school information
- `school_needs` - Stores items/needs for each school
- `donations` - Tracks all donations
- `cart` - Temporary cart storage before checkout

### Step 2: Test with Sample Data
The SQL file includes sample data for 3 schools with various needs.

## File Structure

```
GiveToGrow_Web/
├── school_detail.php          # Individual school details page
├── schools.php                # Schools listing page
├── admin/
│   └── add_school.php         # Admin form to add schools and needs
├── actions/
│   └── add_to_cart.php        # Handles adding items to cart
├── db/
│   └── schools_schema.sql     # Database schema
└── settings/
    └── db_class.php           # Updated with prepared statement support
```

## How to Use

### For Users:
1. **Browse Schools**: Visit `schools.php` to see all available schools
2. **View School Details**: Click "View School" button on any school card
3. **School Detail Page**: See school information, fundraising progress, and available needs
4. **Add to Cart**: Click "Quick Donate" or cart icon to add items
5. **Checkout**: Click "Proceed to Cart" in the footer

### For Admins:
1. **Access Admin Panel**: Login with admin credentials (user_role = 1)
2. **Add School**: Visit `admin/add_school.php`
3. **Fill School Form**:
   - School Name
   - Location/City
   - Country
   - Description
   - Image URL
   - Total Students
   - Fundraising Goal

4. **Add School Needs**: After adding a school, use the second form to add needs:
   - Select School from dropdown
   - Item Name
   - Item Description
   - Category (Books, Desks, Supplies, Technology, Water, Other)
   - Unit Price
   - Quantity Needed
   - Image URL
   - Priority (Low, Medium, High, Urgent)

## Linking Schools to Detail Page

### Update schools.php
Find all "View School" buttons and update them with the school_id:

```php
<button onclick="window.location.href='school_detail.php?id=<?php echo $school['school_id']; ?>'"
        class="flex-1 flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary/20 text-primary dark:bg-primary/30 dark:text-background-light text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/30 dark:hover:bg-primary/40">
    View School
</button>
```

## Database Schema Details

### schools table
- `school_id` - Primary key
- `school_name` - School name
- `location` - City/town
- `country` - Country name
- `description` - Detailed description
- `image_url` - Header image URL
- `total_students` - Number of students
- `fundraising_goal` - Target amount
- `amount_raised` - Current amount raised
- `is_verified` - Verified school badge
- `status` - active, inactive, or completed

### school_needs table
- `need_id` - Primary key
- `school_id` - Foreign key to schools
- `item_name` - Name of the item
- `item_description` - Details about the item
- `item_category` - Books, Desks, Supplies, Technology, Water, Other
- `unit_price` - Price per item
- `quantity_needed` - How many needed
- `quantity_fulfilled` - How many already donated
- `image_url` - Item image
- `priority` - low, medium, high, urgent
- `status` - active, fulfilled, inactive

### cart table
- `cart_id` - Primary key
- `user_id` - Foreign key to users
- `need_id` - Foreign key to school_needs
- `quantity` - Number of items in cart

### donations table
- `donation_id` - Primary key
- `user_id` - Who donated
- `school_id` - Which school
- `need_id` - Which specific item (optional)
- `amount` - Donation amount
- `payment_status` - pending, completed, failed, refunded

## Features Implemented

✅ Database schema with relationships
✅ School detail page with dynamic data from database
✅ Add to cart functionality
✅ Cart count and total in footer
✅ Admin forms to add schools and needs
✅ Prepared statements for SQL injection protection
✅ Session-based authentication
✅ Dark mode support
✅ Responsive design
✅ Progress bars showing fundraising status

## Testing URLs

- Schools Listing: `http://localhost:8888/GiveToGrow_Web/schools.php`
- School Details: `http://localhost:8888/GiveToGrow_Web/school_detail.php?id=1`
- Admin Panel: `http://localhost:8888/GiveToGrow_Web/admin/add_school.php`

## Next Steps

1. Create cart.php page to display cart items
2. Implement checkout/payment process
3. Add image upload functionality instead of URLs
4. Create admin dashboard to manage existing schools
5. Add donation tracking and receipts
6. Implement email notifications
