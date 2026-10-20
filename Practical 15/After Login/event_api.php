<?php
// Practical 13: JSON API for event CRUD, consumed with Fetch API.
require_once __DIR__ . '/auth_check.php';
requireRole('admin');
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function sendJson(int $status, array $data): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function readPayload(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}
function validateEvent(array $data): array {
    $title = trim((string)($data['title'] ?? ''));
    $category = trim((string)($data['category'] ?? ''));
    $date = trim((string)($data['event_date'] ?? ''));
    if ($title === '' || mb_strlen($title) > 150) return [null, 'Title is required and must be at most 150 characters.'];
    if (!in_array($category, ['Technical', 'Non-Technical'], true)) return [null, 'Choose a valid event category.'];
    $d = DateTime::createFromFormat('!Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) return [null, 'Enter a valid event date.'];
    return [['title'=>$title, 'category'=>$category, 'event_date'=>$date], null];
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    try {
        $stmt = $pdo->query('SELECT id, title, category, event_date, created_at FROM events ORDER BY event_date ASC, id DESC');
        sendJson(200, ['success'=>true, 'events'=>$stmt->fetchAll()]);
    } catch (Throwable $e) { sendJson(500, ['success'=>false, 'message'=>'Could not load events. Check the events table.']); }
}
if (!in_array($method, ['POST','PUT','DELETE'], true)) {
    header('Allow: GET, POST, PUT, DELETE');
    sendJson(405, ['success'=>false, 'message'=>'Method not allowed.']);
}
if (empty($_SESSION['csrf_api_token'])) $_SESSION['csrf_api_token'] = bin2hex(random_bytes(32));
$provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_api_token'], $provided)) sendJson(403, ['success'=>false, 'message'=>'Security token expired. Refresh the page and try again.']);
$data = readPayload();
try {
    if ($method === 'POST') {
        [$event, $error] = validateEvent($data);
        if ($error) sendJson(422, ['success'=>false, 'message'=>$error]);
        $stmt = $pdo->prepare('INSERT INTO events (title, category, event_date) VALUES (?, ?, ?)');
        $stmt->execute([$event['title'], $event['category'], $event['event_date']]);
        sendJson(201, ['success'=>true, 'message'=>'Event added successfully.', 'id'=>(int)$pdo->lastInsertId()]);
    }
    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) sendJson(422, ['success'=>false, 'message'=>'A valid event ID is required.']);
    if ($method === 'PUT') {
        [$event, $error] = validateEvent($data);
        if ($error) sendJson(422, ['success'=>false, 'message'=>$error]);
        $stmt = $pdo->prepare('UPDATE events SET title=?, category=?, event_date=? WHERE id=?');
        $stmt->execute([$event['title'], $event['category'], $event['event_date'], $id]);
        if ($stmt->rowCount() === 0) {
            $check = $pdo->prepare('SELECT id FROM events WHERE id=?'); $check->execute([$id]);
            if (!$check->fetch()) sendJson(404, ['success'=>false, 'message'=>'Event not found.']);
        }
        sendJson(200, ['success'=>true, 'message'=>'Event updated successfully.']);
    }
    if ($method === 'DELETE') {
        $stmt = $pdo->prepare('DELETE FROM events WHERE id=?'); $stmt->execute([$id]);
        if (!$stmt->rowCount()) sendJson(404, ['success'=>false, 'message'=>'Event not found.']);
        sendJson(200, ['success'=>true, 'message'=>'Event deleted successfully.']);
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') sendJson(409, ['success'=>false, 'message'=>'An event with this title already exists, or the event is linked to existing registrations.']);
    sendJson(500, ['success'=>false, 'message'=>'Database operation failed. Please check the event table and try again.']);
} catch (Throwable $e) { sendJson(500, ['success'=>false, 'message'=>'Unexpected server error.']); }
