-- Add JSS Grades (Grade 7, 8, 9) to existing grades
-- Run this if you've already imported the database

USE masomo_school_db;

INSERT INTO grades (name, display_name, level) VALUES
('G7', 'Grade 7 (JSS)', 9),
('G8', 'Grade 8 (JSS)', 10),
('G9', 'Grade 9 (JSS)', 11)
ON DUPLICATE KEY UPDATE display_name = VALUES(display_name);

