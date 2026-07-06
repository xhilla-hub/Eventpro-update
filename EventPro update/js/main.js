/* ===================================
 EventPro – JavaScript Interactions
 =================================== */

// ── NAV SCROLL EFFECT
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
 navbar.classList.toggle('scrolled', window.scrollY > 60);
});

// ── MOBILE HAMBURGER
const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('nav-links');
hamburger.addEventListener('click', () => {
 navLinks.classList.toggle('open');
 const bars = hamburger.querySelectorAll('span');
 const isOpen = navLinks.classList.contains('open');
 bars[0].style.transform = isOpen ? 'translateY(7px) rotate(45deg)' : '';
 bars[1].style.opacity = isOpen ? '0' : '1';
 bars[2].style.transform = isOpen ? 'translateY(-7px) rotate(-45deg)' : '';
});

// ── HERO IMAGE SWITCHER
const heroImages = [
 'images/2.png',
 'images/12.png',
 'images/6.png',
];
const heroImg = document.getElementById('hero-img');
const thumbs = document.querySelectorAll('.thumb');

window.switchHero = function(idx) {
 heroImg.style.opacity = '0';
 setTimeout(() => {
 heroImg.src = heroImages[idx];
 heroImg.style.opacity = '1';
 }, 400);
 thumbs.forEach((t, i) => t.classList.toggle('active', i === idx));
};

// Auto-rotate hero
let heroIdx = 0;
setInterval(() => {
 heroIdx = (heroIdx + 1) % heroImages.length;
 switchHero(heroIdx);
}, 5000);

// ── SCROLL REVEAL
const revealObserver = new IntersectionObserver((entries) => {
 entries.forEach(entry => {
 if (entry.isIntersecting) {
 entry.target.classList.add('visible');
 revealObserver.unobserve(entry.target);
 }
 });
}, { threshold: 0.15 });

// Attach reveal to key elements
const revealTargets = [
 '.section-tag',
 '.section-title',
 '.section-sub',
 '.hiw-card',
 '.explore-card',
 '.pkg-card',
 '.gallery-item',
 '.testi-card',
 '.vblog-card',
 '.exp-img-circle',
 '.exp-stats',
 '.cta-content'
];

revealTargets.forEach(selector => {
 document.querySelectorAll(selector).forEach((el, i) => {
 el.classList.add('reveal');
 // For grid items, we want a staggered delay based on their index within the container
 // We can do a simple mod operation to stagger them in rows
 let delayIndex = i % 4; 
 if(selector === '.hiw-card' || selector === '.testi-card' || selector === '.vblog-card') delayIndex = i % 3;
 if(selector === '.section-title' || selector === '.section-tag' || selector === '.section-sub') delayIndex = i % 1;

 el.style.transitionDelay = `${delayIndex * 0.15}s`;
 revealObserver.observe(el);
 });
});

// Trigger Hero Animations on load
document.addEventListener("DOMContentLoaded", () => {
 const heroText = document.querySelector('.hero-text');
 if(heroText) {
 heroText.style.opacity = 0;
 setTimeout(() => {
 heroText.classList.add('hero-animate-up');
 }, 100);
 }
});

// ── STATS COUNTER
function animateCounter(el) {
 const target = parseInt(el.dataset.target, 10);
 const duration = 2000;
 const step = target / (duration / 16);
 let current = 0;
 const timer = setInterval(() => {
 current += step;
 if (current >= target) {
 current = target;
 clearInterval(timer);
 }
 el.textContent = Math.floor(current).toLocaleString();
 if (el.dataset.target === '98') el.textContent += '%';
 if (el.dataset.target === '1200') el.textContent = Math.floor(current).toLocaleString() + '+';
 if (el.dataset.target === '8500') el.textContent = Math.floor(current).toLocaleString() + '+';
 }, 16);
}

const statObserver = new IntersectionObserver((entries) => {
 entries.forEach(entry => {
 if (entry.isIntersecting) {
 animateCounter(entry.target);
 statObserver.unobserve(entry.target);
 }
 });
}, { threshold: 0.5 });

document.querySelectorAll('.stat-number').forEach(el => statObserver.observe(el));

// ── SMOOTH SCROLL for anchor links
document.querySelectorAll('a[href^="#"]').forEach(link => {
 link.addEventListener('click', e => {
 const href = link.getAttribute('href');
 if (href === '#') return;
 const target = document.querySelector(href);
 if (target) {
 e.preventDefault();
 const offset = navbar.offsetHeight + 16;
 const top = target.getBoundingClientRect().top + window.scrollY - offset;
 window.scrollTo({ top, behavior: 'smooth' });
 // close mobile menu
 navLinks.classList.remove('open');
 }
 });
});

// ── HERO PARALLAX (subtle)
window.addEventListener('scroll', () => {
 const scrolled = window.scrollY;
 if (heroImg) {
 heroImg.style.transform = `translateY(${scrolled * 0.25}px) scale(1.05)`;
 }
});

console.log('%cEventPro Loaded ', 'color:#ff3c00;font-weight:bold;font-size:14px;');
