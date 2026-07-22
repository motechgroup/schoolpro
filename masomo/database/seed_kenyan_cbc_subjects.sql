-- Kenyan CBC Subjects Seed Data
-- This script populates standard subjects/learning areas for all grades
-- Based on the Competency-Based Curriculum (CBC) structure

-- Note: This assumes grades have been created with the following names:
-- PP1, PP2, G1, G2, G3, G4, G5, G6, G7, G8, G9

-- Pre-Primary 1 (PP1) Subjects
INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENG-PP1', 'English Language Activities', 'English language activities for Pre-Primary 1', id FROM grades WHERE name = 'PP1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'KIS-PP1', 'Kiswahili Language Activities', 'Kiswahili language activities for Pre-Primary 1', id FROM grades WHERE name = 'PP1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MAT-PP1', 'Mathematical Activities', 'Mathematical activities for Pre-Primary 1', id FROM grades WHERE name = 'PP1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENV-PP1', 'Environmental Activities', 'Environmental activities for Pre-Primary 1', id FROM grades WHERE name = 'PP1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'HNU-PP1', 'Hygiene and Nutrition Activities', 'Hygiene and nutrition activities for Pre-Primary 1', id FROM grades WHERE name = 'PP1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'REL-PP1', 'Religious Education Activities', 'Religious education activities (CRE/IRE/HRE) for Pre-Primary 1', id FROM grades WHERE name = 'PP1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MCA-PP1', 'Movement and Creative Activities', 'Movement and creative activities for Pre-Primary 1', id FROM grades WHERE name = 'PP1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Pre-Primary 2 (PP2) Subjects
INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENG-PP2', 'English Language Activities', 'English language activities for Pre-Primary 2', id FROM grades WHERE name = 'PP2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'KIS-PP2', 'Kiswahili Language Activities', 'Kiswahili language activities for Pre-Primary 2', id FROM grades WHERE name = 'PP2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MAT-PP2', 'Mathematical Activities', 'Mathematical activities for Pre-Primary 2', id FROM grades WHERE name = 'PP2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENV-PP2', 'Environmental Activities', 'Environmental activities for Pre-Primary 2', id FROM grades WHERE name = 'PP2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'HNU-PP2', 'Hygiene and Nutrition Activities', 'Hygiene and nutrition activities for Pre-Primary 2', id FROM grades WHERE name = 'PP2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'REL-PP2', 'Religious Education Activities', 'Religious education activities (CRE/IRE/HRE) for Pre-Primary 2', id FROM grades WHERE name = 'PP2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MCA-PP2', 'Movement and Creative Activities', 'Movement and creative activities for Pre-Primary 2', id FROM grades WHERE name = 'PP2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Lower Primary (Grade 1-3) Subjects
-- Grade 1
INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENG-G1', 'English Language Activities', 'English language activities for Grade 1', id FROM grades WHERE name = 'G1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'KIS-G1', 'Kiswahili Language Activities', 'Kiswahili language activities for Grade 1', id FROM grades WHERE name = 'G1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MAT-G1', 'Mathematics Activities', 'Mathematics activities for Grade 1', id FROM grades WHERE name = 'G1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENV-G1', 'Environmental Activities', 'Environmental activities for Grade 1', id FROM grades WHERE name = 'G1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'HNU-G1', 'Hygiene and Nutrition Activities', 'Hygiene and nutrition activities for Grade 1', id FROM grades WHERE name = 'G1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'REL-G1', 'Religious Education Activities', 'Religious education activities (CRE/IRE/HRE) for Grade 1', id FROM grades WHERE name = 'G1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MCA-G1', 'Movement and Creative Activities', 'Movement and creative activities for Grade 1', id FROM grades WHERE name = 'G1'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Grade 2
INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENG-G2', 'English Language Activities', 'English language activities for Grade 2', id FROM grades WHERE name = 'G2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'KIS-G2', 'Kiswahili Language Activities', 'Kiswahili language activities for Grade 2', id FROM grades WHERE name = 'G2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MAT-G2', 'Mathematics Activities', 'Mathematics activities for Grade 2', id FROM grades WHERE name = 'G2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENV-G2', 'Environmental Activities', 'Environmental activities for Grade 2', id FROM grades WHERE name = 'G2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'HNU-G2', 'Hygiene and Nutrition Activities', 'Hygiene and nutrition activities for Grade 2', id FROM grades WHERE name = 'G2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'REL-G2', 'Religious Education Activities', 'Religious education activities (CRE/IRE/HRE) for Grade 2', id FROM grades WHERE name = 'G2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MCA-G2', 'Movement and Creative Activities', 'Movement and creative activities for Grade 2', id FROM grades WHERE name = 'G2'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Grade 3
INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENG-G3', 'English Language Activities', 'English language activities for Grade 3', id FROM grades WHERE name = 'G3'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'KIS-G3', 'Kiswahili Language Activities', 'Kiswahili language activities for Grade 3', id FROM grades WHERE name = 'G3'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MAT-G3', 'Mathematics Activities', 'Mathematics activities for Grade 3', id FROM grades WHERE name = 'G3'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENV-G3', 'Environmental Activities', 'Environmental activities for Grade 3', id FROM grades WHERE name = 'G3'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'HNU-G3', 'Hygiene and Nutrition Activities', 'Hygiene and nutrition activities for Grade 3', id FROM grades WHERE name = 'G3'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'REL-G3', 'Religious Education Activities', 'Religious education activities (CRE/IRE/HRE) for Grade 3', id FROM grades WHERE name = 'G3'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MCA-G3', 'Movement and Creative Activities', 'Movement and creative activities for Grade 3', id FROM grades WHERE name = 'G3'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Upper Primary (Grade 4-6) Subjects
-- Grade 4
INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENG-G4', 'English', 'English language for Grade 4', id FROM grades WHERE name = 'G4'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'KIS-G4', 'Kiswahili', 'Kiswahili language for Grade 4', id FROM grades WHERE name = 'G4'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MAT-G4', 'Mathematics', 'Mathematics for Grade 4', id FROM grades WHERE name = 'G4'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'SCI-G4', 'Science and Technology', 'Science and Technology for Grade 4', id FROM grades WHERE name = 'G4'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'SST-G4', 'Social Studies', 'Social Studies for Grade 4', id FROM grades WHERE name = 'G4'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'REL-G4', 'Religious Education', 'Religious Education (CRE/IRE/HRE) for Grade 4', id FROM grades WHERE name = 'G4'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'CRE-G4', 'Creative Arts', 'Creative Arts for Grade 4', id FROM grades WHERE name = 'G4'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'PHE-G4', 'Physical and Health Education', 'Physical and Health Education for Grade 4', id FROM grades WHERE name = 'G4'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'AGR-G4', 'Agriculture', 'Agriculture for Grade 4', id FROM grades WHERE name = 'G4'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'HSC-G4', 'Home Science', 'Home Science for Grade 4', id FROM grades WHERE name = 'G4'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Grade 5
INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENG-G5', 'English', 'English language for Grade 5', id FROM grades WHERE name = 'G5'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'KIS-G5', 'Kiswahili', 'Kiswahili language for Grade 5', id FROM grades WHERE name = 'G5'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MAT-G5', 'Mathematics', 'Mathematics for Grade 5', id FROM grades WHERE name = 'G5'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'SCI-G5', 'Science and Technology', 'Science and Technology for Grade 5', id FROM grades WHERE name = 'G5'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'SST-G5', 'Social Studies', 'Social Studies for Grade 5', id FROM grades WHERE name = 'G5'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'REL-G5', 'Religious Education', 'Religious Education (CRE/IRE/HRE) for Grade 5', id FROM grades WHERE name = 'G5'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'CRE-G5', 'Creative Arts', 'Creative Arts for Grade 5', id FROM grades WHERE name = 'G5'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'PHE-G5', 'Physical and Health Education', 'Physical and Health Education for Grade 5', id FROM grades WHERE name = 'G5'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'AGR-G5', 'Agriculture', 'Agriculture for Grade 5', id FROM grades WHERE name = 'G5'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'HSC-G5', 'Home Science', 'Home Science for Grade 5', id FROM grades WHERE name = 'G5'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Grade 6
INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENG-G6', 'English', 'English language for Grade 6', id FROM grades WHERE name = 'G6'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'KIS-G6', 'Kiswahili', 'Kiswahili language for Grade 6', id FROM grades WHERE name = 'G6'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MAT-G6', 'Mathematics', 'Mathematics for Grade 6', id FROM grades WHERE name = 'G6'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'SCI-G6', 'Science and Technology', 'Science and Technology for Grade 6', id FROM grades WHERE name = 'G6'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'SST-G6', 'Social Studies', 'Social Studies for Grade 6', id FROM grades WHERE name = 'G6'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'REL-G6', 'Religious Education', 'Religious Education (CRE/IRE/HRE) for Grade 6', id FROM grades WHERE name = 'G6'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'CRE-G6', 'Creative Arts', 'Creative Arts for Grade 6', id FROM grades WHERE name = 'G6'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'PHE-G6', 'Physical and Health Education', 'Physical and Health Education for Grade 6', id FROM grades WHERE name = 'G6'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'AGR-G6', 'Agriculture', 'Agriculture for Grade 6', id FROM grades WHERE name = 'G6'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'HSC-G6', 'Home Science', 'Home Science for Grade 6', id FROM grades WHERE name = 'G6'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Junior Secondary School (Grade 7-9) Subjects
-- Grade 7 (JSS)
INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENG-G7', 'English', 'English language for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'KIS-G7', 'Kiswahili', 'Kiswahili language for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MAT-G7', 'Mathematics', 'Mathematics for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'INT-G7', 'Integrated Science', 'Integrated Science for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'SST-G7', 'Social Studies', 'Social Studies for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'REL-G7', 'Religious Education', 'Religious Education (CRE/IRE/HRE) for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'BUS-G7', 'Business Studies', 'Business Studies for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'AGR-G7', 'Agriculture', 'Agriculture for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'HSC-G7', 'Home Science', 'Home Science for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ART-G7', 'Art and Design', 'Art and Design for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MUS-G7', 'Music', 'Music for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'PHE-G7', 'Physical and Health Education', 'Physical and Health Education for Grade 7 (JSS)', id FROM grades WHERE name = 'G7'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Grade 8 (JSS)
INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENG-G8', 'English', 'English language for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'KIS-G8', 'Kiswahili', 'Kiswahili language for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MAT-G8', 'Mathematics', 'Mathematics for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'INT-G8', 'Integrated Science', 'Integrated Science for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'SST-G8', 'Social Studies', 'Social Studies for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'REL-G8', 'Religious Education', 'Religious Education (CRE/IRE/HRE) for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'BUS-G8', 'Business Studies', 'Business Studies for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'AGR-G8', 'Agriculture', 'Agriculture for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'HSC-G8', 'Home Science', 'Home Science for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ART-G8', 'Art and Design', 'Art and Design for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MUS-G8', 'Music', 'Music for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'PHE-G8', 'Physical and Health Education', 'Physical and Health Education for Grade 8 (JSS)', id FROM grades WHERE name = 'G8'
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Grade 9 (JSS)
INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ENG-G9', 'English', 'English language for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'KIS-G9', 'Kiswahili', 'Kiswahili language for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MAT-G9', 'Mathematics', 'Mathematics for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'INT-G9', 'Integrated Science', 'Integrated Science for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'SST-G9', 'Social Studies', 'Social Studies for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'REL-G9', 'Religious Education', 'Religious Education (CRE/IRE/HRE) for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'BUS-G9', 'Business Studies', 'Business Studies for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'AGR-G9', 'Agriculture', 'Agriculture for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'HSC-G9', 'Home Science', 'Home Science for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'ART-G9', 'Art and Design', 'Art and Design for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'MUS-G9', 'Music', 'Music for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO learning_areas (code, name, description, grade_id) 
SELECT 'PHE-G9', 'Physical and Health Education', 'Physical and Health Education for Grade 9 (JSS)', id FROM grades WHERE name = 'G9'
ON DUPLICATE KEY UPDATE name = VALUES(name);

