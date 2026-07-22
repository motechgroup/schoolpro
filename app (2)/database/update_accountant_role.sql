-- Update Accountant Role Permissions
-- Ensure accountant role has proper permissions for fee management

-- Update accountant role permissions if it exists
UPDATE roles 
SET permissions = '["students.view", "fees.view", "fees.create", "fees.edit", "fees.assign", "feeheads.view", "feeheads.create", "feeheads.edit", "payments.view", "payments.create", "payments.reconcile", "reports.view", "reports.financial", "examinations.view", "examinations.reports"]'
WHERE name = 'accountant';

-- If accountant role doesn't exist, create it
INSERT INTO roles (name, description, permissions) 
VALUES ('accountant', 'Accountant', '["students.view", "fees.view", "fees.create", "fees.edit", "fees.assign", "feeheads.view", "feeheads.create", "feeheads.edit", "payments.view", "payments.create", "payments.reconcile", "reports.view", "reports.financial", "examinations.view", "examinations.reports"]')
ON DUPLICATE KEY UPDATE 
    description = VALUES(description),
    permissions = VALUES(permissions);
