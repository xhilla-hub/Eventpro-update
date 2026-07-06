<?php
session_start();
if (!isset($_SESSION['user_id'])) {
 header("Location: login.php");
 exit();
}
include '../config/database.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$initials = strtoupper(substr($user_name, 0, 1));

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    if (empty($fullname) || empty($phone)) {
        $error = "Name and phone are required.";
    } else {
        $upd = $pdo->prepare("UPDATE users SET fullname = :fn, phone = :ph WHERE id = :id");
        if ($upd->execute(['fn' => $fullname, 'ph' => $phone, 'id' => $user_id])) {
            $_SESSION['user_name'] = $fullname;
            $_SESSION['user_phone'] = $phone;
            $success = "Profile updated successfully.";
            $user['fullname'] = $fullname;
            $user['phone'] = $phone;
            $user_name = $fullname;
        } else {
            $error = "Failed to update profile.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>My Profile – EventPro</title>
 <link rel="stylesheet" href="../../css/dashboard.css"/>
</head>
<body>
<div class="app-layout">
 <aside class="sidebar" id="sidebar">
 <div class="sidebar-logo">
 <div class="logo-box">EP</div>
 <span class="logo-name">EventPro</span>
 </div>
 <nav class="sidebar-nav">
 <div class="nav-section-label">Main</div>
 <a href="dashboard.php" class="nav-item" id="nav-dashboard"><span class="icon"></span> Dashboard</a>
 <a href="booking.php" class="nav-item" id="nav-booking"><span class="icon"></span> Book an Event</a>
 <a href="my_bookings.php" class="nav-item" id="nav-mybookings"><span class="icon"></span> My Booking</a>
 <div class="nav-section-label">Marketplace</div>
 <a href="vendors.php" class="nav-item" id="nav-vendors"><span class="icon"></span> Browse Vendors</a>
 <a href="packages.php" class="nav-item" id="nav-packages"><span class="icon"></span> Packages</a>
 <div class="nav-section-label">Account</div>
 <a href="profile.php" class="nav-item active" id="nav-profile"><span class="icon"></span> My Profile</a>
 <a href="payment_history.php" class="nav-item" id="nav-payments"><span class="icon"></span> Payments</a>
 </nav>
 </aside>

 <div class="main-area">
 <header class="topbar" style="justify-content: space-between; border-bottom: none; padding-top: 20px;">
 <div class="topbar-left">
 <h2>My Profile</h2>
 <p>Manage your account details</p>
 </div>
 <div class="topbar-actions" style="gap: 20px;">
 <div class="user-pill" style="background:none; padding:0; gap: 12px; border:none;">
 <div class="user-avatar" style="width:40px; height:40px; font-size:1.1rem;"><?= $initials ?></div>
 <div class="user-info" style="line-height:1.2;">
 <div class="user-name" style="font-weight:700; font-size:0.9rem;"><?= htmlspecialchars($user_name) ?></div>
 <div class="user-role" style="font-size:0.75rem; color:var(--muted);"><?= htmlspecialchars($user['email']) ?></div>
 </div>
 </div>
 <a href="logout.php" style="color:var(--muted); font-size:1.2rem; margin-left:8px;" title="Logout">⏻</a>
 </div>
 </header>

 <main class="page-content">
 <?php if ($success): ?><div class="alert-box success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
 <?php if ($error): ?><div class="alert-box error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
 
 <div class="section-card" style="max-width: 600px;">
 <form method="POST" action="">
 <div class="form-field">
 <label>Email Address (Cannot be changed)</label>
 <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly style="background: #f1f5f9;" />
 </div>
 <div class="form-field">
 <label>Full Name</label>
 <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required />
 </div>
 <div class="form-field">
 <label>Phone Number</label>
 <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required />
 </div>
 <button type="submit" class="btn-book" style="margin-top: 10px;">Update Profile</button>
 </form>
 </div>
 </main>
 </div>
</div>
</body>
</html>
