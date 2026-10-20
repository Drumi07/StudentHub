<?php
require_once __DIR__ . "/../After Login/auth_check.php";

// If already logged in, redirect straight to the appropriate dashboard
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
    if (($currentUser['role'] ?? 'student') === 'admin') {
        header("Location: ../After%20Login/admin_dashboard.php");
    } else {
        header("Location: ../After%20Login/Homepage.php");
    }
    exit;
}

// Notice messages based on query parameters
$timeout = isset($_GET['timeout']);
$logout = isset($_GET['logout']);
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Hub - Secure Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .alert-box {
            max-width: 450px;
            margin: 20px auto 0 auto;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.5;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-warning {
            background-color: #fef3c7;
            color: #92400e;
            border-left: 5px solid #f59e0b;
        }
        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 5px solid #ef4444;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 5px solid #10b981;
        }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 12px 0 18px 0;
            font-size: 14px;
            color: #4b5563;
        }
        .remember-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            margin: 0;
        }
        .remember-row label {
            cursor: pointer;
            user-select: none;
        }
        .credentials-card {
            max-width: 450px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px dashed #93c5fd;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 13px;
            color: #1e3a8a;
        }
        .credentials-card h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #1e40af;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .cred-btn {
            background: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
            padding: 3px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 4px;
            transition: 0.2s;
        }
        .cred-btn:hover {
            background: #c7d2fe;
        }
    </style>
</head>

<body>

<header>
    <div class="logo">
        <a href="Homepage.html"><img src="logo.png" alt="Student Hub"></a>
    </div>
    <nav class="navbar" id="navbar">
        <ul>
            <li><a href="Homepage.html">Home</a></li>
            <li><a href="Information.html">Information</a></li>
            <li><a href="About.html">About</a></li>
        </ul>
    </nav>
</header>

<br>

<h1 align="center">Welcome Back</h1>
<p align="center">Secure Login to Student Hub</p>

<!-- Session Notices -->
<?php if ($timeout): ?>
    <div class="alert-box alert-warning">
        <span>⏰ <strong>Session Expired:</strong> You were logged out due to 15 minutes of inactivity. Please log in again.</span>
    </div>
<?php elseif ($logout): ?>
    <div class="alert-box alert-success">
        <span>✓ <strong>Logged Out:</strong> You have been securely logged out. Session destroyed.</span>
    </div>
<?php elseif ($error === 'unauthorized'): ?>
    <div class="alert-box alert-danger">
        <span>🔒 <strong>Access Restricted:</strong> Please log in to access the student dashboard or portal pages.</span>
    </div>
<?php elseif ($error === 'forbidden'): ?>
    <div class="alert-box alert-danger">
        <span>⛔ <strong>Access Denied:</strong> Administrator privileges are required to access that area.</span>
    </div>
<?php endif; ?>

<form class="LoginPage" id="loginForm" onsubmit="event.preventDefault(); login();">

    <label for="loginid"><strong>Email or Student ID:</strong></label><br>
    <input type="text" id="loginid" name="loginid" placeholder="e.g. student@studenthub.com or STU1001" required>
    <span id="loginidError" style="color:#dc2626; font-size:14px;"></span>

    <br><br>

    <label for="user-password"><strong>Password:</strong></label><br>
    <input type="password" id="user-password" name="password" placeholder="Enter your password" required>
    <span id="passwordError" style="color:#dc2626; font-size:14px;"></span>

    <!-- Practical 10: Secure Remember-Me Token Checkbox -->
    <div class="remember-row">
        <input type="checkbox" id="remember_me" name="remember_me">
        <label for="remember_me">Remember Me on this device (30 days secure token)</label>
    </div>

    <button type="button" id="loginButton" onclick="login()">Login</button>

    <p id="successMessage" style="color:#16a34a; font-weight:bold; text-align:center; margin-top:12px;"></p>

    <p align="center" style="font-size:15px; margin-top:20px;">
        Don't have an account?
        <a href="SignIn.html" style="color:#2563eb; font-weight:bold; text-decoration:none;">Create Account</a>
    </p>

</form>

<footer>
    <div class="footer-box">
        <h2>Student Hub</h2>
        <p>Your one-stop platform for academic excellence and campus life.</p>
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

<p class="copyright">@2026: StudentHub Portal - Practical 10 Session Security</p>

<script>
function login() {
    var loginid = document.getElementById("loginid").value.trim();
    var password = document.getElementById("user-password").value;
    var rememberMe = document.getElementById("remember_me").checked;
    var btn = document.getElementById("loginButton");

    document.getElementById("loginidError").innerHTML = "";
    document.getElementById("passwordError").innerHTML = "";
    document.getElementById("successMessage").innerHTML = "";

    if (loginid === "") {
        document.getElementById("loginidError").innerHTML = "Please enter your email or Student ID.";
        return;
    }
    if (password === "") {
        document.getElementById("passwordError").innerHTML = "Please enter your password.";
        return;
    }

    btn.disabled = true;
    btn.innerText = "Authenticating...";

    var formData = new FormData();
    formData.append("loginid", loginid);
    formData.append("password", password);
    if (rememberMe) {
        formData.append("remember_me", "1");
    }

    fetch("../After%20Login/login_user.php", {
        method: "POST",
        body: formData
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        btn.disabled = false;
        btn.innerText = "Login";

        if (data.success) {
            document.getElementById("successMessage").innerHTML = "✓ " + data.message;
            setTimeout(function() {
                window.location.href = data.redirect;
            }, 600);
        } else {
            document.getElementById("passwordError").innerHTML = data.message;
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerText = "Login";
        document.getElementById("passwordError").innerHTML = "Unable to connect. Start Apache and MySQL in XAMPP Control Panel.";
    });
}
</script>

</body>
</html>

