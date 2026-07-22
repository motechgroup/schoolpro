-- Fix CMS Admin Password
-- This script updates the CMS admin password to 'admin123'
-- Run this if you've already imported the CMS tables

USE masomo_school_db;

-- Update existing admin password to 'admin123'
UPDATE cms_admins 
SET password = '$2y$10$U2K3yozLdtrGBUvZjZbHOe1uin9euwZiYkb22EKOkf/EmJYMrh61.',
    status = 'active'
WHERE email = 'admin@cms.local';

-- If admin doesn't exist, create it
INSERT INTO cms_admins (email, password, first_name, last_name, role, status) 
VALUES ('admin@cms.local', '$2y$10$U2K3yozLdtrGBUvZjZbHOe1uin9euwZiYkb22EKOkf/EmJYMrh61.', 'CMS', 'Administrator', 'super_admin', 'active')
ON DUPLICATE KEY UPDATE 
    password = '$2y$10$U2K3yozLdtrGBUvZjZbHOe1uin9euwZiYkb22EKOkf/EmJYMrh61.',
    status = 'active';

