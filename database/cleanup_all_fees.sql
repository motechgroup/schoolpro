-- ============================================================================
-- COMPLETE FEE RECORDS CLEANUP
-- ============================================================================
-- This script removes ALL fee-related records regardless of student existence
-- Use this to completely clear all fees, payments, and invoices
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_SAFE_UPDATES = 0;

-- ============================================================================
-- Delete all fee-related records in proper order (child tables first)
-- ============================================================================

-- Delete M-Pesa transactions
DELETE FROM mpesa_transactions;

-- Delete fee head payments
DELETE FROM fee_head_payments;

-- Delete invoice items
DELETE FROM invoice_items;

-- Delete payments
DELETE FROM payments;

-- Delete invoices
DELETE FROM invoices;

-- Delete student fee heads
DELETE FROM student_fee_heads;

-- ============================================================================
-- Reset AUTO_INCREMENT counters
-- ============================================================================
ALTER TABLE mpesa_transactions AUTO_INCREMENT = 1;
ALTER TABLE fee_head_payments AUTO_INCREMENT = 1;
ALTER TABLE invoice_items AUTO_INCREMENT = 1;
ALTER TABLE payments AUTO_INCREMENT = 1;
ALTER TABLE invoices AUTO_INCREMENT = 1;
ALTER TABLE student_fee_heads AUTO_INCREMENT = 1;

-- ============================================================================
-- Re-enable constraints
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 1;
SET SQL_SAFE_UPDATES = 1;

-- ============================================================================
-- Verification
-- ============================================================================
SELECT 
    'Invoices' AS record_type,
    COUNT(*) AS count 
FROM invoices
UNION ALL
SELECT 'Payments', COUNT(*) FROM payments
UNION ALL
SELECT 'M-Pesa Transactions', COUNT(*) FROM mpesa_transactions
UNION ALL
SELECT 'Fee Head Payments', COUNT(*) FROM fee_head_payments
UNION ALL
SELECT 'Student Fee Heads', COUNT(*) FROM student_fee_heads;

SELECT 'All fee records deleted successfully!' AS status;

