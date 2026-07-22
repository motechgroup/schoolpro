-- Kenyan Primary School Management System Database Schema
-- CBC-Compliant Database Structure

-- Create database
CREATE DATABASE IF NOT EXISTS masomo_school_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE masomo_school_db;

-- Roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grades table (PP1, PP2, Grade 1-6)
CREATE TABLE IF NOT EXISTS grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(10) UNIQUE NOT NULL,
    display_name VARCHAR(50) NOT NULL,
    level INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Classes table
CREATE TABLE IF NOT EXISTS classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    grade_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    class_teacher_id INT NULL,
    capacity INT DEFAULT 40,
    academic_year VARCHAR(9) NOT NULL,
    status ENUM('active', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (grade_id) REFERENCES grades(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_class (grade_id, name, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parents/Guardians table
CREATE TABLE IF NOT EXISTS parents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    phone_alt VARCHAR(20),
    id_number VARCHAR(20),
    occupation VARCHAR(100),
    address TEXT,
    relationship ENUM('father', 'mother', 'guardian', 'other') DEFAULT 'guardian',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admission_number VARCHAR(20) UNIQUE NOT NULL,
    upi VARCHAR(20) UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    date_of_birth DATE NOT NULL,
    admission_date DATE NOT NULL,
    class_id INT NOT NULL,
    parent_id INT NULL,
    parent_relationship VARCHAR(50),
    status ENUM('active', 'alumni', 'transferred', 'suspended') DEFAULT 'active',
    photo VARCHAR(255),
    medical_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE SET NULL,
    INDEX idx_admission (admission_number),
    INDEX idx_upi (upi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teachers table
CREATE TABLE IF NOT EXISTS teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    tsc_number VARCHAR(50),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    qualification VARCHAR(100),
    specialization TEXT,
    employment_date DATE,
    status ENUM('active', 'inactive', 'retired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update classes table to reference teachers
ALTER TABLE classes ADD FOREIGN KEY (class_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL;

-- Learning Areas table (CBC)
CREATE TABLE IF NOT EXISTS learning_areas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    grade_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grade_id) REFERENCES grades(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Strands table (CBC)
CREATE TABLE IF NOT EXISTS strands (
    id INT PRIMARY KEY AUTO_INCREMENT,
    learning_area_id INT NOT NULL,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (learning_area_id) REFERENCES learning_areas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_strand (learning_area_id, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sub-strands table (CBC)
CREATE TABLE IF NOT EXISTS sub_strands (
    id INT PRIMARY KEY AUTO_INCREMENT,
    strand_id INT NOT NULL,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (strand_id) REFERENCES strands(id) ON DELETE CASCADE,
    UNIQUE KEY unique_sub_strand (strand_id, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Competency Levels table (CBC)
CREATE TABLE IF NOT EXISTS competency_levels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sub_strand_id INT NOT NULL,
    level INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sub_strand_id) REFERENCES sub_strands(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Assessments table (Continuous Assessment)
CREATE TABLE IF NOT EXISTS assessments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    learning_area_id INT NOT NULL,
    strand_id INT NOT NULL,
    sub_strand_id INT NOT NULL,
    competency_level_id INT,
    term INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    score DECIMAL(5,2),
    level ENUM('exceeding', 'meeting', 'approaching', 'below') DEFAULT 'meeting',
    teacher_id INT NOT NULL,
    remarks TEXT,
    assessed_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (learning_area_id) REFERENCES learning_areas(id) ON DELETE RESTRICT,
    FOREIGN KEY (strand_id) REFERENCES strands(id) ON DELETE RESTRICT,
    FOREIGN KEY (sub_strand_id) REFERENCES sub_strands(id) ON DELETE RESTRICT,
    FOREIGN KEY (competency_level_id) REFERENCES competency_levels(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE RESTRICT,
    INDEX idx_student_assessment (student_id, term, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student Attendance table
CREATE TABLE IF NOT EXISTS student_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present',
    remarks TEXT,
    marked_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_attendance (student_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teacher Attendance table
CREATE TABLE IF NOT EXISTS teacher_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'leave') DEFAULT 'present',
    check_in_time TIME,
    check_out_time TIME,
    remarks TEXT,
    marked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_teacher_attendance (teacher_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fee Structure table
CREATE TABLE IF NOT EXISTS fee_structure (
    id INT PRIMARY KEY AUTO_INCREMENT,
    grade_id INT NOT NULL,
    fee_name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    term INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    is_mandatory BOOLEAN DEFAULT TRUE,
    due_date DATE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (grade_id) REFERENCES grades(id) ON DELETE RESTRICT,
    INDEX idx_fee_grade_term (grade_id, term, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoices table
CREATE TABLE IF NOT EXISTS invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    student_id INT NOT NULL,
    term INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0.00,
    balance DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'partial', 'paid', 'overdue') DEFAULT 'pending',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_invoice_student (student_id, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoice Items table
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    fee_structure_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_structure_id) REFERENCES fee_structure(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    student_id INT NOT NULL,
    payment_method ENUM('cash', 'mpesa', 'bank', 'cheque', 'other') DEFAULT 'cash',
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    receipt_number VARCHAR(50) UNIQUE,
    mpesa_receipt VARCHAR(50),
    mpesa_transaction_id VARCHAR(50),
    reference_number VARCHAR(100),
    received_by INT NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE RESTRICT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_payment_student (student_id, payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- M-Pesa Transactions table
CREATE TABLE IF NOT EXISTS mpesa_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT,
    merchant_request_id VARCHAR(100),
    checkout_request_id VARCHAR(100),
    result_code INT,
    result_desc VARCHAR(255),
    amount DECIMAL(10,2),
    mpesa_receipt_number VARCHAR(50),
    transaction_date VARCHAR(50),
    phone_number VARCHAR(20),
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    callback_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    INDEX idx_mpesa_receipt (mpesa_receipt_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Announcements table
CREATE TABLE IF NOT EXISTS announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    target_audience ENUM('all', 'parents', 'students', 'teachers', 'staff') DEFAULT 'all',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    module VARCHAR(50),
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_log_user (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO roles (name, description, permissions) VALUES
('super_admin', 'Super Administrator', '["*"]'),
('school_admin', 'School Administrator', '["students.view", "students.create", "students.edit", "students.delete", "teachers.view", "teachers.create", "teachers.edit", "fees.view", "fees.create", "fees.edit", "reports.view"]'),
('head_teacher', 'Head Teacher', '["students.view", "teachers.view", "assessments.view", "assessments.create", "assessments.edit", "attendance.view", "reports.view"]'),
('teacher', 'Teacher', '["students.view", "assessments.view", "assessments.create", "assessments.edit", "attendance.view", "attendance.create"]'),
('bursar', 'Bursar/Accounts', '["students.view", "fees.view", "fees.create", "fees.edit", "payments.view", "payments.create", "reports.view"]'),
('parent', 'Parent/Guardian', '["students.view", "assessments.view", "attendance.view", "fees.view", "payments.view"]'),
('student', 'Student', '["assessments.view", "attendance.view"]');

-- Insert default grades (CBC + JSS)
INSERT INTO grades (name, display_name, level) VALUES
('PP1', 'Pre-Primary 1', 1),
('PP2', 'Pre-Primary 2', 2),
('G1', 'Grade 1', 3),
('G2', 'Grade 2', 4),
('G3', 'Grade 3', 5),
('G4', 'Grade 4', 6),
('G5', 'Grade 5', 7),
('G6', 'Grade 6', 8),
('G7', 'Grade 7 (JSS)', 9),
('G8', 'Grade 8 (JSS)', 10),
('G9', 'Grade 9 (JSS)', 11);

-- Create default super admin user (password: admin123)
-- Password hash for 'admin123' - Generated using password_hash('admin123', PASSWORD_DEFAULT)
-- Verified: password_verify('admin123', '$2y$10$BJPPKBgYDm5qSKBZ517IKOb1M2S1pD.RhwX2PorA6.6.QQaPeuz2y') = true
INSERT INTO users (role_id, email, password, first_name, last_name, status) VALUES
(1, 'admin@school.co.ke', '$2y$10$BJPPKBgYDm5qSKBZ517IKOb1M2S1pD.RhwX2PorA6.6.QQaPeuz2y', 'Super', 'Admin', 'active');

