-- Fix user_role default value
-- The users table currently defaults to 'admin' which is wrong
-- New signups should be 'customer' by default

-- Change the default value for user_role to 'customer'
ALTER TABLE users 
MODIFY COLUMN user_role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer';

-- Verify the change
DESCRIBE users;
