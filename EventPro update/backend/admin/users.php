<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
 header("Location: ../auth/login.php"); exit();
}
include '../config/database.php';

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$initials = strtoupper(substr($user_name, 0, 1));

// Handle delete user action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
 $uid = (int)$_POST['user_id'];
 if ($_POST['action'] === 'delete' && $uid !== (int)$_SESSION['user_id']) {
 $pdo->prepare("DELETE FROM users WHERE id=:id AND role='user'")->execute(['id' => $uid]);
 }
 header("Location: users.php"); exit();
}

// Stats
$total_users = $pdo->query("SELECT COUNT(*) AS c FROM users WHERE role='user'")->fetch()['c'];
$total_bookings_made = $pdo->query("SELECT COUNT(*) AS c FROM bookings")->fetch()['c'];
$total_paid = $pdo->query("SELECT COUNT(DISTINCT user_id) AS c FROM payments WHERE status='completed'")->fetch()['c'];

// Filter / search
$search = trim($_GET['q'] ?? '');
$where = "WHERE role='user'";
if ($search) {
 $safe = $pdo->quote('%'.$search.'%');
 $where .= " AND (fullname ILIKE $safe OR email ILIKE $safe OR phone ILIKE $safe)";
}
$users = $pdo->query("
 SELECT u.*,
 COUNT(b.id) AS booking_count,
 COALESCE(SUM(p.amount),0) AS total_spent
 FROM users u
 LEFT JOIN bookings b ON b.user_id = u.id
 LEFT JOIN payments p ON p.user_id = u.id AND p.status='completed'
 $where
 GROUP BY u.id
 ORDER BY u.created_at DESC
");

$active_page = 'users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>Admin – Users | EventPro</title>
 <link rel="stylesheet" href="../../css/dashboard.css"/>
 <style>
 .user-table { width:100%; border-collapse:collapse; }
 .user-table th { background:var(--bg); padding:12px 16px; text-align:left; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.09em; color:var(--muted); border-bottom:2px solid var(--border); }
 .user-table td { padding:14px 16px; border-bottom:1px solid var(--border); font-size:0.87rem; vertical-align:middle; }
 .user-table tr:hover td { background:#fafafa; }
 .user-avt { width:38px;height:38px;border-radius:50%;background:var(--primary);color:var(--secondary);font-weight:900;font-size:0.95rem;display:grid;place-items:center;font-family:'Barlow Condensed',sans-serif; }
 .btn-del { padding:5px 12px;border-radius:6px;font-size:0.75rem;font-weight:700;border:none;cursor:pointer;background:#FEE2E2;color:#991B1B;transition:background 0.2s; }
 .btn-del:hover { background:#FECACA; }
 </style>
</head>
<body>
<div class="app-layout">

 <?php include 'sidebar.php'; ?>

 <div class="main-area">
 <header class="topbar">
 <div class="topbar-left">
 <h2>Users</h2>
 <p>All registered platform users</p>
 </div>
 <div class="topbar-actions" style="gap:20px;">
 <form method="GET" action="" style="display:flex;align-items:center;">
 <div class="topbar-search">
 <span class="search-icon"></span>
 <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search users…"/>
 </div>
 </form>
 <div class="user-pill" style="background:none;padding:0;gap:10px;border:none;">
 <div class="user-avatar"><?= $initials ?></div>
 <div class="user-info" style="line-height:1.2;">
 <div class="user-name" style="font-size:0.9rem;"><?= htmlspecialchars($user_name) ?></div>
 <div class="user-role" style="font-size:0.72rem;color:var(--muted);">Administrator</div>
 </div>
 </div>
 </div>
 </header>

 <main class="page-content">

 <!-- STATS -->
 <div class="stats-grid">
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-info"><div class="stat-label">Total Users</div><div class="stat-value"><?= $total_users ?></div></div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-info"><div class="stat-label">Total Bookings</div><div class="stat-value"><?= $total_bookings_made ?></div></div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-info"><div class="stat-label">Paid Users</div><div class="stat-value"><?= $total_paid ?></div></div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-info"><div class="stat-label">Active Users</div><div class="stat-value"><?= $total_users ?></div></div>
 </div>
 </div>

 <!-- TABLE -->
 <div class="section-card">
 <div class="section-card-header">
 <h3>Registered Users (<?= $total_users ?>)</h3>
 <?php if ($search): ?>
 <a href="users.php" style="font-size:0.8rem;color:var(--muted);"> Clear search</a>
 <?php endif; ?>
 </div>
 <div style="overflow-x:auto;padding:0 24px 24px;">
 <table class="user-table">
 <thead>
 <tr>
 <th>#</th>
 <th>User</th>
 <th>Email</th>
 <th>Phone</th>
 <th>Bookings</th>
 <th>Total Spent</th>
 <th>Joined</th>
 <th>Actions</th>
 </tr>
 </thead>
 <tbody>
 <?php $i=1; while ($u = $users->fetch()): ?>
 <tr>
 <td style="color:var(--muted);font-size:0.8rem;"><?= $i++ ?></td>
 <td>
 <div style="display:flex;align-items:center;gap:10px;">
 <div class="user-avt"><?= strtoupper(substr($u['fullname'],0,1)) ?></div>
 <div>
 <div style="font-weight:700;font-size:0.9rem;"><?= htmlspecialchars($u['fullname']) ?></div>
 <div style="font-size:0.72rem;color:var(--muted);">User ID #<?= $u['id'] ?></div>
 </div>
 </div>
 </td>
 <td><?= htmlspecialchars($u['email']) ?></td>
 <td><?= htmlspecialchars($u['phone']) ?></td>
 <td><span class="pkg-chip"><?= $u['booking_count'] ?> bookings</span></td>
 <td style="font-weight:700;color:var(--secondary);">TZS <?= number_format($u['total_spent']) ?></td>
 <td style="font-size:0.82rem;color:var(--muted);"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
 <td>
 <form method="POST" onsubmit="return confirm('Delete this user and all their data?')">
 <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
 <button type="submit" name="action" value="delete" class="btn-del">Delete</button>
 </form>
 </td>
 </tr>
 <?php endwhile; ?>
 </tbody>
 </table>
 <?php if ($total_users === 0): ?>
 <div class="empty-state"><div class="empty-icon"></div><h3>No Users Found</h3><p>No registered users yet.</p></div>
 <?php endif; ?>
 </div>
 </div>
 </main>
 </div>
</div>
</body>
</html>
