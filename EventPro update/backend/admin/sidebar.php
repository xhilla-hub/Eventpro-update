<?php
// Admin Sidebar Include
// Usage: include 'sidebar.php'; (pass $active_page before including)
// e.g., $active_page = 'bookings';
if (!isset($active_page)) $active_page = 'bookings';
$user_name = $_SESSION['user_name'] ?? 'Admin';
$user_email = $_SESSION['user_email'] ?? 'admin@gmail.com';
$initials = strtoupper(substr($user_name, 0, 1));

// Badge counts
include_once '../config/database.php';
$pending_cnt = $pdo->query("SELECT COUNT(*) AS cnt FROM bookings WHERE status='pending'")->fetch()['cnt'];
$user_cnt = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role='user'")->fetch()['cnt'];
?>
<aside class="sidebar" id="sidebar">
 <div class="sidebar-logo">
 <div class="logo-box">EP</div>
 <span class="logo-name">Admin Panel</span>
 </div>
 <nav class="sidebar-nav">
 <div class="nav-section-label">Management</div>
 <a href="dashboard.php" class="nav-item <?= $active_page==='bookings'?'active':'' ?>">
 <span class="icon"></span> All Bookings
 <?php if ($pending_cnt > 0): ?>
 <span class="nav-badge"><?= $pending_cnt ?></span>
 <?php endif; ?>
 </a>
 <a href="users.php" class="nav-item <?= $active_page==='users'?'active':'' ?>">
 <span class="icon"></span> Users
 <span class="nav-badge" style="background:rgba(255,255,255,0.15)"><?= $user_cnt ?></span>
 </a>
 <a href="packages.php" class="nav-item <?= $active_page==='packages'?'active':'' ?>">
 <span class="icon"></span> Packages
 </a>
 <a href="payments.php" class="nav-item <?= $active_page==='payments'?'active':'' ?>">
 <span class="icon"></span> Payments
 </a>
 <div class="nav-section-label">System</div>
 <a href="../auth/logout.php" class="nav-item">
 <span class="icon">⏻</span> Logout
 </a>
 </nav>
 <div class="sidebar-footer">
 <div class="user-pill">
 <div class="user-avatar"><?= $initials ?></div>
 <div class="user-info">
 <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
 <div class="user-role">Administrator</div>
 </div>
 </div>
 </div>
</aside>
