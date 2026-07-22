-- Examination Module Database Schema
-- Add tables for examinations and marks

-- Examinations table - stores exam definitions
CREATE TABLE IF NOT EXISTS examinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    class_id INT NOT NULL,
    term INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    exam_date DATE,
    total_marks DECIMAL(5,2) DEFAULT 100.00,
    passing_marks DECIMAL(5,2) DEFAULT 40.00,
    status ENUM('draft', 'active', 'completed', 'archived') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_exam_class_term (class_id, term, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Examination Subjects table - links examinations to learning areas/subjects
CREATE TABLE IF NOT EXISTS examination_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    examination_id INT NOT NULL,
    learning_area_id INT NOT NULL,
    max_marks DECIMAL(5,2) DEFAULT 100.00,
    passing_marks DECIMAL(5,2) DEFAULT 40.00,
    teacher_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (examination_id) REFERENCES examinations(id) ON DELETE CASCADE,
    FOREIGN KEY (learning_area_id) REFERENCES learning_areas(id) ON DELETE RESTRICT,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    UNIQUE KEY unique_exam_subject (examination_id, learning_area_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Examination Marks table - stores marks for each student per subject
CREATE TABLE IF NOT EXISTS examination_marks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    examination_id INT NOT NULL,
    examination_subject_id INT NOT NULL,
    student_id INT NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    grade VARCHAR(2),
    remarks TEXT,
    entered_by INT NOT NULL,
    entered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (examination_id) REFERENCES examinations(id) ON DELETE CASCADE,
    FOREIGN KEY (examination_subject_id) REFERENCES examination_subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (entered_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_student_subject_mark (examination_id, examination_subject_id, student_id),
    INDEX idx_exam_student (examination_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

