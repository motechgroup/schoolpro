-- ============================================================================
-- DATABASE CLEANUP SCRIPT - Keep Only Admin User
-- ============================================================================
-- This script cleans all data from the database except:
-- 1. The admin user (identified by email 'admin@school.co.ke' or role 'super_admin')
-- 2. System tables (roles, grades) - data is preserved
-- 3. Settings table (if exists)
--
-- WARNING: This will delete ALL student, parent, teacher, payment, invoice data!
-- Make sure you have a backup before running this script!
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_SAFE_UPDATES = 0;

-- ============================================================================
-- STEP 1: Identify and preserve admin user ID
-- ============================================================================
-- Get admin user ID (adjust email if your admin uses a different email)
SET @admin_user_id = (SELECT id FROM users WHERE email = 'admin@school.co.ke' OR role_id = 1 LIMIT 1);

-- If no admin found, create one
INSERT INTO users (role_id, email, password, first_name, last_name, status)
SELECT 1, 'admin@school.co.ke', '$2y$10$BJPPKBgYDm5qSKBZ517IKOb1M2S1pD.RhwX2PorA6.6.QQaPeuz2y', 'Super', 'Admin', 'active'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@school.co.ke' OR role_id = 1);

SET @admin_user_id = (SELECT id FROM users WHERE email = 'admin@school.co.ke' OR role_id = 1 LIMIT 1);

-- ============================================================================
-- STEP 2: Delete all activity logs (except admin's if needed)
-- ============================================================================
DELETE FROM activity_logs;

-- ============================================================================
-- STEP 3: Delete all announcements
-- ============================================================================
DELETE FROM announcements;

-- ============================================================================
-- STEP 4: Delete all M-Pesa transactions
-- ============================================================================
DELETE FROM mpesa_transactions;

-- ============================================================================
-- STEP 5: Delete all payments
-- ============================================================================
DELETE FROM payments;

-- ============================================================================
-- STEP 6: Delete fee head payments
-- ============================================================================
DELETE FROM fee_head_payments;

-- ============================================================================
-- STEP 7: Delete invoice items
-- ============================================================================
DELETE FROM invoice_items;

-- ============================================================================
-- STEP 8: Delete all invoices
-- ============================================================================
DELETE FROM invoices;

-- ============================================================================
-- STEP 9: Delete student fee heads
-- ============================================================================
DELETE FROM student_fee_heads;

-- ============================================================================
-- STEP 10: Delete fee structure (optional - comment out if you want to keep fee structures)
-- ============================================================================
-- DELETE FROM fee_structure;

-- ============================================================================
-- STEP 11: Delete all assessments
-- ============================================================================
DELETE FROM assessments;

-- ============================================================================
-- STEP 12: Delete competency levels data (if custom, otherwise keep system defaults)
-- ============================================================================
-- DELETE FROM competency_levels;

-- ============================================================================
-- STEP 13: Delete sub-strands (if custom, otherwise keep system defaults)
-- ============================================================================
-- DELETE FROM sub_strands;

-- ============================================================================
-- STEP 14: Delete strands (if custom, otherwise keep system defaults)
-- ============================================================================
-- DELETE FROM strands;

-- ============================================================================
-- STEP 15: Delete learning areas (if custom, otherwise keep system defaults)
-- ============================================================================
-- DELETE FROM learning_areas;

-- ============================================================================
-- STEP 16: Delete teacher attendance
-- ============================================================================
DELETE FROM teacher_attendance;

-- ============================================================================
-- STEP 17: Delete student attendance
-- ============================================================================
DELETE FROM student_attendance;

-- ============================================================================
-- STEP 18: Delete all students
-- ============================================================================
DELETE FROM students;

-- ============================================================================
-- STEP 19: Delete all parents/guardians
-- ============================================================================
DELETE FROM parents;

-- ============================================================================
-- STEP 20: Delete all classes (optional - comment out if you want to keep class structure)
-- ============================================================================
-- DELETE FROM classes;

-- ============================================================================
-- STEP 21: Delete all teachers (except those linked to admin user if any)
-- ============================================================================
DELETE FROM teachers WHERE user_id != @admin_user_id;

-- ============================================================================
-- STEP 22: Delete all users except admin
-- ============================================================================
DELETE FROM users WHERE id != @admin_user_id;

-- ============================================================================
-- STEP 23: Clean up additional tables (if they exist)
-- ============================================================================

-- Book borrows
DELETE FROM book_borrows WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'book_borrows');

-- Books (optional - comment out if you want to keep library books)
-- DELETE FROM books WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'books');

-- Password resets
DELETE FROM password_resets WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'password_resets');

-- Email logs
DELETE FROM email_logs WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'email_logs');

-- SMS logs
DELETE FROM sms_logs WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sms_logs');

-- WhatsApp logs
DELETE FROM whatsapp_logs WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'whatsapp_logs');

-- Examination marks
DELETE FROM examination_marks WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'examination_marks');

-- Examination subjects (optional)
-- DELETE FROM examination_subjects WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'examination_subjects');

-- Examinations (optional)
-- DELETE FROM examinations WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'examinations');

-- Student library ratings
DELETE FROM student_library_ratings WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'student_library_ratings');

-- Equity transactions
DELETE FROM equity_transactions WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'equity_transactions');

-- Academic years (optional - comment out if you want to keep academic year setup)
-- DELETE FROM academic_years WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'academic_years');

-- Terms (optional - comment out if you want to keep terms setup)
-- DELETE FROM terms WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'terms');

-- CMS tables (only if you want to clean these - usually you want to keep CMS data)
-- DELETE FROM cms_demo_requests WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cms_demo_requests');

-- ============================================================================
-- STEP 24: Reset AUTO_INCREMENT counters (optional, makes IDs start from 1 again)
-- ============================================================================
ALTER TABLE activity_logs AUTO_INCREMENT = 1;
ALTER TABLE announcements AUTO_INCREMENT = 1;
ALTER TABLE assessments AUTO_INCREMENT = 1;
ALTER TABLE book_borrows AUTO_INCREMENT = 1 WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'book_borrows');
ALTER TABLE books AUTO_INCREMENT = 1 WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'books');
ALTER TABLE classes AUTO_INCREMENT = 1;
ALTER TABLE competency_levels AUTO_INCREMENT = 1;
ALTER TABLE email_logs AUTO_INCREMENT = 1 WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'email_logs');
ALTER TABLE examination_marks AUTO_INCREMENT = 1 WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'examination_marks');
ALTER TABLE examinations AUTO_INCREMENT = 1 WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'examinations');
ALTER TABLE fee_head_payments AUTO_INCREMENT = 1;
ALTER TABLE fee_structure AUTO_INCREMENT = 1;
ALTER TABLE invoice_items AUTO_INCREMENT = 1;
ALTER TABLE invoices AUTO_INCREMENT = 1;
ALTER TABLE learning_areas AUTO_INCREMENT = 1;
ALTER TABLE mpesa_transactions AUTO_INCREMENT = 1;
ALTER TABLE parents AUTO_INCREMENT = 1;
ALTER TABLE password_resets AUTO_INCREMENT = 1 WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'password_resets');
ALTER TABLE payments AUTO_INCREMENT = 1;
ALTER TABLE sms_logs AUTO_INCREMENT = 1 WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sms_logs');
ALTER TABLE strands AUTO_INCREMENT = 1;
ALTER TABLE student_attendance AUTO_INCREMENT = 1;
ALTER TABLE student_fee_heads AUTO_INCREMENT = 1;
ALTER TABLE student_library_ratings AUTO_INCREMENT = 1 WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'student_library_ratings');
ALTER TABLE students AUTO_INCREMENT = 1;
ALTER TABLE sub_strands AUTO_INCREMENT = 1;
ALTER TABLE teacher_attendance AUTO_INCREMENT = 1;
ALTER TABLE teachers AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 2;
ALTER TABLE whatsapp_logs AUTO_INCREMENT = 1 WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'whatsapp_logs');

-- ============================================================================
-- STEP 25: Re-enable foreign key checks and safe updates
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 1;
SET SQL_SAFE_UPDATES = 1;

-- ============================================================================
-- VERIFICATION: Show remaining data counts
-- ============================================================================
SELECT 'Users' AS table_name, COUNT(*) AS record_count FROM users
UNION ALL
SELECT 'Students', COUNT(*) FROM students
UNION ALL
SELECT 'Parents', COUNT(*) FROM parents
UNION ALL
SELECT 'Teachers', COUNT(*) FROM teachers
UNION ALL
SELECT 'Payments', COUNT(*) FROM payments
UNION ALL
SELECT 'Invoices', COUNT(*) FROM invoices
UNION ALL
SELECT 'Classes', COUNT(*) FROM classes
UNION ALL
SELECT 'Roles', COUNT(*) FROM roles
UNION ALL
SELECT 'Grades', COUNT(*) FROM grades;

-- ============================================================================
-- Script completed!
-- You should now have only:
-- - Admin user (email: admin@school.co.ke, password: admin123)
-- - System roles
-- - System grades
-- - Settings (if any)
-- All other data has been removed.
-- ============================================================================

