-- Fix Cart Table Structure
-- Run this if you're getting "Unknown column 'user_id'" error

-- Check if cart table exists and drop it if structure is wrong
DROP TABLE IF EXISTS cart;

-- Recreate cart table with correct structure
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    need_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cart_user_id (user_id),
    INDEX idx_cart_need_id (need_id),
    FOREIGN KEY (need_id) REFERENCES school_needs(need_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_need (user_id, need_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Verify the table structure
DESCRIBE cart;

-- You should see these columns:
-- cart_id, user_id, need_id, quantity, added_at
