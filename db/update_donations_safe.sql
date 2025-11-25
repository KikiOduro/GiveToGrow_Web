-- Safe version: Check and add columns only if they don't exist
-- Execute this entire script in phpMyAdmin SQL tab

-- Add quantity column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'donations' 
AND COLUMN_NAME = 'quantity';

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE donations ADD COLUMN quantity INT DEFAULT 1 AFTER amount', 
    'SELECT "Column quantity already exists" as message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add payment_method column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'donations' 
AND COLUMN_NAME = 'payment_method';

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE donations ADD COLUMN payment_method VARCHAR(50) DEFAULT "card" AFTER quantity', 
    'SELECT "Column payment_method already exists" as message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add donor_name column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'donations' 
AND COLUMN_NAME = 'donor_name';

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE donations ADD COLUMN donor_name VARCHAR(255) AFTER payment_status', 
    'SELECT "Column donor_name already exists" as message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add donor_email column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'donations' 
AND COLUMN_NAME = 'donor_email';

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE donations ADD COLUMN donor_email VARCHAR(255) AFTER donor_name', 
    'SELECT "Column donor_email already exists" as message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add transaction_date column
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'donations' 
AND COLUMN_NAME = 'transaction_date';

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE donations ADD COLUMN transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER donor_email', 
    'SELECT "Column transaction_date already exists" as message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes (these will fail silently if they already exist)
ALTER TABLE donations ADD INDEX idx_donation_user (user_id);
ALTER TABLE donations ADD INDEX idx_donation_date (transaction_date);
