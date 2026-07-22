-- Add new roles for RBAC
INSERT INTO roles (name, description, permissions) VALUES
('accountant', 'Accountant', '["students.view", "fees.view", "fees.create", "fees.edit", "payments.view", "payments.create", "reports.view", "reports.financial"]'),
('school_manager', 'School Manager (Admin)', '["students.view", "students.create", "students.edit", "teachers.view", "teachers.create", "teachers.edit", "fees.view", "fees.create", "fees.edit", "reports.view", "attendance.view", "assessments.view"]'),
('receptionist', 'Receptionist', '["students.view", "students.create", "students.edit", "parents.view", "parents.create", "parents.edit", "attendance.view", "attendance.create"]')
ON DUPLICATE KEY UPDATE description=VALUES(description), permissions=VALUES(permissions);

-- Add photo column to teachers table if it doesn't exist
-- Note: Run this only if the column doesn't exist
-- ALTER TABLE teachers ADD COLUMN photo VARCHAR(255) NULL AFTER specialization;

