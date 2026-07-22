-- Add Kenyan Bank Payment Methods and M-Pesa Configuration
-- Run this after the main schema

USE masomo_school_db;

-- Update payment_method ENUM to include Kenyan banks
ALTER TABLE payments 
MODIFY COLUMN payment_method ENUM(
    'cash', 
    'mpesa', 
    'equity', 
    'coop', 
    'kcb', 
    'family_bank', 
    'bank', 
    'cheque', 
    'other'
) DEFAULT 'cash';

-- Add payment settings to settings table
INSERT INTO settings (setting_key, setting_value, updated_at) VALUES
('mpesa_paybill_number', '', NOW()),
('mpesa_paybill_account_prefix', '', NOW()),
('mpesa_api_consumer_key', '', NOW()),
('mpesa_api_consumer_secret', '', NOW()),
('mpesa_api_passkey', '', NOW()),
('mpesa_api_shortcode', '', NOW()),
('mpesa_webhook_url', '', NOW()),
('equity_bank_account', '', NOW()),
('equity_bank_name', 'Equity Bank', NOW()),
('jenga_api_key', '', NOW()),
('jenga_api_secret', '', NOW()),
('jenga_merchant_code', '', NOW()),
('jenga_environment', 'sandbox', NOW()),
('jenga_auto_reconcile', '0', NOW()),
('coop_bank_account', '', NOW()),
('coop_bank_name', 'Co-operative Bank', NOW()),
('kcb_bank_account', '', NOW()),
('kcb_bank_name', 'Kenya Commercial Bank', NOW()),
('family_bank_account', '', NOW()),
('family_bank_name', 'Family Bank', NOW()),
('payment_auto_reconcile', '0', NOW())
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Add M-Pesa transaction tracking fields if not exists
ALTER TABLE mpesa_transactions 
ADD COLUMN IF NOT EXISTS account_number VARCHAR(50) AFTER phone_number,
ADD COLUMN IF NOT EXISTS student_id INT NULL AFTER account_number,
ADD COLUMN IF NOT EXISTS reconciled BOOLEAN DEFAULT FALSE AFTER status,
ADD INDEX IF NOT EXISTS idx_mpesa_account (account_number),
ADD INDEX IF NOT EXISTS idx_mpesa_student (student_id);

-- Add foreign key for student_id if it doesn't exist
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'masomo_school_db' 
    AND TABLE_NAME = 'mpesa_transactions' 
    AND COLUMN_NAME = 'student_id' 
    AND REFERENCED_TABLE_NAME = 'students'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE mpesa_transactions ADD FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL',
    'SELECT "Foreign key already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

