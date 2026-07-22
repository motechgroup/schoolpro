-- ============================================================================
-- CLEANUP ORPHANED FEE RECORDS
-- ============================================================================
-- This script removes all fee-related records that are orphaned
-- (i.e., linked to non-existent students or invoices)
-- Use this when you have fee data but no student records
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_SAFE_UPDATES = 0;

-- ============================================================================
-- STEP 1: Delete M-Pesa transactions linked to non-existent payments
-- ============================================================================
DELETE FROM mpesa_transactions 
WHERE payment_id IS NOT NULL 
AND payment_id NOT IN (SELECT id FROM payments);

-- Also delete orphaned M-Pesa transactions (no payment_id and orphaned)
DELETE FROM mpesa_transactions 
WHERE payment_id IS NULL;

-- ============================================================================
-- STEP 2: Delete fee head payments linked to non-existent payments
-- ============================================================================
DELETE FROM fee_head_payments 
WHERE payment_id NOT IN (SELECT id FROM payments);

-- Delete fee head payments linked to non-existent student fee heads
DELETE FROM fee_head_payments 
WHERE student_fee_head_id NOT IN (SELECT id FROM student_fee_heads);

-- ============================================================================
-- STEP 3: Delete student fee heads for non-existent students
-- ============================================================================
DELETE FROM student_fee_heads 
WHERE student_id NOT IN (SELECT id FROM students);

-- ============================================================================
-- STEP 4: Delete invoice items for non-existent invoices
-- ============================================================================
DELETE FROM invoice_items 
WHERE invoice_id NOT IN (SELECT id FROM invoices);

-- ============================================================================
-- STEP 5: Delete invoices for non-existent students
-- ============================================================================
DELETE FROM invoices 
WHERE student_id NOT IN (SELECT id FROM students);

-- ============================================================================
-- STEP 6: Delete payments for non-existent invoices
-- ============================================================================
DELETE FROM payments 
WHERE invoice_id NOT IN (SELECT id FROM invoices);

-- ============================================================================
-- STEP 7: Delete payments for non-existent students
-- ============================================================================
DELETE FROM payments 
WHERE student_id NOT IN (SELECT id FROM students);

-- ============================================================================
-- STEP 8: Clean up any remaining orphaned records
-- ============================================================================

-- Delete any remaining M-Pesa transactions without valid payment references
DELETE FROM mpesa_transactions 
WHERE payment_id IS NOT NULL 
AND payment_id NOT IN (SELECT id FROM payments);

-- Delete any remaining fee head payments
DELETE FROM fee_head_payments 
WHERE payment_id NOT IN (SELECT id FROM payments)
   OR student_fee_head_id NOT IN (SELECT id FROM student_fee_heads);

-- Delete any remaining invoice items
DELETE FROM invoice_items 
WHERE invoice_id NOT IN (SELECT id FROM invoices);

-- Delete any remaining invoices
DELETE FROM invoices 
WHERE student_id NOT IN (SELECT id FROM students);

-- Delete any remaining payments
DELETE FROM payments 
WHERE invoice_id NOT IN (SELECT id FROM invoices)
   OR student_id NOT IN (SELECT id FROM students);

-- Delete any remaining student fee heads
DELETE FROM student_fee_heads 
WHERE student_id NOT IN (SELECT id FROM students);

-- ============================================================================
-- STEP 9: Reset AUTO_INCREMENT counters (optional)
-- ============================================================================
ALTER TABLE mpesa_transactions AUTO_INCREMENT = 1;
ALTER TABLE fee_head_payments AUTO_INCREMENT = 1;
ALTER TABLE invoice_items AUTO_INCREMENT = 1;
ALTER TABLE invoices AUTO_INCREMENT = 1;
ALTER TABLE payments AUTO_INCREMENT = 1;
ALTER TABLE student_fee_heads AUTO_INCREMENT = 1;

-- ============================================================================
-- STEP 10: Re-enable constraints
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 1;
SET SQL_SAFE_UPDATES = 1;

-- ============================================================================
-- VERIFICATION: Show remaining fee records
-- ============================================================================
SELECT 
    'Students' AS record_type,
    COUNT(*) AS count 
FROM students
UNION ALL
SELECT 'Invoices', COUNT(*) FROM invoices
UNION ALL
SELECT 'Invoice Items', COUNT(*) FROM invoice_items
UNION ALL
SELECT 'Payments', COUNT(*) FROM payments
UNION ALL
SELECT 'M-Pesa Transactions', COUNT(*) FROM mpesa_transactions
UNION ALL
SELECT 'Fee Head Payments', COUNT(*) FROM fee_head_payments
UNION ALL
SELECT 'Student Fee Heads', COUNT(*) FROM student_fee_heads;

-- ============================================================================
-- Show summary of what was cleaned
-- ============================================================================
SELECT 'Orphaned fee records cleanup completed!' AS status;

-- ============================================================================
-- If you still see fees in dashboard but no records above, 
-- check if there's cached data or aggregate queries that need recalculation
-- ============================================================================

