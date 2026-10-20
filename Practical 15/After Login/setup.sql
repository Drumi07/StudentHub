CREATE DATABASE IF NOT EXISTS studenthub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub_db;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(10) NOT NULL,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    department VARCHAR(10) NOT NULL,
    semester TINYINT UNSIGNED NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_role VARCHAR(20) NOT NULL,
    action VARCHAR(100) NOT NULL,
    details VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_created_at (created_at),
    INDEX idx_audit_user_id (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    selector VARCHAR(32) NOT NULL UNIQUE,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_remember_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL UNIQUE,
    category VARCHAR(30) NOT NULL,
    event_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_registration_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_registration_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT unique_student_event UNIQUE (student_id, event_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject VARCHAR(150) NOT NULL,
    feedback TEXT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Default Events
INSERT INTO events (title, category, event_date) VALUES ('Oodo Hackathon', 'technical', '2026-09-25') ON DUPLICATE KEY UPDATE category = VALUES(category), event_date = VALUES(event_date);
INSERT INTO events (title, category, event_date) VALUES ('Coding Competition', 'technical', '2026-09-15') ON DUPLICATE KEY UPDATE category = VALUES(category), event_date = VALUES(event_date);
INSERT INTO events (title, category, event_date) VALUES ('Web Development Workshop', 'technical', '2026-09-30') ON DUPLICATE KEY UPDATE category = VALUES(category), event_date = VALUES(event_date);
INSERT INTO events (title, category, event_date) VALUES ('TechFeud', 'technical', '2026-09-19') ON DUPLICATE KEY UPDATE category = VALUES(category), event_date = VALUES(event_date);
INSERT INTO events (title, category, event_date) VALUES ('Tree Plantation', 'non-technical', '2026-10-25') ON DUPLICATE KEY UPDATE category = VALUES(category), event_date = VALUES(event_date);
INSERT INTO events (title, category, event_date) VALUES ('Music Club Event', 'non-technical', '2026-09-13') ON DUPLICATE KEY UPDATE category = VALUES(category), event_date = VALUES(event_date);
INSERT INTO events (title, category, event_date) VALUES ('Photography Competition', 'non-technical', '2026-09-19') ON DUPLICATE KEY UPDATE category = VALUES(category), event_date = VALUES(event_date);
INSERT INTO events (title, category, event_date) VALUES ('Cultural Fest', 'non-technical', '2026-09-27') ON DUPLICATE KEY UPDATE category = VALUES(category), event_date = VALUES(event_date);

-- Default Administrator account (Email: admin@studenthub.com | Password: Admin@123 | ID: ADMIN001)
INSERT INTO students (name, email, phone, student_id, department, semester, role, password_hash)
VALUES ('System Administrator', 'admin@studenthub.com', '9999999999', 'ADMIN001', 'CSE', 8, 'admin', '$2y$10$OLyRcz1p7sp4s36D75GBieCn7CVGf1SJSwrAKfqlMxF6RF4TpjOfi')
ON DUPLICATE KEY UPDATE role = 'admin', password_hash = '$2y$10$OLyRcz1p7sp4s36D75GBieCn7CVGf1SJSwrAKfqlMxF6RF4TpjOfi';

-- Default Student account (Email: student@studenthub.com | Password: Student@123 | ID: STU1001)
INSERT INTO students (name, email, phone, student_id, department, semester, role, password_hash)
VALUES ('John Doe', 'student@studenthub.com', '9876543210', 'STU1001', 'CSE', 4, 'student', '$2y$10$kL24PZloLt2CG8J38BTrXOqNftxfrVN6a0i644qu2DtfHeLr.suU2')
ON DUPLICATE KEY UPDATE role = 'student', password_hash = '$2y$10$kL24PZloLt2CG8J38BTrXOqNftxfrVN6a0i644qu2DtfHeLr.suU2';
