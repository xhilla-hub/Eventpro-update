<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
 header("Location: ../auth/login.php"); exit();
}
include '../config/database.php';

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$initials = strtoupper(substr($user_name, 0, 1));

// Handle form submissions (add / edit / delete package)
$msg = '';
$edit_pkg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $action = $_POST['action'] ?? '';

 if ($action === 'delete') {
 $pid = (int)$_POST['pkg_id'];
 try {
 $pdo->prepare("DELETE FROM packages WHERE id=:id")->execute(['id' => $pid]);
 $msg = 'success:Package deleted.';
 } catch(Exception $e) {
 $msg = 'error:Cannot delete — package has associated bookings.';
 }
 } elseif ($action === 'add' || $action === 'update') {
 $name = trim($_POST['name']);
 $badge = strtoupper(trim($_POST['badge'] ?? $name));
 $price = (float)$_POST['price'];
 $desc = trim($_POST['description']);
 $feats = trim($_POST['features']);
 $icon = trim($_POST['icon'] ?? '');
 $image = trim( 'images/event_concert.png');

 if ($action === 'add') {
 $pdo->prepare("INSERT INTO packages (name,badge,price,description,features,icon) VALUES(:n,:b,:p,:d,:f,:i)")
 ->execute(['n'=>$name,'b'=>$badge,'p'=>$price,'d'=>$desc,'f'=>$feats,'i'=>$icon]);
 $msg = 'success:Package added successfully!';
 } else {
 $pid = (int)$_POST['pkg_id'];
 $pdo->prepare("UPDATE packages SET name=:n,badge=:b,price=:p,description=:d,features=:f,icon=:i, WHERE id=:id")
 ->execute(['n'=>$name,'b'=>$badge,'p'=>$price,'d'=>$desc,'f'=>$feats,'i'=>$icon,'id'=>$pid]);
 $msg = 'success:Package updated!';
 }
 }
 header("Location: packages.php?msg=".urlencode($msg)); exit();
}

if (isset($_GET['msg'])) $msg = urldecode($_GET['msg']);
if (isset($_GET['edit'])) {
 $edit_pkg = $pdo->prepare("SELECT * FROM packages WHERE id=:id");
 $edit_pkg->execute(['id' => (int)$_GET['edit']]);
 $edit_pkg = $edit_pkg->fetch();
}

$packages = $pdo->query("SELECT p.*, COUNT(b.id) AS booking_count FROM packages p LEFT JOIN bookings b ON b.package_id=p.id GROUP BY p.id ORDER BY p.price ASC");
$active_page = 'packages';
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>Admin – Packages | EventPro</title>
 <link rel="stylesheet" href="../../css/dashboard.css"/>
 <style>
 /* 4-Column Design Matching the Design Image */
 .pkg-admin-grid {
 display: grid;
 grid-template-columns: repeat(4, 1fr);
 gap: 24px;
 margin-top: 24px;
 }

 .pkg-card {
 background: var(--surface);
 border-radius: var(--radius);
 border: 1px solid var(--border);
 overflow: hidden;
 position: relative;
 display: flex;
 flex-direction: column;
 box-shadow: var(--shadow-sm);
 transition: all 0.3s;
 }
 .pkg-card:hover {
 transform: translateY(-8px);
 box-shadow: var(--shadow);
 }
 .pkg-card.featured {
 border: 2px solid var(--primary);
 box-shadow: 0 16px 32px rgba(250,204,21,0.1);
 }

 .pkg-img {
 height: 180px;
 width: 100%;
 position: relative;
 background: var(--bg);
 }
 .pkg-img img {
 width: 100%; height: 100%; object-fit: cover;
 }

 .pkg-popular-tag {
 position: absolute;
 top: 0; left: 50%;
 transform: translate(-50%, -50%);
 background: var(--primary);
 color: var(--secondary);
 font-weight: 800;
 font-size: 0.75rem;
 padding: 6px 16px;
 border-radius: 50px;
 white-space: nowrap;
 z-index: 2;
 }

 .pkg-icon-circle {
 position: absolute;
 bottom: -28px;
 left: 24px;
 width: 56px; height: 56px;
 background: var(--white);
 border-radius: 50%;
 display: grid; place-items: center;
 font-size: 1.6rem;
 box-shadow: 0 4px 12px rgba(0,0,0,0.1);
 z-index: 2;
 }

 .pkg-body {
 padding: 44px 24px 24px;
 display: flex;
 flex-direction: column;
 flex: 1;
 }

 .pkg-name {
 font-family: 'Montserrat', sans-serif;
 font-weight: 900;
 font-size: 1.25rem;
 color: var(--secondary);
 text-transform: uppercase;
 margin-bottom: 4px;
 }
 .pkg-sub {
 font-size: 0.8rem;
 color: var(--muted);
 margin-bottom: 16px;
 }

 .pkg-price {
 font-family: 'Montserrat', sans-serif;
 font-weight: 900;
 font-size: 1.8rem;
 color: var(--secondary);
 line-height: 1;
 margin-bottom: 16px;
 }
 .pkg-price span {
 font-size: 0.8rem;
 color: var(--primary);
 font-family: 'Poppins', sans-serif;
 font-weight: 600;
 }

 .pkg-desc {
 font-size: 0.82rem;
 color: var(--muted);
 line-height: 1.6;
 margin-bottom: 24px;
 padding-bottom: 24px;
 border-bottom: 1px solid var(--border);
 }

 .pkg-feats {
 display: flex;
 flex-direction: column;
 gap: 12px;
 margin-bottom: 32px;
 flex: 1;
 }
 .pkg-feats li {
 font-size: 0.8rem;
 color: var(--text);
 display: flex;
 align-items: flex-start;
 gap: 10px;
 line-height: 1.4;
 }
 .pkg-feats li::before {
 content: '';
 color: #fff;
 background: var(--primary);
 width: 16px; height: 16px;
 border-radius: 50%;
 display: grid; place-items: center;
 font-size: 0.6rem;
 font-weight: 800;
 flex-shrink: 0;
 margin-top: 2px;
 }

 .pkg-admin-actions {
 display: flex; gap: 8px; justify-content: center;
 }
 .btn-edit {
 padding: 10px 20px;
 border-radius: 50px;
 font-size: 0.8rem;
 font-weight: 700;
 border: 1.5px solid var(--border);
 background: transparent;
 color: var(--secondary);
 cursor: pointer;
 transition: all 0.2s;
 flex: 1;
 text-align: center;
 }
 .btn-edit:hover { border-color: var(--primary); background: var(--primary-pale); }
 .btn-del {
 padding: 10px 20px;
 border-radius: 50px;
 font-size: 0.8rem;
 font-weight: 700;
 border: none;
 background: #FEE2E2;
 color: #991B1B;
 cursor: pointer;
 transition: all 0.2s;
 flex: 1;
 }
 .btn-del:hover { background: #FECACA; }

 /* Modals */
 .modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px); }
 .modal-box { background:#fff;border-radius:24px;padding:36px;max-width:600px;width:90%;max-height:90vh;overflow-y:auto; }
 .modal-title { font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.6rem;text-transform:uppercase;color:var(--dark);margin-bottom:24px; }
 .mform-group { margin-bottom:16px; }
 .mform-group label { display:block;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);margin-bottom:6px; }
 .mform-group input,.mform-group textarea { width:100%;border:1.5px solid var(--border);border-radius:12px;padding:12px 16px;font-family:'Poppins',sans-serif;font-size:0.9rem;outline:none;transition:all 0.2s; }
 .mform-group input:focus,.mform-group textarea:focus { border-color:var(--primary);box-shadow:0 0 0 3px rgba(250,204,21,0.15); }
 .mform-group textarea { resize:vertical;min-height:80px; }
 .btn-save { width:100%;background:var(--primary);color:var(--secondary);border:none;border-radius:50px;padding:14px;font-weight:800;font-size:1rem;cursor:pointer;margin-top:8px;font-family:'Poppins',sans-serif; transition: all 0.2s; }
 .btn-save:hover { background:var(--primary-lt);transform:translateY(-1px); box-shadow: 0 8px 24px rgba(250,204,21,0.3); }

 @media(max-width: 1200px) { .pkg-admin-grid { grid-template-columns: repeat(2, 1fr); } }
 @media(max-width: 768px) { .pkg-admin-grid { grid-template-columns: 1fr; } }
 </style>
</head>
<body>
<div class="app-layout">

 <?php include 'sidebar.php'; ?>

 <div class="main-area">
 <header class="topbar">
 <div class="topbar-left">
 <h2>Packages</h2>
 <p>Manage event packages and pricing</p>
 </div>
 <div class="topbar-actions" style="gap:20px;">
 <button onclick="document.getElementById('add-modal').style.display='flex'" class="voyago-btn-primary" style="padding:10px 20px;font-size:0.88rem;border-radius:50px;">+ Add Package</button>
 <div class="user-pill" style="background:none;padding:0;gap:10px;border:none;">
 <div class="user-avatar"><?= $initials ?></div>
 <div class="user-info" style="line-height:1.2;"><div class="user-name" style="font-size:0.9rem;"><?= htmlspecialchars($user_name) ?></div></div>
 </div>
 </div>
 </header>

 <main class="page-content">
 <?php if ($msg): [$type,$text] = explode(':', $msg, 2); ?>
 <div class="alert-box <?= $type ?>" style="margin-bottom:20px;"><?= htmlspecialchars($text) ?></div>
 <?php endif; ?>

 <div class="pkg-admin-grid">
 <?php while ($p = $packages->fetch()): 
 $feats = explode('|', $p['features']); 
 $is_popular = str_contains(strtolower($p['badge']), 'popular');
 $img = 'images/event_concert.png';
 ?>
 <div class="pkg-card <?= $is_popular ? 'featured' : '' ?>">

 <div class="pkg-img">
 <?php if($is_popular): ?>
 <div class="pkg-popular-tag">MOST POPULAR</div>
 <?php endif; ?>
 <img src="../../<?= htmlspecialchars($img) ?>" alt="Package Image"/>
 <div class="pkg-icon-circle"><?= htmlspecialchars($p['icon']) ?></div>
 </div>

 <div class="pkg-body">
 <div class="pkg-name"><?= htmlspecialchars($p['name']) ?></div>
 <!-- We will use description as subtitle and the rest as description -->
 <?php 
 $descParts = explode('.', $p['description'], 2);
 $sub = $descParts[0] . '.';
 $desc = trim($descParts[1] ?? '');
 ?>
 <div class="pkg-sub"><?= htmlspecialchars($sub) ?></div>

 <div class="pkg-price">TZS <?= number_format($p['price']) ?> <span>/event</span></div>

 <?php if($desc): ?>
 <div class="pkg-desc"><?= htmlspecialchars($desc) ?></div>
 <?php else: ?>
 <div class="pkg-desc" style="margin-bottom:12px;padding-bottom:12px;">Complete event package for your next gathering.</div>
 <?php endif; ?>

 <ul class="pkg-feats">
 <?php foreach ($feats as $f): ?>
 <li><?= htmlspecialchars(trim($f)) ?></li>
 <?php endforeach; ?>
 </ul>

 <div class="pkg-admin-actions">
 <a href="packages.php?edit=<?= $p['id'] ?>" class="btn-edit"> Edit</a>
 <form method="POST" onsubmit="return confirm('Delete this package?')" style="flex:1;">
 <input type="hidden" name="pkg_id" value="<?= $p['id'] ?>">
 <button type="submit" name="action" value="delete" class="btn-del" style="width:100%;"> Delete</button>
 </form>
 </div>
 </div>

 </div>
 <?php endwhile; ?>
 </div>
 </main>
 </div>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="add-modal" style="display:none;">
 <div class="modal-box">
 <div class="modal-title"> Add New Package</div>
 <form method="POST">
 <input type="hidden" name="action" value="add">
 <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
 <div class="mform-group"><label>Package Name</label><input type="text" name="name" placeholder="Pro" required/></div>
 <div class="mform-group"><label>Badge text</label><input type="text" name="badge" placeholder="MOST POPULAR"/></div>
 </div>
 <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
 <div class="mform-group"><label>Icon (emoji)</label><input type="text" name="icon" placeholder="⭐" maxlength="4"/></div>
 <div class="mform-group"><label>Price (TZS)</label><input type="number" name="price" placeholder="120000" required/></div>
 </div>
 <div class="mform-group"><label>Image Path</label><input type="text" name="image_path" placeholder="images/corporate_event.png" required/></div>
 <div class="mform-group"><label>Description (Sub. Desc)</label><textarea name="description" placeholder="Brief subtitle. Full description..." required></textarea></div>
 <div class="mform-group"><label>Features (separate with |)</label><textarea name="features" placeholder="Feature One|Feature Two|Feature Three" required></textarea></div>
 <button type="submit" class="btn-save">Save Package</button>
 <button type="button" onclick="document.getElementById('add-modal').style.display='none'" style="width:100%;margin-top:10px;background:none;border:1.5px solid var(--border);padding:12px;border-radius:50px;cursor:pointer;font-weight:600;">Cancel</button>
 </form>
 </div>
</div>

<?php if ($edit_pkg): ?>
<!-- EDIT MODAL (auto-open) -->
<div class="modal-overlay" id="edit-modal" style="display:flex;">
 <div class="modal-box">
 <div class="modal-title"> Edit Package</div>
 <form method="POST">
 <input type="hidden" name="action" value="update">
 <input type="hidden" name="pkg_id" value="<?= $edit_pkg['id'] ?>">
 <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
 <div class="mform-group"><label>Package Name</label><input type="text" name="name" value="<?= htmlspecialchars($edit_pkg['name']) ?>" required/></div>
 <div class="mform-group"><label>Badge text</label><input type="text" name="badge" value="<?= htmlspecialchars($edit_pkg['badge']) ?>"/></div>
 </div>
 <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
 <div class="mform-group"><label>Icon (emoji)</label><input type="text" name="icon" value="<?= htmlspecialchars($edit_pkg['icon']) ?>" maxlength="4"/></div>
 <div class="mform-group"><label>Price (TZS)</label><input type="number" name="price" value="<?= $edit_pkg['price'] ?>" required/></div>
 </div>
 <div class="mform-group"><label>Image Path</label><input type="text" name="image_path" value="<?= htmlspecialchars($edit_pkg['image_path'] ?? '') ?>" required/></div>
 <div class="mform-group"><label>Description</label><textarea name="description" required><?= htmlspecialchars($edit_pkg['description']) ?></textarea></div>
 <div class="mform-group"><label>Features (separate with |)</label><textarea name="features" required><?= htmlspecialchars($edit_pkg['features']) ?></textarea></div>
 <button type="submit" class="btn-save">Update Package</button>
 <a href="packages.php" style="display:block;width:100%;margin-top:10px;background:none;border:1.5px solid var(--border);padding:12px;border-radius:50px;cursor:pointer;font-weight:600;text-align:center;color:var(--dark);">Cancel</a>
 </form>
 </div>
</div>
<?php endif; ?>
</body>
</html>
