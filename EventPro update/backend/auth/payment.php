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

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
if (!$booking_id) {
 header("Location: booking.php");
 exit();
}

// Fetch booking
$bk = $pdo->query("SELECT b.*, p.name AS pkg_name, p.icon AS pkg_icon
 FROM bookings b JOIN packages p ON b.package_id=p.id
 WHERE b.id=$booking_id AND b.user_id=$user_id")->fetch();

if (!$bk) {
 header("Location: my_bookings.php");
 exit();
}

// Check existing payment
$existing = $pdo->query("SELECT * FROM payments WHERE booking_id=$booking_id AND status='completed'")->fetch();

$error = "";
$success = "";
$paid = (bool)$existing;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$paid) {
 $method = $_POST['method'] ?? 'mobile';

 if ($method === 'mobile') {
 $phone = trim($_POST['phone'] ?? '');
 $provider = $_POST['provider'] ?? 'mpesa';

 // Format phone: 07xx or 06xx → 255xx
 $phone_fmt = $phone;
 if (preg_match('/^(07|06)\d{8}$/', $phone)) {
 $phone_fmt = '255' . substr($phone, 1);
 }

 // Unique order reference for Mongike (avoids prisma unique constraint error)
 $order_ref = 'EP-' . $booking_id . '-' . time() . '-' . rand(100, 999);

 // Dynamic Webhook URL construction
 $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
 $webhook_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/webhook.php';

 // Mongike requires public HTTPS webhook URL. If local, use placeholder to allow STK push.
 if (strpos($webhook_url, 'localhost') !== false || strpos($webhook_url, '127.0.0.1') !== false || $protocol === 'http://') {
 $webhook_url = 'https://eventpro.co.tz/backend/auth/webhook.php';
 }

 // Insert payment record as pending
 $stmt = $pdo->prepare("INSERT INTO payments (booking_id, user_id, amount, phone, method, status, checkout_id) VALUES (:booking_id, :user_id, :amount, :phone, :method, 'pending', :checkout_id)");
 $stmt->execute(['booking_id' => $booking_id, 'user_id' => $user_id, 'amount' => $bk['total_amount'], 'phone' => $phone_fmt, 'method' => $provider, 'checkout_id' => $order_ref]);
 $payment_id = $pdo->lastInsertId();

 // Call Mongike API
 $mongike_result = sendMongikePayment($phone_fmt, $bk['total_amount'], $order_ref, $user_name, $_SESSION['user_email'] ?? 'user@eventpro.com', $provider, $webhook_url);
 $mongike_data = json_decode($mongike_result, true);

 if (isset($mongike_data['order_id']) || (isset($mongike_data['status']) && in_array(strtolower($mongike_data['status']), ['success', 'pending', 'initiated']))) {
 $success = " Mobile Payment prompt sent to $phone via " . strtoupper($provider) . ". Please check your phone.";
 } else {
 // Real Money Network Failure
 $error = "Failed to initiate payment on the real money network. Error: " . ($mongike_data['message'] ?? 'Please try again.');
 }
 } elseif ($method === 'card') {
 $card_number = trim($_POST['card_number'] ?? '');
 $card_name = trim($_POST['card_name'] ?? '');
 // Insert payment record as completed (Demo)
 $stmt = $pdo->prepare("INSERT INTO payments (booking_id, user_id, amount, phone, method, status, mpesa_code, paid_at) VALUES (:booking_id, :user_id, :amount, :phone, :method, 'completed', :ref, CURRENT_TIMESTAMP)");
 $stmt->execute(['booking_id' => $booking_id, 'user_id' => $user_id, 'amount' => $bk['total_amount'], 'phone' => 'CARD', 'method' => 'card', 'ref' => 'CARD_'.rand(1000,9999)]);
 
 $stmt2 = $pdo->prepare("UPDATE bookings SET status='confirmed' WHERE id=:id");
 $stmt2->execute(['id' => $booking_id]);

 $paid = true;
 $success = " Card payment successful. Your booking is now confirmed!";
 }
}

function sendMongikePayment($phone, $amount, $ref, $buyerName, $buyerEmail, $provider, $webhookUrl) {
 $apiKey = MONGIKE_API_KEY;
 $url = "https://mongike.com/api/v1/payments/mobile-money/tanzania";

 $network = strtoupper($provider);
 if ($network === 'MPESA') $network = 'VODACOM';
 if ($network === 'TIGOPESA') $network = 'TIGO';
 if ($network === 'AIRTELMONEY') $network = 'AIRTEL';
 if ($network === 'HALOPESA') $network = 'HALOTEL';

 $payload = [
 'order_id' => (string)$ref,
 'amount' => (float)$amount,
 'buyer_phone' => preg_replace('/^\+/', '', $phone),
 'buyer_name' => $buyerName,
 'buyer_email' => $buyerEmail,
 'network' => $network,
 'fee_payer' => 'MERCHANT',
 'webhook_url' => $webhookUrl
 ];

 $ch = curl_init($url);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_POST, true);
 curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
 curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
 curl_setopt($ch, CURLOPT_TIMEOUT, 30);
 curl_setopt($ch, CURLOPT_HTTPHEADER, [
 'Content-Type: application/json',
 'x-api-key: ' . $apiKey
 ]);

 $response = curl_exec($ch);
 if (curl_errno($ch)) return json_encode(['error' => curl_error($ch)]);
 curl_close($ch);
 return $response ?: json_encode(['error' => 'No response']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>Payment – EventPro</title>
 <link rel="stylesheet" href="../../css/dashboard.css"/>
 <style>
 .stepper-container {
 display: flex;
 align-items: center;
 justify-content: space-between;
 max-width: 400px;
 margin: 0 auto 40px auto;
 }
 .step {
 display: flex;
 flex-direction: column;
 align-items: center;
 gap: 8px;
 position: relative;
 z-index: 2;
 }
 .step-circle {
 width: 32px;
 height: 32px;
 border-radius: 50%;
 background: #fff;
 border: 2px solid #e2e8f0;
 display: flex;
 align-items: center;
 justify-content: center;
 font-size: 0.9rem;
 font-weight: bold;
 color: #e2e8f0;
 }
 .step.completed .step-circle {
 background: #ea580c;
 border-color: #ea580c;
 color: #fff;
 }
 .step.active .step-circle {
 border-color: #ea580c;
 border-width: 6px;
 }
 .step-label {
 font-size: 0.8rem;
 font-weight: 600;
 color: #94a3b8;
 }
 .step.completed .step-label, .step.active .step-label {
 color: #334155;
 }
 .step-line {
 flex: 1;
 height: 2px;
 background: #e2e8f0;
 margin: 0 10px;
 position: relative;
 top: -12px;
 z-index: 1;
 }
 .step-line.active {
 background: #ea580c;
 }
 
 .payment-warning {
 background: #fff7ed;
 border: 1px solid #ffedd5;
 color: #c2410c;
 padding: 16px;
 border-radius: 12px;
 display: flex;
 align-items: center;
 gap: 12px;
 margin-bottom: 24px;
 font-size: 0.85rem;
 font-weight: 500;
 }
 .payment-warning .warning-icon {
 width: 24px; height: 24px;
 border-radius: 50%; border: 1px solid #c2410c;
 display: flex; align-items: center; justify-content: center;
 font-weight: bold;
 }
 
 .pm-grid {
 display: grid;
 grid-template-columns: repeat(3, 1fr);
 gap: 16px;
 margin-bottom: 24px;
 }
 .pm-box {
 background: #fff;
 border: 1px solid #e2e8f0;
 border-radius: 12px;
 height: 80px;
 display: flex;
 align-items: center;
 justify-content: center;
 cursor: pointer;
 transition: all 0.2s;
 font-weight: 700;
 color: #334155;
 }
 .pm-box:hover {
 border-color: #cbd5e1;
 }
 .pm-box.active {
 border: 2px solid #ea580c;
 box-shadow: 0 4px 12px rgba(234,88,12,0.15);
 }
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
 <h2>Payment</h2>
 <p>Secure your booking</p>
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
 <div class="stepper-container">
 <div class="step completed">
 <div class="step-circle">✓</div>
 <div class="step-label">Booking</div>
 </div>
 <div class="step-line active"></div>
 <div class="step active">
 <div class="step-circle"></div>
 <div class="step-label">Payment</div>
 </div>
 <div class="step-line"></div>
 <div class="step">
 <div class="step-circle"></div>
 <div class="step-label">Finish</div>
 </div>
 </div>

 <?php if ($success): ?>
 <div class="alert-box success"> <?= htmlspecialchars($success) ?></div>
 <?php endif; ?>
 <?php if ($error): ?>
 <div class="alert-box error"> <?= htmlspecialchars($error) ?></div>
 <?php endif; ?>

 <?php if ($paid): ?>
 <!-- PAYMENT SUCCESS STATE -->
 <div class="section-card" style="max-width:600px;margin:0 auto;text-align:center;padding:48px">
 <div style="font-size:4rem;margin-bottom:16px"></div>
 <h2 style="font-family:'Barlow Condensed',sans-serif;font-weight:900;font-size:2rem;text-transform:uppercase;color:var(--dark);margin-bottom:8px">
 Booking Confirmed!
 </h2>
 <p style="color:var(--muted);font-size:0.9rem;margin-bottom:28px">
 Your event <strong><?= htmlspecialchars($bk['event_name']) ?></strong> is confirmed.
 We'll contact your vendors and begin coordination.
 </p>
 <div style="background:var(--red-pale);border-radius:10px;padding:20px;margin-bottom:28px;text-align:left">
 <div style="font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--muted);margin-bottom:12px">Booking Details</div>
 <div style="font-size:0.9rem;color:var(--dark);line-height:2">
 <strong>Event:</strong> <?= htmlspecialchars($bk['event_name']) ?><br/>
 <strong>Package:</strong> <?= htmlspecialchars($bk['pkg_icon'].' '.$bk['pkg_name']) ?><br/>
 <strong>Date:</strong> <?= date('D, d M Y', strtotime($bk['event_date'])) ?><br/>
 <strong>Location:</strong> <?= htmlspecialchars($bk['event_location']) ?><br/>
 <strong>Amount Paid:</strong> TZS <?= number_format($bk['total_amount']) ?>
 </div>
 </div>
 <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
 <a href="my_bookings.php" class="btn-book" style="width:auto;padding:12px 28px">View My Bookings</a>
 <a href="booking.php" style="padding:12px 28px;border-radius:10px;border:1.5px solid var(--border);font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:1rem;text-transform:uppercase;letter-spacing:0.06em;color:var(--dark)">Book Another</a>
 </div>
 </div>

 <?php else: ?>
 <!-- PAYMENT FORM -->
 <div class="three-col">
 <div class="payment-wrapper">

 <!-- Method Selector -->
 <div class="payment-warning">
 <span class="warning-icon">!</span> Please complete payment within 18 minutes, or else your booking will be cancelled.
 </div>
 
 <div class="pm-grid">
 <div class="pm-box active" id="method-mobile" onclick="switchMethod('mobile')">
 Mobile Money
 </div>
 <div class="pm-box" id="method-card" onclick="switchMethod('card')">
 Bank Card
 </div>
 <div class="pm-box" id="method-cash" onclick="switchMethod('cash')">
 Cash
 </div>
 </div>

 <!-- MOBILE PAYMENT FORM -->
 <div id="mobile-section">
 <form method="POST" action="" id="payment-form">
 <input type="hidden" name="method" value="mobile" />
 <div class="mpesa-form">
 <div class="mpesa-logo" style="justify-content: flex-start; margin-bottom: 20px;">
 <div class="m-icon" style="background: #334155;">📱</div>
 <div class="m-text" style="color: #334155;">Mobile Payment</div>
 </div>

 <div class="mpesa-steps">
 <p>Select your provider and enter your phone number:</p>
 </div>

 <div class="form-field" style="margin-bottom: 16px;">
 <label>Select Provider *</label>
 <select name="provider" required style="width:100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
 <option value="">Choose...</option>
 <option value="mpesa">M-Pesa</option>
 <option value="tigopesa">Tigo Pesa</option>
 <option value="airtelmoney">Airtel Money</option>
 <option value="halopesa">HaloPesa</option>
 </select>
 </div>

 <div class="form-field">
 <label for="phone">Phone Number *</label>
 <input type="tel" id="phone" name="phone"
 placeholder="0712 345 678"
 value="<?= htmlspecialchars($user_phone ?? '') ?>"
 pattern="^(07|06)\d{8}$"
 title="Enter a valid Tanzanian number (07xx or 06xx)"
 required/>
 </div>

 <div style="background:var(--bg);border-radius:10px;padding:16px;margin-top:16px;display:flex;justify-content:space-between;align-items:center">
 <div>
 <div style="font-size:0.75rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted)">Amount Due</div>
 <div style="font-family:'Barlow Condensed',sans-serif;font-weight:900;font-size:2.2rem;color:var(--red)">
 TZS <?= number_format($bk['total_amount']) ?>
 </div>
 </div>
 <div style="text-align:right">
 <div style="font-size:0.75rem;color:var(--muted)">Package</div>
 <div style="font-weight:700;font-size:0.95rem;color:var(--dark)"><?= htmlspecialchars($bk['pkg_icon'].' '.$bk['pkg_name']) ?></div>
 </div>
 </div>

 <div style="text-align:center; font-size:0.75rem; color:#94a3b8; margin: 20px 0;">
 By clicking PROCEED TO PAYMENT, you are agreeing to EventPro's Terms and Conditions & Privacy policy.
 </div>

 <button type="submit" class="btn-pay" id="btn-pay" style="background:#ea580c; border-radius:24px;">
 <span id="pay-label"> Make payment</span>
 <div class="spinner" id="pay-spinner"></div>
 </button>
 </div>
 </form>
 </div>

 <!-- CARD PAYMENT FORM -->
 <div id="card-section" style="display:none">
 <form method="POST" action="" id="payment-form-card">
 <input type="hidden" name="method" value="card" />
 <div class="mpesa-form">
 <h3 style="font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.3rem;text-transform:uppercase;margin-bottom:16px;color:var(--dark)">Bank Card</h3>
 
 <div class="form-field" style="margin-bottom: 16px;">
 <label>Cardholder Name *</label>
 <input type="text" name="card_name" placeholder="John Doe" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px;" />
 </div>
 <div class="form-field" style="margin-bottom: 16px;">
 <label>Card Number *</label>
 <input type="text" name="card_number" placeholder="0000 0000 0000 0000" pattern="\d{16}" title="16 digit card number" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px;" />
 </div>
 <div style="display:flex; gap: 16px; margin-bottom: 24px;">
 <div class="form-field" style="flex:1;">
 <label>Expiry Date *</label>
 <input type="text" name="card_expiry" placeholder="MM/YY" pattern="\d\d/\d\d" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px;" />
 </div>
 <div class="form-field" style="flex:1;">
 <label>CVV *</label>
 <input type="text" name="card_cvv" placeholder="123" pattern="\d{3,4}" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px;" />
 </div>
 </div>

 <button type="submit" class="btn-pay" style="background:#ea580c; border-radius:24px;">
 <span>Pay TZS <?= number_format($bk['total_amount']) ?></span>
 </button>
 </div>
 </form>
 </div>

 <!-- CASH PLACEHOLDER -->
 <div id="cash-section" style="display:none">
 <div class="mpesa-form">
 <h3 style="font-family:'Barlow Condensed',sans-serif;font-weight:800;font-size:1.3rem;text-transform:uppercase;margin-bottom:12px;color:var(--dark)"> Cash Payment</h3>
 <div class="mpesa-steps">
 <p>
 <strong>Cash on Event:</strong><br/>
 Your booking will be reserved as <strong>Pending</strong>. Pay cash directly to the EventPro coordinator at the event venue.<br/><br/>
 <strong>Amount Due:</strong> TZS <?= number_format($bk['total_amount']) ?>
 </p>
 </div>
 <a href="my_bookings.php" class="btn-pay" style="display:block;text-align:center;text-decoration:none;background:var(--red)">
 Confirm Cash Booking
 </a>
 </div>
 </div>
 </div>

 <!-- ORDER SUMMARY (right) -->
 <div>
 <div class="order-summary">
 <div class="order-summary-header">
 <h3> Booking Summary</h3>
 </div>
 <div class="summary-body">
 <div class="summary-pkg-name"><?= htmlspecialchars($bk['pkg_name']) ?></div>
 <div class="summary-pkg-desc"><?= htmlspecialchars($bk['event_name']) ?></div>
 <div class="summary-line">
 <span>Event Type</span>
 <span class="val"><?= htmlspecialchars($bk['event_type']) ?></span>
 </div>
 <div class="summary-line">
 <span>Date</span>
 <span class="val"><?= date('d M Y', strtotime($bk['event_date'])) ?></span>
 </div>
 <div class="summary-line">
 <span>Location</span>
 <span class="val" style="font-size:0.82rem;text-align:right;max-width:120px"><?= htmlspecialchars($bk['event_location']) ?></span>
 </div>
 <div class="summary-line">
 <span>Guests</span>
 <span class="val"><?= number_format($bk['guests']) ?></span>
 </div>
 <div class="summary-line">
 <span>Package</span>
 <span class="val"><?= htmlspecialchars($bk['pkg_icon'].' '.$bk['pkg_name']) ?></span>
 </div>
 <div class="summary-total">
 <span class="lbl">Total</span>
 <span class="amount">TZS <?= number_format($bk['total_amount']) ?></span>
 </div>
 <div style="margin-top:16px;padding:12px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0">
 <p style="font-size:0.78rem;color:#166534">
 Payments are secured via M-Pesa. Your booking details are encrypted.
 </p>
 </div>
 </div>
 </div>
 </div>
 </div>
 <?php endif; ?>

 </main>
 </div>
</div>

<script>
 function switchMethod(m) {
 ['mobile','card','cash'].forEach(x => {
 document.getElementById(x + '-section').style.display = (x === m) ? 'block' : 'none';
 document.getElementById('method-' + x).classList.toggle('active', x === m);
 });
 }
 document.getElementById('payment-form')?.addEventListener('submit', function() {
 document.getElementById('pay-label').style.display = 'none';
 document.getElementById('pay-spinner').style.display = 'block';
 document.getElementById('btn-pay').disabled = true;
 });
 document.getElementById('payment-form-card')?.addEventListener('submit', function() {
 const btn = this.querySelector('button[type="submit"]');
 btn.innerHTML = '<div class="spinner" style="display:block"></div>';
 btn.disabled = true;
 });
function toggleSidebar() {
 document.getElementById('sidebar').classList.toggle('open');
}
</script>
</body>
</html>
