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

// Fetch packages
$stmt = $pdo->query("SELECT * FROM packages ORDER BY price ASC");
$packages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>Packages – EventPro</title>
 <link rel="stylesheet" href="../../css/dashboard.css"/>
 <style>
 .packages-grid {
 display: grid;
 grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
 gap: 24px;
 margin-top: 24px;
 }
 .pkg-card {
 background: #fff;
 border: 1px solid var(--border);
 border-radius: 12px;
 padding: 24px;
 display: flex;
 flex-direction: column;
 transition: transform 0.2s, box-shadow 0.2s;
 }
 .pkg-card:hover {
 transform: translateY(-4px);
 box-shadow: 0 12px 24px rgba(0,0,0,0.05);
 }
 .pkg-header {
 display: flex;
 align-items: center;
 gap: 12px;
 margin-bottom: 16px;
 }
 .pkg-icon {
 font-size: 2rem;
 }
 .pkg-name {
 font-family: 'Barlow Condensed', sans-serif;
 font-weight: 800;
 font-size: 1.5rem;
 text-transform: uppercase;
 color: var(--dark);
 }
 .pkg-badge {
 font-size: 0.7rem;
 font-weight: 700;
 background: var(--red-pale);
 color: var(--red);
 padding: 4px 8px;
 border-radius: 4px;
 letter-spacing: 0.05em;
 }
 .pkg-price {
 font-family: 'Barlow Condensed', sans-serif;
 font-weight: 900;
 font-size: 2rem;
 margin-bottom: 16px;
 }
 .pkg-desc {
 font-size: 0.9rem;
 color: var(--muted);
 margin-bottom: 24px;
 flex: 1;
 }
 .pkg-features {
 list-style: none;
 padding: 0;
 margin-bottom: 24px;
 }
 .pkg-features li {
 font-size: 0.85rem;
 color: var(--dark);
 margin-bottom: 8px;
 display: flex;
 align-items: center;
 gap: 8px;
 }
 .pkg-features li::before {
 content: '✓';
 color: var(--red);
 font-weight: bold;
 }
 </style>
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
 <a href="packages.php" class="nav-item active" id="nav-packages"><span class="icon"></span> Packages</a>
 <div class="nav-section-label">Account</div>
 <a href="profile.php" class="nav-item" id="nav-profile"><span class="icon"></span> My Profile</a>
 <a href="payment_history.php" class="nav-item" id="nav-payments"><span class="icon"></span> Payments</a>
 </nav>
 </aside>

 <div class="main-area">
 <header class="topbar" style="justify-content: space-between; border-bottom: none; padding-top: 20px;">
 <div class="topbar-left">
 <h2>Event Packages</h2>
 <p>Find the perfect plan for your event size</p>
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
 <div class="packages-grid">
 <?php foreach ($packages as $pkg): 
 $features = explode('|', $pkg['features']);
 ?>
 <div class="pkg-card">
 <div class="pkg-header">
 <span class="pkg-icon"><?= htmlspecialchars($pkg['icon']) ?></span>
 <span class="pkg-name"><?= htmlspecialchars($pkg['name']) ?></span>
 <?php if($pkg['badge']): ?>
 <span class="pkg-badge"><?= htmlspecialchars($pkg['badge']) ?></span>
 <?php endif; ?>
 </div>
 <div class="pkg-price" style="color: <?= htmlspecialchars($pkg['color']) ?>">
 TZS <?= number_format($pkg['price']) ?>
 </div>
 <p class="pkg-desc"><?= htmlspecialchars($pkg['description']) ?></p>
 <ul class="pkg-features">
 <?php foreach($features as $f): ?>
 <li><?= htmlspecialchars(trim($f)) ?></li>
 <?php endforeach; ?>
 </ul>
 <a href="booking.php" class="btn-book" style="text-align: center; text-decoration: none;">Book Now</a>
 </div>
 <?php endforeach; ?>
 </div>
 </main>
 </div>
</div>
</body>
</html>
