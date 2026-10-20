<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
requireRole('admin');

// Practical 11: Student Management CRUD using MySQLi prepared statements.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli('localhost', 'root', '', 'studenthub_db');
$mysqli->set_charset('utf8mb4');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['crud_csrf'])) { $_SESSION['crud_csrf'] = bin2hex(random_bytes(32)); }
$csrf = $_SESSION['crud_csrf'];
$message = '';
$messageType = 'success';
$editStudent = null;

function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function validStudentInput(array $data, bool $passwordRequired): string {
    if (trim($data['name'] ?? '') === '' || trim($data['email'] ?? '') === '' || trim($data['phone'] ?? '') === '' || trim($data['student_id'] ?? '') === '' || trim($data['department'] ?? '') === '' || trim($data['semester'] ?? '') === '') return 'Please fill in all required fields.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) return 'Please enter a valid email address.';
    if (!preg_match('/^[0-9]{10}$/', $data['phone'])) return 'Phone number must contain exactly 10 digits.';
    if (!filter_var($data['semester'], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>12]])) return 'Semester must be between 1 and 12.';
    if ($passwordRequired && strlen($data['password'] ?? '') < 8) return 'Password must contain at least 8 characters.';
    return '';
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) {
            throw new RuntimeException('Security check failed. Refresh the page and try again.');
        }
        $action = $_POST['action'] ?? '';
        if ($action === 'delete') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new RuntimeException('Invalid student record.');
            $stmt = $mysqli->prepare("DELETE FROM students WHERE id = ? AND role = 'student'");
            $stmt->bind_param('i', $id); $stmt->execute();
            $message = $stmt->affected_rows ? 'Student record deleted successfully.' : 'Student was not found or cannot be deleted.';
            $stmt->close();
        } elseif ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $data = [
                'name'=>trim($_POST['name'] ?? ''), 'email'=>trim($_POST['email'] ?? ''),
                'phone'=>trim($_POST['phone'] ?? ''), 'student_id'=>trim($_POST['student_id'] ?? ''),
                'department'=>trim($_POST['department'] ?? ''), 'semester'=>trim($_POST['semester'] ?? ''),
                'password'=>$_POST['password'] ?? ''
            ];
            $validation = validStudentInput($data, $id === 0);
            if ($validation !== '') throw new RuntimeException($validation);
            if ($id > 0) {
                if ($data['password'] !== '' && strlen($data['password']) < 8) throw new RuntimeException('New password must contain at least 8 characters.');
                $check = $mysqli->prepare("SELECT id FROM students WHERE (email = ? OR student_id = ?) AND id <> ? LIMIT 1");
                $check->bind_param('ssi', $data['email'], $data['student_id'], $id); $check->execute();
                if ($check->get_result()->num_rows) throw new RuntimeException('That email or Student ID is already in use.');
                $check->close();
                if ($data['password'] !== '') {
                    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                    $stmt = $mysqli->prepare("UPDATE students SET name=?, email=?, phone=?, student_id=?, department=?, semester=?, password_hash=? WHERE id=? AND role='student'");
                    $stmt->bind_param('sssssisi', $data['name'], $data['email'], $data['phone'], $data['student_id'], $data['department'], $data['semester'], $hash, $id);
                } else {
                    $stmt = $mysqli->prepare("UPDATE students SET name=?, email=?, phone=?, student_id=?, department=?, semester=? WHERE id=? AND role='student'");
                    $stmt->bind_param('sssssii', $data['name'], $data['email'], $data['phone'], $data['student_id'], $data['department'], $data['semester'], $id);
                }
                $stmt->execute();
                $message = $stmt->affected_rows >= 0 ? 'Student record updated successfully.' : 'No student record was updated.';
                $stmt->close();
            } else {
                $check = $mysqli->prepare('SELECT id FROM students WHERE email = ? OR student_id = ? LIMIT 1');
                $check->bind_param('ss', $data['email'], $data['student_id']); $check->execute();
                if ($check->get_result()->num_rows) throw new RuntimeException('That email or Student ID already exists.');
                $check->close();
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $role = 'student';
                $stmt = $mysqli->prepare('INSERT INTO students (name,email,phone,student_id,department,semester,role,password_hash) VALUES (?,?,?,?,?,?,?,?)');
                $stmt->bind_param('sssssiss', $data['name'], $data['email'], $data['phone'], $data['student_id'], $data['department'], $data['semester'], $role, $hash);
                $stmt->execute(); $stmt->close();
                $message = 'Student added successfully.';
            }
        }
    }
} catch (mysqli_sql_exception $e) {
    $message = 'Could not save the student record. Check that the email and Student ID are unique.';
    $messageType = 'error';
} catch (Throwable $e) {
    $message = $e->getMessage();
    $messageType = 'error';
}

if (isset($_GET['edit'])) {
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($editId) {
        $stmt = $mysqli->prepare("SELECT id,name,email,phone,student_id,department,semester FROM students WHERE id=? AND role='student'");
        $stmt->bind_param('i', $editId); $stmt->execute(); $editStudent = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$editStudent) { $message = 'Student record not found.'; $messageType = 'error'; }
    }
}
$q = trim($_GET['q'] ?? '');
$department = trim($_GET['department'] ?? '');
$semester = trim($_GET['semester'] ?? '');
$sql = "SELECT id,name,email,phone,student_id,department,semester,created_at FROM students WHERE role='student'";
$params = []; $types = '';
if ($q !== '') { $sql .= ' AND (name LIKE ? OR email LIKE ? OR student_id LIKE ? OR phone LIKE ?)'; $like='%'.$q.'%'; array_push($params,$like,$like,$like,$like); $types.='ssss'; }
if ($department !== '') { $sql .= ' AND department = ?'; $params[]=$department; $types.='s'; }
if ($semester !== '' && ctype_digit($semester)) { $sql .= ' AND semester = ?'; $params[]=(int)$semester; $types.='i'; }
$sql .= ' ORDER BY id DESC';
$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute(); $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
$departments = $mysqli->query("SELECT DISTINCT department FROM students WHERE role='student' ORDER BY department")->fetch_all(MYSQLI_ASSOC);
$currentUserName = $_SESSION['student_name'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Student Management | Student Hub</title><link rel="stylesheet" href="style.css">
<style>
/* Practical 11 page-specific styling; the original blue theme stylesheet is unchanged. */
.crud-wrap{max-width:1250px;margin:35px auto;padding:24px;background:#fff;border-radius:14px;box-shadow:0 8px 28px rgba(37,99,235,.10)}.crud-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}.crud-head h1{color:#1e3a8a;margin:0}.crud-actions{display:flex;gap:10px;flex-wrap:wrap}.crud-btn{display:inline-block;background:#2563eb;color:#fff;border:0;border-radius:7px;padding:10px 15px;text-decoration:none;cursor:pointer;font-size:14px}.crud-btn:hover{background:#1d4ed8}.crud-btn.secondary{background:#475569}.crud-btn.danger{background:#dc2626}.crud-message{padding:12px 15px;border-radius:8px;margin:18px 0;background:#dcfce7;color:#166534}.crud-message.error{background:#fee2e2;color:#991b1b}.crud-form{margin:22px 0;padding:20px;background:#f8fbff;border:1px solid #dbeafe;border-radius:10px}.crud-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}.crud-form label{display:block;font-weight:600;color:#1e3a8a;margin-bottom:6px}.crud-form input,.crud-form select,.crud-filter input,.crud-filter select{box-sizing:border-box;width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:6px;background:#fff}.crud-form input:focus,.crud-filter input:focus,.crud-filter select:focus{outline:2px solid #93c5fd;border-color:#2563eb}.crud-filter{display:grid;grid-template-columns:2fr 1fr 1fr auto auto;gap:10px;margin:20px 0;align-items:end}.crud-table-wrap{overflow-x:auto}.crud-table{width:100%;border-collapse:collapse;min-width:900px}.crud-table th{background:#dbeafe;color:#1e3a8a;text-align:left}.crud-table th,.crud-table td{padding:12px;border-bottom:1px solid #e2e8f0}.crud-table tr:hover{background:#f8fafc}.crud-inline{display:inline}.crud-empty{padding:25px;text-align:center;color:#64748b}@media(max-width:700px){.crud-wrap{margin:15px;padding:15px}.crud-filter{grid-template-columns:1fr}.crud-head h1{font-size:24px}}
</style></head><body>
<div class="crud-wrap">
<div class="crud-head"><div><h1>Student Management</h1><p>Practical 11 — Add, view, edit, delete, search and filter student records.</p></div><div class="crud-actions"><a class="crud-btn secondary" href="admin_dashboard.php">Admin Dashboard</a><a class="crud-btn secondary" href="logout.php">Logout</a></div></div>
<?php if ($message !== ''): ?><div class="crud-message <?= $messageType === 'error' ? 'error' : '' ?>" role="status"><?= h($message) ?></div><?php endif; ?>
<section class="crud-form"><h2><?= $editStudent ? 'Edit Student' : 'Add New Student' ?></h2>
<form method="post" action="students.php<?= $editStudent ? '?edit='.(int)$editStudent['id'] : '' ?>" autocomplete="off"><input type="hidden" name="csrf_token" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($editStudent['id'] ?? 0) ?>">
<div class="crud-grid">
<div><label for="name">Full Name</label><input id="name" name="name" required maxlength="100" value="<?= h($editStudent['name'] ?? '') ?>"></div>
<div><label for="email">Email</label><input id="email" name="email" type="email" required maxlength="150" value="<?= h($editStudent['email'] ?? '') ?>"></div>
<div><label for="phone">Phone (10 digits)</label><input id="phone" name="phone" required pattern="[0-9]{10}" maxlength="10" value="<?= h($editStudent['phone'] ?? '') ?>"></div>
<div><label for="student_id">Student ID</label><input id="student_id" name="student_id" required maxlength="50" value="<?= h($editStudent['student_id'] ?? '') ?>"></div>
<div><label for="department">Department</label><input id="department" name="department" required maxlength="10" value="<?= h($editStudent['department'] ?? '') ?>" placeholder="e.g. CSE"></div>
<div><label for="semester">Semester</label><select id="semester" name="semester" required><option value="">Select semester</option><?php for($i=1;$i<=12;$i++): ?><option value="<?= $i ?>" <?= (string)($editStudent['semester'] ?? '')===(string)$i?'selected':'' ?>><?= $i ?></option><?php endfor; ?></select></div>
<div><label for="password"><?= $editStudent ? 'New Password (optional)' : 'Initial Password' ?></label><input id="password" name="password" type="password" <?= $editStudent ? '' : 'required' ?> minlength="8" autocomplete="new-password" placeholder="At least 8 characters"></div>
</div><div class="crud-actions" style="margin-top:16px"><button class="crud-btn" type="submit"><?= $editStudent ? 'Save Changes' : 'Add Student' ?></button><?php if($editStudent): ?><a class="crud-btn secondary" href="students.php">Cancel Edit</a><?php endif; ?></div></form></section>
<form class="crud-filter" method="get" action="students.php"><div><label for="q">Search name, email, Student ID or phone</label><input id="q" name="q" value="<?= h($q) ?>" placeholder="Enter search text"></div><div><label for="filter-department">Department</label><select id="filter-department" name="department"><option value="">All departments</option><?php foreach($departments as $d): ?><option value="<?= h($d['department']) ?>" <?= $department===$d['department']?'selected':'' ?>><?= h($d['department']) ?></option><?php endforeach; ?></select></div><div><label for="filter-semester">Semester</label><select id="filter-semester" name="semester"><option value="">All semesters</option><?php for($i=1;$i<=12;$i++): ?><option value="<?= $i ?>" <?= $semester===(string)$i?'selected':'' ?>><?= $i ?></option><?php endfor; ?></select></div><button class="crud-btn" type="submit">Search / Filter</button><a class="crud-btn secondary" href="students.php">Clear</a></form>
<p><strong><?= count($students) ?></strong> student record(s) found.</p><div class="crud-table-wrap"><table class="crud-table"><thead><tr><th>ID</th><th>Name</th><th>Student ID</th><th>Email</th><th>Phone</th><th>Department</th><th>Semester</th><th>Actions</th></tr></thead><tbody>
<?php if (!$students): ?><tr><td colspan="8" class="crud-empty">No student records match your search.</td></tr><?php else: foreach($students as $student): ?><tr><td><?= (int)$student['id'] ?></td><td><?= h($student['name']) ?></td><td><?= h($student['student_id']) ?></td><td><?= h($student['email']) ?></td><td><?= h($student['phone']) ?></td><td><?= h($student['department']) ?></td><td><?= (int)$student['semester'] ?></td><td><a class="crud-btn" href="students.php?edit=<?= (int)$student['id'] ?>">Edit</a> <form class="crud-inline" method="post" action="students.php" onsubmit="return confirm('Delete this student record? Their linked registrations and feedback may also be deleted.');"><input type="hidden" name="csrf_token" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$student['id'] ?>"><button class="crud-btn danger" type="submit">Delete</button></form></td></tr><?php endforeach; endif; ?>
</tbody></table></div></div></body></html>
