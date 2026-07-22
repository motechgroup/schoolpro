-- CMS (Content Management System) Database Schema
-- For managing multiple school installations, subscriptions, and monitoring

-- CMS Admins table
CREATE TABLE IF NOT EXISTS cms_admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('super_admin', 'admin', 'support') DEFAULT 'admin',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Schools/Clients table
CREATE TABLE IF NOT EXISTS cms_schools (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_name VARCHAR(255) NOT NULL,
    subdomain VARCHAR(100) UNIQUE NOT NULL,
    domain VARCHAR(255) NULL, -- Custom domain if applicable
    database_name VARCHAR(100) NOT NULL,
    admin_email VARCHAR(100) NOT NULL,
    admin_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    status ENUM('active', 'inactive', 'suspended', 'trial') DEFAULT 'trial',
    subscription_status ENUM('active', 'expired', 'cancelled', 'trial') DEFAULT 'trial',
    subscription_plan ENUM('basic', 'standard', 'premium', 'enterprise', 'trial') DEFAULT 'trial',
    subscription_start_date DATE NULL,
    subscription_end_date DATE NULL,
    max_students INT DEFAULT 100,
    max_teachers INT DEFAULT 20,
    max_storage_mb INT DEFAULT 1000,
    api_key VARCHAR(255) UNIQUE NULL, -- For API authentication
    api_secret VARCHAR(255) NULL,
    last_sync TIMESTAMP NULL, -- Last time school system synced with CMS
    notes TEXT,
    created_by INT NULL, -- CMS admin who created this school
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES cms_admins(id) ON DELETE SET NULL,
    INDEX idx_subdomain (subdomain),
    INDEX idx_status (status),
    INDEX idx_subscription_status (subscription_status),
    INDEX idx_api_key (api_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscriptions table
CREATE TABLE IF NOT EXISTS cms_subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    plan ENUM('basic', 'standard', 'premium', 'enterprise', 'trial') NOT NULL,
    status ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'pending',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    amount DECIMAL(10, 2) DEFAULT 0.00,
    billing_cycle ENUM('monthly', 'quarterly', 'yearly') DEFAULT 'monthly',
    payment_method VARCHAR(50),
    payment_reference VARCHAR(255),
    auto_renew BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES cms_schools(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES cms_admins(id) ON DELETE SET NULL,
    INDEX idx_school_id (school_id),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Monitoring/System Health table
CREATE TABLE IF NOT EXISTS cms_monitoring (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    status ENUM('online', 'offline', 'warning', 'error') DEFAULT 'online',
    last_heartbeat TIMESTAMP NULL,
    system_version VARCHAR(50),
    php_version VARCHAR(20),
    database_status ENUM('connected', 'disconnected', 'error') DEFAULT 'connected',
    storage_used_mb DECIMAL(10, 2) DEFAULT 0.00,
    storage_limit_mb DECIMAL(10, 2) DEFAULT 1000.00,
    total_students INT DEFAULT 0,
    total_teachers INT DEFAULT 0,
    total_users INT DEFAULT 0,
    active_users_24h INT DEFAULT 0,
    total_payments_today DECIMAL(10, 2) DEFAULT 0.00,
    error_count_24h INT DEFAULT 0,
    response_time_ms INT DEFAULT 0,
    uptime_percentage DECIMAL(5, 2) DEFAULT 100.00,
    health_score INT DEFAULT 100, -- 0-100 health score
    alerts JSON NULL, -- Store any alerts or warnings
    metadata JSON NULL, -- Additional system information
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES cms_schools(id) ON DELETE CASCADE,
    INDEX idx_school_id (school_id),
    INDEX idx_status (status),
    INDEX idx_last_heartbeat (last_heartbeat),
    INDEX idx_health_score (health_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs for CMS
CREATE TABLE IF NOT EXISTS cms_activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NULL,
    school_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50), -- 'school', 'subscription', 'admin', etc.
    entity_id INT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES cms_admins(id) ON DELETE SET NULL,
    FOREIGN KEY (school_id) REFERENCES cms_schools(id) ON DELETE SET NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_school_id (school_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default CMS admin (password: admin123)
-- Password hash for 'admin123'
INSERT INTO cms_admins (email, password, first_name, last_name, role, status) 
VALUES ('admin@cms.local', '$2y$10$U2K3yozLdtrGBUvZjZbHOe1uin9euwZiYkb22EKOkf/EmJYMrh61.', 'CMS', 'Administrator', 'super_admin', 'active')
ON DUPLICATE KEY UPDATE 
    password = '$2y$10$U2K3yozLdtrGBUvZjZbHOe1uin9euwZiYkb22EKOkf/EmJYMrh61.',
    status = 'active';

