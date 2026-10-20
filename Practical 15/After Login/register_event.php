<?php
require_once __DIR__ . "/api_response.php";
requireLogin();
require_once __DIR__ . "/db.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") { http_response_code(405); respond(false, "Invalid request."); }
$title = trim($_POST["event_name"] ?? "");
if ($title === "") respond(false, "Event name is missing.");
$stmt = $pdo->prepare("SELECT id FROM events WHERE title = ? LIMIT 1");
$stmt->execute([$title]);
$event = $stmt->fetch();
if (!$event) respond(false, "This event is not available in the database. Import setup.sql again if this is a new installation.");
try {
    $stmt = $pdo->prepare("INSERT INTO registrations (student_id, event_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION["student_id"], $event["id"]]);
    respond(true, "You have registered for " . $title . " successfully.");
} catch (PDOException $e) {
    if ($e->getCode() === "23000") respond(false, "You have already registered for this event.");
    http_response_code(500); respond(false, "Unable to register. Please try again.");
}
?>
