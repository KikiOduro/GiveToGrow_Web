-- Update donations table to include additional fields
-- Run each ALTER statement separately to avoid errors if columns already exist

ALTER TABLE donations ADD COLUMN quantity INT DEFAULT 1 AFTER amount;

ALTER TABLE donations ADD COLUMN payment_method VARCHAR(50) DEFAULT 'card' AFTER quantity;

ALTER TABLE donations ADD COLUMN donor_name VARCHAR(255) AFTER payment_status;

ALTER TABLE donations ADD COLUMN donor_email VARCHAR(255) AFTER donor_name;

ALTER TABLE donations ADD COLUMN transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER donor_email;

-- Add indexes for faster queries
ALTER TABLE donations ADD INDEX idx_donation_user (user_id);

ALTER TABLE donations ADD INDEX idx_donation_date (transaction_date);
