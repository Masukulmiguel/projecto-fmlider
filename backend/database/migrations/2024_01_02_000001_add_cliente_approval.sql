USE fmlider;

ALTER TABLE users
    ADD COLUMN username VARCHAR(80) UNIQUE AFTER id,
    ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER status,
    ADD COLUMN approved_at TIMESTAMP NULL AFTER approval_status,
    ADD COLUMN approved_by BIGINT UNSIGNED NULL AFTER approved_at,
    ADD COLUMN rejection_reason VARCHAR(255) NULL AFTER approved_by,
    ADD INDEX idx_username (username),
    ADD INDEX idx_approval_status (approval_status),
    ADD CONSTRAINT fk_users_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL;

UPDATE users SET username = LOWER(REPLACE(SUBSTRING_INDEX(email, '@', 1), '.', '')) WHERE username IS NULL;
UPDATE users SET approval_status = 'approved' WHERE role = 'admin' AND approval_status = 'pending';

CREATE TABLE IF NOT EXISTS companies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    company_name VARCHAR(255) NOT NULL,
    nif VARCHAR(50),
    logo VARCHAR(255),
    address VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(255),
    service VARCHAR(150),
    case_description LONGTEXT,
    is_completed TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_completed (is_completed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
