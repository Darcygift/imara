<?php
require_once 'header.php';
if (!isset($_SESSION['user_id'])) { header("Location:login.php"); exit; }

$userId    = (int)$_SESSION['user_id'];
$userRole  = $_SESSION['user_role'] ?? 'tenant';
$isHost    = in_array($userRole, ['landlord','agent','admin']);
$success   = ''; $error = '';

// ── Handle POST: Create Listing ──────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='create_listing' && $isHost) {
    $f = $_POST;
    if (empty($f['title'])||empty($f['price'])||empty($f['district'])) {
        $error='Please fill required fields: Title, Price, District.';
    } else {
        try {
            $pdo->prepare("
                INSERT INTO listings (owner_id,title,description,type,listing,price,currency,period,beds,baths,sqm,amenities,images,district,address,lat,lng,upi,verified,max_guests,cleaning_fee,service_fee)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ")->execute([
                $userId, trim($f['title']), trim($f['description']??''), $f['type']??'apartment',
                $f['listing_mode']??'rent', (int)$f['price'], 'RWF', 'night',
                (int)($f['beds']??1), (int)($f['baths']??1), ($f['sqm']??(null)),
                trim($f['amenities']??''), trim($f['images']??'/images/prop1.jpg'),
                trim($f['district']), trim($f['address']??''),
                (float)($f['lat']??-1.9500), (float)($f['lng']??30.0619),
                trim($f['upi']??''), !empty($f['upi'])?1:0,
                (int)($f['max_guests']??2), (int)($f['cleaning_fee']??5000), (int)($f['service_fee']??2000)
            ]);
            $success='Property listing created successfully!';
        } catch (PDOException $e) { $error='DB error: '.$e->getMessage(); }
    }
}

// ── Handle GET actions ────────────────────────────────
if (isset($_GET['delete_id']) && $isHost) {
    try {
        $check=$pdo->prepare("SELECT id FROM listings WHERE id=? AND owner_id=?");
        $check->execute([(int)$_GET['delete_id'],$userId]);
        if($check->fetch()){ $pdo->prepare("DELETE FROM listings WHERE id=?")->execute([(int)$_GET['delete_id']]); $success='Listing deleted.'; }
    } catch(Exception $e){}
}
if (isset($_GET['booking_id'],$_GET['status']) && $isHost) {
    $bid=(int)$_GET['booking_id']; $bs=$_GET['status'];
    $esc=match($bs){'confirmed'=>'held','cancelled'=>'refunded',default=>'pending'};
    try {
        $chk=$pdo->prepare("SELECT id FROM bookings WHERE id=? AND landlord_id=?");
        $chk->execute([$bid,$userId]);
        if($chk->fetch()){
            $pdo->prepare("UPDATE bookings SET status=?,escrow_status=? WHERE id=?")->execute([$bs,$esc,$bid]);
            $success='Booking '.$bs.' successfully.';
        }
    } catch(Exception $e){}
}

// ── Fetch Dashboard Data ─────────────────────────────
$myListings=$bookingsIn=$myBookings=$threads=$wishlist=[];
$totalRevenue=0;
try {
    if ($isHost) {
        $myListings = $pdo->prepare("SELECT * FROM listings WHERE owner_id=? ORDER BY created_at DESC")->execute([$userId]) ? [] : [];
        $s=$pdo->prepare("SELECT * FROM listings WHERE owner_id=? ORDER BY created_at DESC"); $s->execute([$userId]); $myListings=$s->fetchAll();

        $s=$pdo->prepare("SELECT b.*,l.title AS prop_title,u.name AS tenant_name FROM bookings b JOIN listings l ON b.listing_id=l.id JOIN users u ON b.tenant_id=u.id WHERE b.landlord_id=? ORDER BY b.created_at DESC");
        $s->execute([$userId]); $bookingsIn=$s->fetchAll();
        $totalRevenue = array_sum(array_column(array_filter($bookingsIn,fn($b)=>$b['status']==='confirmed'),'total_price'));
    } else {
        $s=$pdo->prepare("SELECT b.*,l.title AS prop_title,u.name AS landlord_name FROM bookings b JOIN listings l ON b.listing_id=l.id JOIN users u ON b.landlord_id=u.id WHERE b.tenant_id=? ORDER BY b.created_at DESC");
        $s->execute([$userId]); $myBookings=$s->fetchAll();
    }
    $s=$pdo->prepare("SELECT DISTINCT u.id,u.name,u.role FROM (SELECT from_id AS pid FROM messages WHERE to_id=:u UNION SELECT to_id AS pid FROM messages WHERE from_id=:u) p JOIN users u ON p.pid=u.id"); $s->execute(['u'=>$userId]); $threads=$s->fetchAll();
    $s=$pdo->prepare("SELECT l.*,COALESCE((SELECT AVG(r.rating) FROM reviews r WHERE r.listing_id=l.id),0) AS avg_rating FROM wishlists w JOIN listings l ON w.listing_id=l.id WHERE w.user_id=?"); $s->execute([$userId]); $wishlist=$s->fetchAll();
} catch(Exception $e){}

$user = $_SESSION;
?>

<div class="container dashboard-layout">
  <!-- ── SIDEBAR ─────────────────────────────────── -->
  <aside class="dashboard-sidebar">
    <div class="glass-panel" style="padding:1.5rem;border-radius:1.25rem;margin-bottom:1.25rem;text-align:center;">
      <div class="avatar" style="width:4rem;height:4rem;font-size:1.5rem;margin:0 auto 0.75rem;">
        <?php echo strtoupper(substr($_SESSION['user_name'],0,1)); ?>
      </div>
      <strong style="font-size:1rem;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
      <p style="font-size:0.75rem;color:var(--text-muted);text-transform:capitalize;margin-top:0.2rem;"><?php echo $userRole; ?></p>
      <a href="profile.php" class="btn btn-secondary btn-sm" style="margin-top:0.875rem;width:100%;">View Profile</a>
    </div>

    <nav class="glass-panel dash-nav" style="padding:0.75rem;border-radius:1.25rem;">
      <button class="dash-nav-item active" onclick="showTab('overview',this)"><span class="nav-icon">📊</span> Overview</button>
      <?php if($isHost): ?>
        <button class="dash-nav-item" onclick="showTab('listings',this)"><span class="nav-icon">🏡</span> My Listings <span class="nav-badge"><?php echo count($myListings);?></span></button>
        <button class="dash-nav-item" onclick="showTab('bookings',this)"><span class="nav-icon">📥</span> Bookings In <?php if(count(array_filter($bookingsIn,fn($b)=>$b['status']==='pending'))>0): ?><span class="nav-badge"><?php echo count(array_filter($bookingsIn,fn($b)=>$b['status']==='pending'));?></span><?php endif;?></button>
      <?php else: ?>
        <button class="dash-nav-item" onclick="showTab('bookings',this)"><span class="nav-icon">📆</span> My Bookings <span class="nav-badge"><?php echo count($myBookings);?></span></button>
      <?php endif; ?>
      <button class="dash-nav-item" onclick="showTab('wishlist',this)"><span class="nav-icon">❤️</span> Wishlist <span class="nav-badge"><?php echo count($wishlist);?></span></button>
      <button class="dash-nav-item" onclick="showTab('messages',this)"><span class="nav-icon">💬</span> Messages <?php if(count($threads)>0): ?><span class="nav-badge"><?php echo count($threads);?></span><?php endif;?></button>
    </nav>
  </aside>

  <!-- ── CONTENT ───────────────────────────────── -->
  <main>
    <!-- Alerts -->
    <?php if($success): ?>
      <div style="background:rgba(34,211,160,0.1);border:1px solid rgba(34,211,160,0.3);color:var(--accent-success);padding:0.875rem 1rem;border-radius:0.875rem;margin-bottom:1.25rem;font-weight:700;display:flex;align-items:center;gap:0.5rem;">✓ <?php echo htmlspecialchars($success);?></div>
    <?php endif; ?>
    <?php if($error): ?>
      <div style="background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.3);color:var(--accent-danger);padding:0.875rem 1rem;border-radius:0.875rem;margin-bottom:1.25rem;font-weight:700;display:flex;align-items:center;gap:0.5rem;">⚠️ <?php echo htmlspecialchars($error);?></div>
    <?php endif; ?>

    <!-- TAB: OVERVIEW ─────────────────────────── -->
    <div id="tab-overview">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
        <h2 style="font-size:1.5rem;">Welcome back, <?php echo htmlspecialchars(explode(' ',$_SESSION['user_name'])[0]);?>! 👋</h2>
        <?php if($isHost): ?>
          <button class="btn btn-primary" onclick="openModal('addModal')">+ New Listing</button>
        <?php endif; ?>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;">
        <?php
        $cards = $isHost ? [
          ['icon'=>'🏡','val'=>count($myListings),'label'=>'Active Listings','trend'=>'+'.count($myListings).' total'],
          ['icon'=>'📥','val'=>count($bookingsIn),'label'=>'Total Bookings','trend'=>count(array_filter($bookingsIn,fn($b)=>$b['status']==='pending')).' pending'],
          ['icon'=>'💰','val'=>number_format($totalRevenue),'label'=>'Confirmed Revenue (RWF)','trend'=>'confirmed only'],
          ['icon'=>'💬','val'=>count($threads),'label'=>'Active Chats','trend'=>''],
          ['icon'=>'❤️','val'=>count($wishlist),'label'=>'Wishlisted','trend'=>''],
        ] : [
          ['icon'=>'📆','val'=>count($myBookings),'label'=>'My Bookings','trend'=>count(array_filter($myBookings,fn($b)=>$b['status']==='confirmed')).' confirmed'],
          ['icon'=>'❤️','val'=>count($wishlist),'label'=>'Wishlisted','trend'=>'properties saved'],
          ['icon'=>'💬','val'=>count($threads),'label'=>'Active Chats','trend'=>''],
        ];
        foreach($cards as $c): ?>
          <div class="glass-panel stat-card">
            <div class="stat-card-icon"><?php echo $c['icon']; ?></div>
            <div class="stat-card-value"><?php echo $c['val']; ?></div>
            <div class="stat-card-label"><?php echo $c['label']; ?></div>
            <?php if($c['trend']): ?><div class="stat-card-trend up"><?php echo $c['trend']; ?></div><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Escrow Info Card -->
      <div class="glass-panel" style="padding:1.75rem;border-radius:1.25rem;border-color:rgba(62,207,207,0.2);">
        <h3 style="font-size:1.1rem;margin-bottom:1rem;">🔐 How Escrow Works</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
          <?php $steps=[['1.','Tenant books & pays 10% deposit via MTN MoMo'],['2.','Funds held securely — landlord cannot withdraw yet'],['3.','After confirmed check-in, funds released to host'],['4.','If host cancels, tenant auto-refunded instantly']];
          foreach($steps as [$n,$t]): ?>
            <div style="display:flex;gap:0.75rem;align-items:flex-start;">
              <span style="background:var(--accent-primary-glow);color:var(--accent-primary);font-weight:900;font-size:0.8rem;padding:0.3rem 0.6rem;border-radius:50%;flex-shrink:0;"><?php echo $n;?></span>
              <p style="font-size:0.85rem;color:var(--text-secondary);line-height:1.5;"><?php echo $t;?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- TAB: LISTINGS ────────────────────────── -->
    <div id="tab-listings" style="display:none;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
        <h2 style="font-size:1.5rem;">My Property Listings</h2>
        <button class="btn btn-primary" onclick="openModal('addModal')">+ Add New</button>
      </div>
      <?php if(empty($myListings)): ?>
        <div class="glass-panel" style="padding:4rem;text-align:center;color:var(--text-muted);">No listings yet. Click "+ Add New" to get started.</div>
      <?php else: ?>
        <div class="property-grid property-grid-3col">
          <?php foreach($myListings as $l):
            $imgs=array_filter(array_map('trim',explode(',',$l['images']))); $img=reset($imgs)?:'images/prop1.jpg';
          ?>
            <div class="glass-panel" style="border-radius:1rem;overflow:hidden;">
              <div style="height:140px;position:relative;">
                <img src="<?php echo htmlspecialchars($img);?>" alt="" style="width:100%;height:100%;object-fit:cover;" onerror="this.src='images/prop1.jpg'">
                <span style="position:absolute;top:0.5rem;right:0.5rem;background:rgba(0,0,0,0.6);color:#fff;font-size:0.7rem;font-weight:700;padding:0.2rem 0.5rem;border-radius:9999px;">👁 <?php echo $l['views'];?></span>
              </div>
              <div style="padding:1rem;">
                <h4 style="font-size:0.9rem;font-weight:800;margin-bottom:0.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($l['title']);?></h4>
                <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:0.75rem;">📍 <?php echo htmlspecialchars($l['district']);?> · <?php echo number_format($l['price']);?> RWF/night</p>
                <div style="display:flex;gap:0.5rem;">
                  <a href="listing-detail.php?id=<?php echo $l['id'];?>" class="btn btn-secondary btn-sm" style="flex:1;text-align:center;">View</a>
                  <a href="dashboard.php?delete_id=<?php echo $l['id'];?>" class="btn btn-danger btn-sm" style="flex:1;text-align:center;" onclick="return confirm('Delete this listing?')">Delete</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- TAB: BOOKINGS ────────────────────────── -->
    <div id="tab-bookings" style="display:none;">
      <h2 style="font-size:1.5rem;margin-bottom:1.5rem;"><?php echo $isHost?'Bookings Received':'My Booking Requests'; ?></h2>
      <?php $bList = $isHost ? $bookingsIn : $myBookings; ?>
      <?php if(empty($bList)): ?>
        <div class="glass-panel" style="padding:4rem;text-align:center;color:var(--text-muted);">No bookings found.</div>
      <?php else: ?>
        <div class="glass-panel" style="border-radius:1.25rem;overflow:hidden;">
          <div style="overflow-x:auto;">
            <table class="data-table">
              <thead><tr>
                <th>Property / <?php echo $isHost?'Tenant':'Host';?></th>
                <th>Check In → Out</th>
                <th>Guests</th>
                <th>Total</th>
                <th>Escrow</th>
                <th>Status</th>
                <?php if($isHost): ?><th>Actions</th><?php endif; ?>
              </tr></thead>
              <tbody>
              <?php foreach($bList as $b):
                $d1=new DateTime($b['check_in']); $d2=new DateTime($b['check_out']);
                $nights=$d1->diff($d2)->days;
              ?>
                <tr>
                  <td>
                    <strong><?php echo htmlspecialchars($b['prop_title']);?></strong><br>
                    <span style="font-size:0.75rem;"><?php echo htmlspecialchars($isHost?$b['tenant_name']:$b['landlord_name']);?></span>
                  </td>
                  <td style="font-family:monospace;font-size:0.8rem;"><?php echo $b['check_in']??'—';?> →<br><?php echo $b['check_out']??'—';?> <span style="color:var(--text-muted);">(<?php echo $nights;?>n)</span></td>
                  <td style="text-align:center;"><?php echo $b['guests_count'];?></td>
                  <td><strong><?php echo number_format($b['total_price']);?> RWF</strong></td>
                  <td style="color:var(--accent-success);font-weight:700;"><?php echo number_format($b['deposit']);?> RWF<br><span style="font-size:0.7rem;text-transform:uppercase;color:var(--text-muted);"><?php echo $b['escrow_status'];?></span></td>
                  <td><span class="badge badge-<?php echo $b['status'];?>"><?php echo ucfirst($b['status']);?></span></td>
                  <?php if($isHost): ?>
                  <td>
                    <div style="display:flex;gap:0.35rem;flex-wrap:wrap;">
                      <a href="chat.php?user_id=<?php echo $b['tenant_id'];?>" class="btn btn-secondary btn-sm">Chat</a>
                      <?php if($b['status']==='pending'): ?>
                        <a href="dashboard.php?booking_id=<?php echo $b['id'];?>&status=confirmed" class="btn btn-success btn-sm">Approve</a>
                        <a href="dashboard.php?booking_id=<?php echo $b['id'];?>&status=cancelled" class="btn btn-danger btn-sm">Cancel</a>
                      <?php endif; ?>
                    </div>
                  </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- TAB: WISHLIST ────────────────────────── -->
    <div id="tab-wishlist" style="display:none;">
      <h2 style="font-size:1.5rem;margin-bottom:1.5rem;">❤️ Saved Favorites</h2>
      <?php if(empty($wishlist)): ?>
        <div class="glass-panel" style="padding:4rem;text-align:center;color:var(--text-muted);">
          <div style="font-size:3rem;margin-bottom:1rem;">❤️</div>
          <p>No favorites yet. Browse listings and tap the heart icon to save them here.</p>
          <a href="listings.php" class="btn btn-primary" style="margin-top:1.25rem;">Browse Properties</a>
        </div>
      <?php else: ?>
        <div class="property-grid property-grid-3col">
          <?php foreach($wishlist as $l):
            $imgs=array_filter(array_map('trim',explode(',',$l['images']))); $img=reset($imgs)?:'images/prop1.jpg';
            $lr=(float)$l['avg_rating'];
          ?>
            <div class="glass-panel" style="border-radius:1rem;overflow:hidden;position:relative;">
              <button class="card-wish-btn active" id="heart-<?php echo $l['id'];?>" onclick="removeWish(event,<?php echo $l['id'];?>)" style="top:0.5rem;right:0.5rem;">♥</button>
              <div style="height:140px;"><img src="<?php echo htmlspecialchars($img);?>" alt="" style="width:100%;height:100%;object-fit:cover;" onerror="this.src='images/prop1.jpg'"></div>
              <div style="padding:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.3rem;">
                  <span style="font-size:0.75rem;color:var(--text-muted);">📍 <?php echo htmlspecialchars($l['district']);?></span>
                  <?php if($lr>0): ?><span style="color:var(--accent-warning);font-weight:700;font-size:0.78rem;">★ <?php echo number_format($lr,2);?></span><?php endif;?>
                </div>
                <h4 style="font-size:0.9rem;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($l['title']);?></h4>
                <div style="display:flex;gap:0.5rem;margin-top:0.75rem;">
                  <a href="listing-detail.php?id=<?php echo $l['id'];?>" class="btn btn-primary btn-sm" style="flex:1;text-align:center;">Book Now</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- TAB: MESSAGES ───────────────────────── -->
    <div id="tab-messages" style="display:none;">
      <h2 style="font-size:1.5rem;margin-bottom:1.5rem;">💬 Conversations</h2>
      <?php if(empty($threads)): ?>
        <div class="glass-panel" style="padding:4rem;text-align:center;color:var(--text-muted);">No conversations yet. Contact a landlord from any property listing.</div>
      <?php else: ?>
        <div class="glass-panel" style="border-radius:1.25rem;overflow:hidden;">
          <?php foreach($threads as $t): ?>
            <a href="chat.php?user_id=<?php echo $t['id'];?>" class="chat-thread-item" style="text-decoration:none;">
              <div class="avatar"><?php echo strtoupper(substr($t['name'],0,1));?></div>
              <div style="flex:1;min-width:0;">
                <div class="chat-thread-name"><?php echo htmlspecialchars($t['name']);?></div>
                <div class="chat-thread-preview" style="color:var(--text-muted);"><?php echo ucfirst($t['role']);?> · Click to open chat</div>
              </div>
              <span class="btn btn-secondary btn-sm">Open →</span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<!-- ── Add Listing Modal ───────────────────────────── -->
<?php if($isHost): ?>
<div id="addModal" class="modal-backdrop" style="display:none;" onclick="if(event.target===this)closeModal('addModal')">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    <h2 style="font-size:1.5rem;margin-bottom:1.5rem;">🏡 Create New Listing</h2>
    <form action="dashboard.php" method="POST" style="display:flex;flex-direction:column;gap:1rem;">
      <input type="hidden" name="action" value="create_listing">
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;">
        <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="glass-input" required placeholder="Modern 3-Bed Apartment Kiyovu"></div>
        <div class="form-group"><label class="form-label">Rate/Night (RWF) *</label><input type="number" name="price" class="glass-input" required placeholder="75000"></div>
      </div>
      <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="glass-input" rows="3" placeholder="Describe the property..."></textarea></div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
        <div class="form-group"><label class="form-label">Max Guests</label><input type="number" name="max_guests" class="glass-input" value="2"></div>
        <div class="form-group"><label class="form-label">Cleaning Fee</label><input type="number" name="cleaning_fee" class="glass-input" value="10000"></div>
        <div class="form-group"><label class="form-label">Service Fee</label><input type="number" name="service_fee" class="glass-input" value="3000"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
        <div class="form-group"><label class="form-label">Type</label>
          <select name="type" class="glass-input"><option value="apartment">Apartment</option><option value="house">House</option><option value="villa">Villa</option><option value="studio">Studio</option><option value="commercial">Commercial</option></select></div>
        <div class="form-group"><label class="form-label">Mode</label>
          <select name="listing_mode" class="glass-input"><option value="rent">Short Stay</option><option value="sale">For Sale</option></select></div>
        <div class="form-group"><label class="form-label">UPI (optional)</label><input type="text" name="upi" class="glass-input" placeholder="1/03/09/02/..."></div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
        <div class="form-group"><label class="form-label">Beds</label><input type="number" name="beds" class="glass-input" value="1"></div>
        <div class="form-group"><label class="form-label">Baths</label><input type="number" name="baths" class="glass-input" value="1"></div>
        <div class="form-group"><label class="form-label">Area (sqm)</label><input type="number" name="sqm" class="glass-input" placeholder="120"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group"><label class="form-label">District *</label><input type="text" name="district" class="glass-input" required placeholder="Kiyovu"></div>
        <div class="form-group"><label class="form-label">Address</label><input type="text" name="address" class="glass-input" placeholder="KN 12 St, House 3"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group"><label class="form-label">Latitude</label><input type="text" name="lat" class="glass-input" value="-1.9500"></div>
        <div class="form-group"><label class="form-label">Longitude</label><input type="text" name="lng" class="glass-input" value="30.0619"></div>
      </div>
      <div class="form-group"><label class="form-label">Amenities (comma separated)</label><input type="text" name="amenities" class="glass-input" placeholder="WiFi, Pool, Security, Generator"></div>
      <div class="form-group"><label class="form-label">Image path</label><input type="text" name="images" class="glass-input" placeholder="/images/prop_kiyovu.png"></div>
      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">Publish Listing</button>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
function showTab(id, btn) {
  document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display='none');
  document.querySelectorAll('.dash-nav-item').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-'+id).style.display='block';
  if(btn) btn.classList.add('active');
}
function openModal(id)  { document.getElementById(id).style.display='flex'; document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).style.display='none'; document.body.style.overflow=''; }
function removeWish(e, id) {
  e.stopPropagation();
  const fd = new FormData(); fd.append('listing_id', id);
  fetch('api/toggle-wishlist.php',{method:'POST',body:fd})
    .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}
</script>

<?php require_once 'footer.php'; ?>
