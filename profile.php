<?php
require_once 'header.php';
$viewId  = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0);
$isSelf  = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $viewId;

if (!$viewId) { header("Location:login.php"); exit; }

try {
    $stmt = $pdo->prepare("SELECT id,name,email,phone,role,verified,created_at FROM users WHERE id=?");
    $stmt->execute([$viewId]);
    $profile = $stmt->fetch();

    $listings  = [];
    $revCount  = 0;
    $avgRating = 0;

    if ($profile) {
        if ($profile['role']==='landlord'||$profile['role']==='agent') {
            $lStmt = $pdo->prepare("
                SELECT l.*,COALESCE((SELECT AVG(r.rating) FROM reviews r WHERE r.listing_id=l.id),0) AS avg_rating
                FROM listings l WHERE l.owner_id=? AND l.available=1 ORDER BY l.featured DESC,l.views DESC");
            $lStmt->execute([$viewId]);
            $listings = $lStmt->fetchAll();
        }
        $rvStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews rv JOIN listings l ON rv.listing_id=l.id WHERE l.owner_id=?");
        $rvStmt->execute([$viewId]);
        $revCount = (int)$rvStmt->fetchColumn();

        $agStmt = $pdo->prepare("SELECT AVG(rv.rating) FROM reviews rv JOIN listings l ON rv.listing_id=l.id WHERE l.owner_id=?");
        $agStmt->execute([$viewId]);
        $avgRating = round((float)$agStmt->fetchColumn(),2);
    }
} catch (PDOException $e) { $profile=null; $listings=[]; }

if (!$profile) { echo '<div class="container" style="padding-top:10rem;text-align:center;">User not found.</div>'; require_once 'footer.php'; exit; }
?>

<div class="profile-hero">
  <div class="container" style="display:flex;gap:2rem;align-items:center;flex-wrap:wrap;">
    <div class="profile-avatar-lg"><?php echo strtoupper(substr($profile['name'],0,1)); ?></div>
    <div style="flex:1;min-width:200px;">
      <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-bottom:0.5rem;">
        <h1 style="font-size:clamp(1.5rem,3vw,2.25rem);"><?php echo htmlspecialchars($profile['name']); ?></h1>
        <?php if($profile['verified']): ?><span class="badge badge-verified">✓ Verified</span><?php endif; ?>
        <span class="badge" style="background:rgba(139,92,246,0.1);color:#8b5cf6;border-color:rgba(139,92,246,0.2);text-transform:capitalize;"><?php echo $profile['role']; ?></span>
      </div>
      <p style="color:var(--text-secondary);">Member since <?php echo date('F Y',strtotime($profile['created_at'])); ?></p>
      <?php if($avgRating>0): ?><p style="color:var(--accent-warning);font-weight:700;margin-top:0.35rem;">★ <?php echo number_format($avgRating,2); ?> · <?php echo $revCount; ?> reviews received</p><?php endif; ?>
    </div>
    <?php if($isSelf): ?>
      <a href="dashboard.php" class="btn btn-secondary">📊 My Dashboard</a>
    <?php endif; ?>
  </div>
</div>

<div class="container" style="padding-top:3rem;padding-bottom:5rem;">
  <!-- Stats row -->
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;margin-bottom:3rem;">
    <?php
    $pStats = [
      ['val'=>count($listings),'label'=>'Properties Listed','icon'=>'🏡'],
      ['val'=>$revCount,       'label'=>'Reviews Received', 'icon'=>'⭐'],
      ['val'=>$avgRating>0?number_format($avgRating,2):'—','label'=>'Avg Rating','icon'=>'★'],
    ];
    foreach($pStats as $s): ?>
      <div class="glass-panel stat-card">
        <div class="stat-card-icon"><?php echo $s['icon']; ?></div>
        <div class="stat-card-value"><?php echo $s['val']; ?></div>
        <div class="stat-card-label"><?php echo $s['label']; ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Listings -->
  <?php if(!empty($listings)): ?>
  <h2 style="font-size:1.5rem;margin-bottom:1.5rem;">
    <?php echo $isSelf?'Your Listings':'Properties by '.htmlspecialchars($profile['name']); ?>
  </h2>
  <div class="property-grid property-grid-3col">
    <?php foreach($listings as $l):
      $imgs=array_filter(array_map('trim',explode(',',$l['images'])));
      $img =reset($imgs)?:'images/prop1.jpg';
      $lr  =(float)$l['avg_rating'];
    ?>
      <article class="glass-panel property-card" onclick="location.href='listing-detail.php?id=<?php echo $l['id'];?>'">
        <div class="card-image-wrap">
          <img src="<?php echo htmlspecialchars($img);?>" alt="" loading="lazy" onerror="this.src='images/prop1.jpg'">
          <div class="card-badges">
            <span class="badge badge-rent"><?php echo $l['listing']==='rent'?'For Rent':($l['listing']==='sale'?'For Sale':'Short Stay'); ?></span>
            <?php if($l['verified']): ?><span class="badge badge-verified">✓ UPI</span><?php endif; ?>
          </div>
        </div>
        <div class="card-body">
          <div class="card-location">
            <span>📍 <?php echo htmlspecialchars($l['district']); ?></span>
            <?php if($lr>0): ?><span class="card-rating">★ <?php echo number_format($lr,2); ?></span><?php endif; ?>
          </div>
          <h3 class="card-title"><?php echo htmlspecialchars($l['title']); ?></h3>
          <div class="card-specs"><span>🛏 <?php echo $l['beds'];?></span><span>🛁 <?php echo $l['baths'];?></span><span>👥 <?php echo $l['max_guests'];?></span></div>
          <div class="card-price"><strong><?php echo number_format($l['price']);?></strong><span><?php echo $l['currency'];?>/night</span></div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php elseif($profile['role']==='landlord'||$profile['role']==='agent'): ?>
    <div class="glass-panel" style="padding:3rem;text-align:center;color:var(--text-muted);">No listings published yet.</div>
  <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
