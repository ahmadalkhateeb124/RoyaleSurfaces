<main style="padding-top:120px;padding-bottom:96px;">
  <div class="container">
    <div class="products-header">
      <div>
        <h1>Slab Inventory</h1>
        <p class="muted" style="font-size:17px;margin-top:8px;">Browse our premium materials available for trade
          sourcing.</p>
      </div>
      <div class="filter-bar" id="filterBar">
        <button class="filter-btn active" onclick="filterProducts('All',this)">All</button>
        <button class="filter-btn" onclick="filterProducts('Granite',this)">Granite</button>
        <button class="filter-btn" onclick="filterProducts('Quartzite',this)">Quartzite</button>
        <button class="filter-btn" onclick="filterProducts('Marble',this)">Marble</button>
        <button class="filter-btn" onclick="filterProducts('Quartz',this)">Quartz</button>
      </div>
    </div>

    <div class="products-grid" id="productsGrid">
      <div class="product-card" data-type="Granite">
        <div class="product-card-img">
          <img src="<?= $base_url ?>assets/images/slab-granite.jpg" alt="Absolute Black granite slab from India"
            loading="lazy" />
          <span class="product-badge">Granite</span>
        </div>
        <div class="product-card-body">
          <h3>Absolute Black</h3>
          <div class="product-specs">
            <div class="spec-row"><span class="spec-key">Origin</span><span class="spec-val">India</span></div>
            <div class="spec-row"><span class="spec-key">Finish</span><span class="spec-val">Polished</span></div>
            <div class="spec-row"><span class="spec-key">Avg Size</span><span class="spec-val">128" × 76"</span></div>
          </div>
          <a href="contact?inquiry=Absolute+Black" class="btn-quote">Request Quote</a>
        </div>
      </div>

      <div class="product-card" data-type="Quartzite">
        <div class="product-card-img">
          <img src="<?= $base_url ?>assets/images/slab-quartzite.jpg" alt="Taj Mahal quartzite slab from Brazil"
            loading="lazy" />
          <span class="product-badge">Quartzite</span>
        </div>
        <div class="product-card-body">
          <h3>Taj Mahal</h3>
          <div class="product-specs">
            <div class="spec-row"><span class="spec-key">Origin</span><span class="spec-val">Brazil</span></div>
            <div class="spec-row"><span class="spec-key">Finish</span><span class="spec-val">Polished</span></div>
            <div class="spec-row"><span class="spec-key">Avg Size</span><span class="spec-val">130" × 80"</span></div>
          </div>
          <a href="contact?inquiry=Taj+Mahal" class="btn-quote">Request Quote</a>
        </div>
      </div>

      <div class="product-card" data-type="Marble">
        <div class="product-card-img">
          <img src="<?= $base_url ?>assets/images/slab-marble.jpg" alt="Carrara Venato marble slab from Italy"
            loading="lazy" />
          <span class="product-badge">Marble</span>
        </div>
        <div class="product-card-body">
          <h3>Carrara Venato</h3>
          <div class="product-specs">
            <div class="spec-row"><span class="spec-key">Origin</span><span class="spec-val">Italy</span></div>
            <div class="spec-row"><span class="spec-key">Finish</span><span class="spec-val">Honed</span></div>
            <div class="spec-row"><span class="spec-key">Avg Size</span><span class="spec-val">115" × 70"</span></div>
          </div>
          <a href="contact?inquiry=Carrara+Venato" class="btn-quote">Request Quote</a>
        </div>
      </div>

      <div class="product-card" data-type="Quartz">
        <div class="product-card-img">
          <img src="<?= $base_url ?>assets/images/slab-quartz.jpg" alt="Calacatta Gold engineered quartz slab"
            loading="lazy" />
          <span class="product-badge">Quartz</span>
        </div>
        <div class="product-card-body">
          <h3>Calacatta Gold</h3>
          <div class="product-specs">
            <div class="spec-row"><span class="spec-key">Origin</span><span class="spec-val">Engineered</span></div>
            <div class="spec-row"><span class="spec-key">Finish</span><span class="spec-val">Polished</span></div>
            <div class="spec-row"><span class="spec-key">Avg Size</span><span class="spec-val">126" × 63"</span></div>
          </div>
          <a href="contact?inquiry=Calacatta+Gold" class="btn-quote">Request Quote</a>
        </div>
      </div>

      <div class="product-card" data-type="Granite">
        <div class="product-card-img">
          <img src="<?= $base_url ?>assets/images/slab-granite.jpg" alt="Via Lactea granite slab from Brazil"
            loading="lazy" />
          <span class="product-badge">Granite</span>
        </div>
        <div class="product-card-body">
          <h3>Via Lactea</h3>
          <div class="product-specs">
            <div class="spec-row"><span class="spec-key">Origin</span><span class="spec-val">Brazil</span></div>
            <div class="spec-row"><span class="spec-key">Finish</span><span class="spec-val">Leathered</span></div>
            <div class="spec-row"><span class="spec-key">Avg Size</span><span class="spec-val">125" × 75"</span></div>
          </div>
          <a href="contact?inquiry=Via+Lactea" class="btn-quote">Request Quote</a>
        </div>
      </div>

      <div class="product-card" data-type="Quartzite">
        <div class="product-card-img">
          <img src="<?= $base_url ?>assets/images/slab-quartzite.jpg" alt="Macaubas Fantasy quartzite slab from Brazil"
            loading="lazy" />
          <span class="product-badge">Quartzite</span>
        </div>
        <div class="product-card-body">
          <h3>Macaubas Fantasy</h3>
          <div class="product-specs">
            <div class="spec-row"><span class="spec-key">Origin</span><span class="spec-val">Brazil</span></div>
            <div class="spec-row"><span class="spec-key">Finish</span><span class="spec-val">Polished</span></div>
            <div class="spec-row"><span class="spec-key">Avg Size</span><span class="spec-val">132" × 78"</span></div>
          </div>
          <a href="contact?inquiry=Macaubas+Fantasy" class="btn-quote">Request Quote</a>
        </div>
      </div>
    </div>

    <div style="background:var(--card);border:1px solid var(--border);padding:48px;text-align:center;margin-top:64px;">
      <h2 style="font-size:26px;margin-bottom:12px;">Inventory Changes Weekly</h2>
      <p class="muted" style="font-size:16px;margin-bottom:28px;">The best way to see our full selection is to visit our
        Dallas showroom in person. New containers arrive regularly.</p>
      <a href="contact" class="btn-primary">Schedule a Showroom Visit</a>
    </div>
  </div>
</main>