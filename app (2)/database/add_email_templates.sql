-- Email Templates and Email Sending System
-- Create tables for email templates and email logs

-- Email Templates Table
CREATE TABLE IF NOT EXISTS email_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    variables TEXT COMMENT 'JSON array of available variables like {student_name}, {parent_name}, etc.',
    category ENUM('fee', 'academic', 'announcement', 'general', 'custom') DEFAULT 'general',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Logs Table (if not exists)
CREATE TABLE IF NOT EXISTS email_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    to_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT,
    template_id INT NULL,
    recipient_type ENUM('parent', 'teacher', 'student', 'admin', 'all') DEFAULT 'parent',
    recipient_id INT NULL COMMENT 'ID of parent, teacher, or student',
    success TINYINT(1) DEFAULT 0,
    error_message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES email_templates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default email templates
INSERT INTO email_templates (name, subject, body, variables, category) VALUES
('Fee Payment Reminder', 'Fee Payment Reminder - {school_name}', 
'Dear {parent_name},

This is a reminder that your child {student_name} (Admission No: {admission_number}) has an outstanding fee balance of KES {balance_amount} for {term} {academic_year}.

Please make payment at your earliest convenience to avoid any inconvenience.

Payment can be made via:
- M-Pesa PayBill: {paybill_number} Account: {admission_number}
- Bank Transfer: {bank_details}

Thank you for your prompt attention to this matter.

Best regards,
{school_name}',
'["parent_name", "student_name", "admission_number", "balance_amount", "term", "academic_year", "school_name", "paybill_number", "bank_details"]',
'fee'),

('Fee Payment Confirmation', 'Fee Payment Confirmation - {school_name}',
'Dear {parent_name},

We have received your payment of KES {payment_amount} for {student_name} (Admission No: {admission_number}).

Payment Details:
- Receipt Number: {receipt_number}
- Payment Date: {payment_date}
- Payment Method: {payment_method}
- Remaining Balance: KES {balance_amount}

Thank you for your payment.

Best regards,
{school_name}',
'["parent_name", "student_name", "admission_number", "payment_amount", "receipt_number", "payment_date", "payment_method", "balance_amount", "school_name"]',
'fee'),

('Academic Progress Update', 'Academic Progress Update - {student_name}',
'Dear {parent_name},

We would like to inform you about {student_name}\'s academic progress for {term} {academic_year}.

Overall Performance: {overall_percentage}% (Grade: {overall_grade})

Top Performing Subjects:
{top_subjects}

Areas for Improvement:
{improvement_areas}

We encourage you to discuss this progress with {student_name} and support their continued learning.

Best regards,
{teacher_name}
{school_name}',
'["parent_name", "student_name", "term", "academic_year", "overall_percentage", "overall_grade", "top_subjects", "improvement_areas", "teacher_name", "school_name"]',
'academic'),

('General Announcement', '{announcement_title}',
'Dear {recipient_name},

{announcement_content}

Best regards,
{school_name}',
'["recipient_name", "announcement_title", "announcement_content", "school_name"]',
'announcement'),

('Welcome Email', 'Welcome to {school_name}',
'Dear {parent_name},

Welcome to {school_name}! We are delighted to have {student_name} join our school community.

Student Details:
- Name: {student_name}
- Admission Number: {admission_number}
- Class: {class_name}
- Grade: {grade_name}

Important Information:
- School Contact: {school_phone}
- School Email: {school_email}
- School Address: {school_address}

We look forward to working together to ensure {student_name}\'s success.

Best regards,
{school_name}',
'["parent_name", "student_name", "admission_number", "class_name", "grade_name", "school_name", "school_phone", "school_email", "school_address"]',
'general');

