<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
 header("Location: ../auth/login.php"); exit();
}
include '../config/database.php';

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$initials = strtoupper(substr($user_name, 0, 1));

// Handle status update actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['booking_id'])) {
 $bid = (int)$_POST['booking_id'];
 $map = ['accept' => 'confirmed', 'decline' => 'cancelled', 'fulfill' => 'completed'];
 if (isset($map[$_POST['action']])) {
 $pdo->prepare("UPDATE bookings SET status=:s WHERE id=:id")
 ->execute(['s' => $map[$_POST['action']], 'id' => $bid]);
 }
 header("Location: dashboard.php"); exit();
}

// Stats
$total_bookings = $pdo->query("SELECT COUNT(*) AS c FROM bookings")->fetch()['c'];
$pending = $pdo->query("SELECT COUNT(*) AS c FROM bookings WHERE status='pending'")->fetch()['c'];
$confirmed = $pdo->query("SELECT COUNT(*) AS c FROM bookings WHERE status='confirmed'")->fetch()['c'];
$completed = $pdo->query("SELECT COUNT(*) AS c FROM bookings WHERE status='completed'")->fetch()['c'];
$total_revenue = $pdo->query("SELECT COALESCE(SUM(amount),0) AS t FROM payments WHERE status='completed'")->fetch()['t'];

// Bookings list
$filter = $_GET['status'] ?? 'all';
$where = $filter !== 'all' ? "WHERE b.status='$filter'" : "";
$bookings = $pdo->query("
 SELECT b.*, p.name AS pkg_name, p.icon AS pkg_icon, u.fullname, u.email, u.phone
 FROM bookings b
 JOIN packages p ON b.package_id=p.id
 JOIN users u ON b.user_id=u.id
 $where
 ORDER BY b.created_at DESC
");
$active_page = 'bookings';

// Fetch all bookings for calendar
$cal_bookings = $pdo->query("
 SELECT b.id, b.event_name, b.event_date, b.event_location, b.status, b.total_amount, p.name AS pkg_name, p.icon AS pkg_icon, u.fullname
 FROM bookings b
 JOIN packages p ON b.package_id=p.id
 JOIN users u ON b.user_id=u.id
 WHERE b.status != 'cancelled'
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>Admin – All Bookings | EventPro</title>
 <link rel="stylesheet" href="../../css/dashboard.css"/>
 <style>
 .admin-action-btn { padding:6px 14px; border-radius:50px; font-size:0.76rem; font-weight:700; border:none; cursor:pointer; transition:all 0.2s; }
 .btn-accept { background:#D1FAE5; color:#065F46; } .btn-accept:hover { background:#A7F3D0; }
 .btn-decline { background:#FEE2E2; color:#991B1B; } .btn-decline:hover { background:#FECACA; }
 .btn-fulfill { background:#DBEAFE; color:#1E40AF; } .btn-fulfill:hover { background:#BFDBFE; }
 .admin-card-meta { font-size:0.78rem; color:#64748B; margin-top:3px; }

 /* Admin Calendar styles */
 .admin-cal-day {
 min-height: 85px;
 background: #F8FAFC;
 border: 1px solid var(--border);
 border-radius: 12px;
 padding: 8px;
 display: flex;
 flex-direction: column;
 justify-content: space-between;
 cursor: pointer;
 transition: all 0.2s;
 }
 .admin-cal-day:hover {
 border-color: var(--primary);
 background: var(--primary-pale);
 }
 .admin-cal-day.empty {
 background: transparent;
 border: none;
 cursor: default;
 opacity: 0;
 }
 .admin-cal-day.today {
 border: 2px solid var(--secondary);
 }
 .admin-cal-day .day-num {
 font-weight: 700;
 font-size: 0.9rem;
 color: var(--secondary);
 }
 .admin-cal-day .events-container {
 display: flex;
 flex-direction: column;
 gap: 4px;
 margin-top: 4px;
 }
 .cal-event-dot {
 font-size: 0.65rem;
 font-weight: 700;
 padding: 2px 6px;
 border-radius: 4px;
 white-space: nowrap;
 overflow: hidden;
 text-overflow: ellipsis;
 max-width: 100%;
 }
 .cal-event-dot.pending {
 background: #FEF3C7;
 color: #92400E;
 }
 .cal-event-dot.confirmed {
 background: #D1FAE5;
 color: #065F46;
 }
 .cal-event-dot.completed {
 background: #DBEAFE;
 color: #1E40AF;
 }
 </style>
</head>
<body>
<div class="app-layout">

 <?php include 'sidebar.php'; ?>

 <div class="main-area">
 <header class="topbar">
 <div class="topbar-left">
 <h2>All Bookings</h2>
 <p>Review, approve or reject customer booking requests</p>
 </div>
 <div class="topbar-actions" style="gap:20px;">
 <div class="topbar-search">
 <span class="search-icon"></span>
 <input type="text" id="search-bookings" placeholder="Search bookings…"/>
 </div>
 <div class="user-pill" style="background:none;padding:0;gap:12px;border:none;">
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
 <div class="stat-icon">⏳</div>
 <div class="stat-info">
 <div class="stat-label">Pending</div>
 <div class="stat-value"><?= $pending ?></div>
 <div class="stat-change">Needs action</div>
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
 <div class="stat-icon"></div>
 <div class="stat-info">
 <div class="stat-label">Completed</div>
 <div class="stat-value"><?= $completed ?></div>
 <div class="stat-change">Fulfilled</div>
 </div>
 </div>
 <div class="stat-card">
 <div class="stat-icon"></div>
 <div class="stat-info">
 <div class="stat-label">Total Revenue</div>
 <div class="stat-value" style="font-size:1.3rem;">TZS <?= number_format($total_revenue) ?></div>
 <div class="stat-change">Completed payments</div>
 </div>
 </div>
 </div>

 <!-- COLLAPSIBLE CALENDAR VIEW -->
 <div class="admin-calendar-section" style="margin-bottom: 24px; margin-top: 24px;">
 <button type="button" class="voyago-btn-primary" id="toggle-calendar-btn" style="padding:12px 24px; font-size:0.88rem; border-radius:50px; background:var(--secondary); color:#fff; display:flex; align-items:center; gap:8px; border:none; cursor:pointer; font-weight: 700;">
 <span></span> Toggle Bookings Calendar View
 </button>

 <div id="admin-calendar-wrapper" style="display:none; margin-top:16px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:24px; box-shadow:var(--shadow-sm);">
 <div class="cal-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; font-family:'Montserrat',sans-serif; font-weight:700; font-size:1.2rem; color:var(--secondary);">
 <button type="button" id="admin-prev-month" style="background:var(--primary-pale); border:none; width:36px; height:36px; border-radius:50%; cursor:pointer; font-weight:bold; color:var(--secondary); display: grid; place-items: center;">‹</button>
 <span id="admin-cal-label"></span>
 <button type="button" id="admin-next-month" style="background:var(--primary-pale); border:none; width:36px; height:36px; border-radius:50%; cursor:pointer; font-weight:bold; color:var(--secondary); display: grid; place-items: center;">›</button>
 </div>
 <div class="cal-weekdays" style="display:grid; grid-template-columns:repeat(7, 1fr); text-align:center; font-weight:700; font-size:0.8rem; color:var(--muted); text-transform:uppercase; margin-bottom:12px;">
 <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
 </div>
 <div class="cal-grid" id="admin-cal-grid" style="display:grid; grid-template-columns:repeat(7, 1fr); gap:8px;"></div>

 <div id="day-details-panel" style="margin-top:24px; display:none; border-top:1px solid var(--border); padding-top:20px;">
 <h3 style="font-family:'Montserrat',sans-serif; font-size:1.1rem; margin-bottom:12px; color:var(--secondary);" id="details-date-title">Bookings for Date</h3>
 <div id="details-bookings-list" style="display:flex; flex-direction:column; gap:12px;"></div>
 </div>
 </div>
 </div>

 <!-- FILTER TABS -->
 <div class="filter-tabs" style="padding:0 0 20px 0;gap:12px;border-bottom:1px solid var(--border);margin-bottom:24px;">
 <?php foreach (['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'] as $k=>$v): ?>
 <a href="dashboard.php?status=<?= $k ?>" class="tab <?= $filter===$k?'active':'' ?>"><?= $v ?></a>
 <?php endforeach; ?>
 </div>

 <!-- BOOKING CARDS -->
 <div id="bookings-container">
 <?php if ($bookings->rowCount() === 0): ?>
 <div class="empty-state">
 <div class="empty-icon"></div>
 <h3>No Bookings Found</h3>
 <p><?= $filter!=='all' ? "No $filter bookings." : "No bookings have been made yet." ?></p>
 </div>
 <?php else: ?>
 <?php while ($b = $bookings->fetch()):
 $sc = ['pending'=>['#92400E','#FEF3C7'], 'confirmed'=>['#065F46','#D1FAE5'], 'cancelled'=>['#991B1B','#FEE2E2'], 'completed'=>['#1E40AF','#DBEAFE']];
 [$sc_text,$sc_bg] = $sc[$b['status']] ?? ['#64748B','#F1F5F9'];
 ?>
 <div class="voyago-booking-card booking-item">
 <div style="width:56px;height:56px;border-radius:14px;background:var(--primary-pale);display:grid;place-items:center;font-size:1.6rem;margin-right:20px;flex-shrink:0;border:1px solid rgba(250,204,21,0.3);"><?= $b['pkg_icon'] ?></div>
 <div class="voyago-bc-info" style="flex:1;">
 <div class="voyago-bc-title">
 <h4><?= htmlspecialchars($b['event_name']) ?></h4>
 <span style="font-size:0.65rem;font-weight:700;padding:3px 10px;border-radius:20px;background:<?= $sc_bg ?>;color:<?= $sc_text ?>;"><?= ucfirst($b['status']) ?></span>
 </div>
 <div class="admin-card-meta">
 <strong><?= htmlspecialchars($b['fullname']) ?></strong> · <?= htmlspecialchars($b['email']) ?> · <?= htmlspecialchars($b['phone']) ?>
 </div>
 <div class="voyago-bc-meta" style="margin-top:10px;">
 <div class="voyago-bc-meta-item"><span>Event Date</span><span><?= date('M d, Y', strtotime($b['event_date'])) ?></span></div>
 <div class="voyago-bc-meta-item"><span>Package</span><span><?= $b['pkg_icon'].' '.htmlspecialchars($b['pkg_name']) ?></span></div>
 <div class="voyago-bc-meta-item"><span>Guests</span><span><?= number_format($b['guests']) ?></span></div>
 <div class="voyago-bc-meta-item"><span>Location</span><span style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($b['event_location']) ?></span></div>
 </div>
 </div>
 <div class="voyago-bc-actions" style="min-width:200px;">
 <div class="voyago-bc-price">TZS <?= number_format($b['total_amount']) ?></div>
 <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;justify-content:flex-end;">
 <?php if ($b['status'] === 'pending'): ?>
 <form method="POST"><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><button type="submit" name="action" value="accept" class="admin-action-btn btn-accept"> Accept</button></form>
 <form method="POST"><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><button type="submit" name="action" value="decline" class="admin-action-btn btn-decline"> Decline</button></form>
 <?php elseif ($b['status'] === 'confirmed'): ?>
 <form method="POST"><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><button type="submit" name="action" value="fulfill" class="admin-action-btn btn-fulfill"> Mark Fulfilled</button></form>
 <?php else: ?>
 <span style="font-size:0.75rem;color:var(--muted);font-style:italic;">No actions available</span>
 <?php endif; ?>
 </div>
 </div>
 </div>
 <?php endwhile; ?>
 <?php endif; ?>
 </div>
 </main>
 </div>
</div>
<script>
document.getElementById('search-bookings')?.addEventListener('input', function() {
 const q = this.value.toLowerCase();
 document.querySelectorAll('.booking-item').forEach(r => r.style.display = r.textContent.toLowerCase().includes(q) ? 'flex' : 'none');
});

const calendarEvents = <?= json_encode($cal_bookings) ?>;

// Toggle Calendar
document.getElementById('toggle-calendar-btn')?.addEventListener('click', function() {
 const wrapper = document.getElementById('admin-calendar-wrapper');
 if (wrapper.style.display === 'none') {
 wrapper.style.display = 'block';
 renderAdminCalendar(adminYear, adminMonth);
 } else {
 wrapper.style.display = 'none';
 document.getElementById('day-details-panel').style.display = 'none';
 }
});

let adminYear = new Date().getFullYear();
let adminMonth = new Date().getMonth();

function renderAdminCalendar(year, month) {
 const label = document.getElementById('admin-cal-label');
 const grid = document.getElementById('admin-cal-grid');
 if (!grid || !label) return;
 grid.innerHTML = '';

 const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
 label.textContent = monthNames[month] + " " + year;

 const firstDay = new Date(year, month, 1).getDay();
 const daysInMonth = new Date(year, month + 1, 0).getDate();

 // Fill leading empty cells
 for (let i = 0; i < firstDay; i++) {
 const emptyCell = document.createElement('div');
 emptyCell.className = 'admin-cal-day empty';
 grid.appendChild(emptyCell);
 }

 const today = new Date();
 const todayDate = today.getDate();
 const todayMonth = today.getMonth();
 const todayYear = today.getFullYear();

 // Fill days
 for (let day = 1; day <= daysInMonth; day++) {
 const dayCell = document.createElement('div');
 dayCell.className = 'admin-cal-day';
 if (day === todayDate && month === todayMonth && year === todayYear) {
 dayCell.classList.add('today');
 }

 // Day number
 const numSpan = document.createElement('span');
 numSpan.className = 'day-num';
 numSpan.textContent = day;
 dayCell.appendChild(numSpan);

 // Construct date string YYYY-MM-DD
 const yyyy = year;
 const mm = String(month + 1).padStart(2, '0');
 const dd = String(day).padStart(2, '0');
 const dateStr = `${yyyy}-${mm}-${dd}`;

 // Filter events for this day
 const dayEvents = calendarEvents.filter(e => e.event_date === dateStr);

 const eventsContainer = document.createElement('div');
 eventsContainer.className = 'events-container';

 dayEvents.forEach(e => {
 const dot = document.createElement('div');
 dot.className = `cal-event-dot ${e.status}`;
 dot.textContent = `${e.pkg_icon} ${e.event_name}`;
 eventsContainer.appendChild(dot);
 });

 dayCell.appendChild(eventsContainer);

 // Click listener to show details panel
 dayCell.addEventListener('click', function() {
 const detailsPanel = document.getElementById('day-details-panel');
 const listContainer = document.getElementById('details-bookings-list');
 const title = document.getElementById('details-date-title');

 const readableDate = new Date(year, month, day).toLocaleDateString('en-US', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'});
 title.textContent = `Bookings on ${readableDate}`;
 listContainer.innerHTML = '';

 if (dayEvents.length === 0) {
 listContainer.innerHTML = '<div style="font-size:0.9rem; color:var(--muted); font-style:italic; padding: 12px 0;">No bookings for this day. This day is free!</div>';
 } else {
 dayEvents.forEach(e => {
 const statusMap = {
 'pending': {text: 'Pending', color: '#92400E', bg: '#FEF3C7'},
 'confirmed': {text: 'Confirmed', color: '#065F46', bg: '#D1FAE5'},
 'completed': {text: 'Completed', color: '#1E40AF', bg: '#DBEAFE'}
 };
 const st = statusMap[e.status] || {text: e.status, color: '#64748B', bg: '#F1F5F9'};

 const bookingDiv = document.createElement('div');
 bookingDiv.style.background = 'var(--bg-alt)';
 bookingDiv.style.borderRadius = '12px';
 bookingDiv.style.padding = '16px';
 bookingDiv.style.border = '1px solid var(--border)';
 bookingDiv.style.display = 'flex';
 bookingDiv.style.justifyContent = 'space-between';
 bookingDiv.style.alignItems = 'center';
 bookingDiv.style.flexWrap = 'wrap';
 bookingDiv.style.gap = '12px';

 bookingDiv.innerHTML = `
 <div>
 <div style="display:flex; align-items:center; gap:8px;">
 <h4 style="font-weight:700; color:var(--secondary); font-size:0.95rem; margin:0;">${e.event_name}</h4>
 <span style="font-size:0.65rem; font-weight:700; padding:2px 8px; border-radius:12px; background:${st.bg}; color:${st.color};">${st.text}</span>
 </div>
 <div style="font-size:0.8rem; color:var(--muted); margin-top:4px;">
 <strong>Client:</strong> ${e.fullname} &middot; <strong>Location:</strong> ${e.event_location} &middot; <strong>Package:</strong> ${e.pkg_icon} ${e.pkg_name}
 </div>
 </div>
 <div style="font-family:'Montserrat',sans-serif; font-weight:900; color:var(--secondary); font-size:1.1rem;">
 TZS ${Number(e.total_amount).toLocaleString()}
 </div>
 `;
 listContainer.appendChild(bookingDiv);
 });
 }

 detailsPanel.style.display = 'block';
 });

 grid.appendChild(dayCell);
 }
}

document.getElementById('admin-prev-month')?.addEventListener('click', function() {
 adminMonth--;
 if (adminMonth < 0) {
 adminMonth = 11;
 adminYear--;
 }
 renderAdminCalendar(adminYear, adminMonth);
});

document.getElementById('admin-next-month')?.addEventListener('click', function() {
 adminMonth++;
 if (adminMonth > 11) {
 adminMonth = 0;
 adminYear++;
 }
 renderAdminCalendar(adminYear, adminMonth);
});
</script>
</body>
</html>
