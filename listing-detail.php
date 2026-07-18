<?php
require_once 'header.php';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if (!$id) { header("Location:listings.php"); exit; }

try {
    $pdo->prepare("UPDATE listings SET views=views+1 WHERE id=?")->execute([$id]);

    $stmt = $pdo->prepare("
        SELECT l.*,
               COALESCE((SELECT AVG(r.rating) FROM reviews r WHERE r.listing_id=l.id),0) AS avg_rating,
               COALESCE((SELECT COUNT(*)       FROM reviews r WHERE r.listing_id=l.id),0) AS reviews_count,
               EXISTS(SELECT 1 FROM wishlists w WHERE w.user_id=:uid AND w.listing_id=l.id) AS is_wishlisted,
               u.name AS owner_name, u.email AS owner_email
        FROM listings l
        JOIN users u ON l.owner_id=u.id
        WHERE l.id=:id
    ");
    $stmt->execute(['id'=>$id,'uid'=>$userId]);
    $listing = $stmt->fetch();

    $revStmt = $pdo->prepare("
        SELECT rv.*, u.name AS reviewer_name
        FROM reviews rv JOIN users u ON rv.user_id=u.id
        WHERE rv.listing_id=? ORDER BY rv.created_at DESC
    ");
    $revStmt->execute([$id]);
    $reviews = $revStmt->fetchAll();

    // Similar listings
    $simStmt = $pdo->prepare("
        SELECT l.id, l.title, l.district, l.price, l.currency, l.period, l.beds, l.images,
               COALESCE((SELECT AVG(r.rating) FROM reviews r WHERE r.listing_id=l.id),0) AS avg_rating
        FROM listings l
        WHERE l.id!=:id AND l.type=:type AND l.available=1
        ORDER BY l.views DESC LIMIT 3
    ");
    $simStmt->execute(['id'=>$id,'type'=>$listing['type']]);
    $similar = $simStmt->fetchAll();
} catch (PDOException $e) { $listing=null; $reviews=[]; $similar=[]; }

if (!$listing) {
    echo '<div class="container" style="padding-top:10rem;text-align:center;min-height:80vh;">';
    echo '<h2>Property Not Found</h2><p style="margin:1rem 0;color:var(--text-secondary);">This listing was removed or does not exist.</p>';
    echo '<a href="listings.php" class="btn btn-primary">Browse All Properties</a></div>';
    require_once 'footer.php'; exit;
}

$imgs   = array_filter(array_map('trim', explode(',', $listing['images'])));
if(empty($imgs)) $imgs = ['images/prop1.jpg'];
$rating = (float)$listing['avg_rating'];
$rcount = (int)$listing['reviews_count'];
$fav    = (bool)$listing['is_wishlisted'];

function typeIcon($t){ return match($t){ 'apartment'=>'🏢','villa'=>'🏰','house'=>'🏡','studio'=>'🛋️','commercial'=>'🏬',default=>'🏠'}; }
function badgeClass($t){ return match($t){ 'rent'=>'badge-rent','sale'=>'badge-sale','shortlet'=>'badge-shortlet',default=>'badge-rent'}; }
function badgeLabel($t){ return match($t){ 'rent'=>'For Rent','sale'=>'For Sale','shortlet'=>'Short Stay',default=>'For Rent'}; }
?>

<div style="padding-top:var(--navbar-h);">

<!-- ── GALLERY GRID ──────────────────────────────── -->
<div class="container" style="padding-top:1.5rem;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;gap:1rem;flex-wrap:wrap;">
    <a href="listings.php" class="btn btn-ghost btn-sm" style="gap:0.35rem;">← Back to Browse</a>
    <div style="display:flex;gap:0.75rem;">
      <button class="btn btn-secondary btn-sm" onclick="toggleWishlist(event,<?php echo $id;?>)" id="heart-<?php echo $id;?>" style="<?php echo $fav?'color:var(--accent-danger);border-color:rgba(248,113,113,0.3);':'';?>">
        ♥ <?php echo $fav ? 'Saved':'Save';?>
      </button>
      <button class="btn btn-secondary btn-sm" onclick="navigator.share && navigator.share({title:'<?php echo htmlspecialchars(addslashes($listing['title']));?>',url:location.href})">↗ Share</button>
    </div>
  </div>

  <!-- Photo grid (up to 3 visible) -->
  <div class="gallery-grid" style="margin-bottom:2rem;">
    <div class="gallery-main" style="overflow:hidden;border-radius:1rem 0 0 1rem;">
      <img src="<?php echo htmlspecialchars($imgs[0]); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>"
           style="width:100%;height:100%;object-fit:cover;cursor:pointer;" onclick="openGallery(0)"
           onerror="this.src='images/prop1.jpg'">
    </div>
    <?php if(isset($imgs[1])): ?>
    <div class="gallery-thumb" style="overflow:hidden;<?php echo !isset($imgs[2])?'border-radius:0 1rem 1rem 0;':'border-radius:0 1rem 0 0;';?>">
      <img src="<?php echo htmlspecialchars($imgs[1]); ?>" alt="" onclick="openGallery(1)"
           style="width:100%;height:100%;object-fit:cover;cursor:pointer;" onerror="this.src='images/prop1.jpg'">
    </div>
    <?php endif; ?>
    <?php if(isset($imgs[2])): ?>
    <div class="gallery-thumb" style="overflow:hidden;border-radius:0 0 1rem 0;position:relative;" data-count="+ <?php echo max(0,count($imgs)-3); ?> photos">
      <img src="<?php echo htmlspecialchars($imgs[2]); ?>" alt="" onclick="openGallery(2)"
           style="width:100%;height:100%;object-fit:cover;cursor:pointer;" onerror="this.src='images/prop1.jpg'">
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── MAIN CONTENT ──────────────────────────────── -->
<div class="container" style="display:grid;grid-template-columns:2fr 1fr;gap:3rem;padding-bottom:5rem;" class="detail-grid">

  <!-- LEFT: Property Info -->
  <div>
    <!-- Title block -->
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.75rem;">
      <span class="badge <?php echo badgeClass($listing['listing']); ?>"><?php echo badgeLabel($listing['listing']); ?></span>
      <?php if($listing['verified']): ?><span class="badge badge-verified">✓ Land Registry Verified</span><?php endif; ?>
      <?php if($listing['featured']): ?><span class="badge" style="background:rgba(139,92,246,0.12);color:#8b5cf6;border:1px solid rgba(139,92,246,0.2);">⭐ Featured</span><?php endif; ?>
    </div>

    <h1 style="font-size:clamp(1.75rem,4vw,2.75rem);margin-bottom:0.75rem;"><?php echo htmlspecialchars($listing['title']); ?></h1>

    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1.75rem;">
      <span style="color:var(--text-secondary);display:flex;align-items:center;gap:0.35rem;">
        📍 <?php echo htmlspecialchars($listing['address'] ? $listing['address'].', '.$listing['district'] : $listing['district']); ?>
      </span>
      <?php if($rating>0): ?>
        <span style="font-weight:700;color:var(--accent-warning);">★ <?php echo number_format($rating,2); ?></span>
        <span style="color:var(--text-muted);font-size:0.875rem;"><?php echo $rcount; ?> review<?php echo $rcount!=1?'s':''; ?></span>
      <?php endif; ?>
    </div>

    <!-- Specs strip -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;padding:1.5rem 0;border-top:1px solid var(--border-glass);border-bottom:1px solid var(--border-glass);margin-bottom:2rem;">
      <?php
      $specs = [
        ['label'=>'Per Night','val'=>number_format($listing['price']).' '.$listing['currency'],'icon'=>'💰'],
        ['label'=>'Bedrooms','val'=>$listing['beds'].' Beds','icon'=>'🛏'],
        ['label'=>'Bathrooms','val'=>$listing['baths'].' Baths','icon'=>'🛁'],
        ['label'=>'Max Guests','val'=>$listing['max_guests'].' People','icon'=>'👥'],
      ];
      if($listing['sqm']) $specs[] = ['label'=>'Area','val'=>$listing['sqm'].'m²','icon'=>'📐'];
      foreach($specs as $s): ?>
        <div style="text-align:center;">
          <div style="font-size:1.5rem;margin-bottom:0.25rem;"><?php echo $s['icon']; ?></div>
          <strong style="display:block;font-size:0.9rem;color:var(--text-primary);"><?php echo $s['val']; ?></strong>
          <span style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;"><?php echo $s['label']; ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Host info -->
    <div class="glass-panel" style="padding:1.25rem;border-radius:1rem;display:flex;align-items:center;gap:1rem;margin-bottom:2rem;">
      <div class="avatar" style="width:3rem;height:3rem;font-size:1.2rem;">
        <?php echo strtoupper(substr($listing['owner_name'],0,1)); ?>
      </div>
      <div>
        <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.15rem;">Hosted by</div>
        <strong style="font-size:1rem;"><?php echo htmlspecialchars($listing['owner_name']); ?></strong>
        <?php if($listing['verified']): ?>
          <span class="badge badge-verified btn-sm" style="margin-left:0.5rem;">Verified Host</span>
        <?php endif; ?>
      </div>
      <?php if($userId && $userId !== (int)$listing['owner_id']): ?>
        <a href="chat.php?user_id=<?php echo $listing['owner_id']; ?>" class="btn btn-secondary btn-sm" style="margin-left:auto;">💬 Message</a>
      <?php endif; ?>
    </div>

    <!-- Description -->
    <h2 style="font-size:1.35rem;margin-bottom:1rem;">About this property</h2>
    <p style="color:var(--text-secondary);line-height:1.8;white-space:pre-wrap;font-size:0.95rem;margin-bottom:2rem;">
      <?php echo htmlspecialchars($listing['description']); ?>
    </p>

    <!-- Amenities -->
    <h2 style="font-size:1.35rem;margin-bottom:1rem;">Amenities</h2>
    <div style="display:flex;flex-wrap:wrap;gap:0.65rem;margin-bottom:2.5rem;">
      <?php foreach(explode(',', $listing['amenities']) as $a):
        if(!trim($a)) continue; ?>
        <span class="glass-panel" style="padding:0.5rem 1rem;border-radius:0.625rem;font-size:0.8rem;font-weight:700;">
          ✨ <?php echo htmlspecialchars(trim($a)); ?>
        </span>
      <?php endforeach; ?>
    </div>

    <!-- UPI Card -->
    <?php if(!empty($listing['upi'])): ?>
    <div class="glass-panel" style="padding:1.25rem 1.5rem;border-radius:1rem;border-color:rgba(34,211,160,0.2);background:rgba(34,211,160,0.03);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:2.5rem;" id="upiCard">
      <div>
        <div style="font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);margin-bottom:0.25rem;">RURA Land Registry UPI</div>
        <strong style="font-size:1.2rem;font-family:monospace;letter-spacing:0.08em;" id="upiVal"><?php echo htmlspecialchars($listing['upi']); ?></strong>
      </div>
      <button class="btn btn-secondary btn-sm" id="upiBtn" onclick="verifyUPI()">Verify in Registry</button>
    </div>
    <?php endif; ?>

    <!-- Map -->
    <h2 style="font-size:1.35rem;margin-bottom:1rem;">Location Map</h2>
    <div id="map" style="height:360px;border-radius:1.25rem;border:1px solid var(--border-glass);margin-bottom:3rem;"></div>

    <!-- Reviews -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:0.75rem;">
      <h2 style="font-size:1.5rem;">
        <?php if($rating>0): ?>
          ★ <?php echo number_format($rating,2); ?> · <?php echo $rcount; ?> Review<?php echo $rcount!=1?'s':''; ?>
        <?php else: ?>
          Guest Reviews
        <?php endif; ?>
      </h2>
    </div>

    <?php if(empty($reviews)): ?>
      <div class="glass-panel" style="padding:2.5rem;text-align:center;color:var(--text-muted);border-radius:1rem;margin-bottom:2rem;">
        No reviews yet. Be the first to review this property after your stay.
      </div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;margin-bottom:2rem;">
        <?php foreach($reviews as $rev): ?>
          <div class="glass-panel review-card">
            <div class="review-header">
              <div class="reviewer-info">
                <div class="reviewer-avatar"><?php echo strtoupper(substr($rev['reviewer_name'],0,1)); ?></div>
                <div>
                  <div class="reviewer-name"><?php echo htmlspecialchars($rev['reviewer_name']); ?></div>
                  <div class="reviewer-date"><?php echo date('M Y',strtotime($rev['created_at'])); ?></div>
                </div>
              </div>
              <div style="color:var(--accent-warning);font-weight:800;">★ <?php echo $rev['rating']; ?></div>
            </div>
            <p class="review-text">"<?php echo htmlspecialchars($rev['comment']); ?>"</p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Submit Review (logged in non-owner only) -->
    <?php if($userId > 0 && $userId !== (int)$listing['owner_id']): ?>
    <div class="glass-panel" style="padding:2rem;border-radius:1.25rem;margin-bottom:3rem;">
      <h3 style="font-size:1.15rem;margin-bottom:1.25rem;">Write a Review</h3>
      <form id="reviewForm" onsubmit="submitReview(event)" style="display:flex;flex-direction:column;gap:1rem;">
        <input type="hidden" name="listing_id" value="<?php echo $id; ?>">
        <div class="form-group">
          <label class="form-label">Your Rating</label>
          <div class="star-selector" id="starSel">
            <?php for($i=1;$i<=5;$i++): ?>
              <span class="s on" data-val="<?php echo $i; ?>" onclick="setStar(<?php echo $i; ?>)">★</span>
            <?php endfor; ?>
          </div>
          <input type="hidden" id="ratingVal" name="rating" value="5">
        </div>
        <div class="form-group">
          <label class="form-label">Your Comment *</label>
          <textarea name="comment" class="glass-input" rows="3" required placeholder="Share your experience at this property..."></textarea>
        </div>
        <button type="submit" class="btn btn-secondary" style="align-self:flex-start;border-radius:var(--radius-btn);" id="reviewBtn">Publish Review</button>
      </form>
      <div id="reviewMsg" style="margin-top:0.75rem;display:none;font-size:0.85rem;font-weight:700;"></div>
    </div>
    <?php endif; ?>

    <!-- Similar Listings -->
    <?php if(!empty($similar)): ?>
    <h2 style="font-size:1.35rem;margin-bottom:1.25rem;">Similar Properties</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;margin-bottom:2rem;">
      <?php foreach($similar as $s):
        $sImgs = array_filter(array_map('trim', explode(',',$s['images'])));
        $sImg  = reset($sImgs) ?: 'images/prop1.jpg';
      ?>
        <a href="listing-detail.php?id=<?php echo $s['id']; ?>" class="glass-panel" style="overflow:hidden;border-radius:1rem;display:block;text-decoration:none;transition:transform 0.25s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
          <img src="<?php echo htmlspecialchars($sImg); ?>" alt="" style="width:100%;height:120px;object-fit:cover;" onerror="this.src='images/prop1.jpg'">
          <div style="padding:0.875rem;">
            <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:0.25rem;">📍 <?php echo htmlspecialchars($s['district']); ?></div>
            <strong style="font-size:0.875rem;display:block;color:var(--text-primary);line-height:1.3;"><?php echo htmlspecialchars($s['title']); ?></strong>
            <div style="color:var(--accent-primary);font-weight:800;margin-top:0.5rem;font-size:0.9rem;"><?php echo number_format($s['price']); ?> <?php echo $s['currency']; ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- RIGHT: Booking Widget -->
  <div>
    <div class="glass-panel booking-widget" style="padding:2rem;">
      <!-- Price header -->
      <div style="display:flex;align-items:baseline;gap:0.5rem;margin-bottom:0.25rem;">
        <strong style="font-size:1.75rem;font-weight:900;"><?php echo number_format($listing['price']); ?></strong>
        <span style="color:var(--text-muted);font-size:0.9rem;"><?php echo $listing['currency']; ?> / night</span>
      </div>
      <?php if($rating>0): ?><div style="font-size:0.85rem;color:var(--accent-warning);margin-bottom:1.25rem;">★ <?php echo number_format($rating,2); ?> · <?php echo $rcount; ?> reviews</div>
      <?php else: ?><div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:1.25rem;">New listing</div><?php endif; ?>

      <!-- Multi-step booking form -->
      <!-- Step indicator -->
      <div class="step-indicator" id="stepIndicator">
        <div class="step-dot active" id="dot1">1</div>
        <div class="step-line" id="line1"></div>
        <div class="step-dot" id="dot2">2</div>
        <div class="step-line" id="line2"></div>
        <div class="step-dot" id="dot3">3</div>
      </div>

      <form id="bookForm" onsubmit="submitBooking(event)">
        <input type="hidden" name="listing_id" value="<?php echo $id; ?>">
        <input type="hidden" id="totalHidden" name="total_price" value="0">

        <!-- STEP 1: Dates + Guests -->
        <div id="step1">
          <h3 style="font-size:1rem;margin-bottom:1rem;">Choose dates & guests</h3>

          <!-- Date pair -->
          <div class="date-pair" style="margin-bottom:1rem;">
            <div class="date-half">
              <label>CHECK IN</label>
              <input type="date" id="checkIn" name="check_in" required onchange="computePrice()">
            </div>
            <div class="date-half">
              <label>CHECK OUT</label>
              <input type="date" id="checkOut" name="check_out" required onchange="computePrice()">
            </div>
          </div>

          <!-- Guests -->
          <div class="form-group" style="margin-bottom:1rem;">
            <label class="form-label">Guests</label>
            <select name="guests_count" class="glass-input">
              <?php for($i=1;$i<=$listing['max_guests'];$i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?> Guest<?php echo $i>1?'s':'';?></option>
              <?php endfor; ?>
            </select>
          </div>

          <!-- Live price breakdown -->
          <div id="priceBox" class="booking-invoice-card" style="display:none;">
            <div class="invoice-row"><span id="nightLabel">0 nights × <?php echo number_format($listing['price']); ?> RWF</span><span id="nightTotal">—</span></div>
            <div class="invoice-row"><span>Cleaning fee</span><span><?php echo number_format($listing['cleaning_fee']); ?> RWF</span></div>
            <div class="invoice-row"><span>Service fee</span><span><?php echo number_format($listing['service_fee']); ?> RWF</span></div>
            <div class="invoice-divider"></div>
            <div class="invoice-total"><span>Total</span><span id="grandTotal">—</span></div>
            <div class="invoice-escrow" style="margin-top:0.75rem;">
              <span>MoMo Escrow (10%)</span><span id="escrowAmt">—</span>
            </div>
          </div>

          <button type="button" class="btn btn-primary" style="width:100%;margin-top:1rem;" onclick="goStep(2)" id="step1Btn">
            Continue to Details →
          </button>
        </div>

        <!-- STEP 2: Contact Details -->
        <div id="step2" style="display:none;">
          <h3 style="font-size:1rem;margin-bottom:1rem;">Your contact details</h3>
          <div class="form-group" style="margin-bottom:0.875rem;">
            <label class="form-label">MTN MoMo Phone *</label>
            <input type="text" name="momo_phone" class="glass-input" required placeholder="e.g. 0788 000 000">
          </div>
          <div class="form-group" style="margin-bottom:0.875rem;">
            <label class="form-label">Special Requests</label>
            <textarea name="notes" class="glass-input" rows="2" placeholder="Early check-in, quiet room, etc."></textarea>
          </div>
          <div style="display:flex;gap:0.5rem;">
            <button type="button" class="btn btn-secondary" onclick="goStep(1)" style="flex:1;">← Back</button>
            <button type="button" class="btn btn-primary" onclick="goStep(3)" style="flex:2;">Review Booking →</button>
          </div>
        </div>

        <!-- STEP 3: Confirm -->
        <div id="step3" style="display:none;">
          <h3 style="font-size:1rem;margin-bottom:1rem;">Confirm Reservation</h3>
          <div id="confirmSummary" class="glass-panel" style="padding:1rem;border-radius:0.875rem;font-size:0.85rem;margin-bottom:1rem;display:flex;flex-direction:column;gap:0.5rem;"></div>
          <div class="glass-panel" style="padding:0.875rem;border-radius:0.75rem;margin-bottom:1rem;font-size:0.8rem;color:var(--accent-success);border-color:rgba(34,211,160,0.2);">
            🔒 Your deposit is held in escrow. If the host cancels, you get a full automatic refund.
          </div>
          <div style="display:flex;gap:0.5rem;">
            <button type="button" class="btn btn-secondary" onclick="goStep(2)" style="flex:1;">← Back</button>
            <button type="submit" class="btn btn-primary" id="confirmBtn" style="flex:2;">Pay MoMo Escrow ✓</button>
          </div>
        </div>
      </form>

      <div id="bookResult" style="display:none;margin-top:1rem;padding:1rem;border-radius:0.875rem;font-size:0.875rem;font-weight:700;"></div>
    </div>
  </div>
</div>
</div>

<!-- Gallery Modal -->
<div id="galleryModal" style="display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,0.95);align-items:center;justify-content:center;flex-direction:column;gap:1rem;">
  <button onclick="closeGallery()" style="position:absolute;top:1.5rem;right:1.5rem;background:rgba(255,255,255,0.15);border:none;color:#fff;width:2.5rem;height:2.5rem;border-radius:50%;font-size:1.25rem;cursor:pointer;">✕</button>
  <img id="galleryImg" src="" alt="" style="max-height:80vh;max-width:90vw;border-radius:0.5rem;object-fit:contain;">
  <div style="display:flex;gap:1rem;">
    <button onclick="galleryNav(-1)" class="btn btn-secondary" style="color:#fff;">← Prev</button>
    <span id="galleryCounter" style="color:rgba(255,255,255,0.6);font-size:0.875rem;align-self:center;"></span>
    <button onclick="galleryNav(1)" class="btn btn-secondary" style="color:#fff;">Next →</button>
  </div>
</div>

<script>
// ── Gallery ──────────────────────────────────────────
const galleryImgs = <?php echo json_encode(array_values($imgs)); ?>;
let galIdx = 0;
function openGallery(i) {
  galIdx = i; document.getElementById('galleryModal').style.display='flex';
  updateGallery(); document.body.style.overflow='hidden';
}
function closeGallery() { document.getElementById('galleryModal').style.display='none'; document.body.style.overflow=''; }
function galleryNav(d) { galIdx = (galIdx+d+galleryImgs.length)%galleryImgs.length; updateGallery(); }
function updateGallery() {
  document.getElementById('galleryImg').src = galleryImgs[galIdx];
  document.getElementById('galleryCounter').textContent = `${galIdx+1} / ${galleryImgs.length}`;
}
document.getElementById('galleryModal').addEventListener('click',e=>{ if(e.target===e.currentTarget) closeGallery(); });

// ── Price Calculator ─────────────────────────────────
const nightly = <?php echo intval($listing['price']); ?>;
const cleanFee = <?php echo intval($listing['cleaning_fee']); ?>;
const svcFee   = <?php echo intval($listing['service_fee']); ?>;

function computePrice() {
  const ci = document.getElementById('checkIn').value;
  const co = document.getElementById('checkOut').value;
  if (!ci||!co) return;
  const nights = Math.ceil((new Date(co)-new Date(ci))/(864e5));
  if (nights<=0) return;
  const base  = nightly*nights;
  const total = base+cleanFee+svcFee;
  const esc   = Math.round(total*0.1);
  document.getElementById('nightLabel').textContent  = `${nights} night${nights>1?'s':''} × ${nightly.toLocaleString()} RWF`;
  document.getElementById('nightTotal').textContent  = base.toLocaleString()+' RWF';
  document.getElementById('grandTotal').textContent  = total.toLocaleString()+' RWF';
  document.getElementById('escrowAmt').textContent   = esc.toLocaleString()+' RWF';
  document.getElementById('totalHidden').value       = total;
  document.getElementById('priceBox').style.display  = 'flex';
}

// ── Step Manager ─────────────────────────────────────
function goStep(n) {
  [1,2,3].forEach(i => {
    document.getElementById('step'+i).style.display = i===n?'block':'none';
    const dot = document.getElementById('dot'+i);
    dot.classList.toggle('active', i===n);
    dot.classList.toggle('done',   i<n);
  });
  [1,2].forEach(i => {
    const line = document.getElementById('line'+i);
    if(line) line.classList.toggle('done', i<n);
  });
  if (n===3) buildConfirmSummary();
}

function buildConfirmSummary() {
  const ci   = document.getElementById('checkIn').value;
  const co   = document.getElementById('checkOut').value;
  const nights = Math.ceil((new Date(co)-new Date(ci))/(864e5));
  const total  = nightly*nights+cleanFee+svcFee;
  const esc    = Math.round(total*0.1);
  document.getElementById('confirmSummary').innerHTML = `
    <div style="display:flex;justify-content:space-between;"><span>Property</span><strong style="color:var(--text-primary);max-width:180px;text-align:right;"><?php echo addslashes(htmlspecialchars($listing['title'])); ?></strong></div>
    <div style="display:flex;justify-content:space-between;"><span>Check In</span><strong style="color:var(--text-primary);">${ci}</strong></div>
    <div style="display:flex;justify-content:space-between;"><span>Check Out</span><strong style="color:var(--text-primary);">${co}</strong></div>
    <div style="display:flex;justify-content:space-between;"><span>Duration</span><strong style="color:var(--text-primary);">${nights} nights</strong></div>
    <div style="height:1px;background:var(--border-glass);margin:0.25rem 0;"></div>
    <div style="display:flex;justify-content:space-between;"><span style="font-weight:800;">Invoice Total</span><strong style="color:var(--accent-primary);font-size:1rem;">${total.toLocaleString()} RWF</strong></div>
    <div style="display:flex;justify-content:space-between;"><span style="color:var(--accent-success);">Escrow Now (10%)</span><strong style="color:var(--accent-success);">${esc.toLocaleString()} RWF</strong></div>
  `;
}

// ── Star Rating ──────────────────────────────────────
function setStar(v) {
  document.getElementById('ratingVal').value = v;
  document.querySelectorAll('#starSel .s').forEach((s,i)=>{
    s.classList.toggle('on', i<v);
  });
}

// ── Submit Booking ───────────────────────────────────
function submitBooking(e) {
  e.preventDefault();
  const btn = document.getElementById('confirmBtn');
  const res = document.getElementById('bookResult');
  btn.textContent='Processing...'; btn.disabled=true;
  const fd = new FormData(document.getElementById('bookForm'));
  fetch('api/book.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(d=>{
      if(d.success){
        res.style.cssText='display:block;background:var(--accent-success-glow);border:1px solid rgba(34,211,160,0.3);color:var(--accent-success);padding:1rem;border-radius:0.875rem;';
        res.textContent='✓ '+d.message;
        [1,2,3].forEach(i=>{ const s=document.getElementById('step'+i); if(s) s.style.display='none'; });
        setTimeout(()=>location.href='dashboard.php',2500);
      } else {
        btn.textContent='Pay MoMo Escrow ✓'; btn.disabled=false;
        showToast(d.error||'Booking failed','error');
        if(d.error&&(d.error.includes('sign in')||d.error.includes('auth'))) setTimeout(()=>location.href='login.php',1500);
      }
    }).catch(()=>{ btn.textContent='Pay MoMo Escrow ✓'; btn.disabled=false; showToast('Network error','error'); });
}

// ── Submit Review ────────────────────────────────────
function submitReview(e) {
  e.preventDefault();
  const btn = document.getElementById('reviewBtn');
  const msg = document.getElementById('reviewMsg');
  btn.textContent='Publishing...'; btn.disabled=true;
  fetch('api/submit-review.php',{method:'POST',body:new FormData(e.target)})
    .then(r=>r.json())
    .then(d=>{
      if(d.success){ showToast('Review published!','success'); setTimeout(()=>location.reload(),900); }
      else { btn.textContent='Publish Review'; btn.disabled=false; showToast(d.error,'error'); }
    }).catch(()=>{ btn.textContent='Publish Review'; btn.disabled=false; });
}

// ── UPI Verify ───────────────────────────────────────
function verifyUPI() {
  const upi = document.getElementById('upiVal').textContent;
  const btn = document.getElementById('upiBtn');
  btn.textContent='Querying RURA...'; btn.disabled=true;
  fetch('api/verify-upi.php?upi='+encodeURIComponent(upi))
    .then(r=>r.json())
    .then(d=>{
      if(d.success){
        document.getElementById('upiCard').style.borderColor='rgba(34,211,160,0.4)';
        btn.textContent='✓ Registry Verified'; btn.style.color='var(--accent-success)';
        showToast('UPI land registry match confirmed!','success');
      } else { btn.textContent='Verify in Registry'; btn.disabled=false; showToast('UPI verification failed','error'); }
    }).catch(()=>{ btn.textContent='Verify in Registry'; btn.disabled=false; });
}

// ── Map init ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded',()=>{
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('checkIn').min  = today;
  document.getElementById('checkOut').min = today;

  const map = L.map('map',{scrollWheelZoom:false})
    .setView([<?php echo floatval($listing['lat']); ?>, <?php echo floatval($listing['lng']); ?>], 15);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap'
  }).addTo(map);
  L.marker([<?php echo floatval($listing['lat']); ?>, <?php echo floatval($listing['lng']); ?>])
    .addTo(map)
    .bindPopup(`<b><?php echo addslashes(htmlspecialchars($listing['title']));?></b><br><?php echo number_format($listing['price']); ?> RWF/night`)
    .openPopup();
});
</script>

<style>
@media(max-width:768px){
  .detail-grid{grid-template-columns:1fr!important;}
  .booking-widget{position:static!important;}
  .gallery-grid{grid-template-columns:1fr!important;grid-template-rows:250px!important;}
}
</style>

<?php require_once 'footer.php'; ?>
