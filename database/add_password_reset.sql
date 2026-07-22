-- Password Reset System
-- Create table for password reset tokens and codes

CREATE TABLE IF NOT EXISTS password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    reset_token VARCHAR(100) NULL COMMENT 'Token for email reset links',
    reset_code VARCHAR(6) NULL COMMENT '6-digit code for SMS reset',
    reset_method ENUM('email', 'sms', 'both') DEFAULT 'email',
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (reset_token),
    INDEX idx_code (reset_code),
    INDEX idx_email (email),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

