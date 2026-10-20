<?php
require_once __DIR__ . "/api_response.php";
requireLogin();
require_once __DIR__ . "/db.php";
$stmt = $pdo->prepare("SELECT e.title, DATE_FORMAT(e.event_date, '%d/%m/%Y') AS event_date, r.registered_at FROM registrations r JOIN events e ON e.id = r.event_id WHERE r.student_id = ? ORDER BY r.registered_at DESC");
$stmt->execute([$_SESSION["student_id"]]);
respond(true, "Registrations loaded.", ["registrations" => $stmt->fetchAll()]);
?>
