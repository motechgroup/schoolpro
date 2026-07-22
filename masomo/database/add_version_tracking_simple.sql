-- Simple version: Add version tracking fields to cms_schools table
-- Run this if the complex version fails

ALTER TABLE cms_schools 
ADD COLUMN current_version VARCHAR(50) NULL COMMENT 'Current version installed on school system',
ADD COLUMN last_code_update TIMESTAMP NULL COMMENT 'When the school system code was last updated',
ADD COLUMN version_last_reported TIMESTAMP NULL COMMENT 'When the school system last reported its version';

ALTER TABLE cms_schools ADD INDEX idx_current_version (current_version);

-- Add system version configuration (what version CMS expects)
CREATE TABLE IF NOT EXISTS cms_system_versions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    version_number VARCHAR(50) NOT NULL UNIQUE,
    release_date DATE NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    changelog TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_current (is_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default current version
INSERT INTO cms_system_versions (version_number, release_date, is_current, changelog) 
VALUES ('1.0.0', CURDATE(), TRUE, 'Initial release')
ON DUPLICATE KEY UPDATE version_number = version_number;

