<?php
require_once __DIR__ . '/auth_check.php';
requireRole('admin');
require_once __DIR__ . '/db.php';

if (empty($_SESSION['csrf_event_token'])) {
    $_SESSION['csrf_event_token'] = bin2hex(random_bytes(32));
}
$message = '';
$messageType = 'success';
$uploadDir = __DIR__ . '/uploads/event_posters/';
$uploadUrl = 'uploads/event_posters/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    $message = 'Could not create the poster upload folder. Check folder permissions.';
    $messageType = 'error';
}

function eventEscape($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function removePoster(?string $name, string $dir): void {
    if ($name && basename($name) === $name) {
        $path = $dir . $name;
        if (is_file($path)) { @unlink($path); }
    }
}
function savePoster(array $file, string $dir): array {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($file['error'] !== UPLOAD_ERR_OK) return [null, 'The poster upload failed. Please try again.'];
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) return [null, 'Poster must be 2 MB or smaller.'];
    if (!is_uploaded_file($file['tmp_name'])) return [null, 'Invalid uploaded file.'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) return [null, 'Only JPG, PNG, and WEBP poster images are allowed.'];
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) return [null, 'The selected file is not a valid image.'];
    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) return [null, 'Could not save the poster. Check folder permissions.'];
    return [$filename, null];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_event_token'], $_POST['csrf_token'] ?? '')) {
        $message = 'Security check failed. Refresh the page and try again.'; $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'delete') {
            $id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
            if (!$id) { $message = 'Invalid event selected.'; $messageType = 'error'; }
            else {
                try {
                    $stmt = $pdo->prepare('SELECT poster FROM events WHERE id = ?'); $stmt->execute([$id]); $existing = $stmt->fetch();
                    if (!$existing) { $message = 'Event not found.'; $messageType = 'error'; }
                    else {
                        $del = $pdo->prepare('DELETE FROM events WHERE id = ?'); $del->execute([$id]);
                        removePoster($existing['poster'] ?? null, $uploadDir);
                        $message = 'Event deleted successfully.';
                    }
                } catch (PDOException $e) { $message = 'Could not delete this event. It may be linked to existing records.'; $messageType = 'error'; }
            }
        } elseif ($action === 'save') {
            $id = filter_var($_POST['event_id'] ?? '', FILTER_VALIDATE_INT);
            $title = trim($_POST['title'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $eventDate = trim($_POST['event_date'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $errors = [];
            if ($title === '' || mb_strlen($title) > 150) $errors[] = 'Enter an event title (maximum 150 characters).';
            if (!in_array($category, ['technical', 'non-technical'], true)) $errors[] = 'Choose a valid event category.';
            $date = DateTime::createFromFormat('Y-m-d', $eventDate);
            if (!$date || $date->format('Y-m-d') !== $eventDate) $errors[] = 'Enter a valid event date.';
            if (mb_strlen($description) > 2000) $errors[] = 'Description must be 2000 characters or fewer.';
            $current = null;
            if ($id) { $q = $pdo->prepare('SELECT id, poster FROM events WHERE id = ?'); $q->execute([$id]); $current = $q->fetch(); if (!$current) $errors[] = 'The event you are editing no longer exists.'; }
            [$newPoster, $uploadError] = savePoster($_FILES['poster'] ?? [], $uploadDir);
            if ($uploadError) $errors[] = $uploadError;
            if ($errors) {
                if ($newPoster) removePoster($newPoster, $uploadDir);
                $message = implode(' ', $errors); $messageType = 'error';
            } else {
                try {
                    if ($id) {
                        $poster = $newPoster ?? ($current['poster'] ?? null);
                        $stmt = $pdo->prepare('UPDATE events SET title = ?, category = ?, event_date = ?, description = ?, poster = ? WHERE id = ?');
                        $stmt->execute([$title, $category, $eventDate, $description, $poster, $id]);
                        if ($newPoster && !empty($current['poster'])) removePoster($current['poster'], $uploadDir);
                        $message = 'Event updated successfully.';
                    } else {
                        $stmt = $pdo->prepare('INSERT INTO events (title, category, event_date, description, poster) VALUES (?, ?, ?, ?, ?)');
                        $stmt->execute([$title, $category, $eventDate, $description, $newPoster,]);
                        $message = 'Event added successfully.';
                    }
                } catch (PDOException $e) {
                    if ($newPoster) removePoster($newPoster, $uploadDir);
                    $message = ($e->getCode() === '23000') ? 'An event with this title already exists.' : 'Could not save the event. Please check the database setup.';
                    $messageType = 'error';
                }
            }
        }
    }
}

$editEvent = null;
if (isset($_GET['edit']) && ctype_digit((string)$_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT id, title, category, event_date, description, poster FROM events WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]); $editEvent = $stmt->fetch() ?: null;
    if (!$editEvent) { $message = 'Event not found.'; $messageType = 'error'; }
}
$search = trim($_GET['search'] ?? '');
$filter = $_GET['category'] ?? '';
$sql = 'SELECT id, title, category, event_date, description, poster, created_at FROM events WHERE 1=1';
$params = [];
if ($search !== '') { $sql .= ' AND (title LIKE ? OR description LIKE ?)'; $params[] = '%' . $search . '%'; $params[] = '%' . $search . '%'; }
if (in_array($filter, ['technical', 'non-technical'], true)) { $sql .= ' AND category = ?'; $params[] = $filter; }
$sql .= ' ORDER BY event_date DESC, id DESC';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $events = $stmt->fetchAll();
$form = $editEvent ?: ['id'=>'','title'=>'','category'=>'technical','event_date'=>'','description'=>'','poster'=>null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Event Management | Student Hub</title><link rel="stylesheet" href="style.css">
<style>
.event-admin{max-width:1200px;margin:35px auto;padding:0 20px}.event-panel{background:#fff;padding:24px;margin-bottom:25px;border-radius:12px;box-shadow:0 4px 16px rgba(18,63,114,.09)}.event-panel h1,.event-panel h2{color:#123f72;margin-top:0}.event-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.event-form label{display:block;font-weight:600;color:#183b60;margin-bottom:6px}.event-form input,.event-form select,.event-form textarea,.event-search input,.event-search select{box-sizing:border-box;width:100%;padding:11px;border:1px solid #cbd5e1;border-radius:7px;font:inherit}.event-form textarea{min-height:95px;resize:vertical}.event-full{grid-column:1/-1}.event-btn{border:0;border-radius:7px;background:#123f72;color:white;padding:10px 16px;cursor:pointer;text-decoration:none;display:inline-block;font:inherit}.event-btn.secondary{background:#64748b}.event-btn.danger{background:#b91c1c}.event-message{padding:12px 15px;border-radius:8px;margin:15px 0}.event-message.success{background:#dcfce7;color:#166534}.event-message.error{background:#fee2e2;color:#991b1b}.event-search{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px}.event-search input{flex:1;min-width:180px}.event-table-wrap{overflow-x:auto}.event-table{width:100%;border-collapse:collapse}.event-table th,.event-table td{padding:11px;border:1px solid #dbe4ee;text-align:left;vertical-align:middle}.event-table th{background:#eaf2fb;color:#123f72}.event-poster{width:85px;height:60px;object-fit:cover;border-radius:5px}.event-actions{display:flex;gap:7px;flex-wrap:wrap}.event-inline{display:inline}.event-hint{color:#64748b;font-size:13px}.event-current-poster{max-width:160px;max-height:110px;display:block;margin-top:8px}@media(max-width:650px){.event-form{grid-template-columns:1fr}.event-full{grid-column:auto}.event-panel{padding:16px}}
</style></head><body>
<header><div class="logo"><a href="admin_dashboard.php"><img src="logo.png" alt="Student Hub"></a></div><nav class="navbar"><ul><li><a href="admin_dashboard.php">Admin Dashboard</a></li><li><a href="students.php">Student Management</a></li><li><a href="Classes.php">Classes</a></li><li><a href="Registration.php">Registrations</a></li><li><a href="Feedback.php">Feedback</a></li><li><a href="EventManagement.php">Events</a></li></ul></nav><div class="login"><button onclick="window.location.href='logout.php'">Logout</button></div></header>
<main class="event-admin"><section class="event-panel"><h1>Event Management</h1><p>Administrator only — add, view, edit, and delete events and upload posters.</p>
<?php if ($message !== ''): ?><div class="event-message <?= $messageType === 'error' ? 'error' : 'success' ?>" role="status"><?= eventEscape($message) ?></div><?php endif; ?>
<h2><?= $editEvent ? 'Edit Event' : 'Add New Event' ?></h2>
<form method="post" enctype="multipart/form-data" class="event-form"><input type="hidden" name="csrf_token" value="<?= eventEscape($_SESSION['csrf_event_token']) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="event_id" value="<?= eventEscape($form['id']) ?>">
<div><label for="title">Event Title *</label><input id="title" name="title" maxlength="150" required value="<?= eventEscape($form['title']) ?>"></div>
<div><label for="category">Category *</label><select id="category" name="category" required><option value="technical" <?= $form['category']==='technical'?'selected':'' ?>>Technical</option><option value="non-technical" <?= $form['category']==='non-technical'?'selected':'' ?>>Non-Technical</option></select></div>
<div><label for="event_date">Event Date *</label><input type="date" id="event_date" name="event_date" required value="<?= eventEscape($form['event_date']) ?>"></div>
<div><label for="poster">Event Poster</label><input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><div class="event-hint">Allowed: JPG, PNG, WEBP. Maximum size: 2 MB.</div><?php if (!empty($form['poster'])): ?><img class="event-current-poster" src="<?= eventEscape($uploadUrl . basename($form['poster'])) ?>" alt="Current poster"><?php endif; ?></div>
<div class="event-full"><label for="description">Description</label><textarea id="description" name="description" maxlength="2000"><?= eventEscape($form['description'] ?? '') ?></textarea></div>
<div class="event-full"><button class="event-btn" type="submit"><?= $editEvent ? 'Update Event' : 'Add Event' ?></button> <?php if ($editEvent): ?><a class="event-btn secondary" href="EventManagement.php">Cancel Edit</a><?php endif; ?></div>
</form></section>
<section class="event-panel"><h2>Event List</h2><form method="get" class="event-search"><input name="search" placeholder="Search event title or description" value="<?= eventEscape($search) ?>"><select name="category"><option value="">All categories</option><option value="technical" <?= $filter==='technical'?'selected':'' ?>>Technical</option><option value="non-technical" <?= $filter==='non-technical'?'selected':'' ?>>Non-Technical</option></select><button class="event-btn" type="submit">Search / Filter</button><a class="event-btn secondary" href="EventManagement.php">Clear</a></form>
<div class="event-table-wrap"><table class="event-table"><thead><tr><th>ID</th><th>Poster</th><th>Title</th><th>Category</th><th>Date</th><th>Description</th><th>Actions</th></tr></thead><tbody>
<?php if (!$events): ?><tr><td colspan="7">No events found.</td></tr><?php else: foreach ($events as $event): ?><tr><td><?= (int)$event['id'] ?></td><td><?php if (!empty($event['poster'])): ?><img class="event-poster" src="<?= eventEscape($uploadUrl . basename($event['poster'])) ?>" alt="Event poster"><?php else: ?>No poster<?php endif; ?></td><td><?= eventEscape($event['title']) ?></td><td><?= eventEscape(ucwords(str_replace('-', ' ', $event['category']))) ?></td><td><?= eventEscape($event['event_date']) ?></td><td><?= eventEscape($event['description'] ?? '') ?></td><td><div class="event-actions"><a class="event-btn" href="EventManagement.php?edit=<?= (int)$event['id'] ?>">Edit</a><form class="event-inline" method="post" onsubmit="return confirm('Delete this event? Existing registrations linked to it may also be removed.');"><input type="hidden" name="csrf_token" value="<?= eventEscape($_SESSION['csrf_event_token']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>"><button class="event-btn danger" type="submit">Delete</button></form></div></td></tr><?php endforeach; endif; ?>
</tbody></table></div></section></main><footer><div class="footer-box"><h2>Student Hub</h2><p>Administrator portal</p></div></footer></body></html>
