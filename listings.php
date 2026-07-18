<?php
require_once 'header.php';
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

$search      = isset($_GET['search'])     ? trim($_GET['search']) : '';
$listingType = isset($_GET['listing'])    ? $_GET['listing']      : '';
$propType    = isset($_GET['type'])       ? $_GET['type']         : '';
$beds        = isset($_GET['beds'])       ? $_GET['beds']         : '';
$guests      = isset($_GET['guests'])     ? (int)$_GET['guests']  : 0;
$minPrice    = isset($_GET['min_price'])  ? (int)$_GET['min_price']: 0;
$maxPrice    = isset($_GET['max_price'])  ? (int)$_GET['max_price']: 0;
$verified    = !empty($_GET['verified'])  ? 1 : 0;
$sort        = isset($_GET['sort'])       ? $_GET['sort'] : 'newest';

$query  = "SELECT l.*,
           COALESCE((SELECT AVG(r.rating) FROM reviews r WHERE r.listing_id=l.id),0) AS avg_rating,
           COALESCE((SELECT COUNT(*)       FROM reviews r WHERE r.listing_id=l.id),0) AS reviews_count,
           EXISTS(SELECT 1 FROM wishlists w WHERE w.user_id=:uid AND w.listing_id=l.id) AS is_wishlisted
           FROM listings l WHERE l.available=1";
$params = ['uid'=>$userId];

if ($search)      { $query.=" AND (l.title LIKE :s OR l.district LIKE :s OR l.description LIKE :s)"; $params['s']='%'.$search.'%'; }
if ($listingType) { $query.=" AND l.listing=:lt"; $params['lt']=$listingType; }
if ($propType)    { $query.=" AND l.type=:pt";    $params['pt']=$propType; }
if ($beds)        { $query.=" AND l.beds>=:beds";  $params['beds']=(int)$beds; }
if ($guests>0)    { $query.=" AND l.max_guests>=:guests"; $params['guests']=$guests; }
if ($minPrice>0)  { $query.=" AND l.price>=:minp"; $params['minp']=$minPrice; }
if ($maxPrice>0)  { $query.=" AND l.price<=:maxp"; $params['maxp']=$maxPrice; }
if ($verified)    { $query.=" AND l.verified=1"; }

switch($sort) {
    case 'price_asc':  $query.=" ORDER BY l.price ASC";  break;
    case 'price_desc': $query.=" ORDER BY l.price DESC"; break;
    case 'rating':     $query.=" ORDER BY avg_rating DESC, l.views DESC"; break;
    default:           $query.=" ORDER BY l.created_at DESC"; break;
}

try { $stmt=$pdo->prepare($query); $stmt->execute($params); $items=$stmt->fetchAll(); }
catch (PDOException $e) { $items=[]; }

function typeIcon($t){ return match($t){'apartment'=>'🏢','villa'=>'🏰','house'=>'🏡','studio'=>'🛋️','commercial'=>'🏬',default=>'🏠'}; }
function badgeClass($t){ return match($t){'rent'=>'badge-rent','sale'=>'badge-sale','shortlet'=>'badge-shortlet',default=>'badge-rent'}; }
function badgeLabel($t){ return match($t){'rent'=>'For Rent','sale'=>'For Sale','shortlet'=>'Short Stay',default=>'For Rent'}; }
?>

<div style="padding-top:var(--navbar-h);min-height:100vh;">
  <div class="container" style="padding-top:2rem;padding-bottom:5rem;">

    <!-- Page header -->
    <div style="margin-bottom:2rem;">
      <h1 style="font-size:clamp(1.75rem,3vw,2.5rem);">Browse Properties</h1>
      <p style="color:var(--text-secondary);margin-top:0.35rem;">
        <?php echo count($items); ?> propert<?php echo count($items)!=1?'ies':'y'; ?> found<?php echo $search?' for "'.htmlspecialchars($search).'"':''; ?>.
      </p>
    </div>

    <div class="listings-layout">
      <!-- ── Sidebar Filters ───────────────────────────── -->
      <aside class="filter-sidebar">
        <div class="glass-panel" style="padding:1.5rem;border-radius:1.25rem;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
            <h3 style="font-size:1rem;font-weight:800;">Filters</h3>
            <a href="listings.php" class="btn btn-ghost btn-sm" style="padding:0.25rem 0.5rem;font-size:0.75rem;">Clear All</a>
          </div>

          <form action="listings.php" method="GET" id="filterForm">
            <!-- Search -->
            <div class="filter-group">
              <div class="filter-group-title">Keyword</div>
              <input type="text" name="search" class="glass-input" placeholder="District, keyword..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <!-- Listing Mode -->
            <div class="filter-group">
              <div class="filter-group-title">Listing Mode</div>
              <div style="display:flex;flex-direction:column;gap:0.4rem;">
                <?php foreach([''=>'Any','rent'=>'For Rent','sale'=>'For Sale','shortlet'=>'Short Stay'] as $v=>$l): ?>
                  <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem;color:var(--text-secondary);">
                    <input type="radio" name="listing" value="<?php echo $v; ?>" <?php echo $listingType===$v?'checked':''; ?> style="accent-color:var(--accent-primary);">
                    <?php echo $l; ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Property Type -->
            <div class="filter-group">
              <div class="filter-group-title">Property Type</div>
              <select name="type" class="glass-input">
                <option value="">Any Type</option>
                <?php foreach(['apartment'=>'Apartment','house'=>'House','villa'=>'Villa','studio'=>'Studio','commercial'=>'Commercial'] as $v=>$l): ?>
                  <option value="<?php echo $v; ?>" <?php echo $propType===$v?'selected':''; ?>><?php echo $l; ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Price Range -->
            <div class="filter-group">
              <div class="filter-group-title">
                Price / Night (RWF)
                <span id="priceRangeLabel" style="font-weight:700;color:var(--text-primary);font-size:0.75rem;text-transform:none;">
                  <?php echo ($minPrice>0||$maxPrice>0) ? number_format($minPrice).'–'.number_format($maxPrice) : 'Any'; ?>
                </span>
              </div>
              <div style="display:flex;gap:0.5rem;">
                <input type="number" name="min_price" class="glass-input" placeholder="Min" value="<?php echo $minPrice?:'' ;?>" style="padding:0.5rem;">
                <input type="number" name="max_price" class="glass-input" placeholder="Max" value="<?php echo $maxPrice?:'' ;?>" style="padding:0.5rem;">
              </div>
            </div>

            <!-- Bedrooms -->
            <div class="filter-group">
              <div class="filter-group-title">Bedrooms</div>
              <div style="display:flex;gap:0.35rem;flex-wrap:wrap;">
                <?php foreach([''=>'Any','1'=>'1+','2'=>'2+','3'=>'3+','4'=>'4+'] as $v=>$l): ?>
                  <button type="button" onclick="this.form.beds.value='<?php echo $v;?>';this.form.submit();"
                    style="padding:0.3rem 0.75rem;border-radius:9999px;border:1.5px solid <?php echo $beds===$v?'var(--accent-primary)':'var(--border-glass)';?>;background:<?php echo $beds===$v?'var(--accent-primary-glow)':'transparent';?>;color:<?php echo $beds===$v?'var(--accent-primary)':'var(--text-secondary)';?>;font-size:0.78rem;font-weight:700;cursor:pointer;transition:all 0.2s;">
                    <?php echo $l; ?>
                  </button>
                <?php endforeach; ?>
                <input type="hidden" name="beds" value="<?php echo htmlspecialchars($beds); ?>">
              </div>
            </div>

            <!-- Guests -->
            <div class="filter-group">
              <div class="filter-group-title">Guests</div>
              <select name="guests" class="glass-input">
                <option value="">Any</option>
                <option value="2" <?php echo $guests==2?'selected':'';?>>2+ Guests</option>
                <option value="4" <?php echo $guests==4?'selected':'';?>>4+ Guests</option>
                <option value="6" <?php echo $guests==6?'selected':'';?>>6+ Guests</option>
                <option value="8" <?php echo $guests==8?'selected':'';?>>8+ Guests</option>
              </select>
            </div>

            <!-- Verified Only -->
            <div class="filter-group">
              <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem;font-weight:700;color:var(--text-primary);">
                <input type="checkbox" name="verified" <?php echo $verified?'checked':''; ?> style="accent-color:var(--accent-primary);width:1rem;height:1rem;">
                ✓ Verified UPI Only
              </label>
            </div>

            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">Apply Filters</button>
          </form>
        </div>
      </aside>

      <!-- ── Results Area ──────────────────────────────── -->
      <div>
        <!-- Sort + View toggle bar -->
        <div class="results-bar">
          <p style="font-size:0.85rem;color:var(--text-muted);">
            <strong style="color:var(--text-primary);"><?php echo count($items); ?></strong> results
          </p>
          <div style="display:flex;align-items:center;gap:0.75rem;">
            <form method="GET" id="sortForm" style="display:flex;align-items:center;gap:0.5rem;">
              <?php foreach(['search','listing','type','beds','guests','min_price','max_price','verified'] as $k): ?>
                <?php if(isset($_GET[$k])&&$_GET[$k]!==''): ?><input type="hidden" name="<?php echo $k;?>" value="<?php echo htmlspecialchars($_GET[$k]);?>"><?php endif; ?>
              <?php endforeach; ?>
              <label class="form-label" style="margin:0;white-space:nowrap;">Sort by:</label>
              <select name="sort" class="glass-input sort-select" onchange="document.getElementById('sortForm').submit()">
                <option value="newest"     <?php echo $sort==='newest'    ?'selected':'';?>>Newest</option>
                <option value="price_asc"  <?php echo $sort==='price_asc' ?'selected':'';?>>Price: Low → High</option>
                <option value="price_desc" <?php echo $sort==='price_desc'?'selected':'';?>>Price: High → Low</option>
                <option value="rating"     <?php echo $sort==='rating'    ?'selected':'';?>>Highest Rated</option>
              </select>
            </form>
            <div class="view-toggle">
              <button class="view-btn active" id="gridBtn" onclick="setView('grid')" title="Grid view">⊞</button>
              <button class="view-btn"         id="listBtn" onclick="setView('list')" title="List view">☰</button>
            </div>
          </div>
        </div>

        <!-- Grid -->
        <?php if(empty($items)): ?>
          <div class="glass-panel" style="padding:5rem 2rem;text-align:center;border-radius:1.25rem;">
            <div style="font-size:3rem;margin-bottom:1rem;">🔍</div>
            <h3 style="font-size:1.5rem;margin-bottom:0.5rem;">No Results</h3>
            <p style="color:var(--text-secondary);">Try different keywords or remove some filters.</p>
            <a href="listings.php" class="btn btn-primary" style="margin-top:1.5rem;">Clear Filters</a>
          </div>
        <?php else: ?>
          <div id="propertyGrid" class="property-grid property-grid-3col" style="gap:1.25rem;">
            <?php foreach($items as $l):
              $imgs = array_filter(array_map('trim', explode(',',$l['images'])));
              $img  = reset($imgs) ?: 'images/prop1.jpg';
              $rating = (float)$l['avg_rating'];
              $rcount = (int)$l['reviews_count'];
              $fav    = (bool)$l['is_wishlisted'];
            ?>
              <article class="glass-panel property-card animate-fade-in" onclick="location.href='listing-detail.php?id=<?php echo $l['id']; ?>'">
                <button class="card-wish-btn <?php echo $fav?'active':''; ?>"
                        id="heart-<?php echo $l['id']; ?>"
                        onclick="toggleWishlist(event,<?php echo $l['id']; ?>)">♥</button>
                <div class="card-image-wrap">
                  <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($l['title']); ?>"
                       loading="lazy" onerror="this.src='images/prop1.jpg'">
                  <div class="card-badges">
                    <span class="badge <?php echo badgeClass($l['listing']); ?>"><?php echo badgeLabel($l['listing']); ?></span>
                    <?php if($l['verified']): ?><span class="badge badge-verified">✓ UPI</span><?php endif; ?>
                  </div>
                </div>
                <div class="card-body">
                  <div class="card-location">
                    <span>📍 <?php echo htmlspecialchars($l['district']); ?> · <?php echo typeIcon($l['type']); ?> <?php echo ucfirst($l['type']); ?></span>
                    <?php if($rating>0): ?><span class="card-rating">★ <?php echo number_format($rating,2); ?></span>
                    <?php else: ?><span style="font-size:0.75rem;color:var(--accent-primary);font-weight:700;">New</span><?php endif; ?>
                  </div>
                  <h3 class="card-title"><?php echo htmlspecialchars($l['title']); ?></h3>
                  <div class="card-specs">
                    <span>🛏 <?php echo $l['beds']; ?></span>
                    <span>🛁 <?php echo $l['baths']; ?></span>
                    <span>👥 <?php echo $l['max_guests']; ?></span>
                    <?php if($l['sqm']): ?><span>📐 <?php echo $l['sqm']; ?>m²</span><?php endif; ?>
                  </div>
                  <div class="card-price">
                    <strong><?php echo number_format($l['price']); ?></strong>
                    <span><?php echo $l['currency']; ?> / night</span>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function setView(v) {
  const grid = document.getElementById('propertyGrid');
  if(!grid) return;
  document.getElementById('gridBtn').classList.toggle('active', v==='grid');
  document.getElementById('listBtn').classList.toggle('active', v==='list');
  if(v==='list') {
    grid.style.gridTemplateColumns='1fr';
    grid.querySelectorAll('.property-card').forEach(c=>{
      c.style.flexDirection='row'; c.style.maxHeight='140px';
      const img=c.querySelector('.card-image-wrap');
      if(img){ img.style.width='180px'; img.style.minWidth='180px'; img.style.aspectRatio='auto'; }
    });
  } else {
    grid.style.gridTemplateColumns='';
    grid.querySelectorAll('.property-card').forEach(c=>{
      c.style.flexDirection=''; c.style.maxHeight='';
      const img=c.querySelector('.card-image-wrap');
      if(img){ img.style.width=''; img.style.minWidth=''; img.style.aspectRatio=''; }
    });
  }
}
</script>

<?php require_once 'footer.php'; ?>
