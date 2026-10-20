<?php
// =====================================================================
// PRACTICAL 10: SECURE LOGIN CONTROLLER
// Features:
// 1. Password verification using password_verify()
// 2. Session ID regeneration using session_regenerate_id(true)
// 3. Role-based redirection (Student -> Homepage.php | Admin -> admin_dashboard.php)
// 4. Secure Remember-Me Token using cryptographic selector & hashed validator
// =====================================================================

require_once __DIR__ . "/api_response.php";
require_once __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    respond(false, "Invalid request method.");
}

$loginid = trim($_POST["loginid"] ?? "");
$password = $_POST["password"] ?? "";
$rememberMe = !empty($_POST["remember_me"]) && ($_POST["remember_me"] === "1" || $_POST["remember_me"] === "on" || $_POST["remember_me"] === "true");

if ($loginid === "" || $password === "") {
    respond(false, "Please enter your Email or Student ID and Password.");
}

// 1. Fetch user by email or student ID using prepared statement
$stmt = $pdo->prepare("SELECT id, name, email, student_id, department, semester, role, password_hash 
                       FROM students 
                       WHERE LOWER(email) = ? OR student_id = ? 
                       LIMIT 1");
$stmt->execute([strtolower($loginid), $loginid]);
$student = $stmt->fetch();

// 2. Verify password with password_verify() against stored hash
if (!$student || !password_verify($password, $student["password_hash"])) {
    respond(false, "Invalid login credentials. Please check your details and try again.");
}

// 3. Prevent Session Fixation: Regenerate session ID upon successful authentication
session_regenerate_id(true);

// 4. Store user information in session
$_SESSION["student_id"] = (int)$student["id"];
$_SESSION["student_name"] = $student["name"];
$_SESSION["student_email"] = $student["email"];
$_SESSION["student_code"] = $student["student_id"];
$_SESSION["department"] = $student["department"];
$_SESSION["semester"] = $student["semester"];
$_SESSION["role"] = $student["role"] ?? "student";
$_SESSION["last_activity"] = time();
writeAuditLog($pdo, 'Successful login', 'User signed in successfully');

// 5. Optional Secure Remember-Me Token Implementation
if ($rememberMe) {
    try {
        // Generate cryptographically secure selector (16 bytes = 32 hex chars) and validator (32 bytes = 64 hex chars)
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $tokenHash = hash("sha256", $validator);
        $expires = date("Y-m-d H:i:s", time() + (30 * 24 * 3600)); // 30 days validity

        // Store hashed token in database
        $tokenInsert = $pdo->prepare("INSERT INTO remember_tokens (student_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)");
        $tokenInsert->execute([$student["id"], $selector, $tokenHash, $expires]);

        // Send split-token cookie to browser (selector:validator) with httpOnly and samesite flags
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie(
            "remember_me",
            $selector . ":" . $validator,
            [
                "expires" => time() + (30 * 24 * 3600),
                "path" => "/",
                "domain" => "",
                "secure" => $isSecure,
                "httponly" => true,
                "samesite" => "Lax"
            ]
        );
    } catch (Exception $e) {
        // If remember token table fails, continue login without breaking user experience
    }
}

// 6. Role-Based Redirection
$userRole = strtolower($student["role"] ?? "student");
if ($userRole === "admin") {
    $redirectUrl = "../After%20Login/admin_dashboard.php";
    $roleName = "Administrator";
} else {
    $redirectUrl = "../After%20Login/Homepage.php?login=success";
    $roleName = "Student";
}

respond(true, "Login successful! Redirecting to " . $roleName . " Portal...", [
    "redirect" => $redirectUrl,
    "role" => $userRole,
    "user" => [
        "name" => $student["name"],
        "email" => $student["email"],
        "role" => $userRole
    ]
]);
?>
