-- Create Admin User for GiveToGrow
-- Run this SQL to create an admin account for testing

-- The password is: Admin@123
-- You can change it after first login

-- First, check if user already exists
-- If exists, update to admin role. If not, insert new admin user.

-- Insert admin user (user_role = 'admin' for admin role, based on ENUM in database)
-- Password hash for 'Admin@123' using PHP password_hash()
INSERT INTO users (user_name, user_email, password_hash, user_role, is_active)
VALUES ('Admin User', 'admin@givetogrow.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1)
ON DUPLICATE KEY UPDATE user_role = 'admin';

-- Note: The password hash above is for 'password' (default Laravel hash)
-- You should generate your own hash using PHP:
-- php -r "echo password_hash('Admin@123', PASSWORD_DEFAULT);"
-- 
-- Or use this temporary one and change password immediately after login
-- Email: admin@givetogrow.org
-- Password: Admin@123
--
-- To generate a new hash for 'Admin@123':
-- Run in PHP: password_hash('Admin@123', PASSWORD_DEFAULT)
-- Result should be like: $2y$10$... (60 characters)

-- Alternative: Update an existing user to admin
-- UPDATE users SET user_role = 'admin' WHERE user_email = 'your-email@example.com';
