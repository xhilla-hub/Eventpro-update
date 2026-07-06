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
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>Browse Vendors – EventPro</title>
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
 <a href="vendors.php" class="nav-item active" id="nav-vendors"><span class="icon"></span> Browse Vendors</a>
 <a href="packages.php" class="nav-item" id="nav-packages"><span class="icon"></span> Packages</a>
 <div class="nav-section-label">Account</div>
 <a href="profile.php" class="nav-item" id="nav-profile"><span class="icon"></span> My Profile</a>
 <a href="payment_history.php" class="nav-item" id="nav-payments"><span class="icon"></span> Payments</a>
 </nav>
 </aside>

 <div class="main-area">
 <header class="topbar" style="justify-content: space-between; border-bottom: none; padding-top: 20px;">
 <div class="topbar-left">
 <h2>Marketplace Vendors</h2>
 <p>Discover top-rated event professionals</p>
 </div>
 <div class="topbar-actions" style="gap: 20px;">
 <div class="user-pill" style="background:none; padding:0; gap: 12px; border:none;">
 <div class="user-avatar" style="width:40px; height:40px; font-size:1.1rem;"><?= $initials ?></div>
 <div class="user-info" style="line-height:1.2;">
 <div class="user-name" style="font-weight:700; font-size:0.9rem;"><?= htmlspecialchars($user_name) ?></div>
 </div>
 </div>
 <a href="logout.php" style="color:var(--muted); font-size:1.2rem; margin-left:8px;" title="Logout">⏻</a>
 </div>
 </header>

 <main class="page-content">
 <div class="section-card" style="text-align:center; padding: 60px 20px;">
 <div style="font-size: 4rem; margin-bottom: 20px;">🏪</div>
 <h3 style="font-family:'Barlow Condensed', sans-serif; font-size: 2rem; font-weight: 800; text-transform:uppercase; margin-bottom: 12px;">Vendor Directory Coming Soon</h3>
 <p style="color: var(--muted); font-size: 1.1rem; max-width: 500px; margin: 0 auto 30px;">We are currently onboarding top-tier catering, photography, and audio/visual vendors to the platform. Check back soon!</p>
 <a href="booking.php" class="btn-book" style="display:inline-block; text-decoration:none;">Book an Event Instead</a>
 </div>
 </main>
 </div>
</div>
</body>
</html>
