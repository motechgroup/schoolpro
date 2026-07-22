-- Add Fee Heads table and update fee structure
-- Run this to add fee heads functionality

USE masomo_school_db;

-- Fee Heads table (lunch, transport, tuition, etc.)
CREATE TABLE IF NOT EXISTS fee_heads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    default_amount DECIMAL(10,2) DEFAULT 0.00,
    is_mandatory BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student Fee Head Assignments (links students to fee heads with custom amounts)
CREATE TABLE IF NOT EXISTS student_fee_heads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    fee_head_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    term INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_head_id) REFERENCES fee_heads(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_fee_head (student_id, fee_head_id, term, academic_year),
    INDEX idx_student_fee (student_id, term, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default fee heads
INSERT INTO fee_heads (code, name, description, default_amount, is_mandatory) VALUES
('TUITION', 'Tuition Fees', 'Main tuition fees', 0.00, TRUE),
('LUNCH', 'Lunch Fees', 'School lunch program fees', 0.00, FALSE),
('TRANSPORT', 'Transport Fees', 'School transport/bus fees', 0.00, FALSE),
('LIBRARY', 'Library Fees', 'Library and reading materials', 0.00, FALSE),
('SPORTS', 'Sports Fees', 'Sports and games activities', 0.00, FALSE),
('MEDICAL', 'Medical Fees', 'Medical and health services', 0.00, FALSE),
('EXAM', 'Examination Fees', 'Examination and assessment fees', 0.00, FALSE),
('UNIFORM', 'Uniform Fees', 'School uniform fees', 0.00, FALSE),
('BUILDING', 'Building Fund', 'School infrastructure development', 0.00, FALSE),
('PTA', 'PTA Fees', 'Parent Teacher Association fees', 0.00, FALSE)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Update invoice_items table to support fee_heads (make fee_structure_id nullable and add fee_head_id)
ALTER TABLE invoice_items 
ADD COLUMN fee_head_id INT NULL AFTER fee_structure_id,
ADD COLUMN description VARCHAR(255) NULL AFTER amount,
ADD FOREIGN KEY (fee_head_id) REFERENCES fee_heads(id) ON DELETE SET NULL;

-- Make fee_structure_id nullable to support both old and new system
ALTER TABLE invoice_items MODIFY fee_structure_id INT NULL;

-- Fee Head Payments table (tracks payments per fee head)
CREATE TABLE IF NOT EXISTS fee_head_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NOT NULL,
    student_fee_head_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_fee_head_id) REFERENCES student_fee_heads(id) ON DELETE CASCADE,
    INDEX idx_payment_fee_head (payment_id, student_fee_head_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

