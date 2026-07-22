-- =========================================================================
-- SchoolPro V2.0.0 Consolidated Database Migration & Schema Compatibility
-- Safe for Live Deployment: Uses IF NOT EXISTS and INSERT IGNORE
-- =========================================================================

-- 1. Ensure Grades Table Contains Playgroup to Grade 9 (Kenyan CBC + JSS)
CREATE TABLE IF NOT EXISTS grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(10) UNIQUE NOT NULL,
    display_name VARCHAR(50) NOT NULL,
    level INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO grades (name, display_name, level, description) VALUES
('PG', 'Playgroup', 1, 'Early Childhood Development - Playgroup'),
('PP1', 'Pre-Primary 1', 2, 'Early Years Education - PP1'),
('PP2', 'Pre-Primary 2', 3, 'Early Years Education - PP2'),
('G1', 'Grade 1', 4, 'Lower Primary Education - Grade 1'),
('G2', 'Grade 2', 5, 'Lower Primary Education - Grade 2'),
('G3', 'Grade 3', 6, 'Lower Primary Education - Grade 3'),
('G4', 'Grade 4', 7, 'Upper Primary Education - Grade 4'),
('G5', 'Grade 5', 8, 'Upper Primary Education - Grade 5'),
('G6', 'Grade 6', 9, 'Upper Primary Education - Grade 6'),
('G7', 'Grade 7 (JSS)', 10, 'Junior Secondary School - Grade 7'),
('G8', 'Grade 8 (JSS)', 11, 'Junior Secondary School - Grade 8'),
('G9', 'Grade 9 (JSS)', 12, 'Junior Secondary School - Grade 9');

-- 2. Fee Heads Tables
CREATE TABLE IF NOT EXISTS fee_heads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE,
    description TEXT,
    is_recurring BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO fee_heads (name, code, description) VALUES
('Tuition Fee', 'TUITION', 'Core Academic Tuition Fee'),
('Lunch & Catering', 'LUNCH', 'School Catering and Lunch Services'),
('Transport Fee', 'TRANSPORT', 'School Bus Transportation Fee'),
('Exam & Assessment Fee', 'EXAM', 'Continuous Assessments and Examination Fee'),
('Activity & Sports Fee', 'ACTIVITY', 'Co-curricular and Sports Activities'),
('Library & Computer Lab', 'ICT_LIB', 'Library Access and Computer Lab Fee'),
('Development / Building Fund', 'BUILDING', 'School Infrastructure Development Fund');

CREATE TABLE IF NOT EXISTS student_fee_heads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    fee_head_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    term INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_head_id) REFERENCES fee_heads(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_student_fee_head (student_id, fee_head_id, term, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fee_head_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NOT NULL,
    student_fee_head_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_fee_head_id) REFERENCES student_fee_heads(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Additional Banking & Payment Integration Tables
CREATE TABLE IF NOT EXISTS kcb_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NULL,
    transaction_reference VARCHAR(100) UNIQUE NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    customer_name VARCHAR(100),
    phone_number VARCHAR(20),
    transaction_date DATETIME NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    raw_payload TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS equity_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NULL,
    transaction_reference VARCHAR(100) UNIQUE NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    customer_name VARCHAR(100),
    phone_number VARCHAR(20),
    transaction_date DATETIME NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    raw_payload TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Messaging & Logs
CREATE TABLE IF NOT EXISTS whatsapp_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('queued', 'sent', 'delivered', 'failed') DEFAULT 'sent',
    reference_id VARCHAR(100),
    response_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sms_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('queued', 'sent', 'delivered', 'failed') DEFAULT 'sent',
    cost DECIMAL(8,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. CMS Extended Tables for SaaS Multi-Tenant Management
CREATE TABLE IF NOT EXISTS cms_schools (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_name VARCHAR(150) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    domain VARCHAR(100),
    status ENUM('active', 'inactive', 'pending', 'suspended') DEFAULT 'active',
    subscription_plan VARCHAR(50) DEFAULT 'standard',
    subscription_expires_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cms_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'mpesa',
    receipt_number VARCHAR(50) UNIQUE,
    payment_date DATE NOT NULL,
    status ENUM('completed', 'pending', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES cms_schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cms_system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_level ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    category VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    context JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cms_backups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(255) NOT NULL,
    filesize_bytes BIGINT NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default CMS school if table empty
INSERT IGNORE INTO cms_schools (id, school_name, subdomain, database_name, admin_email, admin_name, status) VALUES
(1, 'Masomo Primary & Junior Secondary School', 'masomo', 'masomo_school_db', 'info@masomoschool.ac.ke', 'School Admin', 'active');
