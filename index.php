<?php
require_once 'header.php';
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Stats + featured listings
try {
    $activeCount   = (int)$pdo->query("SELECT COUNT(*) FROM listings WHERE available=1")->fetchColumn();
    $verifiedCount = (int)$pdo->query("SELECT COUNT(*) FROM listings WHERE verified=1 AND available=1")->fetchColumn();
    $reviewCount   = (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT l.*,
               COALESCE((SELECT AVG(r.rating) FROM reviews r WHERE r.listing_id=l.id),0) AS avg_rating,
               COALESCE((SELECT COUNT(*)       FROM reviews r WHERE r.listing_id=l.id),0) AS reviews_count,
               EXISTS(SELECT 1 FROM wishlists w WHERE w.user_id=:uid AND w.listing_id=l.id) AS is_wishlisted
        FROM listings l WHERE l.available=1
        ORDER BY l.featured DESC, l.views DESC LIMIT 6
    ");
    $stmt->execute(['uid'=>$userId]);
    $listings = $stmt->fetchAll();
} catch (PDOException $e) {
    $listings=[]; $activeCount=$verifiedCount=$reviewCount=0;
}

function badgeClass($type){ return match($type){ 'rent'=>'badge-rent','sale'=>'badge-sale','shortlet'=>'badge-shortlet',default=>'badge-rent'}; }
function badgeLabel($type){ return match($type){ 'rent'=>'For Rent','sale'=>'For Sale','shortlet'=>'Short Stay',default=>'For Rent'}; }
function typeIcon($type){ return match($type){ 'apartment'=>'🏢','villa'=>'🏰','house'=>'🏡','studio'=>'🛋️','commercial'=>'🏬',default=>'🏠'}; }
function starHtml($rating, $count=0) {
    $full  = floor($rating);
    $empty = 5 - ceil($rating);
    $s = '<div class="star-rating-display" style="display:inline-flex;gap:2px;">';
    for($i=0;$i<$full;$i++)  $s.='<span class="star">★</span>';
    if($rating-$full>=0.5)   $s.='<span class="star">★</span>';
    for($i=0;$i<$empty;$i++) $s.='<span class="star empty">☆</span>';
    $s.='</div>';
    return $s;
}
?>

<!-- ══ HERO ══════════════════════════════════════════════ -->
<section class="hero-section">
  <div class="hero-bg" style="background-image:url('images/hero-kigali.png');"></div>

  <div class="container hero-content">
    <div class="animate-fade-in-up">
      <span class="hero-eyebrow">📍 Rwanda's #1 Property Platform</span>
      <h1 class="hero-title">Find Your Perfect<br><span>Rwanda Home</span></h1>
      <p class="hero-desc">
        Discover verified luxury rentals, sales, and short stays across Kigali, Rubavu, Musanze and beyond — with GPS maps, UPI land validation, and MoMo-protected bookings.
      </p>
    </div>

    <!-- Search Form -->
    <div class="animate-fade-in-up stagger-2" style="position:relative; max-width:900px;">
      <form action="listings.php" method="GET" class="search-form-wrap">
        <div class="search-field">
          <label>Location</label>
          <input type="text" name="search" id="heroSearch" placeholder="Kiyovu, Nyarutarama..." autocomplete="off">
        </div>
        <div class="search-field">
          <label>Type</label>
          <select name="listing">
            <option value="">Any Mode</option>
            <option value="rent">For Rent</option>
            <option value="sale">For Sale</option>
            <option value="shortlet">Short Stay</option>
          </select>
        </div>
        <div class="search-field">
          <label>Property</label>
          <select name="type">
            <option value="">Any Type</option>
            <option value="apartment">Apartment</option>
            <option value="villa">Villa</option>
            <option value="house">House</option>
            <option value="studio">Studio</option>
          </select>
        </div>
        <div class="search-field">
          <label>Guests</label>
          <select name="guests">
            <option value="">Any</option>
            <option value="2">2+</option>
            <option value="4">4+</option>
            <option value="6">6+</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary" style="border-radius:1rem;margin:0.25rem;padding:0.85rem 1.75rem;white-space:nowrap;">
          🔍 Search
        </button>
      </form>

      <!-- Instant-search dropdown -->
      <div class="search-dropdown" id="heroDropdown" style="display:none;"></div>
    </div>

    <!-- Category pills -->
    <div class="category-pills animate-fade-in-up stagger-3">
      <a href="listings.php?type=apartment" class="category-pill">🏢 Apartments</a>
      <a href="listings.php?type=villa"     class="category-pill">🏰 Villas</a>
      <a href="listings.php?type=house"     class="category-pill">🏡 Houses</a>
      <a href="listings.php?listing=shortlet" class="category-pill">🌊 Lake Cottages</a>
      <a href="listings.php?search=musanze" class="category-pill">🌋 Mountain Lodges</a>
      <a href="listings.php?verified=1"     class="category-pill">✓ Verified Only</a>
    </div>

    <!-- Hero stats -->
    <div class="hero-stats animate-fade-in-up stagger-4">
      <div class="hero-stat"><strong><?php echo $activeCount; ?>+</strong><span>Listings</span></div>
      <div class="hero-stat"><strong><?php echo $verifiedCount; ?>+</strong><span>UPI Verified</span></div>
      <div class="hero-stat"><strong><?php echo $reviewCount; ?>+</strong><span>Reviews</span></div>
      <div class="hero-stat"><strong>100%</strong><span>Escrow Safe</span></div>
    </div>
  </div>
</section>

<!-- ══ STATS STRIP ══════════════════════════════════════ -->
<div class="stats-strip">
  <div class="container stats-strip-grid">
    <div class="stat-item"><h3>6+</h3><p>Districts Covered</p></div>
    <div class="stat-item"><h3>MTN</h3><p>MoMo Escrow</p></div>
    <div class="stat-item"><h3>RURA</h3><p>UPI Validated</p></div>
    <div class="stat-item"><h3>24/7</h3><p>Support Chat</p></div>
  </div>
</div>

<!-- ══ FEATURED LISTINGS ════════════════════════════════ -->
<section class="section">
  <div class="container">
    <div class="section-header" style="display:flex;justify-content:space-between;align-items:flex-end;gap:1rem;flex-wrap:wrap;">
      <div>
        <span class="section-label">Featured Properties</span>
        <h2 class="section-title">Top Picks Across Rwanda</h2>
        <p class="section-subtitle">Curated, verified properties in prime locations — from city penthouses to lake cottages.</p>
      </div>
      <a href="listings.php" class="btn btn-secondary">Explore All →</a>
    </div>

    <?php if(empty($listings)): ?>
      <div class="glass-panel" style="padding:4rem;text-align:center;color:var(--text-muted);">
        <p style="font-size:1.5rem;margin-bottom:0.5rem;">🏗️</p>
        <p>No listings found. Database seeding in progress...</p>
      </div>
    <?php else: ?>
      <div class="property-grid property-grid-3col">
        <?php foreach($listings as $l): ?>
          <?php
          $imgs = explode(',', $l['images']);
          $img  = trim($imgs[0]) ?: 'images/prop1.jpg';
          $fav  = $l['is_wishlisted'];
          $rating = (float)$l['avg_rating'];
          $rcount = (int)$l['reviews_count'];
          ?>
          <article class="glass-panel property-card animate-fade-in" onclick="location.href='listing-detail.php?id=<?php echo $l['id']; ?>'">
            <!-- Heart -->
            <button class="card-wish-btn <?php echo $fav ? 'active':''; ?>"
                    id="heart-<?php echo $l['id']; ?>"
                    onclick="toggleWishlist(event,<?php echo $l['id']; ?>)">♥</button>

            <!-- Image -->
            <div class="card-image-wrap">
              <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($l['title']); ?>"
                   loading="lazy" onerror="this.src='images/prop1.jpg'">
              <div class="card-badges">
                <span class="badge <?php echo badgeClass($l['listing']); ?>"><?php echo badgeLabel($l['listing']); ?></span>
                <?php if($l['verified']): ?><span class="badge badge-verified">✓ UPI</span><?php endif; ?>
              </div>
            </div>

            <!-- Body -->
            <div class="card-body">
              <div class="card-location">
                <span>📍 <?php echo htmlspecialchars($l['district']); ?> · <?php echo typeIcon($l['type']); ?> <?php echo ucfirst($l['type']); ?></span>
                <?php if($rating>0): ?>
                  <span class="card-rating">★ <?php echo number_format($rating,2); ?>
                    <span style="color:var(--text-muted);font-weight:400;">(<?php echo $rcount; ?>)</span>
                  </span>
                <?php else: ?>
                  <span style="font-size:0.75rem;color:var(--accent-primary);font-weight:700;">New</span>
                <?php endif; ?>
              </div>

              <h3 class="card-title"><?php echo htmlspecialchars($l['title']); ?></h3>

              <div class="card-specs">
                <span>🛏 <?php echo $l['beds']; ?> Beds</span>
                <span>🛁 <?php echo $l['baths']; ?> Baths</span>
                <span>👥 <?php echo $l['max_guests']; ?> Guests</span>
                <?php if($l['sqm']): ?><span>📐 <?php echo $l['sqm']; ?>m²</span><?php endif; ?>
              </div>

              <div class="card-price">
                <strong><?php echo number_format($l['price']); ?></strong>
                <span><?php echo $l['currency']; ?> / <?php echo $l['period']; ?></span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ══ NEIGHBORHOODS ════════════════════════════════════ -->
<section class="section" style="background:var(--bg-secondary);padding:5rem 0;">
  <div class="container">
    <div class="section-header centered">
      <span class="section-label">Explore by Area</span>
      <h2 class="section-title">Top Rwanda Neighborhoods</h2>
      <p class="section-subtitle">From Kigali's diplomatic zones to the shores of Lake Kivu — find your perfect location.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
      <?php
      $neighborhoods = [
        ['name'=>'Kiyovu','district'=>'Kiyovu','emoji'=>'🏙️','desc'=>'Luxury embassy zone'],
        ['name'=>'Nyarutarama','district'=>'Nyarutarama','emoji'=>'⛳','desc'=>'Golf course villas'],
        ['name'=>'Kimihurura','district'=>'Kimihurura','emoji'=>'☕','desc'=>'Cafés & coworking'],
        ['name'=>'Kibagabaga','district'=>'Kibagabaga','emoji'=>'🌳','desc'=>'Suburban family homes'],
        ['name'=>'Lake Kivu','district'=>'Rubavu','emoji'=>'🌊','desc'=>'Lakefront cottages'],
        ['name'=>'Musanze','district'=>'Musanze','emoji'=>'🌋','desc'=>'Volcano view lodges'],
      ];
      foreach($neighborhoods as $n): 
        try {
          $cnt = $pdo->prepare("SELECT COUNT(*) FROM listings WHERE district=? AND available=1");
          $cnt->execute([$n['district']]);
          $listingCnt = (int)$cnt->fetchColumn();
        } catch(Exception $e){ $listingCnt=0; }
      ?>
        <a href="listings.php?search=<?php echo urlencode($n['district']); ?>" class="glass-panel" style="padding:1.5rem;display:flex;flex-direction:column;gap:0.5rem;border-radius:1rem;transition:all 0.25s;text-decoration:none;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
          <span style="font-size:2rem;"><?php echo $n['emoji']; ?></span>
          <strong style="font-size:1rem;color:var(--text-primary);"><?php echo $n['name']; ?></strong>
          <span style="font-size:0.78rem;color:var(--text-muted);"><?php echo $n['desc']; ?></span>
          <span style="font-size:0.75rem;font-weight:800;color:var(--accent-primary);margin-top:0.5rem;"><?php echo $listingCnt; ?> listing<?php echo $listingCnt!=1?'s':''; ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ WHY IMARA ════════════════════════════════════════ -->
<section class="section">
  <div class="container">
    <div class="section-header centered">
      <span class="section-label">Why Imara.rw</span>
      <h2 class="section-title">Built for Rwanda. Built to Trust.</h2>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1.5rem;">
      <?php
      $features = [
        ['icon'=>'🔐','title'=>'MTN MoMo Escrow','desc'=>'10% commitment deposits are held securely. Auto-refunded if landlord cancels — zero risk for tenants.'],
        ['icon'=>'🏛️','title'=>'RURA UPI Validation','desc'=>'Every property is cross-checked against Rwanda\'s official RURA land registry for verified ownership.'],
        ['icon'=>'🗺️','title'=>'GPS Precision Maps','desc'=>'Exact GPS coordinates with Leaflet-powered interactive maps so you know exactly where the property is.'],
        ['icon'=>'💬','title'=>'Direct Messaging','desc'=>'Talk directly to landlords and agents inside the platform. No need for third-party WhatsApp or emails.'],
        ['icon'=>'⭐','title'=>'Verified Reviews','desc'=>'Star ratings from real tenants who completed their stays. No fake reviews — only authenticated guests can post.'],
        ['icon'=>'📱','title'=>'Mobile Optimized','desc'=>'Fully responsive on every device. Browse, book, and manage from your smartphone anywhere in Rwanda.'],
      ];
      foreach($features as $f): ?>
        <div class="glass-panel feature-card animate-fade-in">
          <div class="feature-icon"><?php echo $f['icon']; ?></div>
          <h3 class="feature-title"><?php echo $f['title']; ?></h3>
          <p class="feature-desc"><?php echo $f['desc']; ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ TESTIMONIALS ══════════════════════════════════════ -->
<section class="section" style="background:var(--bg-secondary);">
  <div class="container">
    <div class="section-header centered">
      <span class="section-label">Guest Stories</span>
      <h2 class="section-title">Loved Across Rwanda</h2>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
      <?php
      $testimonials = [
        ['name'=>'Amina Uwase','role'=>'Business Traveler, Kigali','text'=>'Found the perfect serviced apartment in Kiyovu in under 10 minutes. The MoMo escrow gave me confidence to book without seeing it first. Highly recommended!','stars'=>5],
        ['name'=>'Jean-Paul Habimana','role'=>'Gorilla Safari Tourist','text'=>'Booked the Musanze volcano lodge for 3 nights and it was magical. The UPI verification meant I knew the property was 100% legit. The lake views were breathtaking.','stars'=>5],
        ['name'=>'Solange Mukamana','role'=>'Expat, Nyarutarama','text'=>'As an expat finding verified long-term rentals was always stressful. Imara made it simple and the escrow protection meant my deposit was always safe.','stars'=>5],
      ];
      foreach($testimonials as $t): ?>
        <div class="glass-panel testimonial-card">
          <div class="testimonial-stars"><?php echo str_repeat('★',$t['stars']); ?></div>
          <p class="testimonial-text">"<?php echo $t['text']; ?>"</p>
          <div class="testimonial-author">
            <div class="testimonial-avatar"><?php echo strtoupper(substr($t['name'],0,1)); ?></div>
            <div>
              <div class="testimonial-name"><?php echo $t['name']; ?></div>
              <div class="testimonial-role"><?php echo $t['role']; ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ CTA BANNER ═══════════════════════════════════════ -->
<section class="section-sm">
  <div class="container">
    <div class="glass-panel" style="padding:3.5rem;text-align:center;background:linear-gradient(135deg,var(--accent-primary-glow),rgba(139,92,246,0.1));border-color:rgba(62,207,207,0.2);">
      <h2 style="font-size:clamp(1.75rem,3vw,2.5rem);">Ready to Find Your Home?</h2>
      <p style="color:var(--text-secondary);margin:1rem auto 2rem;max-width:500px;">
        Join thousands of Rwandans using Imara to find, rent, and sell properties safely.
      </p>
      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
        <a href="listings.php" class="btn btn-primary btn-lg">Browse Properties</a>
        <a href="signup.php"   class="btn btn-secondary btn-lg">Create Free Account</a>
      </div>
    </div>
  </div>
</section>

<!-- Instant search JS -->
<script>
(function(){
  const input = document.getElementById('heroSearch');
  const drop  = document.getElementById('heroDropdown');
  let timer;

  input.addEventListener('input', function(){
    clearTimeout(timer);
    const q = this.value.trim();
    if(q.length < 2){ drop.style.display='none'; return; }
    timer = setTimeout(() => {
      fetch(`api/search.php?q=${encodeURIComponent(q)}&limit=6`)
        .then(r=>r.json())
        .then(data => {
          if(!data.results||!data.results.length){ drop.style.display='none'; return; }
          drop.innerHTML = data.results.map(p=>`
            <div class="search-result-item" onclick="location.href='listing-detail.php?id=${p.id}'">
              <img class="search-result-thumb" src="${p.image||'images/prop1.jpg'}" onerror="this.src='images/prop1.jpg'" alt="">
              <div class="search-result-info">
                <strong>${p.title}</strong>
                <span>📍 ${p.district} · ${p.type}</span>
              </div>
              <span class="search-result-price">${Number(p.price).toLocaleString()} RWF</span>
            </div>`).join('');
          drop.style.display='block';
        }).catch(()=>{});
    }, 280);
  });

  document.addEventListener('click', e=>{
    if(!e.target.closest('.search-form-wrap')) drop.style.display='none';
  });
})();
</script>

<?php require_once 'footer.php'; ?>
