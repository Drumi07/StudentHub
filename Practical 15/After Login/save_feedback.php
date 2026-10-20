<?php
require_once __DIR__ . "/api_response.php";
requireLogin();
require_once __DIR__ . "/db.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") { http_response_code(405); respond(false, "Please submit the feedback form."); }
$subject = trim($_POST["subject"] ?? "");
$feedback = trim($_POST["feedback"] ?? "");
$rating = filter_var($_POST["rating"] ?? "", FILTER_VALIDATE_INT);
if ($subject === "") respond(false, "Subject is required.");
if ($feedback === "") respond(false, "Please enter your feedback.");
if ($rating === false || $rating < 1 || $rating > 5) respond(false, "Please select a valid rating.");
try {
    $stmt = $pdo->prepare("INSERT INTO feedback (student_id, subject, feedback, rating) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION["student_id"], $subject, $feedback, $rating]);
    respond(true, "Feedback submitted successfully!");
} catch (PDOException $e) {
    http_response_code(500); respond(false, "Unable to save feedback. Please try again.");
}
?>
