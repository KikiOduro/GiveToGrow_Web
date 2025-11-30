-- School Updates & Notifications Tables
-- Run this SQL in phpMyAdmin to enable the "Track Your Impact" feature
-- Database: ecommerce_2025A_akua_oduro

-- Table to store school updates/posts
CREATE TABLE IF NOT EXISTS school_updates (
    update_id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    update_title VARCHAR(255) NOT NULL,
    update_description TEXT,
    update_type ENUM('general', 'milestone', 'progress', 'completion', 'thank_you') DEFAULT 'general',
    image_url VARCHAR(500),
    is_published TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_school_id (school_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to track which users have been notified and read updates
CREATE TABLE IF NOT EXISTS update_notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    update_id INT NOT NULL,
    user_id INT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_update_id (update_id),
    INDEX idx_user_id (user_id),
    UNIQUE KEY unique_user_update (user_id, update_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: Add some sample updates for testing
-- INSERT INTO school_updates (school_id, update_title, update_description, update_type, image_url) VALUES
-- (14, 'New Books Arrived!', 'Thanks to generous donors, we received 50 new textbooks for our students. The children are so excited to have their own books to learn from!', 'milestone', 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800'),
-- (15, 'Thank You From Janga Islamic Primary', 'We want to express our heartfelt gratitude to everyone who has contributed to our school. Your support means the world to us and our students.', 'thank_you', 'https://images.unsplash.com/photo-1577896851231-70ef18881754?w=800');

-- Verify tables were created
SELECT 'school_updates table created successfully' AS status;
SELECT 'update_notifications table created successfully' AS status;
