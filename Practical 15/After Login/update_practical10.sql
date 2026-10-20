-- =====================================================================
-- PRACTICAL 10: DATABASE UPDATE SCRIPT FOR PHPMYADMIN
-- Run this script if you already have 'studenthub_db' from Practical 9.
-- =====================================================================

USE studenthub_db;

-- 1. Add 'role' column to students table (if not already present)
ALTER TABLE students 
ADD COLUMN IF NOT EXISTS role ENUM('student', 'admin') NOT NULL DEFAULT 'student' AFTER semester;

-- 2. Create 'remember_tokens' table for secure persistent login tokens
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    selector VARCHAR(32) NOT NULL UNIQUE,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_remember_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Insert or update default Administrator account
-- Email: admin@studenthub.com | Password: Admin@123 | Student ID: ADMIN001 | Role: admin
INSERT INTO students (name, email, phone, student_id, department, semester, role, password_hash)
VALUES (
    'System Administrator',
    'admin@studenthub.com',
    '9999999999',
    'ADMIN001',
    'CSE',
    8,
    'admin',
    '$2y$10$OLyRcz1p7sp4s36D75GBieCn7CVGf1SJSwrAKfqlMxF6RF4TpjOfi'
)
ON DUPLICATE KEY UPDATE 
    role = 'admin',
    password_hash = '$2y$10$OLyRcz1p7sp4s36D75GBieCn7CVGf1SJSwrAKfqlMxF6RF4TpjOfi';

-- 4. Insert or update default Student test account
-- Email: student@studenthub.com | Password: Student@123 | Student ID: STU1001 | Role: student
INSERT INTO students (name, email, phone, student_id, department, semester, role, password_hash)
VALUES (
    'John Doe',
    'student@studenthub.com',
    '9876543210',
    'STU1001',
    'CSE',
    4,
    'student',
    '$2y$10$kL24PZloLt2CG8J38BTrXOqNftxfrVN6a0i644qu2DtfHeLr.suU2'
)
ON DUPLICATE KEY UPDATE 
    role = 'student',
    password_hash = '$2y$10$kL24PZloLt2CG8J38BTrXOqNftxfrVN6a0i644qu2DtfHeLr.suU2';

