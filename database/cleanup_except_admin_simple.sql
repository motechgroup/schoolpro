-- ============================================================================
-- SIMPLE DATABASE CLEANUP SCRIPT - Keep Only Admin User
-- ============================================================================
-- Quick cleanup script that removes all user data except admin
-- Preserves system tables (roles, grades, fee_heads, etc.)
--
-- WARNING: This will delete ALL student, parent, teacher, payment data!
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Get admin user ID
SET @admin_id = (SELECT id FROM users WHERE email = 'admin@school.co.ke' OR role_id = 1 LIMIT 1);

-- Delete all data in order (child tables first)
DELETE FROM activity_logs;
DELETE FROM mpesa_transactions;
DELETE FROM fee_head_payments;
DELETE FROM payments;
DELETE FROM invoice_items;
DELETE FROM invoices;
DELETE FROM student_fee_heads;
DELETE FROM assessments;
DELETE FROM teacher_attendance;
DELETE FROM student_attendance;
DELETE FROM students;
DELETE FROM parents;
DELETE FROM teachers WHERE user_id != @admin_id;
DELETE FROM users WHERE id != @admin_id;
DELETE FROM announcements;

-- Clean additional tables if they exist
DELETE FROM book_borrows;
DELETE FROM password_resets;
DELETE FROM email_logs;
DELETE FROM sms_logs;
DELETE FROM whatsapp_logs;
DELETE FROM examination_marks;
DELETE FROM student_library_ratings;
DELETE FROM equity_transactions;

SET FOREIGN_KEY_CHECKS = 1;

-- Show summary
SELECT 'Cleanup completed!' AS status;
SELECT COUNT(*) AS remaining_users FROM users;
SELECT COUNT(*) AS remaining_students FROM students;

