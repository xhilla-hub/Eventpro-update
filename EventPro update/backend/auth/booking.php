<?php
session_start();
if (!isset($_SESSION['user_id'])) {
 header("Location: login.php");
 exit();
}
include '../config/database.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_phone= $_SESSION['user_phone'];
$initials = strtoupper(substr($user_name, 0, 1));

// Fetch packages
$packages = $pdo->query("SELECT * FROM packages ORDER BY price ASC");
$pkgs = [];
while ($p = $packages->fetch()) {
 $pkgs[] = $p;
}

// Fetch booked dates
$booked_stmt = $pdo->query("SELECT DISTINCT event_date FROM bookings WHERE status IN ('pending', 'confirmed', 'completed')");
$booked_dates = [];
while ($row = $booked_stmt->fetch()) {
 $booked_dates[] = $row['event_date'];
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $pkg_id = (int) $_POST['package_id'];
 $evt_name = trim($_POST['event_name']);
 $evt_type = trim($_POST['event_type']);
 $evt_date = $_POST['event_date'];
 $evt_loc = trim($_POST['event_location']);
 $guests = (int) $_POST['guests'];
 $notes = trim($_POST['special_notes'] ?? '');

 if (!$pkg_id || empty($evt_name) || empty($evt_date) || empty($evt_loc) || $guests < 1) {
 $error = "Please fill in all required fields and select a package.";
 } else {
 // Get package price
 $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = :id");
 $stmt->execute(['id' => $pkg_id]);
 $pkg_row = $stmt->fetch();
 $amount = $pkg_row['price'];

 $sql = "INSERT INTO bookings (user_id, package_id, event_name, event_type, event_date, event_location, guests, special_notes, total_amount, status)
 VALUES (:user_id, :pkg_id, :evt_name, :evt_type, :evt_date, :evt_loc, :guests, :notes, :amount, 'pending')";
 $stmt = $pdo->prepare($sql);

 if ($stmt->execute([
 'user_id' => $user_id, 'pkg_id' => $pkg_id, 'evt_name' => $evt_name, 
 'evt_type' => $evt_type, 'evt_date' => $evt_date, 'evt_loc' => $evt_loc, 
 'guests' => $guests, 'notes' => $notes, 'amount' => $amount
 ])) {
 $booking_id = $pdo->lastInsertId();
 header("Location: payment.php?booking_id=$booking_id");
 exit();
 } else {
 $error = "Booking failed. Please try again.";
 }
 }
}

$selected_pkg = isset($_GET['pkg']) ? (int)$_GET['pkg'] : 2; // default Pro
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>Book an Event – EventPro</title>
 <link rel="stylesheet" href="../../css/dashboard.css"/>
 <style>
 .calendar-field-container {
 display: flex;
 flex-direction: column;
 }
 .booking-calendar-wrapper {
 background: var(--surface);
 border: 1px solid var(--border);
 border-radius: var(--radius-sm);
 padding: 20px;
 max-width: 100%;
 margin-top: 10px;
 box-shadow: var(--shadow-sm);
 }
 .cal-header {
 display: flex;
 justify-content: space-between;
 align-items: center;
 margin-bottom: 15px;
 font-family: 'Montserrat', sans-serif;
 font-weight: 700;
 color: var(--secondary);
 }
 .cal-header button {
 background: var(--primary-pale);
 border: none;
 width: 32px; height: 32px;
 border-radius: 50%;
 cursor: pointer;
 font-weight: bold;
 color: var(--secondary);
 transition: all 0.2s;
 }
 .cal-header button:hover {
 background: var(--primary);
 }
 .cal-weekdays {
 display: grid;
 grid-template-columns: repeat(7, 1fr);
 text-align: center;
 font-weight: 700;
 font-size: 0.75rem;
 color: var(--muted);
 text-transform: uppercase;
 margin-bottom: 10px;
 }
 .cal-grid {
 display: grid;
 grid-template-columns: repeat(7, 1fr);
 gap: 6px;
 }
 .cal-day {
 aspect-ratio: 1;
 display: grid;
 place-items: center;
 border-radius: 50%;
 font-size: 0.85rem;
 font-weight: 600;
 cursor: pointer;
 transition: all 0.2s;
 color: var(--secondary);
 border: 1px solid transparent;
 }
 .cal-day.empty {
 cursor: default;
 opacity: 0;
 }
 .cal-day.disabled {
 color: #CBD5E1;
 cursor: not-allowed;
 }
 .cal-day.available {
 background: #F8FAFC;
 }
 .cal-day.available:hover {
 background: var(--primary-pale);
 border-color: var(--primary);
 transform: scale(1.05);
 }
 .cal-day.reserved {
 background: #FEE2E2;
 color: #EF4444;
 cursor: not-allowed;
 position: relative;
 }
 .cal-day.reserved:hover::after {
 content: 'Reserved';
 position: absolute;
 bottom: -20px;
 left: 50%;
 transform: translateX(-50%);
 background: var(--secondary);
 color: #fff;
 font-size: 0.6rem;
 padding: 2px 6px;
 border-radius: 4px;
 white-space: nowrap;
 z-index: 10;
 }
 .cal-day.selected {
 background: var(--primary) !important;
 color: var(--secondary) !important;
 box-shadow: 0 4px 10px rgba(250,204,21,0.4);
 }
 .cal-legend {
 display: flex;
 gap: 20px;
 margin-top: 15px;
 font-size: 0.75rem;
 justify-content: center;
 }
 .legend-item {
 display: flex;
 align-items: center;
 gap: 6px;
 color: var(--muted);
 font-weight: 600;
 }
 .legend-dot {
 width: 10px; height: 10px;
 border-radius: 50%;
 }
 .legend-dot.free { background: #F8FAFC; border: 1px solid var(--border); }
 .legend-dot.busy { background: #FEE2E2; }
 </style>
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
 <a href="booking.php" class="nav-item active" id="nav-booking"><span class="icon"></span> Book an Event</a>
 <a href="my_bookings.php" class="nav-item" id="nav-mybookings"><span class="icon"></span> My Booking</a>
 <div class="nav-section-label">Marketplace</div>
 <a href="vendors.php" class="nav-item" id="nav-vendors"><span class="icon"></span> Browse Vendors</a>
 <a href="packages.php" class="nav-item" id="nav-packages"><span class="icon"></span> Packages</a>
 <div class="nav-section-label">Account</div>
 <a href="profile.php" class="nav-item" id="nav-profile"><span class="icon"></span> My Profile</a>
 <a href="payment_history.php" class="nav-item" id="nav-payments"><span class="icon"></span> Payments</a>
 </nav>
 </aside>

 <!-- MAIN AREA -->
 <div class="main-area">
 <header class="topbar" style="justify-content: space-between; border-bottom: none; padding-top: 20px;">
 <div class="topbar-left">
 <h2>Book an Event</h2>
 <p>Select a package and fill in your event details</p>
 </div>
 <div class="topbar-actions" style="gap: 20px;">
 <button class="topbar-btn" style="border:none; background: var(--primary-pale); color: var(--primary);"><span class="notif-dot"></span></button>
 <button class="topbar-btn" style="border:none; background: var(--primary-pale); color: var(--primary);"></button>
 <div class="user-pill" style="background:none; padding:0; gap: 12px; border:none;">
 <div class="user-avatar" style="width:40px; height:40px; font-size:1.1rem;"><?= $initials ?></div>
 <div class="user-info" style="line-height:1.2;">
 <div class="user-name" style="font-weight:700; font-size:0.9rem;"><?= htmlspecialchars($user_name) ?></div>
 <div class="user-role" style="font-size:0.75rem; color:var(--muted);"><?= htmlspecialchars($_SESSION['user_email'] ?? 'user@eventpro.com') ?></div>
 </div>
 </div>
 <a href="logout.php" style="color:var(--muted); font-size:1.2rem; margin-left:8px;" title="Logout">⏻</a>
 </div>
 </header>

 <main class="page-content">
 <div class="breadcrumb">
 <a href="dashboard.php">Dashboard</a>
 <span>/</span>
 <span>Book an Event</span>
 </div>

 <?php if ($error): ?>
 <div class="alert-box error"> <?= htmlspecialchars($error) ?></div>
 <?php endif; ?>

 <form method="POST" action="" id="booking-form">

 <!-- STEP 1: PICK PACKAGE -->
 <div class="page-header">
 <h1>Step 1: Choose Your Package</h1>
 <p>Select the package that fits your event size and requirements.</p>
 </div>

 <div class="packages-row" id="packages-row">
 <?php foreach ($pkgs as $p):
 $features = explode('|', $p['features']);
 $is_selected = ($p['id'] == $selected_pkg);
 $is_popular = ($p['name'] === 'Pro');
 
 $max_guests = 10000;
 if (preg_match('/up to (\d+) guests/', $p['description'], $m)) {
    $max_guests = $m[1];
 } elseif (preg_match('/(\d+)\+ attendees/', $p['description'], $m)) {
    $max_guests = 10000;
 }
 ?>
 <div class="pkg-select-card <?= $is_popular ? 'popular' : '' ?> <?= $is_selected ? 'selected' : '' ?>"
 id="pkg-card-<?= $p['id'] ?>"
 onclick="selectPackage(<?= $p['id'] ?>, <?= $p['price'] ?>, '<?= addslashes($p['name']) ?>', '<?= addslashes($p['description']) ?>', <?= $max_guests ?>)">
 <?php if ($is_popular): ?>
 <div class="pkg-popular-tag">MOST POPULAR</div>
 <?php endif; ?>
 <input type="radio" class="pkg-radio" name="package_id" value="<?= $p['id'] ?>"
 id="pkg-radio-<?= $p['id'] ?>" <?= $is_selected ? 'checked' : '' ?> required/>
 <div class="pkg-icon"><?= $p['icon'] ?></div>
 <div class="pkg-name"><?= htmlspecialchars($p['name']) ?></div>
 <div class="pkg-price">TZS <?= number_format($p['price']) ?>
 <span>/ event</span>
 </div>
 <div class="pkg-desc"><?= htmlspecialchars($p['description']) ?></div>
 <ul class="pkg-features-list">
 <?php foreach ($features as $f): ?>
 <li><?= htmlspecialchars(trim($f)) ?></li>
 <?php endforeach; ?>
 </ul>
 </div>
 <?php endforeach; ?>
 </div>

 <!-- STEP 2: EVENT DETAILS + SUMMARY -->
 <div class="page-header">
 <h1>Step 2: Event Details</h1>
 <p>Tell us about your event so we can connect the right vendors.</p>
 </div>

 <div class="three-col">
 <!-- FORM -->
 <div>
 <div class="booking-form-card">
 <h3> Event Information</h3>
 <div class="form-grid">
 <div class="form-field">
 <label for="event_name">Event Name *</label>
 <input type="text" id="event_name" name="event_name" placeholder="Annual Tech Conference 2025"
 value="<?= htmlspecialchars($_POST['event_name'] ?? '') ?>" required/>
 </div>
 <div class="form-field">
 <label for="event_type">Event Type *</label>
 <select id="event_type" name="event_type" required>
 <option value="">Select type...</option>
 <option value="Conference" <?= ($_POST['event_type']??'')=='Conference'?'selected':'' ?>>Conference</option>
 <option value="Concert" <?= ($_POST['event_type']??'')=='Concert'?'selected':'' ?>>Concert</option>
 <option value="Corporate" <?= ($_POST['event_type']??'')=='Corporate'?'selected':'' ?>>Corporate Event</option>
 <option value="Wedding" <?= ($_POST['event_type']??'')=='Wedding'?'selected':'' ?>>Wedding / Party</option>
 <option value="Workshop" <?= ($_POST['event_type']??'')=='Workshop'?'selected':'' ?>>Workshop / Training</option>
 <option value="Other" <?= ($_POST['event_type']??'')=='Other'?'selected':'' ?>>Other</option>
 </select>
 </div>
 <div class="form-field full calendar-field-container">
 <label>Select Event Date *</label>
 <input type="hidden" id="event_date" name="event_date" value="<?= htmlspecialchars($_POST['event_date'] ?? '') ?>" required/>
 <div class="booking-calendar-wrapper">
 <div class="cal-header">
 <button type="button" id="prev-month-btn">‹</button>
 <span id="cal-month-year-label"></span>
 <button type="button" id="next-month-btn">›</button>
 </div>
 <div class="cal-weekdays">
 <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
 </div>
 <div class="cal-grid" id="cal-grid"></div>
 <div class="cal-legend">
 <span class="legend-item"><span class="legend-dot free"></span> Available</span>
 <span class="legend-item"><span class="legend-dot busy"></span> Reserved (Booked)</span>
 </div>
 </div>
 </div>
 <div class="form-field">
 <label for="guests">Expected Guests *</label>
 <input type="number" id="guests" name="guests" placeholder="200" min="1"
 value="<?= htmlspecialchars($_POST['guests'] ?? '') ?>" required/>
 </div>
 <div class="form-field full">
 <label for="event_location">Event Location *</label>
 <input type="text" id="event_location" name="event_location"
 placeholder="Julius Nyerere International Convention Centre, Dar es Salaam"
 value="<?= htmlspecialchars($_POST['event_location'] ?? '') ?>" required/>
 </div>
 <div class="form-field full">
 <label for="special_notes">Special Requirements / Notes</label>
 <textarea id="special_notes" name="special_notes"
 placeholder="Any special setup requirements, dietary needs, AV specs..."><?= htmlspecialchars($_POST['special_notes'] ?? '') ?></textarea>
 </div>
 </div>
 </div>
 </div>

 <!-- ORDER SUMMARY -->
 <div>
 <div class="order-summary">
 <div class="order-summary-header">
 <h3> Order Summary</h3>
 </div>
 <div class="summary-body">
 <div class="summary-pkg-name" id="sum-pkg-name">
 <?php
 $def = array_filter($pkgs, fn($p) => $p['id'] == $selected_pkg);
 $def = reset($def);
 echo $def ? htmlspecialchars($def['name']) : 'Pro';
 ?>
 </div>
 <div class="summary-pkg-desc" id="sum-pkg-desc">
 <?= $def ? htmlspecialchars($def['description']) : '' ?>
 </div>
 <div class="summary-line">
 <span>Package</span>
 <span class="val" id="sum-pkg-label">
 <?= $def ? htmlspecialchars($def['name']) : 'Pro' ?> Package
 </span>
 </div>
 <div class="summary-line">
 <span>Event Date</span>
 <span class="val" id="sum-date">—</span>
 </div>
 <div class="summary-line">
 <span>Guests</span>
 <span class="val" id="sum-guests">—</span>
 </div>
 <div class="summary-total">
 <span class="lbl">Total</span>
 <span class="amount" id="sum-total">
 TZS <?= $def ? number_format($def['price']) : '0' ?>
 </span>
 </div>
 <button type="submit" class="btn-book" id="btn-book">
 Proceed to Payment →
 </button>
 </div>
 </div>
 </div>
 </div>

 </form>
 </main>
 </div>
</div>

<script>
const pkgPrices = <?= json_encode(array_column($pkgs, 'price', 'id')) ?>;
const bookedDates = <?= json_encode($booked_dates) ?>;

// Build calendar
let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth();

function renderCalendar(year, month) {
 const label = document.getElementById('cal-month-year-label');
 const grid = document.getElementById('cal-grid');
 if (!grid) return;
 grid.innerHTML = '';

 const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
 label.textContent = monthNames[month] + " " + year;

 const firstDay = new Date(year, month, 1).getDay();
 const daysInMonth = new Date(year, month + 1, 0).getDate();

 // Fill leading empty cells
 for (let i = 0; i < firstDay; i++) {
 const emptyCell = document.createElement('div');
 emptyCell.className = 'cal-day empty';
 grid.appendChild(emptyCell);
 }

 const today = new Date();
 today.setHours(0,0,0,0);

 const selectedDateStr = document.getElementById('event_date').value;

 // Fill month days
 for (let day = 1; day <= daysInMonth; day++) {
 const dayCell = document.createElement('div');
 dayCell.className = 'cal-day';
 dayCell.textContent = day;

 // Construct date string YYYY-MM-DD
 const yyyy = year;
 const mm = String(month + 1).padStart(2, '0');
 const dd = String(day).padStart(2, '0');
 const dateStr = `${yyyy}-${mm}-${dd}`;
 const cellDate = new Date(year, month, day);
 cellDate.setHours(0,0,0,0);

 if (cellDate <= today) {
 dayCell.classList.add('disabled');
 } else if (bookedDates.includes(dateStr)) {
 dayCell.classList.add('reserved');
 } else {
 dayCell.classList.add('available');
 if (dateStr === selectedDateStr) {
 dayCell.classList.add('selected');
 }

 dayCell.addEventListener('click', function() {
 document.querySelectorAll('.cal-day.selected').forEach(c => c.classList.remove('selected'));
 dayCell.classList.add('selected');

 const dateInput = document.getElementById('event_date');
 dateInput.value = dateStr;
 dateInput.dispatchEvent(new Event('change'));
 });
 }

 grid.appendChild(dayCell);
 }
}

document.getElementById('prev-month-btn')?.addEventListener('click', function() {
 currentMonth--;
 if (currentMonth < 0) {
 currentMonth = 11;
 currentYear--;
 }
 renderCalendar(currentYear, currentMonth);
});

document.getElementById('next-month-btn')?.addEventListener('click', function() {
 currentMonth++;
 if (currentMonth > 11) {
 currentMonth = 0;
 currentYear++;
 }
 renderCalendar(currentYear, currentMonth);
});

 function selectPackage(id, price, name, desc, maxGuests) {
 document.querySelectorAll('.pkg-select-card').forEach(c => c.classList.remove('selected'));
 document.getElementById('pkg-card-' + id).classList.add('selected');
 document.getElementById('pkg-radio-' + id).checked = true;

 document.getElementById('sum-pkg-name').textContent = name;
 document.getElementById('sum-pkg-desc').textContent = desc;
 document.getElementById('sum-pkg-label').textContent = name + ' Package';
 document.getElementById('sum-total').textContent = 'TZS ' + Number(price).toLocaleString();
 
 const guestsInput = document.getElementById('guests');
 if (maxGuests) {
    guestsInput.max = maxGuests;
    if (guestsInput.value && parseInt(guestsInput.value) > maxGuests) {
        guestsInput.value = maxGuests;
        document.getElementById('sum-guests').textContent = maxGuests + ' guests';
    }
 }
 }

 // Live update date & guests in summary
 document.getElementById('event_date').addEventListener('change', function() {
 if (this.value) {
 const d = new Date(this.value);
 document.getElementById('sum-date').textContent = d.toLocaleDateString('en-TZ', {day:'numeric', month:'short', year:'numeric'});
 } else {
 document.getElementById('sum-date').textContent = '—';
 }
});
document.getElementById('guests').addEventListener('input', function() {
 document.getElementById('sum-guests').textContent = this.value ? this.value + ' guests' : '—';
});

// Initialize calendar
document.addEventListener("DOMContentLoaded", () => {
 const initDateStr = document.getElementById('event_date').value;
 if (initDateStr) {
 const initDate = new Date(initDateStr);
 currentYear = initDate.getFullYear();
 currentMonth = initDate.getMonth();

 // update summary
 document.getElementById('sum-date').textContent = initDate.toLocaleDateString('en-TZ', {day:'numeric', month:'short', year:'numeric'});
 }
 renderCalendar(currentYear, currentMonth);
 // trigger max guest limit for initial load
 const selectedRadio = document.querySelector('input[name="package_id"]:checked');
 if (selectedRadio) {
    selectedRadio.closest('.pkg-select-card').click();
 }
});

function toggleSidebar() {
 document.getElementById('sidebar').classList.toggle('open');
}
</script>
</body>
</html>
