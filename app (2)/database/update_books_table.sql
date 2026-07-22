-- Update books table to use subject and class instead of category
-- Remove category column and add subject_id and class_id

-- Add subject_id and class_id columns
ALTER TABLE books 
ADD COLUMN subject_id INT NULL AFTER publisher,
ADD COLUMN class_id INT NULL AFTER subject_id;

-- Add foreign keys
ALTER TABLE books 
ADD FOREIGN KEY (subject_id) REFERENCES learning_areas(id) ON DELETE SET NULL,
ADD FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL;

-- Add indexes
ALTER TABLE books 
ADD INDEX idx_subject_id (subject_id),
ADD INDEX idx_class_id (class_id);

-- Note: The category column can be removed manually if needed:
-- ALTER TABLE books DROP COLUMN category;
-- ALTER TABLE books DROP INDEX idx_category;

