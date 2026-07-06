<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
 header("Location: ../auth/login.php"); exit();
}
include '../config/database.php';

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$initials = strtoupper(substr($user_name, 0, 1));

// Stats
$total_revenue = $pdo->query("SELECT COALESCE(SUM(amount),0) AS t FROM payments WHERE status='completed'")->fetch()['t'];
$total_paid = $pdo->query("SELECT COUNT(*) AS c FROM payments WHERE status='completed'")->fetch()['c'];
$total_pending = $pdo->query("SELECT COUNT(*) AS c FROM payments WHERE status='pending'")->fetch()['c'];
$total_payments = $pdo->query("SELECT COUNT(*) AS c FROM payments")->fetch()['c'];

// Filter
$filter = $_GET['status'] ?? 'all';
$where = $filter !== 'all' ? "WHERE pay.status='$filter'" : "";

$payments = $pdo->query("
 SELECT pay.*, u.fullname, u.email, u.phone, b.event_name, b.event_date, p.name AS pkg_name
 FROM payments pay
 JOIN users u ON u.id = pay.user_id
 JOIN bookings b ON b.id = pay.booking_id
 JOIN packages p ON p.id = b.package_id
 $where
 ORDER BY pay.created_at DESC
");

$active_page = 'payments';
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>Admin – Payments | EventPro</title>
 <link rel="stylesheet" href="../../css/dashboard.css"/>
</head>
<body>
<div class="app-layout">

 <?php include 'sidebar.php'; ?>

 <div class="main-area">
 <header class="topbar">
 <div class="topbar-left">
 <h2>Payments</h2>
 <p>Full payment log across all bookings</p>
 </div>
 <div class="topbar-actions" style="gap:20px;">
 <div class="topbar-search">
 <span class="search-icon"></span>
 <input type="text" id="search-pay" placeholder="Search payments…"/>
 </div>
 <div class="user-pill" style="background:none;padding:0;gap:10px;border:none;">
 <div class="user-avatar"><?= $initials ?></div>
 <div class="user-info" style="line-height:1.2;"><div class="user-name" style="font-size:0.9rem;"><?= htmlspecialchars($user_name) ?></div></div>
 </div>
 </div>
 </header>

 <main class="page-content">

 <!-- STATS -->
 <div class="stats-grid">
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-info">
 <div class="stat-label">Total Revenue</div>
 <div class="stat-value" style="font-size:1.3rem;">TZS <?= number_format($total_revenue) ?></div>
 <div class="stat-change">↑ Completed</div>
 </div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-info"><div class="stat-label">Paid</div><div class="stat-value"><?= $total_paid ?></div></div>
 </div>
 <div class="stat-card">
 <div class="stat-icon">⏳</div>
 <div class="stat-info"><div class="stat-label">Pending</div><div class="stat-value"><?= $total_pending ?></div></div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-info"><div class="stat-label">All Transactions</div><div class="stat-value"><?= $total_payments ?></div></div>
 </div>
 </div>

 <!-- FILTER TABS -->
 <div class="filter-tabs" style="padding:0 0 20px 0;gap:12px;border-bottom:1px solid var(--border);margin-bottom:24px;">
 <?php foreach (['all'=>'All','completed'=>'Completed','pending'=>'Pending','failed'=>'Failed'] as $k=>$v): ?>
 <a href="payments.php?status=<?= $k ?>" class="tab <?= $filter===$k?'active':'' ?>"><?= $v ?></a>
 <?php endforeach; ?>
 </div>

 <!-- PAYMENTS TABLE -->
 <div class="section-card">
 <div class="section-card-header">
 <h3>Payment Records</h3>
 </div>
 <div style="overflow-x:auto;padding:0 24px 24px;">
 <table class="bookings-table" id="pay-table">
 <thead>
 <tr>
 <th>#</th>
 <th>Client</th>
 <th>Event</th>
 <th>Package</th>
 <th>Amount</th>
 <th>Method</th>
 <th>M-Pesa / Ref</th>
 <th>Status</th>
 <th>Date</th>
 </tr>
 </thead>
 <tbody>
 <?php $i=1; while ($p = $payments->fetch()):
 $sc = ['completed'=>['#065F46','#D1FAE5'],'pending'=>['#92400E','#FEF3C7'],'failed'=>['#991B1B','#FEE2E2']];
 [$sc_t,$sc_b] = $sc[$p['status']] ?? ['#64748B','#F1F5F9'];
 ?>
 <tr class="pay-row">
 <td style="color:var(--muted);font-size:0.8rem;"><?= $i++ ?></td>
 <td>
 <div style="font-weight:700;font-size:0.88rem;"><?= htmlspecialchars($p['fullname']) ?></div>
 <div style="font-size:0.72rem;color:var(--muted);"><?= htmlspecialchars($p['phone']) ?></div>
 </td>
 <td style="font-size:0.85rem;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p['event_name']) ?></td>
 <td><span class="pkg-chip"><?= htmlspecialchars($p['pkg_name']) ?></span></td>
 <td style="font-weight:700;color:var(--secondary);white-space:nowrap;">TZS <?= number_format($p['amount']) ?></td>
 <td style="text-transform:uppercase;font-size:0.78rem;font-weight:700;color:var(--muted);"><?= htmlspecialchars($p['method']) ?></td>
 <td style="font-size:0.78rem;font-family:monospace;color:var(--dark);"><?= $p['mpesa_code'] ? htmlspecialchars($p['mpesa_code']) : '<span style="color:var(--muted)">—</span>' ?></td>
 <td><span style="font-size:0.68rem;font-weight:700;padding:4px 10px;border-radius:20px;background:<?= $sc_b ?>;color:<?= $sc_t ?>;"><?= ucfirst($p['status']) ?></span></td>
 <td style="font-size:0.78rem;color:var(--muted);white-space:nowrap;"><?= date('M d, Y H:i', strtotime($p['created_at'])) ?></td>
 </tr>
 <?php endwhile; ?>
 </tbody>
 </table>
 <?php if ($total_payments === 0): ?>
 <div class="empty-state"><div class="empty-icon"></div><h3>No Payments Yet</h3><p>Payments will appear here once customers start booking.</p></div>
 <?php endif; ?>
 </div>
 </div>
 </main>
 </div>
</div>
<script>
document.getElementById('search-pay')?.addEventListener('input', function() {
 const q = this.value.toLowerCase();
 document.querySelectorAll('.pay-row').forEach(r => r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none');
});
</script>
</body>
</html>
