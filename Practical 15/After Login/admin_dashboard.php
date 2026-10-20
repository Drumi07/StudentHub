<?php
// =====================================================================
// PRACTICAL 10: PROTECTED ADMINISTRATOR DASHBOARD
// Access is restricted to role === 'admin'.
// Unauthorized students are redirected to Homepage.php with access denied.
// =====================================================================

require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/db.php";

// Enforce Administrator role
requireRole('admin');

$currentUser = getCurrentUser();
$adminName = htmlspecialchars($currentUser['name'] ?? 'Administrator');
$adminEmail = htmlspecialchars($currentUser['email'] ?? '');
$adminCode = htmlspecialchars($currentUser['student_id'] ?? 'ADMIN');

$actionMessage = "";

// 1. Fetch Summary Statistics
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students WHERE role = 'student'")->fetchColumn();
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM students WHERE role = 'admin'")->fetchColumn();
$totalRegistrations = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();

writeAuditLog($pdo, 'Viewed admin dashboard', 'Opened Practical 14 dashboard');
$auditStmt = $pdo->query("SELECT user_name, user_role, action, details, ip_address, created_at FROM audit_logs ORDER BY created_at DESC LIMIT 20");
$auditLogs = $auditStmt->fetchAll();
$totalFeedback = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();

// 2. Fetch All Registered Users
$usersStmt = $pdo->query("SELECT id, name, email, phone, student_id, department, semester, role, created_at FROM students ORDER BY id DESC");
$allUsers = $usersStmt->fetchAll();

// 3. Fetch All Event Registrations
$regStmt = $pdo->query("SELECT r.id, s.name as student_name, s.student_id as stu_code, e.title as event_title, e.category, r.registered_at 
                        FROM registrations r 
                        JOIN students s ON s.id = r.student_id 
                        JOIN events e ON e.id = r.event_id 
                        ORDER BY r.registered_at DESC LIMIT 20");
$allRegistrations = $regStmt->fetchAll();

// 4. Fetch All Feedback Submissions
$fbStmt = $pdo->query("SELECT f.id, s.name as student_name, s.student_id as stu_code, f.subject, f.feedback, f.rating, f.submitted_at 
                       FROM feedback f 
                       JOIN students s ON s.id = f.student_id 
                       ORDER BY f.submitted_at DESC LIMIT 20");
$allFeedback = $fbStmt->fetchAll();

$remainingSeconds = max(0, SESSION_TIMEOUT - (time() - ($_SESSION['last_activity'] ?? time())));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Hub - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-banner {
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            color: #ffffff;
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 14px;
        }
        .admin-badge {
            background-color: #f59e0b;
            color: #1e1b4b;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .admin-timer {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 5px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
        }
        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }
        .logout-btn:hover {
            background: #dc2626;
        }
        .student-portal-link {
            background: #ffffff;
            color: #312e81;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            transition: 0.2s;
        }
        .student-portal-link:hover {
            background: #e0e7ff;
        }
        .admin-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border-left: 5px solid #3b82f6;
        }
        .stat-card h4 {
            margin: 0 0 6px 0;
            color: #64748b;
            font-size: 13px;
            text-transform: uppercase;
        }
        .stat-card .num {
            font-size: 28px;
            font-weight: bold;
            color: #1e293b;
        }
        .section-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            margin-bottom: 35px;
        }
        .section-card h2 {
            margin-top: 0;
            color: #1e3a8a;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
            font-size: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }
        .data-table th {
            background: #f1f5f9;
            color: #334155;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
        }
        .data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
        }
        .data-table tr:hover {
            background: #f8fafc;
        }
        .badge-role {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-admin {
            background: #fef3c7;
            color: #b45309;
        }
        .badge-student {
            background: #d1fae5;
            color: #065f46;
        }
        .btn-toggle {
            padding: 3px 8px;
            font-size: 11px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-toggle:hover {
            background: #e2e8f0;
        }
        .password-protected { display:inline-block; padding:4px 8px; border-radius:999px; background:#e6f1e7; color:#315b3c; font-size:12px; font-weight:700; }
        .msg-box {
            background: #d1fae5;
            color: #065f46;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #10b981;
        }
    </style>
</head>
<body>

    <!-- Administrator Session Header -->
    <div class="admin-banner">
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <span>🛡️ <strong>Admin Portal:</strong> <?= $adminName ?> (<?= $adminEmail ?>)</span>
            <span class="admin-badge">Administrator</span>
            <a href="admin_dashboard.php" class="student-portal-link">🎓 View Student Portal</a>
            <a href="students.php" class="student-portal-link">Student Management</a>
            <a href="EventManagement.php" class="student-portal-link">Event Management</a>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <div class="admin-timer">
                ⏱️ Timeout: <span id="adminTimer">--:--</span>
            </div>
            <button class="logout-btn" onclick="confirmAdminLogout()">Logout</button>
        </div>
    </div>

    <header>
        <div class="logo"><img src="logo.png" alt="Student Hub"></div>
        <button class="menu-button" onclick="toggleMenu()">☰</button>
        <nav class="navbar" id="navbar">
            <ul>
                <li><a href="admin_dashboard.php" style="color: #2563EB; font-weight: bold;">Admin Dashboard</a></li>
                <li><a href="admin_dashboard.php">Student Portal</a></li>
                <li><a href="Classes.php">Classes</a></li>
                <li><a href="Registration.php">Registration</a></li>
                <li><a href="Feedback.php">Feedback</a></li>
                <li><a href="EventManagement.php">Event Management</a></li>
            </ul>
        </nav>
        <div>
            <button class="theme-button" onclick="toggleTheme()">🌙</button>
        </div>
        <div class="login">
            <h2><button onclick="confirmAdminLogout()">Logout</button></h2>
        </div>
    </header>

    <div class="admin-container">

        <h1 style="color: #1e293b; margin-bottom: 8px;">Admin Dashboard</h1>
        <p style="color: #64748b; margin-top: 0; margin-bottom: 25px;">Practical 14: User statistics, latest registrations, role-based access, and audit log.</p>

        <!-- Statistics Counter Cards -->
        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #2563eb;">
                <h4>Total Students</h4>
                <div class="num"><?= $totalStudents ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <h4>Administrators</h4>
                <div class="num"><?= $totalAdmins ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #0ea5e9;">
                <h4>Total Events</h4>
                <div class="num"><?= $totalEvents ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #10b981;">
                <h4>Event Registrations</h4>
                <div class="num"><?= $totalRegistrations ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #8b5cf6;">
                <h4>Feedback Reviews</h4>
                <div class="num"><?= $totalFeedback ?></div>
            </div>
        </div>

        <!-- Registered Students & Roles Table -->
        <div class="section-card">
            <h2>
                <span>👥 Registered Users & Roles</span>
                <span style="font-size:13px; font-weight:normal; color:#64748b;">(Managed via RBAC)</span>
            </h2>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Full Name</th>
                            <th>Student ID</th>
                            <th>Email</th>
                            <th>Dept / Sem</th>
                            <th>Current Role</th>
                            <th>Password Security</th>
                            <th>Registered On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allUsers)): ?>
                            <tr><td colspan="8">No registered users found in database.</td></tr>
                        <?php else: ?>
                            <?php foreach ($allUsers as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($u['student_id']) ?></code></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= htmlspecialchars($u['department']) ?> / Sem <?= $u['semester'] ?></td>
                                    <td>
                                        <span class="badge-role <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-student' ?>">
                                            <?= ucfirst($u['role']) ?>
                                        </span>
                                    </td>
                                    <td><span class="password-protected">Protected · hashed</span></td>
                                    <td><?= date('d M Y, h:i A', strtotime($u['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Event Registrations -->
        <div class="section-card">
            <h2>📅 Recent Event Registrations</h2>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Reg #</th>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Event Title</th>
                            <th>Category</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allRegistrations)): ?>
                            <tr><td colspan="6">No student registrations recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($allRegistrations as $r): ?>
                                <tr>
                                    <td>#<?= $r['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($r['student_name']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($r['stu_code']) ?></code></td>
                                    <td><?= htmlspecialchars($r['event_title']) ?></td>
                                    <td><span style="text-transform: capitalize;"><?= htmlspecialchars($r['category']) ?></span></td>
                                    <td><?= date('d M Y, h:i A', strtotime($r['registered_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Student Feedback Submissions -->
        <div class="section-card">
            <h2>💬 Student Feedback & Ratings</h2>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Feedback #</th>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Rating</th>
                            <th>Feedback Message</th>
                            <th>Submitted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allFeedback)): ?>
                            <tr><td colspan="6">No feedback received yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($allFeedback as $f): ?>
                                <tr>
                                    <td>#<?= $f['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($f['student_name']) ?></strong> (<code><?= htmlspecialchars($f['stu_code']) ?></code>)</td>
                                    <td><?= htmlspecialchars($f['subject']) ?></td>
                                    <td>
                                        <span style="color:#f59e0b; font-weight:bold;">
                                            <?= str_repeat("★", (int)$f['rating']) . str_repeat("☆", 5 - (int)$f['rating']) ?>
                                        </span>
                                        (<?= $f['rating'] ?>/5)
                                    </td>
                                    <td><?= nl2br(htmlspecialchars($f['feedback'])) ?></td>
                                    <td><?= date('d M Y, h:i A', strtotime($f['submitted_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="script.js"></script>

    <script>
    // Session Timeout Countdown Tracker (15 minutes inactivity limit)
    var adminRemaining = <?= $remainingSeconds ?>;
    var adminTimerDisplay = document.getElementById("adminTimer");

    function updateAdminCountdown() {
        if (adminRemaining <= 0) {
            adminTimerDisplay.innerHTML = "00:00 (Expired)";
            window.location.href = "../Before%20Login/Login.php?timeout=1";
            return;
        }

        var m = Math.floor(adminRemaining / 60);
        var s = adminRemaining % 60;
        adminTimerDisplay.innerHTML = 
            (m < 10 ? "0" : "") + m + ":" + 
            (s < 10 ? "0" : "") + s;

        adminRemaining--;
    }

    setInterval(updateAdminCountdown, 1000);
    updateAdminCountdown();

    function confirmAdminLogout() {
        if (confirm("Logout from Administrator Session?")) {
            window.location.href = "logout.php";
        }
    }
    </script>

    <div class="admin-container">
        <div class="section-card">
            <h2><span>Recent Audit Log</span><span style="font-size:13px;font-weight:normal;color:#64748b;">Latest 20 actions</span></h2>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead><tr><th>Date & Time</th><th>User</th><th>Role</th><th>Action</th><th>Details</th><th>IP Address</th></tr></thead>
                    <tbody>
                    <?php if (empty($auditLogs)): ?>
                        <tr><td colspan="6">No audit activity recorded yet.</td></tr>
                    <?php else: foreach ($auditLogs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$log['created_at']) ?></td>
                            <td><?= htmlspecialchars((string)$log['user_name']) ?></td>
                            <td><?= htmlspecialchars((string)$log['user_role']) ?></td>
                            <td><?= htmlspecialchars((string)$log['action']) ?></td>
                            <td><?= htmlspecialchars((string)($log['details'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string)($log['ip_address'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-box">
            <h2>Student Hub</h2>
            <p>Your one-stop platform for academic excellence and campus life.</p>
        </div>
        <div class="footer-box">
            <h3>Administrative Access</h3>
            <p>Restricted to authorized system administrators only.</p>
        </div>
        <div class="footer-box">
            <h3>Contact Support</h3>
            <p>Email: admin@studenthub.com</p>
        </div>
    </footer>
    <p class="copyright">&copy;2026: StudentHub Portal - Practical 10 Admin Control Panel</p>
</body>
</html>