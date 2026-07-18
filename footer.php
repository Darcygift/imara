<?php // footer.php — Premium Footer ?>
<footer class="footer-global">
  <div class="container">
    <div class="footer-top">
      <!-- Brand -->
      <div>
        <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:1rem;">
          <img src="images/logo.png" alt="Imara.rw" style="height:2rem;" onerror="this.style.display='none'">
          <span style="font-family:var(--font-display);font-weight:900;font-size:1.3rem;background:linear-gradient(135deg,var(--accent-primary),#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Imara.rw</span>
        </div>
        <p class="footer-brand-desc">Rwanda's most trusted property platform. Verified listings, GPS precision, and MoMo-secured escrow for peace of mind.</p>
        <div class="footer-trust" style="margin-top:1.5rem;">
          <span class="trust-badge"><span>🔐</span> MTN MoMo</span>
          <span class="trust-badge"><span>🏛️</span> RURA Verified</span>
        </div>
      </div>

      <!-- Explore -->
      <div class="footer-col">
        <h4>Explore</h4>
        <div class="footer-links">
          <a href="listings.php?type=apartment">Apartments</a>
          <a href="listings.php?type=villa">Villas</a>
          <a href="listings.php?type=house">Houses</a>
          <a href="listings.php?listing=shortlet">Short Stays</a>
          <a href="map.php">Interactive Map</a>
        </div>
      </div>

      <!-- Locations -->
      <div class="footer-col">
        <h4>Locations</h4>
        <div class="footer-links">
          <a href="listings.php?search=Kiyovu">Kiyovu, Kigali</a>
          <a href="listings.php?search=Nyarutarama">Nyarutarama</a>
          <a href="listings.php?search=Kimihurura">Kimihurura</a>
          <a href="listings.php?search=Rubavu">Rubavu / Gisenyi</a>
          <a href="listings.php?search=Musanze">Musanze / Ruhengeri</a>
        </div>
      </div>

      <!-- Company -->
      <div class="footer-col">
        <h4>Company</h4>
        <div class="footer-links">
          <a href="#">About Imara</a>
          <a href="#">List Your Property</a>
          <a href="#">Become an Agent</a>
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Service</a>
          <a href="signup.php">Register Free</a>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <span>© <?php echo date('Y'); ?> Imara.rw — All rights reserved. Rwanda Property Platform.</span>
      <div class="footer-trust">
        <span class="trust-badge" style="color:var(--accent-success);">✓ Secure Platform</span>
        <span class="trust-badge">Built for Rwanda 🇷🇼</span>
      </div>
    </div>
  </div>
</footer>
</body>
</html>
