<header class="navbar" id="navbar">
  <div class="container">
    <a href="home" class="logo">
      <div class="logo-icon">R</div>
      <span class="logo-text">Royale Surfaces</span>
    </a>
    <nav class="nav-links">
      <a href="home">Home</a>
      <a href="about">About</a>
      <a href="products">Products</a>
      <a href="services">Services</a>
      <a href="gallery">Gallery</a>
      <a href="contact" class="btn-nav active">Contact Us</a>
    </nav>
    <button class="mobile-toggle" onclick="toggleMenu()" aria-label="Menu">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="6" x2="21" y2="6" />
        <line x1="3" y1="12" x2="21" y2="12" />
        <line x1="3" y1="18" x2="21" y2="18" />
      </svg>
    </button>
  </div>
  <nav class="mobile-menu" id="mobileMenu">
    <a href="home">Home</a>
    <a href="about">About</a>
    <a href="products">Products</a>
    <a href="services">Services</a>
    <a href="gallery">Gallery</a>
    <a href="contact" class="btn-nav">Contact Us</a>
  </nav>
</header>

<main style="padding-top:120px;padding-bottom:96px;">
  <div class="container">
    <div class="contact-grid">

      <!-- LEFT: Info + Map -->
      <div>
        <h1>Dallas Showroom</h1>
        <p class="muted" style="font-size:18px;margin-top:12px;margin-bottom:48px;line-height:1.7;">Our 50,000 sq ft
          slab gallery is open strictly to the trade. Schedule a visit to view our current inventory in person.</p>

        <div class="contact-info-list">
          <div class="contact-info-item">
            <div class="contact-info-icon">
              <svg viewBox="0 0 24 24">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
            </div>
            <div>
              <div class="contact-info-label">Location</div>
              <div class="contact-info-value">4820 Commerce Park Dr<br />Dallas, TX 75247</div>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="contact-info-icon">
              <svg viewBox="0 0 24 24">
                <path
                  d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.37 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.15a16 16 0 0 0 6.09 6.09l.87-.87a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
              </svg>
            </div>
            <div>
              <div class="contact-info-label">Direct Line</div>
              <div class="contact-info-value">(972) 555-0180</div>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="contact-info-icon">
              <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
            </div>
            <div>
              <div class="contact-info-label">Trade Hours</div>
              <div class="contact-info-value">Mon – Fri: 7:00 AM – 5:00 PM<br />Saturday: 8:00 AM – 2:00 PM<br />Sunday:
                Closed</div>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="contact-info-icon">
              <svg viewBox="0 0 24 24">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
              </svg>
            </div>
            <div>
              <div class="contact-info-label">Email</div>
              <div class="contact-info-value">trade@royalesurfaces.com</div>
            </div>
          </div>
        </div>

        <div class="map-embed">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3354.123456789!2d-96.8726!3d32.8011!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2z4820+Commerce+Park+Dr+Dallas+TX+75247!5e0!3m2!1sen!2sus!4v1234567890"
            allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
            title="Royale Surfaces showroom location map">
          </iframe>
        </div>
      </div>

      <!-- RIGHT: Form -->
      <div>
        <h2 class="contact-form-title">Schedule a Showroom Visit</h2>

        <div id="formSuccess" class="form-success">
          <h3>Inquiry Submitted</h3>
          <p>Thank you. A trade specialist will contact you within one business day.</p>
        </div>

        <form id="contactForm" onsubmit="handleSubmit(event)" novalidate>
          <div class="form-row">
            <div class="form-group">
              <label for="name">Full Name *</label>
              <input type="text" id="name" name="name" placeholder="John Smith" required />
              <span class="form-error" id="nameError" style="color:#e87878;font-size:12px;display:none;">Name is
                required</span>
            </div>
            <div class="form-group">
              <label for="company">Company Name *</label>
              <input type="text" id="company" name="company" placeholder="Apex Stone Co." required />
              <span class="form-error" id="companyError" style="color:#e87878;font-size:12px;display:none;">Company is
                required</span>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="phone">Phone Number *</label>
              <input type="tel" id="phone" name="phone" placeholder="(214) 555-0100" required />
              <span class="form-error" id="phoneError" style="color:#e87878;font-size:12px;display:none;">Valid phone
                required</span>
            </div>
            <div class="form-group">
              <label for="email">Email Address *</label>
              <input type="email" id="email" name="email" placeholder="john@apexstone.com" required />
              <span class="form-error" id="emailError" style="color:#e87878;font-size:12px;display:none;">Valid email
                required</span>
            </div>
          </div>
          <div class="form-group">
            <label for="message">Project Details / Inquiry *</label>
            <textarea id="message" name="message"
              placeholder="Please provide details about materials needed or when you'd like to visit..."
              required></textarea>
            <span class="form-error" id="messageError" style="color:#e87878;font-size:12px;display:none;">Please provide
              some details</span>
          </div>
          <button type="submit" class="btn-submit">Submit Inquiry</button>
        </form>
      </div>

    </div>
  </div>
</main>