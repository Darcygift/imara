<?php
// header.php — Premium Global Navigation
require_once 'config.php';

$current_page = basename($_SERVER['PHP_SELF']);
$theme = isset($_COOKIE['imara_theme']) ? htmlspecialchars($_COOKIE['imara_theme']) : 'dark';

$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) { session_destroy(); header("Location: index.php"); exit; }
}

// Unread message count
$unread = 0;
if ($user) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE to_id = ? AND is_read = 0");
        $stmt->execute([$user['id']]);
        $unread = (int)$stmt->fetchColumn();
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Imara.rw — Rwanda's Premier Property Platform</title>
  <meta name="description" content="Discover verified luxury properties for rent and sale across Kigali, Rubavu, Musanze and all of Rwanda. Airbnb-style booking with MTN MoMo escrow protection.">
  <meta property="og:title" content="Imara.rw — Rwanda's Premier Property Platform">
  <meta property="og:description" content="Find your dream property in Rwanda. Verified listings, GPS maps, and secure MoMo escrow.">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
</head>
<body class="theme-<?php echo $theme; ?>">

<!-- Toast Notification Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Main Navbar -->
<header>
  <nav class="main-navbar" id="mainNav">
    <div class="container navbar-inner">
      <!-- Logo -->
      <a href="index.php" class="logo-brand">
        <img src="images/logo.png" alt="Imara.rw" onerror="this.style.display='none'">
        <span class="logo-text">Imara.rw</span>
      </a>

      <!-- Desktop nav links -->
      <nav class="nav-links">
        <a href="index.php"    class="nav-link <?php echo $current_page=='index.php'    ? 'active':''; ?>">Home</a>
        <a href="listings.php" class="nav-link <?php echo $current_page=='listings.php' ? 'active':''; ?>">Browse</a>
        <a href="map.php"      class="nav-link <?php echo $current_page=='map.php'      ? 'active':''; ?>">Map View</a>
        <?php if ($user): ?>
          <a href="chat.php"      class="nav-link <?php echo $current_page=='chat.php'      ? 'active':''; ?>" style="position:relative;">
            Messages <?php if($unread>0): ?><span class="notif-dot"></span><?php endif; ?>
          </a>
          <a href="dashboard.php" class="nav-link <?php echo $current_page=='dashboard.php' ? 'active':''; ?>">Dashboard</a>
        <?php endif; ?>
      </nav>

      <!-- Right actions -->
      <div class="nav-actions">
        <!-- Theme switcher -->
        <div class="theme-switcher" id="themeSwitcher">
          <button class="theme-btn <?php echo $theme=='dark'  ?'active':''; ?>" onclick="setTheme('dark')"  title="Dark">🌙</button>
          <button class="theme-btn <?php echo $theme=='light' ?'active':''; ?>" onclick="setTheme('light')" title="Light">☀️</button>
          <button class="theme-btn <?php echo $theme=='royal' ?'active':''; ?>" onclick="setTheme('royal')" title="Royal">👑</button>
        </div>

        <?php if ($user): ?>
          <a href="profile.php" class="avatar" title="<?php echo htmlspecialchars($user['name']); ?>">
            <?php echo strtoupper(substr($user['name'],0,1)); ?>
          </a>
          <a href="logout.php" class="btn btn-secondary btn-sm">Sign Out</a>
        <?php else: ?>
          <a href="login.php"  class="btn btn-secondary btn-sm">Sign In</a>
          <a href="signup.php" class="btn btn-primary btn-sm">Register Free</a>
        <?php endif; ?>

        <!-- Hamburger -->
        <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </nav>

  <!-- Mobile Drawer Menu -->
  <div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-inner">
      <a href="index.php"    class="mobile-nav-link <?php echo $current_page=='index.php'    ?'active':''; ?>">🏠 Home</a>
      <a href="listings.php" class="mobile-nav-link <?php echo $current_page=='listings.php' ?'active':''; ?>">🔍 Browse Listings</a>
      <a href="map.php"      class="mobile-nav-link <?php echo $current_page=='map.php'      ?'active':''; ?>">🗺️ Interactive Map</a>
      <?php if ($user): ?>
        <a href="chat.php"      class="mobile-nav-link <?php echo $current_page=='chat.php'      ?'active':''; ?>">💬 Messages <?php if($unread>0): ?>(<?php echo $unread; ?>)<?php endif; ?></a>
        <a href="dashboard.php" class="mobile-nav-link <?php echo $current_page=='dashboard.php' ?'active':''; ?>">📊 Dashboard</a>
        <a href="profile.php"   class="mobile-nav-link">👤 My Profile</a>
        <a href="logout.php"    class="mobile-nav-link" style="color:var(--accent-danger);">🚪 Sign Out</a>
      <?php else: ?>
        <div style="display:flex;gap:0.75rem;margin-top:1rem;padding:0 0.5rem;">
          <a href="login.php"  class="btn btn-secondary" style="flex:1;">Sign In</a>
          <a href="signup.php" class="btn btn-primary"   style="flex:1;">Register</a>
        </div>
      <?php endif; ?>

      <!-- Mobile theme switcher -->
      <div style="margin-top:1.5rem;padding:0 0.5rem;">
        <p style="font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);margin-bottom:0.75rem;">Theme</p>
        <div style="display:flex;gap:0.5rem;">
          <button class="btn btn-secondary btn-sm" onclick="setTheme('dark')">🌙 Dark</button>
          <button class="btn btn-secondary btn-sm" onclick="setTheme('light')">☀️ Light</button>
          <button class="btn btn-secondary btn-sm" onclick="setTheme('royal')">👑 Royal</button>
        </div>
      </div>
    </div>
  </div>
</header>

<script>
// ── Theme Manager ──────────────────────────────────────
function setTheme(t) {
  const exp = new Date(Date.now()+30*24*60*60*1000).toUTCString();
  document.cookie = `imara_theme=${t};expires=${exp};path=/`;
  document.body.className = `theme-${t}`;
  document.querySelectorAll('.theme-btn').forEach(b => b.classList.remove('active'));
  const active = document.querySelector(`.theme-btn[onclick="setTheme('${t}')"]`);
  if (active) active.classList.add('active');
}

// ── Mobile Menu Toggle ─────────────────────────────────
function toggleMobileMenu() {
  const menu = document.getElementById('mobileMenu');
  const ham  = document.getElementById('hamburger');
  const open = menu.classList.toggle('open');
  ham.classList.toggle('open', open);
  document.body.style.overflow = open ? 'hidden' : '';
}

// ── Navbar scroll shadow ───────────────────────────────
window.addEventListener('scroll', () => {
  document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 40);
});

// ── Toast Helper (global) ──────────────────────────────
function showToast(message, type = 'info', duration = 4000) {
  const container = document.getElementById('toastContainer');
  const icons = { success:'✓', error:'✕', info:'ℹ' };
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span>${icons[type]||'ℹ'}</span><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => { toast.style.opacity='0'; toast.style.transform='translateX(100%)';
    toast.style.transition='all 0.3s ease';
    setTimeout(() => toast.remove(), 300); }, duration);
}

// ── Global Wishlist Toggle ─────────────────────────────
function toggleWishlist(event, listingId) {
  event.stopPropagation();
  const btn = document.getElementById(`heart-${listingId}`);
  const fd = new FormData(); fd.append('listing_id', listingId);
  fetch('api/toggle-wishlist.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        btn.classList.toggle('active', d.action === 'added');
        showToast(d.action==='added' ? '❤️ Saved to favorites!' : '💔 Removed from favorites', 'success');
      } else if (d.error && (d.error.includes('sign in') || d.error.includes('auth'))) {
        showToast('Please sign in to save properties', 'info');
        setTimeout(() => location.href='login.php', 1500);
      } else { showToast(d.error||'Error', 'error'); }
    })
    .catch(() => showToast('Network error. Try again.', 'error'));
}
</script>
