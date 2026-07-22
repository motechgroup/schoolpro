-- Library Module Database Schema
-- Creates tables for books and book borrowing/assignment

-- Books table
CREATE TABLE IF NOT EXISTS books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(50) UNIQUE,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255),
    publisher VARCHAR(255),
    category VARCHAR(100),
    edition VARCHAR(50),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    location VARCHAR(100),
    description TEXT,
    status ENUM('active', 'inactive', 'lost', 'damaged') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_isbn (isbn),
    INDEX idx_title (title),
    INDEX idx_category (category),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Book borrows/assignments table
CREATE TABLE IF NOT EXISTS book_borrows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    borrowed_by INT, -- User ID of librarian who issued the book
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    returned_to INT NULL, -- User ID of librarian who received the book
    status ENUM('borrowed', 'returned', 'overdue', 'lost') DEFAULT 'borrowed',
    fine_amount DECIMAL(10, 2) DEFAULT 0.00,
    fine_paid BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE RESTRICT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT,
    FOREIGN KEY (borrowed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (returned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_book_id (book_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add librarian role
INSERT INTO roles (name, description, permissions) VALUES
('librarian', 'Librarian', '["library.view", "library.books.view", "library.books.create", "library.books.edit", "library.books.delete", "library.borrow.view", "library.borrow.create", "library.borrow.return", "library.reports.view", "students.view"]')
ON DUPLICATE KEY UPDATE 
    description = VALUES(description),
    permissions = VALUES(permissions);

-- Add library permissions to super_admin and school_admin roles
UPDATE roles SET permissions = JSON_MERGE_PRESERVE(
    COALESCE(permissions, '[]'),
    '["library.view", "library.books.view", "library.books.create", "library.books.edit", "library.books.delete", "library.borrow.view", "library.borrow.create", "library.borrow.return", "library.reports.view"]'
) WHERE name IN ('super_admin', 'school_admin', 'school_manager');

