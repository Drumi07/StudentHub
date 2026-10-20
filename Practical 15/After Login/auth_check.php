<?php
// =====================================================================
// PRACTICAL 10: CORE AUTHENTICATION GUARD & SESSION MANAGEMENT
// Handles:
// 1. Secure session initialization (HttpOnly, SameSite)
// 2. Session timeout verification (Inactivity auto-logout after 15 min)
// 3. Persistent login via secure remember-me token
// 4. Role-based access control (Student vs Admin)
// =====================================================================

// Configure session cookie security flags before starting
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie expires when browser closes
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Session timeout duration: 15 minutes (900 seconds)
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 900);
}

// Function to detect whether current request is an API / AJAX call
function isApiRequest(): bool {
    if (isset($_GET['api']) || isset($_POST['api'])) return true;
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') return true;
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return str_contains($accept, 'application/json');
}

// ---------------------------------------------------------------------
// 1. SESSION TIMEOUT CHECK
// ---------------------------------------------------------------------
if (!empty($_SESSION['student_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        // Inactivity exceeded SESSION_TIMEOUT; destroy session
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();

        if (isApiRequest()) {
            http_response_code(401);
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["success" => false, "message" => "Session timed out due to inactivity. Please log in again.", "timeout" => true]);
            exit;
        }

        header("Location: ../Before%20Login/Login.php?timeout=1");
        exit;
    }
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
}

// ---------------------------------------------------------------------
// 2. SECURE REMEMBER-ME TOKEN RESTORATION (SPLIT TOKEN VERIFICATION)
// ---------------------------------------------------------------------
if (empty($_SESSION['student_id']) && !empty($_COOKIE['remember_me'])) {
    try {
        require_once __DIR__ . "/db.php";
        $parts = explode(':', $_COOKIE['remember_me'], 2);
        if (count($parts) === 2) {
            $selector = $parts[0];
            $validator = $parts[1];

            $tokenStmt = $pdo->prepare("SELECT t.*, s.name, s.email, s.student_id as stu_code, s.role, s.department, s.semester 
                                        FROM remember_tokens t 
                                        JOIN students s ON s.id = t.student_id 
                                        WHERE t.selector = ? AND t.expires_at > NOW() 
                                        LIMIT 1");
            $tokenStmt->execute([$selector]);
            $tokenRow = $tokenStmt->fetch();

            if ($tokenRow && hash_equals($tokenRow['token_hash'], hash('sha256', $validator))) {
                // Token is valid! Regenerate session ID to prevent fixation
                session_regenerate_id(true);

                // Populate session data
                $_SESSION['student_id'] = (int)$tokenRow['student_id'];
                $_SESSION['student_name'] = $tokenRow['name'];
                $_SESSION['student_email'] = $tokenRow['email'];
                $_SESSION['student_code'] = $tokenRow['stu_code'];
                $_SESSION['role'] = $tokenRow['role'] ?? 'student';
                $_SESSION['department'] = $tokenRow['department'] ?? '';
                $_SESSION['semester'] = $tokenRow['semester'] ?? 1;
                $_SESSION['last_activity'] = time();
            }
        }
    } catch (Exception $e) {
        // Fail silently and prompt user to login
    }
}

// ---------------------------------------------------------------------
// 3. AUTHENTICATION & ACCESS CONTROL HELPER FUNCTIONS
// ---------------------------------------------------------------------

/**
 * Returns true if the user is authenticated.
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['student_id']);
}

/**
 * Returns array of currently authenticated user info, or null.
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['student_id'],
        'name' => $_SESSION['student_name'] ?? '',
        'email' => $_SESSION['student_email'] ?? '',
        'student_id' => $_SESSION['student_code'] ?? '',
        'role' => $_SESSION['role'] ?? 'student',
        'department' => $_SESSION['department'] ?? '',
        'semester' => $_SESSION['semester'] ?? 1,
        'last_activity' => $_SESSION['last_activity'] ?? time()
    ];
}

/**
 * Enforces that user is logged in. Redirects to login if not.
 */
function requireAuth(): void {
    if (!isLoggedIn()) {
        if (isApiRequest()) {
            http_response_code(401);
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["success" => false, "message" => "Please log in to continue."]);
            exit;
        }
        header("Location: ../Before%20Login/Login.php?error=unauthorized");
        exit;
    }
}

/**
 * Enforces that user is logged in AND has the specified role.
 */
function requireRole(string $requiredRole): void {
    requireAuth();
    $currentRole = $_SESSION['role'] ?? 'student';
    if ($currentRole !== $requiredRole) {
        if (isApiRequest()) {
            http_response_code(403);
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["success" => false, "message" => "Access denied. Administrator privileges required."]);
            exit;
        }

        // Redirect with forbidden error message
        if ($currentRole === 'student') {
            header("Location: Homepage.php?error=forbidden");
        } else {
            header("Location: admin_dashboard.php?error=forbidden");
        }
        exit;
    }
}

// ---------------------------------------------------------------------
// 4. API ENDPOINT RESPONSE (WHEN ACCESSED VIA FETCH / AJAX DIRECTLY)
// ---------------------------------------------------------------------
if (isset($_GET['api']) || (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'auth_check.php' && $_SERVER['REQUEST_METHOD'] === 'GET')) {
    header("Content-Type: application/json; charset=utf-8");
    if (isLoggedIn()) {
        $remaining = max(0, SESSION_TIMEOUT - (time() - ($_SESSION['last_activity'] ?? time())));
        echo json_encode([
            "authenticated" => true,
            "user" => getCurrentUser(),
            "timeout_seconds" => SESSION_TIMEOUT,
            "remaining_seconds" => $remaining
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "authenticated" => false,
            "message" => "User is not logged in."
        ]);
    }
    exit;
}
?>

