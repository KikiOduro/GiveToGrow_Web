-- Simple Fix for Donations Table
-- Run this in phpMyAdmin if you get "Unknown column 'donor_name'" error

-- Add missing columns to donations table
ALTER TABLE donations ADD COLUMN IF NOT EXISTS quantity INT DEFAULT 1 AFTER amount;
ALTER TABLE donations ADD COLUMN IF NOT EXISTS donor_name VARCHAR(255) AFTER payment_status;
ALTER TABLE donations ADD COLUMN IF NOT EXISTS donor_email VARCHAR(255) AFTER donor_name;
ALTER TABLE donations ADD COLUMN IF NOT EXISTS transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER donor_email;

-- Add indexes
ALTER TABLE donations ADD INDEX IF NOT EXISTS idx_donation_user (user_id);
ALTER TABLE donations ADD INDEX IF NOT EXISTS idx_donation_date (transaction_date);

-- Verify the structure
DESCRIBE donations;
