<?php
session_start();
if (isset($_SESSION['user_id'])) {
 header("Location: dashboard.php");
 exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 include '../config/database.php';

 $fullname = trim($_POST['fullname']);
 $email = trim($_POST['email']);
 $phone = trim($_POST['phone']);
 $password = $_POST['password'];
 $confirm = $_POST['confirm_password'];

 if (empty($fullname) || empty($email) || empty($phone) || empty($password)) {
 $error = "All fields are required.";
 } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
 $error = "Please enter a valid email address.";
 } elseif (strlen($password) < 6) {
 $error = "Password must be at least 6 characters.";
 } elseif ($password !== $confirm) {
 $error = "Passwords do not match.";
 } else {
 $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
 $stmt->execute(['email' => $email]);
 if ($stmt->rowCount() > 0) {
 $error = "An account with this email already exists.";
 } else {
 $hashed = password_hash($password, PASSWORD_DEFAULT);
 $stmt = $pdo->prepare("INSERT INTO users (fullname, email, phone, password) VALUES (:fullname, :email, :phone, :password)");
 if ($stmt->execute(['fullname' => $fullname, 'email' => $email, 'phone' => $phone, 'password' => $hashed])) {
 $user_id = $pdo->lastInsertId();
 $_SESSION['user_id'] = $user_id;
 $_SESSION['user_name'] = $fullname;
 $_SESSION['user_email']= $email;
 $_SESSION['user_phone']= $phone;
 $_SESSION['user_role'] = 'user';
 header("Location: dashboard.php");
 exit();
 } else {
 $error = "Registration failed. Please try again.";
 }
 }
 }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>Create Account – EventPro</title>
 <meta name="description" content="Create your EventPro account to start planning and booking vendors for your events."/>
 <link rel="stylesheet" href="../../css/auth.css"/>
</head>
<body>
 <div class="auth-wrapper">

 <!-- ── LEFT: FORM PANEL ── -->
 <div class="auth-panel">
 <div class="panel-logo">
 <div class="logo-box">EP</div>
 <span class="logo-name">EventPro</span>
 </div>

 <div class="panel-body">
 <h2>Create an account</h2>
 <p class="panel-subtitle">Sign up and get access to your first event free</p>

 <?php if ($error): ?>
 <div class="alert alert-error"> <?= htmlspecialchars($error) ?></div>
 <?php endif; ?>

 <form method="POST" action="" id="register-form" autocomplete="off">
 <div class="form-group">
 <label for="fullname">Full name</label>
 <input type="text" id="fullname" name="fullname" placeholder="Jane Mwangi"
 value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required/>
 </div>

 <div class="form-group">
 <label for="email">Email</label>
 <input type="email" id="email" name="email" placeholder="you@example.com"
 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
 </div>

 <div class="form-group">
 <label for="phone">Phone number</label>
 <input type="tel" id="phone" name="phone" placeholder="0712 345 678"
 value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required/>
 </div>

 <div class="form-group">
 <label for="password">Password</label>
 <input type="password" id="password" name="password" placeholder="Min. 6 characters" required/>
 </div>

 <div class="form-group">
 <label for="confirm_password">Confirm password</label>
 <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required/>
 </div>

 <button type="submit" class="btn-submit" id="register-btn">Create Account →</button>
 </form>

 <div class="divider">OR</div>

 <div class="social-btns">
 <button class="btn-social" type="button">
 <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.7 9.05 7.47c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 3.92zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z" fill="currentColor"/>
 </svg>
 Apple
 </button>
 <button class="btn-social" type="button">
 <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
 <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
 <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
 <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
 <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
 </svg>
 Google
 </button>
 </div>

 <div class="auth-footer">
 Have an account? <a href="login.php">Sign in</a>
 &nbsp;&nbsp;|&nbsp;&nbsp;
 <a href="../../index.php">← Home</a>
 </div>
 </div>
 </div>

 <!-- ── RIGHT: VISUAL PANEL ── -->
 <div class="auth-visual">
 <!-- Background: pic 15 with dark overlay -->
 <div style="position:absolute;inset:0;background:url('../../images/15.png') center center / cover no-repeat;"></div>
 <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(15,23,42,0.82) 0%,rgba(30,41,59,0.75) 60%,rgba(15,23,42,0.9) 100%);"></div>

 <!-- Gold geometric decoration -->
 <div style="position:absolute;top:-80px;right:-80px;width:360px;height:360px;background:rgba(250,204,21,0.08);border-radius:50%;"></div>
 <div style="position:absolute;bottom:-60px;left:-60px;width:280px;height:280px;background:rgba(250,204,21,0.06);border-radius:50%;"></div>

 <!-- Floating top card -->
 <div class="float-card card-top">
 <div style="display:flex;align-items:center;gap:10px;">
 <div style="background:#FEF9C3;border-radius:8px;padding:8px;font-size:1.1rem;"></div>
 <div>
 <div class="fc-title">Wedding Ceremony <span class="fc-dot"></span></div>
 <div class="fc-sub">Confirmed · 350 guests</div>
 </div>
 </div>
 </div>

 <!-- Bottom card: payment received -->
 <div class="float-card card-bot" style="animation-delay:1.5s;">
 <div style="display:flex;align-items:center;gap:10px;">
 <div style="background:#D1FAE5;border-radius:8px;padding:8px;font-size:1.1rem;"></div>
 <div>
 <div class="fc-title">Payment Received</div>
 <div class="fc-sub">TZS 120,000 · M-Pesa · Just now</div>
 </div>
 </div>
 </div>

 <!-- Stats badges -->
 <div class="stats-badge">
 <div class="sbadge">
 <div class="sbadge-val">1.2k+</div>
 <div class="sbadge-lbl">Vendors</div>
 </div>
 <div class="sbadge">
 <div class="sbadge-val">98%</div>
 <div class="sbadge-lbl">Satisfied</div>
 </div>
 <div class="sbadge">
 <div class="sbadge-val">5k+</div>
 <div class="sbadge-lbl">Events</div>
 </div>
 </div>
 </div>

 </div>

 <script>
 const pwd = document.getElementById('password');
 const conf = document.getElementById('confirm_password');
 conf.addEventListener('input', () => {
 conf.style.borderColor = pwd.value && conf.value !== pwd.value ? '#EF4444' : '';
 conf.style.boxShadow = pwd.value && conf.value !== pwd.value ? '0 0 0 3px rgba(239,68,68,0.15)' : '';
 });
 </script>
</body>
</html>