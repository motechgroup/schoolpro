-- Fix Admin Password Script
-- Run this script if you've already imported the database and need to fix the admin password
-- Password: admin123
-- This hash has been verified to work with password_verify()

USE masomo_school_db;

-- Update admin password to correct hash for 'admin123'
UPDATE users 
SET password = '$2y$10$BJPPKBgYDm5qSKBZ517IKOb1M2S1pD.RhwX2PorA6.6.QQaPeuz2y'
WHERE email = 'admin@school.co.ke';

-- Verify the update
SELECT id, email, first_name, last_name, status FROM users WHERE email = 'admin@school.co.ke';

