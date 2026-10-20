<?php
// =====================================================================
// PRACTICAL 10: PROTECTED STUDENT DASHBOARD
// Access is restricted: only authenticated users can view this page.
// Unauthorized visitors are redirected to Login.php immediately.
// =====================================================================

require_once __DIR__ . "/auth_check.php";
requireRole('student'); // Restrict dashboard access

$currentUser = getCurrentUser();
$studentName = htmlspecialchars($currentUser['name'] ?? 'Student');
$studentEmail = htmlspecialchars($currentUser['email'] ?? '');
$studentRole = htmlspecialchars($currentUser['role'] ?? 'student');
$studentCode = htmlspecialchars($currentUser['student_id'] ?? '');
$department = htmlspecialchars($currentUser['department'] ?? 'CSE');
$semester = htmlspecialchars($currentUser['semester'] ?? '1');

$remainingSeconds = max(0, SESSION_TIMEOUT - (time() - ($_SESSION['last_activity'] ?? time())));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Hub - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .session-banner {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: #ffffff;
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .session-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .role-badge {
            background-color: #10b981;
            color: #ffffff;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .role-admin {
            background-color: #f59e0b;
        }
        .session-timer {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 5px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .admin-link-btn {
            background: #ffffff;
            color: #1e3a8a;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            transition: 0.2s;
        }
        .admin-link-btn:hover {
            background: #f1f5f9;
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
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            padding: 25px 50px;
        }
        .stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border-top: 4px solid #2563eb;
        }
        .stat-card h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #64748b;
        }
        .stat-card p {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
        }
        .alert-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            font-weight: 500;
            display: none;
        }
    </style>
</head>
<body>

    <!-- Session User Information Bar -->
    <div class="session-banner">
        <div class="session-info">
            <span>👤 Logged in as: <strong><?= $studentName ?></strong> (<?= $studentEmail ?>)</span>
            <span>ID: <strong><?= $studentCode ?></strong></span>
            <span class="role-badge <?= $studentRole === 'admin' ? 'role-admin' : '' ?>">
                <?= ucfirst($studentRole) ?>
            </span>
            <?php if ($studentRole === 'admin'): ?>
                <a href="admin_dashboard.php" class="admin-link-btn">🛡️ Switch to Admin Dashboard</a>
            <?php endif; ?>
        </div>
        <div class="session-info">
            <div class="session-timer" title="Session automatically expires after 15 minutes of inactivity">
                <span>⏱️ Inactivity Timeout:</span>
                <span id="countdownDisplay">--:--</span>
            </div>
            <button class="logout-btn" onclick="confirmLogout()">Logout</button>
        </div>
    </div>

    <div id="toastNotification" class="alert-toast">
        ✓ Login Successful! Welcome back, <?= $studentName ?>.
    </div>

    <header>
        <div class="logo"><img src="logo.png" alt="Student Hub"></div>
        <button class="menu-button" onclick="toggleMenu()">☰</button>
        <nav class="navbar" id="navbar">
            <ul>
                <li><a href="Homepage.php" style="color: #2563EB; font-weight: bold;">Home</a></li>
                <li><a href="Classes.php">Classes</a></li>
                <li><a href="Registration.php">Registration</a></li>
                <li><a href="Information.html">Information</a></li>
                <li><a href="Feedback.php">Feedback</a></li>
                <li><a href="About.html">About</a></li>
                <?php if ($studentRole === 'admin'): ?>
                    <li><a href="admin_dashboard.php" style="color:#f59e0b; font-weight: bold;">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div>
            <button class="theme-button" onclick="toggleTheme()">🌙</button>
        </div>
        <div class="login">
            <h2><button onclick="confirmLogout()">Logout</button></h2>
        </div>
    </header>

    <!-- Student Quick Info Cards -->
    <div class="quick-stats">
        <div class="stat-card">
            <h3>STUDENT PROFILE</h3>
            <p><?= $studentName ?></p>
            <span style="font-size:12px; color:#64748b;">ID: <?= $studentCode ?></span>
        </div>
        <div class="stat-card" style="border-top-color: #059669;">
            <h3>DEPARTMENT & SEMESTER</h3>
            <p><?= $department ?> - Sem <?= $semester ?></p>
            <span style="font-size:12px; color:#64748b;">Academic Year 2026</span>
        </div>
        <div class="stat-card" style="border-top-color: #7c3aed;">
            <h3>SESSION STATUS</h3>
            <p style="color: #059669;">● Active & Secured</p>
            <span style="font-size:12px; color:#64748b;">Regenerated Session ID Active</span>
        </div>
        <div class="stat-card" style="border-top-color: #f59e0b;">
            <h3>QUICK ACTIONS</h3>
            <p style="font-size:14px; margin-top:4px;">
                <a href="Registration.php" style="color:#2563eb; text-decoration:none;">Register Event &rarr;</a> | 
                <a href="Feedback.php" style="color:#2563eb; text-decoration:none;">Feedback &rarr;</a>
            </p>
        </div>
    </div>

    <section class="announcements">
        <span>Announcements :</span>
        <div class="announcement-slider">
            <div id="announcement-text"></div>
        </div>
    </section>

    <section class="hero">
        <h2>Welcome Back, <?= $studentName ?></h2>
        <p>Your academic journey starts here. Monitor your performance, explore upcoming events, connect with student communities, and access essential campus resources—all through a personalized dashboard designed to keep you informed, organized, and ready to succeed.</p>
        <div class="slider">
            <img src="Images/Slider-1.jpg" id="slide" alt="Slide">
        </div>

        <div class="dots">
            <span onclick="changeSlide(0)">●</span>
            <span onclick="changeSlide(1)">●</span>
            <span onclick="changeSlide(2)">●</span>
        </div>
    </section>

    <section>
        <table class="table">
            <tr>
                <th colspan="2">About Us</th>
            </tr>
            <tr>
                <td>🎓 Academic Excellence</td>
                <td>🏫 Vibrant Campus Life</td>
            </tr>
            <tr>
                <td>💡 Innovation and Research</td>
                <td>🤝 Industry Connections</td>
            </tr>
            <tr>
                <td>💼 Excellent Placement Support</td>
                <td>🌍 Global Exposure</td>
            </tr>
            <tr>
                <td>👨‍💻 Modern Learning Environment</td>
                <td>📚 Extensive Library Resources</td>
            </tr>
            <tr>
                <td>🎭 Student Club and Communities</td>
                <td>🌱 Green and Sustainable Campus</td>
            </tr>
        </table>
    </section>

    <section class="event-section">
        <div class="calender">
            <h2>Calendar</h2>
            <div class="calendar-box">
                <div class="calendar-header">
                    <button onclick="previousMonth()">❮</button>
                    <h3 id="month-year"></h3>
                    <button onclick="nextMonth()">❯</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Sun</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                        </tr>
                    </thead>
                    <tbody id="calendar-body"></tbody>
                </table>
            </div>
        </div>

        <div class="events">
            <table>
                <tr>
                    <th>Upcoming Events</th>
                </tr>
                <tr>
                    <td>18 July - बोधसृजन 1.0 – National Symposium on Innovative Pedagogy and Assessment Practices</td>
                </tr>
                <tr>
                    <td>23 July - Shree Pramukh Swami Maharaj Endowment Chair - PDPIAS</td>
                </tr>
                <tr>
                    <td>30 July - Cultural Fest</td>
                </tr>
            </table>
        </div>
    </section>

    <script src="script.js"></script>

    <script>
    // Show login toast if redirected from login
    if (window.location.search.includes("login=success")) {
        var toast = document.getElementById("toastNotification");
        if (toast) {
            toast.style.display = "block";
            setTimeout(function() { toast.style.display = "none"; }, 4000);
        }
    }

    // Session Timeout Countdown Tracker (15 minutes inactivity limit)
    var remainingSeconds = <?= $remainingSeconds ?>;
    var timerDisplay = document.getElementById("countdownDisplay");

    function updateCountdown() {
        if (remainingSeconds <= 0) {
            timerDisplay.innerHTML = "00:00 (Expired)";
            timerDisplay.style.color = "#f87171";
            window.location.href = "../Before%20Login/Login.php?timeout=1";
            return;
        }

        var minutes = Math.floor(remainingSeconds / 60);
        var seconds = remainingSeconds % 60;
        timerDisplay.innerHTML = 
            (minutes < 10 ? "0" : "") + minutes + ":" + 
            (seconds < 10 ? "0" : "") + seconds;

        if (remainingSeconds <= 60) {
            timerDisplay.style.color = "#f87171";
            timerDisplay.style.fontWeight = "bold";
        }

        remainingSeconds--;
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();

    function confirmLogout() {
        if (confirm("Are you sure you want to log out of Student Hub?")) {
            window.location.href = "logout.php";
        }
    }
    </script>

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

    <p class="copyright">&copy;2026: StudentHub Portal - Practical 10 Protected Session</p>
</body>
</html>

