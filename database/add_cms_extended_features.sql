-- Extended CMS Features Database Schema
-- Includes: Fees, Payments, Owners, Notifications, Logs, Backups

-- School Owners Table
CREATE TABLE IF NOT EXISTS cms_school_owners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    owner_email VARCHAR(100) NOT NULL,
    owner_phone VARCHAR(20),
    owner_address TEXT,
    owner_id_number VARCHAR(50),
    is_primary BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES cms_schools(id) ON DELETE CASCADE,
    INDEX idx_school_id (school_id),
    INDEX idx_owner_email (owner_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Setup and Maintenance Fees Table
CREATE TABLE IF NOT EXISTS cms_school_fees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    fee_type ENUM('setup', 'maintenance') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'KES',
    due_date DATE,
    status ENUM('pending', 'paid', 'overdue', 'waived') DEFAULT 'pending',
    payment_date DATE NULL,
    payment_method VARCHAR(50) NULL,
    transaction_reference VARCHAR(255) NULL,
    notes TEXT,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES cms_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES cms_admins(id) ON DELETE SET NULL,
    INDEX idx_school_id (school_id),
    INDEX idx_fee_type (fee_type),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Revenue/Payments Table
CREATE TABLE IF NOT EXISTS cms_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    fee_id INT NULL,
    payment_type ENUM('setup', 'maintenance', 'subscription', 'other') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'KES',
    payment_method ENUM('mpesa', 'bank_transfer', 'cash', 'cheque', 'other') NOT NULL,
    transaction_reference VARCHAR(255),
    payment_date DATE NOT NULL,
    received_by INT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES cms_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_id) REFERENCES cms_school_fees(id) ON DELETE SET NULL,
    FOREIGN KEY (received_by) REFERENCES cms_admins(id) ON DELETE SET NULL,
    INDEX idx_school_id (school_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_payment_type (payment_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CMS Notifications Table
CREATE TABLE IF NOT EXISTS cms_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_type ENUM('school', 'owner', 'admin', 'all_schools') NOT NULL,
    recipient_id INT NULL, -- school_id, owner_id, or admin_id depending on recipient_type
    notification_type ENUM('info', 'warning', 'error', 'success', 'payment', 'system_update') DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    action_url VARCHAR(255) NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipient (recipient_type, recipient_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (created_by) REFERENCES cms_admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Communications Table
CREATE TABLE IF NOT EXISTS cms_email_communications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_type ENUM('school', 'owner', 'admin', 'all_schools') NOT NULL,
    recipient_id INT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed', 'bounced') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipient_email (recipient_email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (created_by) REFERENCES cms_admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CMS System Logs Table
CREATE TABLE IF NOT EXISTS cms_system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_type ENUM('cms_admin', 'school_system', 'api', 'payment', 'backup', 'system') NOT NULL,
    school_id INT NULL,
    admin_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_data TEXT NULL, -- JSON data
    response_data TEXT NULL, -- JSON data
    status ENUM('success', 'error', 'warning') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_log_type (log_type),
    INDEX idx_school_id (school_id),
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (school_id) REFERENCES cms_schools(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES cms_admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- School System Logs Table (for school super admin)
CREATE TABLE IF NOT EXISTS school_system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    module VARCHAR(50) NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status ENUM('success', 'error', 'warning') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_school_id (school_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_module (module),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backup Records Table
CREATE TABLE IF NOT EXISTS cms_backups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    backup_type ENUM('database', 'files', 'full', 'school_database') NOT NULL,
    school_id INT NULL,
    backup_name VARCHAR(255) NOT NULL,
    backup_path VARCHAR(500) NOT NULL,
    file_size BIGINT NULL, -- Size in bytes
    status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    created_by INT NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_backup_type (backup_type),
    INDEX idx_school_id (school_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (school_id) REFERENCES cms_schools(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES cms_admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CMS Settings Table
CREATE TABLE IF NOT EXISTS cms_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    category VARCHAR(50) DEFAULT 'general',
    updated_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key),
    INDEX idx_category (category),
    FOREIGN KEY (updated_by) REFERENCES cms_admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default CMS settings
INSERT INTO cms_settings (setting_key, setting_value, setting_type, description, category) VALUES
('setup_fee_amount', '50000', 'number', 'Default initial setup fee amount', 'fees'),
('maintenance_fee_amount', '30000', 'number', 'Default yearly maintenance fee amount', 'fees'),
('maintenance_fee_currency', 'KES', 'string', 'Default currency for fees', 'fees'),
('system_version', '1.0.0', 'string', 'Current system version displayed on school systems', 'system'),
('backup_retention_days', '30', 'number', 'Number of days to retain backups', 'backup'),
('backup_auto_enabled', 'true', 'boolean', 'Enable automatic backups', 'backup'),
('backup_schedule', 'daily', 'string', 'Backup schedule (daily, weekly, monthly)', 'backup'),
('email_from_address', 'noreply@schoolpro.com', 'string', 'Default email sender address', 'email'),
('email_from_name', 'SchoolPro CMS', 'string', 'Default email sender name', 'email'),
('notification_enabled', 'true', 'boolean', 'Enable internal notification system', 'notification')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Add setup_fee_paid and maintenance_fee_paid fields to cms_schools
ALTER TABLE cms_schools 
ADD COLUMN setup_fee_paid BOOLEAN DEFAULT FALSE,
ADD COLUMN setup_fee_amount DECIMAL(10, 2) NULL,
ADD COLUMN maintenance_fee_paid BOOLEAN DEFAULT FALSE,
ADD COLUMN maintenance_fee_amount DECIMAL(10, 2) NULL,
ADD COLUMN last_maintenance_payment_date DATE NULL;

