-- Academic Years and Terms Management
-- This allows super admins to set when terms start and end, and manage academic years

USE masomo_school_db;

-- Academic Years table
CREATE TABLE IF NOT EXISTS academic_years (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(20) UNIQUE NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('upcoming', 'active', 'completed', 'archived') DEFAULT 'upcoming',
    is_current BOOLEAN DEFAULT FALSE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Terms table (linked to academic years)
CREATE TABLE IF NOT EXISTS terms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    academic_year_id INT NOT NULL,
    term_number INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('upcoming', 'active', 'completed', 'archived') DEFAULT 'upcoming',
    is_current BOOLEAN DEFAULT FALSE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_term_year (academic_year_id, term_number),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default academic year if none exists
INSERT INTO academic_years (name, start_date, end_date, status, is_current, created_by)
SELECT '2024/2025', '2024-01-08', '2024-12-20', 'active', TRUE, 1
WHERE NOT EXISTS (SELECT 1 FROM academic_years WHERE name = '2024/2025');

-- Insert default terms for the default academic year
SET @academic_year_id = (SELECT id FROM academic_years WHERE name = '2024/2025' LIMIT 1);

INSERT INTO terms (academic_year_id, term_number, name, start_date, end_date, status, is_current, created_by)
SELECT @academic_year_id, 1, 'Term 1', '2024-01-08', '2024-04-05', 'completed', FALSE, 1
WHERE NOT EXISTS (SELECT 1 FROM terms WHERE academic_year_id = @academic_year_id AND term_number = 1);

INSERT INTO terms (academic_year_id, term_number, name, start_date, end_date, status, is_current, created_by)
SELECT @academic_year_id, 2, 'Term 2', '2024-05-06', '2024-08-09', 'completed', FALSE, 1
WHERE NOT EXISTS (SELECT 1 FROM terms WHERE academic_year_id = @academic_year_id AND term_number = 2);

INSERT INTO terms (academic_year_id, term_number, name, start_date, end_date, status, is_current, created_by)
SELECT @academic_year_id, 3, 'Term 3', '2024-09-02', '2024-12-20', 'active', TRUE, 1
WHERE NOT EXISTS (SELECT 1 FROM terms WHERE academic_year_id = @academic_year_id AND term_number = 3);

