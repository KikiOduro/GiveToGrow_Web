-- Impact Tracking and Updates Schema
-- Add this to your database to enable impact tracking features

-- Table for school progress updates
CREATE TABLE IF NOT EXISTS school_updates (
    update_id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    need_id INT NULL,
    update_title VARCHAR(255) NOT NULL,
    update_description TEXT NOT NULL,
    update_type ENUM('milestone', 'progress', 'completion', 'thank_you', 'general') DEFAULT 'general',
    image_url VARCHAR(500),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_published BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    FOREIGN KEY (need_id) REFERENCES school_needs(need_id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_school_id (school_id),
    INDEX idx_created_at (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to track which donors receive updates for which schools/needs
CREATE TABLE IF NOT EXISTS donor_subscriptions (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    school_id INT NOT NULL,
    need_id INT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_update_sent TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    FOREIGN KEY (need_id) REFERENCES school_needs(need_id) ON DELETE SET NULL,
    UNIQUE KEY unique_subscription (user_id, school_id, need_id),
    INDEX idx_user_id (user_id),
    INDEX idx_school_id (school_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to track which users have been notified about updates
CREATE TABLE IF NOT EXISTS update_notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    update_id INT NOT NULL,
    user_id INT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (update_id) REFERENCES school_updates(update_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_notification (update_id, user_id),
    INDEX idx_user_unread (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for impact metrics (quantifiable results)
CREATE TABLE IF NOT EXISTS impact_metrics (
    metric_id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    need_id INT NULL,
    metric_type ENUM('students_benefited', 'items_distributed', 'attendance_increase', 'grade_improvement', 'other') NOT NULL,
    metric_label VARCHAR(255) NOT NULL,
    metric_value DECIMAL(10,2) NOT NULL,
    metric_unit VARCHAR(50),
    measurement_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    FOREIGN KEY (need_id) REFERENCES school_needs(need_id) ON DELETE SET NULL,
    INDEX idx_school_id (school_id),
    INDEX idx_need_id (need_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data for testing
INSERT INTO school_updates (school_id, update_title, update_description, update_type, image_url) VALUES
(1, 'Textbooks Received!', 'We are thrilled to announce that the science textbooks have arrived! Students are already using them in their classes and the excitement is palpable. Thank you to all our donors!', 'milestone', 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b'),
(1, 'Student Progress Update', 'After two months with the new textbooks, we have seen a 25% improvement in science test scores. Students are more engaged and teachers report better classroom participation.', 'progress', 'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45');

INSERT INTO impact_metrics (school_id, metric_type, metric_label, metric_value, metric_unit, measurement_date) VALUES
(1, 'students_benefited', 'Students Using New Materials', 300, 'students', CURDATE()),
(1, 'grade_improvement', 'Average Test Score Increase', 25, 'percent', CURDATE()),
(1, 'attendance_increase', 'Attendance Rate Improvement', 15, 'percent', CURDATE());
