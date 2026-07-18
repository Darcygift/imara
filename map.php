<?php
// map.php - Interactive Geolocation Map View
require_once 'header.php';

// Fetch all available listings to plot on map
try {
    $stmt = $pdo->query("SELECT * FROM listings WHERE available = 1 ORDER BY price ASC");
    $listings = $stmt->fetchAll();
} catch (PDOException $e) {
    $listings = [];
}
?>

<div style="display: grid; grid-template-columns: 340px 1fr; height: calc(100vh - 4.5rem); padding-top: 4.5rem;" class="map-layout-grid">
    <!-- Listings Sidebar -->
    <div style="background-color: var(--bg-primary); border-right: 1px solid var(--border-glass); display: flex; flex-direction: column; overflow-y: auto;">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-glass); background: var(--bg-secondary);">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                📍 Map Explorer
            </h3>
            <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem;">
                Centering on <?php echo count($listings); ?> verified properties. Click pins to view details.
            </p>
        </div>
        
        <div style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 1px; background-color: var(--border-glass);">
            <?php if (empty($listings)): ?>
                <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                    No properties listed with valid geolocations.
                </div>
            <?php else: ?>
                <?php foreach ($listings as $idx => $item): ?>
                    <div class="chat-thread-item map-item-card" id="map-card-<?php echo $item['id']; ?>" onclick="selectMapItem(<?php echo $item['id']; ?>, <?php echo $item['lat']; ?>, <?php echo $item['lng']; ?>)" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-glass); cursor: pointer; transition: background var(--transition-speed) ease;">
                        <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($item['title']); ?></h4>
                        <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.15rem;">📍 <?php echo htmlspecialchars($item['district']); ?></p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem;">
                            <strong style="color: var(--accent-primary); font-size: 0.95rem;"><?php echo number_format($item['price']); ?> RWF</strong>
                            <a href="listing-detail.php?id=<?php echo $item['id']; ?>" style="font-size: 0.7rem; color: var(--text-muted); text-decoration: underline;">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Map Area -->
    <div style="position: relative; height: 100%;">
        <div id="explorer-map" style="width: 100%; height: 100%; z-index: 1;"></div>
        <div style="position: absolute; bottom: 1.5rem; left: 1.5rem; background: var(--bg-glass); backdrop-filter: blur(10px); border: 1px solid var(--border-glass); border-radius: 0.5rem; padding: 0.5rem 1rem; font-size: 0.7rem; color: var(--text-secondary); z-index: 1000; pointer-events: none; box-shadow: var(--card-shadow);">
            🗺️ OpenStreetMap Map Tiles • Imara Escrow Verified Pins
        </div>
    </div>
</div>

<script>
var map;
var markers = {};
var listingsData = <?php echo json_encode($listings); ?>;

document.addEventListener("DOMContentLoaded", function() {
    // Determine default center
    var centerLat = -1.9500;
    var centerLng = 30.0619;
    
    if (listingsData.length > 0) {
        centerLat = parseFloat(listingsData[0].lat);
        centerLng = parseFloat(listingsData[0].lng);
    }
    
    map = L.map('explorer-map', {
        zoomControl: false
    }).setView([centerLat, centerLng], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);
    
    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);
    
    // Plot all markers
    listingsData.forEach(function(item) {
        var price = parseInt(item.price);
        var priceText = price >= 1000000 
            ? (price / 1000000).toFixed(1) + 'M' 
            : Math.round(price / 1000) + 'k';
            
        // Render custom price tag icon
        var customIcon = L.divIcon({
            html: '<div class="price-pin-badge" id="pin-badge-' + item.id + '">' + priceText + '</div>',
            className: 'custom-pin-wrapper',
            iconSize: [50, 24],
            iconAnchor: [25, 12]
        });
        
        var marker = L.marker([parseFloat(item.lat), parseFloat(item.lng)], { icon: customIcon })
            .addTo(map)
            .on('click', function() {
                selectMapItem(item.id, item.lat, item.lng, true);
            });
            
        marker.bindPopup(
            '<div style="color:#0f172a; font-family:sans-serif; padding:0.25rem;">' +
            '<strong style="font-size:0.85rem; display:block;">' + escapeHtml(item.title) + '</strong>' +
            '<span style="color:#b45309; font-weight:700; font-size:0.8rem; display:block; margin-top:0.25rem;">RWF ' + parseInt(item.price).toLocaleString() + '</span>' +
            '<a href="listing-detail.php?id=' + item.id + '" style="font-size:0.75rem; color:#2563eb; text-decoration:underline; display:block; margin-top:0.5rem;">Visit Property Sheet →</a>' +
            '</div>',
            { closeButton: false }
        );
        
        markers[item.id] = marker;
    });
});

function selectMapItem(id, lat, lng, fromMarker = false) {
    // Reset highlights on cards
    document.querySelectorAll('.map-item-card').forEach(function(card) {
        card.classList.remove('active');
        card.style.background = 'transparent';
    });
    
    // Reset highlights on pin badges
    document.querySelectorAll('.price-pin-badge').forEach(function(badge) {
        badge.classList.remove('highlighted');
    });
    
    // Apply highlight to chosen card
    var selectedCard = document.getElementById('map-card-' + id);
    if (selectedCard) {
        selectedCard.classList.add('active');
        selectedCard.style.background = 'var(--bg-tertiary)';
        if (!fromMarker) {
            selectedCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
    
    // Apply highlight to chosen pin badge
    var selectedBadge = document.getElementById('pin-badge-' + id);
    if (selectedBadge) {
        selectedBadge.classList.add('highlighted');
    }
    
    // Pan map to location
    map.setView([parseFloat(lat), parseFloat(lng)], 15, { animate: true });
    
    // Open marker popup
    if (markers[id]) {
        markers[id].openPopup();
    }
}

function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>

<style>
/* Map CSS overrides */
.price-pin-badge {
    background-color: var(--accent-primary);
    border: 1.5px solid #ffffff;
    color: #ffffff;
    font-size: 0.65rem;
    font-weight: 800;
    text-align: center;
    border-radius: 0.35rem;
    padding: 0.2rem 0.4rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
    cursor: pointer;
    transition: all 0.2s ease;
}

body.theme-light .price-pin-badge {
    border-color: var(--accent-primary);
}

.price-pin-badge.highlighted {
    background-color: #ffffff !important;
    color: #0f172a !important;
    border-color: var(--accent-primary) !important;
    transform: scale(1.15);
    box-shadow: 0 0 12px var(--accent-primary);
}

.custom-pin-wrapper {
    overflow: visible !important;
}

@media (max-width: 768px) {
    .map-layout-grid {
        grid-template-columns: 1fr !important;
        grid-template-rows: 250px 1fr !important;
    }
}
</style>

<?php require_once 'footer.php'; ?>
