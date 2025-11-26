# Impact Tracking System - Setup Guide

## Overview
The impact tracking system provides transparency and donor engagement through regular updates, metrics, and notifications. This ensures the promise of "Track Your Impact" on the dashboard is fulfilled.

## Features Implemented

### 1. Database Structure
- **school_updates**: Store progress reports with images and update types
- **donor_subscriptions**: Auto-enrollment when users donate
- **update_notifications**: Track read/unread status
- **impact_metrics**: Quantifiable results (students benefited, grade improvements, etc.)

### 2. Admin Pages
- **admin/post_update.php**: Post updates with optional email notifications
- **admin/add_impact_metric.php**: Add quantifiable metrics

### 3. User Pages
- **school_updates.php**: View updates for a specific school
- **my_updates.php**: View all updates from schools user has supported
- **dashboard.php**: Shows recent updates widget

### 4. Email Notifications
- **actions/send_update_notifications.php**: Email template system
- Sends personalized emails to all donors when updates are posted

## Installation Steps

### Step 1: Run Database Schema
```bash
# Navigate to your MAMP MySQL admin or phpMyAdmin
# Execute the SQL file:
/db/impact_tracking_schema.sql
```

This creates:
- `school_updates` table
- `donor_subscriptions` table
- `update_notifications` table
- `impact_metrics` table

### Step 2: Verify Admin Access
1. Log in as an admin user
2. Navigate to the admin panel
3. You should see two new menu items:
   - "Post Update"
   - "Add Impact Metric"

### Step 3: Test the System

#### Test Posting an Update:
1. Go to `admin/post_update.php`
2. Select a school
3. Choose update type (milestone, progress, completion, etc.)
4. Add title and description
5. Optionally add image URL
6. Check "Send email notifications" if you want to test emails
7. Click "Post Update"

#### Test Adding Metrics:
1. Go to `admin/add_impact_metric.php`
2. Select a school
3. Choose metric type (students benefited, grade improvement, etc.)
4. Enter value and unit
5. Set measurement date
6. Click "Add Metric"

#### Test Viewing Updates:
1. Log in as a regular user who has donated
2. Go to dashboard - you should see "Your Impact Updates" section
3. Click "View all" to see `my_updates.php`
4. Click on any school card, then "View Updates & Impact" button
5. View the `school_updates.php` page with timeline and metrics

### Step 4: Configure Email (Optional)

The email system uses PHP's built-in `mail()` function. For production:

1. **Option A: Use SendGrid**
```php
// In actions/send_update_notifications.php
// Replace mail() function with SendGrid API
```

2. **Option B: Use Mailgun**
```php
// Replace mail() function with Mailgun API
```

3. **Option C: Use AWS SES**
```php
// Replace mail() function with AWS SES
```

For testing locally, MAMP doesn't send real emails. You can:
- Use MailHog for local testing
- Comment out email sending in `post_update.php`
- Use a service like Mailtrap for development

## User Flow

### For Donors:
1. User donates to a school
2. Automatically subscribed to that school's updates
3. When school posts update:
   - Receives email notification (if enabled)
   - Sees update in dashboard widget
   - Can view full update on `school_updates.php`
   - Can see all updates on `my_updates.php`
4. Updates are marked as "read" when viewed

### For Admins:
1. Navigate to admin panel
2. Post updates regularly (weekly/monthly)
3. Add impact metrics as data becomes available
4. Choose whether to send email notifications
5. View recent updates list on post page

## Update Types

- **General**: Regular progress updates
- **Milestone**: Significant achievements (e.g., "50% funded!")
- **Progress**: Ongoing work reports
- **Completion**: Project finished announcements
- **Thank You**: Gratitude messages to donors

## Impact Metrics Types

- **Students Benefited**: Number of students helped
- **Grade Improvement**: Percentage increase in grades
- **Attendance Increase**: Percentage increase in attendance
- **Items Distributed**: Number of items delivered
- **Other**: Custom metrics

## Integration Points

### Links Added:
1. **school_detail.php**: "View Updates & Impact" button
2. **dashboard.php**: Recent updates widget with unread indicators
3. **my_updates.php**: Dedicated updates page

### Database Triggers:
1. When user donates → Auto-subscribe to school updates
2. When update posted → Create notification records
3. When user views update → Mark as read

## Best Practices

### For Admins:
1. Post updates regularly (at least monthly)
2. Include photos when possible
3. Add metrics as data becomes available
4. Use appropriate update types
5. Keep descriptions clear and engaging
6. Thank donors in updates

### Content Guidelines:
- **Update Title**: Clear, concise, exciting (e.g., "Textbooks Delivered!")
- **Description**: Tell a story, mention impact, thank donors
- **Images**: Show students, facilities, progress
- **Metrics**: Be specific with numbers and dates

## Troubleshooting

### Updates Not Showing:
- Check `is_published = 1` in database
- Verify user has donated to that school
- Check donation `payment_status = 'completed'`

### Emails Not Sending:
- Verify PHP mail() is configured
- Check user email addresses are valid
- Consider using external email service
- Check spam folders

### Metrics Not Displaying:
- Ensure `measurement_date` is valid
- Check `metric_value` is numeric
- Verify school_id matches

## Sample Data

The schema includes sample data:
- 2 sample updates
- 3 impact metrics
- Subscription records

You can use this to test the system before adding real data.

## Future Enhancements

Potential additions:
- Photo galleries for updates
- Video support
- Donor comments on updates
- Social media sharing
- Monthly impact summary emails
- Before/after photo comparisons
- Student testimonials
- Impact charts and graphs
- Export impact reports (PDF)
- Update scheduling (post later)

## Support

For questions or issues:
1. Check database connection settings
2. Verify all tables created successfully
3. Check error logs in MAMP
4. Ensure user has donated before testing updates view

## Quick Commands

```bash
# Check if tables exist
SHOW TABLES LIKE '%update%';

# View recent updates
SELECT * FROM school_updates ORDER BY created_at DESC LIMIT 5;

# Check notifications
SELECT * FROM update_notifications WHERE user_id = YOUR_USER_ID;

# View metrics
SELECT * FROM impact_metrics ORDER BY recorded_at DESC;
```

## Summary

The impact tracking system is now fully functional. Admins can post updates and metrics, donors automatically receive notifications, and users can view their impact through multiple interfaces. This fulfills the dashboard promise of transparency and regular updates, building trust and encouraging continued support.
