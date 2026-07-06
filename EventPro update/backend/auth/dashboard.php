<?php
session_start();
if (!isset($_SESSION['user_id'])) {
 header("Location: login.php");
 exit();
}
include '../config/database.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email= $_SESSION['user_email'];

// Get stats
$total_bookings = $pdo->query("SELECT COUNT(*) AS cnt FROM bookings WHERE user_id=$user_id")->fetch()['cnt'];
$confirmed = $pdo->query("SELECT COUNT(*) AS cnt FROM bookings WHERE user_id=$user_id AND status='confirmed'")->fetch()['cnt'];
$pending = $pdo->query("SELECT COUNT(*) AS cnt FROM bookings WHERE user_id=$user_id AND status='pending'")->fetch()['cnt'];
$total_spent = $pdo->query("SELECT COALESCE(SUM(amount),0) AS tot FROM payments WHERE user_id=$user_id AND status='completed'")->fetch()['tot'];

// Get recent bookings
$recent = $pdo->query("
 SELECT b.*, p.name AS pkg_name, p.icon AS pkg_icon
 FROM bookings b
 JOIN packages p ON b.package_id = p.id
 WHERE b.user_id = $user_id
 ORDER BY b.created_at DESC
 LIMIT 5
");

$hour = date('H');
$greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
$initials = strtoupper(substr($user_name, 0, 1));

// Get upcoming event (nearest future booking)
$upcoming = $pdo->query("
 SELECT b.event_name, b.event_date, b.event_location, p.name AS pkg_name
 FROM bookings b JOIN packages p ON b.package_id=p.id
 WHERE b.user_id=$user_id AND b.event_date >= CURRENT_DATE
 ORDER BY b.event_date ASC LIMIT 1
")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>Dashboard – EventPro</title>
 <link rel="stylesheet" href="../../css/dashboard.css"/>
</head>
<body>
<div class="app-layout">

 <!-- ── SIDEBAR ── -->
 <aside class="sidebar" id="sidebar">
 <div class="sidebar-logo">
 <div class="logo-box">EP</div>
 <span class="logo-name">EventPro</span>
 </div>
 <nav class="sidebar-nav">
 <div class="nav-section-label">Main</div>
 <a href="dashboard.php" class="nav-item active" id="nav-dashboard">
 <span class="icon"></span> Dashboard
 </a>
 <a href="booking.php" class="nav-item" id="nav-booking">
 <span class="icon"></span> Book an Event
 </a>
 <a href="my_bookings.php" class="nav-item" id="nav-mybookings">
 <span class="icon"></span> My Booking
 <?php if ($pending > 0): ?>
 <span class="nav-badge"><?= $pending ?></span>
 <?php endif; ?>
 </a>
 <div class="nav-section-label">Marketplace</div>
 <a href="vendors.php" class="nav-item" id="nav-vendors">
 <span class="icon"></span> Browse Vendors
 </a>
 <a href="packages.php" class="nav-item" id="nav-packages">
 <span class="icon"></span> Packages
 </a>
 <div class="nav-section-label">Account</div>
 <a href="profile.php" class="nav-item" id="nav-profile"><span class="icon"></span> My Profile</a>
 <a href="payment_history.php" class="nav-item" id="nav-payments"><span class="icon"></span> Payments</a>
 <a href="#" class="nav-item" id="nav-support">
 <span class="icon"></span> Support
 </a>
 </nav>
 <div class="voyago-promo">
 <h4>50% OFF</h4>
 <p>On your first event booking this season</p>
 <a href="booking.php" class="btn-claim">Claim Now →</a>
 </div>
 </aside>

 <!-- ── MAIN AREA ── -->
 <div class="main-area">
 <!-- TOPBAR -->
 <header class="topbar" style="justify-content: space-between; border-bottom: none; padding-top: 20px;">
 <div class="topbar-left">
 <h2>Dashboard</h2>
 <p><?= date('l, d F Y') ?></p>
 </div>
 <div class="topbar-actions" style="gap: 20px;">
 <div class="topbar-search" style="margin-right: 12px; border-color: var(--border); border-radius: 50px;">
 <span class="search-icon" style="color:var(--muted)"></span>
 <input type="text" placeholder="Search events..." style="background:none; border:none; outline:none;"/>
 </div>
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

 <!-- PAGE CONTENT -->
 <main class="page-content">

 <!-- GREETING CARD -->
 <div class="greeting-card">
 <div class="greeting-text">
 <div class="greet"><?= $greet ?> </div>
 <h1><?= htmlspecialchars(explode(' ', $user_name)[0]) ?></h1>
 <?php if ($upcoming): ?>
 <p>Next event: <strong><?= htmlspecialchars($upcoming['event_name']) ?></strong>
 — <?= date('D, d M Y', strtotime($upcoming['event_date'])) ?></p>
 <?php else: ?>
 <p>You have no upcoming events. Ready to plan your next big event?</p>
 <?php endif; ?>
 </div>
 <div class="greeting-action">
 <a href="booking.php" id="btn-book-now">+ Book an Event</a>
 </div>
 </div>

 <!-- STATS GRID -->
 <div class="stats-grid">
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-info">
 <div class="stat-label">Total Bookings</div>
 <div class="stat-value"><?= $total_bookings ?></div>
 <div class="stat-change">↑ All time</div>
 </div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-info">
 <div class="stat-label">Confirmed</div>
 <div class="stat-value"><?= $confirmed ?></div>
 <div class="stat-change">Active events</div>
 </div>
 </div>
 <div class="stat-card">
 <div class="stat-icon">⏳</div>
 <div class="stat-info">
 <div class="stat-label">Pending</div>
 <div class="stat-value"><?= $pending ?></div>
 <div class="stat-change">Awaiting payment</div>
 </div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-info">
 <div class="stat-label">Total Spent</div>
 <div class="stat-value">TZS <?= number_format($total_spent) ?></div>
 <div class="stat-change">Completed payments</div>
 </div>
 </div>
 </div>

 <!-- BOOKINGS + QUICK ACTIONS -->
 <div class="three-col">
 <!-- Recent Bookings -->
 <div class="section-card">
 <div class="section-card-header">
 <h3>Recent Bookings</h3>
 <a href="my_bookings.php" class="see-all">See all →</a>
 </div>
 <div class="filter-tabs">
 <button class="tab active" onclick="filterTab(this,'all')">All</button>
 <button class="tab" onclick="filterTab(this,'pending')">Pending</button>
 <button class="tab" onclick="filterTab(this,'confirmed')">Confirmed</button>
 </div>
 <div class="event-list" id="event-list">
 <?php if ($recent->rowCount() === 0): ?>
 <div class="empty-state" style="padding:40px 24px">
 <div class="empty-icon"></div>
 <h3>No Bookings Yet</h3>
 <p>Start by booking your first event!</p>
 <a href="booking.php">Book Now →</a>
 </div>
 <?php else: ?>
 <?php while ($b = $recent->fetch()): ?>
 <div class="event-row" data-status="<?= $b['status'] ?>">
 <div class="event-date-box">
 <div class="event-date-day"><?= date('d', strtotime($b['event_date'])) ?></div>
 <div class="event-date-mon"><?= date('M', strtotime($b['event_date'])) ?></div>
 </div>
 <div class="event-info">
 <div class="event-name"><?= htmlspecialchars($b['event_name']) ?></div>
 <div class="event-meta">
 <span class="online-dot"></span>
 <?= htmlspecialchars($b['pkg_icon']) ?> <?= htmlspecialchars($b['pkg_name']) ?>
 · <?= htmlspecialchars($b['event_location']) ?>
 </div>
 </div>
 <span class="event-badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
 </div>
 <?php endwhile; ?>
 <?php endif; ?>
 </div>
 </div>

 <!-- Quick Actions -->
 <div class="section-card">
 <div class="section-card-header">
 <h3>Quick Actions</h3>
 </div>
 <div class="quick-actions">
 <a href="booking.php" class="quick-action" id="qa-book">
 <span class="qa-icon"></span>
 <span class="qa-label">New Booking</span>
 </a>
 <a href="my_bookings.php" class="quick-action" id="qa-mybookings">
 <span class="qa-icon"></span>
 <span class="qa-label">My Bookings</span>
 </a>
 <a href="#" class="quick-action" id="qa-vendors">
 <span class="qa-icon"></span>
 <span class="qa-label">Browse Vendors</span>
 </a>
 <a href="#" class="quick-action" id="qa-payments">
 <span class="qa-icon"></span>
 <span class="qa-label">Payments</span>
 </a>
 </div>

 <?php if ($upcoming): ?>
 <div style="padding: 0 24px 24px;">
 <div style="background:var(--red-pale);border-radius:10px;padding:16px;border-left:4px solid var(--red)">
 <div style="font-size:0.72rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--red);margin-bottom:6px;">
 UPCOMING EVENT
 </div>
 <div style="font-weight:700;font-size:0.95rem;color:var(--dark);margin-bottom:2px;">
 <?= htmlspecialchars($upcoming['event_name']) ?>
 </div>
 <div style="font-size:0.8rem;color:var(--muted)">
 <?= date('D, d M Y', strtotime($upcoming['event_date'])) ?>
 · <?= htmlspecialchars($upcoming['pkg_name']) ?>
 </div>
 </div>
 </div>
 <?php endif; ?>
 </div>
 </div>

 </main>
 </div>
</div>

<script>
function toggleSidebar() {
 document.getElementById('sidebar').classList.toggle('open');
}
function filterTab(el, status) {
 document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
 el.classList.add('active');
 document.querySelectorAll('.event-row').forEach(row => {
 row.style.display = (status === 'all' || row.dataset.status === status) ? 'flex' : 'none';
 });
}
</script>
</body>
</html>
