<?php
require_once __DIR__ . "/auth_check.php";
requireAuth();
require_once __DIR__ . "/db.php";
$role = $_SESSION['role'] ?? 'student';
if ($role === 'admin') {

?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Student Hub - Registered Students by Event</title><link rel="stylesheet" href="style.css"><style>
.admin-wrap{max-width:1200px;margin:45px auto;padding:24px;background:#fff;border-radius:18px;box-shadow:0 12px 36px #123f7212}.admin-wrap h1{color:#123f72}.admin-table{width:100%;border-collapse:collapse;margin-top:22px}.admin-table th,.admin-table td{padding:13px 12px;text-align:left;border-bottom:1px solid #e5e7eb}.admin-table th{background:#eaf2fb;color:#123f72}.admin-table tr:hover td{background:#fafcf9}.admin-nav{display:flex;gap:12px;flex-wrap:wrap;margin:20px 0}.admin-nav a{padding:10px 14px;background:#123f72;color:white;border-radius:9px;text-decoration:none}.admin-nav a.current{background:#93c5fd;color:#102a43}.muted{color:#64748b}.empty{padding:25px;text-align:center;color:#64748b}
</style></head><body><header><div class="logo"><a href="admin_dashboard.php"><img src="logo.png" alt="Student Hub"></a></div><nav class="navbar"><ul><li><a href="admin_dashboard.php">Admin Dashboard</a></li><li><a href="Classes.php">Classes</a></li><li><a href="Registration.php">Registrations</a></li><li><a href="Feedback.php">Feedback</a></li></ul></nav><div class="login"><button onclick="window.location.href='logout.php'">Logout</button></div></header><main class="admin-wrap"><h1>Registered Students by Event</h1><p class="muted">Administrator view — student portal content is not shown in this area.</p><div class="admin-nav"><a href="admin_dashboard.php">Overview</a><a href="Classes.php">Class & Student List</a><a href="Registration.php">Event Registrations</a><a href="Feedback.php">Feedback Responses</a></div><div style="overflow-x:auto"><table class="admin-table"><thead><tr><th>ID</th><th>Event</th><th>Student ID</th><th>Student Name</th><th>Email</th><th>Department</th><th>Semester</th><th>Registered At</th></tr></thead><tbody><?php
$stmt = $pdo->query("SELECT r.id, e.title, s.student_id, s.name, s.email, s.department, s.semester, r.registered_at FROM registrations r JOIN students s ON s.id=r.student_id JOIN events e ON e.id=r.event_id ORDER BY e.title, r.registered_at DESC");
$rows = $stmt->fetchAll();
if (!$rows) { echo '<tr><td class="empty" colspan="8">No records found yet.</td></tr>'; }
foreach ($rows as $row) { echo '<tr>' . '<td>'.htmlspecialchars((string)$row['id']).'</td><td>'.htmlspecialchars((string)$row['title']).'</td><td>'.htmlspecialchars((string)$row['student_id']).'</td><td>'.htmlspecialchars((string)$row['name']).'</td><td>'.htmlspecialchars((string)$row['email']).'</td><td>'.htmlspecialchars((string)$row['department']).'</td><td>'.htmlspecialchars((string)$row['semester']).'</td><td>'.htmlspecialchars((string)$row['registered_at']).'</td>' . '</tr>'; }
?></tbody></table></div></main><footer><div class="footer-box"><h2>Student Hub</h2><p>Administrator portal</p></div></footer></body></html>
<?php
exit;
} 
?>
<?php requireRole('student'); ?>
<html>

<head>
    <title>Student Hub</title>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
</head>

<body>
    <header>
    <div class="Logo"><img src="logo.png"></div>
    <button class="menu-button" onclick="toggleMenu()">☰</button>
    <nav class="navbar" id="navbar">
        <ul>
            <li><a href = "Homepage.php">Home</a></li>
            <li><a href = "Classes.php">Classes</a></li>
            <li><a href = "Registration.php">Registration</a></li>
            <li><a href = "Information.html">Information</a></li>
            <li><a href = "Feedback.php">Feedback</a></li>
            <li><a href = "About.html">About</a></li>
        </ul>
    </nav>
    <div>
    <button class="theme-button" onclick="toggleTheme()">🌙</button></div>
    <div class="login">
        <h2><button onclick="window.location.href='logout.php'">Logout</button></h2>
    </div>
</header>

<section class="Registration">
    <h1 class="page-title">Registration Portal</h1>

    <p class="page-subtitle">Register as a Student or for upcoming events</p>
    <div class="about-container">
        <div class="about-card">
            <h2>Technical Event Registration</h2>

            <p>Explore your technical potential by participating in hackathons, coding competitions, workshops, and other technology-focused events that help you build skills, solve real-world problems, and connect with fellow innovators.</p>
            <p>
                ✓ Hackathons<br>
                ✓ Coding Competitions<br>
                ✓ Technical Quiz<br>
                ✓ Hands on Workshop<br>
                ✓ Ideathons<br>
            </p>
            <img src="Images/tech.png" class="event-photo">
            <div class="login">
            <button onclick="window.location.href='Technical.html'">Register Here</button>
            </div>
        </div>

        <div class="about-card">
            <h2>Non-Technical Event Registration</h2>

            <p>Get involved in campus life by participating in cultural, sports, creative, and social events that help you showcase your talents, build connections, and enjoy a vibrant student experience.</p>
            <p>
                ✓ Cultural Fest<br>
                ✓ Sports Event<br>
                ✓ Photography Competition<br>
                ✓ Club Activities<br>
                ✓ Extra Curricular Activities<br>
            </p>
            <img src="Images/nontech.png" class="event-photo">
            <div class="login">
            <button onclick="window.location.href='Non-Technical.html'">Register Here</button>
            </div>
        </div>
    </div>
</section>

<footer>
    <div class="footer-box">
        <h2>Student Hub</h2>
        <p>Your one-stop platform for academic excellence and campus life.</p>
    </div>

    <div class="footer-box">
        <h3>Quick Links</h3>
        <ul>
            <li><a href="Homepage.php">Home</a></li>
            <li><a href="Classes.php">Classes</a></li>
            <li><a href="Registration.php">Registration</a></li>
            <li><a href="Feedback.php">Feedback</a></li>
        </ul>
    </div>

    <div class="footer-box">
        <h3>Contact</h3>
        <p>Email: studenthub@gmail.com</p>
        <p>Phone: +91 9876543210</p>
    </div>

    <div class="footer-box">
        <h3>Follow Us</h3>
        <p>Instagram</p>
        <p>LinkedIn</p>
        <p>YouTube</p>
    </div>

</footer>
<p class="copyright">&copy;2026: StudentHub Portal</p>
<script src="script.js"></script>
</body>
</html>