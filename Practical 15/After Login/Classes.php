<?php
require_once __DIR__ . "/auth_check.php";
requireAuth();
require_once __DIR__ . "/db.php";
$role = $_SESSION['role'] ?? 'student';
if ($role === 'admin') {

?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Student Hub - Student Class List</title><link rel="stylesheet" href="style.css"><style>
.admin-wrap{max-width:1200px;margin:45px auto;padding:24px;background:#fff;border-radius:18px;box-shadow:0 12px 36px #123f7212}.admin-wrap h1{color:#123f72}.admin-table{width:100%;border-collapse:collapse;margin-top:22px}.admin-table th,.admin-table td{padding:13px 12px;text-align:left;border-bottom:1px solid #e5e7eb}.admin-table th{background:#eaf2fb;color:#123f72}.admin-table tr:hover td{background:#fafcf9}.admin-nav{display:flex;gap:12px;flex-wrap:wrap;margin:20px 0}.admin-nav a{padding:10px 14px;background:#123f72;color:white;border-radius:9px;text-decoration:none}.admin-nav a.current{background:#93c5fd;color:#102a43}.muted{color:#64748b}.empty{padding:25px;text-align:center;color:#64748b}
</style></head><body><header><div class="logo"><a href="admin_dashboard.php"><img src="logo.png" alt="Student Hub"></a></div><nav class="navbar"><ul><li><a href="admin_dashboard.php">Admin Dashboard</a></li><li><a href="Classes.php">Classes</a></li><li><a href="Registration.php">Registrations</a></li><li><a href="Feedback.php">Feedback</a></li></ul></nav><div class="login"><button onclick="window.location.href='logout.php'">Logout</button></div></header><main class="admin-wrap"><h1>Student Class List</h1><p class="muted">Administrator view — student portal content is not shown in this area.</p><div class="admin-nav"><a href="admin_dashboard.php">Overview</a><a href="Classes.php">Class & Student List</a><a href="Registration.php">Event Registrations</a><a href="Feedback.php">Feedback Responses</a></div><div style="overflow-x:auto"><table class="admin-table"><thead><tr><th>Student ID</th><th>Student Name</th><th>Email</th><th>Department</th><th>Semester</th></tr></thead><tbody><?php
$stmt = $pdo->query("SELECT student_id, name, email, department, semester FROM students WHERE role='student' ORDER BY department, semester, name");
$rows = $stmt->fetchAll();
if (!$rows) { echo '<tr><td class="empty" colspan="5">No records found yet.</td></tr>'; }
foreach ($rows as $row) { echo '<tr>' . '<td>'.htmlspecialchars((string)$row['student_id']).'</td><td>'.htmlspecialchars((string)$row['name']).'</td><td>'.htmlspecialchars((string)$row['email']).'</td><td>'.htmlspecialchars((string)$row['department']).'</td><td>'.htmlspecialchars((string)$row['semester']).'</td>' . '</tr>'; }
?></tbody></table></div></main><footer><div class="footer-box"><h2>Student Hub</h2><p>Administrator portal</p></div></footer></body></html>
<?php
exit;
} 
?>
<?php requireRole('student'); ?>
<html>

<head>
    <title>Student Hub - Classes</title>
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

    <br><br>

    <section align="center">
        <h1>My Classes</h1>
        <p>View your subjects, faculty and class information.</p>
    </section>
    <br><br><br>

    <section class="class-container">
        <div class="class-card">

            <div class="class-content">
                <h2>Web Technologies</h2>
                <p>Learn HTML, CSS, JavaScript and the fundamentals of modern web development.</p>
                <p><strong>Faculty:</strong> Faculty Name</p>
                <p><strong>Room:</strong> Lab 101</p>
                <br>
                <button type="button" onclick="openModal('web-tech')">View Class</button>
            </div>
        </div>

        <div class="class-card">

            <div class="class-content">
                <h2>Data Structures</h2>
                <p>Understand arrays, linked lists, stacks, queues, trees and other data structures.</p>
                <p><strong>Faculty:</strong> Faculty Name</p>
                <p><strong>Room:</strong> Lab 204</p>
                <br>
                <button type="button" onclick="openModal('dsa')">View Class</button>
            </div>
        </div>

        <div class="class-card">

            <div class="class-content">
                <h2>Database Management</h2>
                <p>Learn database concepts, SQL, relational databases and data management.</p>
                <p><strong>Faculty:</strong> Faculty Name:</p>
                <p><strong>Room:</strong> Lab 203</p>
                <br>
                <button type="button" onclick="openModal('dbms')">View Class</button>
            </div>
        </div>

        <div class="class-card">
            
            <div class="class-content">
                <h2>Engineering Mathematics</h2>
                <p>Explore mathematical concepts and techniques used in engineering and problem solving.</p>
                <p><strong>Faculty:</strong> Faculty Name</p>
                <p><strong>Room:</strong> Lab 105</p>
                <br>
                <button type="button" onclick="openModal('maths')">View Class</button>
            </div>
        </div>

        <div class="class-card">
            
            <div class="class-content">
                <h2>Computer Networks</h2>
                <p>Learn networking concepts, protocol, communication and network architecture.</p>
                <p><strong>Faculty:</strong> Faculty Name</p>
                <p><strong>Room:</strong> Lab 301</p>
                <br>
                <button type="button" onclick="openModal('fcn')">View Class</button>
            </div>
        </div>

        <div class="class-card">
            
            <div class="class-content">
                <h2>Problem Solving</h2>
                <p>Develop programming and logical problem-solving skills using C and other programming concepts.</p>
                <p><strong>Faculty:</strong> Faculty Name</p>
                <p><strong>Room:</strong> Lab 102</p>
                <br>
                <button type="button" onclick="openModal('prob-sol')">View Class</button>
            </div>
        </div>
    </section>

<div id="web-tech" class="modal">
    <div class="modal-content">
        <h2>🎓 Web Technologies</h2>
        <p>
            Learn about important data structures and how they
            are used to solve programming problems efficiently.
        </p>
        <p>
            <strong>Faculty:</strong> Faculty Name
        </p>
        <p>
            <strong>Room:</strong> Lab 101
        </p>
        <p>
            <strong>Time:</strong> 9:00 AM - 10:00 AM
        </p>
        <h3>Topics</h3>
        <ul>
            <li>JavaScript</li>
            <li>CSS</li>
            <li>HTML</li>
            <li>Python Dataset</li>
        </ul>
        <button type="button" onclick="closeModal('web-tech')">
            Close
        </button>
    </div>
</div>

<div id="dsa" class="modal">
    <div class="modal-content">
        <h2>🎓 Data Structures</h2>
        <p>
            Learn about important data structures and how they
            are used to solve programming problems efficiently.
        </p>
        <p>
            <strong>Faculty:</strong> Faculty Name
        </p>
        <p>
            <strong>Room:</strong> Lab 204
        </p>
        <p>
            <strong>Time:</strong> 10:00 AM - 11:00 AM
        </p>
        <h3>Topics</h3>
        <ul>
            <li>Linked Lists</li>
            <li>Stacks</li>
            <li>Queues</li>
            <li>Trees</li>
        </ul>
        <button type="button" onclick="closeModal('dsa')">
            Close
        </button>
    </div>
</div>

<div id="dbms" class="modal">
    <div class="modal-content">
        <h2>🎓 Database Management</h2>
        <p>
            Learn about important data structures and how they
            are used to solve programming problems efficiently.
        </p>
        <p>
            <strong>Faculty:</strong> Faculty Name
        </p>
        <p>
            <strong>Room:</strong> Lab 203
        </p>
        <p>
            <strong>Time:</strong> 11:00 AM - 12:00 PM
        </p>
        <h3>Topics</h3>
        <ul>
            <li>Data Abstraction</li>
            <li>Data Independence</li>
            <li>Database Users</li>
        </ul>
        <button type="button" onclick="closeModal('dbms')">
            Close
        </button>
    </div>
</div>

<div id="maths" class="modal">
    <div class="modal-content">
        <h2>🎓 Engineering Mathematics</h2>
        <p>
            Learn about important data structures and how they
            are used to solve programming problems efficiently.
        </p>
        <p>
            <strong>Faculty:</strong> Faculty Name
        </p>
        <p>
            <strong>Room:</strong> Lab 105
        </p>
        <p>
            <strong>Time:</strong> 1:00 PM - 2:00 PM
        </p>
        <h3>Topics</h3>
        <ul>
            <li>Group Theory</li>
            <li>Permutation</li>
            <li>Predicates</li>
            <li>Quantifiers</li>
        </ul>
        <button type="button" onclick="closeModal('maths')">
            Close
        </button>
    </div>
</div>

<div id="fcn" class="modal">
    <div class="modal-content">
        <h2>🎓 Computer Networks</h2>
        <p>
            Learn about important data structures and how they
            are used to solve programming problems efficiently.
        </p>
        <p>
            <strong>Faculty:</strong> Faculty Name
        </p>
        <p>
            <strong>Room:</strong> Lab 301
        </p>
        <p>
            <strong>Time:</strong> 2:00 PM - 3:00 PM
        </p>
        <h3>Topics</h3>
        <ul>
            <li>OSI Model</li>
            <li>Switch vs Hub</li>
            <li>Data Link Layer</li>
            <li>Ethernet</li>
        </ul>
        <button type="button" onclick="closeModal('fcn')">
            Close
        </button>
    </div>
</div>

<div id="prob-sol" class="modal">
    <div class="modal-content">
        <h2>🎓 Problem Solving</h2>
        <p>
            Learn about important data structures and how they
            are used to solve programming problems efficiently.
        </p>
        <p>
            <strong>Faculty:</strong> Faculty Name
        </p>
        <p>
            <strong>Room:</strong> Lab 102
        </p>
        <p>
            <strong>Time:</strong> 3:00 PM - 4:00 PM
        </p>
        <h3>Topics</h3>
        <ul>
            <li>Understanding Question</li>
            <li>Critical Thinking</li>
            <li>Analysis</li>
            <li>Implementation</li>
        </ul>
        <button type="button" onclick="closeModal('prob-sol')">
            Close
        </button>
    </div>
</div>

<script src="script.js"></script>
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
</body>
</html>