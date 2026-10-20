<?php
$host = "localhost";
$dbname = "studenthub_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Auto-migration helper: ensure Practical 10 database columns & tables exist
    ensurePractical10Schema($pdo);

} catch (PDOException $e) {
    http_response_code(500);
    die("Database connection failed. Please check that MySQL is running and setup.sql has been imported into phpMyAdmin.");
}

function ensurePractical10Schema(PDO $pdo): void {
    try {
        // Check if students table exists
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'students'");
        if ($tableCheck->rowCount() === 0) {
            return;
        }

        // 1. Check and add role column if missing
        $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'role'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE students ADD COLUMN role ENUM('student', 'admin') NOT NULL DEFAULT 'student' AFTER semester");
        }

        // Practical 14: audit log for important user and admin actions
        $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
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
        ) ENGINE=InnoDB");

        // 2. Check and create remember_tokens table if missing
        $pdo->exec("CREATE TABLE IF NOT EXISTS remember_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            selector VARCHAR(32) NOT NULL UNIQUE,
            token_hash VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_remember_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");

        // 3. Ensure Default Admin user exists
        $adminStmt = $pdo->prepare("SELECT id FROM students WHERE email = ? OR student_id = ? LIMIT 1");
        $adminStmt->execute(['admin@studenthub.com', 'ADMIN001']);
        if (!$adminStmt->fetch()) {
            $ins = $pdo->prepare("INSERT INTO students (name, email, phone, student_id, department, semester, role, password_hash)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([
                'System Administrator',
                'admin@studenthub.com',
                '9999999999',
                'ADMIN001',
                'CSE',
                8,
                'admin',
                '$2y$10$OLyRcz1p7sp4s36D75GBieCn7CVGf1SJSwrAKfqlMxF6RF4TpjOfi'
            ]);
        }

        // 4. Ensure Default Student test user exists
        $stuStmt = $pdo->prepare("SELECT id FROM students WHERE email = ? OR student_id = ? LIMIT 1");
        $stuStmt->execute(['student@studenthub.com', 'STU1001']);
        if (!$stuStmt->fetch()) {
            $ins = $pdo->prepare("INSERT INTO students (name, email, phone, student_id, department, semester, role, password_hash)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([
                'John Doe',
                'student@studenthub.com',
                '9876543210',
                'STU1001',
                'CSE',
                4,
                'student',
                '$2y$10$kL24PZloLt2CG8J38BTrXOqNftxfrVN6a0i644qu2DtfHeLr.suU2'
            ]);
        }
    } catch (Exception $e) {
        // Suppress during fresh install before setup.sql runs
    }
}

function writeAuditLog(PDO $pdo, string $action, string $details = ""): void {
    try {
        $userId = isset($_SESSION["student_id"]) ? (int)$_SESSION["student_id"] : null;
        $userName = (string)($_SESSION["student_name"] ?? "Guest");
        $userRole = (string)($_SESSION["role"] ?? "guest");
        $ipAddress = (string)($_SERVER["REMOTE_ADDR"] ?? "");
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, user_name, user_role, action, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $userName, $userRole, $action, $details, $ipAddress]);
    } catch (Throwable $e) {
        // Audit logging must not interrupt the user's main action.
    }
}
?>
