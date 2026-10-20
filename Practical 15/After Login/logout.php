<?php
// =====================================================================
// PRACTICAL 10: SECURE LOGOUT HANDLER
// Ensures complete session destruction:
// 1. Cleans up Remember-Me token in database
// 2. Invalidates Remember-Me browser cookie
// 3. Empties all $_SESSION data
// 4. Expires session cookie in client browser
// 5. Destroys server-side session via session_destroy()
// 6. Redirects to login page with logout confirmation notice
// =====================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Invalidate Remember-Me Token in DB and Cookie
if (!empty($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (!empty($parts[0])) {
        try {
            require_once __DIR__ . "/db.php";
            $delStmt = $pdo->prepare("DELETE FROM remember_tokens WHERE selector = ?");
            $delStmt->execute([$parts[0]]);
        } catch (Exception $e) {
            // Suppress error during logout
        }
    }

    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// 2. Unset all session variables
$_SESSION = [];

// 3. Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Destroy the session on server
session_destroy();

// 5. Redirect to Login page with status
header("Location: ../Before%20Login/Login.php?logout=1");
exit;
?>
