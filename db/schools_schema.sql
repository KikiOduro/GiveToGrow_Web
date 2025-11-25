-- Schools Table
CREATE TABLE IF NOT EXISTS schools (
    school_id INT AUTO_INCREMENT PRIMARY KEY,
    school_name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    country VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(500),
    total_students INT DEFAULT 0,
    fundraising_goal DECIMAL(10, 2) NOT NULL,
    amount_raised DECIMAL(10, 2) DEFAULT 0.00,
    is_verified BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- School Needs/Items Table
CREATE TABLE IF NOT EXISTS school_needs (
    need_id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_description TEXT,
    item_category ENUM('Books', 'Desks', 'Supplies', 'Technology', 'Water', 'Other') NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    quantity_needed INT NOT NULL,
    quantity_fulfilled INT DEFAULT 0,
    image_url VARCHAR(500),
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('active', 'fulfilled', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Donations Table
CREATE TABLE IF NOT EXISTS donations (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    school_id INT NOT NULL,
    need_id INT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    donation_type ENUM('school', 'item', 'general') DEFAULT 'general',
    quantity INT DEFAULT 1,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    donation_message TEXT,
    is_anonymous BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_school_id (school_id),
    INDEX idx_need_id (need_id),
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    FOREIGN KEY (need_id) REFERENCES school_needs(need_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart Table (for temporary storage before checkout)
CREATE TABLE IF NOT EXISTS cart (
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

-- Insert Sample Data for Testing
INSERT INTO schools (school_name, location, country, description, image_url, total_students, fundraising_goal, amount_raised) VALUES
('Kibera Primary School', 'Nairobi', 'Kenya', 'Kibera Primary provides a safe and nurturing environment for over 300 students. Your contribution helps us provide essential learning materials, nutritious meals, and improve classroom conditions to give these bright minds the future they deserve.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAIbdJsfVuiqymwuOKBSCYh6rNs92ZjXbRGGNJrZ9nh8ynejkQ-diS5YYoGFbDbG7WVoAeTiadOwV68q8UQqGZ04M7kPK2rljci6CBWrVQyvpsy68oK6cu5o0hmz0OU2YTNlDeuGIncpYKlZYOKoSorzzwxgVL23w2edsSzgvk-wHq9GyoHmLWhw_f2TpAY_IQxs7s7hx_V8_4W-K7TkR2nG_NmFrNha5Cn52KiwyW5PdLyJhGa8SnKbyZ43QqmA1WVnwIIUbKROgQw', 300, 10000.00, 6500.00),
('Hope Academy', 'Nairobi', 'Kenya', 'Funding for 150 new reading books to build their first school library.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDmldrHaMVXufNKcfMS4ElyMeiIv4dIJ6sEuZXvNoGzYZSrEiytt2c7p8ojS6J3GW6v9rl6GeNHjM25uBtggyyoOtvhP7OXrMCRVo7agGk9BiUbCt2dsXK8MButOyu0FB3Y_EEPbVy64M9ad8NQONPyVZJWbHyJ3crkp6pGs9PEzrr1hh-6o-cwEMLbhgp-8kkc1gZw7ftpHeg3_P6sl8akNDONdBATbNS8ZFOasvaSwF6sqBw-xRiXCgLfpCW2fsXlw1p-DFbZf6IO', 250, 2000.00, 1500.00),
('Bwindi Primary', 'Kampala', 'Uganda', 'Help us replace 30 broken desks to provide a proper learning space for students.', 'https://lh3.googleusercontent.com/aida-public/AB6AXuB-YGgj4qPRLT-uTLRyQJRYeuOoNWtrcc2ocSWxBBR8KhZOk5BEScQ4UCpmArzEtdOYKmL9ZSo0brkN_MeuAOJ-tmtZTxQ5Hw-BOC2rCj73rVZujKaO9RtN-y-kP5bUeSW3kzJC5ZmlBUxsfTEDy59Rby4nTU23ncI2d-TAxRdqG10VHGSyeGPo5Uo2SPg2wgour5oJ_6U7o3O-aTdSKu5lfzte5uk6VQ5cS_N-qTC9Ik8lQ2yLexnV45ehPbMM8sMJVaulNBp4xx2x', 180, 1500.00, 600.00);

INSERT INTO school_needs (school_id, item_name, item_description, item_category, unit_price, quantity_needed, image_url, priority) VALUES
(1, 'Science Textbooks', 'Modern science textbooks for grades 4-6', 'Books', 25.00, 100, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDXsRBIggnsfBpLO9QukwYPrmW1cosww8xPVlYdqeryprbpgjzu4BnnblBdJTrbsDT2UCHlAOb0nps-2Ss2j6LK-GumdL_-hrCnq97qSIHeGBBnH6675HB9a6mEQwmsSssHUto4P-mG0I0ZXeYTGqC6JSjbuBrkbfojw3m7fEVZ5c5Ju8G7csHlSkzUQkaYuzcOo_7Gwlikl_l_f390XbHxwLk-OnW_QDkjdSLVNOKr2ef87Yuf6A3JCqubhtWiNrwNd_rpgD1nW6zT', 'high'),
(1, 'Library Storybooks', 'Engaging storybooks for young readers', 'Books', 10.00, 200, 'https://lh3.googleusercontent.com/aida-public/AB6AXuAcFImy5jqbtFtfWxGHIuV8DZr_dPRkMK3oBvb-Odkzb6vnRZkIwY49bzL2WT0dWdM0EFoIPgfh4ENEAsZvUDwEAYMBQHmJ5Z0qW8L_fO_Q3k5MXG6hxlUUyNtheg04Fh6C_RJE014V8BTJSisg-7m3intI_gypZ1c_-ju0YFDabgG-_jY3i5983r4LkkgXwlbXP4NoaDLLjL6uOQCgLo0jS8RR4BsCgr-e7qLJ9klVH1sGWMMz5YCtUHv3KcoBroKnEAE7gBHxE-6H', 'medium'),
(1, 'Clean Water Filter', 'Water filtration system for safe drinking water', 'Water', 250.00, 1, 'https://lh3.googleusercontent.com/aida-public/AB6AXuAeiL8t0uqnT86ZeSSlkQ0ZtoP24PKLoK1JJsu6RoPtJOsySGnXuQn503CIKf1G7Cg1wIVqhHvtHZrlJf1SrjwrTn9zlLmKLPQAVcZwvz7wRTlDy2XuHI5Dkum96BuCSUyhj-oRgjfTlA8WTQ5VnLPQqMCMk22lVjrPa_HAxFOzl9IpZFRiy62F2UIWayC-VxpB83qJNPVWO7Fgb71ertdCCQfPSq6ZrUJudq7P7PDQ-jJKaI7BhB3MIYZjTcprRYdESM1N-Vm9nxMP', 'urgent'),
(2, 'Reading Books', 'Books for new school library', 'Books', 15.00, 150, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDXsRBIggnsfBpLO9QukwYPrmW1cosww8xPVlYdqeryprbpgjzu4BnnblBdJTrbsDT2UCHlAOb0nps-2Ss2j6LK-GumdL_-hrCnq97qSIHeGBBnH6675HB9a6mEQwmsSssHUto4P-mG0I0ZXeYTGqC6JSjbuBrkbfojw3m7fEVZ5c5Ju8G7csHlSkzUQkaYuzcOo_7Gwlikl_l_f390XbHxwLk-OnW_QDkjdSLVNOKr2ef87Yuf6A3JCqubhtWiNrwNd_rpgD1nW6zT', 'high'),
(3, 'Student Desks', 'Sturdy wooden desks with chairs', 'Desks', 50.00, 30, 'https://lh3.googleusercontent.com/aida-public/AB6AXuB-YGgj4qPRLT-uTLRyQJRYeuOoNWtrcc2ocSWxBBR8KhZOk5BEScQ4UCpmArzEtdOYKmL9ZSo0brkN_MeuAOJ-tmtZTxQ5Hw-BOC2rCj73rVZujKaO9RtN-y-kP5bUeSW3kzJC5ZmlBUxsfTEDy59Rby4nTU23ncI2d-TAxRdqG10VHGSyeGPo5Uo2SPg2wgour5oJ_6U7o3O-aTdSKu5lfzte5uk6VQ5cS_N-qTC9Ik8lQ2yLexnV45ehPbMM8sMJVaulNBp4xx2x', 'urgent');
