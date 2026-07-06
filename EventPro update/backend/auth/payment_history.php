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

// Fetch payments
$stmt = $pdo->prepare("SELECT p.*, b.event_name, b.event_date FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE p.user_id = :uid ORDER BY p.created_at DESC");
$stmt->execute(['uid' => $user_id]);
$payments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>Payment History – EventPro</title>
 <link rel="stylesheet" href="../../css/dashboard.css"/>
 <style>
 .payment-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
 .payment-table th, .payment-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); }
 .payment-table th { font-weight: 700; color: var(--muted); text-transform: uppercase; font-size: 0.8rem; }
 .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
 .status-completed { background: #dcfce7; color: #166534; }
 .status-pending { background: #fef9c3; color: #854d0e; }
 .status-failed { background: #fee2e2; color: #991b1b; }
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
 <a href="packages.php" class="nav-item" id="nav-packages"><span class="icon"></span> Packages</a>
 <div class="nav-section-label">Account</div>
 <a href="profile.php" class="nav-item" id="nav-profile"><span class="icon"></span> My Profile</a>
 <a href="payment_history.php" class="nav-item active" id="nav-payments"><span class="icon"></span> Payments</a>
 </nav>
 </aside>

 <div class="main-area">
 <header class="topbar" style="justify-content: space-between; border-bottom: none; padding-top: 20px;">
 <div class="topbar-left">
 <h2>Payment History</h2>
 <p>Review your past transactions</p>
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
 <div class="section-card">
 <?php if (empty($payments)): ?>
 <p>No payments found.</p>
 <?php else: ?>
 <table class="payment-table">
 <thead>
 <tr>
 <th>Date</th>
 <th>Event</th>
 <th>Amount (TZS)</th>
 <th>Method</th>
 <th>Ref / Code</th>
 <th>Status</th>
 </tr>
 </thead>
 <tbody>
 <?php foreach ($payments as $p): ?>
 <tr>
 <td><?= date('d M Y, H:i', strtotime($p['created_at'])) ?></td>
 <td><?= htmlspecialchars($p['event_name']) ?></td>
 <td><?= number_format($p['amount']) ?></td>
 <td style="text-transform: capitalize;"><?= htmlspecialchars($p['method']) ?></td>
 <td><?= htmlspecialchars($p['mpesa_code'] ?: $p['checkout_id'] ?: '-') ?></td>
 <td><span class="status-badge status-<?= strtolower($p['status']) ?>"><?= htmlspecialchars($p['status']) ?></span></td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table>
 <?php endif; ?>
 </div>
 </main>
 </div>
</div>
</body>
</html>
