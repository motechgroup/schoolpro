-- WhatsApp Integration Tables
-- Create tables for WhatsApp messaging functionality

-- WhatsApp logs table
CREATE TABLE IF NOT EXISTS whatsapp_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    response TEXT NULL,
    http_code INT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add WhatsApp settings to settings table
INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES
('whatsapp_provider', 'cloud_api', 'WhatsApp API provider: cloud_api, business_api, twilio, messagebird, green_api'),
('whatsapp_api_key', '', 'WhatsApp API Key / Access Token'),
('whatsapp_api_secret', '', 'WhatsApp API Secret (for Twilio/Green API)'),
('whatsapp_phone_number_id', '', 'WhatsApp Phone Number ID (for Cloud API)'),
('whatsapp_business_account_id', '', 'WhatsApp Business Account ID'),
('whatsapp_api_url', '', 'WhatsApp API URL (optional, defaults based on provider)');

