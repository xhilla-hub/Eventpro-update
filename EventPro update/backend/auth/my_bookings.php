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
$user_email= $_SESSION['user_email'] ?? 'user@eventpro.com';

// Filter
$filter = $_GET['status'] ?? 'all';
$where = $filter !== 'all' ? "AND b.status='$filter'" : "";

$bookings = $pdo->query("
 SELECT b.*, p.name AS pkg_name, p.icon AS pkg_icon, p.price AS pkg_price,
 pay.status AS pay_status, pay.mpesa_code
 FROM bookings b
 JOIN packages p ON b.package_id = p.id
 LEFT JOIN payments pay ON pay.booking_id = b.id AND pay.status = 'completed'
 WHERE b.user_id = $user_id $where
 ORDER BY b.created_at DESC
");

$upcoming_cnt = $pdo->query("SELECT COUNT(*) AS cnt FROM bookings WHERE user_id=$user_id AND (status='pending' OR status='confirmed')")->fetch()['cnt'];
$completed_cnt = $pdo->query("SELECT COUNT(*) AS cnt FROM bookings WHERE user_id=$user_id AND status='completed'")->fetch()['cnt'];
$cancelled_cnt = $pdo->query("SELECT COUNT(*) AS cnt FROM bookings WHERE user_id=$user_id AND status='cancelled'")->fetch()['cnt'];
$total_cnt = $pdo->query("SELECT COUNT(*) AS cnt FROM bookings WHERE user_id=$user_id")->fetch()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>My Bookings – EventPro</title>
 <link rel="stylesheet" href="../../css/dashboard.css"/>
</head>
<body>
<div class="app-layout">

 <!-- SIDEBAR -->
 <aside class="sidebar" id="sidebar">
 <div class="sidebar-logo">
 <div class="logo-box">EP</div>
 <span class="logo-name">EventPro</span>
 </div>
 <nav class="sidebar-nav">
 <div class="nav-section-label">Main</div>
 <a href="dashboard.php" class="nav-item" id="nav-dashboard"><span class="icon"></span> Dashboard</a>
 <a href="booking.php" class="nav-item" id="nav-booking"><span class="icon"></span> Book an Event</a>
 <a href="my_bookings.php" class="nav-item active" id="nav-mybookings"><span class="icon"></span> My Booking</a>
 <div class="nav-section-label">Marketplace</div>
 <a href="vendors.php" class="nav-item" id="nav-vendors"><span class="icon"></span> Browse Vendors</a>
 <a href="packages.php" class="nav-item" id="nav-packages"><span class="icon"></span> Packages</a>
 <div class="nav-section-label">Account</div>
 <a href="profile.php" class="nav-item" id="nav-profile"><span class="icon"></span> My Profile</a>
 <a href="payment_history.php" class="nav-item" id="nav-payments"><span class="icon"></span> Payments</a>
 </nav>
 </aside>

 <div class="main-area">
 <header class="topbar" style="justify-content: space-between; border-bottom: none; padding-top: 20px;">
 <div class="topbar-left">
 <h2>My Booking</h2>
 <p>Manage and track all your event reservations</p>
 </div>
 <div class="topbar-actions" style="gap: 20px;">
 <button class="topbar-btn" style="border:none; background: var(--primary-pale); color: var(--primary);"><span class="notif-dot"></span></button>
 <button class="topbar-btn" style="border:none; background: var(--primary-pale); color: var(--primary);"></button>
 <div class="user-pill" style="background:none; padding:0; gap: 12px; border:none;">
 <div class="user-avatar" style="width:40px; height:40px; font-size:1.1rem;"><?= $initials ?></div>
 <div class="user-info" style="line-height:1.2;">
 <div class="user-name" style="font-weight:700; font-size:0.9rem;"><?= htmlspecialchars($user_name) ?></div>
 <div class="user-role" style="font-size:0.75rem; color:var(--muted);"><?= htmlspecialchars($user_email) ?></div>
 </div>
 </div>
 <a href="logout.php" style="color:var(--muted); font-size:1.2rem; margin-left:8px;" title="Logout">⏻</a>
 </div>
 </header>

 <main class="page-content" style="padding-top: 10px;">

 <!-- FILTER TABS -->
 <div class="filter-tabs" style="padding: 0 0 24px 0; gap: 16px; border-bottom: 1px solid var(--border); margin-bottom: 24px;">
 <a href="my_bookings.php?status=all"
 class="tab <?= $filter==='all'?'active':'' ?>" style="border-radius:20px; padding: 10px 24px;">All Bookings</a>
 <a href="my_bookings.php?status=pending"
 class="tab <?= $filter==='pending'?'active':'' ?>" style="border:none; background:none; color: <?= $filter==='pending'?'var(--primary)':'var(--muted)'?>">Pending</a>
 <a href="my_bookings.php?status=confirmed"
 class="tab <?= $filter==='confirmed'?'active':'' ?>" style="border:none; background:none; color: <?= $filter==='confirmed'?'var(--primary)':'var(--muted)'?>">Confirmed</a>
 <a href="my_bookings.php?status=completed"
 class="tab <?= $filter==='completed'?'active':'' ?>" style="border:none; background:none; color: <?= $filter==='completed'?'var(--primary)':'var(--muted)'?>">Completed</a>
 <a href="my_bookings.php?status=cancelled"
 class="tab <?= $filter==='cancelled'?'active':'' ?>" style="border:none; background:none; color: <?= $filter==='cancelled'?'var(--primary)':'var(--muted)'?>">Cancelled</a>
 </div>

 <!-- STAT CARDS -->
 <div class="stats-grid">
 <div class="stat-card" style="align-items:center;">
 <div class="stat-info">
 <div class="stat-value"><?= $upcoming_cnt ?></div>
 <div class="stat-label" style="text-transform:capitalize; font-size:0.8rem; margin-top:8px;">Upcoming Events</div>
 </div>
 <div class="stat-icon" style="background:var(--primary-pale); color:var(--primary); font-size:1.1rem; width:36px; height:36px; border-radius:50%;"></div>
 </div>
 <div class="stat-card" style="align-items:center;">
 <div class="stat-info">
 <div class="stat-value"><?= $completed_cnt ?></div>
 <div class="stat-label" style="text-transform:capitalize; font-size:0.8rem; margin-top:8px;">Completed Events</div>
 </div>
 <div class="stat-icon" style="background:#e6f9ed; color:#2ecc71; font-size:1.1rem; width:36px; height:36px; border-radius:50%;"></div>
 </div>
 <div class="stat-card" style="align-items:center;">
 <div class="stat-info">
 <div class="stat-value"><?= $cancelled_cnt ?></div>
 <div class="stat-label" style="text-transform:capitalize; font-size:0.8rem; margin-top:8px;">Cancelled Events</div>
 </div>
 <div class="stat-icon" style="background:#fde8e8; color:#e74c3c; font-size:1.1rem; width:36px; height:36px; border-radius:50%;"></div>
 </div>
 <div class="stat-card" style="align-items:center;">
 <div class="stat-info">
 <div class="stat-value"><?= $total_cnt ?></div>
 <div class="stat-label" style="text-transform:capitalize; font-size:0.8rem; margin-top:8px;">Total Bookings</div>
 </div>
 <div class="stat-icon" style="background:#fff3cd; color:#f39c12; font-size:1.1rem; width:36px; height:36px; border-radius:50%;"></div>
 </div>
 </div>

 <!-- BOOKINGS LIST -->
 <div class="event-list">
 <?php $rows = $bookings->rowCount(); ?>
 <?php if ($rows === 0): ?>
 <div class="empty-state">
 <div class="empty-icon"></div>
 <h3>No Bookings Found</h3>
 <p><?= $filter !== 'all' ? "No $filter bookings yet." : "You haven't booked any events yet." ?></p>
 <a href="booking.php" class="voyago-btn-primary" style="display:inline-block; margin-top:16px;">+ Book Your First Event</a>
 </div>
 <?php else: ?>
 <?php while ($b = $bookings->fetch()): 
 $status_color = 'var(--muted)';
 $status_bg = 'var(--bg)';
 $status_label = ucfirst($b['status']);
 if ($b['status'] == 'pending' || $b['status'] == 'confirmed') {
 $status_color = '#059669'; $status_bg = '#d1fae5'; $status_label = 'Upcoming';
 } elseif ($b['status'] == 'cancelled') {
 $status_color = '#dc2626'; $status_bg = '#fee2e2';
 } elseif ($b['status'] == 'completed') {
 $status_color = '#4b5563'; $status_bg = '#f3f4f6';
 }
 ?>
 <div class="voyago-booking-card">
 <img src="../../images/event_concert.png" alt="Event" class="voyago-bc-img"/>
 <div class="voyago-bc-info">
 <div class="voyago-bc-title">
 <h4><?= htmlspecialchars($b['event_name']) ?></h4>
 <span style="font-size:0.65rem; font-weight:700; padding:3px 10px; border-radius:20px; background:<?= $status_bg ?>; color:<?= $status_color ?>;">
 <?= $status_label ?>
 </span>
 </div>
 <div class="voyago-bc-loc"><?= htmlspecialchars($b['event_location']) ?></div>
 <div class="voyago-bc-meta">
 <div class="voyago-bc-meta-item">
 <span>Date</span>
 <span><?= date('M d, Y', strtotime($b['event_date'])) ?></span>
 </div>
 <div class="voyago-bc-meta-item">
 <span>Booking ID</span>
 <span>BK-<?= date('Y', strtotime($b['created_at'])) ?>-<?= sprintf('%04d', $b['id']) ?></span>
 </div>
 <div class="voyago-bc-meta-item">
 <span>Type</span>
 <span><?= htmlspecialchars($b['event_type']) ?></span>
 </div>
 </div>
 </div>
 <div class="voyago-bc-actions">
 <div class="voyago-bc-price">TZS <?= number_format($b['total_amount']) ?></div>
 <div class="voyago-bc-btns">
 <button class="voyago-btn-outline" onclick="alert('Details for Booking #<?= $b['id'] ?>')">View Details</button>
 <a href="<?= ($b['pay_status'] !== 'completed') ? 'payment.php?booking_id='.$b['id'] : '#' ?>" class="voyago-btn-primary">Manage</a>
 </div>
 </div>
 </div>
 <?php endwhile; ?>
 <?php endif; ?>
 </div>
 </main>
 </div>
</div>
</body>
</html>
