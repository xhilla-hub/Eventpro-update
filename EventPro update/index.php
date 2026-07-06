<?php
session_start();
require 'backend/config/database.php';
$is_logged_in = isset($_SESSION['user_id']);

$packages = $pdo->query("SELECT * FROM packages ORDER BY price ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8"/>
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>EventPro – Tanzania's #1 Event Planning Marketplace</title>
 <meta name="description" content="Book top vendors for your events — sound, photography, tents, decor, catering and more. Package pricing with TZS payments."/>
 <link rel="preconnect" href="https://fonts.googleapis.com"/>
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
 <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
 <link rel="stylesheet" href="css/style.css"/>
</head>
<body>

 <!-- ── TOPBAR ── -->
 <div class="topbar-strip">
 <div class="topbar-inner">
 <span> Serving all major cities in Tanzania</span>
 <div class="topbar-links">
 <a href="backend/auth/login.php">Login</a>
 <a href="backend/auth/register.php">Register</a>
 <span> +255 700 000 000</span>
 </div>
 </div>
 </div>

 <!-- ── NAVBAR ── -->
 <nav class="navbar" id="navbar">
 <div class="nav-container">
 <a href="#" class="nav-logo" id="nav-logo">
 <span class="logo-icon">EP</span>
 <span class="logo-text">EventPro</span>
 </a>
 <ul class="nav-links" id="nav-links">
 <li><a href="#home" class="active">Home</a></li>
 <li><a href="#about">About Us</a></li>
 <li><a href="#packages">Packages</a></li>
 <li><a href="#how-it-works">How It Works</a></li>
 <li><a href="#events">Events</a></li>
 <li><a href="#contact">Contact Us</a></li>
 </ul>
 <div class="nav-actions">
 <?php if ($is_logged_in): ?>
 <a href="backend/auth/dashboard.php" class="btn-nav-cta" id="btn-dashboard">My Dashboard →</a>
 <?php else: ?>
 <a href="backend/auth/register.php" class="btn-nav-cta" id="btn-register">Get Started →</a>
 <?php endif; ?>
 </div>
 <button class="hamburger" id="hamburger" aria-label="Toggle menu">
 <span></span><span></span><span></span>
 </button>
 </div>
 </nav>

 <!-- ── HERO ── -->
 <section class="hero" id="home">
 <div class="hero-inner">
 <div class="hero-text">
 <div class="hero-tag"> Tanzania's #1 Event Marketplace</div>
 <h1>It's Your Big Event,<br/><span class="hero-yellow">Plan With</span><br/><span class="font-script" style="color:var(--primary)">Confidence</span> </h1>
 <p>From intimate gatherings to large conferences — EventPro connects you with the best vendors for sound, photography, decor, catering and more. All in one place.</p>
 <div class="hero-btns">
 <a href="<?= $is_logged_in ? 'backend/auth/booking.php' : 'backend/auth/register.php' ?>" class="btn-hero-primary" id="btn-hero-start">Start Planning →</a>
 <a href="#how-it-works" class="btn-hero-outline" id="btn-hero-learn">How It Works</a>
 </div>
 <div class="hero-badges">
 <div class="hero-badge"><span class="hb-num">1.2k+</span><span class="hb-lbl">Vendors</span></div>
 <div class="hb-divider"></div>
 <div class="hero-badge"><span class="hb-num">8.5k+</span><span class="hb-lbl">Events Planned</span></div>
 <div class="hb-divider"></div>
 <div class="hero-badge"><span class="hb-num">98%</span><span class="hb-lbl">Satisfaction</span></div>
 </div>
 </div>
 <div class="hero-visual">
 <div class="hero-img-wrap">
 <img src="images/2.png" alt="Grand Event" class="hero-main-img" id="hero-img"/>
 <div class="hero-float-card card-1">
 <div class="hfc-icon"></div>
 <div>
 <div class="hfc-title">Weddings & Gala Dinners</div>
 <div class="hfc-sub">50+ packages available</div>
 </div>
 </div>
 <div class="hero-float-card card-2">
 <div class="hfc-icon"></div>
 <div>
 <div class="hfc-title">Next Event: Corporate Summit</div>
 <div class="hfc-sub">Dec 28, 2025 · Dar es Salaam</div>
 </div>
 </div>
 <div class="hero-float-rating">
 <div class="stars"></div>
 <div class="rat-text">4.9 / 5.0 Rating</div>
 </div>
 </div>
 </div>
 </div>

 <!-- Search Bar -->
 <div class="hero-search-bar">
 <div class="search-wrap">
 <div class="search-field">
 <span class="sf-label">Event Type</span>
 <select class="sf-input" id="search-type">
 <option>Wedding</option>
 <option>Corporate Event</option>
 <option>Concert / Show</option>
 <option>Birthday Party</option>
 <option>Conference</option>
 </select>
 </div>
 <div class="search-div"></div>
 <div class="search-field">
 <span class="sf-label">Location</span>
 <input type="text" class="sf-input" placeholder="Dar es Salaam" id="search-location"/>
 </div>
 <div class="search-div"></div>
 <div class="search-field">
 <span class="sf-label">Date</span>
 <input type="date" class="sf-input" id="search-date"/>
 </div>
 <div class="search-div"></div>
 <div class="search-field">
 <span class="sf-label">Guests</span>
 <input type="number" class="sf-input" placeholder="200" id="search-guests"/>
 </div>
 <a href="<?= $is_logged_in ? 'backend/auth/booking.php' : 'backend/auth/register.php' ?>" class="btn-search" id="btn-search"> Search</a>
 </div>
 </div>
 </section>

 <!-- ── HOW IT WORKS ── -->
 <section class="hiw-section" id="how-it-works">
 <div class="section-container">
 <div class="section-tag"> Simple Process</div>
 <h2 class="section-title">Find Event <span class="font-script" style="color:var(--primary)">Perfection</span></h2>
 <p class="section-sub">Three easy steps to get your dream event perfectly executed</p>
 <div class="hiw-grid">
 <div class="hiw-card">
 <div class="hiw-icon" style="background:#FEF9C3;"></div>
 <h3>Create Your Event</h3>
 <p>Tell us your event type, date, location, and guest count. We generate a custom vendor checklist instantly.</p>
 </div>
 <div class="hiw-card hiw-featured">
 <div class="hiw-icon" style="background:#0F172A;"></div>
 <h3>Find Top Vendors</h3>
 <p>Browse 1,200+ verified vendors — sound, photography, tents, decor, lighting, catering, and more.</p>
 </div>
 <div class="hiw-card">
 <div class="hiw-icon" style="background:#FEF9C3;"></div>
 <h3>Book & Pay Securely</h3>
 <p>Choose your package, make a secure TZS payment via M-Pesa, and track everything on your dashboard.</p>
 </div>
 </div>
 </div>
 </section>

 <!-- ── EXPLORE TOP EVENTS ── -->
 <section class="explore-section" id="events">
 <div class="section-container">
 <div class="section-top">
 <div>
 <div class="section-tag"> Featured Events</div>
 <h2 class="section-title">Explore <span class="font-script" style="color:var(--primary)">Top</span> Event Types</h2>
 </div>
 <a href="<?= $is_logged_in ? 'backend/auth/booking.php' : 'backend/auth/register.php' ?>" class="btn-see-all" id="btn-explore-all">See All Events →</a>
 </div>
 <div class="explore-grid">
 <div class="explore-card" id="exp-card-1">
 <img src="images/1.png" alt="Concert"/>
 <div class="exp-overlay"></div>
 <div class="exp-info">
 <div class="exp-tag"> Concert & Shows</div>
 <h3>Live Music Events</h3>
 <div class="exp-meta"><span>⭐ 4.9</span><span>·</span><span>32 Events</span></div>
 </div>
 <div class="exp-price">From TZS 350,000</div>
 </div>
 <div class="explore-card" id="exp-card-2">
 <img src="images/2.png" alt="Corporate"/>
 <div class="exp-overlay"></div>
 <div class="exp-info">
 <div class="exp-tag"> Corporate</div>
 <h3>Business Conferences</h3>
 <div class="exp-meta"><span>⭐ 4.8</span><span>·</span><span>58 Events</span></div>
 </div>
 <div class="exp-price">From TZS 120,000</div>
 </div>
 <div class="explore-card" id="exp-card-3">
 <img src="images/3.png" alt="Wedding"/>
 <div class="exp-overlay"></div>
 <div class="exp-info">
 <div class="exp-tag"> Wedding</div>
 <h3>Wedding Ceremonies</h3>
 <div class="exp-meta"><span>⭐ 5.0</span><span>·</span><span>120 Events</span></div>
 </div>
 <div class="exp-price">From TZS 200,000</div>
 </div>
 <div class="explore-card" id="exp-card-4">
 <img src="images/4.png" alt="Gala"/>
 <div class="exp-overlay"></div>
 <div class="exp-info">
 <div class="exp-tag"> Gala Dinner</div>
 <h3>Gala & Award Nights</h3>
 <div class="exp-meta"><span>⭐ 4.7</span><span>·</span><span>41 Events</span></div>
 </div>
 <div class="exp-price">From TZS 180,000</div>
 </div>
 </div>
 </div>
 </section>

 <!-- ── EXPERIENCE SECTION ── -->
 <section class="experience-section" id="about">
 <div class="section-container exp-flex">
 <div class="exp-left">
 <div class="exp-img-circle">
 <img src="images/13.png" alt="Our team"/>
 <div class="exp-img-badge"><br/>Best<br/>Platform</div>
 </div>
 </div>
 <div class="exp-right">
 <div class="section-tag"> Our Experience</div>
 <h2 class="section-title">With Our Experience We<br/>Will <span class="font-script" style="color:var(--primary)">Serve</span> You </h2>
 <p class="section-sub" style="text-align:left;">EventPro has been connecting event planners with Tanzania's finest vendors for over 5 years. We've powered some of the largest events in East Africa — from intimate dinners to 10,000-person concerts.</p>
 <div class="exp-stats">
 <div class="exp-stat"><div class="es-num">20+</div><div class="es-lbl">Years Combined Experience</div></div>
 <div class="exp-stat"><div class="es-num">400+</div><div class="es-lbl">Verified Vendors</div></div>
 <div class="exp-stat"><div class="es-num">50K+</div><div class="es-lbl">Happy Clients</div></div>
 </div>
 <a href="backend/auth/register.php" class="btn-exp">Join EventPro Today →</a>
 </div>
 </div>
 </section>

 <!-- ── PACKAGES ── -->
 <section class="packages-section" id="packages">
 <div class="section-container">
 <div class="section-tag"> PACKAGES & PRICING</div>
 <h2 class="section-title" style="text-align:center; font-family:'Montserrat',sans-serif; font-weight:900; font-size:3rem;">Choose The <span class="font-script" style="color:var(--primary)">Perfect</span> Package</h2>
 <p class="section-sub" style="text-align:center; margin-bottom:48px;">Flexible packages designed to make your event unforgettable.</p>

 <div class="pkg-admin-grid public-pkg-grid">
 <?php foreach ($packages as $p): 
 $feats = explode('|', $p['features']); 
 $is_popular = str_contains(strtolower($p['badge']), 'popular');
 $img ='images/event_concert.png';
 $descParts = explode('.', $p['description'], 2);
 $sub = $descParts[0] . '.';
 $desc = trim($descParts[1] ?? '');
 $btnText = "Choose " . ucfirst(strtolower($p['name'])) . " >";
 ?>
 <div class="pkg-card <?= $is_popular ? 'featured' : '' ?>">

 <div class="pkg-img">
 <?php if($is_popular): ?>
 <div class="pkg-popular-tag">MOST POPULAR</div>
 <?php endif; ?>
 <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>"/>
 <div class="pkg-icon-circle"><?= htmlspecialchars($p['icon']) ?></div>
 </div>

 <div class="pkg-body">
 <div class="pkg-name"><?= htmlspecialchars($p['name']) ?></div>
 <div class="pkg-sub"><?= htmlspecialchars($sub) ?></div>

 <div class="pkg-price">TZS <?= number_format($p['price']) ?> <span>/event</span></div>

 <div class="pkg-desc"><?= htmlspecialchars($desc) ?: 'Complete event package for your next gathering.' ?></div>

 <ul class="pkg-feats">
 <?php foreach ($feats as $f): ?>
 <li><?= htmlspecialchars(trim($f)) ?></li>
 <?php endforeach; ?>
 </ul>

 <a href="<?= $is_logged_in ? 'backend/auth/booking.php?pkg='.$p['id'] : 'backend/auth/register.php' ?>" class="btn-pkg-book <?= $is_popular ? 'featured' : '' ?>">
 <?= htmlspecialchars($btnText) ?>
 </a>
 </div>

 </div>
 <?php endforeach; ?>
 </div>

 <!-- Trust Badges Section -->
 <div class="trust-badges">
 <div class="trust-badge">
 <div class="tb-icon"></div>
 <div class="tb-text"><strong>Trusted & Reliable</strong><br/>We deliver what we promise.</div>
 </div>
 <div class="trust-badge">
 <div class="tb-icon"></div>
 <div class="tb-text"><strong>Experienced Team</strong><br/>Skilled professionals at your service.</div>
 </div>
 <div class="trust-badge">
 <div class="tb-icon"></div>
 <div class="tb-text"><strong>Quality Guaranteed</strong><br/>Top-notch services, always.</div>
 </div>
 <div class="trust-badge">
 <div class="tb-icon"></div>
 <div class="tb-text"><strong>24/7 Support</strong><br/>We're here, anytime you need us.</div>
 </div>
 </div>

 </div>
 </section>

 <!-- ── DREAM EVENTS GALLERY ── -->
 <section class="gallery-section">
 <div class="section-container">
 <div class="section-tag"> Event Gallery</div>
 <h2 class="section-title">Your Dream Event Awaits</h2>
 <p class="section-sub">A glimpse of the extraordinary events we've helped bring to life</p>
 <div class="gallery-grid">
 <div class="gallery-item gi-tall" id="gi-1"><img src="images/5.png" alt="Event 1"/><div class="gi-label"> Live Concert</div></div>
 <div class="gallery-item" id="gi-2"><img src="images/corporate_event.png" alt="Event 2"/><div class="gi-label"> Corporate Gala</div></div>
 <div class="gallery-item" id="gi-3"><img src="images/7.png" alt="Event 3"/><div class="gi-label"> Music Festival</div></div>
 <div class="gallery-item gi-wide" id="gi-4"><img src="images/6.png" alt="Event 4"/><div class="gi-label"> Wedding Ceremony</div></div>
 <div class="gallery-item" id="gi-5"><img src="images/8.png" alt="Event 5"/><div class="gi-label"> Gala Dinner</div></div>
 </div>
 </div>
 </section>

 <!-- ── TESTIMONIALS ── -->
 <section class="testimonials-section">
 <div class="section-container">
 <div class="section-tag"> Customer Reviews</div>
 <h2 class="section-title">What Our <span class="font-script" style="color:var(--primary)">Customers</span> Say </h2>
 <div class="testimonials-grid">
 <div class="testi-card" id="tc-1">
 <div class="testi-stars"></div>
 <p>"EventPro made planning our corporate conference so seamless. The vendors were professional and everything arrived on time. Will definitely use again!"</p>
 <div class="testi-user">
 <div class="testi-avatar">AM</div>
 <div>
 <div class="testi-name">Amina Mwangi</div>
 <div class="testi-role">Corporate Events Manager, Dar es Salaam</div>
 </div>
 </div>
 </div>
 <div class="testi-card testi-featured" id="tc-2">
 <div class="testi-stars"></div>
 <p>"Our wedding was absolutely perfect. From the sound to the decor, every vendor delivered beyond expectations. EventPro is a game changer in Tanzania!"</p>
 <div class="testi-user">
 <div class="testi-avatar">JK</div>
 <div>
 <div class="testi-name">James & Kemi Okafor</div>
 <div class="testi-role">Wedding Clients, Arusha</div>
 </div>
 </div>
 </div>
 <div class="testi-card" id="tc-3">
 <div class="testi-stars"></div>
 <p>"Booked the Pro package for our product launch event. 300 guests, zero problems. The dedicated event manager was incredibly helpful throughout."</p>
 <div class="testi-user">
 <div class="testi-avatar">TN</div>
 <div>
 <div class="testi-name">Tariq Nassir</div>
 <div class="testi-role">Startup Founder, Zanzibar</div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </section>

 <!-- ── VENDORS / BLOG ── -->
 <section class="vendors-section" id="vendors">
 <div class="section-container">
 <div class="section-tag"> Our Vendors</div>
 <h2 class="section-title">Featured Vendor <span class="font-script" style="color:var(--primary)">Categories</span></h2>
 <p class="section-sub">We partner with Tanzania's most trusted event service providers</p>
 <div class="vendor-blogs-grid">
 <div class="vblog-card" id="vb-1">
 <div class="vblog-img"><img src="images/14.png" alt="Sound"/></div>
 <div class="vblog-body">
 <div class="vblog-tag"> Audio & Sound</div>
 <h3>Concert-Grade PA Systems & DJ Equipment</h3>
 <p>From intimate weddings to massive concerts, our sound vendors bring crystal-clear audio to any scale.</p>
 <a href="<?= $is_logged_in ? 'backend/auth/booking.php' : 'backend/auth/register.php' ?>" class="vblog-link">Explore Vendors →</a>
 </div>
 </div>
 <div class="vblog-card" id="vb-2">
 <div class="vblog-img"><img src="images/15.png" alt="Photography"/></div>
 <div class="vblog-body">
 <div class="vblog-tag"> Photography & Video</div>
 <h3>Professional Event Photography & Videography</h3>
 <p>Capture every moment with our award-winning photographers and videographers across Tanzania.</p>
 <a href="<?= $is_logged_in ? 'backend/auth/booking.php' : 'backend/auth/register.php' ?>" class="vblog-link">Explore Vendors →</a>
 </div>
 </div>
 <div class="vblog-card" id="vb-3">
 <div class="vblog-img"><img src="images/17.png" alt="Decor"/></div>
 <div class="vblog-body">
 <div class="vblog-tag"> Decor & Design</div>
 <h3>Premium Event Decoration & Tent Setup</h3>
 <p>Transform any venue into a stunning event space with our expert decor and tent installation vendors.</p>
 <a href="<?= $is_logged_in ? 'backend/auth/booking.php' : 'backend/auth/register.php' ?>" class="vblog-link">Explore Vendors →</a>
 </div>
 </div>
 </div>
 </div>
 </section>

 <!-- ── CTA BANNER ── -->
 <section class="cta-banner" id="cta-banner">
 <div class="cta-bg-img"><img src="images/event_hero.png" alt="Event"/></div>
 <div class="cta-overlay"></div>
 <div class="cta-content">
 <div class="section-tag light"> Ready to Start?</div>
 <h2>Let's Plan Your <span class="font-script" style="color:var(--primary)">Perfect</span><br/>Event Together </h2>
 <p>Join 50,000+ happy clients who trust EventPro for their events in Tanzania</p>
 <div class="cta-btns">
 <a href="<?= $is_logged_in ? 'backend/auth/booking.php' : 'backend/auth/register.php' ?>" class="btn-cta-primary" id="btn-cta-start">Start Planning Now →</a>
 <a href="#how-it-works" class="btn-cta-ghost">Learn More</a>
 </div>
 </div>
 </section>

 <!-- ── PARTNERS TICKER ── -->
 <div class="partners-bar" id="partners-bar">
 <div class="ticker-wrapper">
 <div class="ticker-track" id="ticker-track">
 <span> SoundWave Pro</span>
 <span> SnapStudio TZ</span>
 <span> TentMasters EA</span>
 <span> BloomDecor</span>
 <span> CaterElite</span>
 <span> LightCraft Africa</span>
 <span> BeatBox DJ</span>
 <span> VenueVault</span>
 <span> SoundWave Pro</span>
 <span> SnapStudio TZ</span>
 <span> TentMasters EA</span>
 <span> BloomDecor</span>
 <span> CaterElite</span>
 <span> LightCraft Africa</span>
 <span> BeatBox DJ</span>
 <span> VenueVault</span>
 </div>
 </div>
 </div>

 <!-- ── FOOTER ── -->
 <footer class="footer" id="footer">
 <div class="footer-top">
 <div class="footer-brand">
 <div class="footer-logo">
 <span class="logo-icon">EP</span>
 <span class="logo-text">EventPro</span>
 </div>
 <p>Tanzania's #1 event planning marketplace. Connecting planners with top vendors for seamless, unforgettable events.</p>
 <div class="social-links" id="social-links">
 <a href="#" aria-label="Facebook" id="fb-link">f</a>
 <a href="#" aria-label="Twitter" id="tw-link">t</a>
 <a href="#" aria-label="Instagram" id="ig-link">in</a>
 <a href="#" aria-label="LinkedIn" id="li-link">li</a>
 </div>
 </div>
 <div class="footer-col">
 <h4>Quick Links</h4>
 <ul>
 <li><a href="#home">Home</a></li>
 <li><a href="#about">About Us</a></li>
 <li><a href="#packages">Packages</a></li>
 <li><a href="#how-it-works">How It Works</a></li>
 </ul>
 </div>
 <div class="footer-col">
 <h4>Categories</h4>
 <ul>
 <li><a href="#">Weddings</a></li>
 <li><a href="#">Corporate Events</a></li>
 <li><a href="#">Concerts & Shows</a></li>
 <li><a href="#">Gala Dinners</a></li>
 <li><a href="#">Birthday Parties</a></li>
 </ul>
 </div>
 <div class="footer-col">
 <h4>Get In Touch</h4>
 <p> Dar es Salaam, Tanzania</p>
 <p> +255 700 000 000</p>
 <p> hello@eventpro.co.tz</p>
 <p style="margin-top:14px;font-size:0.82rem;">Mon–Fri: 8am – 6pm EAT</p>
 </div>
 </div>
 <div class="footer-bottom">
 <p>© 2025 EventPro Tanzania. All rights reserved.</p>
 <div class="footer-links">
 <a href="#" id="faq-link">FAQ</a>
 <a href="#" id="terms-link">Terms of Service</a>
 <a href="#" id="privacy-link">Privacy Policy</a>
 </div>
 </div>
 <div class="footer-wordmark">EVENTPRO</div>
 </footer>

 <script src="js/main.js"></script>
</body>
</html>
