<?php
require_once __DIR__ . "/api_response.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    respond(false, "Invalid request.");
}

$name = trim($_POST["name"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));
$phone = trim($_POST["phone"] ?? "");
$studentid = trim($_POST["studentid"] ?? "");
$department = trim($_POST["department"] ?? "");
$semester = (int)($_POST["semester"] ?? 0);
$password = $_POST["password"] ?? "";
$confirm = $_POST["confirm"] ?? "";

// Backend validation (frontend validation is also present in SignIn.html).
if ($name === "" || !preg_match("/^[A-Za-z ]+$/", $name)) respond(false, "Enter a valid name using letters and spaces.");
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(false, "Enter a valid email address.");
if (!preg_match("/^[0-9]{10}$/", $phone)) respond(false, "Phone number must contain exactly 10 digits.");
if ($studentid === "" || strlen($studentid) > 50) respond(false, "Enter a valid Student ID.");
if (!in_array($department, ["CSE", "IT", "CE"], true)) respond(false, "Please select a valid department.");
if ($semester < 1 || $semester > 8) respond(false, "Please select a valid semester.");
if (!preg_match("/^(?=.*[A-Za-z])(?=.*[0-9]).{8,}$/", $password)) respond(false, "Password must have at least 8 characters, one letter and one number.");
if ($password !== $confirm) respond(false, "Passwords do not match.");
if (($_POST["terms"] ?? "") !== "yes") respond(false, "Please accept the Terms and Conditions.");

$connection = new mysqli("localhost", "root", "", "studenthub_db");
if ($connection->connect_error) {
    http_response_code(500);
    respond(false, "Database connection failed. Check that MySQL is running and studenthub_db exists.");
}
$connection->set_charset("utf8mb4");

try {
    // Email and Student ID are unique in setup.sql; this prevents duplicates safely.
    $check = $connection->prepare("SELECT id FROM students WHERE email = ? OR student_id = ? LIMIT 1");
    $check->bind_param("ss", $email, $studentid);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        $connection->close();
        respond(false, "This email or Student ID is already registered.");
    }
    $check->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insert = $connection->prepare("INSERT INTO students (name, email, phone, student_id, department, semester, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("sssssis", $name, $email, $phone, $studentid, $department, $semester, $hashedPassword);
    $insert->execute();
    $insert->close();
    $connection->close();
    respond(true, "Account created successfully! You can now log in.");
} catch (mysqli_sql_exception $e) {
    $connection->close();
    if ((int)$e->getCode() === 1062) respond(false, "This email or Student ID is already registered.");
    http_response_code(500);
    respond(false, "Unable to create account. Please try again.");
}
?>
