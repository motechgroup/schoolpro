-- SMS and Settings Tables
-- Run this SQL to add SMS functionality support

-- Settings table for storing application settings
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS Logs table
CREATE TABLE IF NOT EXISTS sms_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    response TEXT,
    http_code INT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default SMS settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('sms_api_key', '', 'TextSMS.co.ke API Key'),
('sms_sender_id', 'MASOMO', 'SMS Sender ID (must be registered)'),
('sms_api_url', 'https://textsms.co.ke/api/send', 'SMS API Endpoint URL')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Insert default school settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('school_name', '', 'School Name (used in ID cards and reports)'),
('school_address', '', 'School Physical Address'),
('school_phone', '', 'School Phone Number'),
('school_email', '', 'School Email Address')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Email logs table
CREATE TABLE IF NOT EXISTS email_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    to_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    success TINYINT(1) DEFAULT 0,
    error_message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

