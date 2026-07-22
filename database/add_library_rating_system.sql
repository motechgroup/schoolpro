-- Library Rating System
-- Tracks student ratings, points, and book condition

-- Student library ratings table
CREATE TABLE IF NOT EXISTS student_library_ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    total_points INT DEFAULT 0,
    total_borrows INT DEFAULT 0,
    total_returns INT DEFAULT 0,
    on_time_returns INT DEFAULT 0,
    late_returns INT DEFAULT 0,
    damaged_books INT DEFAULT 0,
    lost_books INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 5.00, -- Rating out of 5.00
    borrowing_level ENUM('excellent', 'good', 'fair', 'poor', 'restricted') DEFAULT 'good',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_rating (student_id),
    INDEX idx_rating (rating),
    INDEX idx_borrowing_level (borrowing_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Book return conditions tracking
-- Note: Run these ALTER TABLE statements. If columns already exist, you'll get an error which can be ignored.

ALTER TABLE book_borrows 
ADD COLUMN book_condition ENUM('excellent', 'good', 'fair', 'poor', 'damaged') DEFAULT 'good' AFTER return_date;

ALTER TABLE book_borrows 
ADD COLUMN condition_notes TEXT NULL AFTER book_condition;

ALTER TABLE book_borrows 
ADD COLUMN points_awarded INT DEFAULT 0 AFTER fine_paid;

ALTER TABLE book_borrows 
ADD COLUMN points_deducted INT DEFAULT 0 AFTER points_awarded;

-- Add borrowing limit based on rating
ALTER TABLE student_library_ratings
ADD COLUMN max_borrows INT DEFAULT 3 AFTER borrowing_level;

-- Insert default ratings for existing students (optional - can be done via migration)
-- This will be handled by the application when students first borrow books

