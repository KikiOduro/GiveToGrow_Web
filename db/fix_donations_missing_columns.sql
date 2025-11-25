-- Fix Donations Table - Add Only Missing Columns
-- Run each ALTER statement separately, ignore errors if column exists

-- Try to add donor_name (skip if exists)
ALTER TABLE donations ADD COLUMN donor_name VARCHAR(255) AFTER payment_status;

-- Try to add donor_email (skip if exists)
ALTER TABLE donations ADD COLUMN donor_email VARCHAR(255) AFTER donor_name;

-- Try to add transaction_date (skip if exists)
ALTER TABLE donations ADD COLUMN transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER donor_email;

-- Add indexes (these may give errors if they exist, that's OK)
ALTER TABLE donations ADD INDEX idx_donation_user (user_id);
ALTER TABLE donations ADD INDEX idx_donation_date (transaction_date);

-- Verify the final structure
DESCRIBE donations;
